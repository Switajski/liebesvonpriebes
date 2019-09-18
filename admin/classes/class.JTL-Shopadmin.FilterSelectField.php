<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class FilterSelectField extends FilterField
{
    public $oOption_arr     = [];
    public $bReloadOnChange = false;

    /**
     * FilterSelectField constructor.
     * 
     * @param Filter $oFilter
     * @param string $cTitle
     * @param string $cColumn
     * @param int    $nDefaultOption
     */
    public function __construct($oFilter, $cTitle, $cColumn, $nDefaultOption = 0)
    {
        parent::__construct($oFilter, 'select', $cTitle, $cColumn, $nDefaultOption);
    }

    /**
     * Add a select option to a filter select field
     *
     * @param string $cTitle - the label/title for this option
     * @param string $cValue
     * @param int    $nTestOp
     *  1 = contains
     *  2 = begins with
     *  3 = ends with
     *  4 = equals
     *  5 = lower than
     *  6 = greater than
     *  7 = lower than or equal
     *  8 = greater than or equal
     *  9 = equals not
     * @return FilterSelectOption
     */
    public function addSelectOption($cTitle, $cValue, $nTestOp = 0)
    {
        $oOption             = new FilterSelectOption($cTitle, $cValue, $nTestOp);
        $this->oOption_arr[] = $oOption;

        return $oOption;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->oOption_arr;
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        $cValue  = $this->oOption_arr[(int)$this->cValue]->getValue();
        $nTestOp = $this->oOption_arr[(int)$this->cValue]->getTestOp();

        if ($cValue !== '' || $nTestOp == 4 || $nTestOp == 9) {
            switch ($nTestOp) {
                case 1: return $this->cColumn . " LIKE '%" . Shop::DB()->escape($cValue) . "%'";
                case 2: return $this->cColumn . " LIKE '" . Shop::DB()->escape($cValue) . "%'";
                case 3: return $this->cColumn . " LIKE '%" . Shop::DB()->escape($cValue) . "'";
                case 4: return $this->cColumn . " = '" . Shop::DB()->escape($cValue) . "'";
                case 5: return $this->cColumn . " < '" . Shop::DB()->escape($cValue) . "'";
                case 6: return $this->cColumn . " > '" . Shop::DB()->escape($cValue) . "'";
                case 7: return $this->cColumn . " <= '" . Shop::DB()->escape($cValue) . "'";
                case 8: return $this->cColumn . " >= '" . Shop::DB()->escape($cValue) . "'";
                case 9: return $this->cColumn . " != '" . Shop::DB()->escape($cValue) . "'";
            }
        }

        return null;
    }
}
