<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PHPSettingsHelper
 */
class PHPSettingsHelper
{
    use SingletonTrait;
    
    /**
     * @param string $shorthand
     * @return int
     */
    private function shortHandToInt($shorthand)
    {
        switch (substr($shorthand, -1)) {
            case 'M':
            case 'm':
                return (int)$shorthand * 1048576;
            case 'K':
            case 'k':
                return (int)$shorthand * 1024;
            case 'G':
            case 'g':
                return (int)$shorthand * 1073741824;
            default:
                return (int)$shorthand;
        }
    }
    
    /**
     * @return int
     */
    public function limit()
    {
        return $this->shortHandToInt(ini_get('memory_limit'));
    }
    
    /**
     * @return string
     */
    public function version()
    {
        return PHP_VERSION;
    }
    
    /**
     * @return int
     */
    public function executionTime()
    {
        return (int)ini_get('max_execution_time');
    }
    
    /**
     * @return int
     */
    public function postMaxSize()
    {
        return $this->shortHandToInt(ini_get('post_max_size'));
    }
    
    /**
     * @return int
     */
    public function uploadMaxFileSize()
    {
        return $this->shortHandToInt(ini_get('upload_max_filesize'));
    }
    
    /**
     * @return bool
     */
    public function safeMode()
    {
        return (bool)ini_get('safe_mode');
    }
    
    /**
     * @return string
     */
    public function tempDir()
    {
        return sys_get_temp_dir();
    }
    
    /**
     * @return bool
     */
    public function fopenWrapper()
    {
        return (bool)ini_get('allow_url_fopen');
    }
    
    /**
     * @param int $limit - in MB
     * @return bool
     */
    public function hasMinLimit($limit)
    {
        return ($this->limit() >= $limit || $this->limit() === 0);
    }
    
    /**
     * @param int $limit - in S
     * @return bool
     */
    public function hasMinExecutionTime($limit)
    {
        return ($this->executionTime() >= $limit || $this->executionTime() === 0);
    }
    
    /**
     * @param int $limit - in MB
     * @return bool
     */
    public function hasMinPostSize($limit)
    {
        return $this->postMaxSize() >= $limit;
    }
    
    /**
     * @param int $limit - in MB
     * @return bool
     */
    public function hasMinUploadSize($limit)
    {
        return $this->uploadMaxFileSize() >= $limit;
    }
    
    /**
     * @return bool
     */
    public function isTempWriteable()
    {
        return is_writable($this->tempDir());
    }
}
