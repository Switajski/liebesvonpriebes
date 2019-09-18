<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';

/**
 * Class WidgetShopinfo
 */
class WidgetShopinfo extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $nVersionFile    = Shop::getVersion();
        $nVersionDB      = getJTLVersionDB();
        $oTpl            = Template::getInstance();
        $nTplVersion     = (int)$oTpl->getShopVersion();
        $strFileVersion  = sprintf('%.2f', $nVersionFile / 100);
        $strDBVersion    = sprintf('%.2f', $nVersionDB / 100);
        $strTplVersion   = sprintf('%.2f', $nTplVersion / 100);
        $strUpdated      = date_format(date_create(getJTLVersionDB(true)), 'd.m.Y, H:i:m');
        $strMinorVersion = JTL_MINOR_VERSION;
        if ($strMinorVersion === '#JTL_MINOR_VERSION#') {
            $strMinorVersion = 'DEV';
        }

        $this->oSmarty->assign('nVersionFile', $nVersionFile);
        $this->oSmarty->assign('strFileVersion', $strFileVersion);
        $this->oSmarty->assign('strDBVersion', $strDBVersion);
        $this->oSmarty->assign('strTplVersion', $strTplVersion);
        $this->oSmarty->assign('strUpdated', $strUpdated);
        $this->oSmarty->assign('strMinorVersion', $strMinorVersion);
        $this->oSmarty->assign('JTLURL_GET_SHOPVERSION', JTLURL_GET_SHOPVERSION);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/shopinfo.tpl');
    }
}
