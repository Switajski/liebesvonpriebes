<?php

require_once __DIR__ . '/lib/lpa_includes.php';
require_once __DIR__ . '/lib/class.LPALinkHelper.php';
$lpaPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');

$errorCode = $_REQUEST['ErrorCode'];
if(empty($errorCode)) {
    $errorCode = $_REQUEST['AuthenticationStatus'];
}

$cPost_arr = $_SESSION['lpa_last_post_array'];
unset($_SESSION['lpa_last_post_array']);
$orid = $cPost_arr['orid'];

if(empty($errorCode)) {
    $message = 'Error code is missing or invalid';
} elseif($errorCode === 'InvalidSellerId') {
    $message = 'Seller Id is invalid';
} elseif($errorCode === 'InvalidIdStatus') {
    $message = 'Order Reference Id Status is invalid';
} elseif($errorCode === 'InternalServerError') {
    $message = 'Internal Server Error';
} elseif($errorCode === 'Abandoned') {
    $message = 'Authentication Status Abandoned';
}

Jtllog::writeLog('LPA: PROCESS FAILURE: ' . $message, JTLLOG_LEVEL_DEBUG);
// set information for the following page, if the error code is empty, we let the user try again (this might be "abandoned"), else we do not allow another try
$_SESSION['lpa_processing_error'] = array(
    'tryagain' => ($errorCode === 'Abandoned'),
    'message' => ($errorCode === 'Abandoned') ? $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_try_again'] : $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_do_not_try_again'],
    'code' => ($errorCode === 'Abandoned') ? S360_LPA_EXCEPTION_CODE_SOFT_DECLINE : S360_LPA_EXCEPTION_CODE_GENERIC,
    'orid' => $orid
);
header('Location: ' . LPALinkHelper::getFrontendLinkUrl(S360_LPA_FRONTEND_LINK_CHECKOUT));
exit();