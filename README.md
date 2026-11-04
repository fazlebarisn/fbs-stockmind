# FBS StockMind — AI Inventory Forecasting & Customer Replenishment for WooCommerce

**Version:** 1.3.0  
**Author:** [Fazle Bari — FBS Fusion](https://fbsfusion.com/)  
**WordPress.org:** [https://wordpress.org/plugins/fbs-stockmind/](https://wordpress.org/plugins/fbs-stockmind/)  
**Pro Edition:** [https://fbsfusion.com/plugins/#fbs-stockmind](https://fbsfusion.com/plugins/#fbs-stockmind)  
**Video Walkthrough:** [Watch on YouTube](https://www.youtube.com/watch?v=Dkc_0F_MUes)  
**Requires WordPress:** 6.4+ | **Tested up to:** 7.1 | **Requires PHP:** 7.4+ | **WooCommerce:** 6.0 – 11.1 (HPOS Ready)

FBS StockMind is an intelligent inventory forecasting and repeat-order automation plugin for WooCommerce built by **FBS Fusion**. It combines predictive stockout alerts, supplier lead-time tracking, customer replenishment reminders, and a live AI Inventory Assistant.

---

## Key Features

### 🧠 Predictive Stockout Forecasting
- Analyzes historical WooCommerce sales velocity (30-day window in Free; 7–365 days in Pro)
- Aggregates simple and variable product inventory & variation sales velocity
- Deducts refunded quantities for accurate net daily demand
- Factors in per-supplier lead times to alert you before safety thresholds are breached
- Full WooCommerce High-Performance Order Storage (`custom_order_tables`) compatibility

### 🤖 Interactive AI Inventory Assistant
- Powered natively by **WordPress AI Client & Connectors** (`wp_ai_client_prompt` / `Settings ➔ Connectors`)
- Works seamlessly with any connected WordPress AI provider (Google Gemini, OpenAI, Anthropic, Mistral, Vercel AI Gateway)
- Automatically injects live WooCommerce inventory summaries, urgent runout alerts, 30-day best-sellers, and supplier directories
- 1-click Quick Prompt chips and persistent browser chat memory

### 🔄 Smart Customer Replenishment Reminders
- Clean, non-intrusive inline opt-in cards on WooCommerce Thank You pages
- Flexible `[fbs_stockmind_reminder]` shortcode for product pages, posts, or landing pages (supports guest shoppers)
- Responsive HTML reminder emails with signed 1-click unsubscribe links

### 🏢 Supplier Lead-Time Management
- Dedicated supplier directory (up to 3 suppliers in Free; unlimited in Pro)
- Per-product supplier assignment and lead-time overrides

---

## Installation

1. Upload `/fbs-stockmind/` to `/wp-content/plugins/` or install from WordPress.org.
2. Activate **FBS StockMind** in **Plugins** (requires active WooCommerce 6.0+).
3. Open **StockMind** in wp-admin to run predictions, configure suppliers, and connect your preferred AI provider under **StockMind ➔ Settings ➔ AI Assistant**.

---

## Developer Hooks

### Actions
```php
do_action( 'fbs_stockmind_predictions_calculated', $predictions_count );
do_action( 'fbs_stockmind_reminder_sent', $reminder_id, $customer_email );
do_action( 'fbs_stockmind_prediction_dismissed', $prediction_id, $user_id );
do_action( 'fbs_stockmind_save_settings', $_POST );
```

### Filters
```php
apply_filters( 'fbs_stockmind_sales_data_period', $period );
apply_filters( 'fbs_stockmind_max_suppliers', $max_suppliers );
apply_filters( 'fbs_stockmind_ai_daily_limit', $daily_limit );
apply_filters( 'fbs_stockmind_reminder_email_content', $message, $reminder, $product );
```

---

## Support & Studio

- **Engineering Studio:** [FBS Fusion](https://fbsfusion.com/)
- **Plugin Suite & StockMind Pro:** [https://fbsfusion.com/plugins/](https://fbsfusion.com/plugins/)
- **WooCommerce Speed & Security Rescue:** [https://fbsfusion.com/speed-security/](https://fbsfusion.com/speed-security/)
- **Contact:** `fazle@fbsfusion.com`

## License

Licensed under the **GPL-2.0-or-later** license.
