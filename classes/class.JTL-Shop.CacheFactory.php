<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CacheFactory
 * @deprecated since 4.0
 */
class CacheFactory
{
    /**
     * @var null
     */
    protected $_settings;

    /**
     * @var null
     */
    protected $_method;

    /**
     * @var null
     */
    protected $_cache;

    /**
     * @var null|CacheFactory
     */
    private static $_instance;

    /**
     * @param null $method
     * @param array|null $options
     * @return CacheFactory|null
     */
    public static function getInstance($method = null, array $options = null)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($method, $options);
        }

        return self::$_instance;
    }

    /**
     * CacheFactory constructor.
     * @param $method
     * @param $options
     */
    protected function __construct($method, $options)
    {
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return false;
    }

    /**
     * @param null|string $method
     * @param array|null $options
     * @return bool
     */
    public function changeMethod($method = null, array $options = null)
    {
        return false;
    }

    /**
     * @return null
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function fetch($key)
    {
        return false;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function store($key, $value)
    {
        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function clear($key)
    {
        return false;
    }

    /**
     * @param null|string $key
     * @return bool
     */
    public function clearAll($key = null)
    {
        return false;
    }

    /**
     * @return null
     */
    public function getStats()
    {
        return null;
    }

    /**
     * @return bool
     */
    protected function _useDebug()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function _isActivated()
    {
        return false;
    }
}
