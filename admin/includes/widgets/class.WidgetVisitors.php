<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';

/**
 * Class WidgetVisitors
 */
class WidgetVisitors extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
    }

    /**
     * @return array
     */
    public function getVisitorsOfCurrentMonth()
    {
        $oStatistik    = new Statistik(firstDayOfMonth(), time());

        return $oStatistik->holeBesucherStats(2);
    }

    /**
     * @return array
     */
    public function getVisitorsOfLastMonth()
    {
        $nMonth = date('m') - 1;
        $nYear  = date('Y');

        if ($nMonth <= 0) {
            $nMonth = 12;
            $nYear  = date('Y') - 1;
        }

        $nFrom = firstDayOfMonth($nMonth, $nYear);
        $nTo   = lastDayOfMonth($nMonth, $nYear);

        $oStatistik    = new Statistik($nFrom, $nTo);

        return $oStatistik->holeBesucherStats(2);
    }

    /**
     * @return Linechart
     */
    public function getJSON()
    {
        $oCurrentMonth_arr = $this->getVisitorsOfCurrentMonth();
        $oLastMonth_arr    = $this->getVisitorsOfLastMonth();
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

        return prepareLineChartStatsMulti($Series, getAxisNames(STATS_ADMIN_TYPE_BESUCHER), 2);
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->oSmarty->assign('linechart', $this->getJSON())->fetch('tpl_inc/widgets/visitors.tpl');
    }
}
