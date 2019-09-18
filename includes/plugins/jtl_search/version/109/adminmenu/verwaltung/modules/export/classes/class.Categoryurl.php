<?php

/**
 * Categoryurl Class
 *
 * @access public
 * @author
 * @copyright
 */
class Categoryurl extends Document
{
    /**
     * @access protected
     * @var int
     */
    protected $kCategory;

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
     * @param $kCategory
     * @return $this
     */
    public function setCategory($kCategory)
    {
        $this->kCategory = intval($kCategory);

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
     * Gets the kCategory
     *
     * @access public
     * @return int
     */
    public function getCategory()
    {
        return $this->kCategory;
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
