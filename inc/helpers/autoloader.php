<?php
/**
 * Autoloader for FBS StockMind plugin
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');

/**
 * Autoloader function
 *
 * @param string $class_name The class name to load
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_autoloader($class_name)
{
    // Check if the class belongs to our namespace
    if (strpos($class_name, 'FBS_StockMind\\') !== 0) {
        return;
    }

    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is enabled
        error_log('[FBS StockMind Autoloader] Looking for class: ' . $class_name);
    }

    // Remove the namespace prefix
    $class_name = str_replace('FBS_StockMind\\', '', $class_name);
    
    // Convert namespace separators to directory separators
    $class_name = str_replace('\\', '/', $class_name);
    
    // Convert class name to lowercase and add class- prefix
    $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
    
    // Build the file path - use classes directory directly
    $file_path = FBS_STOCKMIND_DIR_PATH . '/inc/classes/' . $class_name . '/' . $file_name;
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is enabled
        error_log('[FBS StockMind Autoloader] Trying path: ' . $file_path);
    }
    
    // Check if file exists and include it
    if (file_exists($file_path)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is enabled
            error_log('[FBS StockMind Autoloader] Found file: ' . $file_path);
        }
        require_once $file_path;
        return;
    }
    
    // Try alternative path structure for some classes (without Inc prefix)
    $class_name_clean = str_replace('Inc/', '', $class_name);
    $alternative_path = FBS_STOCKMIND_DIR_PATH . '/inc/classes/' . $class_name_clean . '/' . $file_name;
    if (file_exists($alternative_path)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is enabled
            error_log('[FBS StockMind Autoloader] Found alternative file: ' . $alternative_path);
        }
        require_once $alternative_path;
        return;
    }
    
    // Try direct class file in classes directory
    $direct_path = FBS_STOCKMIND_DIR_PATH . '/inc/classes/' . $file_name;
    if (file_exists($direct_path)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is enabled
            error_log('[FBS StockMind Autoloader] Found direct file: ' . $direct_path);
        }
        require_once $direct_path;
        return;
    }
    
    // Debug logging for not found
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is enabled
        error_log('[FBS StockMind Autoloader] Class not found: ' . $class_name . ' (tried: ' . $file_path . ', ' . $alternative_path . ', ' . $direct_path . ')');
    }
}

// Register the autoloader
spl_autoload_register('fbs_stockmind_autoloader');
