<?php
/**
 * Frontend reminder form template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div id="fbs-reminder-form" class="fbs-reminder-form" style="display: none;">
    <div class="fbs-reminder-overlay"></div>
    <div class="fbs-reminder-content">
        <div class="fbs-reminder-header">
            <h3 class="fbs-reminder-title">
                <span class="fbs-reminder-icon">🔄</span>
                <?php esc_html_e('Smart Replenishment Reminders', 'fbs-stockmind'); ?>
            </h3>
            <button type="button" class="fbs-reminder-close">&times;</button>
        </div>
        
        <div class="fbs-reminder-body">
            <p class="fbs-reminder-description">
                <?php esc_html_e('Never run out of your favorite products! Set up smart reminders and we\'ll notify you when it\'s time to reorder.', 'fbs-stockmind'); ?>
            </p>
            
            <div class="fbs-reminder-products">
                <?php foreach ($replenishable_products as $fbs_stockmind_product_data): ?>
                    <?php
                    $fbs_stockmind_product = wc_get_product($fbs_stockmind_product_data['id']);
                    if (!$fbs_stockmind_product) continue;
                    
                    $fbs_stockmind_product_image = wp_get_attachment_image_url($fbs_stockmind_product->get_image_id(), 'thumbnail');
                    $fbs_stockmind_product_url = get_permalink($fbs_stockmind_product_data['id']);
                    ?>
                    <div class="fbs-reminder-product" data-product-id="<?php echo esc_attr($fbs_stockmind_product_data['id']); ?>">
                        <div class="fbs-product-image">
                            <?php if ($fbs_stockmind_product_image): ?>
                                <img src="<?php echo esc_url($fbs_stockmind_product_image); ?>" alt="<?php echo esc_attr($fbs_stockmind_product_data['name']); ?>" />
                            <?php else: ?>
                                <div class="fbs-no-image">📦</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="fbs-product-details">
                            <h4 class="fbs-product-name">
                                <a href="<?php echo esc_url($fbs_stockmind_product_url); ?>" target="_blank">
                                    <?php echo esc_html($fbs_stockmind_product_data['name']); ?>
                                </a>
                            </h4>
                            <p class="fbs-product-quantity">
                                <?php 
                                printf(
                                    /* translators: %d: Product quantity */
                                    esc_html(_n('Quantity: %d', 'Quantity: %d', absint($fbs_stockmind_product_data['quantity']), 'fbs-stockmind')),
                                    absint($fbs_stockmind_product_data['quantity'])
                                ); ?>
                            </p>
                        </div>
                        
                        <div class="fbs-product-actions">
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
            
            <div class="fbs-reminder-footer">
                <p class="fbs-reminder-note">
                    <small>
                        <?php esc_html_e('You can manage your reminders anytime from your account page.', 'fbs-stockmind'); ?>
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>
