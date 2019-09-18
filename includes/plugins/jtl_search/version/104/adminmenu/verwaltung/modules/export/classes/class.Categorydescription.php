<?php
/**
 * Categorydescription Class
 * @access public
 * @author
 * @copyright
 */
class Categorydescription extends Document
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
    protected $cDescription;



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
     * Sets the cDescription
     * @access public
     * @var string
     */
    public function setDescription($cDescription)
    {
        $this->cDescription = $this->prepareString($cDescription);
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
