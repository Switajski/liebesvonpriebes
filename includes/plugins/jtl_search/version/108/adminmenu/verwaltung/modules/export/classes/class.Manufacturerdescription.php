<?php

/**
 * Manufacturerdescription Class
 *
 * @access public
 * @author
 * @copyright
 */
class Manufacturerdescription extends Document
{
    /**
     * @access protected
     * @var int
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
     * @param $kManufacturer
     * @return $this
     */
    public function setManufacturer($kManufacturer)
    {
        $this->kManufacturer = intval($kManufacturer);

        return $this;
    }

    /**
     * @param $cLanguageIso
     * @return $this
     */
    public function setLanguageIso($cLanguageIso)
    {
        $this->cLanguageIso = $cLanguageIso;

        return $this;
    }

    /**
     * @param $cDescription
     * @return $this
     */
    public function setDescription($cDescription)
    {
        $this->cDescription = $this->prepareString($cDescription);

        return $this;
    }

    /**
     * Gets the kManufacturer
     *
     * @access public
     * @return int
     */
    public function getManufacturer()
    {
        return $this->kManufacturer;
    }

    /**
     * Gets the cLanguageIso
     *
     * @access public
     * @return string
     */
    public function getLanguageIso()
    {
        return $this->cLanguageIso;
    }

    /**
     * Gets the cDescription
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return $this->cDescription;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return get_class();
    }
}
