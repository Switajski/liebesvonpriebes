<?php

/**
 * Class UrlHelper
 */
class UrlHelper
{
    /**
     * @var string
     */
    private $url = '';

    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $port = '';

    /**
     * @var string
     */
    private $user = '';

    /**
     * @var string
     */
    private $pass = '';

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @var array
     */
    private $default_scheme_ports = ['http' => 80, 'https' => 443];

    /**
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        if ($url) {
            $this->setUrl($url);
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function setUrl($url)
    {
        $this->url = $url;
        // parse URL into respective parts
        $url_components = parse_url($this->url);

        if (!$url_components) {
            return false;
        }
        foreach ($url_components as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        if ($this->path) {
            // case normalization
            $this->path = preg_replace_callback(
                '/(%([0-9abcdef][0-9abcdef]))/x',
                function ($x) {return '%' . strtoupper($x[2]);},
                $this->path
            );
            // percent-encoding normalization
            $this->path = $this->urlDecodeUnreservedChars($this->path);
            // path segment normalization
            $this->path = $this->removeDotSegments($this->path);
        }

        $scheme = '';
        if ($this->scheme) {
            $this->scheme = strtolower($this->scheme);
            $scheme       = $this->scheme . '://';
        }

        if ($this->host) {
            $this->host = strtolower($this->host);
        }

        $this->schemeBasedNormalization();

        // reconstruct uri
        $query = '';
        if ($this->query) {
            $query = '?' . $this->query;
        }

        $fragment = '';
        if ($this->fragment) {
            $fragment = '#' . $this->fragment;
        }

        $port = '';
        if ($this->port) {
            $port = ':' . $this->port;
        }

        $authorization = '';
        if ($this->user) {
            $authorization = $this->user . ':' . $this->pass . '@';
        }

        return $scheme . $authorization . $this->host . $port . $this->path . $query . $fragment;
    }

    /**
     * Decode unreserved characters
     *
     * @see http://www.apps.ietf.org/rfc/rfc3986.html#sec-2.3
     * @param string $string
     * @return mixed
     */
    public function urlDecodeUnreservedChars($string)
    {
        $unreserved = [];
        for ($octet = 65; $octet <= 90; $octet++) {
            $unreserved[] = dechex($octet);
        }
        for ($octet = 97; $octet <= 122; $octet++) {
            $unreserved[] = dechex($octet);
        }
        for ($octet = 48; $octet <= 57; $octet++) {
            $unreserved[] = dechex($octet);
        }

        $unreserved[] = dechex(ord('-'));
        $unreserved[] = dechex(ord('.'));
        $unreserved[] = dechex(ord('_'));
        $unreserved[] = dechex(ord('~'));

        return preg_replace_callback(array_map(
            function ($str) {
                return '/%' . strtoupper($str) . '/x';
            },
            $unreserved
        ), function ($matches) {
            return chr(hexdec($matches[0]));
        }, $string);
    }

    /**
     * Path segment normalization
     *
     * @see http://www.apps.ietf.org/rfc/rfc3986.html#sec-5.2.4
     * @param string $path
     * @return mixed|string
     */
    public function removeDotSegments($path)
    {
        $new_path = '';
        while (!empty($path)) {
            // A
            $pattern_a   = '!^(\.\./|\./)!x';
            $pattern_b_1 = '!^(/\./)!x';
            $pattern_b_2 = '!^(/\.)$!x';
            $pattern_c   = '!^(/\.\./|/\.\.)!x';
            $pattern_d   = '!^(\.|\.\.)$!x';
            $pattern_e   = '!(/*[^/]*)!x';

            if (preg_match($pattern_a, $path)) {
                // remove prefix from $path
                $path = preg_replace($pattern_a, '', $path);
            } elseif (preg_match($pattern_b_1, $path, $matches) || preg_match($pattern_b_2, $path, $matches)) {
                $path = preg_replace('!^' . $matches[1] . '!', '/', $path);
            } elseif (preg_match($pattern_c, $path, $matches)) {
                $path = preg_replace('!^' . preg_quote($matches[1], '!') . '!x', '/', $path);
                // remove the last segment and its preceding "/" (if any) from output buffer
                $new_path = preg_replace('!/([^/]+)$!x', '', $new_path);
            } elseif (preg_match($pattern_d, $path)) {
                $path = preg_replace($pattern_d, '', $path);
            } else {
                if (preg_match($pattern_e, $path, $matches)) {
                    $first_path_segment = $matches[1];

                    $path = preg_replace('/^' . preg_quote($first_path_segment, '/') . '/', '', $path, 1);

                    $new_path .= $first_path_segment;
                }
            }
        }

        return $new_path;
    }

    /**
     * @return $this;
     */
    private function schemeBasedNormalization()
    {
        if (isset($this->default_scheme_ports[$this->scheme]) && $this->default_scheme_ports[$this->scheme] == $this->port) {
            $this->port = '';
        }

        return $this;
    }
}
