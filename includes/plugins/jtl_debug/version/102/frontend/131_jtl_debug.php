<?php
/**
 * HOOK_GLOBALINCLUDE_INC
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
    $jtlDebug->makeLast()
             ->initUserDebugger()
             ->setErrorHandler();
}
