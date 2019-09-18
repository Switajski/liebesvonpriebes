<?php

/**
 * Producturl Class
 *
 * @access public
 * @author
 * @copyright
 */
class Producturl extends Document
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
    protected $cLanguageIso;

    /**
     * @access protected
     * @var string
     */
    protected $cUrl;

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
     * @param $cLanguageIso
     * @return $this
     */
    public function setLanguageIso($cLanguageIso)
    {
        $this->cLanguageIso = $cLanguageIso;

        return $this;
    }

    /**
     * @param $cUrl
     * @return $this
     */
    public function setUrl($cUrl)
    {
        $this->cUrl = $cUrl;

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
     * Gets the cUrl
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->cUrl;
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
