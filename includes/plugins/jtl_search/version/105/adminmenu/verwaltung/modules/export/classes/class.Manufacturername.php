<?php
/**
 * Manufacturername Class
 * @access public
 * @author
 * @copyright
 */
class Manufacturername extends Document
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
    protected $cName;



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
     * Sets the cName
     * @access public
     * @var string
     */
    public function setName($cName)
    {
        $this->cName = $this->prepareString($cName);
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
     * Gets the cName
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->cName;
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
