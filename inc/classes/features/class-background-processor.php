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
     * Process order and aggregate sales
     *
     * @param int $order_id
     */
    public function process_order($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Only process paid/completed orders
        if (!in_array($order->get_status(), ['processing', 'completed'])) {
            return;
        }

        $order_date = $order->get_date_created()->date('Y-m-d');
        global $wpdb;
        $sales_table = $wpdb->prefix . 'fbs_stockmind_daily_sales';

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            if (!$product_id) continue;

            $quantity = $item->get_quantity();
            $line_total = $item->get_total(); // Revenue excluding tax

            // Upsert daily sales record
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$sales_table} (product_id, date, units_sold, revenue, updated_at) 
                 VALUES (%d, %s, %d, %f, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE 
                 units_sold = units_sold + VALUES(units_sold),
                 revenue = revenue + VALUES(revenue),
                 updated_at = CURRENT_TIMESTAMP",
                $product_id,
                $order_date,
                $quantity,
                $line_total
            ));
        }
    }

    /**
     * Process refund and deduct from sales
     *
     * @param int $order_id
     */
    public function process_refund($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) return;

        $order_date = $order->get_date_created()->date('Y-m-d');
        global $wpdb;
        $sales_table = $wpdb->prefix . 'fbs_stockmind_daily_sales';

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            if (!$product_id) continue;

            $quantity = $item->get_quantity();
            $line_total = $item->get_total();

            // Upsert with refund values
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$sales_table} (product_id, date, refund_quantity, revenue, updated_at) 
                 VALUES (%d, %s, %d, -%f, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE 
                 refund_quantity = refund_quantity + VALUES(refund_quantity),
                 revenue = revenue + VALUES(revenue),
                 updated_at = CURRENT_TIMESTAMP",
                $product_id,
                $order_date,
                $quantity,
                $line_total
            ));
        }
    }

    /**
     * Process stock snapshot update
     *
     * @param int $product_id
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

        $date = gmdate('Y-m-d');
        $price = (float) $product->get_regular_price();
        $stock_value = $current_stock > 0 ? $current_stock * $price : 0;

        global $wpdb;
        $snapshots_table = $wpdb->prefix . 'fbs_stockmind_inventory_snapshots';

        // Upsert daily snapshot
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
    }
}
