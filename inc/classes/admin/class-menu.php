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
            wp_die(__('You do not have sufficient permissions to access this page.', 'fbs-stockmind'));
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

}
