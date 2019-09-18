<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';

/**
 * Class WidgetTop10Bestseller_jtl_widgets
 */
class WidgetTop10Bestseller_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('oTop10Bestseller_arr', Shop::DB()->query(
            "SELECT tbestseller.*, twarenkorbpos.cName
                FROM tbestseller
                JOIN twarenkorbpos 
                    ON twarenkorbpos.kArtikel = tbestseller.kArtikel
                    AND twarenkorbpos.nPosTyp = " . C_WARENKORBPOS_TYP_ARTIKEL . "
                JOIN tbestellung 
                    ON tbestellung.kWarenkorb = twarenkorbpos.kWarenkorb
                    AND DATE_SUB(now(), INTERVAL 7 DAY) < tbestellung.dErstellt
                GROUP BY tbestseller.kArtikel
                ORDER BY tbestseller.fAnzahl DESC
                LIMIT 10", 2
        ));
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(__DIR__ . '/widgetTop10Bestseller.tpl');
    }
}
