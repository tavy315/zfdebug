<?php
namespace ZFDebug\Controller\Plugin\Debug\Plugin;

use ZFDebug\Controller\Plugin\Debug;

/**
 * Class Exception
 *
 * @package ZFDebug\Controller\Plugin\Debug\Plugin
 * @author  Octavian Matei <octav@octav.name>
 * @since   10.11.2016
 */
class Exception extends \Zend_Controller_Plugin_Abstract implements PluginInterface
{
    protected static $logger;

    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $identifier = 'exception';

    /**
     * Original error handler
     *
     * @var callable
     */
    protected $originalErrorHandler = null;

    /**
     * Contains any errors
     *
     * @var array
     */
    public static $errors = [];

    protected $rendered = false;

    /**
     * Get the ZFDebug logger
     *
     * @return \Zend_Log|bool
     */
    public static function getLogger()
    {
        if (!self::$logger) {
            /** @var Debug $zfDebug */
            if ($zfDebug = \Zend_Controller_Front::getInstance()->getPlugin('ZFDebug_Controller_Plugin_Debug')) {
                /**
                 * @see Log::getLog()
                 * @var \Zend_Log
                 */
                self::$logger = $zfDebug->getPlugin('Log')->getLog();
            } else {
                return false;
            }
        }

        return self::$logger;
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJPSURBVDjLpZPLS5RhFMYfv9QJlelTQZwRb2OKlKuINuHGLlBEBEOLxAu46oL0F0QQFdWizUCrWnjBaDHgThCMoiKkhUONTqmjmDp2GZ0UnWbmfc/ztrC+GbM2dXbv4ZzfeQ7vefKMMfifyP89IbevNNCYdkN2kawkCZKfSPZTOGTf6Y/m1uflKlC3LvsNTWArr9BT2LAf+W73dn5jHclIBFZyfYWU3or7T4K7AJmbl/yG7EtX1BQXNTVCYgtgbAEAYHlqYHlrsTEVQWr63RZFuqsfDAcdQPrGRR/JF5nKGm9xUxMyr0YBAEXXHgIANq/3ADQobD2J9fAkNiMTMSFb9z8ambMAQER3JC1XttkYGGZXoyZEGyTHRuBuPgBTUu7VSnUAgAUAWutOV2MjZGkehgYUA6O5A0AlkAyRnotiX3MLlFKduYCqAtuGXpyH0XQmOj+TIURt51OzURTYZdBKV2UBSsOIcRp/TVTT4ewK6idECAihtUKOArWcjq/B8tQ6UkUR31+OYXP4sTOdisivrkMyHodWejlXwcC38Fvs8dY5xaIId89VlJy7ACpCNCFCuOp8+BJ6A631gANQSg1mVmOxxGQYRW2nHMha4B5WA3chsv22T5/B13AIicWZmNZ6cMchTXUe81Okzz54pLi0uQWp+TmkZqMwxsBV74Or3od4OISPr0e3SHa3PX0f3HXKofNH/UIG9pZ5PeUth+CyS2EMkEqs4fPEOBJLsyske48/+xD8oxcAYPzs4QaS7RR2kbLTTOTQieczfzfTv8QPldGvTGoF6/8AAAAASUVORK5CYII=';
    }

    /**
     * Creates Error Plugin ans sets the Error Handler
     *
     */
    public function __construct()
    {
        \Zend_Controller_Front::getInstance()->registerPlugin($this);

        $this->originalErrorHandler = set_error_handler([ $this, 'errorHandler' ]);
    }

    /**
     * Gets menu tab for the Debug Bar
     *
     * @return string
     */
    public function getTab()
    {
        return '';
    }

    /**
     * Gets content panel for the Debug Bar
     *
     * @return string
     */
    public function getPanel()
    {
        $this->rendered = true;

        return '';
    }

    /**
     * Debug Bar php error handler
     *
     * @param string $level
     * @param string $message
     * @param string $file
     * @param string $line
     *
     * @return bool
     */
    public function errorHandler($level, $message, $file, $line)
    {
        if (!($level & error_reporting())) {
            return false;
        }

        switch ($level) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $method = 'notice';
                $type = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $method = 'warn';
                $type = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $method = 'crit';
                $type = 'Fatal Error';
                break;
            default:
                $method = 'err';
                $type = 'Unknown, ' . $level;
                break;
        }

        self::$errors[] = [
            'type'    => $type,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'trace'   => debug_backtrace(),
        ];

        $message = sprintf('%s in %s on line %d', $message, str_replace($_SERVER['DOCUMENT_ROOT'], '', $file), $line);

        if (($logger = self::getLogger())) {
            $logger->$method($message);
        }

        if ($this->originalErrorHandler) {
            return call_user_func_array($this->originalErrorHandler, func_get_args());
        }

        return false;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param \Zend_Controller_Request_Abstract
     *
     */
    public function routeStartup(\Zend_Controller_Request_Abstract $request)
    {
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param \Zend_Controller_Request_Abstract
     *
     */
    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param \Zend_Controller_Request_Abstract
     *
     */
    public function preDispatch(\Zend_Controller_Request_Abstract $request)
    {
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param \Zend_Controller_Request_Abstract
     *
     */
    public function postDispatch(\Zend_Controller_Request_Abstract $request)
    {
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param \Zend_Controller_Request_Abstract
     *
     */
    public function dispatchLoopStartup(\Zend_Controller_Request_Abstract $request)
    {
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param \Zend_Controller_Request_Abstract
     */
    public function dispatchLoopShutdown()
    {
        $response = \Zend_Controller_Front::getInstance()->getResponse();
        /** @var \Exception $e */
        foreach ($response->getException() as $e) {
            $exception = get_class($e) . ': ' . $e->getMessage()
                . ' thrown in ' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $e->getFile())
                . ' on line ' . $e->getLine();
            $exception .= '<ol>';
            foreach ($e->getTrace() as $t) {
                $func = $t['function'] . '()';
                if (isset($t['class'])) {
                    $func = $t['class'] . $t['type'] . $func;
                }
                if (!isset($t['file'])) {
                    $t['file'] = 'unknown';
                }
                if (!isset($t['line'])) {
                    $t['line'] = 'n/a';
                }
                $exception .= '<li>' . $func . ' in '
                    . str_replace($_SERVER['DOCUMENT_ROOT'], '', $t['file'])
                    . ' on line ' . $t['line'] . '</li>';
            }
            $exception .= '</ol>';
            if ($logger = self::getLogger()) {
                $logger->crit($exception);
            }
        }
    }
}
