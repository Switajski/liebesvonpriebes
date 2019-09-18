<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_memcache
 * Implements the Memcache memory object caching system - no "d" at the end
 */
class cache_memcache implements ICachingMethod
{
    use JTLCacheTrait;
    
    /**
     * @var cache_memcache
     */
    public static $instance;

    /**
     * @var Memcache
     */
    private $_memcache;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (!empty($options['memcache_host']) && !empty($options['memcache_port']) && $this->isAvailable()) {
            $this->setMemcache($options['memcache_host'], $options['memcache_port']);
            $this->isInitialized = true;
            $this->journalID     = 'memcache_journal';
            //@see http://php.net/manual/de/memcached.expiration.php
            $options['lifetime'] = min(60 * 60 * 24 * 30, $options['lifetime']);
            $this->options       = $options;
            self::$instance      = $this;
        }
    }

    /**
     * @param string $host
     * @param int    $port
     * @return $this
     */
    private function setMemcache($host, $port)
    {
        if ($this->_memcache !== null) {
            $this->_memcache->close();
        }
        $m = new Memcache();
        $m->addServer($host, (int)$port);
        $this->_memcache = $m;

        return $this;
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        return $this->_memcache->set($this->options['prefix'] . $cacheID, $content, 0, ($expiration === null)
            ? $this->options['lifetime']
            : $expiration);
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
     * @return bool
     */
    public function storeMulti($keyValue, $expiration = null)
    {
        return $this->_memcache->set($this->prefixArray($keyValue), ($expiration === null)
            ? $this->options['lifetime']
            : $expiration);
    }

    /**
     * @param string $cacheID
     * @return mixed
     */
    public function load($cacheID)
    {
        return $this->_memcache->get($this->options['prefix'] . $cacheID);
    }

    /**
     * @param array $cacheIDs
     * @return bool|mixed
     */
    public function loadMulti($cacheIDs)
    {
        if (!is_array($cacheIDs)) {
            return false;
        }
        $prefixedKeys = [];
        foreach ($cacheIDs as $_cid) {
            $prefixedKeys[] = $this->options['prefix'] . $_cid;
        }
        $res = $this->dePrefixArray($this->_memcache->get($prefixedKeys));

        //fill up result
        return array_merge(array_fill_keys($cacheIDs, false), $res);
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return class_exists('Memcache');
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID)
    {
        return $this->_memcache->delete($this->options['prefix'] . $cacheID);
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return $this->_memcache->flush();
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $stats = $this->_memcache->getStats();

        return [
            'entries' => $stats['curr_items'],
            'hits'    => $stats['get_hits'],
            'misses'  => $stats['get_misses'],
            'inserts' => $stats['cmd_set'],
            'mem'     => $stats['bytes']
        ];
    }
}
