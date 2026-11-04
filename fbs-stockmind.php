<?php
/**
 * Plugin Name:       FBS StockMind – AI Inventory Management, Predictive Stock Alerts & Reorder Reminders for WooCommerce
 * Plugin URI:        https://fbsfusion.com/plugins/
 * Description:       Prevent stockouts and drive repeat sales with AI inventory forecasting, supplier purchase order alerts, conversational AI assistant, and automated customer replenishment reminders for WooCommerce.
 * Version:           1.3.0
 * Requires at least: 6.4
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Author:            Fazle Bari
 * Author URI:        https://fbsfusion.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fbs-stockmind
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * WC requires at least: 6.0
 * WC tested up to:   11.1.0
 *
 * @package           fbs-stockmind
 * @author            Fazle Bari <fazle@fbsfusion.com>
 */

defined('ABSPATH') or die('Nice Try!');

// Define plugin constants
if (!defined('FBS_STOCKMIND_DIR_PATH')) {
    define('FBS_STOCKMIND_DIR_PATH', __DIR__);
}

define('FBS_STOCKMIND_FILE', __FILE__);
define('FBS_STOCKMIND_URL', plugins_url('', FBS_STOCKMIND_FILE));
define('FBS_STOCKMIND_BASENAME', plugin_basename(__FILE__));
define('FBS_STOCKMIND_VERSION', '1.3.0');

// Declare compatibility with WooCommerce High-Performance Order Storage (HPOS) & Cart/Checkout Blocks
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', FBS_STOCKMIND_FILE, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', FBS_STOCKMIND_FILE, true);
    }
});

// Check if WooCommerce is active (single-site and multisite network-activated)
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
$fbs_stockmind_active_plugins = (array) apply_filters('active_plugins', get_option('active_plugins', []));
if (is_multisite()) {
    $fbs_stockmind_network_plugins = array_keys((array) get_site_option('active_sitewide_plugins', []));
    $fbs_stockmind_active_plugins  = array_merge($fbs_stockmind_active_plugins, $fbs_stockmind_network_plugins);
}

if (!in_array('woocommerce/woocommerce.php', $fbs_stockmind_active_plugins, true)) {
    add_action('admin_notices', 'fbs_stockmind_woocommerce_missing_notice');
    return;
}

/**
 * Display admin notice if WooCommerce is not active
 *
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_woocommerce_missing_notice()
{
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e('FBS StockMind', 'fbs-stockmind'); ?></strong> 
            <?php esc_html_e('requires WooCommerce to be installed and active.', 'fbs-stockmind'); ?>
        </p>
    </div>
    <?php
}

// Load autoloader and main classes
require_once FBS_STOCKMIND_DIR_PATH . '/inc/helpers/autoloader.php';
require_once FBS_STOCKMIND_DIR_PATH . '/inc/functions.php';

// Manually include required classes to avoid autoloader issues
require_once FBS_STOCKMIND_DIR_PATH . '/inc/traits/singleton.php';
require_once FBS_STOCKMIND_DIR_PATH . '/inc/classes/activate/class-activate.php';
require_once FBS_STOCKMIND_DIR_PATH . '/inc/classes/deactivate/class-deactivate.php';
require_once FBS_STOCKMIND_DIR_PATH . '/inc/classes/fbs-stockmind/class-fbs-stockmind.php';
require_once FBS_STOCKMIND_DIR_PATH . '/inc/classes/features/class-supplier-manager.php';

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_activate()
{
    FBS_StockMind\Inc\Activate::get_instance();
}
register_activation_hook(__FILE__, 'fbs_stockmind_activate');

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_deactivate()
{
    FBS_StockMind\Inc\Deactivate::get_instance();
}
register_deactivation_hook(__FILE__, 'fbs_stockmind_deactivate');

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function fbs_stockmind_init()
{
    FBS_StockMind\Inc\FBS_StockMind::get_instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'fbs_stockmind_init');
