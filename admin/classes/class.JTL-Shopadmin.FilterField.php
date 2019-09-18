<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterField
 */
abstract class FilterField
{
    protected $oFilter;
    protected $cType      = '';
    protected $cTitle     = '';
    protected $cTitleLong = '';
    protected $cColumn    = '';
    protected $cValue     = '';
    protected $cId        = '';

    /**
     * FilterField constructor.
     * 
     * @param Filter $oFilter
     * @param string $cType
     * @param string|array $cTitle - either title-string for this field or a pair of short title and long title
     * @param string $cColumn
     * @param string $cDefValue
     */
    public function __construct($oFilter, $cType, $cTitle, $cColumn, $cDefValue = '')
    {
        $this->oFilter    = $oFilter;
        $this->cType      = $cType;
        $this->cTitle     = is_array($cTitle) ? $cTitle[0] : $cTitle;
        $this->cTitleLong = is_array($cTitle) ? $cTitle[1] : '';
        $this->cColumn    = $cColumn;
        $this->cId        = preg_replace('/[^a-zA-Z0-9_]+/', '', $this->cTitle);
        $this->cValue     =
            $oFilter->getAction() === $oFilter->getId() . '_filter'      ? $_GET[$oFilter->getId() . '_' . $this->cId] : (
            $oFilter->getAction() === $oFilter->getId() . '_resetfilter' ? $cDefValue : (
            $oFilter->hasSessionField($this->cId)                        ? $oFilter->getSessionField($this->cId) :
                                                                           $cDefValue
            ));
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->cValue;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->cType;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->cColumn;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->cTitle;
    }

    /**
     * @return string
     */
    public function getTitleLong()
    {
        return $this->cTitleLong;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->cId;
    }

    /**
     * @return string|null
     */
    abstract public function getWhereClause();
}
