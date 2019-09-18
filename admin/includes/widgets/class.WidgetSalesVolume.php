<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Statistik.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';
require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';

/**
 * Class WidgetSalesVolume
 */
class WidgetSalesVolume extends WidgetBase
{
    /**
     * @var stdClass
     */
    public $oWaehrung;

    /**
     *
     */
    public function init()
    {
        $this->oWaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
    }

    /**
     * @param int $nMonth
     * @param int $nYear
     * @return array|mixed
     */
    public function calcVolumeOfMonth($nMonth, $nYear)
    {
        $nAnzeigeIntervall = 0;
        $nDateStampVon     = firstDayOfMonth($nMonth, $nYear);
        $nDateStampBis     = lastDayOfMonth($nMonth, $nYear);
        $oStats_arr        = gibBackendStatistik(STATS_ADMIN_TYPE_UMSATZ, $nDateStampVon, $nDateStampBis, $nAnzeigeIntervall);
        if (is_array($oStats_arr)) {
            foreach ($oStats_arr as &$oStats) {
                $oStats->cLocalized = gibPreisStringLocalized($oStats->nCount, $this->oWaehrung, 1);
            }
        }

        return $oStats_arr;
    }

    /**
     * @return Linechart
     */
    public function getJSON()
    {
        $lastmonth = new DateTime();
        $lastmonth->modify('-1 month');
        $lastmonth         = $lastmonth->format('U');
        $oCurrentMonth_arr = $this->calcVolumeOfMonth(date('n'), date('Y'));
        $oLastMonth_arr    = $this->calcVolumeOfMonth(date('n', $lastmonth), date('Y', $lastmonth));
        if (is_array($oCurrentMonth_arr) && count($oCurrentMonth_arr) > 0) {
            foreach ($oCurrentMonth_arr as &$oCurrentMonth) {
                $oCurrentMonth->dZeit = substr($oCurrentMonth->dZeit, 0, 2);
            }
        }
        if (is_array($oLastMonth_arr) && count($oLastMonth_arr) > 0) {
            foreach ($oLastMonth_arr as &$oLastMonth) {
                $oLastMonth->dZeit = substr($oLastMonth->dZeit, 0, 2);
            }
        }
        $Series = [
            'Letzter Monat' => $oLastMonth_arr,
            'Dieser Monat'  => $oCurrentMonth_arr
        ];

        return prepareLineChartStatsMulti($Series, getAxisNames(STATS_ADMIN_TYPE_UMSATZ), 2);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('linechart', $this->getJSON())->fetch('tpl_inc/widgets/sales_volume.tpl');
    }
}
