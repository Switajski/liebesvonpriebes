<?php
/**
 * Manufacturerurl Class
 * @access public
 * @author
 * @copyright
 */
class Manufacturerurl extends Document
{
    /**
     * @access protected
     * @var integer
     */
    protected $kManufacturer;

    /**
     * @access protected
     * @var string
     */
    protected $cLanguageIso;

    /**
     * @access protected
     * @var string
     */
    protected $cUrl;



    /**
     * Sets the kManufacturer
     * @access public
     * @var integer
     */
    public function setManufacturer($kManufacturer)
    {
        $this->kManufacturer = intval($kManufacturer);
    }

    /**
     * Sets the cLanguageIso
     * @access public
     * @var string
     */
    public function setLanguageIso($cLanguageIso)
    {
        $this->cLanguageIso = $cLanguageIso;
    }

    /**
     * Sets the cUrl
     * @access public
     * @var string
     */
    public function setUrl($cUrl)
    {
        $this->cUrl = $cUrl;
    }


    /**
     * Gets the kManufacturer
     * @access public
     * @return integer
     */
    public function getManufacturer()
    {
        return $this->kManufacturer;
    }

    /**
     * Gets the cLanguageIso
     * @access public
     * @return string
     */
    public function getLanguageIso()
    {
        return $this->cLanguageIso;
    }

    /**
     * Gets the cUrl
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->cUrl;
    }

    public function isValid()
    {
        return true;
    }

    public function getClassName()
    {
        return get_class();
    }
}
