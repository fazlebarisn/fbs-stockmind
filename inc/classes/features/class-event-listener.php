<?php
/**
 * Listens for WooCommerce events and queues them for background processing.
 *
 * @package fbs-stockmind
 * @since 1.1.0
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Event_Listener
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
        // Listen for new orders (processing or completed)
        add_action('woocommerce_order_status_processing', [$this, 'queue_order_processing'], 10, 2);
        add_action('woocommerce_order_status_completed', [$this, 'queue_order_processing'], 10, 2);
        
        // Listen for refunded orders to deduct from sales
        add_action('woocommerce_order_status_refunded', [$this, 'queue_order_refund'], 10, 2);

        // Listen for stock changes
        add_action('woocommerce_product_set_stock', [$this, 'queue_stock_update'], 10, 1);
        add_action('woocommerce_variation_set_stock', [$this, 'queue_stock_update'], 10, 1);
    }

    /**
     * Queue an order for sales aggregation
     *
     * @param int $order_id
     * @param object $order
     */
    public function queue_order_processing($order_id, $order = null)
    {
        if (function_exists('as_enqueue_async_action')) {
            // Check if action is already pending to avoid duplicates
            if (!as_has_scheduled_action('fbs_stockmind_process_order', ['order_id' => $order_id])) {
                as_enqueue_async_action('fbs_stockmind_process_order', ['order_id' => $order_id], 'fbs-stockmind');
            }
        }
    }

    /**
     * Queue a refunded order
     *
     * @param int $order_id
     */
    public function queue_order_refund($order_id)
    {
        if (function_exists('as_enqueue_async_action')) {
            if (!as_has_scheduled_action('fbs_stockmind_process_refund', ['order_id' => $order_id])) {
                as_enqueue_async_action('fbs_stockmind_process_refund', ['order_id' => $order_id], 'fbs-stockmind');
            }
        }
    }

    /**
     * Queue a product for stock snapshot update
     *
     * @param object $product_with_stock
     */
    public function queue_stock_update($product_with_stock)
    {
        if (!$product_with_stock || !is_object($product_with_stock)) {
            return;
        }

        $product_id = $product_with_stock->get_id();
        
        // Immediately recalculate prediction for this product so tables stay real-time accurate
        if (class_exists('\FBS_StockMind\Inc\Features\Predictor')) {
            \FBS_StockMind\Inc\Features\Predictor::get_instance()->update_single_product_prediction($product_id);
        }

        if (function_exists('as_enqueue_async_action')) {
            if (!as_has_scheduled_action('fbs_stockmind_process_stock_update', ['product_id' => $product_id])) {
                as_enqueue_async_action('fbs_stockmind_process_stock_update', ['product_id' => $product_id], 'fbs-stockmind');
            }
        }
    }
}
