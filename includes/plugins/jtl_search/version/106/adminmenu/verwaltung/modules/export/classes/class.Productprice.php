<?php
/**
 * Productprice Class
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
class Productprice extends Document
{
    /**
     * @access protected
     * @var integer
     */
    protected $kProduct;

    /**
     * @access protected
     * @var integer
     */
    protected $kUserGroup;

    /**
     * @access protected
     * @var string
     */
    protected $cCurrencyIso;

    /**
     * @access protected
     * @var varchar(255)
     */
    protected $cBasePrice;
    
    /**
     * @access protected
     * @var float
     */
    protected $fPrice;



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
     * Sets the kUserGroup
     * @access public
     * @var integer
     */
    public function setUserGroup($kUserGroup)
    {
        $this->kUserGroup = intval($kUserGroup);
    }

    /**
     * Sets the cCurrencyIso
     * @access public
     * @var string
     */
    public function setCurrencyIso($cCurrencyIso)
    {
        $this->cCurrencyIso = $cCurrencyIso;
    }

    /**
     * Sets the cBasePrice
     * @access public
     * @var varchar(255)
     */
    public function setBasePrice($cBasePrice)
    {
        $this->cBasePrice = $cBasePrice;
    
        return $this;
    }
    
    /**
     * Sets the fPrice
     * @access public
     * @var float
     */
    public function setPrice($fPrice)
    {
        $this->fPrice = floatval($fPrice);
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
     * Gets the kUserGroup
     * @access public
     * @return integer
     */
    public function getUserGroup()
    {
        return $this->kUserGroup;
    }

    /**
     * Gets the cCurrencyIso
     * @access public
     * @return string
     */
    public function getCurrencyIso()
    {
        return $this->cCurrencyIso;
    }
    
    /**
     * Gets the cBasePrice
     * @access public
     * @return varchar(255)
     */
    public function getBasePrice()
    {
        return $this->cBasePrice;
    }

    /**
     * Gets the fPrice
     * @access public
     * @return float
     */
    public function getPrice()
    {
        return $this->fPrice;
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
