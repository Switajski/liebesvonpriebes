<?php
/**
 * Categorykeyword Class
 * @access public
 * @author
 * @copyright
 */
class Categorykeyword extends Document
{
    /**
     * @access protected
     * @var integer
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
     * Sets the kCategory
     * @access public
     * @var integer
     */
    public function setCategory($kCategory)
    {
        $this->kCategory = intval($kCategory);
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
     * Gets the kCategory
     * @access public
     * @return integer
     */
    public function getCategory()
    {
        return $this->kCategory;
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
