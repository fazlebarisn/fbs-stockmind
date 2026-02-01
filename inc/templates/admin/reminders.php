<?php
/**
 * Admin reminders page template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-stockmind-reminders">
    <!-- Header -->
    <div class="fbs-reminders-header">
        <div class="fbs-header-content">
            <h1 class="fbs-reminders-title">
                <span class="fbs-title-icon">📧</span>
                <?php esc_html_e('Customer Reminders', 'fbs-stockmind'); ?>
            </h1>
            <p class="fbs-reminders-subtitle">
                <?php esc_html_e('Manage customer replenishment reminders and track their effectiveness', 'fbs-stockmind'); ?>
            </p>
        </div>
        <div class="fbs-header-actions">
            <button type="button" class="fbs-btn fbs-btn-secondary" id="fbs-refresh-reminders">
                <span class="fbs-btn-icon">🔄</span>
                <?php esc_html_e('Refresh', 'fbs-stockmind'); ?>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="fbs-reminders-filters">
        <div class="fbs-filter-group">
            <label for="fbs-status-filter" class="fbs-filter-label">
                <?php esc_html_e('Status:', 'fbs-stockmind'); ?>
            </label>
            <select id="fbs-status-filter" class="fbs-filter-select">
                <option value=""><?php esc_html_e('All Reminders', 'fbs-stockmind'); ?></option>
                <option value="active"><?php esc_html_e('Active', 'fbs-stockmind'); ?></option>
                <option value="inactive"><?php esc_html_e('Inactive', 'fbs-stockmind'); ?></option>
            </select>
        </div>
        
        <div class="fbs-filter-group">
            <label for="fbs-search-filter" class="fbs-filter-label">
                <?php esc_html_e('Search:', 'fbs-stockmind'); ?>
            </label>
            <input type="text" 
                   id="fbs-search-filter" 
                   placeholder="<?php esc_attr_e('Search by email or product name...', 'fbs-stockmind'); ?>" 
                   class="fbs-filter-input" />
        </div>
    </div>

    <!-- Reminders List -->
    <div class="fbs-reminders-content">
        <?php if (empty($reminders)): ?>
            <div class="fbs-empty-state">
                <div class="fbs-empty-icon">📭</div>
                <h3 class="fbs-empty-title"><?php esc_html_e('No Reminders Found', 'fbs-stockmind'); ?></h3>
                <p class="fbs-empty-text"><?php esc_html_e('Customer reminders will appear here once customers start setting them up', 'fbs-stockmind'); ?></p>
            </div>
        <?php else: ?>
            <div class="fbs-reminders-list">
                <?php foreach ($reminders as $fbs_stockmind_reminder): ?>
                    <div class="fbs-reminder-item" 
                         data-reminder-id="<?php echo esc_attr($fbs_stockmind_reminder['id']); ?>"
                         data-status="<?php echo esc_attr($fbs_stockmind_reminder['is_active'] ? 'active' : 'inactive'); ?>">
                        
                        <div class="fbs-reminder-details">
                            <div class="fbs-reminder-header">
                                <h3 class="fbs-reminder-product-name">
                                    <a href="<?php echo esc_url(get_permalink($fbs_stockmind_reminder['product_id'])); ?>" target="_blank">
                                        <?php echo esc_html($fbs_stockmind_reminder['product_name']); ?>
                                    </a>
                                </h3>
                                <div class="fbs-reminder-status">
                                    <span class="fbs-status-badge fbs-status-<?php echo esc_attr($fbs_stockmind_reminder['is_active'] ? 'active' : 'inactive'); ?>">
                                        <?php echo esc_html($fbs_stockmind_reminder['is_active'] ? __('Active', 'fbs-stockmind') : __('Inactive', 'fbs-stockmind')); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="fbs-reminder-meta">
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Customer:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value">
                                        <a href="mailto:<?php echo esc_attr($fbs_stockmind_reminder['customer_email']); ?>">
                                            <?php echo esc_html($fbs_stockmind_reminder['customer_email']); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Order:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $fbs_stockmind_reminder['order_id'] . '&action=edit')); ?>" target="_blank">
                                            #<?php echo esc_html($fbs_stockmind_reminder['order_id']); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Created:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value">
                                        <?php echo esc_html(fbs_stockmind_format_date($fbs_stockmind_reminder['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <?php if ($fbs_stockmind_reminder['last_reminder_sent']): ?>
                                    <div class="fbs-meta-item">
                                        <span class="fbs-meta-label"><?php esc_html_e('Last Reminder:', 'fbs-stockmind'); ?></span>
                                        <span class="fbs-meta-value">
                                            <?php echo esc_html(fbs_stockmind_format_date($fbs_stockmind_reminder['last_reminder_sent'])); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Reminders Sent:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value">
                                        <?php echo esc_html($fbs_stockmind_reminder['reminder_count']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="fbs-reminder-actions">
                            <?php if ($fbs_stockmind_reminder['is_active']): ?>
                                <button type="button" 
                                        class="fbs-btn fbs-btn-sm fbs-btn-warning fbs-deactivate-reminder" 
                                        data-reminder-id="<?php echo esc_attr($fbs_stockmind_reminder['id']); ?>">
                                    <?php esc_html_e('Deactivate', 'fbs-stockmind'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="fbs-btn fbs-btn-sm fbs-btn-success fbs-activate-reminder" 
                                        data-reminder-id="<?php echo esc_attr($fbs_stockmind_reminder['id']); ?>">
                                    <?php esc_html_e('Activate', 'fbs-stockmind'); ?>
                                </button>
                            <?php endif; ?>
                            
                            <button type="button" 
                                    class="fbs-btn fbs-btn-sm fbs-btn-danger fbs-delete-reminder" 
                                    data-reminder-id="<?php echo esc_attr($fbs_stockmind_reminder['id']); ?>">
                                <?php esc_html_e('Delete', 'fbs-stockmind'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="fbs-toast-container" class="fbs-toast-container"></div>
