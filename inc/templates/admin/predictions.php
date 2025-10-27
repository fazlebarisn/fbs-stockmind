<?php
/**
 * Admin predictions page template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<div class="fbs-stockmind-predictions">
    <!-- Header -->
    <div class="fbs-predictions-header">
        <div class="fbs-header-content">
            <h1 class="fbs-predictions-title">
                <span class="fbs-title-icon">🔮</span>
                <?php esc_html_e('Stock Predictions', 'fbs-stockmind'); ?>
            </h1>
            <p class="fbs-predictions-subtitle">
                <?php esc_html_e('AI-powered predictions for when products will run out of stock', 'fbs-stockmind'); ?>
            </p>
        </div>
        <div class="fbs-header-actions">
            <button type="button" class="fbs-btn fbs-btn-secondary" id="fbs-calculate-predictions">
                <span class="fbs-btn-icon">⚡</span>
                <?php esc_html_e('Calculate Now', 'fbs-stockmind'); ?>
            </button>
        </div>
    </div>


    <!-- Filters -->
    <div class="fbs-predictions-filters">
        <div class="fbs-filter-group">
            <label for="fbs-urgency-filter" class="fbs-filter-label">
                <?php esc_html_e('Urgency:', 'fbs-stockmind'); ?>
            </label>
            <select id="fbs-urgency-filter" class="fbs-filter-select">
                <option value=""><?php esc_html_e('All Predictions', 'fbs-stockmind'); ?></option>
                <option value="urgent"><?php esc_html_e('Urgent (≤7 days)', 'fbs-stockmind'); ?></option>
                <option value="warning"><?php esc_html_e('Warning (8-14 days)', 'fbs-stockmind'); ?></option>
                <option value="normal"><?php esc_html_e('Normal (>14 days)', 'fbs-stockmind'); ?></option>
            </select>
        </div>
        
        <div class="fbs-filter-group">
            <label for="fbs-search-filter" class="fbs-filter-label">
                <?php esc_html_e('Search:', 'fbs-stockmind'); ?>
            </label>
            <input type="text" 
                   id="fbs-search-filter" 
                   placeholder="<?php esc_attr_e('Search by product name...', 'fbs-stockmind'); ?>" 
                   class="fbs-filter-input" />
        </div>
        
        <div class="fbs-filter-group">
            <button type="button" class="fbs-btn fbs-btn-sm fbs-btn-secondary" id="fbs-clear-filters">
                <?php esc_html_e('Clear Filters', 'fbs-stockmind'); ?>
            </button>
        </div>
    </div>

    <!-- Predictions List -->
    <div class="fbs-predictions-content">
        
        <?php if (empty($predictions)): ?>
            <div class="fbs-empty-state">
                <div class="fbs-empty-icon">✅</div>
                <h3 class="fbs-empty-title"><?php esc_html_e('No Predictions Found', 'fbs-stockmind'); ?></h3>
                <p class="fbs-empty-text"><?php esc_html_e('All products are well-stocked! Predictions will appear here when products need attention.', 'fbs-stockmind'); ?></p>
            </div>
        <?php else: ?>
            <div class="fbs-predictions-list">
                <?php foreach ($predictions as $prediction): ?>
                    <?php
                    // Use the days_until_runout from the prediction data (already calculated correctly)
                    $days_until_runout = $prediction['days_until_runout'];
                    $urgency_class = $days_until_runout <= 0 ? 'urgent' : ($days_until_runout <= 7 ? 'urgent' : ($days_until_runout <= 14 ? 'warning' : 'normal'));
                    ?>
                    <div class="fbs-prediction-item fbs-prediction-<?php echo esc_attr($urgency_class); ?>" 
                         data-product-id="<?php echo esc_attr($prediction['product_id']); ?>"
                         data-prediction-id="<?php echo esc_attr($prediction['id']); ?>"
                         data-urgency="<?php echo esc_attr($urgency_class); ?>">
                        
                        <div class="fbs-prediction-image">
                            <?php if ($prediction['product_image']): ?>
                                <img src="<?php echo esc_url($prediction['product_image']); ?>" alt="<?php echo esc_attr($prediction['product_name']); ?>" />
                            <?php else: ?>
                                <div class="fbs-no-image">📦</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="fbs-prediction-details">
                            <div class="fbs-prediction-header">
                                <h3 class="fbs-prediction-name">
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $prediction['product_id'] . '&action=edit')); ?>" target="_blank">
                                        <?php echo esc_html($prediction['product_name']); ?>
                                    </a>
                                </h3>
                                <div class="fbs-prediction-urgency">
                                    <span class="fbs-urgency-badge fbs-urgency-<?php echo esc_attr($urgency_class); ?>">
                                        <?php 
                                        if ($days_until_runout <= 0) {
                                            esc_html_e('Out of Stock', 'fbs-stockmind');
                                        } elseif ($days_until_runout <= 7) {
                                            esc_html_e('Urgent', 'fbs-stockmind');
                                        } elseif ($days_until_runout <= 14) {
                                            esc_html_e('Warning', 'fbs-stockmind');
                                        } else {
                                            esc_html_e('Normal', 'fbs-stockmind');
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="fbs-prediction-meta">
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Current Stock:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value fbs-stock-value">
                                        <strong><?php echo esc_html($prediction['current_stock']); ?></strong> <?php esc_html_e('units', 'fbs-stockmind'); ?>
                                    </span>
                                </div>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Predicted Runout:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value fbs-runout-value">
                                        <?php echo esc_html(fbs_stockmind_format_date($prediction['predicted_runout_date'])); ?>
                                    </span>
                                </div>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Days Until Runout:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value fbs-days-value fbs-days-<?php echo esc_attr($urgency_class); ?>">
                                        <?php 
                                        if ($days_until_runout <= 0) {
                                            esc_html_e('Already out of stock', 'fbs-stockmind');
                                        } elseif ($days_until_runout == 1) {
                                            esc_html_e('Today', 'fbs-stockmind');
                                        } else {
                                            printf(
                                                esc_html(_n('%d day', '%d days', $days_until_runout, 'fbs-stockmind')),
                                                $days_until_runout
                                            );
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Confidence:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value fbs-confidence-value">
                                        <span class="fbs-confidence-badge fbs-confidence-<?php echo esc_attr($predictor->get_confidence_level($prediction['confidence_score'])); ?>">
                                            <?php echo esc_html(number_format($prediction['confidence_score'] * 100, 0)); ?>%
                                        </span>
                                    </span>
                                </div>
                                
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Last Calculated:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value">
                                        <?php echo esc_html(fbs_stockmind_time_ago($prediction['calculated_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="fbs-prediction-actions">
                            <a href="<?php echo esc_url(get_permalink($prediction['product_id'])); ?>" 
                               target="_blank" 
                               class="fbs-btn fbs-btn-sm fbs-btn-secondary">
                                <?php esc_html_e('View Product', 'fbs-stockmind'); ?>
                            </a>
                            
                            
                            <button type="button" 
                                    class="fbs-btn fbs-btn-sm fbs-btn-secondary fbs-dismiss-prediction" 
                                    data-prediction-id="<?php echo esc_attr($prediction['id']); ?>">
                                <?php esc_html_e('Dismiss', 'fbs-stockmind'); ?>
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
