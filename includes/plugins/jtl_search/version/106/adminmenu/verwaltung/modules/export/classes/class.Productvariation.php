<?php
/**
 * Produktvariation Class
 * @access public
 * @author
 * @copyright
 */
class Productvariation extends Document
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
    protected $cKey;

    /**
     * @access protected
     * @var string
     */
    protected $cValue;

    /**
     * @access protected
     * @var string
     */
    protected $cLanguageIso;



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
     * Sets the cKey
     * @access public
     * @var string
     */
    public function setKey($cKey)
    {
        $this->cKey = $cKey;
    }

    /**
     * Sets the cValue
     * @access public
     * @var string
     */
    public function setValue($cValue)
    {
        $this->cValue = $this->prepareString($cValue);
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
     * Gets the cKey
     * @access public
     * @return string
     */
    public function getKey()
    {
        return $this->cKey;
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
     * Gets the cValue
     * @access public
     * @return string
     */
    public function getValue()
    {
        return $this->cValue;
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
