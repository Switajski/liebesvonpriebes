<?php
/**
 * Productcategory Class
 * @access public
 * @author
 * @copyright
 */
class Productcategory extends Document
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
    protected $kCategory;



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
     * Sets the kCategory
     * @access public
     * @var integer
     */
    public function setCategory($kCategory)
    {
        $this->kCategory = intval($kCategory);
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
     * Gets the kCategory
     * @access public
     * @return integer
     */
    public function getCategory()
    {
        return $this->kCategory;
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
