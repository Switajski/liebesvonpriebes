<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (PHP_ERROR_HANDLER) {
    /**
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param array  $errcontext
     */
    function jtlErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $types = [
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_CORE_ERROR        => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED'
        ];
        file_put_contents(
            PFAD_LOGFILES . 'php_error.log',
            date("[Y-m-d H:i:s] ") . "(" . $types[$errno] . ") File ({$errfile}, {$errline}): {$errstr}\n",
            FILE_APPEND
        );
    }

    set_error_handler('jtlErrorHandler', E_ALL ^ E_NOTICE);
}
