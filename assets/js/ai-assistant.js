jQuery(document).ready(function($) {
    const $form = $('#fbs-ai-chat-form');
    const $input = $('#fbs-ai-chat-input');
    const $messages = $('#fbs-ai-chat-messages');
    const $submit = $('#fbs-ai-chat-submit');
    const $quotaPill = $('#fbs-ai-quota-pill');

    if (!$form.length || !$messages.length) {
        return;
    }

    const STORAGE_KEY = 'fbsStockMindChatHistory_v2';
    let chatHistory = [];

    function scrollToBottom() {
        $messages.scrollTop($messages[0].scrollHeight);
    }

    function escapeHtml(raw) {
        return String(raw || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMarkdownSafe(content) {
        // Escape all HTML first to prevent XSS from product titles or prompt echoes
        let safe = escapeHtml(content);

        // Convert safe HTTPS markdown links [label](https://...)
        safe = safe.replace(
            /\[([^\]]+)\]\((https:\/\/[^\s)]+)\)/g,
            '<a href="$2" target="_blank" rel="noopener noreferrer" class="fbs-ai-inline-link">$1 ↗</a>'
        );

        // Bold **text**
        safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

        // Bullet lines starting with "- " or "* "
        safe = safe.replace(/^(?:-|\*)\s+(.+)$/gm, '<span class="fbs-ai-bullet">• $1</span>');

        // Line breaks
        safe = safe.replace(/\n/g, '<br>');

        return safe;
    }

    function createMessageElement(role, content) {
        const isUser = role === 'user';
        const avatar = isUser ? '👤' : '🤖';
        const className = isUser ? 'fbs-ai-message-user' : 'fbs-ai-message-assistant';

        const $wrapper = $('<div>').addClass('fbs-ai-message ' + className);
        const $avatar = $('<div>').addClass('fbs-ai-message-avatar').text(avatar);
        const $content = $('<div>').addClass('fbs-ai-message-content');
        const $p = $('<p>').html(formatMarkdownSafe(content));

        $content.append($p);
        $wrapper.append($avatar).append($content);
        return $wrapper;
    }

    function addMessage(role, content, save) {
        if (typeof save === 'undefined') {
            save = true;
        }
        $messages.append(createMessageElement(role, content));
        scrollToBottom();

        chatHistory.push({ role: role, content: String(content || '') });
        if (chatHistory.length > 24) {
            chatHistory = chatHistory.slice(-24);
        }

        if (save) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(chatHistory));
            } catch (e) {}
        }
    }

    // Restore saved conversation history on load
    try {
        const savedHistory = localStorage.getItem(STORAGE_KEY);
        if (savedHistory) {
            const parsed = JSON.parse(savedHistory);
            if (Array.isArray(parsed) && parsed.length > 0) {
                $messages.empty();
                parsed.forEach(function(msg) {
                    if (msg && (msg.role === 'user' || msg.role === 'assistant') && msg.content) {
                        addMessage(msg.role, msg.content, false);
                    }
                });
            }
        }
    } catch (e) {}

    // Quick Prompt Chips
    $(document).on('click', '.fbs-ai-prompt-chip', function(e) {
        e.preventDefault();
        if ($submit.prop('disabled')) {
            return;
        }
        const promptText = $(this).data('prompt');
        if (promptText) {
            $input.val(promptText);
            $form.trigger('submit');
        }
    });

    // Clear Chat handler
    $(document).on('click', '#fbs-ai-clear-chat', function(e) {
        e.preventDefault();
        const confirmMsg = (fbsStockMindAI.strings && fbsStockMindAI.strings.confirmClear) || 'Are you sure you want to clear this conversation?';
        const clearedMsg = (fbsStockMindAI.strings && fbsStockMindAI.strings.clearedMsg) || 'Conversation cleared! How can I help you with your WooCommerce inventory today?';
        if (window.confirm(confirmMsg)) {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (err) {}
            chatHistory = [];
            $messages.empty();
            addMessage('assistant', clearedMsg, false);
        }
    });

    function showTypingIndicator() {
        const $typing = $(
            '<div class="fbs-ai-message fbs-ai-message-assistant" id="fbs-ai-typing">' +
                '<div class="fbs-ai-message-avatar">🤖</div>' +
                '<div class="fbs-ai-typing"><span></span><span></span><span></span></div>' +
            '</div>'
        );
        $messages.append($typing);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        $('#fbs-ai-typing').remove();
    }

    $input.on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $form.trigger('submit');
        }
    });

    $form.on('submit', function(e) {
        e.preventDefault();

        const message = $input.val().trim();
        if (!message) {
            return;
        }

        $input.val('');
        $input.prop('disabled', true);
        $submit.prop('disabled', true);

        const priorHistory = chatHistory.slice(-10);
        addMessage('user', message, true);
        showTypingIndicator();

        $.ajax({
            url: fbsStockMindAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fbs_stockmind_ai_chat',
                nonce: fbsStockMindAI.nonce,
                message: message,
                history: JSON.stringify(priorHistory)
            },
            success: function(response) {
                removeTypingIndicator();

                if (response && response.success && response.data && response.data.reply) {
                    addMessage('assistant', response.data.reply, true);
                    if ($quotaPill.length && typeof response.data.remaining !== 'undefined') {
                        $quotaPill.text(response.data.remaining + ' / 5 Free Queries Left Today');
                    }
                } else {
                    const errPrefix = (fbsStockMindAI.strings && fbsStockMindAI.strings.genericError) || 'Sorry, I encountered an error:';
                    const errDetail = (response && response.data) ? String(response.data) : 'Please verify your AI settings.';
                    addMessage('assistant', errPrefix + ' ' + errDetail, false);
                }
            },
            error: function() {
                removeTypingIndicator();
                const netErr = (fbsStockMindAI.strings && fbsStockMindAI.strings.networkError) || 'Sorry, there was a network error connecting to the AI.';
                addMessage('assistant', netErr, false);
            },
            complete: function() {
                $input.prop('disabled', false).trigger('focus');
                $submit.prop('disabled', false);
            }
        });
    });
});
