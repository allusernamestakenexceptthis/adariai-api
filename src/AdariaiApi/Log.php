<?php
/**
 * Log handles logs and has log_info, log_debug, log_warn and log_formatter methods.
 * they take message and params as arguments. params is an array of key value pairs. 
 *
 * PHP version 7.4.3
 *
 * @category AdariAI
 * @package  AdariaiApi
 * @author   Ari Adari <admin@ariadari.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @version  GIT:1.0.0
 * @link     http://aridadari.com http://gomilkyway.com
 */

namespace AdariaiApi;

/**
 * Log class
 *
 * @category AdariAI
 * @package  AdariaiApi
 * @author   Ari Adari <admin@ariadari.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     http://aridadari.com http://gomilkyway.com
 */

class Log
{
    // 0 = Debug, 1 = Info, 2 = Warn, 3 = Error, 4 = Off
    private static $_logLevel = 4;

    /**
     * Set the log level
     *
     * @param string $level The log level (debug, info, warn, error, off)
     *
     * @return void
     */
    public static function setLogLevel($level)
    {
        switch (strtolower($level)) {
        case 'debug':
            self::$_logLevel = 0;
            break;
        case 'info':
            self::$_logLevel = 1;
            break;
        case 'warn':
            self::$_logLevel = 2;
            break;
        case 'error':
            self::$_logLevel = 3;
            break;
        case 'off':
            self::$_logLevel = 4;
            break;
        default:
            throw new \Exception('Invalid log level: ' . $level);
        }
    }

    /**
     * Log a debug message
     * 
     * @param string $message The message to log
     * @param array  $context The context to log
     *
     * @return void
     */
    public static function debug($message, $context = array())
    {
        self::log(0, $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message The message to log
     * @param array  $context The context to log
     *
     * @return void
     */
    public static function info($message, $context = array())
    {
        self::log(1, $message, $context);
    }

    /**
     * Log a warn message
     * 
     * @param string $message The message to log
     * @param array  $context The context to log
     *
     * @return void
     */
    public static function warn($message, $context = array())
    {
        self::log(2, $message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message The message to log
     * @param array  $context The context to log
     *
     * @return void
     */
    public static function error($message, $context = array())
    {
        self::log(3, $message, $context);
    }

    /**
     * Log a message
     * 
     * @param string $level   The log level
     * @param string $message The message to log
     * @param array  $context The context to log
     *
     * @return void
     */
    private static function log($level, $message, $context)
    {
        if ($level >= self::$_logLevel) {
            $timestamp = date('Y-m-d H:i:s');
            $levelString = strtoupper(['debug', 'info', 'warn', 'error'][$level]);
            $formattedMessage = self::_formatMessage($message, $context);
            $logMessage = "{$timestamp} [{$levelString}] {$formattedMessage}\n";
            error_log($logMessage, 3, '/var/log/php.log');
        }
    }

    /**
     * Format a message
     * 
     * @param string $message The message to log
     * @param array  $context The context to log
     *
     * @return string
     */
    private static function _formatMessage($message, $context)
    {
        if (count($context) > 0) {
            foreach ($context as $key => $value) {
                $message = str_replace("{{$key}}", $value, $message);
            }
        }
        return $message;
    }
}
