<?php

/**
 * Manufacturername Class
 *
 * @access public
 * @author
 * @copyright
 */
class Manufacturername extends Document
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
    protected $cName;

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
     * @param $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = $this->prepareString($cName);

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
     * Gets the cName
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->cName;
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
