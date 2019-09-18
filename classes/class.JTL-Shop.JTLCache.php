<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
define('CACHING_ROOT_DIR', __DIR__ . '/');
define('CACHING_METHODS_DIR', CACHING_ROOT_DIR . 'CachingMethods/');
define('CACHING_GROUP_ARTICLE', 'art');
define('CACHING_GROUP_CATEGORY', 'cat');
define('CACHING_GROUP_LANGUAGE', 'lang');
define('CACHING_GROUP_TEMPLATE', 'tpl');
define('CACHING_GROUP_OPTION', 'opt');
define('CACHING_GROUP_PLUGIN', 'plgn');
define('CACHING_GROUP_CORE', 'core');
define('CACHING_GROUP_OBJECT', 'obj');
define('CACHING_GROUP_BOX', 'bx');
define('CACHING_GROUP_NEWS', 'nws');
define('CACHING_GROUP_ATTRIBUTE', 'attr');
define('CACHING_GROUP_MANUFACTURER', 'mnf');
define('CACHING_GROUP_FILTER', 'fltr');

/**
 * Class JTLCache
 *
 * @method mixed get(string $cacheID, callable $callback = null, mixed $customData = null)
 * @method mixed fetch(string $cacheID, callable $callback = null, mixed $customData = null)
 * @method mixed set(string $cacheID, mixed $content, array $tags = null, int $expiration = null)
 * @method mixed store(string $cacheID, mixed $content, array $tags = null, int $expiration = null)
 * @method array getMulti(array $cacheIDs)
 * @method bool setMulti(array $keyValue, array $tags = null, int $expiration = null)
 * @method bool|int delete(string $cacheID, array $tags = null, array $hookInfo = null)
 * @method bool|int flush(string $cacheID, array $tags = null, array $hookInfo = null)
 * @method bool flushAll()
 * @method int flushTags(array $tags, array $hookInfo = null)
 * @method bool setCacheTag(array $tags, string $cacheID)
 * @method JTLCache setLifetime(int $lifetime)
 * @method bool isCacheGroupActive(string $groupID)
 * @method string getBaseID(bool $hash = false, bool $customerID = false, bool|int $customerGroup = true, bool|int $languageID = true, bool|int $currencyID = true, bool $sslStatus = true)
 * @method bool isActive()
 * @method bool isAvailable()
 * @method bool isPageCacheEnabled()
 * @method array getAllMethods()
 * @method JTLCache setCacheDir(string $dir)
 * @method ICachingMethod getActiveMethod()
 * @method array checkAvailability()
 * @method int getResultCode()
 * @method array benchmark(array|string $methods = 'all', mixed|string $testData = 'simple string', int $runCount = 1000, int $repeat = 1, bool $echo = true, bool $format = false)
 */
class JTLCache
{
    /**
     * default port for redis caching method
     */
    const DEFAULT_REDIS_PORT = 6379;

    /**
     * default host name for redis caching method
     */
    const DEFAULT_REDIS_HOST = 'localhost';

    /**
     * default memcache(d) port
     */
    const DEFAULT_MEMCACHE_PORT = 11211;

    /**
     * default memcache(d) host name
     */
    const DEFAULT_MEMCACHE_HOST = 'localhost';

    /**
     * default cache life time in seconds (86400 = 1 day)
     */
    const DEFAULT_LIFETIME = 86400;

    /**
     * result code for successful getting result from cache
     */
    const RES_SUCCESS = 1;

    /**
     * result code for cache miss
     */
    const RES_FAIL = 2;

    /**
     * result code when getting multiple values at once
     */
    const RES_UNDEF = 3;

    /**
     * currently active caching method
     *
     * @var ICachingMethod
     */
    private $_method;

    /**
     * caching options
     *
     * @var array
     */
    private $options;

    /**
     * plugin instance
     *
     * @var JTLCache
     */
    private static $instance;

    /**
     * get/set result code
     *
     * @var int
     */
    private $resultCode;

