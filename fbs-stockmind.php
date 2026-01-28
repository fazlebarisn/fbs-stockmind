<?php
/**
 * Plugin Name:       FBS StockMind
 * Description:       Premium WooCommerce plugin that provides predictive low-stock alerts for store owners and smart replenishment reminders for customers.
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Fazle Bari
 * Author URI:        https://github.com/fazlebarisn/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fbs-stockmind
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * @package           fbs-stockmind
 * @author            Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');

// Define plugin constants
if (!defined('FBS_STOCKMIND_DIR_PATH')) {
    define('FBS_STOCKMIND_DIR_PATH', __DIR__);
}

define('FBS_STOCKMIND_FILE', __FILE__);
define('FBS_STOCKMIND_URL', plugins_url('', FBS_STOCKMIND_FILE));
define('FBS_STOCKMIND_BASENAME', plugin_basename(__FILE__));
define('FBS_STOCKMIND_VERSION', '1.0.0');

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
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
function activate_fbs_stockmind()
{
    FBS_StockMind\Inc\Activate::get_instance();
}
register_activation_hook(__FILE__, 'activate_fbs_stockmind');

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */
function deactivate_fbs_stockmind()
{
    FBS_StockMind\Inc\Deactivate::get_instance();
}
register_deactivation_hook(__FILE__, 'deactivate_fbs_stockmind');

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
