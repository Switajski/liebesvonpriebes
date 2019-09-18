<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetPatch
 */
class WidgetPatch extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('version', getJTLVersion());
        $this->oSmarty->assign('JTLURL_GET_SHOPPATCH', JTLURL_GET_SHOPPATCH);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/patch.tpl');
    }
}
