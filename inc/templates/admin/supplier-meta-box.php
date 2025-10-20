<?php
/**
 * Supplier meta box template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-supplier-meta-box">
    <div class="fbs-meta-grid">
        <div class="fbs-meta-field">
            <label for="fbs_supplier_lead_time" class="fbs-meta-label">
                <?php esc_html_e('Lead Time (Days)', 'fbs-stockmind'); ?>
            </label>
            <input type="number" 
                   id="fbs_supplier_lead_time" 
                   name="_fbs_supplier_lead_time" 
                   value="<?php echo esc_attr($lead_time); ?>" 
                   min="1" 
                   max="365" 
                   class="fbs-meta-input" />
            <p class="fbs-meta-description">
                <?php esc_html_e('Average time needed to restock products from this supplier', 'fbs-stockmind'); ?>
            </p>
        </div>

        <div class="fbs-meta-field">
            <label for="fbs_supplier_email" class="fbs-meta-label">
                <?php esc_html_e('Email Address', 'fbs-stockmind'); ?>
            </label>
            <input type="email" 
                   id="fbs_supplier_email" 
                   name="_fbs_supplier_email" 
                   value="<?php echo esc_attr($email); ?>" 
                   class="fbs-meta-input" />
            <p class="fbs-meta-description">
                <?php esc_html_e('Primary contact email for this supplier', 'fbs-stockmind'); ?>
            </p>
        </div>

        <div class="fbs-meta-field">
            <label for="fbs_supplier_phone" class="fbs-meta-label">
                <?php esc_html_e('Phone Number', 'fbs-stockmind'); ?>
            </label>
            <input type="tel" 
                   id="fbs_supplier_phone" 
                   name="_fbs_supplier_phone" 
                   value="<?php echo esc_attr($phone); ?>" 
                   class="fbs-meta-input" />
            <p class="fbs-meta-description">
                <?php esc_html_e('Contact phone number for this supplier', 'fbs-stockmind'); ?>
            </p>
        </div>

        <div class="fbs-meta-field">
            <label for="fbs_supplier_is_active" class="fbs-meta-label">
                <input type="checkbox" 
                       id="fbs_supplier_is_active" 
                       name="_fbs_supplier_is_active" 
                       value="1" 
                       <?php checked($is_active); ?> 
                       class="fbs-meta-checkbox" />
                <?php esc_html_e('Active Supplier', 'fbs-stockmind'); ?>
            </label>
            <p class="fbs-meta-description">
                <?php esc_html_e('Uncheck to disable this supplier without deleting', 'fbs-stockmind'); ?>
            </p>
        </div>
    </div>

    <div class="fbs-meta-field">
        <label for="fbs_supplier_address" class="fbs-meta-label">
            <?php esc_html_e('Address', 'fbs-stockmind'); ?>
        </label>
        <textarea id="fbs_supplier_address" 
                  name="_fbs_supplier_address" 
                  rows="3" 
                  class="fbs-meta-textarea"><?php echo esc_textarea($address); ?></textarea>
        <p class="fbs-meta-description">
            <?php esc_html_e('Physical address or shipping information', 'fbs-stockmind'); ?>
        </p>
    </div>

    <div class="fbs-meta-field">
        <label for="fbs_supplier_notes" class="fbs-meta-label">
            <?php esc_html_e('Notes', 'fbs-stockmind'); ?>
        </label>
        <textarea id="fbs_supplier_notes" 
                  name="_fbs_supplier_notes" 
                  rows="4" 
                  class="fbs-meta-textarea"><?php echo esc_textarea($notes); ?></textarea>
        <p class="fbs-meta-description">
            <?php esc_html_e('Additional notes about this supplier (minimum order quantities, special terms, etc.)', 'fbs-stockmind'); ?>
        </p>
    </div>
</div>
