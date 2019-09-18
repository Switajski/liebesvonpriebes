<?php

namespace JTL\core;

/**
 * Class SessionHandler
 *
 * @package JTL\Session
 */
class SessionHandler
{
    /**
     * @var array
     */
    public $sessionData;

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->sessionData;
    }

    /**
     * @param string $key
     * @param null $default
     * @return array|null
     */
    public function get($key, $default = null)
    {
        $array = $this->sessionData;
        if ($key === null) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value)
    {
        return self::array_set($this->sessionData, $name, $value);
    }

    /**
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function array_set(&$array, $key, $value)
    {
        if ($key === null) {
            $array = $value;

            return $array;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * put a key/value pair or array of key/value pairs in the session.
     *
     * @param  string|array $key
     * @param  mixed|null   $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }
        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * push a value onto a session array.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value)
    {
        $array   = $this->get($key, []);
        $array[] = $value;
        $this->put($key, $array);
    }
}
