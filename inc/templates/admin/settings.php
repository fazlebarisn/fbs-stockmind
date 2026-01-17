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
                    $settings_class = \FBS_StockMind\Inc\Admin\Settings::get_instance();
                    $prediction_editable = $settings_class->is_prediction_settings_editable();
                    ?>
                    
                    <div class="fbs-form-group">
                        <label for="sales_data_period" class="fbs-form-label">
                            <?php esc_html_e('Sales Data Analysis Period (Days)', 'fbs-stockmind'); ?>
                            <?php if (!$prediction_editable): ?>
                                <span style="color: #666; font-size: 0.9em; font-weight: normal;">(<?php esc_html_e('Fixed', 'fbs-stockmind'); ?>)</span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="sales_data_period" 
                               name="sales_data_period" 
                               value="<?php echo esc_attr($settings['sales_data_period']); ?>" 
                               min="7" 
                               max="365" 
                               <?php echo $prediction_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $prediction_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Number of days of sales data to analyze for predictions', 'fbs-stockmind'); ?>
                        </p>
                    </div>
                    
                    <div class="fbs-form-group">
                        <label for="prediction_accuracy_threshold" class="fbs-form-label">
                            <?php esc_html_e('Prediction Accuracy Threshold', 'fbs-stockmind'); ?>
                        </label>
                        <input type="number" 
                               id="prediction_accuracy_threshold" 
                               name="prediction_accuracy_threshold" 
                               value="<?php echo esc_attr($settings['prediction_accuracy_threshold']); ?>" 
                               min="0.1" 
                               max="1.0" 
                               step="0.1" 
                               <?php echo $prediction_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $prediction_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Minimum confidence level for predictions (0.1 = 10%, 1.0 = 100%)', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-info-box">
                        <h3 class="fbs-info-title"><?php esc_html_e('How Predictions Work', 'fbs-stockmind'); ?></h3>
                        <ul class="fbs-info-list">
                            <?php
                            // Get actual sales data period being used
                            $saved_period = fbs_stockmind_get_option('sales_data_period', 0);
                            if ($saved_period > 0) {
                                $sales_period = $saved_period;
                            } else {
                                $sales_period = apply_filters('fbs_stockmind_sales_data_period', 30, 0);
                            }
                            ?>
                            <li><?php 
                                printf(
                                    esc_html__('Analyzes sales data from the last %d days', 'fbs-stockmind'),
                                    $sales_period
                                ); 
                            ?></li>
                            <li><?php esc_html_e('Calculates average daily sales rate', 'fbs-stockmind'); ?></li>
                            <li><?php esc_html_e('Factors in supplier lead times', 'fbs-stockmind'); ?></li>
                            <li><?php esc_html_e('Updates predictions daily via cron job', 'fbs-stockmind'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Reminders Settings Tab -->
            <div class="fbs-tab-content" id="reminders-tab">
                <div class="fbs-settings-section">
                    <h2 class="fbs-section-title"><?php esc_html_e('Customer Reminder Settings', 'fbs-stockmind'); ?></h2>
                    
                    <?php
                    $settings_class = \FBS_StockMind\Inc\Admin\Settings::get_instance();
                    $reminder_editable = $settings_class->is_reminder_settings_editable();
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
                            <?php if (!$reminder_editable): ?>
                                <span style="color: #666; font-size: 0.9em; font-weight: normal;">(<?php esc_html_e('Fixed', 'fbs-stockmind'); ?>)</span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="reminder_advance_days" 
                               name="reminder_advance_days" 
                               value="<?php echo esc_attr($settings['reminder_advance_days']); ?>" 
                               min="1" 
                               max="30" 
                               <?php echo $reminder_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $reminder_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('How many days before predicted runout to send reminders', 'fbs-stockmind'); ?>
                        </p>
                    </div>

                    <div class="fbs-form-group">
                        <label for="max_reminder_attempts" class="fbs-form-label">
                            <?php esc_html_e('Maximum Reminder Attempts', 'fbs-stockmind'); ?>
                            <?php if (!$reminder_editable): ?>
                                <span style="color: #666; font-size: 0.9em; font-weight: normal;">(<?php esc_html_e('Fixed', 'fbs-stockmind'); ?>)</span>
                            <?php endif; ?>
                        </label>
                        <input type="number" 
                               id="max_reminder_attempts" 
                               name="max_reminder_attempts" 
                               value="<?php echo esc_attr($settings['max_reminder_attempts']); ?>" 
                               min="1" 
                               max="10" 
                               <?php echo $reminder_editable ? '' : 'readonly'; ?>
                               class="fbs-form-input <?php echo $reminder_editable ? '' : 'fbs-readonly-input'; ?>" />
                        <p class="fbs-form-description">
                            <?php esc_html_e('Maximum number of reminder emails to send per customer', 'fbs-stockmind'); ?>
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
        </div>

        <!-- Form Actions -->
        <div class="fbs-form-actions">
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
</div>

<!-- Toast Notification Container -->
<div id="fbs-toast-container" class="fbs-toast-container"></div>
