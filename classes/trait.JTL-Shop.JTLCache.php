<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class JTLCacheTrait
 */
trait JTLCacheTrait
{
    /**
     * @var array
     */
    public $options;

    /**
     * @var string
     */
    public $journalID;

    /**
     * @var array
     */
    public $journal;

    /**
     * @var bool
     */
    public $isInitialized = false;

    /**
     * @var bool
     */
    public $journalHasChanged = false;

    /**
     * @param array $options
     * @return JTLCacheTrait
     */
    public static function getInstance($options)
    {
        return (self::$instance !== null) ? self::$instance : new self($options);
    }

    /**
     * save the journal to persistent cache
     */
    public function __destruct()
    {
        //save journal on destruct
        if ($this->isInitialized === true && $this->journalHasChanged === true && count($this->journal) > 0) {
            $this->store($this->journalID, $this->journal, 0);
        }
    }

    /**
     * test data availability and integrity
     *
     * @return bool
     */
    public function test()
    {
        //if it's not available, it's not working
        if ($this->isInitialized === false || !$this->isAvailable()) {
            return false;
        }
        //store value to cache and load again
        $cID   = 'jtl_cache_test';
        $value = 'test-value';
        $set   = $this->store($cID, $value, 10);
        $load  = $this->load($cID);
        $flush = $this->flush($cID);

        //loaded value should equal stored value and it should be correctly flushed
        return $value === $load && $set && $flush;
    }

    /**
     * check if string was serialized before
     *
     * @param string $data
     * @return bool
     */
    public function is_serialized($data)
    {
        //if it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a' :
            case 'O' :
            case 's' :
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * check if data has to be serialized before storing
     * can be used by caching methods that don't support storing of native php objects/arrays
     *
     * @param mixed $data
     * @return bool
     */
    public function must_be_serialized($data)
    {
        return (is_object($data) || is_array($data));
    }

    /**
     * write meta data to journal - for use of cache tags
     *
     * @param string|array $tags
     * @param string       $cacheID - not prefixed
     * @return bool
     */
    public function writeJournal($tags, $cacheID)
    {
        if ($this->journal === null) {
            $this->getJournal();
        }
        $this->journalHasChanged = true;
        if (is_string($tags)) {
            if (isset($this->journal[$tags])) {
                if (!in_array($cacheID, $this->journal[$tags], true)) {
                    $this->journal[$tags][] = $cacheID;
                }
            } else {
                $journalEntry         = [];
                $journalEntry[]       = $cacheID;
                $this->journal[$tags] = $journalEntry;
            }
        } elseif (is_array($tags)) {
            foreach ($tags as $tag) {
                if (isset($this->journal[$tag])) {
                    if (!in_array($cacheID, $this->journal[$tag], true)) {
                        $this->journal[$tag][] = $cacheID;
                    }
                } else {
                    $journalEntry        = [];
                    $journalEntry[]      = $cacheID;
                    $this->journal[$tag] = $journalEntry;
                }
            }
        }

        return true;
    }

    /**
     * get cache IDs by cache tag(s)
     *
     * @param array|string $tags
     * @return array
     */
    public function getKeysByTag($tags)
    {
        //load journal from extra cache
        $this->getJournal();
        if (is_string($tags)) {
            return isset($this->journal[$tags])
                ? $this->journal[$tags]
                : [];
        } elseif (is_array($tags)) {
            $res = [];
            foreach ($tags as $tag) {
                if (isset($this->journal[$tag])) {
                    foreach ($this->journal[$tag] as $cacheID) {
                        $res[] = $cacheID;
                    }
                }
            }

            //remove duplicate keys from array and return it
            return array_unique($res);
        }

        return [];
    }

    /**
     * check if key exists - defaults to false
     * but methods can implement this to allow storing boolean values
     *
     * @param string $key
     * @return bool
     */
    public function keyExists($key)
    {
        return false;
    }

    /**
     * add cache tags to cached value
     *
     * @param string|array $tags
     * @param string       $cacheID
     * @return bool
     */
    public function setCacheTag($tags, $cacheID)
    {
        return $this->writeJournal($tags, $cacheID);
    }

    /**
     * removes cache IDs associated with given tags from cache
     *
     * @param array $tags
     * @return int
     */
    public function flushTags($tags)
    {
        $deleted = 0;
        foreach ($this->getKeysByTag($tags) as $_id) {
            $res = $this->flush($_id);
            $this->clearCacheTags($_id);
            if ($res === true) {
                ++$deleted;
            } elseif (is_int($res)) {
                $deleted += $res;
            }
        }

        return $deleted;
    }

    /**
     * clean up journal after deleting cache entries
     *
     * @param array|string $tags
     * @return bool
     */
    public function clearCacheTags($tags)
    {
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $this->clearCacheTags($tag);
            }
        }
        $this->getJournal();
        //avoid infinite loops
        if ($tags !== $this->journalID && $this->journal !== false) {
            //load meta data
            foreach ($this->journal as $tagName => $value) {
                //search for key in meta values
                if (($index = array_search($tags, $value, true)) !== false) {
                    unset($this->journal[$tagName][$index]);
                    if (count($this->journal[$tagName]) === 0) {
                        //remove empty tag nodes
                        unset($this->journal[$tagName]);
                    }
                }
            }
            //write back journal
            $this->journalHasChanged = true;

            return true;
        }

        return false;
    }

    /**
     * load journal
     *
     * @return array
     */
    public function getJournal()
    {
        if ($this->journal === null) {
            $this->journal = ($journal = $this->load($this->journalID)) !== false
                ? $journal
                : [];
        }

        return $this->journal;
    }

    /**
     * adds prefixes to array of cache IDs
     *
     * @param array $array
     * @return array
     */
    protected function prefixArray($array)
    {
        $newKeyArray = [];
        foreach ($array as $_key => $_val) {
            $newKey               = $this->options['prefix'] . $_key;
            $newKeyArray[$newKey] = $_val;
        }

        return $newKeyArray;
    }

    /**
     * removes prefixes from result array of cached keys/values
     *
     * @param array $array
     * @return array
     */
    protected function dePrefixArray($array)
    {
        $newKeyArray = [];
        foreach ($array as $_key => $_val) {
            $newKey               = str_replace($this->options['prefix'], '', $_key);
            $newKeyArray[$newKey] = $_val;
        }

        return $newKeyArray;
    }

    /**
     * more readable output for uptime stats
     *
     * @param int $seconds
     * @return string
     */
    protected function secondsToTime($seconds)
    {
        $dtF = new DateTime("@0");
        $dtT = new DateTime("@$seconds");

        return $dtF->diff($dtT)->format('%a Tage, %h Stunden, %i Minuten und %s Sekunden');
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }
}
