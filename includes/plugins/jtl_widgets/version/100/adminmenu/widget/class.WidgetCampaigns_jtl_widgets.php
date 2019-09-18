<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kampagne.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kampagne_inc.php';

/**
 * Class WidgetCampaigns_jtl_widgets
 */
class WidgetCampaigns_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $_SESSION['Kampagne']                 = new stdClass();
        $_SESSION['Kampagne']->nAnsicht       = 2;
        $_SESSION['Kampagne']->nSort          = 0;
        $_SESSION['Kampagne']->cSort          = 'DESC';
        $_SESSION['Kampagne']->nDetailAnsicht = 2;
        $_SESSION['Kampagne']->cFromDate_arr  = ['nJahr' => date('Y'), 'nMonat' => date('n'), 'nTag' => '1'];
        $_SESSION['Kampagne']->cToDate_arr    = ['nJahr' => date('Y'), 'nMonat' => date('n'), 'nTag' => date('j')];
        $_SESSION['Kampagne']->cFromDate      = date('Y-n-1');
        $_SESSION['Kampagne']->cToDate        = date('Y-n-j');

        $oKampagne_arr    = holeAlleKampagnen(true, false);
        $oKampagneDef_arr = holeAlleKampagnenDefinitionen();
        $kFirst           = array_keys($oKampagne_arr);
        $kFirst           = $kFirst[0];
        $kKampagne        = (int)$oKampagne_arr[$kFirst]->kKampagne;

        if (isset($_SESSION['jtl_widget_kampagnen']['kKampagne']) &&
            $_SESSION['jtl_widget_kampagnen']['kKampagne'] > 0
        ) {
            $kKampagne = (int)$_SESSION['jtl_widget_kampagnen']['kKampagne'];
        }
        if (isset($_GET['kKampagne']) && (int)$_GET['kKampagne'] > 0) {
            $kKampagne = (int)$_GET['kKampagne'];
        }

        $_SESSION['jtl_widget_kampagnen']['kKampagne'] = $kKampagne;

        $oKampagneStat_arr = holeKampagneDetailStats($kKampagne, $oKampagneDef_arr);

        $this->oSmarty->assign('kKampagne', $kKampagne)
                      ->assign('cType_arr', array_keys($oKampagneStat_arr))
                      ->assign('oKampagne_arr', $oKampagne_arr)
                      ->assign("oKampagneDef_arr", $oKampagneDef_arr)
                      ->assign('oKampagneStat_arr', $oKampagneStat_arr);
    }

    /**
     * @return bool|mixed|string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(__DIR__ . '/widgetCampaigns.tpl');
    }
}
