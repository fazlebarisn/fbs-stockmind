<?php
/**
 * AI Assistant Chat UI and WordPress Native AI Client Integration
 *
 * Uses WordPress's native AI Client (wp_ai_client_prompt) & Settings -> Connectors.
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
                __('WordPress AI Client API (wp_ai_client_prompt) is not available. Please use WordPress 7.0+ or activate the official WordPress AI plugin.', 'fbs-stockmind')
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
     * Determine whether WordPress AI Connection is ready.
     *
     * @return bool
     * @since 1.3.0
     */
    public static function is_ai_configured()
    {
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

        $ai_css_ver = file_exists(FBS_STOCKMIND_DIR_PATH . '/assets/css/ai-assistant.css')
            ? FBS_STOCKMIND_VERSION . '.' . filemtime(FBS_STOCKMIND_DIR_PATH . '/assets/css/ai-assistant.css')
            : FBS_STOCKMIND_VERSION;
        $ai_js_ver = file_exists(FBS_STOCKMIND_DIR_PATH . '/assets/js/ai-assistant.js')
            ? FBS_STOCKMIND_VERSION . '.' . filemtime(FBS_STOCKMIND_DIR_PATH . '/assets/js/ai-assistant.js')
            : FBS_STOCKMIND_VERSION;

        wp_enqueue_style(
            'fbs-stockmind-ai',
            FBS_STOCKMIND_URL . '/assets/css/ai-assistant.css',
            [],
            $ai_css_ver
        );

        wp_enqueue_script(
            'fbs-stockmind-ai',
            FBS_STOCKMIND_URL . '/assets/js/ai-assistant.js',
            ['jquery'],
            $ai_js_ver,
            true
        );

        wp_localize_script('fbs-stockmind-ai', 'fbsStockMindAI', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('fbs_stockmind_ai_nonce'),
            'strings' => [
                'confirmClear' => __('Are you sure you want to clear this conversation?', 'fbs-stockmind'),
                'clearedMsg'   => __('Conversation cleared! Ask me anything about your WooCommerce stock, predictions, or suppliers.', 'fbs-stockmind'),
                'networkError' => __('Sorry, there was a network error connecting to WordPress AI.', 'fbs-stockmind'),
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
        $status         = self::get_connectors_status();
        $connectors_url = $status['connectors_url'];

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
                    <h2><?php esc_html_e('Connect WordPress AI to Start Chatting', 'fbs-stockmind'); ?></h2>
                    <p><?php esc_html_e('StockMind uses WordPress\'s native AI Client & Connectors. Connect Google Gemini, OpenAI, Anthropic, or Mistral once in WordPress Settings ➔ Connectors to unlock live inventory chat.', 'fbs-stockmind'); ?></p>
                    <div class="fbs-ai-setup-card__btns">
                        <a href="<?php echo esc_url($connectors_url); ?>" class="button button-primary button-hero">
                            ⚙️ <?php esc_html_e('Manage WordPress Connectors', 'fbs-stockmind'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fbs-stockmind-settings&tab=ai')); ?>" class="button button-secondary button-hero">
                            🤖 <?php esc_html_e('View StockMind AI Settings', 'fbs-stockmind'); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="fbs-ai-chat-container">
                    <div class="fbs-ai-chat-header">
                        <div class="fbs-ai-status">
                            <span class="fbs-ai-status__dot"></span>
                            <strong><?php esc_html_e('Connected via WordPress AI Connectors', 'fbs-stockmind'); ?></strong>
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
                                        esc_html_e('Hello! I am your StockMind AI Assistant, powered by your WordPress AI Connection. Pick a quick prompt above or ask a question about your inventory below!', 'fbs-stockmind');
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
     * Handle AJAX chat request via WordPress Native AI Client
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

        if (!self::is_wp_ai_ready()) {
            wp_send_json_error(__('No active AI provider is connected in WordPress. Please go to Settings -> Connectors to connect Google Gemini, OpenAI, or Anthropic.', 'fbs-stockmind'));
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

        $response = $this->call_wp_native_ai($system_prompt, $history, $message);

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
     * Build live store context JSON string for the WordPress AI Client prompt.
     *
     * @return string
     * @since 1.3.0
     */
    private function build_live_store_context()
    {
        $ai_tools = AI_Tools::get_instance();
        $payload  = [
            'summary'            => $ai_tools->get_inventory_summary(),
            'high_risk_products' => $ai_tools->get_stock_risk_products(fbs_stockmind_is_pro_active() ? 15 : 5),
            'best_sellers_30d'   => $ai_tools->get_best_sellers(30, 5),
        ];

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
            $prompt_parts[] = 'RECENT CONVERSATION:';
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
}
