/**
 * FBS StockMind Frontend JavaScript
 * Customer reminder functionality
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        FBSStockMindFrontend.init();
    });

    // Main frontend object
    window.FBSStockMindFrontend = {
        
        /**
         * Initialize frontend functionality
         */
        init: function() {
            this.initReminderForm();
            this.initNotifications();
        },

        /**
         * Initialize reminder form functionality
         */
        initReminderForm: function() {
            // Show reminder form after a short delay
            setTimeout(function() {
                $('#fbs-reminder-form').fadeIn(300);
            }, 2000);
            
            // Close form handlers
            $('.fbs-reminder-close, .fbs-reminder-overlay').on('click', function() {
                $('#fbs-reminder-form').fadeOut(300);
            });
            
            // Set reminder handler
            $('.fbs-set-reminder').on('click', function() {
                FBSStockMindFrontend.setReminder($(this));
            });
        },

        /**
         * Initialize notifications
         */
        initNotifications: function() {
            // Check for URL parameters that might indicate success/error
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('fbs_status');
            const message = urlParams.get('fbs_message');
            
            if (status && message) {
                FBSStockMindFrontend.showToast(status, decodeURIComponent(message));
            }
        },

        /**
         * Set reminder for a product
         */
        setReminder: function($button) {
            const productId = $button.data('product-id');
            const orderId = $button.data('order-id');
            const customerEmail = $button.data('customer-email');
            
            if (!productId || !orderId || !customerEmail) {
                FBSStockMindFrontend.showToast('error', 'Missing required information.');
                return;
            }
            
            // Disable button and show loading
            $button.prop('disabled', true).html('<span class="fbs-btn-icon">⏳</span> Setting...');
            
            // Make AJAX request
            $.ajax({
                url: fbsStockMind.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_set_reminder',
                    nonce: fbsStockMind.nonce,
                    customer_email: customerEmail,
                    product_id: productId,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        $button.html('<span class="fbs-btn-icon">✅</span> Reminder Set!');
                        $button.removeClass('fbs-btn-primary').addClass('fbs-btn-success');
                        
                        FBSStockMindFrontend.showToast('success', response.data);
                        
                        // Track the success
                        FBSStockMindFrontend.trackReminderSet(productId, orderId, 'success');
                    } else {
                        $button.prop('disabled', false).html('<span class="fbs-btn-icon">🔔</span> Enable Reminder');
                        
                        FBSStockMindFrontend.showToast('error', response.data);
                        
                        // Track the error
                        FBSStockMindFrontend.trackReminderSet(productId, orderId, 'error', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $button.prop('disabled', false).html('<span class="fbs-btn-icon">🔔</span> Enable Reminder');
                    
                    FBSStockMindFrontend.showToast('error', fbsStockMind.strings.error);
                    
                    // Track the error
                    FBSStockMindFrontend.trackReminderSet(productId, orderId, 'error', error);
                }
            });
        },

        /**
         * Track reminder set event
         */
        trackReminderSet: function(productId, orderId, status, errorMessage) {
            // You can integrate with analytics here
            if (typeof gtag !== 'undefined') {
                gtag('event', 'reminder_set', {
                    'product_id': productId,
                    'order_id': orderId,
                    'status': status,
                    'error_message': errorMessage || null
                });
            }
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
            
            // Create container if it doesn't exist
            if ($('#fbs-toast-container').length === 0) {
                $('body').append('<div id="fbs-toast-container" class="fbs-toast-container"></div>');
            }
            
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
        },

        /**
         * Close reminder form
         */
        closeReminderForm: function() {
            $('#fbs-reminder-form').fadeOut(300);
        },

        /**
         * Show reminder form
         */
        showReminderForm: function() {
            $('#fbs-reminder-form').fadeIn(300);
        }
    };

    // Make functions available globally
    window.fbsStockMind = window.fbsStockMind || {};
    window.fbsStockMind.showToast = FBSStockMindFrontend.showToast;
    window.fbsStockMind.closeReminderForm = FBSStockMindFrontend.closeReminderForm;
    window.fbsStockMind.showReminderForm = FBSStockMindFrontend.showReminderForm;

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // ESC key to close reminder form
        if (e.key === 'Escape' && $('#fbs-reminder-form').is(':visible')) {
            FBSStockMindFrontend.closeReminderForm();
        }
    });

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, pause any timers if needed
        } else {
            // Page is visible again, resume any timers if needed
        }
    });

    // Handle form submissions to prevent double-submission
    $('.fbs-set-reminder').on('click', function(e) {
        const $button = $(this);
        
        if ($button.prop('disabled') || $button.hasClass('fbs-btn-success')) {
            e.preventDefault();
            return false;
        }
    });

    // Add loading states to buttons
    $(document).on('click', '.fbs-btn', function() {
        const $button = $(this);
        
        if ($button.hasClass('fbs-btn-loading')) {
            return false;
        }
        
        // Add loading class for visual feedback
        $button.addClass('fbs-btn-loading');
        
        // Remove loading class after a delay (in case AJAX doesn't complete)
        setTimeout(() => {
            $button.removeClass('fbs-btn-loading');
        }, 10000);
    });

    // Remove loading class when AJAX completes
    $(document).ajaxComplete(function() {
        $('.fbs-btn-loading').removeClass('fbs-btn-loading');
    });

})(jQuery);