    /**
     * @var array
     */
    private $cachingGroups = [];

    /**
     * init cache and set default method
     *
     * @param array $options
     * @param bool  $ignoreInstance - used for page cache to not overwrite the instance and delete debug output
     */
    public function __construct($options = [], $ignoreInstance = false)
    {
        if ($ignoreInstance === false) {
            self::$instance = $this;
        }
        $this->setCachingGroups()
             ->setOptions($options);
    }

    /**
     * singleton
     *
     * @param array $options
     * @return JTLCache
     */
    public static function getInstance($options = [])
    {
        return (self::$instance !== null) ? self::$instance : new self($options);
    }

    /**
     * object wrapper
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $mapping = self::map($method);

        return ($mapping !== null) ? call_user_func_array([$this, $mapping], $arguments) : null;
    }

    /**
     * static wrapper
     * this allows to call Cache::set() etc.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $mapping = self::map($method);

        return ($mapping !== null) ? call_user_func_array([self::$instance, $mapping], $arguments) : null;
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map($method)
    {
        $mapping = [
            'get'                => '_get',
            'fetch'              => '_get',
            'set'                => '_set',
            'store'              => '_set',
            'getMulti'           => '_getMulti',
            'setMulti'           => '_setMulti',
            'delete'             => '_flush',
            'flush'              => '_flush',
            'flushAll'           => '_flushAll',
            'flushTags'          => '_flushTags',
            'setCacheTag'        => '_setCacheTag',
            'setLifetime'        => '_setCacheLifetime',
            'isCacheGroupActive' => '_isCacheGroupActive',
            'getBaseID'          => '_getBaseID',
            'isActive'           => '_isActive',
            'isAvailable'        => '_isAvailable',
            'isPageCacheEnabled' => '_isPageCacheEnabled',
            'getAllMethods'      => '_getAllMethods',
            'setCacheDir'        => '_setCacheDir',
            'getActiveMethod'    => '_getActiveMethod',
            'checkAvailability'  => '_checkAvailability',
            'getResultCode'      => '_getResultCode',
            'benchmark'          => '_benchmark',
        ];

        return isset($mapping[$method]) ? $mapping[$method] : null;
    }

    /**
     * build list of all caching groups
     * enriched with description placeholders that can be loaded as smarty variables
     *
     * @return $this
     */
    private function setCachingGroups()
    {
        $this->cachingGroups = [
            [
                'name'        => 'CACHING_GROUP_ARTICLE',
                'nicename'    => 'cg_article_nicename',
                'value'       => CACHING_GROUP_ARTICLE,
                'description' => 'cg_article_description'],
            [
                'name'        => 'CACHING_GROUP_CATEGORY',
                'nicename'    => 'cg_category_nicename',
                'value'       => CACHING_GROUP_CATEGORY,
                'description' => 'cg_category_description'],
            [
                'name'        => 'CACHING_GROUP_LANGUAGE',
                'nicename'    => 'cg_language_nicename',
                'value'       => CACHING_GROUP_LANGUAGE,
                'description' => 'cg_language_description'],
            [
                'name'        => 'CACHING_GROUP_TEMPLATE',
                'nicename'    => 'cg_template_nicename',
                'value'       => CACHING_GROUP_TEMPLATE,
                'description' => 'cg_template_description'],
            [
                'name'        => 'CACHING_GROUP_OPTION',
                'nicename'    => 'cg_option_nicename',
                'value'       => CACHING_GROUP_OPTION,
                'description' => 'cg_option_description'],
            [
                'name'        => 'CACHING_GROUP_PLUGIN',
                'nicename'    => 'cg_plugin_nicename',
                'value'       => CACHING_GROUP_PLUGIN,
                'description' => 'cg_plugin_description'],
            [
                'name'        => 'CACHING_GROUP_CORE',
                'nicename'    => 'cg_core_nicename',
                'value'       => CACHING_GROUP_CORE,
                'description' => 'cg_core_description'],
            [
                'name'        => 'CACHING_GROUP_OBJECT',
                'nicename'    => 'cg_object_nicename',
                'value'       => CACHING_GROUP_OBJECT,
                'description' => 'cg_object_description'],
            [
                'name'        => 'CACHING_GROUP_BOX',
                'nicename'    => 'cg_box_nicename',
                'value'       => CACHING_GROUP_BOX,
                'description' => 'cg_box_description'],
            [
                'name'        => 'CACHING_GROUP_NEWS',
                'nicename'    => 'cg_news_nicename',
                'value'       => CACHING_GROUP_NEWS,
                'description' => 'cg_news_description'],
            [
                'name'        => 'CACHING_GROUP_ATTRIBUTE',
                'nicename'    => 'cg_attribute_nicename',
                'value'       => CACHING_GROUP_ATTRIBUTE,
                'description' => 'cg_attribute_description'],
            [
                'name'        => 'CACHING_GROUP_MANUFACTURER',
                'nicename'    => 'cg_manufacturer_nicename',
                'value'       => CACHING_GROUP_MANUFACTURER,
                'description' => 'cg_manufacturer_description'],
            [
                'name'        => 'CACHING_GROUP_FILTER',
                'nicename'    => 'cg_filter_nicename',
                'value'       => CACHING_GROUP_FILTER,
                'description' => 'cg_filter_description'],
        ];

        return $this;
    }

