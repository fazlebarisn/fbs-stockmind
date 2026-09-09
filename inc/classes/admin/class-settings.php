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
        add_action('wp_ajax_fbs_stockmind_test_ai_connection', [$this, 'test_ai_connection']);
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
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_alert_window', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 14,
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_default_lead_time', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 7,
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_prediction_accuracy_threshold', [
            'type' => 'number',
            'sanitize_callback' => [$this, 'sanitize_float'],
            'default' => 0.3,
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_sales_data_period', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 30,
        ]);
        
        // Reminder settings
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_reminder_advance_days', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 5,
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_max_reminder_attempts', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 1,
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_enable_customer_reminders', [
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => false,
        ]);
        
        // Email settings
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_email_from_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_email_from_address', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => '',
        ]);
        
        // Feature toggles
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_enable_admin_alerts', [
            'type' => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default' => false,
        ]);

        // AI Assistant Settings
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_ai_provider', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'gemini',
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_ai_model', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'gemini-3.6-flash',
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_ai_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('fbs_stockmind_settings', 'fbs_stockmind_ai_tone', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'analytical',
        ]);
    }
    
    /**
     * Sanitize float value
     *
     * @param mixed $value The value to sanitize
     * @return float
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function sanitize_float($value)
    {
        // Convert to float
        $float_value = floatval($value);
        
        // Check for NaN or INF
        if (!is_finite($float_value)) {
            return 0.6; // Default threshold value
        }
        
        // Clamp to reasonable range (0-1 for accuracy threshold)
        // This ensures the value is between 0 and 1
        if ($float_value < 0) {
            return 0.0;
        }
        if ($float_value > 1) {
            return 1.0;
        }
        
        return $float_value;
    }
    
    /**
     * Sanitize boolean value
     *
     * @param mixed $value The value to sanitize
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function sanitize_boolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
        if (isset($_POST['submit']) && isset($_POST['_wpnonce'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
            $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
            if (wp_verify_nonce($nonce, 'fbs_stockmind_settings')) {
                $this->save_settings();
            }
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
        // Verify nonce (additional check for plugin checker)
        if (!isset($_POST['_wpnonce'])) {
            return;
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'fbs_stockmind_settings')) {
            return;
        }
        
        // Check if prediction settings are editable (free: read-only, pro: editable)
        $prediction_editable = $this->is_prediction_settings_editable();
        
        // Get default values
        $default_threshold = apply_filters('fbs_stockmind_default_accuracy_threshold', 0.3);
        $default_sales_period = apply_filters('fbs_stockmind_sales_data_period', 30, 0);
        
        // Get current values
        $current_settings = $this->get_all_settings();
        
        // Only save prediction settings if editable (pro version)
        $prediction_threshold = $prediction_editable 
            ? floatval(isset($_POST['prediction_accuracy_threshold']) ? wp_unslash($_POST['prediction_accuracy_threshold']) : $current_settings['prediction_accuracy_threshold'])
            : $current_settings['prediction_accuracy_threshold'];
        
        $sales_data_period = $prediction_editable
            ? absint(isset($_POST['sales_data_period']) ? wp_unslash($_POST['sales_data_period']) : $current_settings['sales_data_period'])
            : $current_settings['sales_data_period'];
        
        // Check if reminder settings are editable (free: read-only, pro: editable)
        $reminder_editable = $this->is_reminder_settings_editable();
        
        // Get current values
        $current_settings = $this->get_all_settings();
        
        // Only save reminder attempts if editable (pro version)
        $reminder_attempts = $reminder_editable
            ? absint(isset($_POST['max_reminder_attempts']) ? wp_unslash($_POST['max_reminder_attempts']) : $current_settings['max_reminder_attempts'])
            : $current_settings['max_reminder_attempts'];
        
        // Only save reminder advance days if editable (pro version)
        $reminder_advance_days = $reminder_editable
            ? absint(isset($_POST['reminder_advance_days']) ? wp_unslash($_POST['reminder_advance_days']) : $current_settings['reminder_advance_days'])
            : $current_settings['reminder_advance_days'];
        
        $settings_to_save = [
            'alert_window' => absint(isset($_POST['alert_window']) ? wp_unslash($_POST['alert_window']) : 14),
            'default_lead_time' => absint(isset($_POST['default_lead_time']) ? wp_unslash($_POST['default_lead_time']) : 7),
            'prediction_accuracy_threshold' => $prediction_threshold,
            'sales_data_period' => $sales_data_period,
            'reminder_advance_days' => $reminder_advance_days,
            'max_reminder_attempts' => $reminder_attempts,
            'enable_customer_reminders' => isset($_POST['enable_customer_reminders']),
            'email_from_name' => isset($_POST['email_from_name']) ? sanitize_text_field(wp_unslash($_POST['email_from_name'])) : '',
            'email_from_address' => isset($_POST['email_from_address']) ? sanitize_email(wp_unslash($_POST['email_from_address'])) : '',
            'enable_admin_alerts' => isset($_POST['enable_admin_alerts']),
            'fbs_stockmind_openai_key' => isset($_POST['fbs_stockmind_ai_api_key']) ? sanitize_text_field(wp_unslash($_POST['fbs_stockmind_ai_api_key'])) : (isset($_POST['fbs_stockmind_openai_key']) ? sanitize_text_field(wp_unslash($_POST['fbs_stockmind_openai_key'])) : ''),
            'ai_provider' => isset($_POST['fbs_stockmind_ai_provider']) ? sanitize_text_field(wp_unslash($_POST['fbs_stockmind_ai_provider'])) : 'gemini',
            'ai_model' => isset($_POST['fbs_stockmind_ai_model']) ? sanitize_text_field(wp_unslash($_POST['fbs_stockmind_ai_model'])) : 'gemini-3.6-flash',
            'ai_api_key' => isset($_POST['fbs_stockmind_ai_api_key']) ? sanitize_text_field(wp_unslash($_POST['fbs_stockmind_ai_api_key'])) : '',
            'ai_tone' => isset($_POST['fbs_stockmind_ai_tone']) ? sanitize_text_field(wp_unslash($_POST['fbs_stockmind_ai_tone'])) : 'analytical',
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
        // Verify nonce
        if (!isset($_POST['nonce'])) {
            wp_send_json_error(esc_html__('Security check failed.', 'fbs-stockmind'));
            return;
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'fbs_stockmind_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'fbs-stockmind'));
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'fbs-stockmind'));
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Will be sanitized below
        $settings_data = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : [];
        $settings_data = is_array($settings_data) ? array_map('sanitize_text_field', $settings_data) : [];
        
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
                case 'fbs_stockmind_openai_key':
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
        // Get default accuracy threshold (0.3 for free, 0.8 for pro)
        $default_threshold = apply_filters('fbs_stockmind_default_accuracy_threshold', 0.3);
        // Get default sales data period (30 for free, 90 for pro)
        $default_sales_period = apply_filters('fbs_stockmind_sales_data_period', 30, 0);
        
        // Determine if settings are editable (Pro active)
        $prediction_editable = $this->is_prediction_settings_editable();
        $reminder_editable = $this->is_reminder_settings_editable();
        
        // If not editable, enforce the defaults
        $accuracy_threshold = $prediction_editable ? fbs_stockmind_get_option('prediction_accuracy_threshold', $default_threshold) : $default_threshold;
        $sales_period = $prediction_editable ? fbs_stockmind_get_option('sales_data_period', $default_sales_period) : $default_sales_period;
        
        $advance_days = $reminder_editable ? fbs_stockmind_get_option('reminder_advance_days', 5) : apply_filters('fbs_stockmind_default_reminder_advance_days', 5);
        $max_attempts = $reminder_editable ? fbs_stockmind_get_option('max_reminder_attempts', 1) : apply_filters('fbs_stockmind_default_max_reminder_attempts', 1);
        
        return [
            'alert_window' => fbs_stockmind_get_option('alert_window', 14),
            'default_lead_time' => fbs_stockmind_get_option('default_lead_time', 7),
            'prediction_accuracy_threshold' => $accuracy_threshold,
            'sales_data_period' => $sales_period,
            'reminder_advance_days' => $advance_days,
            'max_reminder_attempts' => $max_attempts,
            'enable_customer_reminders' => fbs_stockmind_get_option('enable_customer_reminders', true),
            'email_from_name' => fbs_stockmind_get_option('email_from_name', get_bloginfo('name')),
            'email_from_address' => fbs_stockmind_get_option('email_from_address', get_option('admin_email')),
            'enable_admin_alerts' => fbs_stockmind_get_option('enable_admin_alerts', true),
            'ai_provider' => fbs_stockmind_get_option('ai_provider', 'gemini'),
            'ai_model' => fbs_stockmind_get_option('ai_model', 'gemini-3.6-flash'),
            'ai_api_key' => fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', '')),
            'ai_tone' => fbs_stockmind_get_option('ai_tone', 'analytical'),
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
    
    /**
     * Check if reminder settings are editable
     * Allow pro to override via filter
     *
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function is_reminder_settings_editable()
    {
        // Free version: read-only, Pro can enable via filter
        return apply_filters('fbs_stockmind_reminder_settings_editable', false);
    }

    /**
     * Test AI connection (Google Gemini or OpenAI)
     */
    public function test_ai_connection()
    {
        check_ajax_referer('fbs_stockmind_nonce', 'nonce');

        if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied.', 'fbs-stockmind')]);
        }

        $provider = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : 'gemini';
        $api_key  = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

        if (empty($api_key)) {
            $api_key = fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', ''));
        }

        if (empty($api_key)) {
            wp_send_json_error(['message' => __('Please enter an API key first.', 'fbs-stockmind')]);
        }

        // phpcs:disable PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Testing user-configured direct API credentials
        if ($provider === 'openai') {
            $response = wp_remote_get('https://api.openai.com/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key
                ],
                'timeout' => 15
            ]);

            if (is_wp_error($response)) {
                wp_send_json_error(['message' => $response->get_error_message()]);
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $err = isset($body['error']['message']) ? $body['error']['message'] : __('Invalid OpenAI API Key or unauthorized request.', 'fbs-stockmind');
                wp_send_json_error(['message' => $err]);
            }

            wp_send_json_success(['message' => __('Connection successful! OpenAI API key is valid.', 'fbs-stockmind')]);
        } else {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($api_key);
            $response = wp_remote_get($url, [
                'timeout' => 15
            ]);

            if (is_wp_error($response)) {
                wp_send_json_error(['message' => $response->get_error_message()]);
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $err = isset($body['error']['message']) ? $body['error']['message'] : __('Invalid Google Gemini API Key or unauthorized request.', 'fbs-stockmind');
                wp_send_json_error(['message' => $err]);
            }

            wp_send_json_success(['message' => __('Connection successful! Google Gemini API key is valid.', 'fbs-stockmind')]);
        }
        // phpcs:enable PluginCheck.CodeAnalysis.AIProvider.DirectIntegration
    }
}
