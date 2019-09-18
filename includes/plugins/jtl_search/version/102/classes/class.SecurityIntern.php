<?php
require_once(JTLSEARCH_PFAD_CLASSES.'interface.ISecurity.php');
/**
 * SecurityIntern Class
 * @access public
 * @author Daniel Boehmer
 * @copyright 2011 JTL-Software GmbH
 */
class SecurityIntern implements ISecurity
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
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->cSHA1Key = "";
        $this->cParam_arr = array();
    }
    
    
    /**
     * Create SHA1 Key
     * @access public
     * @param bool bReturnKey
     * @return string / bool
     */
    public function createKey($bReturnKey = true)
    {
        if (is_array($this->cParam_arr) && count($this->cParam_arr) > 0) {
            if (defined("JTLSEARCH_SECRET_KEY")) {
                $this->cSHA1Key = JTLSEARCH_SECRET_KEY;
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
     * Sets the cParam_arr
     * @access public
     * @var array
     */
    public function setParam_arr(array $cParam_arr)
    {
        $this->cParam_arr = $cParam_arr;
    }
    
    /**
     * Gets the cSHA1Key
     * @access public
     * @return string
     */
    public function getSHA1Key()
    {
        return $this->cSHA1Key;
    }
    
    /**
     * Gets the cParam_arr
     * @access public
     * @return array
     */
    public function getParam_arr()
    {
        return $this->cParam_arr;
    }
    
    /**
     * Sets the cProjectId
     * @access public
     */
    public function setProjectId($cProjectId)
    {
        $this->cProjectId = $cProjectId;
    }
    
    
    /**
     * Sets the cAuthHash
     * @access public
     */
    public function setAuthHash($cAuthHash)
    {
        $this->cAuthHash = $cAuthHash;
    }
}
