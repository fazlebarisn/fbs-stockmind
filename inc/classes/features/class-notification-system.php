<?php
/**
 * Notification system class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Notification_System
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
        add_action('admin_notices', [$this, 'show_admin_notices']);
        add_action('wp_ajax_fbs_stockmind_dismiss_notice', [$this, 'dismiss_admin_notice']);
    }

    /**
     * Show admin notices
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function show_admin_notices()
    {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'fbs-stockmind') === false) {
            return;
        }

        // Check for low stock alerts
        if (fbs_stockmind_get_option('enable_admin_alerts', true)) {
            $this->show_low_stock_alerts();
        }

        // Check for plugin setup notices
        $this->show_setup_notices();
    }

    /**
     * Show low stock alerts
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function show_low_stock_alerts()
    {
        global $wpdb;
        
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        $alert_window = fbs_stockmind_get_option('alert_window', 14);
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is from trusted source, notification count needs real-time data
        $urgent_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $predictions_table p
             LEFT JOIN {$wpdb->posts} pr ON p.product_id = pr.ID
             WHERE p.is_dismissed = %d 
             AND pr.post_status = %s
             AND pr.post_type = %s
             AND p.predicted_runout_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
            0, 'publish', 'product'
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        if ($urgent_count > 0) {
            $this->display_notice(
                'warning',
                sprintf(
                    /* translators: %d: Number of products */
                    __('⚠️ %d products are predicted to run out of stock within 7 days!', 'fbs-stockmind'),
                    $urgent_count
                ),
                admin_url('admin.php?page=fbs-stockmind-predictions'),
                __('View Predictions', 'fbs-stockmind')
            );
        }
    }

    /**
     * Show setup notices
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function show_setup_notices()
    {
        // Check if suppliers are set up
        global $wpdb;
        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is from trusted source, notification count needs real-time data
        $supplier_count = $wpdb->get_var("SELECT COUNT(*) FROM $suppliers_table WHERE is_active = 1");

        if ($supplier_count == 0) {
            $this->display_notice(
                'info',
                __('📋 Set up your suppliers to get more accurate stock predictions with lead times.', 'fbs-stockmind'),
                admin_url('admin.php?page=fbs-stockmind-suppliers'),
                __('Add Suppliers', 'fbs-stockmind')
            );
        }

        // Check if customer reminders are enabled
        if (!fbs_stockmind_get_option('enable_customer_reminders', true)) {
            $this->display_notice(
                'info',
                __('🔔 Customer reminders are currently disabled. Enable them to help customers never run out of their favorite products.', 'fbs-stockmind'),
                admin_url('admin.php?page=fbs-stockmind-settings'),
                __('Enable Reminders', 'fbs-stockmind')
            );
        }
    }

    /**
     * Display admin notice
     *
     * @param string $type    Notice type (success, error, warning, info)
     * @param string $message Notice message
     * @param string $url     Action URL
     * @param string $button  Button text
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function display_notice($type, $message, $url = '', $button = '')
    {
        $notice_id = 'fbs_stockmind_' . md5($message);
        
        // Check if notice was dismissed
        if (get_user_meta(get_current_user_id(), $notice_id, true)) {
            return;
        }

        $class = 'notice notice-' . $type . ' is-dismissible fbs-stockmind-notice';
        
        echo '<div class="' . esc_attr($class) . '" data-notice-id="' . esc_attr($notice_id) . '">';
        echo '<p>' . wp_kses_post($message) . '</p>';
        
        if ($url && $button) {
            echo '<p><a href="' . esc_url($url) . '" class="button button-primary">' . esc_html($button) . '</a></p>';
        }
        
        echo '</div>';
    }

    /**
     * Dismiss admin notice
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function dismiss_admin_notice()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Insufficient permissions.', 'fbs-stockmind'));
        }

        // Verify nonce
        if (!isset($_POST['nonce'])) {
            wp_die(esc_html__('Security check failed.', 'fbs-stockmind'));
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'fbs_stockmind_nonce')) {
            wp_die(esc_html__('Security check failed.', 'fbs-stockmind'));
        }

        $notice_id = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';
        
        if (!$notice_id) {
            wp_send_json_error(__('Invalid notice ID.', 'fbs-stockmind'));
        }

        update_user_meta(get_current_user_id(), $notice_id, true);
        wp_send_json_success(__('Notice dismissed.', 'fbs-stockmind'));
    }

    /**
     * Send email notification to admin
     *
     * @param string $subject Email subject
     * @param string $message Email message
     * @param string $type    Notification type
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function send_admin_notification($subject, $message, $type = 'info')
    {
        $admin_email = get_option('admin_email');
        $from_name = fbs_stockmind_get_option('email_from_name', get_bloginfo('name'));
        $from_email = fbs_stockmind_get_option('email_from_address', $admin_email);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        ];

        $email_message = $this->get_email_template($subject, $message, $type);
        
        return wp_mail($admin_email, $subject, $email_message, $headers);
    }

    /**
     * Get email template CSS
     *
     * @param string $color Header background color
     * @return string
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_email_template_css($color)
    {
        $css = '
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f8f9fa;
            }
            .email-container {
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .email-header {
                background-color: ' . esc_attr($color) . ';
                color: white;
                padding: 30px;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 600;
            }
            .email-body {
                padding: 30px;
            }
            .email-footer {
                background-color: #f8f9fa;
                padding: 20px;
                text-align: center;
                color: #6c757d;
                font-size: 14px;
            }
        ';
        return $css;
    }

    /**
     * Get email template
     *
     * @param string $subject Email subject
     * @param string $message Email message
     * @param string $type    Notification type
     * @return string
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_email_template($subject, $message, $type)
    {
        $type_colors = [
            'success' => '#28a745',
            'error' => '#dc3545',
            'warning' => '#ffc107',
            'info' => '#17a2b8',
        ];
        
        $color = $type_colors[$type] ?? '#17a2b8';
        $css = $this->get_email_template_css($color);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($subject); ?></title>
            <style><?php echo esc_html($css); ?></style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>🧠 <?php echo esc_html(get_bloginfo('name')); ?> - StockMind</h1>
                </div>
                
                <div class="email-body">
                    <h2><?php echo esc_html($subject); ?></h2>
                    <div><?php echo wp_kses_post($message); ?></div>
                </div>
                
                <div class="email-footer">
                    <p><?php esc_html_e('This is an automated notification from StockMind.', 'fbs-stockmind'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Log notification
     *
     * @param string $message Notification message
     * @param string $type    Notification type
     * @param array  $data    Additional data
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function log_notification($message, $type = 'info', $data = [])
    {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'user_id' => get_current_user_id(),
        ];

        $logs = get_option('fbs_stockmind_notification_logs', []);
        $logs[] = $log_entry;
        
        // Keep only last 100 log entries
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('fbs_stockmind_notification_logs', $logs);
    }

    /**
     * Get notification logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_notification_logs($limit = 50)
    {
        $logs = get_option('fbs_stockmind_notification_logs', []);
        return array_slice(array_reverse($logs), 0, $limit);
    }

    /**
     * Clear notification logs
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function clear_notification_logs()
    {
        delete_option('fbs_stockmind_notification_logs');
    }
}
