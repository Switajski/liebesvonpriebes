<?php

/**
 * Interface ICachingMethod - interface class for caching methods
 */
interface ICachingMethod
{
    /**
     * store value to cache
     *
     * @param string   $cacheID - key to identify the value
     * @param mixed    $content - the content to save
     * @param int|null $expiration - expiration time in seconds
     * @return bool - success
     */
    public function store($cacheID, $content, $expiration);

    /**
     * store multiple values to multiple keys at once to cache
     *
     * @param array    $idContent - array keys are cache IDs, array values are content to save
     * @param int|null $expiration - expiration time in seconds
     * @return bool
     */
    public function storeMulti($idContent, $expiration);

    /**
     * get value from cache
     *
     * @param string $cacheID
     * @return mixed|bool - the loaded data or false if not found
     */
    public function load($cacheID);

    /**
     * check if key exists
     *
     * @param string $key
     * @return bool
     */
    public function keyExists($key);

    /**
     * get multiple values at once from cache
     *
     * @param array $cacheIDs
     * @return mixed|bool
     */
    public function loadMulti($cacheIDs);

    /**
     * add cache tags to cached value
     *
     * @param string|array $tags
     * @param string       $cacheID
     * @return bool
     */
    public function setCacheTag($tags, $cacheID);

    /**
     * get cache IDs by cache tag(s)
     *
     * @param array|string $tags
     * @return array
     */
    public function getKeysByTag($tags);

    /**
     * removes cache IDs associated with given tags from cache
     *
     * @param array $tags
     * @return int
     */
    public function flushTags($tags);

    /**
     * load journal
     *
     * @return array
     */
    public function getJournal();

    /**
     * class singleton getter
     *
     * @param array $options
     * @return mixed
     */
    public static function getInstance($options);

    /**
     * check if php functions for using the selected caching method exist
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * check if method was successfully initialized
     *
     * @return bool
     */
    public function isInitialized();

    /**
     * clear cache by cid or gid
     *
     * @param string $cacheID
     * @return bool - success
     */
    public function flush($cacheID);

    /**
     * flushes all values from cache
     *
     * @return bool - success
     */
    public function flushAll();

    /**
     * test data integrity and if functions are working properly - default implementation @JTLCacheTrait
     *
     * @return bool - success
     */
    public function test();

    /**
     * get statistical data for caching method if supported
     *
     * @return array|null - null if not supported
     */
    public function getStats();
}
