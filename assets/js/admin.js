/**
 * FBS StockMind Admin JavaScript
 * Modern, interactive admin interface
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        FBSStockMindAdmin.init();
    });

    // Main admin object
    window.FBSStockMindAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initTabs();
            this.initModals();
            this.initButtons();
            this.initFilters();
            this.initNotifications();
            this.initTooltips();
        },

        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            $('.fbs-tab-button').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const tabId = $button.data('tab');
                
                // Update active tab button
                $('.fbs-tab-button').removeClass('active');
                $button.addClass('active');
                
                // Update active tab content
                $('.fbs-tab-content').removeClass('active');
                $(`#${tabId}-tab`).addClass('active');
            });
        },

        /**
         * Initialize modal functionality
         */
        initModals: function() {
            // Open supplier modal
            $('#fbs-add-supplier, #fbs-add-first-supplier').on('click', function() {
                FBSStockMindAdmin.openSupplierModal();
            });

            // Edit supplier modal
            $(document).on('click', '.fbs-edit-supplier', function() {
                const supplierId = $(this).data('supplier-id');
                FBSStockMindAdmin.editSupplier(supplierId);
            });

            // Close modal
            $(document).on('click', '.fbs-modal-close, .fbs-modal-overlay, .fbs-modal-cancel', function() {
                FBSStockMindAdmin.closeModal();
            });

            // Save supplier
            $('#fbs-save-supplier').on('click', function() {
                FBSStockMindAdmin.saveSupplier();
            });
        },

        /**
         * Initialize button functionality
         */
        initButtons: function() {
            // Dismiss prediction
            $(document).on('click', '.fbs-dismiss-prediction', function() {
                const predictionId = $(this).data('prediction-id');
                FBSStockMindAdmin.dismissPrediction(predictionId);
            });


            // Delete supplier
            $(document).on('click', '.fbs-delete-supplier', function() {
                const supplierId = $(this).data('supplier-id');
                FBSStockMindAdmin.deleteSupplier(supplierId);
            });

            // Deactivate reminder
            $(document).on('click', '.fbs-deactivate-reminder', function() {
                const reminderId = $(this).data('reminder-id');
                FBSStockMindAdmin.deactivateReminder(reminderId);
            });

            // Activate reminder
            $(document).on('click', '.fbs-activate-reminder', function() {
                const reminderId = $(this).data('reminder-id');
                FBSStockMindAdmin.activateReminder(reminderId);
            });

            // Delete reminder
            $(document).on('click', '.fbs-delete-reminder', function() {
                const reminderId = $(this).data('reminder-id');
                FBSStockMindAdmin.deleteReminder(reminderId);
            });

            // Calculate predictions
            $('#fbs-calculate-predictions').on('click', function() {
                FBSStockMindAdmin.calculatePredictions();
            });


            // Reset settings
            $('#fbs-reset-settings').on('click', function() {
                FBSStockMindAdmin.resetSettings();
            });
        },

        /**
         * Initialize filters
         */
        initFilters: function() {
            // Status filter
            $('#fbs-status-filter').on('change', function() {
                FBSStockMindAdmin.filterReminders();
            });

            // Search filter
            $('#fbs-search-filter').on('input', function() {
                FBSStockMindAdmin.filterReminders();
            });
        },

        /**
         * Initialize notifications
         */
        initNotifications: function() {
            // Dismiss admin notices
            $(document).on('click', '.fbs-stockmind-notice .notice-dismiss', function() {
                const $notice = $(this).closest('.fbs-stockmind-notice');
                const noticeId = $notice.data('notice-id');
                
                if (noticeId) {
                    FBSStockMindAdmin.dismissNotice(noticeId);
                }
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltip functionality if needed
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const tooltip = $element.data('tooltip');
                
                $element.on('mouseenter', function() {
                    // Show tooltip
                });
                
                $element.on('mouseleave', function() {
                    // Hide tooltip
                });
            });
        },

        /**
         * Open supplier modal
         */
        openSupplierModal: function() {
            $('#fbs-modal-title').text(fbsStockMind.strings.addSupplier || 'Add New Supplier');
            $('#fbs-supplier-form')[0].reset();
            $('#fbs-supplier-id').val('');
            $('#fbs-supplier-modal').show();
        },

        /**
         * Edit supplier
         */
        editSupplier: function(supplierId) {
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_get_supplier',
                    nonce: fbsStockMind.nonce,
                    supplier_id: supplierId
                },
                success: function(response) {
                    if (response.success) {
                        const supplier = response.data;
                        $('#fbs-modal-title').text('Edit Supplier');
                        $('#fbs-supplier-id').val(supplier.id);
                        $('#fbs-supplier-name').val(supplier.name);
                        $('#fbs-supplier-lead-time').val(supplier.lead_time);
                        $('#fbs-supplier-email').val(supplier.email);
                        $('#fbs-supplier-phone').val(supplier.phone);
                        $('#fbs-supplier-address').val(supplier.address);
                        $('#fbs-supplier-notes').val(supplier.notes);
                        $('#fbs-supplier-is-active').prop('checked', supplier.is_active == 1);
                        $('#fbs-supplier-modal').show();
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                }
            });
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.fbs-modal').hide();
        },

        /**
         * Save supplier
         */
        saveSupplier: function() {
            const $form = $('#fbs-supplier-form');
            const formData = $form.serialize();
            
            $('#fbs-save-supplier').prop('disabled', true).html('<span class="fbs-btn-icon">⏳</span> Saving...');
            
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_save_supplier',
                    nonce: fbsStockMind.nonce,
                    ...Object.fromEntries(new URLSearchParams(formData))
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        FBSStockMindAdmin.closeModal();
                        location.reload(); // Reload to show updated data
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                },
                complete: function() {
                    $('#fbs-save-supplier').prop('disabled', false).html('<span class="fbs-btn-icon">💾</span> Save Supplier');
                }
            });
        },

        /**
         * Delete supplier
         */
        deleteSupplier: function(supplierId) {
            if (!confirm(fbsStockMind.strings.confirmDelete)) {
                return;
            }

            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_delete_supplier',
                    nonce: fbsStockMind.nonce,
                    supplier_id: supplierId
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        $(`.fbs-supplier-card[data-supplier-id="${supplierId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                }
            });
        },

        /**
         * Dismiss prediction
         */
        dismissPrediction: function(predictionId) {
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_dismiss_prediction',
                    nonce: fbsStockMind.nonce,
                    prediction_id: predictionId
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        $(`.fbs-prediction-item[data-prediction-id="${predictionId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                }
            });
        },


        /**
         * Deactivate reminder
         */
        deactivateReminder: function(reminderId) {
            if (!confirm(fbsStockMind.strings.confirmDeactivate)) {
                return;
            }

            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_deactivate_reminder',
                    nonce: fbsStockMind.nonce,
                    reminder_id: reminderId
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        location.reload(); // Reload to show updated status
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                }
            });
        },

        /**
         * Activate reminder
         */
        activateReminder: function(reminderId) {
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_activate_reminder',
                    nonce: fbsStockMind.nonce,
                    reminder_id: reminderId
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        location.reload(); // Reload to show updated status
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                }
            });
        },

        /**
         * Delete reminder
         */
        deleteReminder: function(reminderId) {
            if (!confirm(fbsStockMind.strings.confirmDelete)) {
                return;
            }

            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_delete_reminder',
                    nonce: fbsStockMind.nonce,
                    reminder_id: reminderId
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        $(`.fbs-reminder-item[data-reminder-id="${reminderId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                }
            });
        },

        /**
         * Calculate predictions
         */
        calculatePredictions: function() {
            $('#fbs-calculate-predictions').prop('disabled', true).html('<span class="fbs-btn-icon">⏳</span> Calculating...');
            
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_calculate_predictions',
                    nonce: fbsStockMind.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                },
                complete: function() {
                    $('#fbs-calculate-predictions').prop('disabled', false).html('<span class="fbs-btn-icon">⚡</span> Calculate Now');
                }
            });
        },

        /**
         * Refresh predictions
         */
        refreshPredictions: function() {
            $('#fbs-refresh-predictions').prop('disabled', true).html('<span class="fbs-btn-icon">⏳</span> Refreshing...');
            
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_refresh_predictions',
                    nonce: fbsStockMind.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FBSStockMindAdmin.showToast('success', response.data);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        FBSStockMindAdmin.showToast('error', response.data);
                    }
                },
                error: function() {
                    FBSStockMindAdmin.showToast('error', fbsStockMind.strings.error);
                },
                complete: function() {
                    $('#fbs-refresh-predictions').prop('disabled', false).html('<span class="fbs-btn-icon">🔄</span> Refresh Predictions');
                }
            });
        },

        /**
         * Reset settings
         */
        resetSettings: function() {
            if (!confirm('Are you sure you want to reset all settings to their default values?')) {
                return;
            }

            // Reset form to default values
            $('#alert_window').val(14);
            $('#default_lead_time').val(7);
            $('#prediction_accuracy_threshold').val(0.8);
            $('#reminder_advance_days').val(5);
            $('#max_reminder_attempts').val(3);
            $('#enable_customer_reminders').prop('checked', true);
            $('#email_from_name').val($('#email_from_name').data('default') || '');
            $('#email_from_address').val($('#email_from_address').data('default') || '');
            $('#enable_admin_alerts').prop('checked', true);
            
            FBSStockMindAdmin.showToast('info', 'Settings reset to default values. Click Save to apply changes.');
        },

        /**
         * Filter reminders
         */
        filterReminders: function() {
            const statusFilter = $('#fbs-status-filter').val();
            const searchFilter = $('#fbs-search-filter').val().toLowerCase();
            
            $('.fbs-reminder-item').each(function() {
                const $item = $(this);
                const status = $item.data('status');
                const text = $item.text().toLowerCase();
                
                let show = true;
                
                // Status filter
                if (statusFilter && status !== statusFilter) {
                    show = false;
                }
                
                // Search filter
                if (searchFilter && !text.includes(searchFilter)) {
                    show = false;
                }
                
                if (show) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        },

        /**
         * Dismiss notice
         */
        dismissNotice: function(noticeId) {
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_dismiss_notice',
                    nonce: fbsStockMind.nonce,
                    notice_id: noticeId
                }
            });
        },

        /**
         * Show toast notification
         */
        showToast: function(type, message) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            
            const toast = $(`
                <div class="fbs-toast fbs-toast-${type}">
                    <span class="fbs-toast-icon">${icons[type]}</span>
                    <span class="fbs-toast-message">${message}</span>
                    <button class="fbs-toast-close">&times;</button>
                </div>
            `);
            
            $('#fbs-toast-container').append(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Manual close
            toast.find('.fbs-toast-close').on('click', function() {
                toast.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        }
    };

    // Make showToast available globally
    window.fbsStockMind = window.fbsStockMind || {};
    window.fbsStockMind.showToast = FBSStockMindAdmin.showToast;

})(jQuery);
