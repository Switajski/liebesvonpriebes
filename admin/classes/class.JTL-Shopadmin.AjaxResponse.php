<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if (!function_exists('getallheaders')) {
    /**
     * Get all HTTP header key/values as an associative array for the current request.
     * polyfill for nginx
     *
     * @see https://github.com/ralouphie/getallheaders/blob/master/src/getallheaders.php     *
     * @return string[string] The HTTP header key/value pairs.
     */
    function getallheaders()
    {
        $headers     = [];
        $copy_server = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $key = substr($key, 5);
                if (!isset($copy_server[$key], $_SERVER[$key])) {
                    $key           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }
        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass               = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}

/**
 * Class Notification
 */
class AjaxResponse
{
    protected static $_tpl = ['error' => null, 'data' => null, 'type' => null];

    /**
     * @param string     $message
     * @param int        $code
     * @param array|null $errors
     * @return object
     */
    public function buildError($message, $code = 500, array $errors = null)
    {
        $tpl        = (object) static::$_tpl;
        $tpl->error = (object) [
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors
        ];

        return $tpl;
    }

    /**
     * @param object|array $data
     * @return object
     */
    public function buildResponse($data)
    {
        $tpl       = (object) static::$_tpl;
        $tpl->data = $data;
        if (is_array($tpl->data)) {
            $tpl->data = (object) $tpl->data;
        }

        return $tpl;
    }

    /**
     * @param object $data
     * @param string $type
     * @throws Exception
     */
    public function makeResponse($data, $type = null)
    {
        if (!is_object($data)) {
            throw new Exception('Unexpected data type');
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/json');

        if ($data->error !== null) {
            header(makeHTTPHeader($data->error->code), true, $data->error->code);
        }

        $data->type = $type;
        $json       = json_encode($data);

        if (json_last_error() === JSON_ERROR_UTF8) {
            $data = utf8_convert_recursive($data);
            $json = json_encode($data);
        }

        if ($json === null || json_last_error() !== JSON_ERROR_NONE) {
            $data = $this->buildError(json_last_error_msg());
            $json = json_encode($data);
        }

        echo $json;
        exit;
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0) {
            return true;
        }
        $accept = explode(',', getallheaders()['Accept']);

        return in_array('application/json', $accept, true);
    }

    /**
     * @param string $filename
     * @param string $mimetype
     */
    public function pushFile($filename, $mimetype)
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
