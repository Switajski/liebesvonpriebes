<?php
/**
 * Producturl Class
 * @access public
 * @author
 * @copyright
 */
class Producturl extends Document
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
    protected $cUrl;



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
     * Sets the cUrl
     * @access public
     * @var string
     */
    public function setUrl($cUrl)
    {
        $this->cUrl = $cUrl;
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
