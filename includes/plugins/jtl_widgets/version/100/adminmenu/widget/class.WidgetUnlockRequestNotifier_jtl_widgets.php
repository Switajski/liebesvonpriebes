<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';

/**
 * Class WidgetUnlockRequestNotifier_jtl_widgets
 */
class WidgetUnlockRequestNotifier_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $cSQL               = '';
        $cSuchSQL           = new stdClass();
        $oRequestGroup_arr  = [];
        $kRequestCountTotal = 0;

        $cSuchSQL->cWhere             = '';
        $oRequestGroup                = new stdClass();
        $oRequestGroup->cGroupName    = 'Bewertungen';
        $oRequestGroup->kRequestCount = count(gibBewertungFreischalten($cSQL, $cSuchSQL, false));
        $oRequestGroup_arr[]          = $oRequestGroup;
        $kRequestCountTotal += $oRequestGroup->kRequestCount;

        $cSuchSQL->cOrder             = ' dZuletztGesucht DESC ';
        $oRequestGroup                = new stdClass();
        $oRequestGroup->cGroupName    = 'Suchanfragen';
        $oRequestGroup->kRequestCount = count(gibSuchanfrageFreischalten($cSQL, $cSuchSQL, false));
        $oRequestGroup_arr[]          = $oRequestGroup;
        $kRequestCountTotal += $oRequestGroup->kRequestCount;

        $oRequestGroup                = new stdClass();
        $oRequestGroup->cGroupName    = 'Tags';
        $oRequestGroup->kRequestCount = count(gibTagFreischalten($cSQL, $cSuchSQL, false));
        $oRequestGroup_arr[]          = $oRequestGroup;
        $kRequestCountTotal += $oRequestGroup->kRequestCount;

        $oRequestGroup                = new stdClass();
        $oRequestGroup->cGroupName    = 'Newskommentare';
        $oRequestGroup->kRequestCount = count(gibNewskommentarFreischalten($cSQL, $cSuchSQL, false));
        $oRequestGroup_arr[]          = $oRequestGroup;
        $kRequestCountTotal += $oRequestGroup->kRequestCount;

        $cSuchSQL->cOrder             = ' tnewsletterempfaenger.dEingetragen DESC ';
        $oRequestGroup                = new stdClass();
        $oRequestGroup->cGroupName    = 'Newsletterempf&auml;nger';
        $oRequestGroup->kRequestCount = count(gibNewsletterEmpfaengerFreischalten($cSQL, $cSuchSQL, false));
        $oRequestGroup_arr[]          = $oRequestGroup;
        $kRequestCountTotal += $oRequestGroup->kRequestCount;

        $this->oSmarty->assign('oRequestGroup_arr', $oRequestGroup_arr)
                      ->assign('kRequestCountTotal', $kRequestCountTotal);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(__DIR__ . '/widgetUnlockRequestNotifier.tpl');
    }
}
