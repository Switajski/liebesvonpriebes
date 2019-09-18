<?php
/**
 * HOOK_SMARTY_OUTPUTFILTER
 *
 * hook for rendering output
 *
 * @package     jtl_debug
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */

if (!isset($_GET['jtl-debug-session'])) {
    $jtlDebug = jtl_debug::getInstance($oPlugin);
    if ($jtlDebug->getIsActivated() === true) {
        $jtlDebug->run();
    }
}
