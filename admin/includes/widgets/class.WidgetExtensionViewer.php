<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';

/**
 * Class WidgetExtensionViewer
 */
class WidgetExtensionViewer extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $oNice      = Nice::getInstance();
        $oModul_arr = $oNice->gibAlleMoeglichenModule();
        foreach ($oModul_arr as &$oModul) {
            $oModul->bActive = $oNice->checkErweiterung($oModul->kModulId);
        }
        $this->oSmarty->assign('oModul_arr', $oModul_arr);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/extension_viewer.tpl');
    }
}
