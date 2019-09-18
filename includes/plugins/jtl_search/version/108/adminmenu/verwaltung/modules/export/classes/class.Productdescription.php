<?php

/**
 * Productdescription Class
 *
 * @access public
 */
class Productdescription extends Document
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
    protected $cDescription;

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
     * @param $cDescription
     * @return $this
     */
    public function setDescription($cDescription)
    {
        $this->cDescription = $this->prepareString($cDescription);

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
     * Gets the cDescription
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return $this->cDescription;
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
