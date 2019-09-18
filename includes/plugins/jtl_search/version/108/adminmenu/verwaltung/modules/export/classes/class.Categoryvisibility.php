<?php

/**
 * Categoryvisibility Class
 *
 * @access public
 * @author
 * @copyright
 */
class Categoryvisibility extends Document
{
    /**
     * @access protected
     * @var int
     */
    protected $kCategory;

    /**
     * @access protected
     * @var int
     */
    protected $kUserGroup;

    /**
     * @param $kCategory
     * @return $this
     */
    public function setCategory($kCategory)
    {
        $this->kCategory = intval($kCategory);

        return $this;
    }

    /**
     * @param $kUserGroup
     * @return $this
     */
    public function setUserGroup($kUserGroup)
    {
        $this->kUserGroup = $kUserGroup;

        return $this;
    }

    /**
     * Gets the kCategory
     *
     * @access public
     * @return int
     */
    public function getCategory()
    {
        return $this->kCategory;
    }

    /**
     * Gets the kUserGroup
     *
     * @access public
     * @return string
     */
    public function getUserGroup()
    {
        return $this->kUserGroup;
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
