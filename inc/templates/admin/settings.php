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
                    <h2 class="fbs-ai-settings-heading">
                        <?php esc_html_e('🤖 AI Inventory Assistant Settings', 'fbs-stockmind'); ?>
                    </h2>
                    <p class="fbs-ai-settings-lead">
                        <?php esc_html_e('Uses WordPress\'s native AI Client & Connectors to analyze your live WooCommerce stock velocity, runout forecasts, best-sellers, and supplier lead times in 1 click.', 'fbs-stockmind'); ?>
                    </p>

                    <?php
                    $fbs_sm_ai_status      = \FBS_StockMind\Inc\Features\AI_Assistant::get_connectors_status();
                    $fbs_sm_wp_ai_ready    = !empty($fbs_sm_ai_status['is_ready']);
                    $fbs_sm_connectors_url = $fbs_sm_ai_status['connectors_url'];
                    $fbs_sm_wp_ai_url      = $fbs_sm_ai_status['wp_ai_url'];
                    $fbs_sm_ai_providers   = $fbs_sm_ai_status['providers'] ?? [];
                    ?>

                    <table class="form-table fbs-ai-form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('WordPress AI Connection', 'fbs-stockmind'); ?></th>
                            <td>
                                <div class="fbs-wp-ai-card <?php echo $fbs_sm_wp_ai_ready ? 'fbs-wp-ai-card--ready' : 'fbs-wp-ai-card--setup'; ?>">
                                    <div class="fbs-wp-ai-card__header">
                                        <div class="fbs-wp-ai-card__title-group">
                                            <span class="fbs-wp-ai-card__icon <?php echo $fbs_sm_wp_ai_ready ? 'fbs-wp-ai-card__icon--ready' : 'fbs-wp-ai-card__icon--setup'; ?>">
                                                <?php echo $fbs_sm_wp_ai_ready ? '✅' : '⚠️'; ?>
                                            </span>
                                            <div>
                                                <div class="fbs-wp-ai-card__title">
                                                    <?php
                                                    if ($fbs_sm_wp_ai_ready) {
                                                        esc_html_e('Connected via WordPress AI Connectors', 'fbs-stockmind');
                                                    } else {
                                                        esc_html_e('No Active AI Connector Configured', 'fbs-stockmind');
                                                    }
                                                    ?>
                                                </div>
                                                <div class="fbs-wp-ai-card__sub">
                                                    <?php esc_html_e('Uses WordPress native AI Client (wp_ai_client_prompt) & Settings ➔ Connectors.', 'fbs-stockmind'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="fbs-wp-ai-card__badge <?php echo $fbs_sm_wp_ai_ready ? 'fbs-wp-ai-card__badge--ready' : 'fbs-wp-ai-card__badge--setup'; ?>">
                                            <?php echo $fbs_sm_wp_ai_ready ? esc_html__('Ready', 'fbs-stockmind') : esc_html__('Setup Needed', 'fbs-stockmind'); ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($fbs_sm_ai_providers)) : ?>
                                        <div class="fbs-wp-ai-providers">
                                            <?php foreach ($fbs_sm_ai_providers as $fbs_prov) : ?>
                                                <div class="fbs-wp-ai-provider-pill <?php echo !empty($fbs_prov['is_connected']) ? 'fbs-wp-ai-provider-pill--connected' : ''; ?>">
                                                    <span class="fbs-wp-ai-provider-dot <?php echo !empty($fbs_prov['is_connected']) ? 'fbs-wp-ai-provider-dot--connected' : ''; ?>"></span>
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

                                    <div class="fbs-wp-ai-card__actions">
                                        <a href="<?php echo esc_url($fbs_sm_connectors_url); ?>" class="fbs-wp-ai-btn-primary">
                                            <span>⚙️</span> <?php esc_html_e('Manage WordPress Connectors', 'fbs-stockmind'); ?>
                                        </a>
                                        <button type="button" class="fbs-wp-ai-btn-secondary fbs-stockmind-test-ai-btn" id="fbs-stockmind-test-ai-key">
                                            <?php esc_html_e('Test AI Connection', 'fbs-stockmind'); ?>
                                        </button>
                                        <?php if (!empty($fbs_sm_wp_ai_url)) : ?>
                                            <a href="<?php echo esc_url($fbs_sm_wp_ai_url); ?>" class="fbs-wp-ai-link">
                                                <?php esc_html_e('WordPress AI Settings ➔', 'fbs-stockmind'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <p class="fbs-wp-ai-test-msg">
                                        <span id="fbs-stockmind-ai-test-result"></span>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Default Response Tone', 'fbs-stockmind'); ?></th>
                            <td>
                                <select name="fbs_stockmind_ai_tone" id="fbs_stockmind_ai_tone" class="fbs-form-select fbs-ai-tone-select">
                                    <option value="analytical" <?php selected($settings['ai_tone'], 'analytical'); ?>><?php esc_html_e('Analytical & Action-Oriented (Purchasing Manager)', 'fbs-stockmind'); ?></option>
                                    <option value="concise" <?php selected($settings['ai_tone'], 'concise'); ?>><?php esc_html_e('Concise & Direct (Executive Summary)', 'fbs-stockmind'); ?></option>
                                    <option value="persuasive" <?php selected($settings['ai_tone'], 'persuasive'); ?>><?php esc_html_e('Strategic & Growth-Focused (Merchandising)', 'fbs-stockmind'); ?></option>
                                    <option value="friendly" <?php selected($settings['ai_tone'], 'friendly'); ?>><?php esc_html_e('Warm, Helpful & Friendly', 'fbs-stockmind'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <div class="fbs-ai-callout-card">
                        <div class="fbs-ai-callout-card__copy">
                            <h4>
                                <span>✨</span> <?php esc_html_e('Live WooCommerce Inventory Intelligence (v1.3)', 'fbs-stockmind'); ?>
                            </h4>
                            <p>
                                <?php
                                echo wp_kses(
                                    __('Open the <strong>AI Assistant</strong> screen to chat directly with your catalog. StockMind automatically feeds your live <strong>Active Predictions, Low-Stock Counts, 30-Day Best-Sellers, and Supplier Lead Times</strong> into your connected WordPress AI provider for instant purchasing insights.', 'fbs-stockmind'),
                                    ['strong' => []]
                                );
                                ?>
                            </p>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-ai-assistant')); ?>" class="fbs-btn fbs-btn-primary">
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
