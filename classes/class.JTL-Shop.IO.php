<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class IO
 */
class IO
{
    /**
     * @var static
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $functions = [];

    /**
     * ctor
     */
    private function __construct() { }

    /**
     * copy-ctor
     */
    private function __clone() { }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance === null ? (static::$instance = new static()) : static::$instance;
    }

    /**
     * Registers a PHP function or method.
     * This makes the function available for XMLHTTPRequest requests.
     *
     * @param string        $name - name udner which this function is callable
     * @param null|callable $function - target function name, method-tuple or closure
     * @param null|string   $include - file where this function is defined in
     * @return $this
     * @throws Exception
     */
    public function register($name, $function = null, $include = null)
    {
        if ($this->exists($name)) {
            throw new Exception("Function already registered");
        }

        if ($function === null) {
            $function = $name;
        }

        $this->functions[$name] = [$function, $include];

        return $this;
    }

    /**
     * @param string $reqString
     * @return mixed
     */
    public function handleRequest($reqString)
    {
        $request = json_decode($reqString, true);

        if (($errno = json_last_error()) != JSON_ERROR_NONE) {
            return new IOError("Error {$errno} while decoding data");
        }

        if (!isset($request['name'], $request['params'])) {
            return new IOError("Missing request property");
        }

        ob_start();
        set_time_limit(0);

        $result = $this->execute($request['name'], $request['params']);

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return $result;
    }

    /**
     * @param $data
     */
    public function respondAndExit($data)
    {
        // respond with an error?
        if (is_object($data) && get_class($data) === 'IOError') {
            header(makeHTTPHeader($data->code), true, $data->code);
        } elseif (is_object($data) && get_class($data) === 'IOFile') {
            $this->pushFile($data->filename, $data->mimetype);
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/json');

        die(json_safe_encode($data));
    }

    /**
     * Check if function exists
     *
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->functions[$name]);
    }

    /**
     * Executes a registered function
     *
     * @param string $name
     * @param mixed  $params
     * @return mixed
     * @throws Exception
     */
    public function execute($name, $params)
    {
        if (!$this->exists($name)) {
            return new IOError("Function not registered");
        }

        $function = $this->functions[$name][0];
        $include  = $this->functions[$name][1];

        if ($include !== null) {
            require_once $include;
        }

        if (is_array($function)) {
            $ref = new ReflectionMethod($function[0], $function[1]);
        } else {
            $ref = new ReflectionFunction($function);
        }

        if ($ref->getNumberOfRequiredParameters() > count($params)) {
            return new IOError("Wrong required parameter count");
        }

        return call_user_func_array($function, $params);
    }

    /**
     * @param string $filename
     * @param string $mimetype
     */
    protected function pushFile($filename, $mimetype)
    {
        $userAgent = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        $browserAgent = '';
        if (preg_match('/Opera\/([0-9].[0-9]{1,2})/', $userAgent, $m)) {
            $browserAgent = 'opera';
        } elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $userAgent, $m)) {
            $browserAgent = 'ie';
        } elseif (preg_match('/OmniWeb\/([0-9].[0-9]{1,2})/', $userAgent, $m)) {
            $browserAgent = 'omniweb';
        } elseif (preg_match('/Netscape([0-9]{1})/', $userAgent, $m)) {
            $browserAgent = 'netscape';
        } elseif (preg_match('/Mozilla\/([0-9].[0-9]{1,2})/', $userAgent, $m)) {
            $browserAgent = 'mozilla';
        } elseif (preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $userAgent, $m)) {
            $browserAgent = 'konqueror';
        }

        if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
            $mimetype = ($browserAgent === 'ie' || $browserAgent === 'opera')
                ? 'application/octetstream'
                : 'application/octet-stream';
        }

        @ob_end_clean();
        @ini_set('zlib.output_compression', 'Off');

        header('Pragma: public');
        header('Content-Transfer-Encoding: none');

        if ($browserAgent === 'ie') {
            header('Content-Type: ' . $mimetype);
            header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        } else {
            header('Content-Type: ' . $mimetype . '; name="' . basename($filename) . '"');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        }

        $size = @filesize($filename);
        if ($size) {
            header("Content-length: $size");
        }

        readfile($filename);
        exit;
    }
}