    /**
     * get list of all caching groups
     *
     * @return array
     */
    public function getCachingGroups()
    {
        return $this->cachingGroups;
    }

    /**
     * set options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options = [])
    {
        $defaults = [
            'activated'        => false, //main switch
            'method'           => 'null', //caching method to use - init with null to avoid errors after installation
            'redis_port'       => self::DEFAULT_REDIS_PORT, //port of redis server
            'redis_pass'       => null, //password for redis server
            'redis_host'       => self::DEFAULT_REDIS_HOST, //host of redis server
            'redis_db'         => null, //optional redis database id, null or 0 for default
            'redis_persistent' => false, //optional redis database id, null or 0 for default
            'memcache_port'    => self::DEFAULT_MEMCACHE_PORT, //port for memcache(d) server
            'memcache_host'    => self::DEFAULT_MEMCACHE_HOST, //host of memcache(d) server
            'prefix'           => 'jc_' . (defined('DB_NAME') ? DB_NAME . '_' : ''), //try to make a quite unique prefix if multiple shops are used
            'lifetime'         => self::DEFAULT_LIFETIME, //cache lifetime in seconds
            'collect_stats'    => false, //used to tell caching methods to collect statistical data or not (if not provided transparently)
            'debug'            => false, //enable or disable collecting of debug data
            'debug_method'     => 'echo', //'ssd'/'jtld' for SmarterSmartyDebug/JTLDebug, 'echo' for direct echo
            'cache_dir'        => OBJECT_CACHE_DIR, //file cache directory
            'file_extension'   => '.fc', //file extension for file cache
            'page_cache'       => false, //smarty page cache switch
            'types_disabled'   => [] //disabled cache groups
        ];
        //merge defaults with assigned options and set them
        $this->options = array_merge($defaults, $options);
        //always add trailing slash
        if (substr($this->options['cache_dir'], strlen($this->options['cache_dir']) - 1) !== '/') {
            $this->options['cache_dir'] .= '/';
        }
        if ($this->options['method'] !== 'redis' && (int)$this->options['lifetime'] < 0) {
            $this->options['lifetime'] = 0;
        }
        //accept only valid integer lifetime values
        $this->options['lifetime'] = ($this->options['lifetime'] === '' || (int)$this->options['lifetime'] === 0)
            ? self::DEFAULT_LIFETIME
            : (int)$this->options['lifetime'];
        if ($this->options['types_disabled'] === null) {
            $this->options['types_disabled'] = [];
        }
        if ($this->options['debug'] === true && $this->options['debug_method'] === 'echo') {
            echo '<br />Initialized Cache with method ' . $this->options['method'];
        }

        return $this;
    }

    /**
     * set caching method by name
     *
     * @param string $methodName
     * @return bool
     */
    public function setCache($methodName)
    {
        $cache = null;
        if (file_exists(CACHING_METHODS_DIR . 'class.cachingMethod.' . $methodName . '.php')) {
            require_once CACHING_METHODS_DIR . 'class.cachingMethod.' . $methodName . '.php';
            /** @var ICachingMethod $className */
            $className = 'cache_' . $methodName;
            $cache     = $className::getInstance($this->options);
        }
        //check method's health
        if (!empty($cache) && $cache instanceof ICachingMethod && $cache->isInitialized() && $cache->isAvailable()) {
            $this->setMethod($cache);

            return true;
        }
        //fallback to null method
        if (file_exists(CACHING_METHODS_DIR . 'class.cachingMethod.null.php')) {
            require_once CACHING_METHODS_DIR . 'class.cachingMethod.null.php';
            $this->setMethod(cache_null::getInstance($this->options));
        }

        return false;
    }

