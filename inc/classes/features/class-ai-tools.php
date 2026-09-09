<?php
/**
 * AI Tool definitions for the Inventory Assistant (Pro)
 *
 * @package fbs-stockmind
 * @since 1.0.0
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') || die('Nice Try!');

class AI_Tools
{
    use Singleton;

    /**
     * Get inventory summary tool
     *
     * @return array
     */
    public function get_inventory_summary()
    {
        global $wpdb;

        $predictions_table = $wpdb->prefix . 'fbs_stockmind_predictions';
        $suppliers_table = $wpdb->prefix . 'fbs_stockmind_suppliers';
        
        $products_needing_attention = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT product_id) FROM $predictions_table 
             WHERE is_dismissed = 0 AND predicted_runout_date <= DATE_ADD(CURDATE(), INTERVAL %d DAY)",
            fbs_stockmind_get_option('alert_window', 14)
        ));
        
        $total_suppliers = $wpdb->get_var("SELECT COUNT(*) FROM $suppliers_table WHERE is_active = 1");

        $low_stock_count = count(wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'stock_status' => 'instock',
            'meta_query' => [
                [
                    'key' => '_stock',
                    'value' => 10,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
            ],
            'return' => 'ids'
        ]));
        
        $out_of_stock_count = count(wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'stock_status' => 'outofstock',
            'return' => 'ids'
        ]));

        return [
            'products_needing_attention' => (int) $products_needing_attention,
            'total_suppliers_configured' => (int) $total_suppliers,
            'low_stock_products_count' => (int) $low_stock_count,
            'out_of_stock_products_count' => (int) $out_of_stock_count,
            'current_date' => gmdate('Y-m-d')
        ];
    }

    /**
     * Get high risk products
     *
     * @param int $limit
     * @return array
     */
    public function get_stock_risk_products($limit = 5)
    {
        global $wpdb;
        $predictions_table = $wpdb->prefix . 'fbs_stockmind_predictions';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, predicted_runout_date, days_until_runout, confidence_score 
             FROM $predictions_table 
             WHERE is_dismissed = 0 
             ORDER BY days_until_runout ASC 
             LIMIT %d",
            $limit
        ));

        $products = [];
        foreach ($results as $row) {
            $product = wc_get_product($row->product_id);
            if (!$product) continue;

            $products[] = [
                'name' => $product->get_name(),
                'sku' => $product->get_sku(),
                'current_stock' => $product->get_stock_quantity(),
                'predicted_runout_date' => $row->predicted_runout_date,
                'days_until_runout' => $row->days_until_runout,
                'ai_confidence_score' => $row->confidence_score,
                'supplier_name' => $this->get_supplier_name($row->product_id)
            ];
        }

        return $products;
    }

    /**
     * Get best selling products over X days
     *
     * @param int $days
     * @param int $limit
     * @return array
     */
    public function get_best_sellers($days = 30, $limit = 5)
    {
        global $wpdb;
        $sales_table = $wpdb->prefix . 'fbs_stockmind_daily_sales';
        $start_date = gmdate('Y-m-d', strtotime("-{$days} days"));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, SUM(units_sold) as total_sold, SUM(revenue) as total_revenue
             FROM $sales_table 
             WHERE date >= %s
             GROUP BY product_id 
             ORDER BY total_sold DESC 
             LIMIT %d",
            $start_date,
            $limit
        ));

        $products = [];
        foreach ($results as $row) {
            $product = wc_get_product($row->product_id);
            if (!$product) continue;

            $products[] = [
                'name' => $product->get_name(),
                'sku' => $product->get_sku(),
                'total_units_sold' => (int) $row->total_sold,
                'total_revenue' => (float) $row->total_revenue,
                'current_stock' => $product->get_stock_quantity()
            ];
        }

        return $products;
    }

    /**
     * Get product supplier name
     */
    private function get_supplier_name($product_id)
    {
        $supplier_id = fbs_stockmind_get_product_supplier($product_id);
        if (!$supplier_id) return 'No Supplier';

        global $wpdb;
        $suppliers_table = $wpdb->prefix . 'fbs_stockmind_suppliers';
        $name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $suppliers_table WHERE id = %d", $supplier_id));
        
        return $name ? $name : 'Unknown';
    }

    /**
     * List of available tools for OpenAI
     */
    public function get_openai_tools_definition()
    {
        $is_pro_active = function_exists('fbs_stockmind_pro_is_woocommerce_active');
        
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_inventory_summary',
                    'description' => 'Get a high-level summary of the store\'s current inventory health, including how many products need attention, total suppliers, and low/out-of-stock counts.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass() // Empty object required by OpenAI for no params
                    ]
                ]
            ]
        ];

        // Only give the advanced prediction/reporting tools if Pro is active
        if ($is_pro_active) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'get_stock_risk_products',
                    'description' => 'Get a list of products that are at high risk of running out of stock soon, powered by the AI prediction engine.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Number of products to return (default 5, max 20)'
                            ]
                        ]
                    ]
                ]
            ];
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'get_best_sellers',
                    'description' => 'Get the best selling products by volume over a specific time period.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'days' => [
                                'type' => 'integer',
                                'description' => 'Number of days to look back (e.g., 7, 30, 90)'
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Number of products to return'
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $tools;
    }
}
