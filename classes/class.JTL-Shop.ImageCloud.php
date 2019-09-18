<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ImageCloud
 */
final class ImageCloud
{
    const USER_AGENT     = 'JTL-ImageCloud/1.0';
    const CLOUD_SALT     = '### JTL-ImageCloudStorage ###';
    const CLOUD_ENDPOINT = 'https://ics.jtl-software.de';

    private static $uid;

    private static $instance;

    /**
     * @return ImageCloud
     */
    public static function getInstance()
    {
        return (self::$instance === null) ? new self() : self::$instance;
    }

    /**
     * @param string $uid
     */
    public static function setId($uid)
    {
        self::$uid = $uid;
    }

    /**
     *
     */
    private function __construct()
    {
        //
    }

    private function __clone()
    {
        //
    }

    /**
     * Returns the image url
     *
     * @param  string $hash image hash
     * @return string|null
     */
    public function get($hash)
    {
        return $this->getCloudEndpoint($hash);
    }

    /**
     * Checks whether the hash value exists
     *
     * @param  string $hash image hash
     * @return $this
     */
    public function exists($hash)
    {
        $res = Guzzle\Http\StaticClient::head($this->getCloudEndpoint($hash), [
            'headers' => [
                'User-Agent'      => self::USER_AGENT,
                'X-Merchant-UUID' => self::$uid
            ]
        ]);

        return $this;
    }

    /**
     * Store the image with a generated hash name
     *
     * @param  string $filename Path to file
     * @return false|stdClass
     * @throws Exception
     */
    public function store($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $filename));
        }

        $hash = $this->getHash($filename);
        $res  = Guzzle\Http\StaticClient::post($this->getCloudEndpoint($hash), [
            'body'    => file_get_contents($filename),
            'headers' => [
                'User-Agent'      => self::USER_AGENT,
                'Content-Type'    => $this->getMimeType($filename),
                'X-Merchant-UUID' => self::$uid
            ]
        ]);

        $json = $res->getBody(true);
        $obj  = json_decode($json);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception('Deserialization error', json_last_error());
        }

        return $obj->success;
    }

    /**
     * Generate Hash from file
     *
     * @param  string $filename Path to file
     * @return string
     * @throws Exception
     */
    public function getHash($filename)
    {
        $size = @getimagesize($filename);

        if (!$size) {
            throw new Exception(sprintf('Could not determin the image size from file "%s"', $filename));
        }

        $pathinfo = pathinfo($filename);

        return sprintf('%s_%s_%s_%s.%s', $size[0], $size[1], md5_file($filename),
            bin2hex(mhash(MHASH_SHA512, file_get_contents($filename) . self::CLOUD_SALT)), $pathinfo['extension']);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    private function getMimeType($filename)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        return finfo_file($finfo, $filename);
    }

    /**
     * @param $hash
     * @return string
     */
    private function getCloudEndpoint($hash)
    {
        return sprintf('%s/%s', self::CLOUD_ENDPOINT, $hash);
    }
}
