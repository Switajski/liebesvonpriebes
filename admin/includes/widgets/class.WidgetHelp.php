<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetHelp
 */
class WidgetHelp extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('JTLURL_GET_SHOPHELP', JTLURL_GET_SHOPHELP);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/help.tpl');
    }
}