    /**
     * set caching method
     *
     * @param ICachingMethod|JTLCacheTrait $method
     * @return $this
     */
    private function setMethod($method)
    {
        $this->_method = $method;

        return $this;
    }

    /**
     * load jtl cache config from db
     *
     * @return array
     */
    public function getJtlCacheConfig()
    {
        //the DB class is needed for this
        if (!class_exists('Shop')) {
            return [];
        }
        $cacheConfig = Shop::DB()->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_CACHING);
        $cacheInit   = [];
        if (!empty($cacheConfig)) {
            foreach ($cacheConfig as $_conf) {
                if ($_conf->cWert === 'Y' || $_conf->cWert === 'y') {
                    $value = true;
                } elseif ($_conf->cWert === 'N' || $_conf->cWert === 'n') {
                    $value = false;
                } elseif ($_conf->cWert === '') {
                    $value = null;
                } elseif (is_numeric($_conf->cWert)) {
                    $value = (int)$_conf->cWert;
                } else {
                    $value = $_conf->cWert;
                }
                //naming convention is 'caching_'<var-name> for options saved in database
                $cacheInit[str_replace('caching_', '', $_conf->cName)] = $value;
            }
        }
        //disabled cache types are saved as serialized string in db
        if (isset($cacheInit['types_disabled']) &&
            is_string($cacheInit['types_disabled']) &&
            $cacheInit['types_disabled'] !== ''
        ) {
            $cacheInit['types_disabled'] = unserialize($cacheInit['types_disabled']);
        }

