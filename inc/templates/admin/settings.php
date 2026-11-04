<?php
/**
 * Admin settings template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-stockmind-settings">
    <!-- Header -->
    <div class="fbs-settings-header">
        <h1 class="fbs-settings-title">
            <span class="fbs-title-icon">⚙️</span>
            <?php esc_html_e('StockMind Settings', 'fbs-stockmind'); ?>
        </h1>
        <p class="fbs-settings-subtitle">
            <?php esc_html_e('Configure prediction algorithms, reminder settings, and email preferences', 'fbs-stockmind'); ?>
        </p>
    </div>

    <form method="post" action="" class="fbs-settings-form">
        <?php wp_nonce_field('fbs_stockmind_settings'); ?>
        
        <!-- Settings Tabs -->
        <div class="fbs-settings-tabs">
            <nav class="fbs-tab-nav">
                <button type="button" class="fbs-tab-button active" data-tab="general">
                    <span class="fbs-tab-icon">🔧</span>
                    <?php esc_html_e('General', 'fbs-stockmind'); ?>
                </button>
                <button type="button" class="fbs-tab-button" data-tab="predictions">
                    <span class="fbs-tab-icon">🔮</span>
                    <?php esc_html_e('Predictions', 'fbs-stockmind'); ?>
                </button>
                <button type="button" class="fbs-tab-button" data-tab="reminders">
                    <span class="fbs-tab-icon">📧</span>
                    <?php esc_html_e('Reminders', 'fbs-stockmind'); ?>
                </button>
                <button type="button" class="fbs-tab-button" data-tab="email">
                    <span class="fbs-tab-icon">✉️</span>
                    <?php esc_html_e('Email', 'fbs-stockmind'); ?>
                </button>
                <button type="button" class="fbs-tab-button" data-tab="ai">
                    <span class="fbs-tab-icon">🤖</span>
                    <?php esc_html_e('AI Assistant', 'fbs-stockmind'); ?>
                </button>
                <?php if (!function_exists('fbs_stockmind_pro_is_woocommerce_active')): ?>
                <button type="button" class="fbs-tab-button pro-tab" data-tab="pro" style="color: #f59e0b;">
                    <span class="fbs-tab-icon">⭐</span>
                    <?php esc_html_e('Pro Options', 'fbs-stockmind'); ?>
                </button>
                <?php endif; ?>
                <?php do_action('fbs_stockmind_settings_tabs_nav'); ?>
            </nav>

            <!-- General Settings Tab -->
            <div class="fbs-tab-content active" id="general-tab">
                <div class="fbs-settings-section">
                    <h2 class="fbs-section-title"><?php esc_html_e('General Settings', 'fbs-stockmind'); ?></h2>
                    
                    <div class="fbs-form-group">
                        <label for="alert_window" class="fbs-form-label">
                            <?php esc_html_e('Alert Window (Days)', 'fbs-stockmind'); ?>
                        </label>
                        <input type="number" 
                               id="alert_window" 
                               name="alert_window" 
                               value="<?php echo esc_attr($settings['alert_window']); ?>" 
                               min="1" 
                               max="90" 
                               class="fbs-form-input" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('How many days in advance to show low stock alerts', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-form-group">
                        <label for="default_lead_time" class="fbs-form-label">
                            <?php esc_html_e('Default Lead Time (Days)', 'fbs-stockmind'); ?>
                        </label>
                        <input type="number" 
                               id="default_lead_time" 
                               name="default_lead_time" 
                               value="<?php echo esc_attr($settings['default_lead_time']); ?>" 
                               min="1" 
                               max="365" 
                               class="fbs-form-input" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Default time needed to restock products (can be overridden per supplier)', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-form-group">
                        <label class="fbs-form-label">
                            <input type="checkbox" 
                                   name="enable_admin_alerts" 
                                   value="1" 
                                   <?php checked($settings['enable_admin_alerts']); ?> 
                                   class="fbs-form-checkbox" />
                            <?php esc_html_e('Enable Admin Alerts', 'fbs-stockmind'); ?>
                        </label>
                        <p class="fbs-form-description">
                            <?php esc_html_e('Show low stock alerts in the admin dashboard', 'fbs-stockmind'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Predictions Settings Tab -->
            <div class="fbs-tab-content" id="predictions-tab">
                <div class="fbs-settings-section">
                    <h2 class="fbs-section-title"><?php esc_html_e('Prediction Settings', 'fbs-stockmind'); ?></h2>
                    
                    <?php
                    $fbs_stockmind_settings_class = \FBS_StockMind\Inc\Admin\Settings::get_instance();
                    $fbs_stockmind_prediction_editable = $fbs_stockmind_settings_class->is_prediction_settings_editable();
                    ?>
                    
                    <div class="fbs-form-group">
                        <label for="sales_data_period" class="fbs-form-label">
                            <?php esc_html_e('Sales Data Analysis Period (Days)', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_prediction_editable): ?>
                                <span class="fbs-pro-badge-inline"><?php esc_html_e('PRO', 'fbs-stockmind'); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="sales_data_period" 
                               name="sales_data_period" 
                               value="<?php echo esc_attr($settings['sales_data_period']); ?>" 
                               min="7" 
                               max="365" 
                               <?php echo $fbs_stockmind_prediction_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $fbs_stockmind_prediction_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Number of days of sales data to analyze for predictions.', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_prediction_editable): ?>
                                <span class="fbs-pro-feature-note">
                                    <span class="fbs-pro-note-tag">⭐ <?php esc_html_e('Pro Feature:', 'fbs-stockmind'); ?></span> 
                                    <?php esc_html_e('Fixed at 30 days in the free version.', 'fbs-stockmind'); ?> 
                                    <a href="#" class="fbs-open-pro-tab"><?php esc_html_e('Upgrade to Pro', 'fbs-stockmind'); ?></a> 
                                    <?php esc_html_e('to customize sales analysis windows up to 365 days for seasonal sales patterns.', 'fbs-stockmind'); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="fbs-form-group">
                        <label for="prediction_accuracy_threshold" class="fbs-form-label">
                            <?php esc_html_e('Prediction Accuracy Threshold', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_prediction_editable): ?>
                                <span class="fbs-pro-badge-inline"><?php esc_html_e('PRO', 'fbs-stockmind'); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="prediction_accuracy_threshold" 
                               name="prediction_accuracy_threshold" 
                               value="<?php echo esc_attr($settings['prediction_accuracy_threshold']); ?>" 
                               min="0.1" 
                               max="1.0" 
                               step="0.1" 
                               <?php echo $fbs_stockmind_prediction_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $fbs_stockmind_prediction_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Minimum confidence level required for predictions (0.1 = 10%, 1.0 = 100%).', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_prediction_editable): ?>
                                <span class="fbs-pro-feature-note">
                                    <span class="fbs-pro-note-tag">⭐ <?php esc_html_e('Pro Feature:', 'fbs-stockmind'); ?></span> 
                                    <?php esc_html_e('Preset at 0.3 (30%) in the free version.', 'fbs-stockmind'); ?> 
                                    <a href="#" class="fbs-open-pro-tab"><?php esc_html_e('Upgrade to Pro', 'fbs-stockmind'); ?></a> 
                                    <?php esc_html_e('to fine-tune confidence thresholds and unlock advanced statistical prediction algorithms.', 'fbs-stockmind'); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="fbs-info-box">
                        <h3 class="fbs-info-title">💡 <?php esc_html_e('How Stock Predictions Work', 'fbs-stockmind'); ?></h3>
                        <p class="fbs-info-text">
                            <?php esc_html_e('FBS StockMind continuously forecasts inventory depletion to help prevent stockouts and optimize your reorder timing:', 'fbs-stockmind'); ?>
                        </p>
                        <ul class="fbs-info-list">
                            <?php
                            // Get actual sales data period being used
                            $fbs_stockmind_saved_period = fbs_stockmind_get_option('sales_data_period', 0);
                            if ($fbs_stockmind_saved_period > 0) {
                                $fbs_stockmind_sales_period = $fbs_stockmind_saved_period;
                            } else {
                                $fbs_stockmind_sales_period = apply_filters('fbs_stockmind_sales_data_period', 30, 0);
                            }
                            ?>
                            <li>
                                <strong><?php esc_html_e('Sales Velocity Analysis:', 'fbs-stockmind'); ?></strong> 
                                <?php 
                                printf(
                                    /* translators: %d: Number of days */
                                    esc_html__('Calculates daily burn rate using sales history across your active window (last %d days).', 'fbs-stockmind'),
                                    absint($fbs_stockmind_sales_period)
                                ); 
                                ?>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Supplier Lead Times:', 'fbs-stockmind'); ?></strong> 
                                <?php esc_html_e('Factors in fulfillment lead time to calculate your latest safe purchase date before items run out.', 'fbs-stockmind'); ?>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Confidence Scoring:', 'fbs-stockmind'); ?></strong> 
                                <?php esc_html_e('Scores prediction accuracy (Very Low, Low, Medium, High) based on sales volume, consistency, and recency.', 'fbs-stockmind'); ?>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Critical Stock Protection:', 'fbs-stockmind'); ?></strong> 
                                <?php esc_html_e('Products nearing depletion or within their supplier lead time are immediately surfaced as urgent alerts.', 'fbs-stockmind'); ?>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Instant & Scheduled Sync:', 'fbs-stockmind'); ?></strong> 
                                <?php esc_html_e('Predictions update in real-time on orders and stock edits, as well as scheduled daily background scans.', 'fbs-stockmind'); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Reminders Settings Tab -->
            <div class="fbs-tab-content" id="reminders-tab">
                <div class="fbs-settings-section">
                    <h2 class="fbs-section-title"><?php esc_html_e('Customer Reminder Settings', 'fbs-stockmind'); ?></h2>
                    
                    <?php
                    $fbs_stockmind_settings_class = \FBS_StockMind\Inc\Admin\Settings::get_instance();
                    $fbs_stockmind_reminder_editable = $fbs_stockmind_settings_class->is_reminder_settings_editable();
                    ?>
                    
                    <div class="fbs-form-group">
                        <label class="fbs-form-label">
                            <input type="checkbox" 
                                   name="enable_customer_reminders" 
                                   value="1" 
                                   <?php checked($settings['enable_customer_reminders']); ?> 
                                   class="fbs-form-checkbox" />
                            <?php esc_html_e('Enable Customer Reminders', 'fbs-stockmind'); ?>
                        </label>
                        <p class="fbs-form-description">
                            <?php esc_html_e('Allow customers to set up replenishment reminders', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-form-group">
                        <label for="reminder_advance_days" class="fbs-form-label">
                            <?php esc_html_e('Reminder Advance Days', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_reminder_editable): ?>
                                <span class="fbs-pro-badge-inline"><?php esc_html_e('PRO', 'fbs-stockmind'); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="reminder_advance_days" 
                               name="reminder_advance_days" 
                               value="<?php echo esc_attr($settings['reminder_advance_days']); ?>" 
                               min="1" 
                               max="30" 
                               <?php echo $fbs_stockmind_reminder_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $fbs_stockmind_reminder_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('How many days before predicted runout to send reminders.', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_reminder_editable): ?>
                                <span class="fbs-pro-feature-note">
                                    <span class="fbs-pro-note-tag">⭐ <?php esc_html_e('Pro Feature:', 'fbs-stockmind'); ?></span> 
                                    <?php esc_html_e('Fixed at 5 days in the free version.', 'fbs-stockmind'); ?> 
                                    <a href="#" class="fbs-open-pro-tab"><?php esc_html_e('Upgrade to Pro', 'fbs-stockmind'); ?></a> 
                                    <?php esc_html_e('to configure custom advance reminder schedules (1–30 days) to match product life-cycles.', 'fbs-stockmind'); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="fbs-form-group">
                        <label for="max_reminder_attempts" class="fbs-form-label">
                            <?php esc_html_e('Maximum Reminder Attempts', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_reminder_editable): ?>
                                <span class="fbs-pro-badge-inline"><?php esc_html_e('PRO', 'fbs-stockmind'); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="max_reminder_attempts" 
                               name="max_reminder_attempts" 
                               value="<?php echo esc_attr($settings['max_reminder_attempts']); ?>" 
                               min="1" 
                               max="10" 
                               <?php echo $fbs_stockmind_reminder_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $fbs_stockmind_reminder_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Maximum number of reminder emails to send per customer.', 'fbs-stockmind'); ?>
                            <?php if (!$fbs_stockmind_reminder_editable): ?>
                                <span class="fbs-pro-feature-note">
                                    <span class="fbs-pro-note-tag">⭐ <?php esc_html_e('Pro Feature:', 'fbs-stockmind'); ?></span> 
                                    <?php esc_html_e('Limited to 1 email attempt in the free version.', 'fbs-stockmind'); ?> 
                                    <a href="#" class="fbs-open-pro-tab"><?php esc_html_e('Upgrade to Pro', 'fbs-stockmind'); ?></a> 
                                    <?php esc_html_e('to enable multi-step replenishment sequences (up to 10 automated follow-up emails).', 'fbs-stockmind'); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Email Settings Tab -->
            <div class="fbs-tab-content" id="email-tab">
                <div class="fbs-settings-section">
                    <h2 class="fbs-section-title"><?php esc_html_e('Email Settings', 'fbs-stockmind'); ?></h2>
                    
                    <div class="fbs-form-group">
                        <label for="email_from_name" class="fbs-form-label">
                            <?php esc_html_e('From Name', 'fbs-stockmind'); ?>
                        </label>
                        <input type="text" 
                               id="email_from_name" 
                               name="email_from_name" 
                               value="<?php echo esc_attr($settings['email_from_name']); ?>" 
                               class="fbs-form-input" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Name to use in reminder emails', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-form-group">
                        <label for="email_from_address" class="fbs-form-label">
                            <?php esc_html_e('From Email Address', 'fbs-stockmind'); ?>
                        </label>
                        <input type="email" 
                               id="email_from_address" 
                               name="email_from_address" 
                               value="<?php echo esc_attr($settings['email_from_address']); ?>" 
                               class="fbs-form-input" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Email address to use for reminder emails', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-info-box">
                        <h3 class="fbs-info-title"><?php esc_html_e('Email Templates', 'fbs-stockmind'); ?></h3>
                        <p class="fbs-info-text">
                            <?php esc_html_e('Email templates can be customized by editing the files in the templates/email directory.', 'fbs-stockmind'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- AI Assistant Settings Tab -->
            <div class="fbs-tab-content" id="ai-tab">
                <div class="fbs-settings-section">
                    <h2 class="fbs-section-title" style="display: flex; align-items: center; gap: 8px;">
                        <span>🤖</span> <?php esc_html_e('AI Assistant Settings', 'fbs-stockmind'); ?>
                    </h2>
                    <p style="color: #4b5563; font-size: 14px; margin-bottom: 24px;">
                        <?php esc_html_e('Connect via WordPress Settings ➔ Connectors or use your own Google Gemini / OpenAI API key to analyze inventory health, forecast runout dates, and answer live questions about your WooCommerce catalog.', 'fbs-stockmind'); ?>
                    </p>

                    <?php
                    $fbs_sm_ai_status      = \FBS_StockMind\Inc\Features\AI_Assistant::get_connectors_status();
                    $fbs_sm_wp_ai_ready    = !empty($fbs_sm_ai_status['is_ready']);
                    $fbs_sm_connectors_url = $fbs_sm_ai_status['connectors_url'];
                    $fbs_sm_wp_ai_url      = $fbs_sm_ai_status['wp_ai_url'];
                    $fbs_sm_ai_providers   = $fbs_sm_ai_status['providers'] ?? [];
                    ?>

                    <div class="fbs-form-group">
                        <label for="fbs_stockmind_ai_provider" class="fbs-form-label">
                            <?php esc_html_e('AI Connection Method', 'fbs-stockmind'); ?>
                        </label>
                        <select name="fbs_stockmind_ai_provider" id="fbs_stockmind_ai_provider" class="fbs-form-select" style="max-width: 480px; width: 100%;">
                            <option value="wp_native" <?php selected($settings['ai_provider'], 'wp_native'); ?>>
                                <?php esc_html_e('WordPress Default AI Connector (Settings ➔ Connectors — Recommended)', 'fbs-stockmind'); ?>
                            </option>
                            <option value="gemini" <?php selected($settings['ai_provider'], 'gemini'); ?>>
                                <?php esc_html_e('Google Gemini Direct API Key (Fast & Free Tier)', 'fbs-stockmind'); ?>
                            </option>
                            <option value="openai" <?php selected($settings['ai_provider'], 'openai'); ?>>
                                <?php esc_html_e('OpenAI Direct API Key (GPT-4o mini / GPT-4o)', 'fbs-stockmind'); ?>
                            </option>
                        </select>
                    </div>

                    <!-- WordPress Native AI Connector Status Box -->
                    <div id="fbs-stockmind-wp-native-card" class="fbs-form-group" style="<?php echo 'wp_native' === $settings['ai_provider'] ? '' : 'display:none;'; ?>">
                        <div style="background: #ffffff; border: 1px solid <?php echo $fbs_sm_wp_ai_ready ? '#a7f3d0' : '#fde68a'; ?>; border-radius: 12px; padding: 18px 20px; max-width: 640px; box-shadow: 0 1px 3px rgba(15,23,42,0.04);">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background: <?php echo $fbs_sm_wp_ai_ready ? '#d1fae5' : '#fef3c7'; ?>; font-size: 16px;">
                                        <?php echo $fbs_sm_wp_ai_ready ? '✅' : '⚠️'; ?>
                                    </span>
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">
                                            <?php
                                            if ($fbs_sm_wp_ai_ready) {
                                                esc_html_e('Connected via WordPress AI Connectors', 'fbs-stockmind');
                                            } else {
                                                esc_html_e('No Active WordPress AI Connector Configured', 'fbs-stockmind');
                                            }
                                            ?>
                                        </div>
                                        <div style="font-size: 12.5px; color: #64748b; margin-top: 2px;">
                                            <?php esc_html_e('Uses WordPress native AI Client (wp_ai_client_prompt) & Settings ➔ Connectors.', 'fbs-stockmind'); ?>
                                        </div>
                                    </div>
                                </div>
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: <?php echo $fbs_sm_wp_ai_ready ? '#ecfdf5' : '#fffbeb'; ?>; color: <?php echo $fbs_sm_wp_ai_ready ? '#047857' : '#b45309'; ?>; border: 1px solid <?php echo $fbs_sm_wp_ai_ready ? '#6ee7b7' : '#fcd34d'; ?>;">
                                    <?php echo $fbs_sm_wp_ai_ready ? esc_html__('Ready', 'fbs-stockmind') : esc_html__('Setup Needed', 'fbs-stockmind'); ?>
                                </span>
                            </div>

                            <?php if (!empty($fbs_sm_ai_providers)) : ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 16px 0; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                                    <?php foreach ($fbs_sm_ai_providers as $fbs_prov) : ?>
                                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 6px; font-size: 12px; background: <?php echo !empty($fbs_prov['is_connected']) ? '#f0fdf4' : '#f8fafc'; ?>; border: 1px solid <?php echo !empty($fbs_prov['is_connected']) ? '#bbf7d0' : '#e2e8f0'; ?>; color: #334155;">
                                            <span style="width: 7px; height: 7px; border-radius: 50%; background: <?php echo !empty($fbs_prov['is_connected']) ? '#10b981' : '#94a3b8'; ?>;"></span>
                                            <strong><?php echo esc_html($fbs_prov['name']); ?>:</strong>
                                            <span>
                                                <?php
                                                if (!empty($fbs_prov['is_connected'])) {
                                                    esc_html_e('Connected', 'fbs-stockmind');
                                                } elseif (!empty($fbs_prov['has_key']) && empty($fbs_prov['plugin_active'])) {
                                                    esc_html_e('Provider Plugin Inactive', 'fbs-stockmind');
                                                } else {
                                                    esc_html_e('Not Connected', 'fbs-stockmind');
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                                <a href="<?php echo esc_url($fbs_sm_connectors_url); ?>" class="button button-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                                    <span>⚙️</span> <?php esc_html_e('Manage WordPress Connectors', 'fbs-stockmind'); ?>
                                </a>
                                <button type="button" class="button button-secondary fbs-stockmind-test-ai-btn">
                                    <?php esc_html_e('Test AI Connection', 'fbs-stockmind'); ?>
                                </button>
                                <?php if (!empty($fbs_sm_wp_ai_url)) : ?>
                                    <a href="<?php echo esc_url($fbs_sm_wp_ai_url); ?>" class="button button-link" style="text-decoration: none;">
                                        <?php esc_html_e('WordPress AI Settings ➔', 'fbs-stockmind'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Direct API Key Fields (Gemini / OpenAI) -->
                    <div id="fbs-stockmind-direct-ai-fields" style="<?php echo 'wp_native' === $settings['ai_provider'] ? 'display:none;' : ''; ?>">
                        <div class="fbs-form-group">
                            <label for="fbs_stockmind_ai_model" class="fbs-form-label">
                                <?php esc_html_e('AI Model', 'fbs-stockmind'); ?>
                            </label>
                            <select name="fbs_stockmind_ai_model" id="fbs_stockmind_ai_model" class="fbs-form-select" style="max-width: 480px; width: 100%;">
                                <optgroup label="<?php esc_attr_e('Google Gemini Models', 'fbs-stockmind'); ?>" id="optgroup-gemini">
                                    <option value="gemini-2.5-flash" <?php selected($settings['ai_model'], 'gemini-2.5-flash'); ?>><?php esc_html_e('Gemini 2.5 Flash (Recommended — Fast & Accurate)', 'fbs-stockmind'); ?></option>
                                    <option value="gemini-2.0-flash" <?php selected($settings['ai_model'], 'gemini-2.0-flash'); ?>><?php esc_html_e('Gemini 2.0 Flash', 'fbs-stockmind'); ?></option>
                                    <option value="gemini-2.5-pro" <?php selected($settings['ai_model'], 'gemini-2.5-pro'); ?>><?php esc_html_e('Gemini 2.5 Pro (Advanced Reasoning)', 'fbs-stockmind'); ?></option>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e('OpenAI Models', 'fbs-stockmind'); ?>" id="optgroup-openai">
                                    <option value="gpt-4o-mini" <?php selected($settings['ai_model'], 'gpt-4o-mini'); ?>><?php esc_html_e('GPT-4o mini (Recommended — Fast & Cost-Effective)', 'fbs-stockmind'); ?></option>
                                    <option value="gpt-4o" <?php selected($settings['ai_model'], 'gpt-4o'); ?>><?php esc_html_e('GPT-4o (High Intelligence)', 'fbs-stockmind'); ?></option>
                                </optgroup>
                            </select>
                            <p class="fbs-form-description">
                                <?php esc_html_e('Select which model powers your StockMind AI Inventory Assistant.', 'fbs-stockmind'); ?>
                            </p>
                        </div>

                        <div class="fbs-form-group">
                            <label for="fbs_stockmind_ai_api_key" class="fbs-form-label">
                                <?php esc_html_e('API Key', 'fbs-stockmind'); ?>
                            </label>
                            <div style="display: flex; gap: 10px; align-items: center; max-width: 540px; flex-wrap: wrap;">
                                <input type="password" 
                                       name="fbs_stockmind_ai_api_key" 
                                       id="fbs_stockmind_ai_api_key" 
                                       value="<?php echo esc_attr($settings['ai_api_key']); ?>" 
                                       placeholder="<?php esc_attr_e('Paste your Google Gemini or OpenAI API Key here', 'fbs-stockmind'); ?>" 
                                       class="fbs-form-input" 
                                       style="flex: 1; min-width: 260px;" />
                                <button type="button" class="fbs-btn fbs-btn-secondary fbs-stockmind-test-ai-btn" id="fbs-stockmind-test-ai-key" style="white-space: nowrap; padding: 10px 16px;">
                                    <?php esc_html_e('Test Connection', 'fbs-stockmind'); ?>
                                </button>
                            </div>
                            <p class="fbs-form-description" style="margin-top: 6px;">
                                <span id="fbs-stockmind-key-hint">
                                    <?php
                                    printf(
                                        /* translators: 1: Google AI Studio link, 2: OpenAI link */
                                        esc_html__('Get a free Gemini API key from %1$s or an OpenAI key from %2$s.', 'fbs-stockmind'),
                                        '<a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" style="color: #0284c7; text-decoration: underline;">Google AI Studio</a>',
                                        '<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" style="color: #0284c7; text-decoration: underline;">OpenAI Platform</a>'
                                    );
                                    ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <p class="fbs-form-description" style="margin-top: -6px; margin-bottom: 18px;">
                        <span id="fbs-stockmind-ai-test-result" style="font-weight: 600;"></span>
                    </p>

                    <div class="fbs-form-group">
                        <label for="fbs_stockmind_ai_tone" class="fbs-form-label">
                            <?php esc_html_e('Assistant Response Tone', 'fbs-stockmind'); ?>
                        </label>
                        <select name="fbs_stockmind_ai_tone" id="fbs_stockmind_ai_tone" class="fbs-form-select" style="max-width: 480px; width: 100%;">
                            <option value="analytical" <?php selected($settings['ai_tone'], 'analytical'); ?>><?php esc_html_e('Analytical & Action-Oriented (Purchasing Manager)', 'fbs-stockmind'); ?></option>
                            <option value="concise" <?php selected($settings['ai_tone'], 'concise'); ?>><?php esc_html_e('Concise & Direct (Executive Summary)', 'fbs-stockmind'); ?></option>
                            <option value="persuasive" <?php selected($settings['ai_tone'], 'persuasive'); ?>><?php esc_html_e('Strategic & Growth-Focused (Merchandising)', 'fbs-stockmind'); ?></option>
                            <option value="friendly" <?php selected($settings['ai_tone'], 'friendly'); ?>><?php esc_html_e('Warm, Helpful & Friendly', 'fbs-stockmind'); ?></option>
                        </select>
                    </div>

                    <div style="margin-top: 25px; padding: 18px 20px; border-radius: 12px; background: linear-gradient(135deg, #f0f9ff 0%, #eef2ff 100%); border: 1px solid #bae6fd; max-width: 650px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <div style="max-width: 440px;">
                            <h4 style="margin: 0 0 6px 0; color: #0f172a; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                                <span>✨</span> <?php esc_html_e('Ready to Chat with Your Inventory?', 'fbs-stockmind'); ?>
                            </h4>
                            <p style="margin: 0; color: #334155; font-size: 13px; line-height: 1.55;">
                                <?php esc_html_e('Open the AI Assistant screen to ask questions about low-stock items, runout forecasts, and weekly restock priorities.', 'fbs-stockmind'); ?>
                            </p>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-ai-assistant')); ?>" class="fbs-btn fbs-btn-primary" style="text-decoration: none;">
                            🤖 <?php esc_html_e('Open AI Assistant →', 'fbs-stockmind'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!function_exists('fbs_stockmind_pro_is_woocommerce_active')): ?>
            <!-- Pro Options Showcase Tab -->
            <div class="fbs-tab-content" id="pro-tab">
                <div class="fbs-settings-section">
                    <!-- Hero Banner -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 14px; padding: 32px; color: #ffffff; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(102, 126, 234, 0.25);">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                            <div style="max-width: 680px;">
                                <span style="background: rgba(255,255,255,0.22); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">
                                    <?php esc_html_e('Premium Upgrade', 'fbs-stockmind'); ?>
                                </span>
                                <h2 style="color: #ffffff; margin: 12px 0 8px 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">
                                    <?php esc_html_e('Unlock the Full Power of StockMind Pro', 'fbs-stockmind'); ?>
                                </h2>
                                <p style="color: rgba(255,255,255,0.92); margin: 0 0 14px 0; font-size: 14px; line-height: 1.6;">
                                    <?php esc_html_e('Automate your entire inventory operations with smart purchase orders, visual depletion graphs, multi-step customer replenishment sequences, custom 365-day forecasting, SMS alerts, and AI-driven purchasing strategies.', 'fbs-stockmind'); ?>
                                </p>
                                <div style="font-size: 12px; color: rgba(255,255,255,0.85); display: flex; gap: 16px; flex-wrap: wrap;">
                                    <span>⚡ <?php esc_html_e('Instant Activation', 'fbs-stockmind'); ?></span>
                                    <span>🛡️ <?php esc_html_e('30-Day Money-Back Guarantee', 'fbs-stockmind'); ?></span>
                                    <span>👑 <?php esc_html_e('Priority VIP Support', 'fbs-stockmind'); ?></span>
                                </div>
                            </div>
                            <div>
                                <a href="<?php echo esc_url(fbs_stockmind_utm_url('plugins/', 'pro-upgrade', 'settings-pro-hero', 'fbs-stockmind')); ?>" target="_blank" rel="noopener noreferrer" class="fbs-btn" style="background: #ffffff; color: #4338ca; font-weight: 800; font-size: 15px; padding: 14px 28px; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.2); text-decoration: none; display: inline-block;">
                                    <?php esc_html_e('Explore StockMind Pro ↗', 'fbs-stockmind'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Section Title -->
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0;">
                        ✨ <?php esc_html_e('Everything Included in StockMind Pro', 'fbs-stockmind'); ?>
                    </h3>

                    <!-- 10 Feature Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 35px;">
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">📈</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Visual Stock Runout Charts', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Interactive visual depletion graphs mapping sales velocity against stock levels, displaying critical reorder threshold safety lines.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">🏢</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Unlimited Suppliers & Directory', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Manage unlimited vendor profiles, individual lead times, minimum order quantities (MOQ), wholesale pricing, and direct contacts.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">📝</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('1-Click Purchase Order Generation', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Generate branded PDF and CSV purchase orders formatted and ready to email directly to your manufacturers and distributors.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">⚡</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Automated PO Restock Triggers', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Automatically draft purchase orders and notify suppliers the moment stock dips below calculated lead-time thresholds.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">📅</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Custom 365-Day Analysis Windows', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Customize sales analysis windows from 7 up to 365 days (instead of fixed 30 days) to accurately account for seasonality and holiday spikes.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">🎯</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Tunable Prediction Accuracy', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Fine-tune prediction confidence thresholds from 10% to 100% and toggle advanced statistical algorithms tailored for large catalogs.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">🔄</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Multi-Step Customer Replenishment', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Send up to 10 automated smart follow-up reminder emails before and after runout with dynamic WooCommerce discount coupons.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">💬</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Twilio SMS & WhatsApp Alerts', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Receive urgent SMS and WhatsApp restocking notifications directly on your phone when critical high-velocity items risk stockout.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">🤖</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Advanced AI Inventory Analyst', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Run in-depth catalog health audits, bestseller growth trends, supplier risk evaluations, and custom AI-powered purchasing strategies.', 'fbs-stockmind'); ?></p>
                        </div>
                        <div class="fbs-pro-feature-card">
                            <div class="fbs-pro-card-icon">🏬</div>
                            <h4 class="fbs-pro-card-title"><?php esc_html_e('Multi-Warehouse Stock Tracking', 'fbs-stockmind'); ?></h4>
                            <p class="fbs-pro-card-desc"><?php esc_html_e('Track inventory velocity and depletion dates across multiple fulfillment locations and warehouses with location-specific runout dates.', 'fbs-stockmind'); ?></p>
                        </div>
                    </div>

                    <!-- Free vs Pro Comparison Table -->
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 14px 0;">
                        ⚖️ <?php esc_html_e('Free vs. StockMind Pro Comparison', 'fbs-stockmind'); ?>
                    </h3>
                    <table class="fbs-pro-comparison-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;"><?php esc_html_e('Feature', 'fbs-stockmind'); ?></th>
                                <th style="width: 28%;"><?php esc_html_e('Free Version', 'fbs-stockmind'); ?></th>
                                <th class="pro-col" style="width: 32%;"><?php esc_html_e('StockMind Pro ⭐', 'fbs-stockmind'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php esc_html_e('Sales Analysis Window', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Fixed 30 Days', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Flexible (7 to 365 Days) ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Prediction Accuracy Threshold', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Preset at 30%', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Customizable (10% to 100%) ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Supplier Management', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Basic Directory (Up to 3)', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Unlimited Suppliers & Terms ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Purchase Orders (PDF & CSV)', 'fbs-stockmind'); ?></strong></td>
                                <td><span style="color: #94a3b8;">✕ <?php esc_html_e('Not Available', 'fbs-stockmind'); ?></span></td>
                                <td class="pro-col"><?php esc_html_e('1-Click PDF & CSV PO Generation ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Automated PO Restock Triggers', 'fbs-stockmind'); ?></strong></td>
                                <td><span style="color: #94a3b8;">✕ <?php esc_html_e('Manual Only', 'fbs-stockmind'); ?></span></td>
                                <td class="pro-col"><?php esc_html_e('Automated Triggers on Low Stock ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Visual Depletion Charts', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Text Data Only', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Interactive Chart.js Graphs ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Customer Replenishment Reminders', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('1 Reminder Email Attempt', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Up to 10 Follow-ups + Coupons ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Urgent Restock Notifications', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Email Only', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Email + Twilio SMS & WhatsApp ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('AI Assistant Queries', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Standard Overview', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Deep Audits & Custom Strategy ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Multi-Warehouse Support', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Single Location', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Multi-Location & Warehouses ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Customer Support', 'fbs-stockmind'); ?></strong></td>
                                <td><?php esc_html_e('Community Forum', 'fbs-stockmind'); ?></td>
                                <td class="pro-col"><?php esc_html_e('Priority VIP Support & Updates ✨', 'fbs-stockmind'); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Bottom CTA Banner -->
                    <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center; margin-top: 25px;">
                        <h4 style="margin: 0 0 6px 0; font-size: 17px; color: #1e293b; font-weight: 700;">
                            🚀 <?php esc_html_e('Ready to scale your inventory and eliminate stockouts?', 'fbs-stockmind'); ?>
                        </h4>
                        <p style="margin: 0 0 16px 0; color: #64748b; font-size: 13px;">
                            <?php esc_html_e('Upgrade to StockMind Pro to take full control of your supply chain and increase customer lifetime value.', 'fbs-stockmind'); ?>
                        </p>
                        <a href="<?php echo esc_url(fbs_stockmind_utm_url('plugins/', 'pro-upgrade', 'settings-pro-footer', 'fbs-stockmind')); ?>" target="_blank" rel="noopener noreferrer" class="fbs-btn fbs-btn-primary" style="font-size: 14px; padding: 12px 28px; text-decoration: none;">
                            <?php esc_html_e('Explore StockMind Pro on FBSFusion.com ↗', 'fbs-stockmind'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php do_action('fbs_stockmind_settings_tabs_content', $settings); ?>
        </div>

        <!-- Form Actions -->
        <div class="fbs-form-actions" id="fbs-settings-form-actions">
            <button type="submit" name="submit" class="fbs-btn fbs-btn-primary fbs-btn-lg">
                <span class="fbs-btn-icon">💾</span>
                <?php esc_html_e('Save Settings', 'fbs-stockmind'); ?>
            </button>
            <button type="button" class="fbs-btn fbs-btn-secondary" id="fbs-reset-settings">
                <span class="fbs-btn-icon">🔄</span>
                <?php esc_html_e('Reset to Defaults', 'fbs-stockmind'); ?>
            </button>
        </div>
    </form>

    <!-- Quiet FBS Fusion Engineering Footer Banner -->
    <div class="fbs-studio-footer-card">
        <div class="fbs-studio-footer-card__copy">
            <strong><?php esc_html_e('Built by FBS Fusion Engineering', 'fbs-stockmind'); ?></strong>
            <span><?php esc_html_e('Need sub-second WooCommerce Core Web Vitals, 502 Bad Gateway rescue, malware cleanup, or custom ERP/AI integrations?', 'fbs-stockmind'); ?></span>
        </div>
        <div class="fbs-studio-footer-card__links">
            <a href="<?php echo esc_url(fbs_stockmind_utm_url('speed-security/', 'agency-services', 'settings-footer-audit')); ?>" target="_blank" rel="noopener noreferrer" class="fbs-studio-pill fbs-studio-pill--emerald">
                ⚡ <?php esc_html_e('Free Store Speed Audit ↗', 'fbs-stockmind'); ?>
            </a>
            <a href="<?php echo esc_url(fbs_stockmind_utm_url('hire-us/', 'agency-services', 'settings-footer-hire')); ?>" target="_blank" rel="noopener noreferrer" class="fbs-studio-pill">
                🧩 <?php esc_html_e('Custom WooCommerce Dev ↗', 'fbs-stockmind'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="fbs-toast-container" class="fbs-toast-container"></div>
