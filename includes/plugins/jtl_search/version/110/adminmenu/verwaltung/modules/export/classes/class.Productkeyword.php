<?php

/**
 * Productkeyword Class
 *
 * @access public
 */
class Productkeyword extends Document
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
    protected $cKeywords;

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
     * @param $cKeywords
     * @return $this
     */
    public function setKeywords($cKeywords)
    {
        $this->cKeywords = $this->prepareString($cKeywords);

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
     * Gets the cKeywords
     *
     * @access public
     * @return string
     */
    public function getKeywords()
    {
        return $this->cKeywords;
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
