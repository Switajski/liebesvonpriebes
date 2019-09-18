<?php
/**
 * HOOK_GLOBALINCLUDE_INC
 *
 * @package     jtl_debug
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */

if (!isset($_GET['jtl-debug-session'])) {
    require_once $oPlugin->cFrontendPfad . 'inc/class.jtl_debug.php';
    $jtlDebug = new jtl_debug($oPlugin);

    if ($jtlDebug->getIsActivated() === true) {
        $jtlDebug->makeLast()
                 ->initUserDebugger()
                 ->setErrorHandler();
    }
}
