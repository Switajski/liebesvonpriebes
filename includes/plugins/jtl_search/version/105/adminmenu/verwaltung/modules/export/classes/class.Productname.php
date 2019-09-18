<?php
/**
 * Productname Class
 * @access public
 * @author
 * @copyright
 */
class Productname extends Document
{
    /**
     * @access protected
     * @var integer
     */
    protected $kProduct;

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
     * Sets the kProduct
     * @access public
     * @var integer
     */
    public function setProduct($kProduct)
    {
        $this->kProduct = intval($kProduct);
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
     * Gets the kProduct
     * @access public
     * @return integer
     */
    public function getProduct()
    {
        return $this->kProduct;
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
