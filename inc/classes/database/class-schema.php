<?php
/**
 * Database schema definitions and creation logic.
 *
 * @package fbs-stockmind
 * @since 1.1.0
 */

namespace FBS_StockMind\Inc\Database;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Schema
{
    use Singleton;

    /**
     * Get table definitions
     *
     * @return array
     */
    private function get_schema()
    {
        global $wpdb;

        $collate = $wpdb->get_charset_collate();

        $tables = [
            "CREATE TABLE {$wpdb->prefix}fbs_stockmind_daily_sales (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                date date NOT NULL,
                units_sold int(11) NOT NULL DEFAULT 0,
                refund_quantity int(11) NOT NULL DEFAULT 0,
                revenue decimal(19,4) NOT NULL DEFAULT 0.0000,
                updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY product_date (product_id, date),
                KEY date (date)
            ) $collate;",

            "CREATE TABLE {$wpdb->prefix}fbs_stockmind_inventory_events (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                event_type varchar(50) NOT NULL,
                quantity_change int(11) NOT NULL,
                stock_after int(11) NOT NULL,
                reference_id bigint(20) unsigned DEFAULT NULL,
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY product_id (product_id),
                KEY event_type (event_type),
                KEY created_at (created_at)
            ) $collate;",
            
            "CREATE TABLE {$wpdb->prefix}fbs_stockmind_inventory_snapshots (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                date date NOT NULL,
                stock_level int(11) NOT NULL,
                stock_value decimal(19,4) NOT NULL DEFAULT 0.0000,
                PRIMARY KEY (id),
                UNIQUE KEY product_date (product_id, date)
            ) $collate;",
            
            "CREATE TABLE {$wpdb->prefix}fbs_stockmind_forecasts (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                forecast_date date NOT NULL,
                expected_daily_demand decimal(10,4) NOT NULL DEFAULT 0.0000,
                days_until_runout decimal(10,2) DEFAULT NULL,
                confidence_score decimal(4,2) NOT NULL DEFAULT 0.00,
                risk_level varchar(20) NOT NULL DEFAULT 'SAFE',
                calculated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY product_date (product_id, forecast_date),
                KEY risk_level (risk_level)
            ) $collate;"
        ];

        return $tables;
    }

    /**
     * Create or update database tables
     */
    public function create_tables()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $schemas = $this->get_schema();

        foreach ($schemas as $sql) {
            dbDelta($sql);
        }

        update_option('fbs_stockmind_db_version', '1.1.0');
    }
}
