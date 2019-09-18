<?php
/**
 * HOOK_SMARTY_INC
 *
 * @package     jtl_debug
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */

if (!isset($_GET['jtl-debug-session'])) {
    require_once $oPlugin->cFrontendPfad . 'inc/class.jtl_debug.php';
    $jtlDebug = jtl_debug::getInstance($oPlugin);
    if ($jtlDebug->getIsActivated() === true) {
        global $smarty;
        //enable smarty debugging
        $smarty->debugging = true;
        //set debug template to empty file to avoid the default popup (our own logic is in hook 140)
        $smarty->debug_tpl = $oPlugin->cFrontendPfad . 'template/empty.tpl';
    }
}
