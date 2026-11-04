<?php
/**
 * Email reminder template (inline styles for universal email client & WP.org compliance)
 *
 * @package fbs-stockmind
 * @since   1.0.0
 * @author  Fazle Bari <fazle@fbsfusion.com>
 */

defined('ABSPATH') or die('Nice Try!');

$fbs_sm_email_subject = !empty($subject) ? $subject : __('Time to Reorder!', 'fbs-stockmind');
$fbs_sm_unsub_link    = !empty($unsubscribe_url) ? $unsubscribe_url : home_url('/');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($fbs_sm_email_subject); ?></title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px 16px; background-color: #f1f5f9;">
    <div style="background-color: #ffffff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 60%, #0284c7 100%); color: #ffffff; padding: 30px 28px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em;">
                🔄 <?php esc_html_e('Time to Reorder!', 'fbs-stockmind'); ?>
            </h1>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #cbd5e1;">
                <?php esc_html_e('Your smart replenishment reminder from', 'fbs-stockmind'); ?> <?php echo esc_html(get_bloginfo('name')); ?>
            </p>
        </div>

        <div style="padding: 30px 28px;">
            <p style="margin: 0 0 14px 0; font-size: 15px; color: #334155;">
                <?php esc_html_e('Hello!', 'fbs-stockmind'); ?>
            </p>

            <p style="margin: 0 0 22px 0; font-size: 15px; color: #334155;">
                <?php esc_html_e('Based on your previous order, you might be running low on one of your favorite items soon. Reorder now so you never miss a day!', 'fbs-stockmind'); ?>
            </p>

            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #f8fafc; margin: 0 0 24px 0;">
                <tr>
                    <?php if (!empty($product_image)) : ?>
                        <td width="96" style="padding: 18px 0 18px 18px; vertical-align: middle;">
                            <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_name); ?>" width="80" height="80" style="display: block; width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 1px solid #e2e8f0;" />
                        </td>
                    <?php endif; ?>
                    <td style="padding: 18px; vertical-align: middle;">
                        <h3 style="margin: 0 0 6px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
                            <?php echo esc_html($product_name); ?>
                        </h3>
                        <?php if (!empty($product_price)) : ?>
                            <p style="margin: 0 0 6px 0; font-size: 14px; color: #0284c7; font-weight: 700;">
                                <?php echo wp_kses_post($product_price); ?>
                            </p>
                        <?php endif; ?>
                        <p style="margin: 0; font-size: 13px; color: #64748b;">
                            <?php esc_html_e('Ready for fast dispatch while in stock.', 'fbs-stockmind'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <div style="text-align: center; margin: 26px 0;">
                <a href="<?php echo esc_url($product_url); ?>" style="display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 13px 28px; border-radius: 9px; font-size: 15px; font-weight: 700;">
                    <?php esc_html_e('Reorder Product Now →', 'fbs-stockmind'); ?>
                </a>
            </div>

            <p style="margin: 0 0 14px 0; font-size: 14px; color: #475569;">
                <?php esc_html_e('Thank you for shopping with us!', 'fbs-stockmind'); ?>
            </p>

            <p style="margin: 0; font-size: 14px; color: #0f172a; font-weight: 600;">
                <?php echo esc_html(get_bloginfo('name')); ?>
            </p>
        </div>

        <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 28px; text-align: center; color: #64748b; font-size: 12.5px;">
            <p style="margin: 0 0 8px 0;">
                <?php esc_html_e('You received this email because you opted into a replenishment reminder on our store.', 'fbs-stockmind'); ?>
            </p>
            <p style="margin: 0;">
                <a href="<?php echo esc_url($fbs_sm_unsub_link); ?>" style="color: #475569; text-decoration: underline;">
                    <?php esc_html_e('Unsubscribe from this reminder', 'fbs-stockmind'); ?>
                </a>
            </p>
        </div>
    </div>
</body>
</html>
