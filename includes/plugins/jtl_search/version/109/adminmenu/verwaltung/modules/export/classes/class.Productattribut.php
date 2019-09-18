<?php

/**
 * Productattribut Class
 *
 * @access public
 */
class Productattribut extends Document
{
    /**
     * @access protected
     * @var int
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
     * @param $kProduct
     * @return $this
     */
    public function setProduct($kProduct)
    {
        $this->kProduct = intval($kProduct);

        return $this;
    }

    /**
     * @param $cKey
     * @return $this
     */
    public function setKey($cKey)
    {
        $this->cKey = $cKey;

        return $this;
    }

    /**
     * @param $cValue
     * @return $this
     */
    public function setValue($cValue)
    {
        $this->cValue = $this->prepareString($cValue);

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
     * Gets the cKey
     *
     * @access public
     * @return string
     */
    public function getKey()
    {
        return $this->cKey;
    }

    /**
     * Gets the cValue
     *
     * @access public
     * @return string
     */
    public function getValue()
    {
        return $this->cValue;
    }

    /**
     * Gets the cValue
     *
     * @access public
     * @return string
     */
    public function getLanguageIso()
    {
        return $this->cLanguageIso;
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
