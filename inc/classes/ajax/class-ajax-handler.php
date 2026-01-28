<?php
/**
 * AJAX handler class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Ajax;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Ajax_Handler
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
        // Admin AJAX actions
        add_action('wp_ajax_fbs_stockmind_dismiss_prediction', [$this, 'handle_dismiss_prediction']);
        add_action('wp_ajax_fbs_stockmind_save_supplier', [$this, 'handle_save_supplier']);
        add_action('wp_ajax_fbs_stockmind_delete_supplier', [$this, 'handle_delete_supplier']);
        add_action('wp_ajax_fbs_stockmind_get_supplier', [$this, 'handle_get_supplier']);
        add_action('wp_ajax_fbs_stockmind_deactivate_reminder', [$this, 'handle_deactivate_reminder']);
        add_action('wp_ajax_fbs_stockmind_activate_reminder', [$this, 'handle_activate_reminder']);
        add_action('wp_ajax_fbs_stockmind_delete_reminder', [$this, 'handle_delete_reminder']);
        add_action('wp_ajax_fbs_stockmind_refresh_predictions', [$this, 'handle_refresh_predictions']);
        add_action('wp_ajax_fbs_stockmind_calculate_predictions', [$this, 'handle_calculate_predictions']);
        add_action('wp_ajax_fbs_stockmind_dismiss_notice', [$this, 'handle_dismiss_notice']);
        
        // Frontend AJAX actions
        add_action('wp_ajax_fbs_stockmind_set_reminder', [$this, 'handle_set_reminder']);
        add_action('wp_ajax_nopriv_fbs_stockmind_set_reminder', [$this, 'handle_set_reminder']);
    }

    /**
     * Handle dismiss prediction
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_dismiss_prediction()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // Nonce verified above via verify_nonce(), now safely process POST data
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $prediction_id = isset($_POST['prediction_id']) ? absint(wp_unslash($_POST['prediction_id'])) : 0;
        
        if (!$prediction_id) {
            wp_send_json_error(__('Invalid prediction ID.', 'fbs-stockmind'));
        }

        $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
        
        if ($predictor->dismiss_prediction($prediction_id)) {
            wp_send_json_success(__('Prediction dismissed successfully.', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Failed to dismiss prediction.', 'fbs-stockmind'));
        }
    }


    /**
     * Handle save supplier
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_save_supplier()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // Nonce verified above via verify_nonce(), now safely process POST data
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $supplier_data = [
            'name' => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '',
            'lead_time' => isset($_POST['lead_time']) ? absint(wp_unslash($_POST['lead_time'])) : 7,
            'email' => isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
            'address' => isset($_POST['address']) ? sanitize_textarea_field(wp_unslash($_POST['address'])) : '',
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($supplier_data['name'])) {
            wp_send_json_error(__('Supplier name is required.', 'fbs-stockmind'));
        }

        $supplier_manager = \FBS_StockMind\Inc\Features\Supplier_Manager::get_instance();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $supplier_id = isset($_POST['supplier_id']) ? absint(wp_unslash($_POST['supplier_id'])) : 0;

        if ($supplier_id) {
            // Update existing supplier
            if ($supplier_manager->update_supplier($supplier_id, $supplier_data)) {
                wp_send_json_success(__('Supplier updated successfully.', 'fbs-stockmind'));
            } else {
                wp_send_json_error(__('Failed to update supplier.', 'fbs-stockmind'));
            }
        } else {
            // Create new supplier
            $new_supplier_id = $supplier_manager->create_supplier($supplier_data);
            if ($new_supplier_id) {
                wp_send_json_success(__('Supplier created successfully.', 'fbs-stockmind'));
            } else {
                wp_send_json_error(__('Failed to create supplier.', 'fbs-stockmind'));
            }
        }
    }

    /**
     * Handle delete supplier
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_delete_supplier()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $supplier_id = isset($_POST['supplier_id']) ? absint(wp_unslash($_POST['supplier_id'])) : 0;
        
        if (!$supplier_id) {
            wp_send_json_error(__('Invalid supplier ID.', 'fbs-stockmind'));
        }

        $supplier_manager = \FBS_StockMind\Inc\Features\Supplier_Manager::get_instance();
        
        if ($supplier_manager->delete_supplier($supplier_id)) {
            wp_send_json_success(__('Supplier deleted successfully.', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Failed to delete supplier.', 'fbs-stockmind'));
        }
    }

    /**
     * Handle get supplier
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_get_supplier()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $supplier_id = isset($_POST['supplier_id']) ? absint(wp_unslash($_POST['supplier_id'])) : 0;
        
        if (!$supplier_id) {
            wp_send_json_error(__('Invalid supplier ID.', 'fbs-stockmind'));
        }

        $supplier_manager = \FBS_StockMind\Inc\Features\Supplier_Manager::get_instance();
        $supplier = $supplier_manager->get_supplier($supplier_id);
        
        if ($supplier) {
            wp_send_json_success($supplier);
        } else {
            wp_send_json_error(__('Supplier not found.', 'fbs-stockmind'));
        }
    }

    /**
     * Handle deactivate reminder
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_deactivate_reminder()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $reminder_id = isset($_POST['reminder_id']) ? absint(wp_unslash($_POST['reminder_id'])) : 0;
        
        if (!$reminder_id) {
            wp_send_json_error(__('Invalid reminder ID.', 'fbs-stockmind'));
        }

        $reminder_manager = \FBS_StockMind\Inc\Features\Customer_Reminders::get_instance();
        
        if ($reminder_manager->deactivate_reminder($reminder_id)) {
            wp_send_json_success(__('Reminder deactivated successfully.', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Failed to deactivate reminder.', 'fbs-stockmind'));
        }
    }

    /**
     * Handle activate reminder
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_activate_reminder()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $reminder_id = isset($_POST['reminder_id']) ? absint(wp_unslash($_POST['reminder_id'])) : 0;
        
        if (!$reminder_id) {
            wp_send_json_error(__('Invalid reminder ID.', 'fbs-stockmind'));
        }

        global $wpdb;
        $reminders_table = fbs_stockmind_get_table_name('reminders');
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary for updating reminder status, real-time operation
        $result = $wpdb->update(
            $reminders_table,
            ['is_active' => 1],
            ['id' => $reminder_id],
            ['%d'],
            ['%d']
        );

        if ($result !== false) {
            wp_send_json_success(__('Reminder activated successfully.', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Failed to activate reminder.', 'fbs-stockmind'));
        }
    }

    /**
     * Handle delete reminder
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_delete_reminder()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $reminder_id = isset($_POST['reminder_id']) ? absint(wp_unslash($_POST['reminder_id'])) : 0;
        
        if (!$reminder_id) {
            wp_send_json_error(__('Invalid reminder ID.', 'fbs-stockmind'));
        }

        $reminder_manager = \FBS_StockMind\Inc\Features\Customer_Reminders::get_instance();
        
        if ($reminder_manager->delete_reminder($reminder_id)) {
            wp_send_json_success(__('Reminder deleted successfully.', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Failed to delete reminder.', 'fbs-stockmind'));
        }
    }

    /**
     * Handle refresh predictions
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_refresh_predictions()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
        $predictor->calculate_all_predictions();
        
        wp_send_json_success(__('Predictions refreshed successfully.', 'fbs-stockmind'));
    }

    /**
     * Handle calculate predictions
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_calculate_predictions()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        $predictor = \FBS_StockMind\Inc\Features\Predictor::get_instance();
        $predictor->calculate_all_predictions();
        
        wp_send_json_success(__('Predictions calculated successfully.', 'fbs-stockmind'));
    }

    /**
     * Handle dismiss notice
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_dismiss_notice()
    {
        $this->verify_nonce();
        $this->check_admin_permissions();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        $notice_id = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        
        if (!$notice_id) {
            wp_send_json_error(__('Invalid notice ID.', 'fbs-stockmind'));
        }

        update_user_meta(get_current_user_id(), $notice_id, true);
        wp_send_json_success(__('Notice dismissed.', 'fbs-stockmind'));
    }

    /**
     * Handle set reminder (frontend)
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function handle_set_reminder()
    {
        $this->verify_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() method
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Will be unslashed
        $customer_email = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : '';
        $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
        $order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($customer_email) || !$product_id || !$order_id) {
            wp_send_json_error(__('Invalid data provided.', 'fbs-stockmind'));
        }

        if (!is_email($customer_email)) {
            wp_send_json_error(__('Invalid email address.', 'fbs-stockmind'));
        }

        $reminder_manager = \FBS_StockMind\Inc\Features\Customer_Reminders::get_instance();
        
        if ($reminder_manager->set_reminder($customer_email, $product_id, $order_id)) {
            wp_send_json_success(__('Reminder set successfully!', 'fbs-stockmind'));
        } else {
            wp_send_json_error(__('Reminder already exists or failed to set.', 'fbs-stockmind'));
        }
    }

    /**
     * Verify nonce
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function verify_nonce()
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'fbs_stockmind_nonce')) {
            wp_die(esc_html__('Security check failed.', 'fbs-stockmind'));
        }
    }

    /**
     * Check admin permissions
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function check_admin_permissions()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Insufficient permissions.', 'fbs-stockmind'));
        }
    }
}
