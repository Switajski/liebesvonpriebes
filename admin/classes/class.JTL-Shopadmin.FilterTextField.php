<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
class FilterTextField extends FilterField
{
    protected $nTestOp       = 0;
    protected $nDataType     = 0;
    protected $bCustomTestOp = true;

    /**
     * FilterTextField constructor.
     * 
     * @param Filter $oFilter
     * @param string|array $cTitle - either title-string for this field or a pair of short title and long title
     * @param string|array $cColumn - column/field or array of them to be searched disjunctively (OR)
     * @param int    $nTestOp
     *  0 = custom
     *  1 = contains
     *  2 = begins with
     *  3 = ends with
     *  4 = equals
     *  5 = lower than
     *  6 = greater than
     *  7 = lower than or equal
     *  8 = greater than or equal
     *  9 = equals not
     * @param int    $nDataType
     *  0 = text
     *  1 = number
     */
    public function __construct($oFilter, $cTitle, $cColumn, $nTestOp = 0, $nDataType = 0)
    {
        parent::__construct($oFilter, 'text', $cTitle, $cColumn);

        $this->nTestOp       = (int)$nTestOp;
        $this->nDataType     = (int)$nDataType;
        $this->bCustomTestOp = $this->nTestOp == 0;

        if ($this->bCustomTestOp) {
            $this->nTestOp =
                $oFilter->getAction() === $oFilter->getId() . '_filter'      ? (int)$_GET[$oFilter->getId() . '_' . $this->cId . '_op'] : (
                $oFilter->getAction() === $oFilter->getId() . '_resetfilter' ? 1 : (
                $oFilter->hasSessionField($this->cId . '_op')                ? (int)$oFilter->getSessionField($this->cId . '_op') :
                                                                               1
                ));
        }
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        if ($this->cValue !== '' || ($this->nDataType === 0 && ($this->nTestOp == 4 || $this->nTestOp == 9))) {
            if (is_array($this->cColumn)) {
                $cColumn_arr = $this->cColumn;
            } else {
                $cColumn_arr = [$this->cColumn];
            }
            $cClausePart_arr = [];
            foreach ($cColumn_arr as $cColumn) {
                switch ($this->nTestOp) {
                    case 1: $cClausePart_arr[] = $cColumn . " LIKE '%" . Shop::DB()->escape($this->cValue) . "%'"; break;
                    case 2: $cClausePart_arr[] = $cColumn . " LIKE '" . Shop::DB()->escape($this->cValue) . "%'"; break;
                    case 3: $cClausePart_arr[] = $cColumn . " LIKE '%" . Shop::DB()->escape($this->cValue) . "'"; break;
                    case 4: $cClausePart_arr[] = $cColumn . " = '" . Shop::DB()->escape($this->cValue) . "'"; break;
                    case 5: $cClausePart_arr[] = $cColumn . " < '" . Shop::DB()->escape($this->cValue) . "'"; break;
                    case 6: $cClausePart_arr[] = $cColumn . " > '" . Shop::DB()->escape($this->cValue) . "'"; break;
                    case 7: $cClausePart_arr[] = $cColumn . " <= '" . Shop::DB()->escape($this->cValue) . "'"; break;
                    case 8: $cClausePart_arr[] = $cColumn . " >= '" . Shop::DB()->escape($this->cValue) . "'"; break;
                    case 9: $cClausePart_arr[] = $cColumn . " != '" . Shop::DB()->escape($this->cValue) . "'"; break;
                }
            }

            return '(' . implode(' OR ', $cClausePart_arr) . ')';
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTestOp()
    {
        return $this->nTestOp;
    }

    /**
     * @return int
     */
    public function getDataType()
    {
        return $this->nDataType;
    }

    /**
     * @return boolean
     */
    public function isCustomTestOp()
    {
        return $this->bCustomTestOp;
    }
}
