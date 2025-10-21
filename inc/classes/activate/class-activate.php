<?php
/**
 * Plugin activation class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Activate
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
        $this->create_database_tables();
        $this->migrate_database_tables();
        $this->set_default_options();
        $this->create_supplier_post_type();
        $this->flush_rewrite_rules();
    }

    /**
     * Create custom database tables
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function create_database_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Predictions table
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        $predictions_sql = "CREATE TABLE $predictions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            predicted_runout_date date NOT NULL,
            confidence_score decimal(3,2) DEFAULT 0.00,
            calculated_at datetime NOT NULL,
            is_dismissed tinyint(1) NOT NULL DEFAULT 0,
            dismissed_at datetime NULL,
            dismissed_by bigint(20) NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY predicted_runout_date (predicted_runout_date),
            KEY confidence_score (confidence_score),
            KEY is_dismissed (is_dismissed)
        ) $charset_collate;";

        // Reminders table
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        $reminders_sql = "CREATE TABLE $reminders_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            customer_email varchar(255) NOT NULL,
            product_id bigint(20) NOT NULL,
            order_id bigint(20) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            last_reminder_sent datetime NULL,
            reminder_count int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY customer_email (customer_email),
            KEY product_id (product_id),
            KEY order_id (order_id),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Suppliers table
        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        $suppliers_sql = "CREATE TABLE $suppliers_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            lead_time int(11) NOT NULL DEFAULT 7,
            contact_info text NULL,
            email varchar(255) NULL,
            phone varchar(50) NULL,
            address text NULL,
            notes text NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY name (name),
            KEY is_active (is_active)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        dbDelta($predictions_sql);
        dbDelta($reminders_sql);
        dbDelta($suppliers_sql);

        // Store database version
        update_option('fbs_stockmind_db_version', '1.0.0');
    }

    /**
     * Migrate existing database tables
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function migrate_database_tables()
    {
        global $wpdb;

        $predictions_table = fbs_stockmind_get_table_name('predictions');
        
        // Check if confidence_score column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'confidence_score'",
            DB_NAME,
            $predictions_table
        ));

        if (empty($column_exists)) {
            // Add confidence_score column to existing table
            $wpdb->query("ALTER TABLE $predictions_table ADD COLUMN confidence_score decimal(3,2) DEFAULT 0.00 AFTER predicted_runout_date");
            $wpdb->query("ALTER TABLE $predictions_table ADD KEY confidence_score (confidence_score)");
            
            fbs_stockmind_log('Added confidence_score column to predictions table');
        }
    }

    /**
     * Set default plugin options
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function set_default_options()
    {
        $default_options = [
            'alert_window' => 14, // days
            'default_lead_time' => 7, // days
            'reminder_advance_days' => 5, // days
            'max_reminder_attempts' => 3,
            'email_from_name' => get_bloginfo('name'),
            'email_from_address' => get_option('admin_email'),
            'prediction_accuracy_threshold' => 0.8,
            'enable_customer_reminders' => true,
            'enable_admin_alerts' => true,
        ];

        foreach ($default_options as $option => $value) {
            if (get_option('fbs_stockmind_' . $option) === false) {
                add_option('fbs_stockmind_' . $option, $value);
            }
        }
    }

    /**
     * Create supplier custom post type
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function create_supplier_post_type()
    {
        // Register supplier post type directly here to avoid dependency issues
        $labels = [
            'name' => __('Suppliers', 'fbs-stockmind'),
            'singular_name' => __('Supplier', 'fbs-stockmind'),
            'menu_name' => __('Suppliers', 'fbs-stockmind'),
            'add_new' => __('Add New Supplier', 'fbs-stockmind'),
            'add_new_item' => __('Add New Supplier', 'fbs-stockmind'),
            'edit_item' => __('Edit Supplier', 'fbs-stockmind'),
            'new_item' => __('New Supplier', 'fbs-stockmind'),
            'view_item' => __('View Supplier', 'fbs-stockmind'),
            'search_items' => __('Search Suppliers', 'fbs-stockmind'),
            'not_found' => __('No suppliers found', 'fbs-stockmind'),
            'not_found_in_trash' => __('No suppliers found in trash', 'fbs-stockmind'),
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'menu_icon' => 'dashicons-businessman',
            'supports' => ['title', 'editor'],
            'show_in_rest' => false,
        ];

        register_post_type('fbs_supplier', $args);
    }

    /**
     * Flush rewrite rules
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function flush_rewrite_rules()
    {
        flush_rewrite_rules();
    }
}
