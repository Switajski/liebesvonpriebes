<?php

require_once __DIR__ . '/lib/lpa_defines.php';

try {
    if(isset($_SESSION['lpa_basket_error_message'])) {
        $MsgWarning = Shop::Smarty()->getConfigVars('MsgWarning');
        if(empty($MsgWarning)) {
            $MsgWarning = $_SESSION['lpa_basket_error_message'];
        } else {
            $MsgWarning .= ' ' . $_SESSION['lpa_basket_error_message'];
        }
        Shop::Smarty()->assign('MsgWarning', $MsgWarning);
        Shop::Smarty()->assign('lpa_force_logout', true);
        unset($_SESSION['lpa_basket_error_message']);
    }
} catch (Exception $ex) {
    Jtllog::writeLog('LPA: Hook 52: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
}