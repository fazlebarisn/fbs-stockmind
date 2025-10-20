<?php
/**
 * Plugin deactivation class
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc;

use FBS_StockMind\Inc\Traits\Singleton;

defined('ABSPATH') or die('Nice Try!');

class Deactivate
{
    use Singleton;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function __construct()
    {
        $this->clear_scheduled_events();
        $this->flush_rewrite_rules();
    }

    /**
     * Clear scheduled cron events
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function clear_scheduled_events()
    {
        wp_clear_scheduled_hook('fbs_stockmind_daily_predictions');
        wp_clear_scheduled_hook('fbs_stockmind_daily_reminders');
    }

    /**
     * Flush rewrite rules
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function flush_rewrite_rules()
    {
        flush_rewrite_rules();
    }
}
