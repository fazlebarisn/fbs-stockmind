<?php
/**
 * Singleton trait for FBS StockMind plugin
 *
 * @package fbs-stockmind
 * @since 1.0.0
 * @author Fazle Bari <fazlebarisn@gmail.com>
 */

namespace FBS_StockMind\Inc\Traits;

defined('ABSPATH') or die('Nice Try!');

trait Singleton
{
    /**
     * The single instance of the class
     *
     * @var object
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected static $instance = null;

    /**
     * Get the single instance of the class
     *
     * @return object
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    protected function __construct()
    {
        // Constructor implementation
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    private function __clone()
    {
        // Prevent cloning
    }

    /**
     * Prevent unserialization
     *
     * @since 1.0.0
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    public function __wakeup()
    {
        // Prevent unserialization
    }
}
