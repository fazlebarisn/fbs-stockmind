<?php
/**
 * Admin dashboard template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-stockmind-dashboard">
    <!-- Header -->
    <div class="fbs-dashboard-header">
        <div class="fbs-header-content">
            <h1 class="fbs-dashboard-title">
                <span class="fbs-title-icon">🧠</span>
                <?php esc_html_e('StockMind Dashboard', 'fbs-stockmind'); ?>
            </h1>
            <p class="fbs-dashboard-subtitle">
                <?php esc_html_e('Predictive inventory management and smart replenishment alerts', 'fbs-stockmind'); ?>
            </p>
        </div>
        <div class="fbs-header-actions">
            <button type="button" class="fbs-btn fbs-btn-primary" id="fbs-refresh-predictions">
                <span class="fbs-btn-icon">🔄</span>
                <?php esc_html_e('Refresh Predictions', 'fbs-stockmind'); ?>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="fbs-stats-grid">
        <div class="fbs-stat-card fbs-stat-warning">
            <div class="fbs-stat-icon">⚠️</div>
            <div class="fbs-stat-content">
                <h3 class="fbs-stat-number"><?php echo esc_html($stats['products_needing_attention']); ?></h3>
                <p class="fbs-stat-label"><?php esc_html_e('Products Needing Attention', 'fbs-stockmind'); ?></p>
            </div>
        </div>
        
        <div class="fbs-stat-card fbs-stat-info">
            <div class="fbs-stat-icon">📧</div>
            <div class="fbs-stat-content">
                <h3 class="fbs-stat-number"><?php echo esc_html($stats['active_reminders']); ?></h3>
                <p class="fbs-stat-label"><?php esc_html_e('Active Customer Reminders', 'fbs-stockmind'); ?></p>
            </div>
        </div>
        
        <div class="fbs-stat-card fbs-stat-success">
            <div class="fbs-stat-icon">🏢</div>
            <div class="fbs-stat-content">
                <h3 class="fbs-stat-number"><?php echo esc_html($stats['total_suppliers']); ?></h3>
                <p class="fbs-stat-label"><?php esc_html_e('Active Suppliers', 'fbs-stockmind'); ?></p>
            </div>
        </div>
        
        <div class="fbs-stat-card fbs-stat-danger">
            <div class="fbs-stat-icon">📦</div>
            <div class="fbs-stat-content">
                <h3 class="fbs-stat-number"><?php echo esc_html($stats['low_stock_products']); ?></h3>
                <p class="fbs-stat-label"><?php esc_html_e('Low Stock Products', 'fbs-stockmind'); ?></p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="fbs-dashboard-grid">
        <!-- Recent Predictions -->
        <div class="fbs-dashboard-card">
            <div class="fbs-card-header">
                <h2 class="fbs-card-title">
                    <span class="fbs-card-icon">🔮</span>
                    <?php esc_html_e('Recent Predictions', 'fbs-stockmind'); ?>
                </h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-predictions')); ?>" class="fbs-card-link">
                    <?php esc_html_e('View All', 'fbs-stockmind'); ?>
                </a>
            </div>
            <div class="fbs-card-content">
                <?php if (empty($recent_predictions)): ?>
                    <div class="fbs-empty-state">
                        <div class="fbs-empty-icon">✅</div>
                        <p class="fbs-empty-text"><?php esc_html_e('No products need immediate attention', 'fbs-stockmind'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="fbs-predictions-list">
                        <?php foreach ($recent_predictions as $prediction): ?>
                            <div class="fbs-prediction-item" data-product-id="<?php echo esc_attr($prediction['product_id']); ?>">
                                <div class="fbs-prediction-image">
                                    <?php if ($prediction['product_image']): ?>
                                        <img src="<?php echo esc_url($prediction['product_image']); ?>" alt="<?php echo esc_attr($prediction['product_name']); ?>" />
                                    <?php else: ?>
                                        <div class="fbs-no-image">📦</div>
                                    <?php endif; ?>
                                </div>
                                <div class="fbs-prediction-details">
                                    <h4 class="fbs-prediction-name"><?php echo esc_html($prediction['product_name']); ?></h4>
                                    <div class="fbs-prediction-meta">
                                        <span class="fbs-stock-info">
                                            <strong><?php echo esc_html($prediction['current_stock']); ?></strong> <?php esc_html_e('in stock', 'fbs-stockmind'); ?>
                                        </span>
                                        <span class="fbs-runout-info fbs-runout-<?php echo esc_attr($prediction['days_until_runout'] <= 7 ? 'urgent' : ($prediction['days_until_runout'] <= 14 ? 'warning' : 'normal')); ?>">
                                            <?php 
                                            if ($prediction['days_until_runout'] <= 0) {
                                                esc_html_e('Out of stock predicted', 'fbs-stockmind');
                                            } else {
                                                printf(
                                                    /* translators: %d: Number of days */
                                                    esc_html(_n('%d day until runout', '%d days until runout', $prediction['days_until_runout'], 'fbs-stockmind')),
                                                    $prediction['days_until_runout']
                                                );
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="fbs-prediction-actions">
                                    <button type="button" class="fbs-btn fbs-btn-sm fbs-btn-secondary fbs-dismiss-prediction" data-prediction-id="<?php echo esc_attr($prediction['id']); ?>">
                                        <?php esc_html_e('Dismiss', 'fbs-stockmind'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Replenishments -->
        <div class="fbs-dashboard-card">
            <div class="fbs-card-header">
                <h2 class="fbs-card-title">
                    <span class="fbs-card-icon">🔄</span>
                    <?php esc_html_e('Upcoming Replenishments', 'fbs-stockmind'); ?>
                </h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-reminders')); ?>" class="fbs-card-link">
                    <?php esc_html_e('View All', 'fbs-stockmind'); ?>
                </a>
            </div>
            <div class="fbs-card-content">
                <?php if (empty($upcoming_replenishments)): ?>
                    <div class="fbs-empty-state">
                        <div class="fbs-empty-icon">📭</div>
                        <p class="fbs-empty-text"><?php esc_html_e('No customer reminders set', 'fbs-stockmind'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="fbs-replenishments-list">
                        <?php foreach ($upcoming_replenishments as $replenishment): ?>
                            <div class="fbs-replenishment-item">
                                <div class="fbs-replenishment-image">
                                    <?php if ($replenishment['product_image']): ?>
                                        <img src="<?php echo esc_url($replenishment['product_image']); ?>" alt="<?php echo esc_attr($replenishment['product_name']); ?>" />
                                    <?php else: ?>
                                        <div class="fbs-no-image">📦</div>
                                    <?php endif; ?>
                                </div>
                                <div class="fbs-replenishment-details">
                                    <h4 class="fbs-replenishment-name"><?php echo esc_html($replenishment['product_name']); ?></h4>
                                    <div class="fbs-replenishment-meta">
                                        <span class="fbs-customer-info">
                                            <?php echo esc_html($replenishment['customer_email']); ?>
                                        </span>
                                        <span class="fbs-reminder-info">
                                            <?php 
                                            if ($replenishment['reminder_count'] > 0) {
                                                printf(
                                                    /* translators: %d: Number of reminders */
                                                    esc_html(_n('%d reminder sent', '%d reminders sent', $replenishment['reminder_count'], 'fbs-stockmind')),
                                                    $replenishment['reminder_count']
                                                );
                                            } else {
                                                esc_html_e('No reminders sent yet', 'fbs-stockmind');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="fbs-replenishment-actions">
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $replenishment['order_id'] . '&action=edit')); ?>" class="fbs-btn fbs-btn-sm fbs-btn-secondary">
                                        <?php esc_html_e('View Order', 'fbs-stockmind'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="fbs-quick-actions">
        <h2 class="fbs-section-title"><?php esc_html_e('Quick Actions', 'fbs-stockmind'); ?></h2>
        <div class="fbs-actions-grid">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-suppliers')); ?>" class="fbs-action-card">
                <div class="fbs-action-icon">🏢</div>
                <h3 class="fbs-action-title"><?php esc_html_e('Manage Suppliers', 'fbs-stockmind'); ?></h3>
                <p class="fbs-action-description"><?php esc_html_e('Add and configure supplier information', 'fbs-stockmind'); ?></p>
            </a>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings')); ?>" class="fbs-action-card">
                <div class="fbs-action-icon">⚙️</div>
                <h3 class="fbs-action-title"><?php esc_html_e('Plugin Settings', 'fbs-stockmind'); ?></h3>
                <p class="fbs-action-description"><?php esc_html_e('Configure prediction and reminder settings', 'fbs-stockmind'); ?></p>
            </a>
            
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>" class="fbs-action-card">
                <div class="fbs-action-icon">📦</div>
                <h3 class="fbs-action-title"><?php esc_html_e('Manage Products', 'fbs-stockmind'); ?></h3>
                <p class="fbs-action-description"><?php esc_html_e('Configure replenishable products and suppliers', 'fbs-stockmind'); ?></p>
            </a>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="fbs-toast-container" class="fbs-toast-container"></div>
