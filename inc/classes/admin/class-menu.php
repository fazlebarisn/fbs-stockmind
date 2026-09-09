<?php
/**
 * Admin menu class for FBS StockMind
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Admin;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Menu
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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'check_permissions']);
        add_action('admin_notices', [$this, 'display_onboarding_notice']);
        add_action('admin_post_fbs_stockmind_onboarding', [$this, 'handle_onboarding_action']);
        add_action('admin_post_fbs_stockmind_dismiss_onboarding', [$this, 'handle_dismiss_onboarding']);
    }

    /**
     * Add admin menu
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function add_admin_menu()
    {
        // Check if user has permission
        if (!fbs_stockmind_can_manage()) {
            return;
        }

        // Main menu page
        add_menu_page(
            __('StockMind', 'fbs-stockmind'),
            __('StockMind', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind',
            [$this, 'render_dashboard_page'],
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'),
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Dashboard', 'fbs-stockmind'),
            __('Dashboard', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind',
            [$this, 'render_dashboard_page']
        );

        // Predictions submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Predictions', 'fbs-stockmind'),
            __('Predictions', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind-predictions',
            [$this, 'render_predictions_page']
        );

        // Suppliers submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Suppliers', 'fbs-stockmind'),
            __('Suppliers', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind-suppliers',
            [$this, 'render_suppliers_page']
        );

        // Reminders submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Customer Reminders', 'fbs-stockmind'),
            __('Customer Reminders', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind-reminders',
            [$this, 'render_reminders_page']
        );

        // Settings submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Settings', 'fbs-stockmind'),
            __('Settings', 'fbs-stockmind'),
            'manage_options',
            'fbs-stockmind-settings',
            [$this, 'render_settings_page']
        );

        // Our Plugins submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Our Plugins', 'fbs-stockmind'),
            __('Our Plugins', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind-plugins',
            [$this, 'render_our_plugins_page']
        );

        // Meet The Author submenu
        add_submenu_page(
            'fbs-stockmind',
            __('Meet The Author', 'fbs-stockmind'),
            __('Meet The Author', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind-author',
            [$this, 'render_author_page']
        );

        // Allow pro to add additional menu items
        do_action('fbs_stockmind_admin_menu', 'fbs-stockmind');
    }

    /**
     * Check user permissions
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function check_permissions()
    {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'fbs-stockmind') === false) {
            return;
        }

        if (!fbs_stockmind_can_manage()) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'fbs-stockmind'));
        }
    }

    /**
     * Render dashboard page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_dashboard_page()
    {
        Dashboard::get_instance()->render();
    }

    /**
     * Render predictions page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_predictions_page()
    {
        $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
        $predictions = $predictor->get_active_predictions();
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/predictions.php';
    }

    /**
     * Render suppliers page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_suppliers_page()
    {
        $supplier_manager = \FBS_StockMind\Inc\Features\Supplier_Manager::get_instance();
        $suppliers = $supplier_manager->get_all_suppliers();
        $supplier_count = $supplier_manager->get_suppliers_count();
        $max_suppliers = apply_filters('fbs_stockmind_max_suppliers', 3);

        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/suppliers.php';
    }

    /**
     * Render reminders page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_reminders_page()
    {
        $reminder_manager = \FBS_StockMind\Inc\Features\Customer_Reminders::get_instance();
        $reminders = $reminder_manager->get_all_reminders();
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/reminders.php';
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_settings_page()
    {
        Settings::get_instance()->render();
    }

    /**
     * Display onboarding notice
     */
    public function display_onboarding_notice()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Query parameters used only for displaying transient UI notice following a safe redirect
        if (isset($_GET['onboarding']) && sanitize_text_field(wp_unslash($_GET['onboarding'])) === 'success') {
            $count = isset($_GET['count']) ? absint(wp_unslash($_GET['count'])) : 0;
            ?>
            <div class="notice notice-success is-dismissible" style="padding: 10px;">
                <p><strong><?php esc_html_e('Success!', 'fbs-stockmind'); ?></strong> <?php 
                    printf(
                        /* translators: %d: Number of products marked as replenishable */
                        esc_html__('We found and marked %d products as replenishable.', 'fbs-stockmind'), 
                        absint($count)
                    ); 
                ?></p>
            </div>
            <?php
            return;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if (get_option('fbs_stockmind_onboarding_dismissed')) {
            return;
        }

        // Only show to admins
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        ?>
        <div class="notice notice-info" style="padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 style="margin-top: 0;"><?php esc_html_e('Welcome to FBS StockMind! 🎉', 'fbs-stockmind'); ?></h3>
                    <p><?php esc_html_e("Let's get started by automatically finding products that your customers frequently buy. We can mark them as 'replenishable' for you.", 'fbs-stockmind'); ?></p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=fbs_stockmind_onboarding&_wpnonce=' . wp_create_nonce('fbs_onboarding'))); ?>" class="button button-primary"><?php esc_html_e('Auto-Detect Products', 'fbs-stockmind'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=fbs_stockmind_dismiss_onboarding&_wpnonce=' . wp_create_nonce('fbs_onboarding'))); ?>" class="button"><?php esc_html_e('Skip for now', 'fbs-stockmind'); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle onboarding action
     */
    public function handle_onboarding_action()
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified via wp_verify_nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'fbs_onboarding')) {
            wp_die(esc_html__('Security check failed', 'fbs-stockmind'));
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permission denied', 'fbs-stockmind'));
        }

        // Simple logic to find products with more than 3 sales total and mark them as replenishable
        global $wpdb;
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Onboarding scan query, runs once upon user request and needs real-time order item metadata
        $results = $wpdb->get_results("
            SELECT order_item_meta.meta_value as product_id, SUM(order_item_meta_qty.meta_value) as total_qty
            FROM {$wpdb->prefix}woocommerce_order_items as order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_qty ON order_items.order_item_id = order_item_meta_qty.order_item_id
            WHERE order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            AND order_item_meta_qty.meta_key = '_qty'
            GROUP BY product_id
            HAVING total_qty >= 3
            LIMIT 50
        ");
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        $count = 0;
        if (!empty($results)) {
            foreach ($results as $row) {
                if ($row->product_id) {
                    update_post_meta($row->product_id, '_fbs_stockmind_replenishable', true);
                    $count++;
                }
            }
        }

        update_option('fbs_stockmind_onboarding_dismissed', 1);

        wp_safe_redirect(admin_url('admin.php?page=fbs-stockmind&onboarding=success&count=' . $count));
        exit;
    }

    /**
     * Handle dismiss onboarding
     */
    public function handle_dismiss_onboarding()
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified via wp_verify_nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'fbs_onboarding')) {
            wp_die(esc_html__('Security check failed', 'fbs-stockmind'));
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permission denied', 'fbs-stockmind'));
        }

        update_option('fbs_stockmind_onboarding_dismissed', 1);
        wp_safe_redirect(admin_url('admin.php?page=fbs-stockmind'));
        exit;
    }

    /**
     * Render Our Plugins page
     *
     * @since 1.0.0
     */
    public function render_our_plugins_page()
    {
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/our-plugins.php';
    }

    /**
     * Render Meet the Author page
     *
     * @since 1.0.0
     */
    public function render_author_page()
    {
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/plugin-author.php';
    }
}
