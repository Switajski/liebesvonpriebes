<?php
require_once JTLSEARCH_PFAD_CLASSES . 'interface.ISecurity.php';

/**
 * Security Class
 *
 * @access public
 * @author Daniel Boehmer
 * @copyright 2011 JTL-Software GmbH
 */
class Security implements ISecurity
{
    /**
     * @access private
     * @var string
     */
    private $cSHA1Key;

    /**
     * @access private
     * @var array
     */
    private $cParam_arr;

    /**
     * @access private
     * @var string
     */
    private $cProjectId;

    /**
     * @access private
     * @var string
     */
    private $cAuthHash;

    /**
     * @param $cProjectId
     * @param $cAuthHash
     */
    public function __construct($cProjectId, $cAuthHash)
    {
        $this->cProjectId = $cProjectId;
        $this->cAuthHash  = $cAuthHash;
    }

    /**
     * Create SHA1 Key
     *
     * @access public
     * @param bool $bReturnKey
     * @return string / bool
     */
    public function createKey($bReturnKey = true)
    {
        if (is_array($this->cParam_arr) && count($this->cParam_arr) > 0) {
            if (strlen($this->cAuthHash) > 0 && strlen($this->cProjectId) > 0) {
                $this->cSHA1Key = "{$this->cAuthHash}.{$this->cProjectId}";
                foreach ($this->cParam_arr as $cParam) {
                    $this->cSHA1Key .= "." . $cParam;
                }

                $this->cSHA1Key = sha1($this->cSHA1Key);

                if ($bReturnKey) {
                    return $this->cSHA1Key;
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $cParam_arr
     * @return $this
     */
    public function setParam_arr(array $cParam_arr)
    {
        $this->cParam_arr = $cParam_arr;

        return $this;
    }

    /**
     * Gets the cSHA1Key
     *
     * @access public
     * @return string
     */
    public function getSHA1Key()
    {
        return $this->cSHA1Key;
    }

    /**
     * Gets the cParam_arr
     *
     * @access public
     * @return array
     */
    public function getParam_arr()
    {
        return $this->cParam_arr;
    }

    /**
     * @param $cProjectId
     * @return $this
     */
    public function setProjectId($cProjectId)
    {
        $this->cProjectId = $cProjectId;

        return $this;
    }

    /**
     * @param $cAuthHash
     * @return $this
     */
    public function setAuthHash($cAuthHash)
    {
        $this->cAuthHash = $cAuthHash;

        return $this;
    }
}
