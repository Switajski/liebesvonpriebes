<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SingletonTrait
 */
trait SingletonTrait
{
    /**
     * @var static
     */
    private static $_instance;

    /**
     * @return static
     */
    final public static function getInstance()
    {
        if (static::$_instance === null) {
            static::$_instance = new static;
        }

        return static::$_instance;
    }

    /**
     * SingletonTrait constructor.
     */
    final private function __construct()
    {
        $this->init();
    }

    /**
     *
     */
    final private function __wakeup()
    {
    }

    /**
     *
     */
    final private function __clone()
    {
    }

    /**
     *
     */
    protected function init()
    {
    }
}
