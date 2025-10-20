<?php
/**
 * Admin assets class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Admin;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Assets
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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
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
                'confirmDelete' => __('Are you sure you want to delete this item?', 'fbs-stockmind'),
                'confirmDeactivate' => __('Are you sure you want to deactivate this item?', 'fbs-stockmind'),
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
}
