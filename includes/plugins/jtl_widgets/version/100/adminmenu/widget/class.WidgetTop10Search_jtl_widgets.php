<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';

/**
 * Class WidgetTop10Search
 */
class WidgetTop10Search_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign( 'oTop10Search_arr',
            Shop::DB()->query("
                SELECT * FROM tsuchanfrage
                    WHERE DATE_SUB(now(), INTERVAL 7 DAY) < dZuletztGesucht
                    ORDER BY nAnzahlGesuche DESC LIMIT 10", 2
            )
        );
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(__DIR__ . '/widgetTop10Search.tpl');
    }
}
