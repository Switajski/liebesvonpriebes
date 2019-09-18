<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_redis
 * Implements caching via phpredis
 *
 * @see https://github.com/nicolasff/phpredis
 */
class cache_redis implements ICachingMethod
{
    use JTLCacheTrait;
    
    /**
     * @var cache_redis
     */
    public static $instance;

    /**
     * @var Redis
     */
    private $_redis;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $res             = false;
        $this->journalID = 'redis_journal';
        $this->options   = $options;
        if ($this->isAvailable()) {
            $res = $this->setRedis(
                $options['redis_host'],
                $options['redis_port'],
                $options['redis_pass'],
                $options['redis_db'],
                $options['redis_persistent']
            );
        }
        if ($res === false) {
            $this->_redis        = null;
            $this->isInitialized = false;
        } else {
            $this->isInitialized = true;
        }
    }

    /**
     * @param string|null $host
     * @param int|null    $port
     * @param string|null $pass
     * @param int|null    $database
     * @param bool        $persist
     * @return bool
     */
    private function setRedis($host = null, $port = null, $pass = null, $database = null, $persist = false)
    {
        $redis   = new Redis();
        $connect = $persist === false ? 'connect' : 'pconnect';
        if ($host !== null) {
            try {
                $res = ($port !== null && $host[0] !== '/')
                    ? $redis->$connect($host, (int)$port)
                    : $redis->$connect($host); //for connecting to socket
                if ($res !== false && $pass !== null && $pass !== '') {
                    $res = $redis->auth($pass);
                }
                if ($res !== false && $database !== null && $database !== '') {
                    $res = $redis->select((int)$database);
                }
            } catch (RedisException $e) {
                Shop::dbg($e->getMessage(), false, 'exception:');
                Jtllog::writeLog('RedisException: ' . $e->getMessage(), JTLLOG_LEVEL_ERROR);

                return false;
            }
            if ($res === false) {
                return false;
            }
            //set custom prefix
            $redis->setOption(Redis::OPT_PREFIX, $this->options['prefix']);
            //set php serializer for objects and arrays
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

            $this->_redis = $redis;

            return true;
        }

        return false;
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        try {
            $res = $this->_redis->set($cacheID, $content);
            $exp = $expiration === null ? $this->options['lifetime'] : $expiration;
            // the journal and negative expiration values should not cause an expiration
            if ($cacheID !== $this->journalID && $exp > -1) {
                $this->_redis->setTimeout($cacheID, $exp);
            }

            return $res;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @param array    $idContent
     * @param int|null $expiration
     * @return bool|mixed
     */
    public function storeMulti($idContent, $expiration = null)
    {
        try {
            $res = $this->_redis->mset($idContent);
            foreach (array_keys($idContent) as $_cacheID) {
                $this->_redis->setTimeout($_cacheID, (($expiration === null)
                    ? $this->options['lifetime']
                    : $expiration)
                );
            }

            return $res;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @param string $cacheID
     * @return bool|mixed|string
     */
    public function load($cacheID)
    {
        try {
            return $this->_redis->get($cacheID);
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @param array $cacheIDs
     * @return array|bool|mixed
     */
    public function loadMulti($cacheIDs)
    {
        try {
            $res    = $this->_redis->mget($cacheIDs);
            $i      = 0;
            $return = [];
            foreach ($res as $_idx => $_val) {
                $return[$cacheIDs[$i]] = $_val;
                ++$i;
            }

            return $return;
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return class_exists('Redis');
    }

    /**
     * @param string $cacheID
     * @return bool|int
     */
    public function flush($cacheID)
    {
        try {
            return $this->_redis->delete($cacheID);
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return false;
        }
    }

    /**
     * @param array|string $tags
     * @param string       $cacheID
     * @return bool
     */
    public function setCacheTag($tags = [], $cacheID)
    {
        $res   = false;
        $redis = $this->_redis->multi();
        if (is_string($tags)) {
            $tags = [$tags];
        }
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $redis->sAdd(self::_keyFromTagName($tag), $cacheID);
            }
            $redis->exec();
            $res = true;
        }

        return $res;
    }

    /**
     * custom prefix for tag IDs
     *
     * @param string $tagName
     * @return string
     */
    private static function _keyFromTagName($tagName)
    {
        return 'tag_' . $tagName;
    }

    /**
     * redis can delete multiple cacheIDs at once
     *
     * @param array|string $tags
     * @return int
     */
    public function flushTags($tags)
    {
        return $this->flush(array_unique($this->getKeysByTag($tags)));
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return $this->_redis->flushDB();
    }

    /**
     * @param array|string $tags
     * @return array
     */
    public function getKeysByTag($tags = [])
    {
        $matchTags = is_string($tags)
            ? [self::_keyFromTagName($tags)]
            : array_map('cache_redis::_keyFromTagName', $tags);
        $res       = count($tags) === 1
            ? $this->_redis->sMembers($matchTags[0])
            : $this->_redis->sUnion($matchTags);
        if (PHP_SAPI === 'srv' || PHP_SAPI === 'cli') { // for some reason, hhvm does not unserialize values
            foreach ($res as &$_cid) {
                // phpredis will throw an exception when unserializing unserialized data
                try {
                    $_cid = $this->_redis->_unserialize($_cid);
                } catch (RedisException $e) {
                    // we know we don't have to continue unserializing when there was an exception
                    break;
                }
            }
        }

        return is_array($res) ? $res : [];
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function keyExists($cacheID)
    {
        return $this->_redis->exists($cacheID);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $numEntries  = null;
        $slowLog     = [];
        $slowLogData = [];
        try {
            $stats = $this->_redis->info();
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();

            return [];
        }
        try {
            $slowLog = method_exists($this->_redis, 'slowlog')
                ? $this->_redis->slowlog('get', 25)
                : [];
        } catch (RedisException $e) {
            echo 'Redis exception: ' . $e->getMessage();
        }
        $db  = $this->_redis->getDBNum();
        $idx = 'db' . $db;
        if (isset($stats[$idx])) {
            $dbStats = explode(',', $stats[$idx]);
            foreach ($dbStats as $stat) {
                if (strpos($stat, 'keys=') !== false) {
                    $numEntries = str_replace('keys=', '', $stat);
                }
            }
        }
        foreach ($slowLog as $_slow) {
            $slowLogDataEntry = [];
            if (isset($_slow[1])) {
                $slowLogDataEntry['date'] = date('d.m.Y H:i:s', $_slow[1]);
            }
            if (isset($_slow[3][0])) {
                $slowLogDataEntry['cmd'] = $_slow[3][0];
            }
            if (isset($_slow[2]) && $_slow[2] > 0) {
                $slowLogDataEntry['exec_time'] = ($_slow[2] / 1000000);
            }
            $slowLogData[] = $slowLogDataEntry;
        }

        return [
            'entries'  => $numEntries,
            'uptime'   => isset($stats['uptime_in_seconds'])
                ? $stats['uptime_in_seconds']
                : null, //uptime in seconds
            'uptime_h' => isset($stats['uptime_in_seconds'])
                ? $this->secondsToTime($stats['uptime_in_seconds'])
                : null, //human readable
            'hits'     => $stats['keyspace_hits'], //cache hits
            'misses'   => $stats['keyspace_misses'], //cache misses
            'hps'      => isset($stats['uptime_in_seconds'])
                ? ($stats['keyspace_hits'] / $stats['uptime_in_seconds'])
                : null, //hits per second
            'mps'      => isset($stats['uptime_in_seconds'])
                ? ($stats['keyspace_misses'] / $stats['uptime_in_seconds'])
                : null, //misses per second
            'mem'      => $stats['used_memory'], //used memory in bytes
            'slow'     => $slowLogData //redis slow log
        ];
    }
}
