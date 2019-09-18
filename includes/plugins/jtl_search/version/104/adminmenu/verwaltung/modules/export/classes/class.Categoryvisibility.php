<?php
/**
 * Categoryvisibility Class
 * @access public
 * @author
 * @copyright
 */
class Categoryvisibility extends Document
{
    /**
     * @access protected
     * @var integer
     */
    protected $kCategory;

    /**
     * @access protected
     * @var integer
     */
    protected $kUserGroup;



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
     * Sets the kUserGroup
     * @access public
     * @var string
     */
    public function setUserGroup($kUserGroup)
    {
        $this->kUserGroup = $kUserGroup;
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

    /**
     * Gets the kUserGroup
     * @access public
     * @return string
     */
    public function getUserGroup()
    {
        return $this->kUserGroup;
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
