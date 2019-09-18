<?php

/**
 * Categorykeyword Class
 *
 * @access public
 * @author
 * @copyright
 */
class Categorykeyword extends Document
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
    protected $cKeywords;

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
     * @param $cKeywords
     * @return $this
     */
    public function setKeywords($cKeywords)
    {
        $this->cKeywords = $this->prepareString($cKeywords);

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
