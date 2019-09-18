<?php

/**
 * Productprice Class
 *
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
class Productprice extends Document
{
    /**
     * @access protected
     * @var int
     */
    protected $kProduct;

    /**
     * @access protected
     * @var int
     */
    protected $kUserGroup;

    /**
     * @access protected
     * @var string
     */
    protected $cCurrencyIso;

    /**
     * @access protected
     * @var string
     */
    protected $cBasePrice;

    /**
     * @access protected
     * @var float
     */
    protected $fPrice;

    /**
     * @param $kProduct
     * @return $this
     */
    public function setProduct($kProduct)
    {
        $this->kProduct = intval($kProduct);

        return $this;
    }

    /**
     * @param $kUserGroup
     * @return $this
     */
    public function setUserGroup($kUserGroup)
    {
        $this->kUserGroup = intval($kUserGroup);

        return $this;
    }

    /**
     * @param $cCurrencyIso
     * @return $this
     */
    public function setCurrencyIso($cCurrencyIso)
    {
        $this->cCurrencyIso = $cCurrencyIso;

        return $this;
    }

    /**
     * @param string $cBasePrice
     * @return $this
     */
    public function setBasePrice($cBasePrice)
    {
        $this->cBasePrice = $cBasePrice;

        return $this;
    }

    /**
     * @param $fPrice
     * @return $this
     */
    public function setPrice($fPrice)
    {
        $this->fPrice = floatval($fPrice);

        return $this;
    }

    /**
     * Gets the kProduct
     *
     * @access public
     * @return int
     */
    public function getProduct()
    {
        return $this->kProduct;
    }

    /**
     * Gets the kUserGroup
     *
     * @access public
     * @return int
     */
    public function getUserGroup()
    {
        return $this->kUserGroup;
    }

    /**
     * Gets the cCurrencyIso
     *
     * @access public
     * @return string
     */
    public function getCurrencyIso()
    {
        return $this->cCurrencyIso;
    }

    /**
     * Gets the cBasePrice
     *
     * @access public
     * @return string
     */
    public function getBasePrice()
    {
        return $this->cBasePrice;
    }

    /**
     * Gets the fPrice
     *
     * @access public
     * @return float
     */
    public function getPrice()
    {
        return $this->fPrice;
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
