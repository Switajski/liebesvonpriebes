<?php
// -- Multibyte Compatibility functions ---------------------------------------
// http://svn.iphonewebdev.com/lace/lib/mb_compat.php

/**
 *  mb_internal_encoding()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_internal_encoding')) {
    /**
     * @param $enc
     * @return bool
     */
    function mb_internal_encoding($enc)
    {
        return true;
    }
}

/**
 *  mb_regex_encoding()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_regex_encoding')) {
    /**
     * @param $enc
     * @return bool
     */
    function mb_regex_encoding($enc)
    {
        return true;
    }
}

/**
 *  mb_strlen()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_strlen')) {
    /**
     * @param $str
     * @return int
     */
    function mb_strlen($str)
    {
        return strlen($str);
    }
}

/**
 *  mb_strpos()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_strpos')) {
    /**
     * @param     $haystack
     * @param     $needle
     * @param int $offset
     * @return bool|int
     */
    function mb_strpos($haystack, $needle, $offset=0)
    {
        return strpos($haystack, $needle, $offset);
    }
}
/**
 *  mb_stripos()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_stripos')) {
    /**
     * @param     $haystack
     * @param     $needle
     * @param int $offset
     * @return int
     */
    function mb_stripos($haystack, $needle, $offset=0)
    {
        return stripos($haystack, $needle, $offset);
    }
}

/**
 *  mb_substr()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_substr')) {
    /**
     * @param     $str
     * @param     $start
     * @param int $length
     * @return string
     */
    function mb_substr($str, $start, $length=0)
    {
        return substr($str, $start, $length);
    }
}

/**
 *  mb_substr_count()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_substr_count')) {
    /**
     * @param $haystack
     * @param $needle
     * @return int
     */
    function mb_substr_count($haystack, $needle)
    {
        return substr_count($haystack, $needle);
    }
}
