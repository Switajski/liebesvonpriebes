<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class FilterSelectOption
{
    protected $cTitle  = '';
    protected $cValue  = '';
    protected $nTestOp = 0;

    /**
     * FilterSelectOption constructor.
     * 
     * @param string $cTitle
     * @param string $cValue
     * @param int    $nTestOp
     */
    public function __construct($cTitle, $cValue, $nTestOp)
    {
        $this->cTitle  = $cTitle;
        $this->cValue  = $cValue;
        $this->nTestOp = $nTestOp;
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
    public function getValue()
    {
        return $this->cValue;
    }

    /**
     * @return int
     */
    public function getTestOp()
    {
        return $this->nTestOp;
    }
}
