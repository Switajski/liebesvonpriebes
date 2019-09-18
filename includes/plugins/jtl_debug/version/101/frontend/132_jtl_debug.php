<?php
/**
 * HOOK_INDEX_NAVI_HEAD_POSTGET
 *
 * @package     jtl_debug
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 *
 * @global Plugin $oPlugin
 */

if (isset($_GET['jtl-debug-session'])) {
    require $oPlugin->cFrontendPfad . 'inc/class.jtl_debug.php';
    jtl_debug::getOutputAjax();
}
