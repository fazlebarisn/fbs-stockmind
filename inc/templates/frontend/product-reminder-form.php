<?php
/**
 * Frontend inline shortcode/product reminder form template
 *
 * @package fbs-stockmind
 * @since   1.1.0
 * @author  Fazle Bari <fazle@fbsfusion.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-inline-reminder">
    <h4 class="fbs-inline-reminder__title">
        <span class="fbs-reminder-icon">🔔</span>
        <?php esc_html_e('Want a reminder when it\'s time to reorder?', 'fbs-stockmind'); ?>
    </h4>
    
    <div class="fbs-reminder-products">
        <?php foreach ($replenishable_products as $fbs_stockmind_product_data) : ?>
            <div class="fbs-reminder-product fbs-reminder-product--compact" data-product-id="<?php echo esc_attr($fbs_stockmind_product_data['id']); ?>">
                <div class="fbs-product-actions fbs-product-actions--inline">
                    <?php if (empty($customer_email)) : ?>
                        <div class="fbs-reminder-email-wrap fbs-reminder-email-wrap--inline">
                            <input type="email" class="fbs-reminder-email-input" placeholder="<?php esc_attr_e('Your email address', 'fbs-stockmind'); ?>" />
                        </div>
                    <?php endif; ?>
                    <button type="button" 
                            class="fbs-btn fbs-btn-primary fbs-set-reminder" 
                            data-product-id="<?php echo esc_attr($fbs_stockmind_product_data['id']); ?>"
                            data-order-id="<?php echo esc_attr($order_id); ?>"
                            data-customer-email="<?php echo esc_attr($customer_email); ?>">
                        <span class="fbs-btn-icon">🔔</span>
                        <?php esc_html_e('Enable Reminder', 'fbs-stockmind'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
