<?php

/**
 * Manufacturerurl Class
 *
 * @access public
 * @author
 * @copyright
 */
class Manufacturerurl extends Document
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
    protected $cUrl;

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
     * @param $cUrl
     * @return $this
     */
    public function setUrl($cUrl)
    {
        $this->cUrl = $cUrl;

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
     * Gets the cUrl
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->cUrl;
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
