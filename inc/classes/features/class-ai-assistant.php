<?php
/**
 * AI Assistant Chat UI and API Integration (Pro)
 *
 * @package fbs-stockmind
 * @since 1.0.0
 */

namespace FBS_StockMind\Inc\Features;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') || die('Nice Try!');

class AI_Assistant
{
    use Singleton;

    /**
     * Constructor
     */
    protected function __construct()
    {
        add_action('admin_menu', [$this, 'add_submenu_page'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_fbs_stockmind_ai_chat', [$this, 'handle_chat_request']);
    }

    /**
     * Add submenu page under StockMind
     */
    public function add_submenu_page()
    {
        add_submenu_page(
            'fbs-stockmind',
            __('AI Assistant', 'fbs-stockmind'),
            __('AI Assistant', 'fbs-stockmind'),
            'manage_woocommerce',
            'fbs-stockmind-ai-assistant',
            [$this, 'render_page']
        );
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'fbs-stockmind-ai-assistant') === false) {
            return;
        }

        wp_enqueue_style(
            'fbs-stockmind-ai',
            FBS_STOCKMIND_URL . '/assets/css/ai-assistant.css',
            [],
            FBS_STOCKMIND_VERSION
        );

        wp_enqueue_script(
            'fbs-stockmind-ai',
            FBS_STOCKMIND_URL . '/assets/js/ai-assistant.js',
            ['jquery'],
            FBS_STOCKMIND_VERSION,
            true
        );

        wp_localize_script('fbs-stockmind-ai', 'fbsStockMindAI', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fbs_stockmind_ai_nonce'),
        ]);
    }

    /**
     * Render the page
     */
    public function render_page()
    {
        $api_key = fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', ''));
        ?>
        <div class="wrap fbs-stockmind-wrap">
            <h1 class="wp-heading-inline">
                <span class="fbs-title-icon">🤖</span> <?php esc_html_e('StockMind AI Inventory Assistant', 'fbs-stockmind'); ?>
            </h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings&tab=ai')); ?>" class="page-title-action">
                ⚙️ <?php esc_html_e('AI Assistant Settings', 'fbs-stockmind'); ?>
            </a>
            <hr class="wp-header-end">

            <?php if (empty($api_key)): ?>
                <div class="notice notice-warning" style="margin-top: 15px; padding: 15px; border-left-color: #f59e0b;">
                    <p style="font-size: 14px; margin-bottom: 8px;">
                        <strong><?php esc_html_e('AI API Key Required', 'fbs-stockmind'); ?></strong><br>
                        <?php esc_html_e('Please configure your Google Gemini or OpenAI API key to activate your interactive inventory assistant.', 'fbs-stockmind'); ?>
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings&tab=ai')); ?>" class="button button-primary" style="margin-top: 5px;">
                        ⚙️ <?php esc_html_e('Go to AI Assistant Settings', 'fbs-stockmind'); ?> &rarr;
                    </a>
                </div>
            <?php else: ?>
                <div class="fbs-ai-chat-container">
                    <div class="fbs-ai-chat-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; border-bottom: 1px solid #e5e7eb; background: #fafafa; border-radius: 12px 12px 0 0;">
                        <div style="font-size: 13px; color: #6b7280; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                            <span><?php esc_html_e('AI Assistant Connected', 'fbs-stockmind'); ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings&tab=ai')); ?>" class="button button-small" style="color: #4b5563; border-color: #d1d5db; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <span>⚙️</span> <?php esc_html_e('AI Assistant Settings', 'fbs-stockmind'); ?>
                            </a>
                            <button type="button" id="fbs-ai-clear-chat" class="button button-small" style="color: #6b7280; border-color: #d1d5db;">
                                <span>🗑️</span> <?php esc_html_e('Clear Chat', 'fbs-stockmind'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="fbs-ai-chat-messages" id="fbs-ai-chat-messages">
                        <div class="fbs-ai-message fbs-ai-message-assistant">
                            <div class="fbs-ai-message-avatar">🤖</div>
                            <div class="fbs-ai-message-content">
                                <p>Hello! I am your StockMind AI Assistant. <?php echo function_exists('fbs_stockmind_pro_is_woocommerce_active') ? 'I can analyze your inventory, predict runouts, and help you make smart purchasing decisions.' : 'I can provide a high-level summary of your inventory health. Upgrade to Pro for advanced risk predictions and best-seller reports!'; ?> How can I help you today?</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="fbs-ai-chat-input-area">
                        <form id="fbs-ai-chat-form">
                            <textarea id="fbs-ai-chat-input" placeholder="<?php esc_attr_e('Ask about your inventory health, stock risks, or runouts...', 'fbs-stockmind'); ?>" rows="2"></textarea>
                            <button type="submit" class="fbs-btn fbs-btn-primary" id="fbs-ai-chat-submit">
                                <span><?php esc_html_e('Send', 'fbs-stockmind'); ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handle AJAX chat request
     */
    public function handle_chat_request()
    {
        check_ajax_referer('fbs_stockmind_ai_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied.');
        }

        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        $history_json = isset($_POST['history']) ? wp_unslash($_POST['history']) : '[]';
        $history = json_decode($history_json, true);
        
        if (empty($message)) {
            wp_send_json_error('Message is empty.');
        }

        if (!is_array($history)) {
            $history = [];
        }

        $provider = fbs_stockmind_get_option('ai_provider', 'gemini');
        $model    = fbs_stockmind_get_option('ai_model', 'gemini-3.6-flash');
        $api_key  = fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', ''));

        if (empty($api_key)) {
            wp_send_json_error('API key is missing. Please configure it in Settings -> AI Assistant.');
        }
        
        $is_pro_active = function_exists('fbs_stockmind_pro_is_woocommerce_active');
        $usage_key = 'fbs_stockmind_ai_usage_' . gmdate('Y_m_d');
        $usage_count = (int) get_transient($usage_key);

        if (!$is_pro_active && $usage_count >= 5) {
            wp_send_json_success([
                'reply' => '⚠️ **Daily Limit Reached** ⚠️<br><br>You have reached the limit of 5 questions per day for the free version. Please <a href="https://wpbay.com/product/fbs-stockmind-pro/" target="_blank">upgrade to StockMind Pro</a> to unlock unlimited AI Assistant usage, custom visual charts, SMS alerts, and more!'
            ]);
        }

        $tone = fbs_stockmind_get_option('ai_tone', 'analytical');
        $system_prompt = "You are the StockMind AI Inventory Assistant for WooCommerce. You help store owners manage stock, predict runouts, and make smart purchasing decisions. Tone: {$tone}. Be concise and professional.";

        if ($provider === 'openai') {
            $messages = [
                [
                    'role' => 'system',
                    'content' => $system_prompt . ' Use the provided tools to fetch real data.'
                ]
            ];

            // Append recent history (last 10 messages)
            $recent_history = array_slice($history, -10);
            foreach ($recent_history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = [
                        'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                        'content' => sanitize_text_field($msg['content'])
                    ];
                }
            }

            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];

            $response = $this->call_openai_api($api_key, $messages, $model);
        } else {
            // Google Gemini
            $response = $this->call_gemini_api($api_key, $system_prompt, $history, $message, $model);
        }

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        if (!$is_pro_active) {
            set_transient($usage_key, $usage_count + 1, DAY_IN_SECONDS);
        }

        wp_send_json_success($response);
    }

    /**
     * Call Google Gemini API
     */
    private function call_gemini_api($api_key, $system_prompt, $history, $message, $model = 'gemini-3.6-flash')
    {
        // Inject live store inventory data as real context for Gemini
        $ai_tools = AI_Tools::get_instance();
        $inventory_data = $ai_tools->get_inventory_summary();
        $context_str = "CURRENT WOOCOMMERCE STORE DATA:\n" . wp_json_encode($inventory_data) . "\n\n";

        // Format multi-turn conversation history for Gemini
        $contents = [];
        $initial_instruction = $system_prompt . "\n\n" . $context_str;

        $recent_history = array_slice($history, -8);
        if (!empty($recent_history)) {
            foreach ($recent_history as $idx => $item) {
                $role = (isset($item['role']) && $item['role'] === 'assistant') ? 'model' : 'user';
                $text = isset($item['content']) ? sanitize_text_field($item['content']) : '';
                if ($idx === 0 && $role === 'user') {
                    $text = $initial_instruction . "User Question: " . $text;
                }
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $text]]
                ];
            }
        }

        $current_text = empty($contents) ? ($initial_instruction . "User Question: " . $message) : $message;
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $current_text]]
        ];

        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7
            ]
        ];

        $candidates = array_unique([
            $model,
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-3.7-flash',
            'gemini-flash-latest'
        ]);

        $last_err = 'Failed to connect to Gemini.';

        foreach ($candidates as $candidate_model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($candidate_model) . ':generateContent?key=' . urlencode($api_key);

            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode($body),
                'timeout' => 30
            ]);

            if (is_wp_error($response)) {
                $last_err = $response->get_error_message();
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            $raw_body = wp_remote_retrieve_body($response);
            $data = json_decode($raw_body, true);

            if ($code === 200 && !empty($data['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'reply' => $data['candidates'][0]['content']['parts'][0]['text']
                ];
            }

            if (!empty($data['error']['message'])) {
                $last_err = $data['error']['message'];
            }
        }

        return new \WP_Error('gemini_error', $last_err);
    }

    /**
     * Call OpenAI API (handling Tool calls)
     */
    private function call_openai_api($api_key, $messages, $depth = 0)
    {
        // Prevent infinite loops
        if ($depth > 5) {
            return new \WP_Error('ai_error', 'Maximum tool depth reached.');
        }

        $tools = AI_Tools::get_instance()->get_openai_tools_definition();

        $body = [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => 'auto',
            'temperature' => 0.7
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code !== 200) {
            $err = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown OpenAI error';
            return new \WP_Error('ai_error', $err);
        }

        $choice = $data['choices'][0]['message'];

        // If the AI decided to call a tool
        if (!empty($choice['tool_calls'])) {
            $messages[] = $choice; // Add the tool call message to history

            foreach ($choice['tool_calls'] as $tool_call) {
                if ($tool_call['type'] === 'function') {
                    $function_name = $tool_call['function']['name'];
                    $function_args = json_decode($tool_call['function']['arguments'], true) ?: [];
                    
                    // Execute local PHP tool
                    $tool_result = $this->execute_tool($function_name, $function_args);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $tool_call['id'],
                        'name' => $function_name,
                        'content' => json_encode($tool_result)
                    ];
                }
            }

            // Recursively call OpenAI again with the tool result appended
            return $this->call_openai_api($api_key, $messages, $depth + 1);
        }

        // Return the final text response
        return [
            'reply' => $choice['content']
        ];
    }

    /**
     * Execute a specific AI Tool
     */
    private function execute_tool($name, $args)
    {
        $ai_tools = AI_Tools::get_instance();
        
        switch ($name) {
            case 'get_inventory_summary':
                return $ai_tools->get_inventory_summary();
                
            case 'get_stock_risk_products':
                $limit = isset($args['limit']) ? intval($args['limit']) : 5;
                return $ai_tools->get_stock_risk_products($limit);
                
            case 'get_best_sellers':
                $days = isset($args['days']) ? intval($args['days']) : 30;
                $limit = isset($args['limit']) ? intval($args['limit']) : 5;
                return $ai_tools->get_best_sellers($days, $limit);
                
            default:
                return ['error' => 'Tool not found.'];
        }
    }
}
