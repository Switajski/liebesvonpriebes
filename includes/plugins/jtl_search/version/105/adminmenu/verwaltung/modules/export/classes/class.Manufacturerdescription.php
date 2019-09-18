<?php
/**
 * Manufacturerdescription Class
 * @access public
 * @author
 * @copyright
 */
class Manufacturerdescription extends Document
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
    protected $cDescription;



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
     * Sets the cDescription
     * @access public
     * @var string
     */
    public function setDescription($cDescription)
    {
        $this->cDescription = $this->prepareString($cDescription);
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
     * Gets the cDescription
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return $this->cDescription;
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
