=== FBS StockMind ===
Contributors: fazlebari
Tags: woocommerce, inventory, stock management, predictions, reminders, stock alerts
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium WooCommerce plugin that provides predictive low-stock alerts for store owners and smart replenishment reminders for customers.

== Description ==

FBS StockMind is a powerful WordPress plugin that provides predictive low-stock alerts for store owners and smart replenishment reminders for customers. By accurately analyzing your sales velocity and inventory levels, StockMind ensures you never run out of your best-selling items, while simultaneously offering a seamless way to invite repeat buyers back to your store.

= Core Features =

* **AI-Powered Predictions**: The free version intelligently analyzes your store's sales data from the last 30 days. It calculates average daily sales rates and provides mathematically precise predictions of when each product will run out of stock.
* **Onboarding Wizard & Auto-Detect**: Getting started is incredibly fast. Upon activation, an automated wizard can instantly scan your past orders and automatically mark your most frequently purchased products as "replenishable".
* **Dashboard Widget**: Keep track of the most critical stock alerts directly from your main WordPress Dashboard without having to navigate into the plugin settings.
* **Smart Customer Reminders**: Add a non-intrusive reminder form to your WooCommerce Thank You pages, or embed it anywhere using the `[fbs_stockmind_reminder]` shortcode. Customers (and guest users!) can enter their email to receive a timely reminder to reorder exactly when they are likely running low.
* **Modern Admin Interface**: Enjoy a beautiful, card-based dashboard design with real-time statistics, intuitive navigation, quick filters, and toast-style notifications.

= Go Further with FBS StockMind Pro =

Ready to fully automate your inventory? Upgrade to **FBS StockMind Pro** to unlock:
* **90 Days of Sales History**: Analyzes up to 90 days of data and automatically detects seasonal trends.
* **Auto-Purchase Orders**: Convert a low-stock prediction into a Draft Purchase Order with one click and email it directly to your supplier.
* **Unlimited Suppliers**: Advanced supplier performance tracking, individual lead times, and no limits (the free version limits you to 3 suppliers).
* **Revenue-Generating Reminders**: Attach unique discount coupons to the "Reorder Now" emails to incentivize immediate purchases.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/fbs-stockmind/` or install it directly via the WordPress Plugin directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure WooCommerce is installed and active.
4. Navigate to **StockMind** in your admin menu to view your dashboard and configure your settings.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, FBS StockMind requires WooCommerce to be installed and active.

= What version of WordPress is required? =

WordPress 6.4 or higher is required.

= What version of PHP is required? =

PHP 7.4 or higher is required.

= How do I configure suppliers? =

Go to StockMind > Suppliers and add your suppliers with their standard lead times. You can then assign suppliers to individual products in the product edit screen.

= How are predictions calculated? =

Predictions are calculated daily via a WordPress cron job. The plugin analyzes your recent sales data, calculates your average daily sales rate, and factors in supplier lead times to accurately predict the exact date a product will run out of stock.

== Screenshots ==

1. Dashboard view showing predictions and statistics
2. Recent Predictions and Upcoming Replenishments
3. Quick Actions
4. Stock Predictions
5. Manage suppliers
6. Manage customer replenishment reminders
7. StockMind general settings
8. StockMind Prediction settings
9. StockMind Customer Reminder settings
10. StockMind Email settings
11. Customer Reminder On Product Page.

== Changelog ==

= 1.1.0 =
* New: Automated Onboarding Wizard - quickly scan past orders and identify frequently bought items as replenishable with one click!
* New: Dashboard Widget - keep track of the most critical stock alerts right from your main WordPress dashboard.
* New: Anywhere Shortcode `[fbs_stockmind_reminder]` - embed the reminder form on any page or post. Now fully supports guest users!
* Tweak: Product titles are now clickable throughout the admin dashboard, taking you directly to the Edit Product screen.
* Tweak: Enforced accurate display of free tier limitations on the Settings page (30 days of data analysis).
* Fix: Code standards and escaping improvements in the dashboard widget and pro-features teaser.

= 1.0.2 =
* Initial release
* AI-powered stock predictions
* Supplier management system
* Customer reminder functionality
* Modern admin interface
* Comprehensive settings panel

== Upgrade Notice ==

= 1.0.1 =
This major update introduces an onboarding wizard, a dashboard widget, and the `[fbs_stockmind_reminder]` shortcode for guests. We highly recommend upgrading to take advantage of these new features!

= 1.0.0 =
Initial release of FBS StockMind.
