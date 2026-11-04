<?php
/**
 * Official FBS Fusion Plugin Suite admin template.
 *
 * @package fbs-stockmind
 * @since   1.3.0
 * @author  Fazle Bari <fazle@fbsfusion.com>
 */

defined('ABSPATH') || die('Nice Try!');

$fbs_stockmind_suite_url   = fbs_stockmind_utm_url('plugins/', 'plugin-cross-promo', 'our-plugins-header');
$fbs_stockmind_speed_url   = fbs_stockmind_utm_url('speed-security/', 'agency-services', 'our-plugins-banner');
$fbs_stockmind_hire_url    = fbs_stockmind_utm_url('hire-us/', 'agency-services', 'our-plugins-hire');
$fbs_stockmind_pro_url     = fbs_stockmind_utm_url('plugins/', 'pro-upgrade', 'our-plugins-card', 'fbs-stockmind');
$fbs_stockmind_faq_pro_url = fbs_stockmind_utm_url('product-faq-for-woocommerce/', 'plugin-cross-promo', 'our-plugins-faq');
?>
<div class="wrap fbs-plugins-wrap">
    <div class="fbs-plugins-header">
        <div>
            <h1><?php esc_html_e('Official FBS Fusion Plugin Suite', 'fbs-stockmind'); ?></h1>
            <p><?php esc_html_e('Explore our WordPress.org & Pro WooCommerce utilities — engineered for sub-second Core Web Vitals, fortress security, intelligent inventory forecasting, and higher store conversions.', 'fbs-stockmind'); ?></p>
        </div>
        <a href="<?php echo esc_url($fbs_stockmind_suite_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-plugins-suite-btn">
            <?php esc_html_e('View Full Suite on FBSFusion.com ↗', 'fbs-stockmind'); ?>
        </a>
    </div>

    <div class="fbs-plugins-grid">

        <!-- Featured: FBS Secure Optimize -->
        <div class="fbs-plugin-card fbs-plugin-card--featured">
            <div class="fbs-plugin-card-header">
                <div class="fbs-plugin-icon-wrap bg-secure">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h3><?php esc_html_e('FBS Secure Optimize', 'fbs-stockmind'); ?></h3>
                    <span class="fbs-plugin-badge"><?php esc_html_e('⚡ Recommended for WooCommerce Speed & Security', 'fbs-stockmind'); ?></span>
                </div>
            </div>
            <div class="fbs-plugin-card-body">
                <p><?php esc_html_e('All-in-one Core Web Vitals & Security suite. Includes full-page HTML caching, 1-click Purge Cache, non-blocking Google Fonts, zero-CLS image dimensions, WAF firewall, login protection, and forensic malware scanning.', 'fbs-stockmind'); ?></p>
            </div>
            <div class="fbs-plugin-card-footer">
                <a href="https://wordpress.org/plugins/fbs-secure-optimize/" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn btn-secure">
                    <?php esc_html_e('Install Free on WP.org ↗', 'fbs-stockmind'); ?>
                </a>
            </div>
        </div>

        <!-- FBS StockMind & Pro -->
        <div class="fbs-plugin-card">
            <div class="fbs-plugin-card-header">
                <div class="fbs-plugin-icon-wrap bg-stockmind">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h3><?php esc_html_e('FBS StockMind', 'fbs-stockmind'); ?></h3>
                    <span class="fbs-plugin-badge">
                        <?php
                        if (fbs_stockmind_is_pro_active()) {
                            esc_html_e('✔ StockMind Pro Active', 'fbs-stockmind');
                        } else {
                            esc_html_e('✔ Installed • Pro Available', 'fbs-stockmind');
                        }
                        ?>
                    </span>
                </div>
            </div>
            <div class="fbs-plugin-card-body">
                <p><?php esc_html_e('AI Inventory Forecasting & Repeat Order Engine for WooCommerce. Predict exact stockout dates, chat with your live inventory via AI, generate PDF/CSV Purchase Orders, and automate customer reorder reminders with coupons.', 'fbs-stockmind'); ?></p>
            </div>
            <div class="fbs-plugin-card-footer">
                <a href="<?php echo esc_url($fbs_stockmind_pro_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn btn-stockmind">
                    <?php esc_html_e('Explore StockMind Pro ↗', 'fbs-stockmind'); ?>
                </a>
            </div>
        </div>

        <!-- Product FAQ for WooCommerce & Pro -->
        <div class="fbs-plugin-card">
            <div class="fbs-plugin-card-header">
                <div class="fbs-plugin-icon-wrap bg-faq">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3><?php esc_html_e('Product FAQ for WooCommerce', 'fbs-stockmind'); ?></h3>
            </div>
            <div class="fbs-plugin-card-body">
                <p><?php esc_html_e('Spec-aware AI FAQ generation, Google FAQPage JSON-LD schema, Gutenberg block, and rich HTML accordions. Upgrade to Pro for Shopper Q&A, Typo-Tolerant Fuzzy Search, Bulk Category AI, and Search Gap Analytics.', 'fbs-stockmind'); ?></p>
            </div>
            <div class="fbs-plugin-card-footer">
                <a href="https://wordpress.org/plugins/product-faq-for-woocommerce/" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn fbs-plugin-btn--outline">
                    <?php esc_html_e('Free on WP.org ↗', 'fbs-stockmind'); ?>
                </a>
                <a href="<?php echo esc_url($fbs_stockmind_faq_pro_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn btn-faq">
                    <?php esc_html_e('Product FAQ Pro ↗', 'fbs-stockmind'); ?>
                </a>
            </div>
        </div>

        <!-- Product Table for Group Products -->
        <div class="fbs-plugin-card">
            <div class="fbs-plugin-card-header">
                <div class="fbs-plugin-icon-wrap bg-table">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="9" y1="3" x2="9" y2="21" stroke="#ffffff" stroke-width="2"/>
                        <line x1="15" y1="3" x2="15" y2="21" stroke="#ffffff" stroke-width="2"/>
                        <line x1="3" y1="9" x2="21" y2="9" stroke="#ffffff" stroke-width="2"/>
                        <line x1="3" y1="15" x2="21" y2="15" stroke="#ffffff" stroke-width="2"/>
                    </svg>
                </div>
                <h3><?php esc_html_e('Product Table For Group Products', 'fbs-stockmind'); ?></h3>
            </div>
            <div class="fbs-plugin-card-body">
                <p><?php esc_html_e('Upgrade WooCommerce grouped products into clean, high-converting comparison tables so shoppers can select quantities and add bundles on a single screen.', 'fbs-stockmind'); ?></p>
            </div>
            <div class="fbs-plugin-card-footer">
                <a href="https://wordpress.org/plugins/product-table-for-group-products/" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn btn-table">
                    <?php esc_html_e('Install Free on WP.org ↗', 'fbs-stockmind'); ?>
                </a>
            </div>
        </div>

        <!-- FBS Activity Tracker -->
        <div class="fbs-plugin-card">
            <div class="fbs-plugin-card-header">
                <div class="fbs-plugin-icon-wrap bg-activity">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3><?php esc_html_e('FBS Activity Tracker', 'fbs-stockmind'); ?></h3>
            </div>
            <div class="fbs-plugin-card-body">
                <p><?php esc_html_e('Real-time WordPress & WooCommerce security audit log. Track admin logins, product price/stock edits, plugin changes, and settings updates in a clean searchable log.', 'fbs-stockmind'); ?></p>
            </div>
            <div class="fbs-plugin-card-footer">
                <a href="https://wordpress.org/plugins/fbs-activity-tracker/" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn btn-activity">
                    <?php esc_html_e('Install Free on WP.org ↗', 'fbs-stockmind'); ?>
                </a>
            </div>
        </div>

        <!-- Export Payout For AffiliateWP -->
        <div class="fbs-plugin-card">
            <div class="fbs-plugin-card-header">
                <div class="fbs-plugin-icon-wrap bg-payout">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="2" x2="16" y2="4" stroke="#ffffff" stroke-width="2"/>
                        <line x1="8" y1="2" x2="8" y2="4" stroke="#ffffff" stroke-width="2"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke="#ffffff" stroke-width="2"/>
                        <path d="M12 14v4M10 16h4" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3><?php esc_html_e('Export Payout For AffiliateWP', 'fbs-stockmind'); ?></h3>
            </div>
            <div class="fbs-plugin-card-body">
                <p><?php esc_html_e('1-click payout export utility for AffiliateWP. Filter affiliate payouts by date, status, or payment method and export clean CSV/Excel bookkeeping reports.', 'fbs-stockmind'); ?></p>
            </div>
            <div class="fbs-plugin-card-footer">
                <a href="https://wordpress.org/plugins/export-payout-for-affiliatewp/" target="_blank" rel="noopener noreferrer" class="fbs-plugin-btn btn-payout">
                    <?php esc_html_e('Install Free on WP.org ↗', 'fbs-stockmind'); ?>
                </a>
            </div>
        </div>

    </div>

    <!-- Studio Services Callout -->
    <div class="fbs-plugins-services-callout">
        <div class="fbs-plugins-services-callout__copy">
            <h2><?php esc_html_e('Need Guaranteed 95–100 WooCommerce Speed, Malware Cleanup, or Custom Development?', 'fbs-stockmind'); ?></h2>
            <p><?php esc_html_e('FBS Fusion engineers high-concurrency WooCommerce stores, fixes slow TTFB & 502 Bad Gateway crashes under heavy traffic, eradicates hidden backdoors, and builds custom WordPress & Laravel plugins.', 'fbs-stockmind'); ?></p>
        </div>
        <div class="fbs-plugins-services-callout__actions">
            <a href="<?php echo esc_url($fbs_stockmind_speed_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-plugins-callout-btn fbs-plugins-callout-btn--emerald">
                <?php esc_html_e('⚡ Speed & Security Services ↗', 'fbs-stockmind'); ?>
            </a>
            <a href="<?php echo esc_url($fbs_stockmind_hire_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-plugins-callout-btn fbs-plugins-callout-btn--glass">
                <?php esc_html_e('Request Free Store Audit ↗', 'fbs-stockmind'); ?>
            </a>
        </div>
    </div>
</div>
