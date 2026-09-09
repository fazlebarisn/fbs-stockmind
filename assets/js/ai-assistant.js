jQuery(document).ready(function($) {
    const $form = $('#fbs-ai-chat-form');
    const $input = $('#fbs-ai-chat-input');
    const $messages = $('#fbs-ai-chat-messages');
    const $submit = $('#fbs-ai-chat-submit');
    
    const STORAGE_KEY = 'fbsStockMindChatHistory';
    let chatHistory = [];

    function scrollToBottom() {
        $messages.scrollTop($messages[0].scrollHeight);
    }

    function renderMessageHtml(role, content) {
        const isUser = role === 'user';
        const avatar = isUser ? '👤' : '🤖';
        const className = isUser ? 'fbs-ai-message-user' : 'fbs-ai-message-assistant';
        
        // Convert simple markdown to HTML (bold and line breaks)
        let htmlContent = content
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        return `
            <div class="fbs-ai-message ${className}">
                <div class="fbs-ai-message-avatar">${avatar}</div>
                <div class="fbs-ai-message-content">
                    <p>${htmlContent}</p>
                </div>
            </div>
        `;
    }

    function addMessage(role, content, save = true) {
        $messages.append(renderMessageHtml(role, content));
        scrollToBottom();
        
        chatHistory.push({ role: role, content: content });

        if (save) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(chatHistory));
            } catch(e) {}
        }
    }

    // Load saved conversation history on load
    try {
        const savedHistory = localStorage.getItem(STORAGE_KEY);
        if (savedHistory) {
            const parsed = JSON.parse(savedHistory);
            if (Array.isArray(parsed) && parsed.length > 0) {
                // Clear initial placeholder if we have saved history
                $messages.empty();
                parsed.forEach(function(msg) {
                    if (msg.role && msg.content) {
                        addMessage(msg.role, msg.content, false);
                    }
                });
            }
        }
    } catch(e) {}

    // Clear Chat handler
    $(document).on('click', '#fbs-ai-clear-chat', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to clear this conversation?')) {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch(e) {}
            chatHistory = [];
            $messages.empty();
            // Restore default greeting
            addMessage('assistant', 'Conversation cleared! How can I help you with your WooCommerce inventory today?');
        }
    });

    function showTypingIndicator() {
        const html = `
            <div class="fbs-ai-message fbs-ai-message-assistant" id="fbs-ai-typing">
                <div class="fbs-ai-message-avatar">🤖</div>
                <div class="fbs-ai-typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        $messages.append(html);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        $('#fbs-ai-typing').remove();
    }

    $input.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            $form.submit();
        }
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        
        const message = $input.val().trim();
        if (!message) return;
        
        // Disable input
        $input.val('');
        $input.prop('disabled', true);
        $submit.prop('disabled', true);
        
        // Add user message
        addMessage('user', message);
        showTypingIndicator();
        
        // Send to server
        $.ajax({
            url: fbsStockMindAI.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fbs_stockmind_ai_chat',
                nonce: fbsStockMindAI.nonce,
                message: message,
                history: JSON.stringify(chatHistory.slice(0, -1)) // Send history excluding the message we just added
            },
            success: function(response) {
                removeTypingIndicator();
                
                if (response.success && response.data && response.data.reply) {
                    addMessage('assistant', response.data.reply);
                } else {
                    addMessage('assistant', 'Sorry, I encountered an error. ' + (response.data || 'Please try again.'));
                }
            },
            error: function() {
                removeTypingIndicator();
                addMessage('assistant', 'Sorry, there was a network error connecting to the AI.');
            },
            complete: function() {
                $input.prop('disabled', false).focus();
                $submit.prop('disabled', false);
            }
        });
    });
});
