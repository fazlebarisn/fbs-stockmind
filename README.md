# FBS StockMind

**Premium WooCommerce Plugin for Predictive Inventory Management**

FBS StockMind is a powerful WordPress plugin that provides predictive low-stock alerts for store owners and smart replenishment reminders for customers. Built with modern web technologies and following WordPress coding standards.

## Features

### 🧠 AI-Powered Predictions
- Analyzes sales data from the last 90 days
- Calculates average daily sales rate
- Factors in supplier lead times
- Updates predictions daily via cron job
- Configurable accuracy thresholds

### 🏢 Supplier Management
- Custom supplier management system
- Lead time configuration per supplier
- Contact information and notes
- Product-supplier associations
- Active/inactive supplier status

### 📧 Smart Customer Reminders
- Non-intrusive reminder forms on thank you pages
- Email notifications before predicted runout
- Configurable advance notice periods
- Maximum reminder attempt limits
- Beautiful email templates

### 🎨 Modern Admin Interface
- Card-based dashboard design
- Real-time statistics and alerts
- Intuitive navigation and filters
- Toast-style notifications
- Responsive design for all devices

### ⚙️ Advanced Settings
- Configurable alert windows
- Email customization options
- Feature toggles
- Prediction accuracy controls
- Reminder management settings

## Installation

1. Upload the plugin files to `/wp-content/plugins/fbs-stockmind/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure WooCommerce is installed and active
4. Navigate to StockMind in your admin menu to configure settings

## Requirements

- WordPress 6.4.2 or higher
- PHP 7.4 or higher
- WooCommerce plugin (active)
- MySQL 5.6 or higher

## Configuration

### Initial Setup

1. **Add Suppliers**: Go to StockMind > Suppliers and add your suppliers with lead times
2. **Configure Products**: Edit products to mark them as replenishable and assign suppliers
3. **Adjust Settings**: Go to StockMind > Settings to configure prediction and reminder settings
4. **Test Predictions**: Use the "Refresh Predictions" button to generate initial predictions

### Settings Overview

- **Alert Window**: How many days in advance to show low stock alerts (default: 14 days)
- **Default Lead Time**: Default time needed to restock products (default: 7 days)
- **Prediction Accuracy**: Minimum confidence level for predictions (default: 0.8)
- **Reminder Advance Days**: Days before runout to send customer reminders (default: 5 days)
- **Max Reminder Attempts**: Maximum reminder emails per customer (default: 3)

## Usage

### For Store Owners

1. **Dashboard**: View products needing attention and upcoming replenishments
2. **Predictions**: Review AI-generated stock predictions and take action
3. **Suppliers**: Manage supplier information and lead times
4. **Reminders**: Monitor customer reminder activity and effectiveness
5. **Settings**: Configure prediction algorithms and notification preferences

### For Customers

1. **Automatic Detection**: Reminder forms appear on order completion pages
2. **Easy Setup**: One-click reminder activation for replenishable products
3. **Smart Notifications**: Receive email reminders before running out
4. **Account Management**: Manage reminders through account page

## Database Schema

The plugin creates three custom tables:

- `wp_fbs_stockmind_predictions`: Stores product predictions and runout dates
- `wp_fbs_stockmind_reminders`: Tracks customer reminder subscriptions
- `wp_fbs_stockmind_suppliers`: Manages supplier information and lead times

## API & Hooks

### Actions

```php
// Triggered when predictions are calculated
do_action('fbs_stockmind_predictions_calculated', $predictions_count);

// Triggered when reminder is sent
do_action('fbs_stockmind_reminder_sent', $reminder_id, $customer_email);

// Triggered when prediction is dismissed
do_action('fbs_stockmind_prediction_dismissed', $prediction_id, $user_id);
```

### Filters

```php
// Modify prediction calculation
apply_filters('fbs_stockmind_prediction_algorithm', $algorithm_data, $product_id);

// Customize reminder email content
apply_filters('fbs_stockmind_reminder_email_content', $content, $reminder_data);

// Modify supplier lead time
apply_filters('fbs_stockmind_supplier_lead_time', $lead_time, $supplier_id, $product_id);
```

## Security

- All inputs are sanitized using WordPress functions
- All outputs are escaped appropriately
- WordPress nonces protect all AJAX actions
- User capabilities are checked for all operations
- SQL queries use prepared statements

## Performance

- Predictions are calculated via daily cron jobs
- Database queries are optimized with proper indexing
- Assets are minified and cached
- Lazy loading for large datasets
- Efficient caching mechanisms

## Troubleshooting

### Common Issues

1. **Predictions not showing**: Ensure WooCommerce is active and products have sales data
2. **Reminders not sending**: Check email settings and SMTP configuration
3. **Cron jobs not running**: Verify WordPress cron is enabled and working
4. **Database errors**: Check table creation during plugin activation

### Debug Mode

Enable WordPress debug mode to see detailed error logs:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support, feature requests, or bug reports, please contact:

- **Author**: Fazle Bari
- **Email**: fazlebarisn@gmail.com
- **Website**: https://www.cansoft.com/

## Changelog

### Version 1.0.0
- Initial release
- AI-powered stock predictions
- Supplier management system
- Customer reminder functionality
- Modern admin interface
- Comprehensive settings panel

## License

This plugin is licensed under the GPL-2.0-or-later license.

## Credits

- Built with modern web technologies
- Follows WordPress coding standards
- Uses WordPress best practices
- Responsive design principles
- Accessibility considerations

---

**FBS StockMind** - Making inventory management intelligent and effortless.
