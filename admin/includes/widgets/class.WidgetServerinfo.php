<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetServerinfo
 */
class WidgetServerinfo extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $cUrl = parse_url(Shop::getURL());
        $this->oSmarty->assign('phpOS', PHP_OS)
                      ->assign('phpVersion', StringHandler::htmlentities(PHP_VERSION))
                      ->assign('phpLT55', version_compare(PHP_VERSION, '5.5') < 0)
                      ->assign('serverAddress', StringHandler::htmlentities($_SERVER['SERVER_ADDR']))
                      ->assign('serverHTTPHost', StringHandler::htmlentities($_SERVER['HTTP_HOST']))
                      ->assign('mySQLVersion', StringHandler::htmlentities(Shop::DB()->info()))
                      ->assign('mySQLStats', StringHandler::htmlentities(Shop::DB()->stats()))
                      ->assign('cShopHost', $cUrl['scheme'] . '://' . $cUrl['host']);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/serverinfo.tpl');
    }
}
