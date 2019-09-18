<?php
/**
 * Categoryurl Class
 * @access public
 * @author
 * @copyright
 */
class Categoryurl extends Document
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
    protected $cUrl;



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
     * Sets the cUrl
     * @access public
     * @var string
     */
    public function setUrl($cUrl)
    {
        $this->cUrl = $cUrl;
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
     * Gets the cUrl
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->cUrl;
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
