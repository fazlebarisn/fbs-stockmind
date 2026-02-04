<?php
/**
 * Helper functions for FBS StockMind plugin
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');

/**
 * Get plugin option with default value
 *
 * @param string $option_name The option name
 * @param mixed  $default     Default value if option doesn't exist
 * @return mixed
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_get_option($option_name, $default = false)
{
    return get_option('fbs_stockmind_' . $option_name, $default);
}

/**
 * Update plugin option
 *
 * @param string $option_name The option name
 * @param mixed  $value       The value to store
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_update_option($option_name, $value)
{
    return update_option('fbs_stockmind_' . $option_name, $value);
}

/**
 * Delete plugin option
 *
 * @param string $option_name The option name
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_delete_option($option_name)
{
    return delete_option('fbs_stockmind_' . $option_name);
}

/**
 * Get database table name with WordPress prefix
 *
 * @param string $table_name The table name without prefix
 * @return string
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_get_table_name($table_name)
{
    global $wpdb;
    return $wpdb->prefix . 'fbs_stockmind_' . $table_name;
}

/**
 * Check if current user can manage stockmind
 *
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_can_manage()
{
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

/**
 * Sanitize and validate product ID
 *
 * @param mixed $product_id The product ID to validate
 * @return int|false
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_validate_product_id($product_id)
{
    $product_id = absint($product_id);
    if ($product_id <= 0) {
        return false;
    }
    
    $product = wc_get_product($product_id);
    return $product ? $product_id : false;
}

/**
 * Get product stock quantity
 *
 * @param int $product_id The product ID
 * @return int
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_get_product_stock($product_id)
{
    $product = wc_get_product($product_id);
    if (!$product) {
        return 0;
    }
    
    if ($product->is_type('variable')) {
        $total_stock = 0;
        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);
            if ($variation) {
                // For variable products, get stock quantity regardless of managing_stock
                $stock_qty = $variation->get_stock_quantity();
                if ($stock_qty !== null) {
                    $total_stock += $stock_qty;
                }
            }
        }
        return $total_stock;
    }
    
    // For simple products, get stock quantity regardless of managing_stock
    $stock_qty = $product->get_stock_quantity();
    return $stock_qty !== null ? $stock_qty : 0;
}

/**
 * Check if product is replenishable
 *
 * @param int $product_id The product ID
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_is_product_replenishable($product_id)
{
    return (bool) get_post_meta($product_id, '_fbs_stockmind_replenishable', true);
}

/**
 * Set product as replenishable
 *
 * @param int  $product_id The product ID
 * @param bool $replenishable Whether the product is replenishable
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_set_product_replenishable($product_id, $replenishable = true)
{
    return update_post_meta($product_id, '_fbs_stockmind_replenishable', $replenishable);
}

/**
 * Get supplier ID for a product
 *
 * @param int $product_id The product ID
 * @return int|false
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_get_product_supplier($product_id)
{
    $supplier_id = get_post_meta($product_id, '_fbs_stock_supplier_id', true);
    return $supplier_id ? absint($supplier_id) : false;
}

/**
 * Set supplier for a product
 *
 * @param int $product_id   The product ID
 * @param int $supplier_id  The supplier ID
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_set_product_supplier($product_id, $supplier_id)
{
    return update_post_meta($product_id, '_fbs_stock_supplier_id', absint($supplier_id));
}

/**
 * Format date for display
 *
 * @param string $date The date string
 * @param string $format The date format
 * @return string
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_format_date($date, $format = 'M j, Y')
{
    if (empty($date)) {
        return __('Never', 'fbs-stockmind');
    }
    
    return date_i18n($format, strtotime($date));
}

/**
 * Get time ago string
 *
 * @param string $date The date string
 * @return string
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_time_ago($date)
{
    if (empty($date)) {
        return __('Never', 'fbs-stockmind');
    }
    
    return human_time_diff(strtotime($date), current_time('timestamp')) . ' ' . __('ago', 'fbs-stockmind');
}

/**
 * Trigger database migration for existing installations
 *
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_trigger_migration()
{
    \FBS_StockMind\Inc\Activate::trigger_migration();
}

/**
 * Log debug message (no-op in production; kept for API compatibility)
 *
 * @param string $message The debug message
 * @param string $level   The log level
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_log($message, $level = 'info')
{
    // No-op: debug logging disabled.
}

/**
 * Check if product has stock tracking enabled
 *
 * @param WC_Product|int $product The product object or product ID
 * @return bool
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_is_product_stock_tracked($product)
{
    // If product ID is passed, get the product object
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    
    if (!$product) {
        return false;
    }

    // For simple products, check if stock management is enabled
    if ($product->is_type('simple')) {
        return $product->managing_stock();
    }

    // For variable products, check if any variation has stock management enabled
    if ($product->is_type('variable')) {
        $variations = $product->get_children();
        foreach ($variations as $variation_id) {
            $variation = wc_get_product($variation_id);
            if ($variation && $variation->managing_stock()) {
                return true; // At least one variation has stock tracking
            }
        }
        return false; // No variations have stock tracking
    }

    // For other product types, check if stock management is enabled
    return $product->managing_stock();
}