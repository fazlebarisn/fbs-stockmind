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
                <?php foreach ($replenishable_products as $product_data): ?>
                    <?php
                    $product = wc_get_product($product_data['id']);
                    if (!$product) continue;
                    
                    $product_image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                    $product_url = get_permalink($product_data['id']);
                    ?>
                    <div class="fbs-reminder-product" data-product-id="<?php echo esc_attr($product_data['id']); ?>">
                        <div class="fbs-product-image">
                            <?php if ($product_image): ?>
                                <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_data['name']); ?>" />
                            <?php else: ?>
                                <div class="fbs-no-image">📦</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="fbs-product-details">
                            <h4 class="fbs-product-name">
                                <a href="<?php echo esc_url($product_url); ?>" target="_blank">
                                    <?php echo esc_html($product_data['name']); ?>
                                </a>
                            </h4>
                            <p class="fbs-product-quantity">
                                <?php 
                                printf(
                                    /* translators: %d: Product quantity */
                                    esc_html(_n('Quantity: %d', 'Quantity: %d', absint($product_data['quantity']), 'fbs-stockmind')),
                                    absint($product_data['quantity'])
                                ); ?>
                            </p>
                        </div>
                        
                        <div class="fbs-product-actions">
                            <button type="button" 
                                    class="fbs-btn fbs-btn-primary fbs-set-reminder" 
                                    data-product-id="<?php echo esc_attr($product_data['id']); ?>"
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

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Show reminder form after a short delay
    setTimeout(function() {
        $('#fbs-reminder-form').fadeIn(300);
    }, 2000);
    
    // Close form handlers
    $('.fbs-reminder-close, .fbs-reminder-overlay').on('click', function() {
        $('#fbs-reminder-form').fadeOut(300);
    });
    
    // Set reminder handler
    $('.fbs-set-reminder').on('click', function() {
        var $button = $(this);
        var productId = $button.data('product-id');
        var orderId = $button.data('order-id');
        var customerEmail = $button.data('customer-email');
        
        // Disable button and show loading
        $button.prop('disabled', true).html('<span class="fbs-btn-icon">⏳</span> <?php esc_html_e('Setting...', 'fbs-stockmind'); ?>');
        
        // Make AJAX request
        $.ajax({
            url: fbsStockMind.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fbs_stockmind_ajax',
                action_type: 'set_reminder',
                nonce: fbsStockMind.nonce,
                customer_email: customerEmail,
                product_id: productId,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    $button.html('<span class="fbs-btn-icon">✅</span> <?php esc_html_e('Reminder Set!', 'fbs-stockmind'); ?>');
                    $button.removeClass('fbs-btn-primary').addClass('fbs-btn-success');
                    
                    // Show success message
                    if (typeof fbsStockMind.showToast === 'function') {
                        fbsStockMind.showToast('success', response.data);
                    }
                } else {
                    $button.prop('disabled', false).html('<span class="fbs-btn-icon">🔔</span> <?php esc_html_e('Enable Reminder', 'fbs-stockmind'); ?>');
                    
                    // Show error message
                    if (typeof fbsStockMind.showToast === 'function') {
                        fbsStockMind.showToast('error', response.data);
                    }
                }
            },
            error: function() {
                $button.prop('disabled', false).html('<span class="fbs-btn-icon">🔔</span> <?php esc_html_e('Enable Reminder', 'fbs-stockmind'); ?>');
                
                // Show error message
                if (typeof fbsStockMind.showToast === 'function') {
                    fbsStockMind.showToast('error', fbsStockMind.strings.error);
                }
            }
        });
    });
});
</script>
