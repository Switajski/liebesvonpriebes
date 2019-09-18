<?php

/**
 * Manufacturerkeyword Class
 *
 * @access public
 * @author
 * @copyright
 */
class Manufacturerkeyword extends Document
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
    protected $cKeywords;

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
     * @param $cKeywords
     * @return $this
     */
    public function setKeywords($cKeywords)
    {
        $this->cKeywords = $this->prepareString($cKeywords);

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
     * Gets the cKeywords
     *
     * @access public
     * @return string
     */
    public function getKeywords()
    {
        return $this->cKeywords;
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
