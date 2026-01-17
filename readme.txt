=== FBS StockMind ===
Contributors: fazlebari
Tags: woocommerce, inventory, stock management, predictions, reminders, suppliers
Requires at least: 6.4.2
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium WooCommerce plugin that provides predictive low-stock alerts for store owners and smart replenishment reminders for customers.

== Description ==

FBS StockMind is a powerful WordPress plugin that provides predictive low-stock alerts for store owners and smart replenishment reminders for customers. Built with modern web technologies and following WordPress coding standards.

= Features =

* **AI-Powered Predictions**: Analyzes sales data from the last 90 days, calculates average daily sales rate, factors in supplier lead times, and updates predictions daily via cron job
* **Supplier Management**: Custom supplier management system with lead time configuration per supplier, contact information, notes, and product-supplier associations
* **Smart Customer Reminders**: Non-intrusive reminder forms on thank you pages, email notifications before predicted runout, configurable advance notice periods, and maximum reminder attempt limits
* **Modern Admin Interface**: Card-based dashboard design, real-time statistics and alerts, intuitive navigation and filters, toast-style notifications, and responsive design
* **Advanced Settings**: Configurable alert windows, email customization options, feature toggles, prediction accuracy controls, and reminder management settings

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/fbs-stockmind/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure WooCommerce is installed and active
4. Navigate to StockMind in your admin menu to configure settings

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, FBS StockMind requires WooCommerce to be installed and active.

= What version of WordPress is required? =

WordPress 6.4.2 or higher is required.

= What version of PHP is required? =

PHP 7.4 or higher is required.

= How do I configure suppliers? =

Go to StockMind > Suppliers and add your suppliers with lead times. You can then assign suppliers to products in the product edit screen.

= How are predictions calculated? =

Predictions are calculated daily via cron job. The plugin analyzes sales data from the last 90 days, calculates average daily sales rate, and factors in supplier lead times.

== Screenshots ==

1. Dashboard view showing predictions and statistics
2. Predictions list with confidence scores
3. Supplier management interface
4. Settings configuration page
5. Customer reminder form on order completion page

== Changelog ==

= 1.0.0 =
* Initial release
* AI-powered stock predictions
* Supplier management system
* Customer reminder functionality
* Modern admin interface
* Comprehensive settings panel

== Upgrade Notice ==

= 1.0.0 =
Initial release of FBS StockMind.

== Support ==

For support, feature requests, or bug reports, please contact:
* Author: Fazle Bari
* Email: fazlebarisn@gmail.com
* Website: https://www.cansoft.com/

== Credits ==

Built with modern web technologies, follows WordPress coding standards, uses WordPress best practices, responsive design principles, and accessibility considerations.
