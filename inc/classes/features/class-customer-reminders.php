<?php
/**
 * Customer reminders class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Customer_Reminders
{
    use Singleton;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function __construct()
    {
        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function setup_hooks()
    {
        // Register shortcode
        add_shortcode('fbs_stockmind_reminder', [$this, 'render_reminder_shortcode']);

        // Signed 1-click reminder unsubscribe handler
        add_action('template_redirect', [$this, 'handle_unsubscribe_request']);
    }

    /**
     * Generate a signed 1-click unsubscribe URL for a customer reminder.
     *
     * @param int    $reminder_id    Reminder row ID.
     * @param string $customer_email Customer email address.
     * @return string
     * @since 1.3.0
     */
    public function get_unsubscribe_url($reminder_id, $customer_email)
    {
        $reminder_id = absint($reminder_id);
        $token       = hash_hmac('sha256', $reminder_id . '|' . strtolower(trim((string) $customer_email)), wp_salt('auth'));

        return add_query_arg(
            [
                'fbs_sm_unsub' => $reminder_id,
                'token'        => $token,
            ],
            home_url('/')
        );
    }

    /**
     * Handle signed 1-click unsubscribe requests from customer reminder emails.
     *
     * @since 1.3.0
     * @return void
     */
    public function handle_unsubscribe_request()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Cryptographically signed HMAC token used for stateless email unsubscribe links
        if (!isset($_GET['fbs_sm_unsub'], $_GET['token'])) {
            return;
        }

        $reminder_id = absint(wp_unslash($_GET['fbs_sm_unsub']));
        $token       = sanitize_text_field(wp_unslash($_GET['token']));
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ($reminder_id <= 0 || empty($token)) {
            return;
        }

        global $wpdb;
        $reminders_table = fbs_stockmind_get_table_name('reminders');

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table lookup for signed unsubscribe verification
        $reminder = $wpdb->get_row($wpdb->prepare(
            "SELECT id, customer_email FROM {$reminders_table} WHERE id = %d",
            $reminder_id
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        if (!$reminder) {
            wp_die(
                esc_html__('This reminder link is no longer active.', 'fbs-stockmind'),
                esc_html__('Reminder Unsubscribe', 'fbs-stockmind'),
                ['response' => 200, 'back_link' => true]
            );
        }

        $expected_token = hash_hmac('sha256', $reminder->id . '|' . strtolower(trim((string) $reminder->customer_email)), wp_salt('auth'));
        if (!hash_equals($expected_token, $token)) {
            wp_die(
                esc_html__('Invalid unsubscribe verification link.', 'fbs-stockmind'),
                esc_html__('Reminder Unsubscribe', 'fbs-stockmind'),
                ['response' => 403]
            );
        }

        $this->deactivate_reminder((int) $reminder->id);

        $message_html = sprintf(
            '<h2>%1$s</h2><p>%2$s</p><p><a class="button" href="%3$s">%4$s</a></p>',
            esc_html__('You have been unsubscribed', 'fbs-stockmind'),
            esc_html__('You will no longer receive replenishment reminder emails for this product.', 'fbs-stockmind'),
            esc_url(home_url('/')),
            esc_html__('Return to Store', 'fbs-stockmind')
        );

        wp_die(
            wp_kses_post($message_html),
            esc_html__('Unsubscribed Successfully', 'fbs-stockmind'),
            ['response' => 200]
        );
    }

    /**
     * Build sanitized From email header preventing header injection.
     *
     * @return string
     * @since 1.3.0
     */
    private function get_sanitized_from_header()
    {
        $raw_name  = (string) fbs_stockmind_get_option('email_from_name', get_bloginfo('name'));
        $from_name = trim(preg_replace('/[\r\n<>"]+/', '', wp_strip_all_tags($raw_name)));
        if ('' === $from_name) {
            $from_name = trim(preg_replace('/[\r\n<>"]+/', '', wp_strip_all_tags(get_bloginfo('name'))));
        }

        $raw_email  = sanitize_email((string) fbs_stockmind_get_option('email_from_address', get_option('admin_email')));
        $from_email = is_email($raw_email) ? $raw_email : sanitize_email((string) get_option('admin_email'));

        return 'From: ' . $from_name . ' <' . $from_email . '>';
    }

    /**
     * Display reminder form on thank you page
     *
     * @param array $replenishable_products Array of replenishable products
     * @param int   $order_id              The order ID
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function display_reminder_form($replenishable_products, $order_id)
    {
        if (empty($replenishable_products) || !fbs_stockmind_get_option('enable_customer_reminders', true)) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $customer_email = $order->get_billing_email();

        wp_enqueue_style('fbs-stockmind-frontend');
        wp_enqueue_script('fbs-stockmind-frontend');
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/frontend/reminder-form.php';
    }

    /**
     * Render reminder form via shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_reminder_shortcode($atts)
    {
        if (!fbs_stockmind_get_option('enable_customer_reminders', true)) {
            return '';
        }

        $atts = shortcode_atts([
            'product_id' => 0,
        ], $atts, 'fbs_stockmind_reminder');

        $product_id = intval($atts['product_id']);
        
        if (!$product_id && is_product()) {
            $product_id = get_the_ID();
        }

        if (!$product_id || !fbs_stockmind_is_product_replenishable($product_id)) {
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return '';
        }

        // We don't have an order context here, so order_id is 0
        $order_id = 0;
        
        // If user is logged in, use their email
        $customer_email = '';
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $customer_email = $current_user->user_email;
        }

        $replenishable_products = [
            [
                'id' => $product_id,
                'name' => $product->get_name(),
                'quantity' => 1,
            ]
        ];

        wp_enqueue_style('fbs-stockmind-frontend');
        wp_enqueue_script('fbs-stockmind-frontend');

        ob_start();
        echo '<div class="fbs-shortcode-container">';
        // The template expects $replenishable_products, $order_id, and optionally $customer_email
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/frontend/product-reminder-form.php';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Set customer reminder
     *
     * @param string $customer_email Customer email address
     * @param int    $product_id     Product ID
     * @param int    $order_id       Order ID
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function set_reminder($customer_email, $product_id, $order_id)
    {
        global $wpdb;

        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        // Check if reminder already exists
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is from trusted source, real-time check needed
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $reminders_table 
             WHERE customer_email = %s AND product_id = %d AND is_active = 1",
            $customer_email,
            $product_id
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        if ($existing) {
            return false; // Reminder already exists
        }

        // Insert new reminder
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary for inserting reminder data, real-time operation
        $result = $wpdb->insert(
            $reminders_table,
            [
                'customer_email' => sanitize_email($customer_email),
                'product_id' => absint($product_id),
                'order_id' => absint($order_id),
                'is_active' => 1,
                'created_at' => current_time('mysql'),
                'reminder_count' => 0,
            ],
            ['%s', '%d', '%d', '%d', '%s', '%d']
        );

        return $result !== false;
    }

    /**
     * Handle AJAX set reminder
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_set_reminder()
    {
        // Verify nonce
        if (!isset($_POST['nonce'])) {
            wp_send_json_error(esc_html__('Security check failed.', 'fbs-stockmind'));
            return;
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'fbs_stockmind_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'fbs-stockmind'));
            return;
        }

        $customer_email = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : '';
        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;

        if (empty($customer_email) || !$product_id) {
            wp_send_json_error(__('Invalid data provided.', 'fbs-stockmind'));
        }

        if (!is_email($customer_email)) {
            wp_send_json_error(__('Invalid email address.', 'fbs-stockmind'));
        }

        if ($this->set_reminder($customer_email, $product_id, $order_id)) {
            wp_send_json_success(__('Reminder set successfully!', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Reminder already exists.', 'fbs-stockmind'));
        }
    }

    /**
     * Process reminders (called by cron job)
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function process_reminders()
    {
        if (!fbs_stockmind_get_option('enable_customer_reminders', true)) {
            return;
        }

        global $wpdb;
        
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        // Allow pro to override max attempts (free version: 1 attempt)
        $default_max_attempts = apply_filters('fbs_stockmind_default_max_reminder_attempts', 1);
        $max_attempts = apply_filters(
            'fbs_stockmind_max_reminder_attempts',
            fbs_stockmind_get_option('max_reminder_attempts', $default_max_attempts)
        );
        // Allow pro to override advance days (free version: fixed 5 days)
        $default_advance_days = apply_filters('fbs_stockmind_default_reminder_advance_days', 5);
        $advance_days = apply_filters(
            'fbs_stockmind_reminder_advance_days',
            fbs_stockmind_get_option('reminder_advance_days', $default_advance_days)
        );
        
        // Get active reminders that haven't exceeded max attempts
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is from trusted source, real-time processing needed
        $reminders = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, pr.post_title as product_name
             FROM $reminders_table r
             LEFT JOIN {$wpdb->posts} pr ON r.product_id = pr.ID
             WHERE r.is_active = 1 
             AND r.reminder_count < %d
             AND pr.post_status = 'publish'
             AND pr.post_type = 'product'
             ORDER BY r.created_at ASC",
            $max_attempts
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        foreach ($reminders as $reminder) {
            $this->process_single_reminder($reminder, $advance_days);
        }

        fbs_stockmind_log('Processed ' . count($reminders) . ' customer reminders');
    }

    /**
     * Process a single reminder
     *
     * @param object $reminder     The reminder object
     * @param int    $advance_days Days in advance to send reminder
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function process_single_reminder($reminder, $advance_days)
    {
        // Get product prediction
        $predicted_date = $this->get_product_prediction($reminder->product_id);
        
        if (!$predicted_date) {
            return;
        }

        // Calculate when to send reminder
        $reminder_date = gmdate('Y-m-d', strtotime("{$predicted_date} -{$advance_days} days"));
        $today = gmdate('Y-m-d');

        // Check if it's time to send reminder
        if ($reminder_date <= $today) {
            // Check if we already sent a reminder recently (within 7 days)
            $last_reminder = $reminder->last_reminder_sent;
            if ($last_reminder && strtotime($last_reminder) > strtotime('-7 days')) {
                return;
            }

            // Send reminder email
            if ($this->send_reminder_email($reminder)) {
                $this->update_reminder_sent($reminder->id);
                do_action('fbs_stockmind_after_reminder_sent', $reminder);
            }
        }
    }

    /**
     * Get product prediction
     *
     * @param int $product_id The product ID
     * @return string|false The predicted runout date or false
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_product_prediction($product_id)
    {
        global $wpdb;
        
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is from trusted source, real-time data needed
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT predicted_runout_date FROM $predictions_table 
             WHERE product_id = %d AND is_dismissed = 0",
            $product_id
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return $result;
    }

    /**
     * Send reminder email
     *
     * @param object $reminder The reminder object
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function send_reminder_email($reminder)
    {
        // Allow pro to override email template
        $email_template = apply_filters('fbs_stockmind_reminder_email_template', null, $reminder);
        
        if ($email_template !== null) {
            return $this->send_custom_reminder_email($reminder, $email_template);
        }
        
        $product = wc_get_product($reminder->product_id);
        if (!$product) {
            return false;
        }

        $customer_email = $reminder->customer_email;
        $product_name = $reminder->product_name;
        $product_url = get_permalink($reminder->product_id);
        
        $subject = sprintf(
            /* translators: %s: Product name */
            __('Time to reorder: %s', 'fbs-stockmind'),
            $product_name
        );

        $message = $this->get_reminder_email_template($reminder, $product, $product_url, $subject);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            $this->get_sanitized_from_header(),
        ];

        $sent = wp_mail($customer_email, $subject, $message, $headers);
        
        if ($sent) {
            fbs_stockmind_log("Reminder email sent to {$customer_email} for product {$product_name}");
        } else {
            fbs_stockmind_log("Failed to send reminder email to {$customer_email} for product {$product_name}", 'error');
        }

        return $sent;
    }

    /**
     * Get reminder email template
     *
     * @param object $reminder    The reminder object
     * @param object $product     The product object
     * @param string $product_url The product URL
     * @param string $subject     The email subject line
     * @return string
     * @since 1.0.0
     * @author Fazle Bari <fazle@fbsfusion.com>
     */
    private function get_reminder_email_template($reminder, $product, $product_url, $subject = '')
    {
        $product_name    = $product->get_name();
        $product_price   = $product->get_price_html();
        $product_image   = wp_get_attachment_image_url($product->get_image_id(), 'medium');
        $unsubscribe_url = $this->get_unsubscribe_url($reminder->id, $reminder->customer_email);
        if ('' === $subject) {
            $subject = sprintf(
                /* translators: %s: Product name */
                __('Time to reorder: %s', 'fbs-stockmind'),
                $product_name
            );
        }
        
        ob_start();
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/email/reminder.php';
        return ob_get_clean();
    }

    /**
     * Update reminder sent status
     *
     * @param int $reminder_id The reminder ID
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function update_reminder_sent($reminder_id)
    {
        global $wpdb;
        
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary for updating reminder status, real-time operation
        $wpdb->update(
            $reminders_table,
            [
                'last_reminder_sent' => current_time('mysql'),
                // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is from trusted source
                'reminder_count' => $wpdb->get_var($wpdb->prepare(
                    "SELECT reminder_count FROM $reminders_table WHERE id = %d",
                    $reminder_id
                )) + 1,
                // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter
            ],
            ['id' => $reminder_id],
            ['%s', '%d'],
            ['%d']
        );
    }

    /**
     * Get all reminders
     *
     * @param int $limit Number of reminders to retrieve
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_all_reminders($limit = -1)
    {
        global $wpdb;
        
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        $limit_clause = $limit > 0 ? $wpdb->prepare("LIMIT %d", $limit) : '';
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name and limit clause are from trusted sources, admin list needs real-time data
        $results = $wpdb->get_results(
            "SELECT r.*, pr.post_title as product_name
             FROM $reminders_table r
             LEFT JOIN {$wpdb->posts} pr ON r.product_id = pr.ID
             WHERE pr.post_status = 'publish'
             AND pr.post_type = 'product'
             ORDER BY r.created_at DESC
             $limit_clause"
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        $reminders = [];
        foreach ($results as $result) {
            $product = wc_get_product($result->product_id);
            if (!$product) {
                continue;
            }

            $reminders[] = [
                'id' => $result->id,
                'customer_email' => $result->customer_email,
                'product_id' => $result->product_id,
                'product_name' => $result->product_name,
                'product_image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'order_id' => $result->order_id,
                'is_active' => $result->is_active,
                'created_at' => $result->created_at,
                'last_reminder_sent' => $result->last_reminder_sent,
                'reminder_count' => $result->reminder_count,
            ];
        }

        return $reminders;
    }

    /**
     * Deactivate reminder
     *
     * @param int $reminder_id The reminder ID
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function deactivate_reminder($reminder_id)
    {
        global $wpdb;
        
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary for deactivating reminder, real-time operation
        $result = $wpdb->update(
            $reminders_table,
            ['is_active' => 0],
            ['id' => $reminder_id],
            ['%d'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Delete reminder
     *
     * @param int $reminder_id The reminder ID
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function delete_reminder($reminder_id)
    {
        global $wpdb;
        
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary for deleting reminder, real-time operation
        $result = $wpdb->delete(
            $reminders_table,
            ['id' => $reminder_id],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Send custom reminder email (for pro to override)
     *
     * @param object $reminder The reminder object
     * @param string $template Custom email template
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function send_custom_reminder_email($reminder, $template)
    {
        $product = wc_get_product($reminder->product_id);
        if (!$product) {
            return false;
        }

        $customer_email = $reminder->customer_email;
        $product_name = $reminder->product_name;
        
        $subject = sprintf(
            /* translators: %s: Product name */
            __('Time to reorder: %s', 'fbs-stockmind'),
            $product_name
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            $this->get_sanitized_from_header(),
        ];

        return wp_mail($customer_email, $subject, $template, $headers);
    }
}
