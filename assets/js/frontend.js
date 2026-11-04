/**
 * FBS StockMind Frontend JavaScript
 * Customer replenishment reminder functionality
 *
 * @package fbs-stockmind
 * @since   1.0.0
 * @author  Fazle Bari <fazle@fbsfusion.com>
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        FBSStockMindFrontend.init();
    });

    window.FBSStockMindFrontend = {

        /**
         * Initialize frontend functionality
         */
        init: function() {
            this.initReminderForm();
        },

        /**
         * Initialize reminder form functionality
         */
        initReminderForm: function() {
            // Dismiss inline reminder card smoothly
            $(document).on('click', '.fbs-reminder-close, .fbs-reminder-overlay', function() {
                $('#fbs-reminder-form').slideUp(220);
            });

            // Set reminder handler
            $(document).on('click', '.fbs-set-reminder', function(e) {
                e.preventDefault();
                const $btn = $(this);
                if ($btn.prop('disabled') || $btn.hasClass('fbs-btn-success')) {
                    return false;
                }
                FBSStockMindFrontend.setReminder($btn);
            });
        },

        /**
         * Set reminder for a product
         */
        setReminder: function($button) {
            const productId = $button.data('product-id');
            const orderId = $button.data('order-id') || 0;
            let customerEmail = $button.data('customer-email');

            const $emailInput = $button.closest('.fbs-product-actions').find('.fbs-reminder-email-input');
            if ($emailInput.length > 0) {
                customerEmail = $emailInput.val().trim();
                if (!customerEmail) {
                    FBSStockMindFrontend.showToast('error', 'Please enter your email address.');
                    $emailInput.trigger('focus');
                    return;
                }
            }

            if (!productId || !customerEmail) {
                FBSStockMindFrontend.showToast('error', 'Missing required information.');
                return;
            }

            const strings = (typeof fbsStockMind !== 'undefined' && fbsStockMind.strings) ? fbsStockMind.strings : {};
            const ajaxUrl = (typeof fbsStockMind !== 'undefined' && fbsStockMind.ajaxUrl) ? fbsStockMind.ajaxUrl : '/wp-admin/admin-ajax.php';
            const nonce = (typeof fbsStockMind !== 'undefined' && fbsStockMind.nonce) ? fbsStockMind.nonce : '';

            $button.prop('disabled', true).html('<span class="fbs-btn-icon">⏳</span> ' + (strings.setting || 'Setting reminder...'));

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fbs_stockmind_ajax',
                    action_type: 'set_reminder',
                    nonce: nonce,
                    customer_email: customerEmail,
                    product_id: productId,
                    order_id: orderId
                },
                success: function(response) {
                    if (response && response.success) {
                        $button.html('<span class="fbs-btn-icon">✅</span> ' + (strings.reminderSet || 'Reminder Set!'));
                        $button.removeClass('fbs-btn-primary').addClass('fbs-btn-success');
                        FBSStockMindFrontend.showToast('success', response.data);
                        FBSStockMindFrontend.trackReminderSet(productId, orderId, 'success');
                    } else {
                        const errText = (response && response.data) ? response.data : (strings.error || 'An error occurred.');
                        if (errText === strings.alreadyExists || errText === 'Reminder already exists.') {
                            $button.prop('disabled', true).html('<span class="fbs-btn-icon">✅</span> ' + (strings.alreadySet || 'Reminder Already Set'));
                            $button.removeClass('fbs-btn-primary').addClass('fbs-btn-success');
                        } else {
                            $button.prop('disabled', false).html('<span class="fbs-btn-icon">🔔</span> ' + (strings.enableReminder || 'Enable Reminder'));
                            FBSStockMindFrontend.showToast('error', errText);
                        }
                        FBSStockMindFrontend.trackReminderSet(productId, orderId, 'error', errText);
                    }
                },
                error: function(xhr, status, error) {
                    $button.prop('disabled', false).html('<span class="fbs-btn-icon">🔔</span> ' + (strings.enableReminder || 'Enable Reminder'));
                    FBSStockMindFrontend.showToast('error', strings.error || 'An error occurred. Please try again.');
                    FBSStockMindFrontend.trackReminderSet(productId, orderId, 'error', error);
                }
            });
        },

        /**
         * Track reminder set event via GA4 if present
         */
        trackReminderSet: function(productId, orderId, status, errorMessage) {
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
         * Show toast notification (XSS-safe)
         */
        showToast: function(type, message) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            const safeType = Object.prototype.hasOwnProperty.call(icons, type) ? type : 'info';

            const toast = $('<div>')
                .addClass('fbs-toast fbs-toast-' + safeType)
                .append($('<span class="fbs-toast-icon">').text(icons[safeType]))
                .append($('<span class="fbs-toast-message">').text(message || ''))
                .append($('<button type="button" class="fbs-toast-close" aria-label="Close">&times;</button>'));

            if ($('#fbs-toast-container').length === 0) {
                $('body').append('<div id="fbs-toast-container" class="fbs-toast-container"></div>');
            }

            $('#fbs-toast-container').append(toast);

            setTimeout(function() {
                toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            toast.find('.fbs-toast-close').on('click', function() {
                toast.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        closeReminderForm: function() {
            $('#fbs-reminder-form').slideUp(220);
        },

        showReminderForm: function() {
            $('#fbs-reminder-form').slideDown(220);
        }
    };

    window.fbsStockMind = window.fbsStockMind || {};
    window.fbsStockMind.showToast = FBSStockMindFrontend.showToast;
    window.fbsStockMind.closeReminderForm = FBSStockMindFrontend.closeReminderForm;
    window.fbsStockMind.showReminderForm = FBSStockMindFrontend.showReminderForm;

})(jQuery);
