<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Path Class
 *
 * @access public
 * @author Daniel Böhmer
 */
class Path
{
    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function combine()
    {
        $paths = func_get_args();

        if (!is_array($paths) || count($paths) == 0) {
            throw new InvalidArgumentException('empty or invalid paths');
        }

        return str_replace('//', '/', implode(DIRECTORY_SEPARATOR, $paths));
    }

    /**
     * @param string $path
     * @param bool   $real
     * @return string
     */
    public static function getDirectoryName($path, $real = true)
    {
        return ($real && is_dir($path)) ? realpath(dirname($path)) : dirname($path);
    }

    /**
     * @param string $path
     * @return mixed|string
     */
    public static function getFileName($path)
    {
        return self::hasExtension($path) ? self::getFileNameWithoutExtension($path) . '.'
            . self::getExtension($path) : self::getFileNameWithoutExtension($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public static function getFileNameWithoutExtension($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public static function getExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function hasExtension($path)
    {
        return (strlen(self::getExtension($path)) > 0);
    }
}