        return $cacheInit;
    }

    /**
     * load and set cache config from db values
     *
     * @return $this
     */
    public function setJtlCacheConfig()
    {
        $this->setOptions($this->getJtlCacheConfig())->init();

        return $this;
    }

    /**
     * initialize cache
     *
     * @return $this
     */
    public function init()
    {
        if ($this->options['activated'] === true) {
            //set the configure caching method
            $this->setCache($this->options['method']);
            //preload shop settings and lang vars to avoid single cache/mysql requests
            $settings = Shopsetting::getInstance();
            $settings->preLoad();
            Shop::Lang()->preLoad();
        } else {
            //set fallback null method
            $this->setCache('null');
        }

        return $this;
    }

    /**
     * get current options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * set redis authentication parameters
     *
     * @param string      $host
     * @param int         $port
     * @param string|null $pass
     * @param int|null    $database
     * @return $this
     */
    public function setRedisCredentials($host, $port, $pass = null, $database = null)
    {
        $this->options['redis_host'] = $host;
        $this->options['redis_port'] = $port;
        $this->options['redis_pass'] = $pass;
        $this->options['redis_db']   = $database;

        return $this;
    }

    /**
     * set memcache authentication parameters
     *
     * @param string $host
     * @param int    $port
     * @return $this
     */
    public function setMemcacheCredentials($host, $port)
    {
        $this->options['memcache_host'] = $host;
        $this->options['memcache_port'] = $port;

        return $this;
    }

    /**
     * set memcached authentication parameters
     *
     * @param string $host
     * @param int    $port
     * @return $this
     */
    public function setMemcachedCredentials($host, $port)
    {
        return $this->setMemcacheCredentials($host, $port);
    }

    /**
     * get value from cache
     *
     * @param string   $cacheID
     * @param callable $callback
     * @param mixed    $customData
     * @return mixed
     */
    public function _get($cacheID, $callback = null, $customData = null)
    {
        $res              = ($this->options['activated'] === true)
            ? $this->_method->load($cacheID)
            : false;
        $this->resultCode = ($res !== false || $this->_method->keyExists($cacheID))
            ? self::RES_SUCCESS
            : self::RES_FAIL;
        if ($this->options['debug'] === true) {
            if ($this->options['debug_method'] === 'echo') {
                echo '<br />Key ' . $cacheID . (($this->resultCode !== self::RES_SUCCESS)
                        ? ' could not be'
                        : 'successfully') . ' loaded.';
            } else {
                Profiler::setCacheProfile('get', (($res !== false) ? 'success' : 'failure'), $cacheID);
            }
        }
        if ($callback !== null && $this->resultCode !== self::RES_SUCCESS && is_callable($callback)) {
            $content    = null;
            $tags       = null;
            $expiration = null;
            $res        = call_user_func_array(
                $callback,
                [$this, $cacheID, &$content, &$tags, &$expiration, $customData]
            );
            if ($res === true) {
                $this->_set($cacheID, $content, $tags, $expiration);

                return $this->_get($cacheID);
            }
        }

        return $res;
    }

    /**
     * store value to cache
     *
     * @param string     $cacheID
     * @param mixed      $content
     * @param array|null $tags
     * @param int|null   $expiration
     * @return mixed
     */
    public function _set($cacheID, $content, $tags = null, $expiration = null)
    {
        $res = false;
        if ($this->options['activated'] === true && $this->isCacheGroupActive($tags) === true) {
            $res = $this->_method->store($cacheID, $content, $expiration);
            if ($res === true && $tags !== null) {
                $this->_setCacheTag($tags, $cacheID);
            }
        }
        if ($this->options['debug'] === true) {
            if ($this->options['debug_method'] === 'echo') {
                echo '<br />Key ' . $cacheID . (($res !== false) ? 'successfully' : 'could not be') . ' set.';
            } else {
                Profiler::setCacheProfile('set', (($res !== false) ? 'success' : 'failure'), $cacheID);
            }
        }
        $this->resultCode = ($res === false) ? self::RES_FAIL : self::RES_SUCCESS;

        return $res;
    }

    /**
     * store multiple values to multiple cache IDs at once
     *
     * @param array      $keyValue - key=cacheID, value=content
     * @param array|null $tags
     * @param int|null   $expiration
     * @return bool
     */
    public function _setMulti($keyValue, $tags = null, $expiration = null)
    {
        if ($this->options['activated'] === true && $this->isCacheGroupActive($tags) === true) {
            $res = $this->_method->storeMulti($keyValue, $expiration);
            if ($res === true && $tags !== null) {
                foreach (array_keys($keyValue) as $_cacheID) {
                    $this->_setCacheTag($tags, $_cacheID);
                }
            }
            $this->resultCode = self::RES_UNDEF; //for now, let's not check every part of the result

            return $res;
        }
        $this->resultCode = self::RES_FAIL;

        return false;
    }

    /**
     * get multiple values from cache
     *
     * @param array $cacheIDs
     * @return array
     */
    public function _getMulti($cacheIDs)
    {
        $this->resultCode = self::RES_UNDEF; //for now, let's not check every part of the result

        return $this->_method->loadMulti($cacheIDs);
    }

    /**
     * check if cache for selected group id is active
     * this allows the disabling of certain cache types
     *
     * @param string|array $groupID
     * @return bool
     */
    public function _isCacheGroupActive($groupID)
    {
        if ($this->options['activated'] === false) {
            //if the cache is disabled, every tag is inactive
            return false;
        }
        if (is_string($groupID) &&
            is_array($this->options['types_disabled']) &&
            in_array($groupID, $this->options['types_disabled'], true)
        ) {
            return false;
        }
        if (is_array($groupID)) {
            foreach ($groupID as $group) {
                if (in_array($group, $this->options['types_disabled'], true)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string|array $tags
     * @return array
     */
    public function getKeysByTag($tags)
    {
        return $this->_method->getKeysByTag($tags);
    }

    /**
     * add cache tag to cache value by ID
     *
     * @param array  $tags
     * @param string $cacheID
     * @return bool
     */
    public function _setCacheTag($tags, $cacheID)
    {
        return ($this->options['activated'] === true)
            ? $this->_method->setCacheTag($tags, $cacheID)
            : false;
    }

    /**
     * set custom cache lifetime
     *
     * @param int $lifetime
     * @return $this
     */
    public function _setCacheLifetime($lifetime)
    {
        $this->options['lifetime'] = ((int)$lifetime > 0)
            ? (int)$lifetime
            : self::DEFAULT_LIFETIME;

        return $this;
    }

    /**
     * set custom file cache directory
     *
     * @param string $dir
     * @return $this
     */
    public function _setCacheDir($dir)
    {
        $this->options['cache_dir'] = $dir;

        return $this;
    }

    /**
     * get the currently activated cache method
     *
     * @return ICachingMethod
     */
    public function _getActiveMethod()
    {
        return $this->_method;
    }

    /**
     * remove single ID from cache or group or remove whole group
     *
     * @param string|int|null $cacheID
     * @param string|array    $tags
     * @param array|null      $hookInfo
     * @return bool|int
     */
    public function _flush($cacheID = null, $tags = null, $hookInfo = null)
    {
        $res = false;
        if ($cacheID !== null && $tags === null) {
            $res = ($this->options['activated'] === true)
                ? $this->_method->flush($cacheID)
                : false;
        } elseif ($tags !== null) {
            $res = $this->_flushTags($tags, $hookInfo);
        }
        if ($this->options['debug'] === true) {
            if ($this->options['debug_method'] === 'echo') {
                echo '<br />Key ' . $cacheID . (($res !== false) ? ' ' : ' not') . ' flushed';
            } else {
                Profiler::setCacheProfile('flush', (($res !== false) ? 'success' : 'failure'), $cacheID);
            }
        }
        if ($hookInfo !== null && defined('HOOK_CACHE_FLUSH_AFTER') && function_exists('executeHook')) {
            executeHook(HOOK_CACHE_FLUSH_AFTER, $hookInfo);
        }
        $this->resultCode = is_int($res) ? self::RES_FAIL : self::RES_SUCCESS;

        return $res;
    }

    /**
     * delete keys tagged with $tags
     *
     * @param array      $tags
     * @param array|null $hookInfo
     * @return int - number of deleted keys
     */
    public function _flushTags($tags, $hookInfo = null)
    {
        $deleted = $this->_method->flushTags($tags);
        if ($hookInfo !== null && defined('HOOK_CACHE_FLUSH_AFTER') && function_exists('executeHook')) {
            executeHook(HOOK_CACHE_FLUSH_AFTER, $hookInfo);
        }

        return $deleted;
    }

    /**
     * clear all values from cache
     *
     * @return bool
     */
    public function _flushAll()
    {
        $this->_method->flush($this->_method->journalID);

        return $this->_method->flushAll();
    }

    /**
     * get result code from last operation
     *
     * @return int
     */
    public function _getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * get caching method's journal data
     *
     * @return array
     */
    public function getJournal()
    {
        return $this->_method->getJournal();
    }

    /**
     * get statistical data
     *
     * @return array
     */
    public function getStats()
    {
        return $this->_method->getStats();
    }

    /**
     * test method's integrity
     *
     * @return bool
     */
    public function testMethod()
    {
        return $this->_method->test();
    }

    /**
     * check if caching method is available
     *
     * @return bool
     */
    public function _isAvailable()
    {
        return $this->_method->isAvailable();
    }

    /**
     * check if caching is enabled
     *
     * @return bool
     */
    public function _isActive()
    {
        return (bool)$this->options['activated'];
    }

    /**
     * check if full page cache is enabled
     *
     * @return bool
     */
    public function _isPageCacheEnabled()
    {
        return false;
    }

    /**
     * get list of all installed caching methods
     *
     * @return array
     */
    public function _getAllMethods()
    {
        $methodNames = [];
        $files       = scandir(CACHING_METHODS_DIR);
        if (is_array($files)) {
            foreach ($files as $_file) {
                if (strpos($_file, 'class.cachingMethod') !== false) {
                    $methodNames[] = str_replace(['class.cachingMethod.', '.php'], '', $_file);
                }
            }
        }

        return $methodNames;
    }

    /**
     * check which caching methods are available and usable
     *
     * @return array
     */
    public function _checkAvailability()
    {
        $available = [];
        foreach ($this->_getAllMethods() as $methodName) {
            $class = 'cache_' . $methodName;
            include_once CACHING_METHODS_DIR . 'class.cachingMethod.' . $methodName . '.php';
            if (class_exists($class)) {
                /** @var ICachingMethod $instance */
                $instance               = new $class($this->options);
                $available[$methodName] = [
                    'available'  => $instance->isAvailable(),
                    'functional' => $instance->test()
                ];
            } else {
                $available[$methodName] = [
                    'available'  => false,
                    'functional' => false
                ];
            }
        }

        return $available;
    }

    /**
     * generate basic cache id with popular variables
     *
     * @param bool     $hash
     * @param bool     $customerID
     * @param bool|int $customerGroup
     * @param bool|int $languageID
     * @param bool|int $currencyID
     * @param bool     $sslStatus
     * @return string
     */
    public function _getBaseID($hash = false, $customerID = false, $customerGroup = true, $languageID = true, $currencyID = true, $sslStatus = true)
    {
        $baseID = 'b';
        //add customer ID
        if ($customerID === true) {
            $baseID .= '_cid';
            $baseID .= isset($_SESSION['Kunde']->kKunde)
                ? $_SESSION['Kunde']->kKunde
                : '-1';
        }
        //add customer group
        if ($customerGroup === true) {
            $baseID .= '_cgid';
            $baseID .= isset($_SESSION['Kundengruppe']->kKundengruppe)
                ? $_SESSION['Kundengruppe']->kKundengruppe
                : Kundengruppe::getDefaultGroupID();
        } elseif (is_numeric($customerGroup)) {
            $baseID .= '_cgid' . (int)$customerGroup;
        }
        //add language ID
        if ($languageID === true) {
            $baseID .= '_lid';
            $lang = Shop::getLanguage();
            if ($lang > 0) {
                $baseID .= $lang;
            } elseif (isset($_SESSION['kSprache'])) {
                $baseID .= $_SESSION['kSprache'];
            } else {
                $baseID .= '0';
            }
        } elseif (is_numeric($languageID)) {
            $baseID .= '_lid' . (int)$languageID;
        }
        //add currency ID
        if ($currencyID === true) {
            $baseID .= '_curid';
            $baseID .= isset($_SESSION['Waehrung']->kWaehrung)
                ? $_SESSION['Waehrung']->kWaehrung
                : '0';
        } elseif (is_numeric($currencyID)) {
            $baseID .= '_curid' . (int)$currencyID;
        }
        //add current SSL status
        if ($sslStatus === true && function_exists('pruefeSSL')) {
            $baseID .= '_ssl' . pruefeSSL();
        }

        if ($this->options['debug'] === true && $this->options['debug_method'] === 'echo') {
            echo '<br>generated $baseID ' . $baseID;
        }

        return ($hash === true) ? md5($baseID) : $baseID;
    }

    /**
     * simple benchmark for different caching methods
     *
     * @param array|string $methods - the method to benchmark
     * @param mixed        $testData - the value to set
     * @param int          $runCount - the number of values to set/get
     * @param int          $repeat - the number of benchmark repetitions
     * @param bool         $echo - direct string output
     * @param bool         $format - german number format
     * @return array
     */
    public function _benchmark($methods = 'all', $testData = 'simple string', $runCount = 1000, $repeat = 1, $echo = true, $format = false)
    {
        $this->options['activated'] = true;
        $this->options['lifetime']  = self::DEFAULT_LIFETIME;
        //sanitize input
        if (!is_int($runCount) || $runCount < 1) {
            $runCount = 1;
        }
        if (!is_int($repeat) || $repeat < 1) {
            $repeat = 1;
        }
        $results = [];
        if ($methods === 'all') {
            $methods = $this->_getAllMethods();
        }
        if (is_array($methods)) {
            foreach ($methods as $method) {
                if ($method !== 'null') {
                    $results[] = $this->_benchmark($method, $testData, $runCount, $repeat, $echo, $format);
                }
            }
        } else {
            $timesSet     = 0;
            $timesGet     = 0;
            $cacheSetRes  = $this->setCache($methods);
            $validResults = true;
            if ($echo === true) {
                echo '### Testing ' . $methods . ' cache ###';
            }
            $result = [
                'method'  => $methods,
                'status'  => 'ok',
                'timings' => ['get' => 0.0, 'set' => 0.0]
            ];
            if ($cacheSetRes !== false) {
                for ($i = 0; $i < $repeat; ++$i) {
                    //set testing
                    $start = microtime(true);
                    for ($j = 0; $j < $runCount; ++$j) {
                        $cacheID = 'c_' . $j;
                        $this->set($cacheID, $testData);
                    }
                    $end          = microtime(true);
                    $runTimingSet = ($end - $start);
                    $timesSet += $runTimingSet;
                    //get testing
                    $start = microtime(true);
                    for ($j = 0; $j < $runCount; ++$j) {
                        $cacheID = 'c_' . $j;
                        $res     = $this->get($cacheID);
                        if ($res !== $testData) {
                            $validResults = false;
                        }
                    }
                    $end          = microtime(true);
                    $runTimingGet = ($end - $start);
                    $timesGet += $runTimingGet;
                }
            } else {
                if ($echo === true) {
                    echo '<br />Caching method not supported by server<br /><br />';
                }
                $result['status'] = 'failed';

                return $result;
            }
            if ($timesSet > 0.0 && $timesGet > 0.0 && $validResults !== false) {
                //calculate averages
                $rpsGet   = ($runCount * $repeat / $timesGet);
                $rpsSet   = ($runCount * $repeat / $timesSet);
                $timesSet /= $repeat;
                $timesGet /= $repeat;
                if ($format === true) {
                    $timesSet = number_format($timesSet, 4, ',', '.');
                    $timesGet = number_format($timesGet, 4, ',', '.');
                    $rpsSet   = number_format($rpsSet, 2, ',', '.');
                    $rpsGet   = number_format($rpsGet, 2, ',', '.');
                }
                //output averages
                if ($echo === true) {
                    echo '<br />Avg. time for setting: ' . $timesSet . 's (' . $rpsSet . ' requests per second)';
                    echo '<br />Avg. time for getting: ' . $timesGet . 's (' . $rpsGet . ' requests per second)';
                }
                $result['timings'] = ['get' => $timesGet, 'set' => $timesSet];
                $result['rps']     = ['get' => $rpsGet, 'set' => $rpsSet];
            }
            if ($validResults === false) {
                if ($echo === true) {
                    echo '<br />Got invalid results when loading cached values!';
                }
                $result['status'] = 'invalid';
            }
            if ($echo === true) {
                echo '<br /><br />';
            }

            return $result;
        }

        return $results;
    }
}
