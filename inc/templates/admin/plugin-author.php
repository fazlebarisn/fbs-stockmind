<?php
/**
 * Meet The Author & FBS Fusion Studio Admin Template.
 *
 * @package fbs-stockmind
 * @since   1.3.0
 * @author  Fazle Bari <fazle@fbsfusion.com>
 */

defined('ABSPATH') || die('Nice Try!');

$fbs_stockmind_author_img  = FBS_STOCKMIND_URL . '/assets/img/fazle-bari-profile.jpg';
$fbs_stockmind_home_url    = fbs_stockmind_utm_url('', 'author-page', 'hero-visit-site');
$fbs_stockmind_hire_url    = fbs_stockmind_utm_url('hire-us/', 'agency-services', 'author-page-cta');
$fbs_stockmind_speed_url   = fbs_stockmind_utm_url('speed-security/', 'agency-services', 'author-page-speed');
$fbs_stockmind_plugins_url = fbs_stockmind_utm_url('plugins/', 'pro-upgrade', 'author-page-plugins', 'fbs-stockmind');
?>
<div class="wrap fbs-author-wrap">
    <div class="fbs-author-hero">
        <div>
            <span class="fbs-author-hero__eyebrow">
                <?php esc_html_e('Official Plugin Creator & WooCommerce Engineering Studio', 'fbs-stockmind'); ?>
            </span>
            <h1><?php esc_html_e('Meet Fazle Bari & FBS Fusion', 'fbs-stockmind'); ?></h1>
            <p>
                <?php esc_html_e('Senior WordPress, WooCommerce & Laravel engineering — building conversion-focused plugins and sub-second, fortress-grade online stores.', 'fbs-stockmind'); ?>
            </p>
        </div>
        <div class="fbs-author-hero__actions">
            <a href="https://profiles.wordpress.org/fazlebari/" target="_blank" rel="noopener noreferrer" class="fbs-author-btn fbs-author-btn--glass">
                <span class="dashicons dashicons-wordpress-alt"></span>
                <?php esc_html_e('WP.org Profile ↗', 'fbs-stockmind'); ?>
            </a>
            <a href="<?php echo esc_url($fbs_stockmind_home_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-author-btn fbs-author-btn--emerald">
                <?php esc_html_e('Visit FBSFusion.com ↗', 'fbs-stockmind'); ?>
            </a>
        </div>
    </div>

    <div class="fbs-author-grid">
        <aside class="fbs-author-card">
            <div class="fbs-author-photo-wrap">
                <img
                    src="<?php echo esc_url($fbs_stockmind_author_img); ?>"
                    alt="<?php esc_attr_e('Fazle Bari — Lead Architect at FBS Fusion', 'fbs-stockmind'); ?>"
                    width="268"
                    height="268"
                />
            </div>

            <span class="fbs-author-community-pill">
                <?php esc_html_e('● WordPress Community Contributor', 'fbs-stockmind'); ?>
            </span>

            <h2><?php esc_html_e('Fazle Bari', 'fbs-stockmind'); ?></h2>
            <p class="fbs-author-role"><?php esc_html_e('Lead Architect, FBS Fusion', 'fbs-stockmind'); ?></p>

            <div class="fbs-author-meta">
                <div class="fbs-author-meta__item">
                    <span class="dashicons dashicons-email-alt"></span>
                    <a href="mailto:fazle@fbsfusion.com">fazle@fbsfusion.com</a>
                </div>
                <div class="fbs-author-meta__item">
                    <span class="dashicons dashicons-admin-site-alt3"></span>
                    <a href="<?php echo esc_url($fbs_stockmind_home_url); ?>" target="_blank" rel="noopener noreferrer">fbsfusion.com</a>
                </div>
                <div class="fbs-author-meta__item">
                    <span class="dashicons dashicons-wordpress"></span>
                    <a href="https://profiles.wordpress.org/fazlebari/" target="_blank" rel="noopener noreferrer">profiles.wordpress.org/fazlebari</a>
                </div>
                <div class="fbs-author-meta__item">
                    <span class="dashicons dashicons-location"></span>
                    <span><?php esc_html_e('Dhaka, Bangladesh (Global Remote)', 'fbs-stockmind'); ?></span>
                </div>
            </div>

            <div class="fbs-author-socials">
                <a href="https://github.com/fazlebarisn" target="_blank" rel="noopener noreferrer" class="fbs-author-social" aria-label="GitHub" title="GitHub">
                    <svg role="img" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                </a>
                <a href="https://www.linkedin.com/in/fazle-bari/" target="_blank" rel="noopener noreferrer" class="fbs-author-social" aria-label="LinkedIn" title="LinkedIn">
                    <svg role="img" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0z"/></svg>
                </a>
                <a href="https://profiles.wordpress.org/fazlebari/" target="_blank" rel="noopener noreferrer" class="fbs-author-social" aria-label="WordPress.org" title="WordPress.org Profile">
                    <svg role="img" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zM1.807 12c0-1.48.318-2.885.886-4.156l4.885 13.385A10.203 10.203 0 011.807 12zm10.193 10.193c-1.001 0-1.968-.147-2.883-.417l3.06-8.895 3.136 8.591c.021.051.046.097.074.142a10.155 10.155 0 01-3.387.579zm1.404-14.99c.614-.032 1.167-.097 1.167-.097.55-.065.485-.872-.065-.84 0 0-1.652.13-2.717.13-1.002 0-2.685-.13-2.685-.13-.55-.032-.614.808-.065.84 0 0 .52.065 1.069.097l1.589 4.354-2.232 6.693-3.713-11.047c.615-.032 1.168-.097 1.168-.097.549-.065.484-.872-.065-.84 0 0-1.653.13-2.718.13-.191 0-.416-.005-.655-.013A10.184 10.184 0 0112 1.807c2.655 0 5.072 1.015 6.887 2.677-.044-.003-.086-.009-.131-.009-1.002 0-1.712.872-1.712 1.809 0 .84.485 1.551 1.002 2.391.388.679.84 1.551.84 2.811 0 .872-.335 1.883-.776 3.295l-1.018 3.4-3.688-10.978zm5.069 13.797l3.081-8.898c.576-1.44.768-2.592.768-3.616 0-.372-.024-.716-.068-1.038a10.168 10.168 0 01-.002 8.708 10.214 10.214 0 01-3.779 4.844z"/></svg>
                </a>
                <a href="https://www.facebook.com/fbsfusion" target="_blank" rel="noopener noreferrer" class="fbs-author-social" aria-label="Facebook" title="FBS Fusion on Facebook">
                    <svg role="img" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
            </div>
        </aside>

        <div class="fbs-author-main">
            <div class="fbs-author-stats">
                <div class="fbs-author-stat">
                    <strong>6+</strong>
                    <span><?php esc_html_e('Official WP.org Plugins', 'fbs-stockmind'); ?></span>
                </div>
                <div class="fbs-author-stat">
                    <strong>95–100</strong>
                    <span><?php esc_html_e('Lighthouse PageSpeed Target', 'fbs-stockmind'); ?></span>
                </div>
                <div class="fbs-author-stat">
                    <strong>Grade A+</strong>
                    <span><?php esc_html_e('HTTP Security & Malware Defense', 'fbs-stockmind'); ?></span>
                </div>
            </div>

            <div class="fbs-author-block">
                <h3><?php esc_html_e('About Fazle Bari & FBS Fusion', 'fbs-stockmind'); ?></h3>
                <p>
                    <?php esc_html_e('Founded in October 2024 by Lead Architect Fazle Bari, FBS Fusion is a forward-thinking software studio specializing in WordPress, WooCommerce, and Laravel engineering. Born from a passion for seamless user experiences and high-performance functionality, FBS Fusion helps businesses—from growing stores to high-volume enterprises—build smarter, faster, and more resilient digital commerce.', 'fbs-stockmind'); ?>
                </p>
                <p>
                    <?php esc_html_e('At its core, FBS Fusion believes in fusion—the blend of clean architecture, intuitive design, and AI innovation. Every plugin we ship strictly follows WordPress Coding Standards (WPCS) and is engineered to solve real operational bottlenecks without bloating your store.', 'fbs-stockmind'); ?>
                </p>
            </div>

            <div class="fbs-author-block">
                <h3><?php esc_html_e('How FBS Fusion Can Help Your Store', 'fbs-stockmind'); ?></h3>
                <div class="fbs-service-cards">
                    <div class="fbs-service-card">
                        <h4>⚡ <?php esc_html_e('WooCommerce Speed & 502 Rescue', 'fbs-stockmind'); ?></h4>
                        <p><?php esc_html_e('Sub-second TTFB & LCP, Redis/MySQL query tuning, and eliminating 502 Bad Gateway crashes during high-traffic sales.', 'fbs-stockmind'); ?></p>
                    </div>
                    <div class="fbs-service-card">
                        <h4>🛡️ <?php esc_html_e('Malware Eradication & Hardening', 'fbs-stockmind'); ?></h4>
                        <p><?php esc_html_e('Deep forensic backdoor removal, database cleanup, Grade A+ security headers, and zero-downtime store recovery.', 'fbs-stockmind'); ?></p>
                    </div>
                    <div class="fbs-service-card">
                        <h4>🧩 <?php esc_html_e('Custom WooCommerce & AI Dev', 'fbs-stockmind'); ?></h4>
                        <p><?php esc_html_e('Bespoke WooCommerce plugins, ERP/CRM API integrations, HPOS migrations, and custom AI inventory workflows.', 'fbs-stockmind'); ?></p>
                    </div>
                </div>
            </div>

            <div class="fbs-author-block">
                <h3><?php esc_html_e('Core Engineering Specializations', 'fbs-stockmind'); ?></h3>
                <div class="fbs-skill-pills">
                    <span class="fbs-skill-pill"><?php esc_html_e('WooCommerce Plugin Engineering', 'fbs-stockmind'); ?></span>
                    <span class="fbs-skill-pill"><?php esc_html_e('Core Web Vitals & Sub-Second LCP', 'fbs-stockmind'); ?></span>
                    <span class="fbs-skill-pill"><?php esc_html_e('Malware Forensics & WAF Security', 'fbs-stockmind'); ?></span>
                    <span class="fbs-skill-pill"><?php esc_html_e('PHP 8.x & Laravel SaaS Backends', 'fbs-stockmind'); ?></span>
                    <span class="fbs-skill-pill"><?php esc_html_e('REST APIs, Webhooks & AI Integrations', 'fbs-stockmind'); ?></span>
                    <span class="fbs-skill-pill"><?php esc_html_e('MySQL & Autoload Optimization', 'fbs-stockmind'); ?></span>
                </div>
            </div>

            <div class="fbs-author-cta-box">
                <div>
                    <h4><?php esc_html_e('Want a Free Staging Speed & Security Audit for Your Store?', 'fbs-stockmind'); ?></h4>
                    <p><?php esc_html_e('Work directly with Fazle Bari and the FBS Fusion team for guaranteed PageSpeed optimization, malware cleanup, or StockMind Pro.', 'fbs-stockmind'); ?></p>
                </div>
                <div class="fbs-author-cta-box__btns">
                    <a href="<?php echo esc_url($fbs_stockmind_plugins_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-author-btn fbs-author-btn--outline">
                        <?php esc_html_e('Explore StockMind Pro ↗', 'fbs-stockmind'); ?>
                    </a>
                    <a href="<?php echo esc_url($fbs_stockmind_speed_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-author-btn fbs-author-btn--outline">
                        <?php esc_html_e('Speed & Security ↗', 'fbs-stockmind'); ?>
                    </a>
                    <a href="<?php echo esc_url($fbs_stockmind_hire_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-author-btn fbs-author-btn--indigo">
                        <?php esc_html_e('Request Free Audit / Hire Us ↗', 'fbs-stockmind'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
