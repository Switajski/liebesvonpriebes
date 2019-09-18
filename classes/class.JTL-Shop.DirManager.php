<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class DirManager
 *
 * @access public
 * @author Daniel Böhmer
 */
class DirManager
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var bool
     */
    public $isdir;

    /**
     * @var string
     */
    public $path;

    /**
     * Userfunc (callback function) must have 1 parameter (array)
     *
     * @param string        $path
     * @param null|callable $userfunc
     * @param array         $parameters
     * @return $this
     */
    public function getData($path, $userfunc = null, array $parameters = null)
    {
        // Linux or Windows?
        $islinux = true;
        if (strpos($path, '\\') !== false) {
            $islinux = false;
        }
        if ($islinux) {
            if (strpos(substr($path, strlen($path) - 1, 1), '/') === false) {
                $path .= '/';
            }
        } else {
            if (strpos(substr($path, strlen($path) - 1, 1), '\\') === false) {
                $path .= '\\';
            }
        }
        if (is_dir($path)) {
            $this->path = $path;
            $dirhandle  = @opendir($path);
            if ($dirhandle) {
                while (($file = readdir($dirhandle)) !== false) {
                    if ($file !== '.' && $file !== '..' && $file !== '.svn' && $file !== '.git') {
                        $this->filename = $file;
                        // Go 1 level deeper
                        if (is_dir($path . $file)) {
                            $this->isdir = true;
                            $this->getData($path . $file, $userfunc, $parameters);
                        }
                        // Last level dir?
                        $options = [
                            'filename' => $file,
                            'path'     => $path,
                            'isdir'    => false
                        ];
                        if (is_dir($path . $file)) {
                            $options['isdir'] = true;
                        }
                        if ($parameters !== null && is_array($parameters)) {
                            $options = array_merge($options, $parameters);
                        }
                        if ($userfunc !== null) {
                            $userfunc($options);
                        }
                    }
                }

                @closedir($dirhandle);
            }
        }

        return $this;
    }
}
