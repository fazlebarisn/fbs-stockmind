<?php
/**
 * Predictive inventory logic class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Predictor
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
        // Add product meta boxes
        add_action('add_meta_boxes', [$this, 'add_product_meta_boxes']);
        add_action('save_post', [$this, 'save_product_meta']);
    }

    /**
     * Add product meta boxes
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function add_product_meta_boxes()
    {
        add_meta_box(
            'fbs_stockmind_product_settings',
            __('StockMind Settings', 'fbs-stockmind'),
            [$this, 'render_product_meta_box'],
            'product',
            'side',
            'default'
        );
    }

    /**
     * Render product meta box
     *
     * @param WP_Post $post The post object
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_product_meta_box($post)
    {
        wp_nonce_field('fbs_stockmind_product_meta', 'fbs_stockmind_product_meta_nonce');
        
        $is_replenishable = fbs_stockmind_is_product_replenishable($post->ID);
        $supplier_id = fbs_stockmind_get_product_supplier($post->ID);
        $suppliers = $this->get_all_suppliers();
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/product-meta-box.php';
    }

    /**
     * Save product meta
     *
     * @param int $post_id The post ID
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function save_product_meta($post_id)
    {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check if this is a revision
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Check if this is the correct post type
        if (get_post_type($post_id) !== 'product') {
            return;
        }

        // Check nonce
        if (!isset($_POST['fbs_stockmind_product_meta_nonce']) || 
            !wp_verify_nonce($_POST['fbs_stockmind_product_meta_nonce'], 'fbs_stockmind_product_meta')) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save replenishable setting
        $is_replenishable = isset($_POST['fbs_stockmind_replenishable']);
        fbs_stockmind_set_product_replenishable($post_id, $is_replenishable);

        // Save supplier
        if (isset($_POST['fbs_stockmind_supplier_id'])) {
            $supplier_id = absint($_POST['fbs_stockmind_supplier_id']);
            if ($supplier_id > 0) {
                fbs_stockmind_set_product_supplier($post_id, $supplier_id);
            } else {
                delete_post_meta($post_id, '_fbs_stockmind_supplier_id');
            }
        }
    }

    /**
     * Calculate runout date for a product
     *
     * @param int $product_id The product ID
     * @return array|false Array with prediction data or false if calculation fails
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function calculate_runout_date($product_id)
    {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }

        // Skip products that don't have stock tracking enabled
        if (!fbs_stockmind_is_product_stock_tracked($product)) {
            return false;
        }

        // Get current stock - product must have stock tracking enabled at this point
        $current_stock = $product->get_stock_quantity();
        
        // Handle null stock - don't create predictions for products with null stock
        if ($current_stock === null) {
            return false; // Don't create predictions for products with null stock
        }
        
        // Handle out-of-stock products (0 or negative stock)
        if ($current_stock <= 0) {
            // Create a special prediction for out-of-stock products
            return [
                'predicted_date' => date('Y-m-d'), // Today
                'confidence_score' => 1.0, // 100% confidence - we know it's out of stock
                'days_until_runout' => 0, // 0 days = already out of stock
                'average_daily_sales' => 0,
                'lead_time' => 0,
                'data_points' => 0,
                'is_out_of_stock' => true // Flag to identify out-of-stock products
            ];
        }

        // Get sales data period - check saved option first, then use filter (default 30 days for free)
        $saved_period = fbs_stockmind_get_option('sales_data_period', 0);
        if ($saved_period > 0) {
            // Use saved value if it exists and is valid
            $sales_data_period = $saved_period;
        } else {
            // Otherwise use filter (allows pro to override default)
            $sales_data_period = apply_filters('fbs_stockmind_sales_data_period', 30, $product_id);
        }
        $sales_data = $this->get_product_sales_data($product_id, $sales_data_period);
        if (empty($sales_data)) {
            return false;
        }

        // Calculate average daily sales
        $total_sales = array_sum($sales_data);
        $days_with_sales = count(array_filter($sales_data));
        $average_daily_sales = $days_with_sales > 0 ? $total_sales / $days_with_sales : 0;

        if ($average_daily_sales <= 0) {
            return false;
        }

        // Calculate prediction confidence - allow pro to override with advanced algorithm
        $confidence_score = apply_filters(
            'fbs_stockmind_calculate_confidence',
            $this->calculate_prediction_confidence($product_id, $sales_data),
            $product_id,
            $sales_data
        );
        
        // Get accuracy threshold setting - allow pro to override
        $default_threshold = apply_filters('fbs_stockmind_default_accuracy_threshold', 0.6);
        $accuracy_threshold = apply_filters(
            'fbs_stockmind_prediction_accuracy_threshold',
            fbs_stockmind_get_option('prediction_accuracy_threshold', $default_threshold),
            $product_id
        );
        
        // Check if confidence meets threshold
        if ($confidence_score < $accuracy_threshold) {
            return false; // Prediction not reliable enough
        }

        // Get lead time
        $lead_time = $this->get_product_lead_time($product_id);

        // Calculate predicted runout date
        $days_until_runout = $current_stock / $average_daily_sales;
        
        // Round to whole days to avoid strtotime issues with decimals
        $days_until_runout = round($days_until_runout);
        
        // Ensure minimum of 1 day
        if ($days_until_runout < 1) {
            $days_until_runout = 1;
        }
        
        // Calculate the predicted runout date (from today)
        $predicted_runout_date = date('Y-m-d', strtotime("+{$days_until_runout} days"));
        
        // Adjust for lead time - but don't go into the past
        // If lead time is greater than days until runout, set to minimum 1 day
        if ($lead_time >= $days_until_runout) {
            $adjusted_date = date('Y-m-d', strtotime('+1 day')); // Tomorrow
            $actual_days_until_runout = 1;
        } else {
            $adjusted_date = date('Y-m-d', strtotime("{$predicted_runout_date} -{$lead_time} days"));
            $actual_days_until_runout = (strtotime($adjusted_date) - time()) / DAY_IN_SECONDS;
        }
        
        // CRITICAL FIX: Ensure we never have negative days or past dates
        // If the adjusted date is in the past, it means the product is critical
        if ($actual_days_until_runout <= 0) {
            $adjusted_date = date('Y-m-d', strtotime('+1 day')); // Tomorrow
            $actual_days_until_runout = 1; // 1 day until runout
        }
        
        // Additional safety check: ensure the date is not in the past
        if (strtotime($adjusted_date) < time()) {
            $adjusted_date = date('Y-m-d', strtotime('+1 day')); // Tomorrow
            $actual_days_until_runout = 1; // 1 day until runout
        }

        return [
            'predicted_date' => $adjusted_date,
            'confidence_score' => $confidence_score,
            'days_until_runout' => $actual_days_until_runout,
            'average_daily_sales' => $average_daily_sales,
            'lead_time' => $lead_time,
            'data_points' => $days_with_sales
        ];
    }

    /**
     * Calculate prediction confidence score
     *
     * @param int $product_id The product ID
     * @param array $sales_data Array of daily sales data
     * @return float Confidence score between 0.0 and 1.0
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function calculate_prediction_confidence($product_id, $sales_data)
    {
        // Data Volume Score (0-1)
        $data_volume_score = $this->get_data_volume_score($sales_data);
        
        // Consistency Score (0-1) - based on sales variance
        $consistency_score = $this->get_consistency_score($sales_data);
        
        // Recency Score (0-1) - based on recent activity
        $recency_score = $this->get_recency_score($sales_data);
        
        // Product Type Score (0-1) - based on product characteristics
        $product_type_score = $this->get_product_type_score($product_id);
        
        // Calculate weighted average
        $confidence = ($data_volume_score * 0.3) + 
                     ($consistency_score * 0.3) + 
                     ($recency_score * 0.25) + 
                     ($product_type_score * 0.15);
        
        return round($confidence, 2);
    }

    /**
     * Get data volume score based on available sales data
     *
     * @param array $sales_data Array of daily sales data
     * @return float Score between 0.0 and 1.0
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_data_volume_score($sales_data)
    {
        $days_with_data = count(array_filter($sales_data));
        
        if ($days_with_data >= 60) return 1.0;
        if ($days_with_data >= 45) return 0.9;
        if ($days_with_data >= 30) return 0.7;
        if ($days_with_data >= 20) return 0.5;
        if ($days_with_data >= 10) return 0.3;
        if ($days_with_data >= 5) return 0.2;
        
        return 0.1;
    }

    /**
     * Get consistency score based on sales variance
     *
     * @param array $sales_data Array of daily sales data
     * @return float Score between 0.0 and 1.0
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_consistency_score($sales_data)
    {
        $sales_values = array_filter($sales_data);
        
        if (count($sales_values) < 2) {
            return 0.3; // Not enough data for variance calculation
        }
        
        $mean = array_sum($sales_values) / count($sales_values);
        $variance = 0;
        
        foreach ($sales_values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        $variance = $variance / count($sales_values);
        $coefficient_of_variation = $mean > 0 ? sqrt($variance) / $mean : 1;
        
        // Lower coefficient of variation = higher consistency
        if ($coefficient_of_variation <= 0.3) return 1.0;
        if ($coefficient_of_variation <= 0.5) return 0.8;
        if ($coefficient_of_variation <= 0.7) return 0.6;
        if ($coefficient_of_variation <= 1.0) return 0.4;
        
        return 0.2;
    }

    /**
     * Get recency score based on recent sales activity
     *
     * @param array $sales_data Array of daily sales data
     * @return float Score between 0.0 and 1.0
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_recency_score($sales_data)
    {
        $recent_days = 30;
        $recent_sales = 0;
        $current_date = new \DateTime();
        
        for ($i = 0; $i < $recent_days; $i++) {
            $date = $current_date->format('Y-m-d');
            if (isset($sales_data[$date]) && $sales_data[$date] > 0) {
                $recent_sales += $sales_data[$date];
            }
            $current_date->sub(new \DateInterval('P1D'));
        }
        
        // Score based on recent sales volume
        if ($recent_sales >= 50) return 1.0;
        if ($recent_sales >= 30) return 0.9;
        if ($recent_sales >= 20) return 0.8;
        if ($recent_sales >= 10) return 0.6;
        if ($recent_sales >= 5) return 0.4;
        if ($recent_sales >= 1) return 0.3;
        
        return 0.1;
    }

    /**
     * Get product type score based on product characteristics
     *
     * @param int $product_id The product ID
     * @return float Score between 0.0 and 1.0
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_product_type_score($product_id)
    {
        $product = wc_get_product($product_id);
        if (!$product) return 0.5;
        
        $score = 0.5; // Base score
        
        // Check if product is replenishable
        if (fbs_stockmind_is_product_replenishable($product_id)) {
            $score += 0.2; // Replenishable products are more predictable
        }
        
        // Check product type
        if ($product->is_type('simple')) {
            $score += 0.1; // Simple products are more predictable
        } elseif ($product->is_type('variable')) {
            $score -= 0.1; // Variable products are less predictable
        }
        
        // Check if product has supplier
        if (fbs_stockmind_get_product_supplier($product_id)) {
            $score += 0.1; // Products with suppliers are more predictable
        }
        
        // Check stock management
        if ($product->managing_stock()) {
            $score += 0.1; // Stock-managed products are more predictable
        }
        
        return min(1.0, max(0.0, $score));
    }

    /**
     * Get product sales data for a specific period
     *
     * @param int $product_id The product ID
     * @param int $days Number of days to look back
     * @return array Array of daily sales quantities
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_product_sales_data($product_id, $days = 90)
    {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        // Get all completed orders from the start date
        $orders = wc_get_orders(array(
            'limit' => -1,
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => '>=' . $start_date,
            'orderby' => 'date',
            'order' => 'ASC',
        ));

        // Initialize sales data array with all dates set to 0
        $sales_data = [];
        $current_date = new \DateTime($start_date);
        $end_date = new \DateTime();

        while ($current_date <= $end_date) {
            $date_str = $current_date->format('Y-m-d');
            $sales_data[$date_str] = 0;
            $current_date->add(new \DateInterval('P1D'));
        }

        // Process each order to extract sales data for the specific product
        foreach ($orders as $order) {
            $order_date = $order->get_date_created()->date('Y-m-d');
            
            /** @var WC_Order_Item_Product $item */
            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() == $product_id) {
                    $quantity = $item->get_quantity();
                    if (isset($sales_data[$order_date])) {
                        $sales_data[$order_date] += $quantity;
                    }
                }
            }
        }

        return $sales_data;
    }

    /**
     * Get lead time for a product
     *
     * @param int $product_id The product ID
     * @return int Lead time in days
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_product_lead_time($product_id)
    {
        $supplier_id = fbs_stockmind_get_product_supplier($product_id);
        
        if ($supplier_id) {
            global $wpdb;
            $suppliers_table = fbs_stockmind_get_table_name('suppliers');
            $lead_time = $wpdb->get_var($wpdb->prepare(
                "SELECT lead_time FROM $suppliers_table WHERE id = %d AND is_active = 1",
                $supplier_id
            ));
            
            if ($lead_time) {
                return (int) $lead_time;
            }
        }

        return fbs_stockmind_get_option('default_lead_time', 7);
    }

    /**
     * Calculate predictions for all products
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function calculate_all_predictions()
    {
        global $wpdb;

        // Clear ALL existing predictions to start fresh
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        $wpdb->query("DELETE FROM $predictions_table WHERE is_dismissed = 0");
        $wpdb->query("DELETE FROM $predictions_table WHERE is_dismissed = 1");

        // Get all published products
        // Note: We can't filter by manage_stock in wc_get_products for variable products,
        // so we'll check each product individually
        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish',
        ]);

        $predictions_table = fbs_stockmind_get_table_name('predictions');
        $alert_window = fbs_stockmind_get_option('alert_window', 14);
        $predictions_created = 0;

        foreach ($products as $product) {
            $product_id = $product->get_id();
            
            // Double check stock tracking is enabled (for variable products, check variations)
            if (!fbs_stockmind_is_product_stock_tracked($product)) {
                // Remove any existing prediction if stock tracking is disabled
                $wpdb->delete(
                    $predictions_table,
                    ['product_id' => $product_id, 'is_dismissed' => 0],
                    ['%d', '%d']
                );
                continue;
            }
            
            try {
                $prediction_data = $this->calculate_runout_date($product_id);
            } catch (\Exception $e) {
                continue;
            }

            if (!$prediction_data) {
                // Remove any existing prediction if no new data
                $wpdb->delete(
                    $predictions_table,
                    ['product_id' => $product_id, 'is_dismissed' => 0],
                    ['%d', '%d']
                );
                continue;
            }

            $predicted_date = $prediction_data['predicted_date'];
            $confidence_score = $prediction_data['confidence_score'];
            $days_until_runout = $prediction_data['days_until_runout'];

            // Check if prediction is within alert window
            
            if ($days_until_runout <= $alert_window) {
                // Insert or update prediction
                $existing = $wpdb->get_row($wpdb->prepare(
                    "SELECT id FROM $predictions_table WHERE product_id = %d AND is_dismissed = 0",
                    $product_id
                ));

                if ($existing) {
                    $wpdb->update(
                        $predictions_table,
                        [
                            'predicted_runout_date' => $predicted_date,
                            'days_until_runout' => $days_until_runout,
                            'confidence_score' => $confidence_score,
                            'calculated_at' => current_time('mysql'),
                        ],
                        ['id' => $existing->id],
                        ['%s', '%f', '%f', '%s'],
                        ['%d']
                    );
                    $predictions_created++;
                } else {
                    $wpdb->insert(
                        $predictions_table,
                        [
                            'product_id' => $product_id,
                            'predicted_runout_date' => $predicted_date,
                            'days_until_runout' => $days_until_runout,
                            'confidence_score' => $confidence_score,
                            'calculated_at' => current_time('mysql'),
                            'is_dismissed' => 0,
                        ],
                        ['%d', '%s', '%f', '%f', '%s', '%d']
                    );
                    $predictions_created++;
                }
            } else {
                // Remove prediction if it's outside alert window
                $wpdb->delete(
                    $predictions_table,
                    ['product_id' => $product_id, 'is_dismissed' => 0],
                    ['%d', '%d']
                );
            }
        }
    }

    /**
     * Clean up predictions with dates in the past
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function cleanup_expired_predictions()
    {
        global $wpdb;
        
        $predictions_table = fbs_stockmind_get_table_name('predictions');
        
        // Remove predictions that are more than 7 days past their predicted date
        // This allows for critical products (0 days) to still show
        $wpdb->query(
            "DELETE FROM $predictions_table 
             WHERE is_dismissed = 0 
             AND predicted_runout_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );
    }

    /**
     * Get active predictions
     *
     * @param int $limit Number of predictions to retrieve
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_active_predictions($limit = -1)
    {
        global $wpdb;

        $predictions_table = fbs_stockmind_get_table_name('predictions');
        
        $limit_clause = $limit > 0 ? $wpdb->prepare("LIMIT %d", $limit) : '';
        
        $results = $wpdb->get_results(
            "SELECT p.*
             FROM $predictions_table p
             WHERE p.is_dismissed = 0 
             ORDER BY p.confidence_score DESC, p.predicted_runout_date ASC
             $limit_clause"
        );

        $predictions = [];
        foreach ($results as $result) {
            $product = wc_get_product($result->product_id);
            if (!$product || $product->get_status() !== 'publish') {
                continue;
            }

            // Skip products that don't have stock tracking enabled
            if (!fbs_stockmind_is_product_stock_tracked($product)) {
                // Remove prediction if stock tracking is disabled
                $wpdb->delete(
                    $predictions_table,
                    ['id' => $result->id],
                    ['%d']
                );
                continue;
            }

            $predictions[] = [
                'id' => $result->id,
                'product_id' => $result->product_id,
                'product_name' => $product->get_name(),
                'product_image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'current_stock' => $product->get_stock_quantity(),
                'predicted_runout_date' => $result->predicted_runout_date,
                'confidence_score' => $result->confidence_score,
                'calculated_at' => $result->calculated_at,
                'days_until_runout' => $result->days_until_runout,
            ];
        }

        return $predictions;
    }
    

    /**
     * Dismiss a prediction
     *
     * @param int $prediction_id The prediction ID
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function dismiss_prediction($prediction_id)
    {
        global $wpdb;

        $predictions_table = fbs_stockmind_get_table_name('predictions');
        
        $result = $wpdb->update(
            $predictions_table,
            [
                'is_dismissed' => 1,
                'dismissed_at' => current_time('mysql'),
                'dismissed_by' => get_current_user_id(),
            ],
            ['id' => $prediction_id],
            ['%d', '%s', '%d'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Handle AJAX dismiss prediction
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_dismiss_prediction()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'fbs-stockmind'));
        }

        $prediction_id = absint($_POST['prediction_id'] ?? 0);
        
        if (!$prediction_id) {
            wp_send_json_error(__('Invalid prediction ID.', 'fbs-stockmind'));
        }

        if ($this->dismiss_prediction($prediction_id)) {
            wp_send_json_success(__('Prediction dismissed successfully.', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Failed to dismiss prediction.', 'fbs-stockmind'));
        }
    }


    /**
     * Get confidence level for display
     *
     * @param float $confidence_score The confidence score
     * @return string The confidence level class
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_confidence_level($confidence_score)
    {
        if ($confidence_score >= 0.8) return 'high';
        if ($confidence_score >= 0.6) return 'medium';
        if ($confidence_score >= 0.4) return 'low';
        return 'very-low';
    }

    /**
     * Get all suppliers
     *
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_all_suppliers()
    {
        global $wpdb;
        
        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        
        $results = $wpdb->get_results(
            "SELECT * FROM $suppliers_table WHERE is_active = 1 ORDER BY name ASC"
        );

        return $results;
    }
}
