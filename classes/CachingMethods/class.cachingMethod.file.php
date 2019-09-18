<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class cache_file
 * Implements caching via filesystem
 */
class cache_file implements ICachingMethod
{
    use JTLCacheTrait;

    /**
     * @var cache_file
     */
    public static $instance;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->journalID     = 'file_journal';
        $this->options       = $options;
        $this->isInitialized = true;
        self::$instance      = $this;
    }

    /**
     * @param string $cacheID
     * @return bool|string
     */
    private function getFileName($cacheID)
    {
        return is_string($cacheID)
            ? $this->options['cache_dir'] . $cacheID . $this->options['file_extension']
            : false;
    }

    /**
     * @param string   $cacheID
     * @param mixed    $content
     * @param int|null $expiration
     * @return bool
     */
    public function store($cacheID, $content, $expiration = null)
    {
        $dir = $this->options['cache_dir'];
        if (!is_dir($dir) && mkdir($dir) === false) {
            return false;
        }
        $fileName = $this->getFileName($cacheID);
        if ($fileName === false) {
            return false;
        }

        return (file_put_contents(
                $fileName,
                serialize(
                    [
                        'value'    => $content,
                        'lifetime' => ($expiration === null)
                            ? $this->options['lifetime']
                            : $expiration
                    ]
                )
            ) !== false);
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
        $fileName = $this->getFileName($cacheID);
        if ($fileName !== false && file_exists($fileName)) {
            $data = unserialize(file_get_contents($fileName));
            if ($data['lifetime'] === 0 || (time() - filemtime($fileName)) < $data['lifetime']) {
                return $data['value'];
            }
            $this->flush($cacheID);
        }

        return false;
    }

    /**
     * @param array $cacheIDs
     * @return array|bool
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
        $res = true;
        if (!is_dir($this->options['cache_dir'])) {
            $res = mkdir($this->options['cache_dir']);
        }

        return $res && is_writable($this->options['cache_dir']);
    }

    /**
     * @param string $str
     * @return bool
     */
    private function recursiveDelete($str)
    {
        if (is_file($str)) {
            return unlink($str);
        }
        if (is_dir($str)) {
            $scan = glob(rtrim($str, '/') . '/*');
            foreach ($scan as $index => $path) {
                $this->recursiveDelete($path);
            }

            return ($str === $this->options['cache_dir'])
                ? true
                : rmdir($str);
        }

        return false;
    }

    /**
     * @param string $cacheID
     * @return bool
     */
    public function flush($cacheID)
    {
        $fileName = $this->getFileName($cacheID);

        return ($fileName !== false && file_exists($fileName)) ? unlink($fileName) : false;
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        $this->journal = null;
        
        return $this->recursiveDelete($this->options['cache_dir']);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $dir   = opendir($this->options['cache_dir']);
        $total = 0;
        $num   = 0;
        while ($dir && ($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($this->options['cache_dir'] . $file)) {
                    //read sub dir
                    $subDir = opendir($this->options['cache_dir'] . $file);
                    while ($subDir && ($f = readdir($subDir)) !== false) {
                        if ($f !== '.' && $f !== '..') {
                            $filePath = $this->options['cache_dir'] . $file . '/' . $f;
                            $total += filesize($filePath);
                            ++$num;
                        }
                    }
                    closedir($subDir);
                } elseif (is_file($this->options['cache_dir'] . $file)) {
                    $total += filesize($this->options['cache_dir'] . $file);
                    ++$num;
                }
            }
        }
        if ($dir !== false) {
            closedir($dir);
        }

        return [
            'entries' => $num,
            'hits'    => null,
            'misses'  => null,
            'inserts' => null,
            'mem'     => $total
        ];
    }
}
