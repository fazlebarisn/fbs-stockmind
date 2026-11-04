<?php
/**
 * AI Assistant Chat UI and API Integration
 *
 * Supports WordPress Native AI Client (Settings -> Connectors) as well as
 * direct Google Gemini and OpenAI API keys.
 *
 * @package fbs-stockmind
 * @since   1.0.0
 * @author  Fazle Bari <fazle@fbsfusion.com>
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
     * Check if WordPress native AI Client (wp_ai_client_prompt) is available and ready.
     *
     * @return bool
     * @since 1.3.0
     */
    public static function is_wp_ai_ready()
    {
        if (!function_exists('wp_ai_client_prompt')) {
            return false;
        }

        try {
            $builder = wp_ai_client_prompt('Test');
            if (function_exists('WordPress\\AI\\get_preferred_models_for_text_generation')) {
                $models = \WordPress\AI\get_preferred_models_for_text_generation();
                if (!empty($models)) {
                    $builder = $builder->using_model_preference(...$models);
                }
            }
            return (bool) $builder->is_supported_for_text_generation();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Retrieve status of registered WordPress AI Provider Connectors (Settings -> Connectors).
     *
     * @return array<string, mixed>
     * @since 1.3.0
     */
    public static function get_connectors_status()
    {
        $providers   = [];
        $has_any_key = false;

        if (function_exists('wp_get_connectors')) {
            $all_connectors = (array) wp_get_connectors();
            foreach ($all_connectors as $id => $data) {
                if (!is_array($data) || ($data['type'] ?? '') !== 'ai_provider') {
                    continue;
                }

                $auth          = $data['authentication'] ?? [];
                $setting_name  = $auth['setting_name'] ?? '';
                $constant_name = $auth['constant_name'] ?? '';
                $env_var_name  = $auth['env_var_name'] ?? '';

                $has_key = false;
                if (!empty($env_var_name) && false !== getenv($env_var_name) && '' !== getenv($env_var_name)) {
                    $has_key = true;
                } elseif (!empty($constant_name) && defined($constant_name) && '' !== constant($constant_name)) {
                    $has_key = true;
                } elseif (!empty($setting_name) && '' !== get_option($setting_name, '')) {
                    $has_key = true;
                }

                $plugin_active = true;
                if (isset($data['plugin']['is_active']) && is_callable($data['plugin']['is_active'])) {
                    try {
                        $plugin_active = (bool) call_user_func($data['plugin']['is_active']);
                    } catch (\Throwable $e) {
                        $plugin_active = false;
                    }
                }

                $is_connected = $has_key && $plugin_active;
                if ($is_connected) {
                    $has_any_key = true;
                }

                $providers[$id] = [
                    'id'            => $id,
                    'name'          => $data['name'] ?? ucfirst((string) $id),
                    'description'   => $data['description'] ?? '',
                    'has_key'       => $has_key,
                    'plugin_active' => $plugin_active,
                    'is_connected'  => $is_connected,
                ];
            }
        }

        $is_ready = self::is_wp_ai_ready();

        return [
            'is_ready'       => $is_ready,
            'has_any_key'    => $has_any_key || $is_ready,
            'connectors_url' => admin_url('options-connectors.php'),
            'wp_ai_url'      => defined('WPAI_VERSION') ? admin_url('options-general.php?page=ai-wp-admin') : '',
            'providers'      => $providers,
        ];
    }

    /**
     * Generate text using WordPress Core AI Client (wp_ai_client_prompt).
     *
     * @param string $prompt              User prompt content.
     * @param string $system_instructions Optional system instructions.
     * @param float  $temperature         Generation temperature.
     * @return string|\WP_Error
     * @since 1.3.0
     */
    public static function generate_with_wp_ai($prompt, $system_instructions = '', $temperature = 0.7)
    {
        if (!function_exists('wp_ai_client_prompt')) {
            return new \WP_Error(
                'wp_ai_unavailable',
                __('WordPress AI Client API (wp_ai_client_prompt) is not available on this site. Please use WordPress 7.0+ or select Google Gemini / OpenAI API Key in StockMind Settings.', 'fbs-stockmind')
            );
        }

        try {
            $builder = wp_ai_client_prompt($prompt);

            if (function_exists('WordPress\\AI\\get_preferred_models_for_text_generation')) {
                $models = \WordPress\AI\get_preferred_models_for_text_generation();
                if (!empty($models)) {
                    $builder = $builder->using_model_preference(...$models);
                }
            }

            if (!empty($system_instructions)) {
                $builder = $builder->using_system_instruction($system_instructions);
            }

            $builder = $builder->using_temperature((float) $temperature);

            if (!$builder->is_supported_for_text_generation()) {
                return new \WP_Error(
                    'wp_ai_not_configured',
                    __('No connected AI provider found in WordPress Settings -> Connectors.', 'fbs-stockmind')
                );
            }

            return $builder->generate_text();
        } catch (\Throwable $e) {
            return new \WP_Error('wp_ai_exception', $e->getMessage());
        }
    }

    /**
     * Determine whether any AI backend is currently configured and ready.
     *
     * @return bool
     * @since 1.3.0
     */
    public static function is_ai_configured()
    {
        $provider = (string) fbs_stockmind_get_option('ai_provider', '');
        $api_key  = (string) fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', ''));

        if ('wp_native' === $provider) {
            return self::is_wp_ai_ready();
        }

        if (!empty($api_key)) {
            return true;
        }

        return self::is_wp_ai_ready();
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
     *
     * @param string $hook Admin screen hook.
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
            'nonce'   => wp_create_nonce('fbs_stockmind_ai_nonce'),
            'strings' => [
                'confirmClear' => __('Are you sure you want to clear this conversation?', 'fbs-stockmind'),
                'clearedMsg'   => __('Conversation cleared! Ask me anything about your WooCommerce stock, predictions, or suppliers.', 'fbs-stockmind'),
                'networkError' => __('Sorry, there was a network error connecting to the AI service.', 'fbs-stockmind'),
                'genericError' => __('Sorry, I encountered an error:', 'fbs-stockmind'),
            ],
        ]);
    }

    /**
     * Render the AI Assistant page
     */
    public function render_page()
    {
        $is_configured  = self::is_ai_configured();
        $is_pro_active  = fbs_stockmind_is_pro_active();
        $provider       = (string) fbs_stockmind_get_option('ai_provider', self::is_wp_ai_ready() ? 'wp_native' : 'gemini');
        $api_key        = (string) fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', ''));
        if (empty($api_key) && self::is_wp_ai_ready()) {
            $provider = 'wp_native';
        }

        $provider_labels = [
            'wp_native' => __('WordPress AI Connector', 'fbs-stockmind'),
            'gemini'    => __('Google Gemini', 'fbs-stockmind'),
            'openai'    => __('OpenAI', 'fbs-stockmind'),
        ];
        $provider_label = $provider_labels[$provider] ?? __('AI Connected', 'fbs-stockmind');

        $usage_key   = 'fbs_stockmind_ai_usage_' . gmdate('Y_m_d');
        $usage_count = (int) get_transient($usage_key);
        $remaining   = max(0, 5 - $usage_count);
        $pro_url     = fbs_stockmind_utm_url('plugins/', 'pro-upgrade', 'ai-assistant-header', 'fbs-stockmind');
        ?>
        <div class="wrap fbs-ai-page-wrap">
            <div class="fbs-ai-hero">
                <div>
                    <span class="fbs-ai-hero__eyebrow"><?php esc_html_e('Real-Time WooCommerce Inventory Intelligence', 'fbs-stockmind'); ?></span>
                    <h1>🤖 <?php esc_html_e('StockMind AI Inventory Assistant', 'fbs-stockmind'); ?></h1>
                    <p><?php esc_html_e('Ask natural-language questions about your live stock health, predicted runout dates, low-stock items, and restocking priorities.', 'fbs-stockmind'); ?></p>
                </div>
                <div class="fbs-ai-hero__actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings&tab=ai')); ?>" class="fbs-ai-btn fbs-ai-btn--glass">
                        ⚙️ <?php esc_html_e('AI Settings', 'fbs-stockmind'); ?>
                    </a>
                    <?php if (!$is_pro_active) : ?>
                        <a href="<?php echo esc_url($pro_url); ?>" target="_blank" rel="noopener noreferrer" class="fbs-ai-btn fbs-ai-btn--emerald">
                            🚀 <?php esc_html_e('Unlock Unlimited AI (Pro) ↗', 'fbs-stockmind'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$is_configured) : ?>
                <div class="fbs-ai-setup-card">
                    <div class="fbs-ai-setup-card__icon">🔌</div>
                    <h2><?php esc_html_e('Connect Your AI Provider to Start Chatting', 'fbs-stockmind'); ?></h2>
                    <p><?php esc_html_e('Connect via WordPress Settings ➔ Connectors (zero extra keys needed) or paste a free Google Gemini or OpenAI API key in StockMind Settings.', 'fbs-stockmind'); ?></p>
                    <div class="fbs-ai-setup-card__btns">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings&tab=ai')); ?>" class="button button-primary button-hero">
                            ⚙️ <?php esc_html_e('Configure StockMind AI Settings', 'fbs-stockmind'); ?>
                        </a>
                        <?php if (function_exists('wp_ai_client_prompt')) : ?>
                            <a href="<?php echo esc_url(admin_url('options-connectors.php')); ?>" class="button button-secondary button-hero">
                                🔗 <?php esc_html_e('Open WordPress Connectors', 'fbs-stockmind'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="fbs-ai-chat-container">
                    <div class="fbs-ai-chat-header">
                        <div class="fbs-ai-status">
                            <span class="fbs-ai-status__dot"></span>
                            <strong><?php echo esc_html($provider_label); ?></strong>
                            <span class="fbs-ai-status__sep">•</span>
                            <?php if ($is_pro_active) : ?>
                                <span class="fbs-ai-quota-pill fbs-ai-quota-pill--pro"><?php esc_html_e('Unlimited Pro Queries', 'fbs-stockmind'); ?></span>
                            <?php else : ?>
                                <span class="fbs-ai-quota-pill" id="fbs-ai-quota-pill">
                                    <?php
                                    printf(
                                        /* translators: %d: Remaining daily free queries */
                                        esc_html__('%d / 5 Free Queries Left Today', 'fbs-stockmind'),
                                        absint($remaining)
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="fbs-ai-header-btns">
                            <button type="button" id="fbs-ai-clear-chat" class="button button-small">
                                🗑️ <?php esc_html_e('Clear Chat', 'fbs-stockmind'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="fbs-ai-prompts-bar">
                        <span class="fbs-ai-prompts-label"><?php esc_html_e('Quick Prompts:', 'fbs-stockmind'); ?></span>
                        <button type="button" class="fbs-ai-prompt-chip" data-prompt="<?php esc_attr_e('Summarize my current inventory health and highlight any urgent stockout risks.', 'fbs-stockmind'); ?>">
                            📊 <?php esc_html_e('Inventory Health Summary', 'fbs-stockmind'); ?>
                        </button>
                        <button type="button" class="fbs-ai-prompt-chip" data-prompt="<?php esc_attr_e('Which products are at risk of running out of stock soonest, and when should I reorder them?', 'fbs-stockmind'); ?>">
                            ⚠️ <?php esc_html_e('Urgent Stockout Risks', 'fbs-stockmind'); ?>
                        </button>
                        <button type="button" class="fbs-ai-prompt-chip" data-prompt="<?php esc_attr_e('Give me an actionable restocking checklist for this week based on my store data.', 'fbs-stockmind'); ?>">
                            📦 <?php esc_html_e('Weekly Restock Checklist', 'fbs-stockmind'); ?>
                        </button>
                    </div>

                    <div class="fbs-ai-chat-messages" id="fbs-ai-chat-messages">
                        <div class="fbs-ai-message fbs-ai-message-assistant">
                            <div class="fbs-ai-message-avatar">🤖</div>
                            <div class="fbs-ai-message-content">
                                <p>
                                    <?php
                                    if ($is_pro_active) {
                                        esc_html_e('Hello! I am your StockMind Pro AI Inventory Analyst. I have live access to your stock levels, runout predictions, supplier lead times, and best-sellers. What would you like to analyze today?', 'fbs-stockmind');
                                    } else {
                                        esc_html_e('Hello! I am your StockMind AI Assistant. I can summarize your live WooCommerce inventory health and highlight urgent low-stock alerts. Pick a quick prompt above or ask a question below!', 'fbs-stockmind');
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="fbs-ai-chat-input-area">
                        <form id="fbs-ai-chat-form">
                            <textarea id="fbs-ai-chat-input" placeholder="<?php esc_attr_e('Ask about your inventory health, urgent stock risks, or reorder priorities... (Press Enter to send)', 'fbs-stockmind'); ?>" rows="2"></textarea>
                            <button type="submit" class="fbs-ai-send-btn" id="fbs-ai-chat-submit">
                                <?php esc_html_e('Send ↑', 'fbs-stockmind'); ?>
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
            wp_send_json_error(__('Permission denied.', 'fbs-stockmind'));
        }

        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw JSON string is decoded and sanitized per item below
        $history_json    = isset($_POST['history']) ? wp_unslash($_POST['history']) : '[]';
        $history_decoded = json_decode($history_json, true);
        $history         = [];

        if (is_array($history_decoded)) {
            foreach ($history_decoded as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (isset($item['role'], $item['content'])) {
                    $role      = ('assistant' === $item['role']) ? 'assistant' : 'user';
                    $history[] = [
                        'role'    => $role,
                        'content' => sanitize_textarea_field((string) $item['content']),
                    ];
                } elseif (isset($item['sender'], $item['text'])) {
                    $role      = ('assistant' === $item['sender'] || 'bot' === $item['sender']) ? 'assistant' : 'user';
                    $history[] = [
                        'role'    => $role,
                        'content' => sanitize_textarea_field((string) $item['text']),
                    ];
                }
            }
        }

        if (empty($message)) {
            wp_send_json_error(__('Message is empty.', 'fbs-stockmind'));
        }

        $provider = (string) fbs_stockmind_get_option('ai_provider', self::is_wp_ai_ready() ? 'wp_native' : 'gemini');
        $model    = (string) fbs_stockmind_get_option('ai_model', 'gemini-2.5-flash');
        $api_key  = (string) fbs_stockmind_get_option('ai_api_key', fbs_stockmind_get_option('openai_key', ''));

        // Auto-fallback to WP Native AI Connector if direct API key is empty and WP Connector is ready
        if ('wp_native' !== $provider && empty($api_key) && self::is_wp_ai_ready()) {
            $provider = 'wp_native';
        }

        if ('wp_native' === $provider && !self::is_wp_ai_ready()) {
            wp_send_json_error(__('No active WordPress AI Connector found. Please connect a provider in Settings -> Connectors or enter an API key in StockMind Settings.', 'fbs-stockmind'));
        }

        if ('wp_native' !== $provider && empty($api_key)) {
            wp_send_json_error(__('API key is missing. Please configure it in StockMind -> Settings -> AI Assistant.', 'fbs-stockmind'));
        }

        $is_pro_active = fbs_stockmind_is_pro_active();
        $usage_key     = 'fbs_stockmind_ai_usage_' . gmdate('Y_m_d');
        $usage_count   = (int) get_transient($usage_key);

        if (!$is_pro_active && $usage_count >= 5) {
            $pro_upgrade_url = fbs_stockmind_utm_url('plugins/', 'pro-upgrade', 'ai-daily-limit', 'fbs-stockmind');
            wp_send_json_success([
                'reply'     => sprintf(
                    "✨ **Daily Free Limit Reached (5/5)**\n\nYou have used your 5 free AI inventory queries for today. [Upgrade to FBS StockMind Pro](%s) to unlock unlimited AI Assistant conversations, 365-day seasonal forecasting, 1-click PDF Purchase Orders, and Twilio SMS alerts!",
                    $pro_upgrade_url
                ),
                'remaining' => 0,
            ]);
        }

        $tone          = (string) fbs_stockmind_get_option('ai_tone', 'analytical');
        $system_prompt = "You are the StockMind AI Inventory Assistant for WooCommerce. Help the store owner manage stock, prevent stockouts, and prioritize purchase orders using ONLY the real store data provided. Tone: {$tone}. Use concise bullet points and bold key numbers.";

        if ('wp_native' === $provider) {
            $response = $this->call_wp_native_ai($system_prompt, $history, $message);
        } elseif ('openai' === $provider) {
            $messages = [
                [
                    'role'    => 'system',
                    'content' => $system_prompt . ' Always call the provided tools to fetch accurate live WooCommerce inventory data before answering.',
                ],
            ];

            $recent_history = array_slice($history, -10);
            foreach ($recent_history as $msg) {
                $messages[] = [
                    'role'    => 'assistant' === $msg['role'] ? 'assistant' : 'user',
                    'content' => $msg['content'],
                ];
            }

            $messages[] = [
                'role'    => 'user',
                'content' => $message,
            ];

            $response = $this->call_openai_api($api_key, $messages, $model, 0);
        } else {
            $response = $this->call_gemini_api($api_key, $system_prompt, $history, $message, $model);
        }

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        if (!$is_pro_active) {
            $new_count = $usage_count + 1;
            set_transient($usage_key, $new_count, DAY_IN_SECONDS);
            $response['remaining'] = max(0, 5 - $new_count);
        }

        wp_send_json_success($response);
    }

    /**
     * Build live store context JSON string for prompt-based AI providers (WP Native & Gemini).
     *
     * @return string
     * @since 1.3.0
     */
    private function build_live_store_context()
    {
        $ai_tools = AI_Tools::get_instance();
        $payload  = [
            'summary' => $ai_tools->get_inventory_summary(),
        ];

        if (fbs_stockmind_is_pro_active()) {
            $payload['high_risk_products'] = $ai_tools->get_stock_risk_products(10);
            $payload['best_sellers_30d']   = $ai_tools->get_best_sellers(30, 5);
        }

        return "CURRENT LIVE WOOCOMMERCE STORE DATA:\n" . wp_json_encode($payload) . "\n\n";
    }

    /**
     * Call WordPress Native AI Connector (wp_ai_client_prompt).
     *
     * @param string $system_prompt System instruction.
     * @param array  $history       Sanitized chat history.
     * @param string $message       Current user message.
     * @return array|\WP_Error
     * @since 1.3.0
     */
    private function call_wp_native_ai($system_prompt, $history, $message)
    {
        $prompt_parts   = [];
        $prompt_parts[] = $this->build_live_store_context();

        $recent_history = array_slice($history, -8);
        if (!empty($recent_history)) {
            $prompt_parts[] = "RECENT CONVERSATION:";
            foreach ($recent_history as $item) {
                $speaker        = ('assistant' === $item['role']) ? 'Assistant' : 'Store Owner';
                $prompt_parts[] = "{$speaker}: {$item['content']}";
            }
            $prompt_parts[] = '';
        }

        $prompt_parts[] = "Store Owner Question: {$message}";

        $text = self::generate_with_wp_ai(implode("\n", $prompt_parts), $system_prompt, 0.7);
        if (is_wp_error($text)) {
            return $text;
        }

        return [
            'reply' => (string) $text,
        ];
    }

    /**
     * Call Google Gemini API with valid v1beta models and multi-turn history.
     *
     * @param string $api_key       Gemini API key.
     * @param string $system_prompt System instructions.
     * @param array  $history       Sanitized chat history.
     * @param string $message       User message.
     * @param string $model         Preferred Gemini model slug.
     * @return array|\WP_Error
     */
    private function call_gemini_api($api_key, $system_prompt, $history, $message, $model = 'gemini-2.5-flash')
    {
        $valid_gemini_models = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-2.5-pro'];
        if (!in_array($model, $valid_gemini_models, true)) {
            $model = 'gemini-2.5-flash';
        }

        $initial_instruction = $system_prompt . "\n\n" . $this->build_live_store_context();
        $contents            = [];
        $recent_history      = array_slice($history, -8);

        if (!empty($recent_history)) {
            foreach ($recent_history as $idx => $item) {
                $role = ('assistant' === $item['role']) ? 'model' : 'user';
                $text = $item['content'];
                if (0 === $idx && 'user' === $role) {
                    $text = $initial_instruction . 'User Question: ' . $text;
                }
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        $current_text = empty($contents) ? ($initial_instruction . 'User Question: ' . $message) : $message;
        $contents[]   = [
            'role'  => 'user',
            'parts' => [['text' => $current_text]],
        ];

        $body = [
            'contents'         => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
            ],
        ];

        $candidates = array_values(array_unique([
            $model,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
        ]));

        $last_err = __('Failed to connect to Google Gemini API.', 'fbs-stockmind');

        foreach ($candidates as $candidate_model) {
            // phpcs:disable PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Optional direct integration when merchant supplies their own Gemini API key
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($candidate_model) . ':generateContent?key=' . rawurlencode($api_key);

            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode($body),
                'timeout' => 30,
            ]);
            // phpcs:enable PluginCheck.CodeAnalysis.AIProvider.DirectIntegration

            if (is_wp_error($response)) {
                $last_err = $response->get_error_message();
                continue;
            }

            $code     = wp_remote_retrieve_response_code($response);
            $raw_body = wp_remote_retrieve_body($response);
            $data     = json_decode($raw_body, true);

            if (200 === $code && !empty($data['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'reply' => (string) $data['candidates'][0]['content']['parts'][0]['text'],
                ];
            }

            if (!empty($data['error']['message'])) {
                $last_err = (string) $data['error']['message'];
            }
        }

        return new \WP_Error('gemini_error', $last_err);
    }

    /**
     * Call OpenAI API with recursive tool execution support.
     *
     * @param string $api_key  OpenAI API key.
     * @param array  $messages Chat messages array.
     * @param string $model    OpenAI model slug.
     * @param int    $depth    Current recursion depth for tool calls.
     * @return array|\WP_Error
     */
    private function call_openai_api($api_key, $messages, $model = 'gpt-4o-mini', $depth = 0)
    {
        $depth = (int) $depth;
        if ($depth > 5) {
            return new \WP_Error('ai_error', __('Maximum tool call depth reached.', 'fbs-stockmind'));
        }

        $allowed_openai_models = ['gpt-4o-mini', 'gpt-4o', 'gpt-3.5-turbo'];
        $selected_model        = in_array($model, $allowed_openai_models, true) ? $model : 'gpt-4o-mini';

        $tools = AI_Tools::get_instance()->get_openai_tools_definition();

        $body = [
            'model'       => $selected_model,
            'messages'    => $messages,
            'tools'       => $tools,
            'tool_choice' => 'auto',
            'temperature' => 0.7,
        ];

        // phpcs:disable PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Optional direct integration when merchant supplies their own OpenAI API key
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 30,
        ]);
        // phpcs:enable PluginCheck.CodeAnalysis.AIProvider.DirectIntegration

        if (is_wp_error($response)) {
            return $response;
        }

        $code     = wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
        $data     = json_decode($raw_body, true);

        if (200 !== $code) {
            $err = isset($data['error']['message']) ? (string) $data['error']['message'] : __('Unknown OpenAI API error.', 'fbs-stockmind');
            return new \WP_Error('ai_error', $err);
        }

        $choice = $data['choices'][0]['message'] ?? [];

        if (!empty($choice['tool_calls']) && is_array($choice['tool_calls'])) {
            $messages[] = $choice;

            foreach ($choice['tool_calls'] as $tool_call) {
                if (($tool_call['type'] ?? '') === 'function') {
                    $function_name = (string) ($tool_call['function']['name'] ?? '');
                    $function_args = json_decode((string) ($tool_call['function']['arguments'] ?? '{}'), true);
                    if (!is_array($function_args)) {
                        $function_args = [];
                    }

                    $tool_result = $this->execute_tool($function_name, $function_args);

                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => (string) ($tool_call['id'] ?? ''),
                        'name'         => $function_name,
                        'content'      => wp_json_encode($tool_result),
                    ];
                }
            }

            return $this->call_openai_api($api_key, $messages, $selected_model, $depth + 1);
        }

        return [
            'reply' => (string) ($choice['content'] ?? ''),
        ];
    }

    /**
     * Execute a specific AI Tool
     *
     * @param string $name Tool name.
     * @param array  $args Tool arguments.
     * @return array
     */
    private function execute_tool($name, $args)
    {
        $ai_tools = AI_Tools::get_instance();

        switch ($name) {
            case 'get_inventory_summary':
                return $ai_tools->get_inventory_summary();

            case 'get_stock_risk_products':
                $limit = isset($args['limit']) ? min(20, max(1, intval($args['limit']))) : 5;
                return $ai_tools->get_stock_risk_products($limit);

            case 'get_best_sellers':
                $days  = isset($args['days']) ? min(365, max(1, intval($args['days']))) : 30;
                $limit = isset($args['limit']) ? min(20, max(1, intval($args['limit']))) : 5;
                return $ai_tools->get_best_sellers($days, $limit);

            default:
                return ['error' => 'Tool not found.'];
        }
    }
}
