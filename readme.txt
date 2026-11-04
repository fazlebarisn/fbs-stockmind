=== FBS StockMind – AI Inventory Management, Predictive Stock Alerts & Reorder Reminders for WooCommerce ===
Contributors: fazlebari
Tags: woocommerce, inventory management, stock alerts, ai assistant, replenishment
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI inventory forecasting, predictive low-stock alerts, smart customer reorder reminders, and an interactive AI Assistant for WooCommerce.

== Description ==

**FBS StockMind** (by [FBS Fusion](https://fbsfusion.com/)) is an intelligent inventory forecasting and customer replenishment engine for WooCommerce that combines predictive stockout alerts, automated reorder reminders, supplier lead-time tracking, and an interactive AI Inventory Assistant.

[youtube https://www.youtube.com/watch?v=Dkc_0F_MUes]

By analyzing real-time WooCommerce sales velocity (across both simple and variable products) alongside net refunds and supplier lead times, StockMind helps you restock best-sellers before they run out—while driving repeat purchases from existing customers.

= Core Features =

* **Predictive Stockout Forecasting**: Analyzes the past 30 days of WooCommerce order velocity (including variable product variations and net refunds) and predicts exact stock runout dates so you can reorder ahead of supplier lead times.
* **Interactive AI Inventory Assistant**: Chat directly with an AI assistant that understands your live catalog! Powered natively by **WordPress AI Client & Connectors** (`Settings ➔ Connectors`) — connect Google Gemini, OpenAI, Anthropic, Mistral, or Vercel AI Gateway once in WordPress and ask about low-stock risks, runout forecasts, best-sellers, or supplier lead times.
* **Live Store Inventory Context**: StockMind automatically feeds your live active predictions, stock-risk products, 30-day best-sellers, and supplier counts into your WordPress AI Connection for factual, up-to-the-minute answers.
* **Quick Prompt Chips & Persistent Chat**: Launch 1-click inventory audits with Quick Prompt chips and keep your conversation intact across page reloads.
* **Real-Time Self-Healing & HPOS Ready**: Fully compatible with WooCommerce High-Performance Order Storage (`custom_order_tables`) and Cart/Checkout Blocks. Predictions automatically refresh when orders or variations change.
* **Automated Onboarding Wizard**: Scans historical WooCommerce orders on activation and flags frequently purchased items as replenishable in one click.
* **Non-Intrusive Customer Replenishment Reminders**: Display clean inline reminder opt-in cards on WooCommerce Thank You pages or anywhere via the `[fbs_stockmind_reminder]` shortcode—complete with guest support and 1-click signed email unsubscribe links.
* **Supplier Lead-Time Directory**: Manage up to 3 suppliers in the Free edition with lead times and contact details linked directly to product predictions.
* **WordPress Dashboard Widget**: Monitor urgent low-stock countdowns right from your main WordPress Admin Dashboard.

= Go Further with FBS StockMind Pro =

Ready to automate your entire WooCommerce supply chain? Explore **[FBS StockMind Pro](https://fbsfusion.com/plugins/#fbs-stockmind)** to unlock:

* **Flexible 7–365 Day Sales Analysis Windows**: Account for seasonal demand spikes and long-term velocity trends.
* **Customizable Prediction Confidence Thresholds**: Fine-tune confidence thresholds (10%–100%) for large catalogs.
* **1-Click PDF & CSV Purchase Orders**: Generate branded Purchase Orders and email them directly to suppliers.
* **Automated PO Restock Triggers**: Draft purchase orders automatically the moment stock crosses lead-time thresholds.
* **Unlimited Suppliers & Vendor Directory**: Remove the 3-supplier limit and manage full vendor terms.
* **Visual Depletion Charts**: Interactive Chart.js depletion graphs with safety threshold lines.
* **Multi-Step Replenishment Sequences + Discount Coupons**: Send up to 10 automated follow-up reminders with dynamic WooCommerce coupon codes.
* **Twilio SMS & WhatsApp Urgent Alerts**: Get instant mobile notifications when high-velocity SKUs risk stockout.
* **Multi-Warehouse Stock Tracking**: Track velocity and runout dates across multiple fulfillment locations.

= More Official Plugins by FBS Fusion =

* **[Product FAQ for WooCommerce](https://wordpress.org/plugins/product-faq-for-woocommerce/)** – AI-generated product FAQs, Google `FAQPage` JSON-LD schema, and interactive accordions ([Pro Edition](https://fbsfusion.com/product-faq-for-woocommerce/)).
* **[FBS Secure Optimize](https://wordpress.org/plugins/fbs-secure-optimize/)** – All-in-one Core Web Vitals optimization, page caching, WAF firewall, and malware scanner.
* **[FBS Activity Tracker](https://wordpress.org/plugins/fbs-activity-tracker/)** – Real-time WordPress & WooCommerce security and admin activity audit log.

== External Services ==

FBS StockMind integrates exclusively with WordPress's native **AI Client API (`wp_ai_client_prompt`)** and **Settings ➔ Connectors**. The plugin does not make hardcoded requests to third-party AI endpoints on its own; AI requests are routed only when an administrator connects an AI provider in **WordPress Settings ➔ Connectors** and initiates an inventory chat query or connection test in the WordPress admin dashboard.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/fbs-stockmind/` or install directly via **Plugins ➔ Add New** in WordPress.
2. Activate **FBS StockMind** through the **Plugins** screen in WordPress.
3. Ensure **WooCommerce 6.0+** is installed and active.
4. Navigate to **StockMind** in your WordPress admin menu to view your dashboard, configure suppliers, and set up the optional AI Assistant.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, FBS StockMind requires WooCommerce 6.0 or higher and fully supports WooCommerce High-Performance Order Storage (HPOS).

= Does FBS StockMind work with Variable Products? =

Yes! StockMind aggregates variation stock levels (or parent-managed stock) and variation order history so variable products receive accurate runout predictions.

= Does the AI Assistant require a separate API key inside StockMind? =

No! StockMind uses WordPress's native **Settings ➔ Connectors** integration (`wp_ai_client_prompt`). Connect Google Gemini, OpenAI, Anthropic, or Mistral once in WordPress **Settings ➔ Connectors**, and StockMind AI Assistant works automatically.

= How do customer replenishment reminders work? =

Shoppers can opt in to replenishment reminders via the non-intrusive inline card on the WooCommerce Thank You page or anywhere you place the `[fbs_stockmind_reminder]` shortcode. When their predicted reorder window arrives, StockMind sends a friendly HTML reminder email with a 1-click unsubscribe link.

= Can guest customers subscribe to replenishment reminders? =

Yes! Both logged-in customers and guest shoppers can subscribe using their email address.

== Screenshots ==

1. StockMind Overview Dashboard with predictive alerts, KPI cards, and quick actions.
2. Interactive AI Inventory Assistant with Quick Prompt chips and live store context.
3. AI Assistant Settings supporting WordPress Native AI Connectors, Google Gemini, and OpenAI.
4. Stock Predictions list showing runout dates, days remaining, and confidence scores.
5. Supplier management directory with lead times and product assignments.
6. Customer replenishment reminder manager.
7. General, prediction, and reminder configuration settings.
8. WordPress Dashboard widget showing urgent stock alerts.
9. Non-intrusive inline replenishment signup card on the WooCommerce Thank You page.

== Changelog ==

= 1.3.0 =
* New: Switched AI Inventory Assistant exclusively to WordPress's native AI Client & Connectors (`wp_ai_client_prompt` / `Settings ➔ Connectors`), matching the unified WordPress AI Connection architecture.
* New: Redesigned AI Inventory Assistant UI with dark gradient header, active provider & daily quota badges, and 1-click Quick Prompt chips.
* New: Declared official WooCommerce High-Performance Order Storage (`custom_order_tables`) and Cart/Checkout Blocks compatibility.
* New: Added signed 1-click unsubscribe links to customer replenishment reminder emails.
* New: Converted the Thank You page reminder popup into a clean, non-blocking inline replenishment card.
* Tweak: Refreshed "Our Plugins", "Meet The Author", and FBS Fusion studio links and resources.
* Fix: Resolved variable product stock aggregation and variation sales velocity tracking in stock predictions.
* Fix: Prevented duplicate order sales recording across multiple WooCommerce order status transitions and deducted order refunds from net velocity.
* Fix: Eliminated time-of-day drift in supplier lead-time runout date calculations.
* Fix: Fixed PHP 8+ argument count error during recursive OpenAI function-calling responses.
* Fix: Hardened AJAX nonce checks, capability checks, email header sanitization, and DOM text escaping across admin and frontend scripts.

= 1.2.0 =
* New: Built-in AI Assistant supporting OpenAI and Google Gemini models.
* New: Smart Function Calling for OpenAI allowing the AI Assistant to query live store data.
* New: Persistent chat conversation memory stored in the browser across page reloads and navigations.
* New: Dedicated AI Assistant settings tab with provider selection, model configuration, response tone controls, and live API connection testing.
* New: Real-time self-healing predictions that recalculate runout dates whenever an order is completed or stock is adjusted.
* Tweak: Verified compatibility and updated "Tested up to" header for WordPress 7.1.
* Fix: Addressed WordPress.org Plugin Check (PCP) and PHPCS notices.

= 1.1.2 =
* New: Integrated "Our Plugins" portfolio showcase page and "Meet The Author" profile page.
* Tweak: Redesigned the backend UI with modern card layouts.

= 1.1.1 =
* Fix: Minor UI improvements and bug fixes.

= 1.1.0 =
* New: Automated Onboarding Wizard, WordPress Dashboard Widget, and `[fbs_stockmind_reminder]` guest-ready shortcode.

= 1.0.2 =
* Initial release on WordPress.org.

== Upgrade Notice ==

= 1.3.0 =
Major reliability, AI & UX update: Adds WordPress Native AI Connectors support, Gemini 2.5 Flash, HPOS compatibility, accurate variable product forecasting, 1-click email unsubscribe, and security hardening.
