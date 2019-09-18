<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MultiRequest
 */
class MultiRequest
{
    /**
     * @var resource
     */
    public $handle;

    /**
     * 
     */
    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    /**
     * @param array    $urls
     * @param callable $callback
     */
    public function process($urls, $callback)
    {
        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true]);
            curl_multi_add_handle($this->handle, $ch);
        }

        do {
            if (curl_multi_select($this->handle) == -1) {
                usleep(100);
            }

            $mrc = curl_multi_exec($this->handle, $active);

            if ($state = curl_multi_info_read($this->handle)) {
                $info = curl_getinfo($state['handle']);
                $callback(curl_multi_getcontent($state['handle']), $info);
                curl_multi_remove_handle($this->handle, $state['handle']);
            }
        } while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);
    }

    public function __destruct()
    {
        curl_multi_close($this->handle);
    }
}
