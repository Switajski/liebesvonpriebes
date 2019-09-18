<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';

/**
 * Class WidgetShopStats_jtl_widgets
 */
class WidgetShopStats_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $nStampNow       = mktime(0, 0, 0);
        $nStampYesterday = $nStampNow - 86400;
        $nStampTomorrow  = $nStampNow + 86400;

        $oStatYesterday = new stdClass();
        $oStatToday     = new stdClass();

        $oStatYesterday->nStampVon = $nStampYesterday;
        $oStatYesterday->nStampBis = $nStampNow;

        $oStatToday->nStampVon = $nStampNow;
        $oStatToday->nStampBis = $nStampTomorrow;

        $this->baueUmsatz($oStatYesterday, $oStatToday)
             ->baueBesucher($oStatYesterday, $oStatToday)
             ->baueNeukunden($oStatYesterday, $oStatToday)
             ->baueAnzahlBestellungen($oStatYesterday, $oStatToday)
             ->baueBesucherProBestellungen($oStatYesterday, $oStatToday);

        $this->oSmarty->assign('oStatYesterday', $oStatYesterday)
                      ->assign('oStatToday', $oStatToday);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(__DIR__ . '/widgetShopStats.tpl');
    }

    /**
     * @param object $oStatYesterday
     * @param object $oStatToday
     * @return $this
     */
    private function baueUmsatz(&$oStatYesterday, &$oStatToday)
    {
        //gestern
        $oUmsatz_arr = Shop::DB()->query("
            SELECT tbestellung.dErstellt AS dZeit, SUM(tbestellung.fGesamtsumme) AS nCount,
                DATE_FORMAT(tbestellung.dErstellt, '%m') AS nMonth, DATE_FORMAT(tbestellung.dErstellt, '%H') AS nHour, 
                DATE_FORMAT(tbestellung.dErstellt, '%d') AS nDay,
                DATE_FORMAT(tbestellung.dErstellt, '%Y') AS nYear
                FROM tbestellung
                WHERE tbestellung.dErstellt BETWEEN FROM_UNIXTIME(" . $oStatYesterday->nStampVon . ")
                    AND FROM_UNIXTIME(" . $oStatYesterday->nStampBis . ")
                GROUP BY DAY(tbestellung.dErstellt), YEAR(tbestellung.dErstellt), MONTH(tbestellung.dErstellt)
                ORDER BY tbestellung.dErstellt ASC", 2
        );

        $oStatYesterday->fUmsatz = 0.0;
        if (is_array($oUmsatz_arr) && count($oUmsatz_arr) > 0) {
            foreach ($oUmsatz_arr as $oUmsatz) {
                $oStatYesterday->fUmsatz += $oUmsatz->nCount;
            }
        }
        $oStatYesterday->fUmsatz = gibPreisStringLocalized($oStatYesterday->fUmsatz);

        // Heute
        $oUmsatz_arr         = Shop::DB()->query("
            SELECT tbestellung.dErstellt AS dZeit, SUM(tbestellung.fGesamtsumme) AS nCount,
                DATE_FORMAT(tbestellung.dErstellt, '%m') AS nMonth, DATE_FORMAT(tbestellung.dErstellt, '%H') AS nHour, 
                DATE_FORMAT(tbestellung.dErstellt, '%d') AS nDay,
                DATE_FORMAT(tbestellung.dErstellt, '%Y') AS nYear
                FROM tbestellung
                WHERE tbestellung.dErstellt BETWEEN FROM_UNIXTIME(" . $oStatToday->nStampVon . ")
                    AND FROM_UNIXTIME(" . $oStatToday->nStampBis . ")
                GROUP BY DAY(tbestellung.dErstellt), YEAR(tbestellung.dErstellt), MONTH(tbestellung.dErstellt)
                ORDER BY tbestellung.dErstellt ASC", 2
        );
        $oStatToday->fUmsatz = 0.0;
        if (is_array($oUmsatz_arr) && count($oUmsatz_arr) > 0) {
            foreach ($oUmsatz_arr as $oUmsatz) {
                $oStatToday->fUmsatz += $oUmsatz->nCount;
            }
        }
        $oStatToday->fUmsatz = gibPreisStringLocalized($oStatToday->fUmsatz);

        return $this;
    }

    /**
     * @param object $oStatYesterday
     * @param object $oStatToday
     * @return $this
     */
    private function baueBesucher(&$oStatYesterday, &$oStatToday)
    {
        //Gestern
        $oBesucher_arr = Shop::DB()->query(
            "SELECT * , sum( t.nCount ) AS nCount
                FROM (
                SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime, DATE_FORMAT( dZeit, '%m' ) AS nMonth, 
                DATE_FORMAT( dZeit, '%H' ) AS nHour, DATE_FORMAT( dZeit, '%d' ) AS nDay, 
                DATE_FORMAT( dZeit, '%Y' ) AS nYear, COUNT( dZeit ) AS nCount
                FROM tbesucherarchiv
                WHERE dZeit BETWEEN FROM_UNIXTIME(" . $oStatYesterday->nStampVon . ") 
                    AND FROM_UNIXTIME(" . $oStatYesterday->nStampBis . ")
                    AND kBesucherBot = 0
                GROUP BY DAY(dZeit), YEAR(dZeit), MONTH(dZeit)
                UNION SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime, DATE_FORMAT( dZeit, '%m' ) AS nMonth, 
                    DATE_FORMAT( dZeit, '%H' ) AS nHour, 
                    DATE_FORMAT( dZeit, '%d' ) AS nDay, 
                    DATE_FORMAT( dZeit, '%Y' ) AS nYear, COUNT( dZeit ) AS nCount
                FROM tbesucher
                WHERE dZeit BETWEEN FROM_UNIXTIME(" . $oStatYesterday->nStampVon . ") 
                    AND FROM_UNIXTIME(" . $oStatYesterday->nStampBis . ")
                    AND kBesucherBot = 0
                GROUP BY DAY(dZeit), YEAR(dZeit), MONTH(dZeit)
                ) AS t
                GROUP BY DAY(dZeit), YEAR(dZeit), MONTH(dZeit)
                ORDER BY dTime ASC", 2
        );

        $oStatYesterday->nBesucher = 0;
        if (is_array($oBesucher_arr) && count($oBesucher_arr) > 0) {
            foreach ($oBesucher_arr as $oBesucher) {
                $oStatYesterday->nBesucher += (int)$oBesucher->nCount;
            }
        }
        //Heute
        $oBesucher_arr = Shop::DB()->query(
            "SELECT * , sum( t.nCount ) AS nCount
                FROM (
                SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime, DATE_FORMAT( dZeit, '%m' ) AS nMonth, 
                DATE_FORMAT( dZeit, '%H' ) AS nHour, DATE_FORMAT( dZeit, '%d' ) AS nDay, 
                DATE_FORMAT( dZeit, '%Y' ) AS nYear, COUNT( dZeit ) AS nCount
                FROM tbesucherarchiv
                WHERE dZeit BETWEEN FROM_UNIXTIME(" . $oStatToday->nStampVon . ") 
                    AND FROM_UNIXTIME(" . $oStatToday->nStampBis . ")
                    AND kBesucherBot = 0
                GROUP BY DAY(dZeit), YEAR(dZeit), MONTH(dZeit)
                UNION SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime, DATE_FORMAT( dZeit, '%m' ) AS nMonth, 
                    DATE_FORMAT( dZeit, '%H' ) AS nHour, DATE_FORMAT( dZeit, '%d' ) AS nDay, 
                    DATE_FORMAT( dZeit, '%Y' ) AS nYear, COUNT( dZeit ) AS nCount
                FROM tbesucher
                WHERE dZeit BETWEEN FROM_UNIXTIME(" . $oStatToday->nStampVon . ") 
                    AND FROM_UNIXTIME(" . $oStatToday->nStampBis . ")
                    AND kBesucherBot = 0
                GROUP BY DAY(dZeit), YEAR(dZeit), MONTH(dZeit)
                ) AS t
                GROUP BY DAY(dZeit), YEAR(dZeit), MONTH(dZeit)
                ORDER BY dTime ASC", 2
        );

        $oStatToday->nBesucher = 0;
        if (is_array($oBesucher_arr) && count($oBesucher_arr) > 0) {
            foreach ($oBesucher_arr as $oBesucher) {
                $oStatToday->nBesucher += (int)$oBesucher->nCount;
            }
        }

        return $this;
    }

    /**
     * @param object $oStatYesterday
     * @param object $oStatToday
     * @return $this
     */
    private function baueNeukunden(&$oStatYesterday, &$oStatToday)
    {
        //Gestern
        $oKunde                     = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tkunde
                WHERE dErstellt = '" . date('Y-m-d', $oStatYesterday->nStampVon) . "'
                    AND nRegistriert = 1", 1
        );
        $oStatYesterday->nNeuKunden = 0;
        if (isset($oKunde->nAnzahl) && $oKunde->nAnzahl > 0) {
            $oStatYesterday->nNeuKunden = (int)$oKunde->nAnzahl;
        }
        //Heute
        $oKunde                 = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tkunde
                WHERE dErstellt = '" . date('Y-m-d', $oStatToday->nStampVon) . "'
                    AND nRegistriert = 1", 1
        );
        $oStatToday->nNeuKunden = 0;
        if (isset($oKunde->nAnzahl) && $oKunde->nAnzahl > 0) {
            $oStatToday->nNeuKunden = (int)$oKunde->nAnzahl;
        }

        return $this;
    }

    /**
     * @param object $oStatYesterday
     * @param object $oStatToday
     * @return $this
     */
    private function baueAnzahlBestellungen(&$oStatYesterday, &$oStatToday)
    {
        //Gestern
        $oBestellung                       = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbestellung
                WHERE dErstellt BETWEEN FROM_UNIXTIME(" . $oStatYesterday->nStampVon . ")
                    AND FROM_UNIXTIME(" . $oStatYesterday->nStampBis . ")", 1
        );
        $oStatYesterday->nAnzahlBestellung = 0;
        if (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) {
            $oStatYesterday->nAnzahlBestellung = $oBestellung->nAnzahl;
        }
        //Heute
        $oBestellung                   = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbestellung
                WHERE dErstellt BETWEEN FROM_UNIXTIME(" . $oStatToday->nStampVon . ")
                    AND FROM_UNIXTIME(" . $oStatToday->nStampBis . ")", 1
        );
        $oStatToday->nAnzahlBestellung = 0;
        if (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) {
            $oStatToday->nAnzahlBestellung = $oBestellung->nAnzahl;
        }

        return $this;
    }

    /**
     * @param object $oStatYesterday
     * @param object $oStatToday
     * @return $this
     */
    private function baueBesucherProBestellungen(&$oStatYesterday, &$oStatToday)
    {
        //Gestern
        $oStatYesterday->nBesucherProBestellung = 0;
        if ($oStatYesterday->nBesucher > 0 && $oStatYesterday->nAnzahlBestellung > 0) {
            $oStatYesterday->nBesucherProBestellung = number_format(
                $oStatYesterday->nBesucher / $oStatYesterday->nAnzahlBestellung, 2, ',', '.'
            );
        }
        //Heute
        $oStatToday->nBesucherProBestellung = 0;
        if ($oStatToday->nBesucher > 0 && $oStatToday->nAnzahlBestellung > 0) {
            $oStatToday->nBesucherProBestellung = number_format(
                $oStatToday->nBesucher / $oStatToday->nAnzahlBestellung, 2, ',', '.'
            );
        }

        return $this;
    }
}
