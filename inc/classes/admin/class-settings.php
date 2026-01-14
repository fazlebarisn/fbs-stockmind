<?php
/**
 * Admin settings class for FBS StockMind
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Admin;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Settings
{
    use Singleton;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function __construct()
    {
        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function setup_hooks()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function register_settings()
    {
        // General settings
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_alert_window');
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_default_lead_time');
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_prediction_accuracy_threshold');
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_sales_data_period');
        
        // Reminder settings
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_reminder_advance_days');
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_max_reminder_attempts');
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_enable_customer_reminders');
        
        // Email settings
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_email_from_name');
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_email_from_address');
        
        // Feature toggles
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_enable_admin_alerts');
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render()
    {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'fbs_stockmind_settings')) {
            $this->save_settings();
        }

        $settings = $this->get_all_settings();
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/settings.php';
    }

    /**
     * Save settings
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function save_settings()
    {
        // Check if prediction settings are editable (free: read-only, pro: editable)
        $prediction_editable = $this->is_prediction_settings_editable();
        
        // Get default values
        $default_threshold = apply_filters('fbs_stockmind_default_accuracy_threshold', 0.6);
        $default_sales_period = apply_filters('fbs_stockmind_sales_data_period', 30, 0);
        
        // Get current values
        $current_settings = $this->get_all_settings();
        
        // Only save prediction settings if editable (pro version)
        $prediction_threshold = $prediction_editable 
            ? floatval($_POST['prediction_accuracy_threshold'] ?? $current_settings['prediction_accuracy_threshold'])
            : $current_settings['prediction_accuracy_threshold'];
        
        $sales_data_period = $prediction_editable
            ? absint($_POST['sales_data_period'] ?? $current_settings['sales_data_period'])
            : $current_settings['sales_data_period'];
        
        // Get max reminder attempts (1 for free, 10 for pro)
        $max_attempts = defined('FBS_STOCKMIND_PRO_VERSION') ? 10 : 1;
        $reminder_attempts = absint($_POST['max_reminder_attempts'] ?? 1);
        // Enforce max attempts for free version
        if (!defined('FBS_STOCKMIND_PRO_VERSION') && $reminder_attempts > 1) {
            $reminder_attempts = 1;
        }
        
        $settings_to_save = [
            'alert_window' => absint($_POST['alert_window'] ?? 14),
            'default_lead_time' => absint($_POST['default_lead_time'] ?? 7),
            'prediction_accuracy_threshold' => $prediction_threshold,
            'sales_data_period' => $sales_data_period,
            'reminder_advance_days' => absint($_POST['reminder_advance_days'] ?? 5),
            'max_reminder_attempts' => min($reminder_attempts, $max_attempts),
            'enable_customer_reminders' => isset($_POST['enable_customer_reminders']),
            'email_from_name' => sanitize_text_field($_POST['email_from_name'] ?? ''),
            'email_from_address' => sanitize_email($_POST['email_from_address'] ?? ''),
            'enable_admin_alerts' => isset($_POST['enable_admin_alerts']),
        ];

        foreach ($settings_to_save as $key => $value) {
            fbs_stockmind_update_option($key, $value);
        }

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__('Settings saved successfully!', 'fbs-stockmind') . 
                 '</p></div>';
        });
    }

    /**
     * Handle AJAX save settings
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_save_settings()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'fbs-stockmind'));
        }

        $settings_data = $_POST['settings'] ?? [];
        
        if (empty($settings_data)) {
            wp_send_json_error(__('No settings data provided.', 'fbs-stockmind'));
        }

        $settings_to_save = [];
        foreach ($settings_data as $key => $value) {
            switch ($key) {
                case 'alert_window':
                case 'default_lead_time':
                case 'reminder_advance_days':
                case 'max_reminder_attempts':
                    $settings_to_save[$key] = absint($value);
                    break;
                case 'prediction_accuracy_threshold':
                    $settings_to_save[$key] = floatval($value);
                    break;
                case 'email_from_name':
                    $settings_to_save[$key] = sanitize_text_field($value);
                    break;
                case 'email_from_address':
                    $settings_to_save[$key] = sanitize_email($value);
                    break;
                case 'enable_customer_reminders':
                case 'enable_admin_alerts':
                    $settings_to_save[$key] = (bool) $value;
                    break;
            }
        }

        foreach ($settings_to_save as $key => $value) {
            fbs_stockmind_update_option($key, $value);
        }

        wp_send_json_success(__('Settings saved successfully!', 'fbs-stockmind'));
    }

    /**
     * Get all settings
     *
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function get_all_settings()
    {
        // Get default accuracy threshold (0.6 for free, 0.8 for pro)
        $default_threshold = apply_filters('fbs_stockmind_default_accuracy_threshold', 0.6);
        // Get default sales data period (30 for free, 90 for pro)
        $default_sales_period = apply_filters('fbs_stockmind_sales_data_period', 30, 0);
        
        return [
            'alert_window' => fbs_stockmind_get_option('alert_window', 14),
            'default_lead_time' => fbs_stockmind_get_option('default_lead_time', 7),
            'prediction_accuracy_threshold' => fbs_stockmind_get_option('prediction_accuracy_threshold', $default_threshold),
            'sales_data_period' => fbs_stockmind_get_option('sales_data_period', $default_sales_period),
            'reminder_advance_days' => fbs_stockmind_get_option('reminder_advance_days', 5),
            'max_reminder_attempts' => fbs_stockmind_get_option('max_reminder_attempts', 1), // Free: 1 attempt default
            'enable_customer_reminders' => fbs_stockmind_get_option('enable_customer_reminders', true),
            'email_from_name' => fbs_stockmind_get_option('email_from_name', get_bloginfo('name')),
            'email_from_address' => fbs_stockmind_get_option('email_from_address', get_option('admin_email')),
            'enable_admin_alerts' => fbs_stockmind_get_option('enable_admin_alerts', true),
        ];
    }
    
    /**
     * Check if prediction settings are editable
     * Allow pro to override via filter
     *
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function is_prediction_settings_editable()
    {
        // Free version: read-only, Pro can enable via filter
        return apply_filters('fbs_stockmind_prediction_settings_editable', false);
    }
}
