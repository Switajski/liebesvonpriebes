<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
class FilterDateRangeField extends FilterField
{
    /**
     * @var string
     */
    private $dStart = '';

    /**
     * @var string
     */
    private $dEnd = '';

    /**
     * FilterDateRangeField constructor.
     * @param Filter $oFilter
     * @param string $cTitle
     * @param string $cColumn
     * @param string $cDefValue
     */
    public function __construct($oFilter, $cTitle, $cColumn, $cDefValue = '')
    {
        parent::__construct($oFilter, 'daterange', $cTitle, $cColumn, $cDefValue);

        $dRange = explode(' - ', $this->cValue);

        if (count($dRange) === 2) {
            $this->dStart = date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $this->dEnd   = date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';
        }
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        $dRange = explode(' - ', $this->cValue);

        if (count($dRange) === 2) {
            $dStart = date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $dEnd   = date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';

            return $this->cColumn . " >= '" . $dStart . "' AND " . $this->cColumn . " <= '" . $dEnd . "'";
        }

        return null;
    }

    /**
     * @return string
     */
    public function getStart()
    {
        return $this->dStart;
    }

    /**
     * @return string
     */
    public function getEnd()
    {
        return $this->dEnd;
    }
}
