=== FBS StockMind – AI Inventory Management, Predictive Stock Alerts & Reorder Reminders for WooCommerce ===
Contributors: fazlebari
Tags: woocommerce, inventory management, stock alerts, ai assistant, replenishment
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered predictive inventory management, intelligent low-stock alerts, smart customer replenishment reminders, and an interactive AI Assistant for WooCommerce.

== Description ==

FBS StockMind is an intelligent inventory management plugin for WooCommerce that combines predictive stockout forecasting, automated customer replenishment reminders, and an interactive AI Assistant.

By analyzing historical sales velocity and real-time inventory levels, StockMind ensures you never run out of your best-selling items while keeping customer repurchase rates high.

= Core Features =

* **AI-Powered Stock Predictions**: Analyzes past 30 days of WooCommerce sales data, calculates average daily sales velocity, and predicts exact stock runout dates so you can replenish before running out.
* **Interactive AI Inventory Assistant**: Chat directly with an AI assistant that understands your catalog! Ask about stock levels, low-stock risks, recommended restock quantities, or store health summaries using your own OpenAI or Google Gemini API key.
* **Smart Function Calling**: When using OpenAI, the AI Assistant dynamically queries your actual store database (predictions, inventory counts, and suppliers) to provide factual, up-to-the-minute answers.
* **Persistent Chat Experience**: Your conversation with the AI Assistant persists across page navigations and reloads, allowing you to ask follow-up questions without losing context.
* **Real-time Recalculation & Self-Healing**: Predictions automatically refresh when orders are placed or stock levels change, with instant self-healing checks to prevent stale data.
* **Automated Onboarding Wizard**: Scans previous orders on activation and automatically marks your most frequently purchased items as "replenishable" with one click.
* **Smart Customer Replenishment Reminders**: Embed reminder opt-in forms on Thank You pages or anywhere via the `[fbs_stockmind_reminder]` shortcode. Customers and guests receive friendly email notifications when it's time to reorder.
* **Supplier Management**: Keep track of product suppliers, lead times, and contact details to ensure reorders are placed well before safety thresholds.
* **Dashboard Widget**: Monitor critical low-stock alerts and runout countdowns directly from your WordPress admin dashboard.
* **Modern & Intuitive Admin UI**: Enjoy a responsive, card-based interface with toast notifications, quick filters, and clean navigation.

= Go Further with FBS StockMind Pro =

Ready to fully automate your supply chain? Upgrade to **FBS StockMind Pro** to unlock:
* **90 Days of Sales History**: Deep historical trend analysis and seasonal demand tracking.
* **Customizable Prediction Thresholds**: Adjust prediction accuracy thresholds and analysis periods directly from settings.
* **Auto-Purchase Orders**: Generate Draft Purchase Orders with one click and email them straight to suppliers.
* **Unlimited Suppliers & Performance Metrics**: Remove the 3-supplier limit and track vendor fulfillment times.
* **Revenue-Generating Reminders**: Attach automatic discount coupons to "Reorder Now" emails to accelerate repeat sales.
* **Multi-Stage Reminder Sequences**: Schedule multiple follow-up reminders with custom timing intervals.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/fbs-stockmind/` or install it directly via the WordPress Plugin directory.
2. Toggle the plugin through the 'Plugins' screen in WordPress.
3. Ensure WooCommerce is installed and active.
4. Navigate to **StockMind** in your admin menu to view your dashboard and configure your settings.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, FBS StockMind requires WooCommerce 6.0+ to be installed and active.

= Does the AI Assistant require an API key? =

Yes. To use the built-in AI Assistant, add your free Google Gemini API key or OpenAI API key under **StockMind > Settings > AI Assistant**. Your API key is stored securely in your WordPress database and connects directly to the provider without any middleman server.

= What AI models are supported? =

The AI Assistant supports OpenAI models (such as GPT-4o and GPT-4o-mini) with function-calling support, as well as Google Gemini models (such as Gemini 2.5 Flash and Gemini 2.0 Flash).

= How are stock predictions calculated? =

Predictions are calculated using daily sales velocity, current stock levels, and configured supplier lead times. StockMind recalculates predictions automatically in real time whenever an order is placed or stock is updated, and performs a comprehensive daily recalculation via WP-Cron.

= How do customer replenishment reminders work? =

Customers can opt in to replenishment reminders on the order Thank You page or via the `[fbs_stockmind_reminder]` shortcode. When a predicted replenishment cycle arrives, StockMind automatically dispatches a friendly email reminder prompting them to reorder.

= Can I use replenishment reminders for guest customers? =

Yes! Both registered customers and guest shoppers can sign up for replenishment reminders using their email address.

= How do I configure suppliers? =

Go to StockMind > Suppliers and add your suppliers with their standard lead times. You can then assign suppliers to individual products in the product edit screen.

== Screenshots ==

1. StockMind Overview Dashboard with predictive alerts and sales statistics.
2. Interactive AI Assistant chat interface for real-time inventory queries.
3. AI Assistant Settings with OpenAI and Google Gemini configurations.
4. Stock Predictions list showing runout dates, days remaining, and confidence scores.
5. Supplier management screen with lead times and contact info.
6. Customer replenishment reminder manager.
7. General, prediction, and reminder configuration settings.
8. WordPress Dashboard widget showing urgent stock alerts.
9. Customer replenishment signup form on the WooCommerce Thank You page.

== Changelog ==

= 1.2.0 =
* New: Built-in AI Assistant supporting OpenAI (GPT-4o, GPT-4o-mini) and Google Gemini (Gemini 2.5 Flash, 2.0 Flash) models.
* New: Smart Function Calling for OpenAI allowing the AI Assistant to query live store data (low-stock counts, runout predictions, and supplier directories).
* New: Persistent chat conversation memory stored in the browser across page reloads and navigations.
* New: Dedicated AI Assistant settings tab with provider selection, custom model configuration, response tone controls, and live API connection testing.
* New: Real-time self-healing predictions that instantly recalculate runout dates whenever an order is completed or stock is adjusted.
* Tweak: Streamlined admin menu structure by removing redundant upsell navigation and embedding Pro Options directly inside Settings.
* Tweak: Added explanatory "How Predictions Work" reference guide directly in the Predictions interface.
* Tweak: Added direct cross-navigation between the AI Assistant chat screen and AI Assistant settings.
* Tweak: Verified compatibility and updated "Tested up to" header for WordPress 7.1.
* Fix: Addressed all WordPress.org Plugin Check (PCP) and PHPCS notices regarding prepared queries, dynamic table prefixes, unescaped database parameters, and input sanitization.

= 1.1.2 =
* New: Integrated "Our Plugins" portfolio showcase page.
* New: Integrated "Meet The Author" profile details page.
* Tweak: Redesigned the backend UI with modern aesthetics, clean drop shadows, and responsive grid layouts.

= 1.1.1 =
* Fix: Minor UI improvements and bug fixes.

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

= 1.2.0 =
Major update: Adds an interactive AI Assistant for your inventory, real-time self-healing predictions, and WordPress 7.1 compatibility.

= 1.1.0 =
Introduces the automated onboarding wizard, WordPress dashboard widget, and guest replenishment shortcode.
