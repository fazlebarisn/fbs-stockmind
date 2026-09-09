<?php
/**
 * Migrates historical data into the new custom analytics tables.
 *
 * @package fbs-stockmind
 * @since 1.1.0
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Data_Migrator
{
    use Singleton;

    /**
     * Constructor
     */
    protected function __construct()
    {
        add_action('fbs_stockmind_migrate_orders_batch', [$this, 'process_orders_batch'], 10, 2);
        add_action('wp_ajax_fbs_stockmind_trigger_migration', [$this, 'ajax_trigger_migration']);
    }

    /**
     * Trigger migration via AJAX
     */
    public function ajax_trigger_migration()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied.');
        }

        if (!function_exists('as_enqueue_async_action')) {
            wp_send_json_error('Action Scheduler not available.');
        }

        // Start migration
        $this->queue_migration_batch(0);

        wp_send_json_success('Historical data migration started in the background.');
    }

    /**
     * Queue a batch of orders for migration
     *
     * @param int $offset
     */
    public function queue_migration_batch($offset = 0)
    {
        as_enqueue_async_action('fbs_stockmind_migrate_orders_batch', [
            'offset' => $offset,
            'batch_size' => 50
        ], 'fbs-stockmind');
    }

    /**
     * Process a batch of historical orders
     *
     * @param int $offset
     * @param int $batch_size
     */
    public function process_orders_batch($offset, $batch_size)
    {
        $orders = wc_get_orders([
            'limit' => $batch_size,
            'offset' => $offset,
            'status' => ['completed', 'processing'],
            'orderby' => 'date',
            'order' => 'ASC',
            'return' => 'ids',
        ]);

        if (empty($orders)) {
            // Migration complete
            update_option('fbs_stockmind_historical_migration_complete', time());
            return;
        }

        $processor = Background_Processor::get_instance();
        
        foreach ($orders as $order_id) {
            $processor->process_order($order_id);
        }

        // Queue the next batch
        $this->queue_migration_batch($offset + $batch_size);
    }
}
