<?php
/**
 * HOOK_SMARTY_INC
 *
 * @package     jtl_debug
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 *
 * @global Plugin $oPlugin
 */

if (!isset($_GET['jtl-debug-session'])) {
    require_once $oPlugin->cFrontendPfad . 'inc/class.jtl_debug.php';
    $jtlDebug = jtl_debug::getInstance($oPlugin);
    if ($jtlDebug->getIsActivated() === true) {
        $smarty = Shop::Smarty();
        //enable smarty debugging
        $smarty->setDebugging(true);
        //set debug template to empty file to avoid the default popup (our own logic is in hook 140)
        $smarty->setDebugTemplate($oPlugin->cFrontendPfad . 'template/empty.tpl');
    }
}
