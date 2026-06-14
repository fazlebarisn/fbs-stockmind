<?php
/**
 * Suppliers admin page template
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

defined('ABSPATH') or die('Nice Try!');
?>

<?php
$fbs_supplier_at_limit = ($max_suppliers > 0 && $supplier_count >= $max_suppliers);
?>
<div class="fbs-stockmind-suppliers">
    <!-- Header -->
    <div class="fbs-suppliers-header">
        <div class="fbs-header-content">
            <h1 class="fbs-suppliers-title">
                <span class="fbs-title-icon">🏢</span>
                <?php esc_html_e('Suppliers', 'fbs-stockmind'); ?>
            </h1>
            <p class="fbs-suppliers-subtitle">
                <?php esc_html_e('Manage your suppliers and their lead times for accurate predictions', 'fbs-stockmind'); ?>
            </p>
        </div>
        <div class="fbs-header-actions">
            <?php if ($fbs_supplier_at_limit) : ?>
                <span class="fbs-supplier-limit-notice" title="<?php esc_attr_e('Upgrade to FBS StockMind Pro for unlimited suppliers.', 'fbs-stockmind'); ?>">
                    <?php
                    printf(
                        /* translators: %d: Maximum number of suppliers */
                        esc_html__('Maximum %d suppliers (free version).', 'fbs-stockmind'),
                        (int) $max_suppliers
                    );
                    ?>
                </span>
            <?php else : ?>
                <button type="button" class="fbs-btn fbs-btn-primary" id="fbs-add-supplier">
                    <span class="fbs-btn-icon">➕</span>
                    <?php esc_html_e('Add New Supplier', 'fbs-stockmind'); ?>
                </button>
            <?php endif; ?>
            <?php do_action('fbs_stockmind_supplier_header_actions'); ?>
        </div>
    </div>

    <!-- Suppliers List -->
    <div class="fbs-suppliers-content">
        <?php if (empty($suppliers)): ?>
            <div class="fbs-empty-state">
                <div class="fbs-empty-icon">🏢</div>
                <h3 class="fbs-empty-title"><?php esc_html_e('No Suppliers Found', 'fbs-stockmind'); ?></h3>
                <p class="fbs-empty-text"><?php esc_html_e('Add your first supplier to start getting accurate stock predictions', 'fbs-stockmind'); ?></p>
                <?php if (!$fbs_supplier_at_limit) : ?>
                    <button type="button" class="fbs-btn fbs-btn-primary" id="fbs-add-first-supplier">
                        <?php esc_html_e('Add Your First Supplier', 'fbs-stockmind'); ?>
                    </button>
                <?php else : ?>
                    <p class="fbs-supplier-limit-text"><?php esc_html_e('Maximum 3 suppliers in the free version. Upgrade to Pro for unlimited.', 'fbs-stockmind'); ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="fbs-suppliers-grid">
                <?php foreach ($suppliers as $fbs_stockmind_supplier): ?>
                    <div class="fbs-supplier-card" data-supplier-id="<?php echo esc_attr($fbs_stockmind_supplier->id); ?>">
                        <div class="fbs-supplier-header">
                            <h3 class="fbs-supplier-name"><?php echo esc_html($fbs_stockmind_supplier->name); ?></h3>
                            <div class="fbs-supplier-status">
                                <span class="fbs-status-badge fbs-status-<?php echo esc_attr($fbs_stockmind_supplier->is_active ? 'active' : 'inactive'); ?>">
                                    <?php echo esc_html($fbs_stockmind_supplier->is_active ? __('Active', 'fbs-stockmind') : __('Inactive', 'fbs-stockmind')); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="fbs-supplier-details">
                            <div class="fbs-supplier-meta">
                                <div class="fbs-meta-item">
                                    <span class="fbs-meta-label"><?php esc_html_e('Lead Time:', 'fbs-stockmind'); ?></span>
                                    <span class="fbs-meta-value"><?php echo esc_html($fbs_stockmind_supplier->lead_time); ?> <?php esc_html_e('days', 'fbs-stockmind'); ?></span>
                                </div>
                                
                                <?php if ($fbs_stockmind_supplier->email): ?>
                                    <div class="fbs-meta-item">
                                        <span class="fbs-meta-label"><?php esc_html_e('Email:', 'fbs-stockmind'); ?></span>
                                        <span class="fbs-meta-value">
                                            <a href="mailto:<?php echo esc_attr($fbs_stockmind_supplier->email); ?>">
                                                <?php echo esc_html($fbs_stockmind_supplier->email); ?>
                                            </a>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($fbs_stockmind_supplier->phone): ?>
                                    <div class="fbs-meta-item">
                                        <span class="fbs-meta-label"><?php esc_html_e('Phone:', 'fbs-stockmind'); ?></span>
                                        <span class="fbs-meta-value">
                                            <a href="tel:<?php echo esc_attr($fbs_stockmind_supplier->phone); ?>">
                                                <?php echo esc_html($fbs_stockmind_supplier->phone); ?>
                                            </a>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($fbs_stockmind_supplier->notes): ?>
                                <div class="fbs-supplier-notes">
                                    <p><?php echo esc_html(wp_trim_words($fbs_stockmind_supplier->notes, 20)); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="fbs-supplier-actions">
                            <button type="button" class="fbs-btn fbs-btn-sm fbs-btn-primary fbs-edit-supplier" 
                                    data-supplier-id="<?php echo esc_attr($fbs_stockmind_supplier->id); ?>">
                                <?php esc_html_e('Edit', 'fbs-stockmind'); ?>
                            </button>
                            <button type="button" class="fbs-btn fbs-btn-sm fbs-btn-secondary fbs-view-products" 
                                    data-supplier-id="<?php echo esc_attr($fbs_stockmind_supplier->id); ?>">
                                <?php esc_html_e('View Products', 'fbs-stockmind'); ?>
                            </button>
                            <button type="button" class="fbs-btn fbs-btn-sm fbs-btn-danger fbs-delete-supplier" 
                                    data-supplier-id="<?php echo esc_attr($fbs_stockmind_supplier->id); ?>">
                                <?php esc_html_e('Delete', 'fbs-stockmind'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div id="fbs-supplier-modal" class="fbs-modal" style="display: none;">
    <div class="fbs-modal-overlay"></div>
    <div class="fbs-modal-content">
        <div class="fbs-modal-header">
            <h2 class="fbs-modal-title" id="fbs-modal-title">
                <?php esc_html_e('Add New Supplier', 'fbs-stockmind'); ?>
            </h2>
            <button type="button" class="fbs-modal-close">&times;</button>
        </div>
        
        <form id="fbs-supplier-form" class="fbs-modal-body">
            <input type="hidden" id="fbs-supplier-id" name="supplier_id" value="" />
            
            <div class="fbs-form-group">
                <label for="fbs-supplier-name" class="fbs-form-label">
                    <?php esc_html_e('Supplier Name', 'fbs-stockmind'); ?> <span class="fbs-required">*</span>
                </label>
                <input type="text" 
                       id="fbs-supplier-name" 
                       name="name" 
                       required 
                       class="fbs-form-input" />
            </div>
            
            <div class="fbs-form-row">
                <div class="fbs-form-group">
                    <label for="fbs-supplier-lead-time" class="fbs-form-label">
                        <?php esc_html_e('Lead Time (Days)', 'fbs-stockmind'); ?> <span class="fbs-required">*</span>
                    </label>
                    <input type="number" 
                           id="fbs-supplier-lead-time" 
                           name="lead_time" 
                           value="7" 
                           min="1" 
                           max="365" 
                           required 
                           class="fbs-form-input" />
                </div>
                
                <div class="fbs-form-group">
                    <label for="fbs-supplier-email" class="fbs-form-label">
                        <?php esc_html_e('Email Address', 'fbs-stockmind'); ?>
                    </label>
                    <input type="email" 
                           id="fbs-supplier-email" 
                           name="email" 
                           class="fbs-form-input" />
                </div>
            </div>
            
            <div class="fbs-form-group">
                <label for="fbs-supplier-phone" class="fbs-form-label">
                    <?php esc_html_e('Phone Number', 'fbs-stockmind'); ?>
                </label>
                <input type="tel" 
                       id="fbs-supplier-phone" 
                       name="phone" 
                       class="fbs-form-input" />
            </div>
            
            <div class="fbs-form-group">
                <label for="fbs-supplier-address" class="fbs-form-label">
                    <?php esc_html_e('Address', 'fbs-stockmind'); ?>
                </label>
                <textarea id="fbs-supplier-address" 
                          name="address" 
                          rows="3" 
                          class="fbs-form-textarea"></textarea>
            </div>
            
            <div class="fbs-form-group">
                <label for="fbs-supplier-notes" class="fbs-form-label">
                    <?php esc_html_e('Notes', 'fbs-stockmind'); ?>
                </label>
                <textarea id="fbs-supplier-notes" 
                          name="notes" 
                          rows="4" 
                          class="fbs-form-textarea"></textarea>
            </div>
            
            <div class="fbs-form-group">
                <label class="fbs-form-label">
                    <input type="checkbox" 
                           id="fbs-supplier-is-active" 
                           name="is_active" 
                           value="1" 
                           checked 
                           class="fbs-form-checkbox" />
                    <?php esc_html_e('Active Supplier', 'fbs-stockmind'); ?>
                </label>
            </div>
        </form>
        
        <div class="fbs-modal-footer">
            <button type="button" class="fbs-btn fbs-btn-secondary fbs-modal-cancel">
                <?php esc_html_e('Cancel', 'fbs-stockmind'); ?>
            </button>
            <button type="button" class="fbs-btn fbs-btn-primary" id="fbs-save-supplier">
                <span class="fbs-btn-icon">💾</span>
                <?php esc_html_e('Save Supplier', 'fbs-stockmind'); ?>
            </button>
        </div>
    </div>
</div>

<!-- View Products Modal -->
<div id="fbs-supplier-products-modal" class="fbs-modal" style="display: none;">
    <div class="fbs-modal-overlay"></div>
    <div class="fbs-modal-content">
        <div class="fbs-modal-header">
            <h2 class="fbs-modal-title" id="fbs-supplier-products-modal-title">
                <?php esc_html_e('Products', 'fbs-stockmind'); ?>
            </h2>
            <button type="button" class="fbs-modal-close">&times;</button>
        </div>
        <div class="fbs-modal-body" id="fbs-supplier-products-list">
            <p class="fbs-loading"><?php esc_html_e('Loading...', 'fbs-stockmind'); ?></p>
        </div>
        <div class="fbs-modal-footer">
            <button type="button" class="fbs-btn fbs-btn-secondary fbs-modal-close">
                <?php esc_html_e('Close', 'fbs-stockmind'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="fbs-toast-container" class="fbs-toast-container"></div>
