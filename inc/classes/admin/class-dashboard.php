<?php
/**
 * Admin dashboard class for FBS StockMind
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Admin;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Dashboard
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
        // Constructor implementation
    }

    /**
     * Render the dashboard page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render()
    {
        // Get dashboard data
        $stats = $this->get_dashboard_stats();
        $recent_predictions = $this->get_recent_predictions();
        $upcoming_replenishments = $this->get_upcoming_replenishments();
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/dashboard.php';
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_dashboard_stats()
    {
        global $wpdb;
        
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        
        // Get total products needing attention
        $products_needing_attention = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT product_id) FROM $predictions_table 
             WHERE is_dismissed = 0 AND predicted_runout_date <= DATE_ADD(CURDATE(), INTERVAL %d DAY)",
            fbs_stockmind_get_option('alert_window', 14)
        ));
        
        // Get total active reminders
        $active_reminders = $wpdb->get_var(
            "SELECT COUNT(*) FROM $reminders_table WHERE is_active = 1"
        );
        
        // Get total suppliers
        $total_suppliers = $wpdb->get_var(
            "SELECT COUNT(*) FROM $suppliers_table WHERE is_active = 1"
        );
        
        // Get products with low stock
        $low_stock_products = $this->get_low_stock_products_count();
        
        return [
            'products_needing_attention' => (int) $products_needing_attention,
            'active_reminders' => (int) $active_reminders,
            'total_suppliers' => (int) $total_suppliers,
            'low_stock_products' => (int) $low_stock_products,
        ];
    }

    /**
     * Get recent predictions
     *
     * @param int $limit Number of predictions to retrieve
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_recent_predictions($limit = 5)
    {
        global $wpdb;
        
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, pr.post_title as product_name, pr.post_status
             FROM $predictions_table p
             LEFT JOIN {$wpdb->posts} pr ON p.product_id = pr.ID
             WHERE p.is_dismissed = 0 
             AND pr.post_status = 'publish'
             AND pr.post_type = 'product'
             ORDER BY p.predicted_runout_date ASC
             LIMIT %d",
            $limit
        ));
        
        $predictions = [];
        $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
        
        foreach ($results as $result) {
            $product = wc_get_product($result->product_id);
            if (!$product) {
                continue;
            }
            
            // Skip products that don't have stock tracking enabled
            if (!fbs_stockmind_is_product_stock_tracked($product)) {
                continue;
            }
            
            $predictions[] = [
                'id' => $result->id,
                'product_id' => $result->product_id,
                'product_name' => $result->product_name,
                'product_image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'current_stock' => $product->get_stock_quantity(),
                'predicted_runout_date' => $result->predicted_runout_date,
                'days_until_runout' => $this->calculate_days_until_runout($result->predicted_runout_date),
                'calculated_at' => $result->calculated_at,
            ];
        }
        
        return $predictions;
    }

    /**
     * Get upcoming replenishments
     *
     * @param int $limit Number of replenishments to retrieve
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_upcoming_replenishments($limit = 5)
    {
        global $wpdb;
        
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, pr.post_title as product_name
             FROM $reminders_table r
             LEFT JOIN {$wpdb->posts} pr ON r.product_id = pr.ID
             WHERE r.is_active = 1
             AND pr.post_status = 'publish'
             AND pr.post_type = 'product'
             ORDER BY r.created_at DESC
             LIMIT %d",
            $limit
        ));
        
        $replenishments = [];
        foreach ($results as $result) {
            $product = wc_get_product($result->product_id);
            if (!$product) {
                continue;
            }
            
            $replenishments[] = [
                'id' => $result->id,
                'customer_email' => $result->customer_email,
                'product_id' => $result->product_id,
                'product_name' => $result->product_name,
                'product_image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'order_id' => $result->order_id,
                'created_at' => $result->created_at,
                'last_reminder_sent' => $result->last_reminder_sent,
                'reminder_count' => $result->reminder_count,
            ];
        }
        
        return $replenishments;
    }

    /**
     * Get count of products with low stock
     *
     * @return int
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_low_stock_products_count()
    {
        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'stock_status' => 'instock',
            'meta_query' => [
                [
                    'key' => '_stock',
                    'value' => 10, // Consider low stock if less than 10
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
            ],
        ]);
        
        return count($products);
    }

    /**
     * Calculate days until runout
     *
     * @param string $runout_date The predicted runout date
     * @return int
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function calculate_days_until_runout($runout_date)
    {
        // Use the same calculation as the predictions page for consistency
        return (strtotime($runout_date) - time()) / DAY_IN_SECONDS;
    }
}
