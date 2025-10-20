<?php
/**
 * Email reminder template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($subject); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .product-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 6px;
            object-fit: cover;
        }
        .product-details h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .product-details p {
            margin: 5px 0;
            color: #6c757d;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .unsubscribe-link {
            color: #6c757d;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .product-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔄 <?php esc_html_e('Time to Reorder!', 'fbs-stockmind'); ?></h1>
            <p><?php esc_html_e('Your smart replenishment reminder', 'fbs-stockmind'); ?></p>
        </div>
        
        <div class="email-body">
            <p><?php esc_html_e('Hello!', 'fbs-stockmind'); ?></p>
            
            <p><?php esc_html_e('Based on your previous purchase patterns, it looks like you might be running low on one of your favorite products. We thought you\'d like to know!', 'fbs-stockmind'); ?></p>
            
            <div class="product-card">
                <?php if ($product_image): ?>
                    <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_name); ?>" class="product-image" />
                <?php endif; ?>
                
                <div class="product-details">
                    <h3><?php echo esc_html($product_name); ?></h3>
                    <p><strong><?php esc_html_e('Price:', 'fbs-stockmind'); ?></strong> <?php echo wp_kses_post($product_price); ?></p>
                    <p><?php esc_html_e('This product is predicted to run out of stock soon based on your purchase history.', 'fbs-stockmind'); ?></p>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="<?php echo esc_url($product_url); ?>" class="cta-button">
                    <?php esc_html_e('Order Now', 'fbs-stockmind'); ?>
                </a>
            </div>
            
            <p><?php esc_html_e('Don\'t worry if you\'re not ready to order yet - we\'ll send you another reminder if needed. You can also manage your reminders anytime from your account page.', 'fbs-stockmind'); ?></p>
            
            <p><?php esc_html_e('Thanks for being a valued customer!', 'fbs-stockmind'); ?></p>
            
            <p>
                <?php esc_html_e('Best regards,', 'fbs-stockmind'); ?><br>
                <?php echo esc_html(get_bloginfo('name')); ?>
            </p>
        </div>
        
        <div class="email-footer">
            <p>
                <?php esc_html_e('This email was sent because you requested a replenishment reminder for this product.', 'fbs-stockmind'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="unsubscribe-link">
                    <?php esc_html_e('Manage your reminders', 'fbs-stockmind'); ?>
                </a>
            </p>
        </div>
    </div>
</body>
</html>
