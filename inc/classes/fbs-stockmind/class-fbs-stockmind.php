<?php
/**
 * Main FBS StockMind plugin class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class FBS_StockMind
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
        $this->load_classes();
    }

    /**
     * Setup WordPress hooks
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function setup_hooks()
    {
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_ajax_fbs_stockmind_ajax', [$this, 'handle_ajax_request']);
        add_action('wp_ajax_nopriv_fbs_stockmind_ajax', [$this, 'handle_ajax_request']);
        
        // WooCommerce hooks
        add_action('woocommerce_thankyou', [$this, 'show_replenishment_reminder'], 10, 1);
        add_action('woocommerce_order_status_completed', [$this, 'show_replenishment_reminder'], 10, 1);
        
        // Cron jobs
        add_action('fbs_stockmind_daily_predictions', [$this, 'run_daily_predictions']);
        add_action('fbs_stockmind_daily_reminders', [$this, 'run_daily_reminders']);
        
        // Schedule cron jobs if not already scheduled
        if (!wp_next_scheduled('fbs_stockmind_daily_predictions')) {
            wp_schedule_event(time(), 'daily', 'fbs_stockmind_daily_predictions');
        }
        
        if (!wp_next_scheduled('fbs_stockmind_daily_reminders')) {
            wp_schedule_event(time(), 'daily', 'fbs_stockmind_daily_reminders');
        }
    }

    /**
     * Load required classes
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function load_classes()
    {
        // Manually include required classes to avoid autoloader issues
        $required_files = [
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/admin/class-menu.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/admin/class-dashboard.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/admin/class-settings.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/admin/class-assets.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/features/class-predictor.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/features/class-supplier-manager.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/features/class-customer-reminders.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/features/class-notification-system.php',
            FBS_STOCKMIND_DIR_PATH . '/inc/classes/ajax/class-ajax-handler.php',
        ];
        
        foreach ($required_files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
        
        // Load core classes
        Admin\Menu::get_instance();
        Admin\Dashboard::get_instance();
        Admin\Settings::get_instance();
        Admin\Assets::get_instance();
        
        // Load feature classes
        Features\Predictor::get_instance();
        Features\Supplier_Manager::get_instance();
        Features\Customer_Reminders::get_instance();
        Features\Notification_System::get_instance();
        
        // Load AJAX handlers
        Ajax\Ajax_Handler::get_instance();
    }

    /**
     * Load plugin text domain
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'fbs-stockmind',
            false,
            dirname(plugin_basename(FBS_STOCKMIND_FILE)) . '/languages'
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook The current admin page hook
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function enqueue_admin_assets($hook)
    {
        // Only load on StockMind admin pages
        if (strpos($hook, 'fbs-stockmind') === false) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'fbs-stockmind-admin',
            FBS_STOCKMIND_URL . '/assets/css/admin.css',
            [],
            FBS_STOCKMIND_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'fbs-stockmind-admin',
            FBS_STOCKMIND_URL . '/assets/js/admin.js',
            ['jquery'],
            FBS_STOCKMIND_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('fbs-stockmind-admin', 'fbsStockMind', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fbs_stockmind_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'fbs-stockmind'),
                'error' => __('An error occurred. Please try again.', 'fbs-stockmind'),
                'success' => __('Operation completed successfully.', 'fbs-stockmind'),
                'confirm' => __('Are you sure?', 'fbs-stockmind'),
            ],
        ]);
    }

    /**
     * Enqueue frontend assets
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function enqueue_frontend_assets()
    {
        // Only load on WooCommerce pages
        if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'fbs-stockmind-frontend',
            FBS_STOCKMIND_URL . '/assets/css/frontend.css',
            [],
            FBS_STOCKMIND_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'fbs-stockmind-frontend',
            FBS_STOCKMIND_URL . '/assets/js/frontend.js',
            ['jquery'],
            FBS_STOCKMIND_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('fbs-stockmind-frontend', 'fbsStockMind', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fbs_stockmind_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'fbs-stockmind'),
                'error' => __('An error occurred. Please try again.', 'fbs-stockmind'),
                'success' => __('Reminder set successfully!', 'fbs-stockmind'),
            ],
        ]);
    }

    /**
     * Handle AJAX requests
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_ajax_request()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'fbs_stockmind_nonce')) {
            wp_die(esc_html__('Security check failed.', 'fbs-stockmind'));
        }

        $action = sanitize_text_field($_POST['action_type'] ?? '');
        
        // Route to appropriate handler
        switch ($action) {
            case 'set_reminder':
                Features\Customer_Reminders::get_instance()->handle_set_reminder();
                break;
            case 'dismiss_prediction':
                Features\Predictor::get_instance()->handle_dismiss_prediction();
                break;
            case 'create_purchase_draft':
                Features\Predictor::get_instance()->handle_create_purchase_draft();
                break;
            case 'save_settings':
                Admin\Settings::get_instance()->handle_save_settings();
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'fbs-stockmind'));
        }
    }

    /**
     * Show replenishment reminder on thank you page
     *
     * @param int $order_id The order ID
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function show_replenishment_reminder($order_id)
    {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $replenishable_products = [];
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if ($product_id && fbs_stockmind_is_product_replenishable($product_id)) {
                $replenishable_products[] = [
                    'id' => $product_id,
                    'name' => $item->get_name(),
                    'quantity' => $item->get_quantity(),
                ];
            }
        }

        if (!empty($replenishable_products)) {
            Features\Customer_Reminders::get_instance()->display_reminder_form($replenishable_products, $order_id);
        }
    }

    /**
     * Run daily predictions cron job
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function run_daily_predictions()
    {
        Features\Predictor::get_instance()->calculate_all_predictions();
    }

    /**
     * Run daily reminders cron job
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function run_daily_reminders()
    {
        Features\Customer_Reminders::get_instance()->process_reminders();
    }
}
