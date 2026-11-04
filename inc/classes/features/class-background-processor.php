<?php
/**
 * Processes queued background actions for data aggregation.
 *
 * @package fbs-stockmind
 * @since 1.1.0
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Background_Processor
{
    use Singleton;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks
     */
    protected function setup_hooks()
    {
        add_action('fbs_stockmind_process_order', [$this, 'process_order']);
        add_action('fbs_stockmind_process_refund', [$this, 'process_refund']);
        add_action('fbs_stockmind_process_stock_update', [$this, 'process_stock_update']);
    }

    /**
     * Process order and aggregate sales (idempotent via HPOS-compatible order meta)
     *
     * @param int $order_id Order ID.
     */
    public function process_order($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Only process paid/completed orders
        if (!in_array($order->get_status(), ['processing', 'completed'], true)) {
            return;
        }

        // Prevent duplicate sales inflation when an order transitions processing -> completed or retries
        if ($order->get_meta('_fbs_stockmind_sales_recorded')) {
            return;
        }

        $order->update_meta_data('_fbs_stockmind_sales_recorded', 1);
        $order->save_meta_data();

        $date_created = $order->get_date_created();
        $order_date   = $date_created ? $date_created->date('Y-m-d') : gmdate('Y-m-d');

        global $wpdb;
        $sales_table = fbs_stockmind_get_table_name('daily_sales');
        $parent_products_to_recalc = [];

        foreach ($order->get_items() as $item) {
            $parent_product_id = (int) $item->get_product_id();
            $record_product_id = $item->get_variation_id() ? (int) $item->get_variation_id() : $parent_product_id;
            if (!$record_product_id) {
                continue;
            }

            $quantity   = max(0, (int) $item->get_quantity());
            $line_total = (float) $item->get_total(); // Revenue excluding tax

            // Upsert daily sales record
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table name from trusted prefix
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$sales_table} (product_id, date, units_sold, revenue, updated_at) 
                 VALUES (%d, %s, %d, %f, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE 
                 units_sold = units_sold + VALUES(units_sold),
                 revenue = revenue + VALUES(revenue),
                 updated_at = CURRENT_TIMESTAMP",
                $record_product_id,
                $order_date,
                $quantity,
                $line_total
            ));
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

            if ($parent_product_id > 0) {
                $parent_products_to_recalc[$parent_product_id] = true;
            }
        }

        // Recalculate prediction once per parent product
        if (!empty($parent_products_to_recalc) && class_exists('\FBS_StockMind\Inc\Features\Predictor')) {
            $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
            foreach (array_keys($parent_products_to_recalc) as $pid) {
                $predictor->update_single_product_prediction($pid);
            }
        }
    }

    /**
     * Process refund and deduct from sales (idempotent via HPOS-compatible order meta)
     *
     * @param int $order_id Order ID.
     */
    public function process_refund($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_meta('_fbs_stockmind_refund_recorded')) {
            return;
        }

        $order->update_meta_data('_fbs_stockmind_refund_recorded', 1);
        $order->save_meta_data();

        $date_created = $order->get_date_created();
        $order_date   = $date_created ? $date_created->date('Y-m-d') : gmdate('Y-m-d');

        global $wpdb;
        $sales_table = fbs_stockmind_get_table_name('daily_sales');
        $parent_products_to_recalc = [];

        foreach ($order->get_items() as $item) {
            $parent_product_id = (int) $item->get_product_id();
            $record_product_id = $item->get_variation_id() ? (int) $item->get_variation_id() : $parent_product_id;
            if (!$record_product_id) {
                continue;
            }

            $quantity   = max(0, (int) $item->get_quantity());
            $line_total = (float) $item->get_total();

            // Upsert with refund values
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table name from trusted prefix
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$sales_table} (product_id, date, refund_quantity, revenue, updated_at) 
                 VALUES (%d, %s, %d, -%f, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE 
                 refund_quantity = refund_quantity + VALUES(refund_quantity),
                 revenue = revenue + VALUES(revenue),
                 updated_at = CURRENT_TIMESTAMP",
                $record_product_id,
                $order_date,
                $quantity,
                $line_total
            ));
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

            if ($parent_product_id > 0) {
                $parent_products_to_recalc[$parent_product_id] = true;
            }
        }

        if (!empty($parent_products_to_recalc) && class_exists('\FBS_StockMind\Inc\Features\Predictor')) {
            $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
            foreach (array_keys($parent_products_to_recalc) as $pid) {
                $predictor->update_single_product_prediction($pid);
            }
        }
    }

    /**
     * Process stock snapshot update
     *
     * @param int $product_id Product or Variation ID.
     */
    public function process_stock_update($product_id)
    {
        $product = wc_get_product($product_id);
        if (!$product || !$product->managing_stock()) {
            return;
        }

        $current_stock = $product->get_stock_quantity();
        if ($current_stock === null) {
            return;
        }

        $date        = gmdate('Y-m-d');
        $price       = (float) $product->get_price();
        $stock_value = $current_stock > 0 ? $current_stock * $price : 0;

        global $wpdb;
        $snapshots_table = fbs_stockmind_get_table_name('inventory_snapshots');

        // Upsert daily snapshot
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table name from trusted prefix
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$snapshots_table} (product_id, date, stock_level, stock_value) 
             VALUES (%d, %s, %d, %f)
             ON DUPLICATE KEY UPDATE 
             stock_level = VALUES(stock_level),
             stock_value = VALUES(stock_value)",
            $product_id,
            $date,
            $current_stock,
            $stock_value
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter

        // Update predictions table for the main published product (or parent if variation)
        $target_prediction_id = $product->get_parent_id() > 0 ? (int) $product->get_parent_id() : (int) $product_id;
        if (class_exists('\FBS_StockMind\Inc\Features\Predictor')) {
            \FBS_StockMind\Inc\Features\Predictor::get_instance()->update_single_product_prediction($target_prediction_id);
        }
    }
}
