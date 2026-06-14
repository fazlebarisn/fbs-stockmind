<?php
/**
 * Frontend inline reminder form template
 *
 * @package fbs-stockmind
 * @since 1.1.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-inline-reminder" style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
    <h4 style="margin-top: 0; margin-bottom: 10px; font-size: 16px;">
        <span class="fbs-reminder-icon">🔔</span>
        <?php esc_html_e('Want a reminder when it\'s time to reorder?', 'fbs-stockmind'); ?>
    </h4>
    
    <div class="fbs-reminder-products">
        <?php foreach ($replenishable_products as $fbs_stockmind_product_data): ?>
            <div class="fbs-reminder-product" data-product-id="<?php echo esc_attr($fbs_stockmind_product_data['id']); ?>" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                
                <div class="fbs-product-actions" style="display: flex; gap: 10px; align-items: center; width: 100%;">
                    <?php if (empty($customer_email)): ?>
                        <div style="flex: 1; min-width: 200px;">
                            <input type="email" class="fbs-reminder-email-input" placeholder="<?php esc_attr_e('Your email address', 'fbs-stockmind'); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" />
                        </div>
                    <?php endif; ?>
                    <button type="button" 
                            class="fbs-btn fbs-btn-primary fbs-set-reminder" 
                            data-product-id="<?php echo esc_attr($fbs_stockmind_product_data['id']); ?>"
                            data-order-id="<?php echo esc_attr($order_id); ?>"
                            data-customer-email="<?php echo esc_attr($customer_email); ?>"
                            style="padding: 8px 15px; white-space: nowrap; border: none; border-radius: 4px; background: #2271b1; color: #fff; cursor: pointer;">
                        <?php esc_html_e('Enable Reminder', 'fbs-stockmind'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
