<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_xcache
 * Implements the XCache Opcode Cache
 *
 * @warning Untested
 * @warning Does not support caching groups
 */
class cache_xcache implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_xcache
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->journalID = 'xcache_journal';
        if ($this->isAvailable() === true) {
            $this->options       = $options;
            $this->isInitialized = true;
        }
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        return xcache_set(
            $this->options['prefix'] . $cacheID,
            ($this->must_be_serialized($content)
                ? serialize($content)
                : $content),
            ($expiration === null)
                ? $this->options['lifetime']
                : $expiration
        );
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
     * @return bool
     */
    public function storeMulti($keyValue, $expiration = null)
    {
        $res = true;
        foreach ($keyValue as $_key => $_value) {
            $res = $res && $this->store($_key, $_value, $expiration);
        }

        return $res;
    }

    /**
     * @param string $cacheID
     * @return bool|mixed
     */
    public function load($cacheID)
    {
        if (xcache_isset($this->options['prefix'] . $cacheID) === true) {
            $data = xcache_get($this->options['prefix'] . $cacheID);

            return $this->is_serialized($data) ? unserialize($data) : $data;
        }

        return false;
    }

    /**
     * @param array $cacheIDs
     * @return array
     */
    public function loadMulti($cacheIDs)
    {
        $res = [];
        foreach ($cacheIDs as $_cid) {
            $res[$_cid] = $this->load($cacheIDs);
        }

        return $res;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return function_exists('xcache_set');
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID)
    {
        return xcache_unset($this->options['prefix'] . $cacheID);
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return xcache_unset_by_prefix($this->options['prefix']);
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function keyExists($cacheID)
    {
        return xcache_isset($cacheID);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return [];
    }
}
