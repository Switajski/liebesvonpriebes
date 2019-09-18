<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_apc
 * implements the APC Opcode Cache
 */
class cache_apc implements ICachingMethod
{
    use JTLCacheTrait;
    
    /**
     * @var cache_apc
     */
    public static $instance;

    /**
     * check whether apc_ or apcu_ functions should be used
     *
     * @var bool
     */
    private $u;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->journalID     = 'apc_journal';
        $this->options       = $options;
        $this->u             = function_exists('apcu_store');
        self::$instance      = $this;
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        $func = $this->u ? 'apcu_store' : 'apc_store';

        return $func($this->options['prefix'] . $cacheID, $content, ($expiration === null)
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
        $func = $this->u ? 'apcu_store' : 'apc_store';

        return $func($this->prefixArray($keyValue), null, ($expiration === null)
            ? $this->options['lifetime']
            : $expiration);
    }

    /**
     * @param string $cacheID
     * @return bool|mixed
     */
    public function load($cacheID)
    {
        $func = $this->u ? 'apcu_fetch' : 'apc_fetch';

        return $func($this->options['prefix'] . $cacheID);
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
        $func         = $this->u ? 'apcu_fetch' : 'apc_fetch';
        $prefixedKeys = [];
        foreach ($cacheIDs as $_cid) {
            $prefixedKeys[] = $this->options['prefix'] . $_cid;
        }
        $res = $this->dePrefixArray($func($prefixedKeys));

        //fill up with false values
        return array_merge(array_fill_keys($cacheIDs, false), $res);
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return ((function_exists('apc_store') && function_exists('apc_exists')) ||
                (function_exists('apcu_store') && function_exists('apcu_exists')));
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID)
    {
        $func = $this->u ? 'apcu_delete' : 'apc_delete';

        return $func($this->options['prefix'] . $cacheID);
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return $this->u ? apcu_clear_cache() : apc_clear_cache('user');
    }

    /**
     * @param string $cacheID
     * @return bool|string[]
     */
    public function keyExists($cacheID)
    {
        $func = $this->u ? 'apcu_exists' : 'apc_exists';

        return $func($this->options['prefix'] . $cacheID);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        try {
            $tmp   = $this->u ? apcu_cache_info() : apc_cache_info('user');
            $stats = [
                'entries' => isset($tmp['num_entries']) ? $tmp['num_entries'] : 0,
                'hits'    => isset($tmp['num_hits']) ? $tmp['num_hits'] : 0,
                'misses'  => isset($tmp['num_misses']) ? $tmp['num_misses'] : 0,
                'inserts' => isset($tmp['num_inserts']) ? $tmp['num_inserts'] : 0,
                'mem'     => isset($tmp['mem_size']) ? $tmp['mem_size'] : 0
            ];
        } catch (Exception $e) {
            $stats = [];
        }

        return $stats;
    }
}
