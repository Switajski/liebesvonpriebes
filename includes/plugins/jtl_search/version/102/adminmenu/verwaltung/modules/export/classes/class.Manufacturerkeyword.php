<?php
/**
 * Manufacturerkeyword Class
 * @access public
 * @author
 * @copyright
 */
class Manufacturerkeyword extends Document
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
    protected $cKeywords;



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
     * Sets the cKeywords
     * @access public
     * @var string
     */
    public function setKeywords($cKeywords)
    {
        $this->cKeywords = $this->prepareString($cKeywords);
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
     * Gets the cKeywords
     * @access public
     * @return string
     */
    public function getKeywords()
    {
        return $this->cKeywords;
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
