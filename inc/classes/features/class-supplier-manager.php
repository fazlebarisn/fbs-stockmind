<?php
/**
 * Supplier management class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Supplier_Manager
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
        add_action('init', [$this, 'register_supplier_post_type']);
        add_action('add_meta_boxes', [$this, 'add_supplier_meta_boxes']);
        add_action('save_post', [$this, 'save_supplier_meta']);
    }

    /**
     * Register supplier custom post type
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function register_supplier_post_type()
    {
        $labels = [
            'name' => __('Suppliers', 'fbs-stockmind'),
            'singular_name' => __('Supplier', 'fbs-stockmind'),
            'menu_name' => __('Suppliers', 'fbs-stockmind'),
            'add_new' => __('Add New Supplier', 'fbs-stockmind'),
            'add_new_item' => __('Add New Supplier', 'fbs-stockmind'),
            'edit_item' => __('Edit Supplier', 'fbs-stockmind'),
            'new_item' => __('New Supplier', 'fbs-stockmind'),
            'view_item' => __('View Supplier', 'fbs-stockmind'),
            'search_items' => __('Search Suppliers', 'fbs-stockmind'),
            'not_found' => __('No suppliers found', 'fbs-stockmind'),
            'not_found_in_trash' => __('No suppliers found in trash', 'fbs-stockmind'),
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add it to our custom menu
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'menu_icon' => 'dashicons-businessman',
            'supports' => ['title', 'editor'],
            'show_in_rest' => false,
        ];

        register_post_type('fbs_supplier', $args);
    }

    /**
     * Add supplier meta boxes
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function add_supplier_meta_boxes()
    {
        add_meta_box(
            'fbs_supplier_details',
            __('Supplier Details', 'fbs-stockmind'),
            [$this, 'render_supplier_meta_box'],
            'fbs_supplier',
            'normal',
            'high'
        );
    }

    /**
     * Render supplier meta box
     *
     * @param WP_Post $post The post object
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function render_supplier_meta_box($post)
    {
        wp_nonce_field('fbs_supplier_meta', 'fbs_supplier_meta_nonce');
        
        $lead_time = get_post_meta($post->ID, '_fbs_supplier_lead_time', true);
        $email = get_post_meta($post->ID, '_fbs_supplier_email', true);
        $phone = get_post_meta($post->ID, '_fbs_supplier_phone', true);
        $address = get_post_meta($post->ID, '_fbs_supplier_address', true);
        $notes = get_post_meta($post->ID, '_fbs_supplier_notes', true);
        $is_active = get_post_meta($post->ID, '_fbs_supplier_is_active', true);
        
        include FBS_STOCKMIND_DIR_PATH . '/inc/templates/admin/supplier-meta-box.php';
    }

    /**
     * Save supplier meta
     *
     * @param int $post_id The post ID
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function save_supplier_meta($post_id)
    {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check if this is a revision
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Check if this is the correct post type
        if (get_post_type($post_id) !== 'fbs_supplier') {
            return;
        }

        // Check nonce
        if (!isset($_POST['fbs_supplier_meta_nonce']) || 
            !wp_verify_nonce($_POST['fbs_supplier_meta_nonce'], 'fbs_supplier_meta')) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save meta fields
        $fields = [
            '_fbs_supplier_lead_time' => 'absint',
            '_fbs_supplier_email' => 'sanitize_email',
            '_fbs_supplier_phone' => 'sanitize_text_field',
            '_fbs_supplier_address' => 'sanitize_textarea_field',
            '_fbs_supplier_notes' => 'sanitize_textarea_field',
            '_fbs_supplier_is_active' => 'absint',
        ];

        foreach ($fields as $field => $sanitize_function) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_function, $_POST[$field]);
                update_post_meta($post_id, $field, $value);
            }
        }

        // Sync with database table
        $this->sync_supplier_to_table($post_id);
    }

    /**
     * Sync supplier data to custom table
     *
     * @param int $post_id The post ID
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function sync_supplier_to_table($post_id)
    {
        global $wpdb;

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'fbs_supplier') {
            return;
        }

        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        
        $lead_time = get_post_meta($post_id, '_fbs_supplier_lead_time', true) ?: 7;
        $email = get_post_meta($post_id, '_fbs_supplier_email', true);
        $phone = get_post_meta($post_id, '_fbs_supplier_phone', true);
        $address = get_post_meta($post_id, '_fbs_supplier_address', true);
        $notes = get_post_meta($post_id, '_fbs_supplier_notes', true);
        $is_active = get_post_meta($post_id, '_fbs_supplier_is_active', true) ?: 1;

        // Check if supplier exists in table
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $suppliers_table WHERE id = %d",
            $post_id
        ));

        $data = [
            'name' => $post->post_title,
            'lead_time' => $lead_time,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'notes' => $notes,
            'is_active' => $is_active,
            'updated_at' => current_time('mysql'),
        ];

        if ($existing) {
            // Update existing record
            $wpdb->update(
                $suppliers_table,
                $data,
                ['id' => $post_id],
                ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s'],
                ['%d']
            );
        } else {
            // Insert new record
            $data['id'] = $post_id;
            $data['created_at'] = current_time('mysql');
            
            $wpdb->insert(
                $suppliers_table,
                $data,
                ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
            );
        }
    }

    /**
     * Get all suppliers
     *
     * @param bool $active_only Whether to return only active suppliers
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_all_suppliers($active_only = true)
    {
        global $wpdb;

        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        
        $where_clause = $active_only ? 'WHERE is_active = 1' : '';
        
        $results = $wpdb->get_results(
            "SELECT * FROM $suppliers_table $where_clause ORDER BY name ASC"
        );

        // Allow pro to remove supplier limit (free version limited to 3)
        $max_suppliers = apply_filters('fbs_stockmind_max_suppliers', 3);
        
        // If pro is active, max_suppliers will be -1 (unlimited)
        if ($max_suppliers > 0 && count($results) > $max_suppliers) {
            $results = array_slice($results, 0, $max_suppliers);
        }

        return apply_filters('fbs_stockmind_suppliers_list', $results, $active_only);
    }

    /**
     * Get supplier by ID
     *
     * @param int $supplier_id The supplier ID
     * @return object|null
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_supplier($supplier_id)
    {
        global $wpdb;

        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $suppliers_table WHERE id = %d",
            $supplier_id
        ));

        return $result;
    }

    /**
     * Create a new supplier
     *
     * @param array $data Supplier data
     * @return int|false The supplier ID or false on failure
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function create_supplier($data)
    {
        // Check supplier limit - allow pro to override
        $max_suppliers = apply_filters('fbs_stockmind_max_suppliers', 3);
        
        if ($max_suppliers > 0) {
            global $wpdb;
            $suppliers_table = fbs_stockmind_get_table_name('suppliers');
            $current_count = $wpdb->get_var("SELECT COUNT(*) FROM $suppliers_table WHERE is_active = 1");
            
            if ($current_count >= $max_suppliers) {
                return new \WP_Error(
                    'supplier_limit_reached',
                    sprintf(
                        /* translators: %d: Maximum number of suppliers */
                        __('Maximum %d suppliers allowed in free version. Upgrade to Pro for unlimited suppliers.', 'fbs-stockmind'),
                        $max_suppliers
                    )
                );
            }
        }
        
        // Allow pro to modify supplier data before creation
        $data = apply_filters('fbs_stockmind_before_create_supplier', $data);
        
        $post_data = [
            'post_title' => sanitize_text_field($data['name']),
            'post_content' => sanitize_textarea_field($data['notes'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'fbs_supplier',
        ];

        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return false;
        }

        // Save meta fields
        $meta_fields = [
            '_fbs_supplier_lead_time' => absint($data['lead_time'] ?? 7),
            '_fbs_supplier_email' => sanitize_email($data['email'] ?? ''),
            '_fbs_supplier_phone' => sanitize_text_field($data['phone'] ?? ''),
            '_fbs_supplier_address' => sanitize_textarea_field($data['address'] ?? ''),
            '_fbs_supplier_notes' => sanitize_textarea_field($data['notes'] ?? ''),
            '_fbs_supplier_is_active' => 1,
        ];

        foreach ($meta_fields as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }

        // Sync to table
        $this->sync_supplier_to_table($post_id);

        return $post_id;
    }

    /**
     * Update supplier
     *
     * @param int $supplier_id The supplier ID
     * @param array $data Supplier data
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function update_supplier($supplier_id, $data)
    {
        $post_data = [
            'ID' => $supplier_id,
            'post_title' => sanitize_text_field($data['name']),
            'post_content' => sanitize_textarea_field($data['notes'] ?? ''),
        ];

        $result = wp_update_post($post_data);
        
        if (is_wp_error($result)) {
            return false;
        }

        // Update meta fields
        $meta_fields = [
            '_fbs_supplier_lead_time' => absint($data['lead_time'] ?? 7),
            '_fbs_supplier_email' => sanitize_email($data['email'] ?? ''),
            '_fbs_supplier_phone' => sanitize_text_field($data['phone'] ?? ''),
            '_fbs_supplier_address' => sanitize_textarea_field($data['address'] ?? ''),
            '_fbs_supplier_notes' => sanitize_textarea_field($data['notes'] ?? ''),
            '_fbs_supplier_is_active' => absint($data['is_active'] ?? 1),
        ];

        foreach ($meta_fields as $key => $value) {
            update_post_meta($supplier_id, $key, $value);
        }

        // Sync to table
        $this->sync_supplier_to_table($supplier_id);

        return true;
    }

    /**
     * Delete supplier
     *
     * @param int $supplier_id The supplier ID
     * @return bool
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function delete_supplier($supplier_id)
    {
        // Delete from custom table
        global $wpdb;
        $suppliers_table = fbs_stockmind_get_table_name('suppliers');
        $wpdb->delete($suppliers_table, ['id' => $supplier_id], ['%d']);

        // Delete post
        return wp_delete_post($supplier_id, true) !== false;
    }

    /**
     * Get products associated with a supplier
     *
     * @param int $supplier_id The supplier ID
     * @return array
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function get_supplier_products($supplier_id)
    {
        $products = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_fbs_stockmind_supplier_id',
                    'value' => $supplier_id,
                    'compare' => '=',
                ],
            ],
            'numberposts' => -1,
        ]);

        return $products;
    }
}
