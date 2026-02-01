<?php
/**
 * Product meta box template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-product-meta-box">
    <div class="fbs-meta-field">
        <label for="fbs_stockmind_replenishable" class="fbs-meta-label">
            <input type="checkbox" 
                   id="fbs_stockmind_replenishable" 
                   name="fbs_stockmind_replenishable" 
                   value="1" 
                   <?php checked($is_replenishable); ?> 
                   class="fbs-meta-checkbox" />
            <?php esc_html_e('Enable Customer Reminders', 'fbs-stockmind'); ?>
        </label>
        <p class="fbs-meta-description">
            <?php esc_html_e('Allow customers to set up replenishment reminders for this product', 'fbs-stockmind'); ?>
        </p>
    </div>

    <div class="fbs-meta-field">
        <label for="fbs_stockmind_supplier_id" class="fbs-meta-label">
            <?php esc_html_e('Supplier', 'fbs-stockmind'); ?>
        </label>
        <select id="fbs_stockmind_supplier_id" 
                name="fbs_stockmind_supplier_id" 
                class="fbs-meta-select">
            <option value=""><?php esc_html_e('Select Supplier', 'fbs-stockmind'); ?></option>
            <?php foreach ($suppliers as $fbs_stockmind_supplier): ?>
                <option value="<?php echo esc_attr($fbs_stockmind_supplier->id); ?>" 
                        <?php selected($supplier_id, $fbs_stockmind_supplier->id); ?>>
                    <?php echo esc_html($fbs_stockmind_supplier->name); ?>
                    (<?php echo esc_html($fbs_stockmind_supplier->lead_time); ?> <?php esc_html_e('days', 'fbs-stockmind'); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <p class="fbs-meta-description">
            <?php esc_html_e('Select the supplier for this product to calculate accurate lead times', 'fbs-stockmind'); ?>
        </p>
    </div>

    <div class="fbs-meta-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-suppliers')); ?>" 
           class="fbs-meta-link">
            <?php esc_html_e('Manage Suppliers', 'fbs-stockmind'); ?>
        </a>
    </div>
</div>
