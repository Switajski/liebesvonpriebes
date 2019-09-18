<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_session
 * Implements caching via PHP $_SESSION object
 */
class cache_session implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_session
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->isInitialized = true;
        $this->journalID     = 'session_journal';
        $this->options       = $options;
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        $_SESSION[$this->options['prefix'] . $cacheID] = [
            'value'     => $content,
            'timestamp' => time(),
            'lifetime'  => ($expiration === null)
                ? $this->options['lifetime']
                : $expiration
        ];

        return true;
    }

    /**
     * @param array    $keyValue
     * @param int|null $expiration
     * @return bool
     */
    public function storeMulti($keyValue, $expiration = null)
    {
        foreach ($keyValue as $_key => $_value) {
            $this->store($_key, $_value, $expiration);
        }

        return true;
    }

    /**
     * @param string $cacheID
     * @return bool|mixed
     */
    public function load($cacheID)
    {
        $originalCacheID = $cacheID;
        $cacheID         = $this->options['prefix'] . $cacheID;
        if (isset($_SESSION[$cacheID])) {
            $cacheValue = $_SESSION[$cacheID];
            if ((time() - $cacheValue['timestamp']) < $cacheValue['lifetime']) {
                return $cacheValue['value'];
            }
            $this->flush($originalCacheID);

            return false;
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
        return isset($_SESSION);
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID)
    {
        unset($_SESSION[$this->options['prefix'] . $cacheID]);

        return true;
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        foreach ($_SESSION as $_sessionKey => $_sessionValue) {
            if (strpos($_sessionKey, $this->options['prefix']) === 0) {
                unset($_SESSION[$_sessionKey]);
            }
        }

        return true;
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function keyExists($cacheID)
    {
        return isset($_SESSION[$this->options['prefix'] . $cacheID]);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $num = 0;
        $tmp = [];
        foreach ($_SESSION as $_sessionKey => $_sessionValue) {
            if (strpos($_sessionKey, $this->options['prefix']) === 0) {
                $num++;
                $tmp[] = $_sessionKey;
            }
        }
        $startMemory = memory_get_usage();
        $_tmp2       = unserialize(serialize($tmp));
        $total       = memory_get_usage() - $startMemory;

        return [
            'entries' => $num,
            'hits'    => null,
            'misses'  => null,
            'inserts' => null,
            'mem'     => $total
        ];
    }
}
