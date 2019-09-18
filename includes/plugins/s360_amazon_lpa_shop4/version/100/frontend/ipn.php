<?php

/*
 * This is the IPN file remotely accessed by amazon for notifications about payments.
 */
require_once(dirname(__FILE__) . "/../../../../../globalinclude.php");
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAStatusHandler.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once($oPlugin->cFrontendPfad . 'lib/PayWithAmazon/IpnHandler.php');

if (!$oPlugin || $oPlugin->kPlugin == 0 || $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_USEIPN] === '0') {
    header("HTTP/1.1 503 Service Unavailable");
    exit(0);
}

if (!function_exists('lpa_getallheaders')) {
    /*
     * Workaround function if we run on php 5.3 with fastcgi
     */

    function lpa_getallheaders() {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            } else {
                $headers[strtolower($name)] = $value;
            }
        }
        return $headers;
    }

}

if(isset($_GET['lpacheck'])) {
    $query = $_SERVER['PHP_SELF'];
    $path = pathinfo( $query );
    header( "HTTP/1.1 200 OK" );
    echo "IPN is reachable. (".$path['dirname'].")";
    exit(0);
}

$headers = lpa_getallheaders();
$body = file_get_contents('php://input');
Jtllog::writeLog("LPA: LPA-IPN: IPN empfangen:\nHeaders: ". print_r($headers, true) . "\nBody: " . print_r($body, true), JTLLOG_LEVEL_DEBUG);


try {
    $ipnHandler = new \PayWithAmazon\IpnHandler($headers, $body);
    $notification = $ipnHandler->toArray();
    Jtllog::writeLog("LPA: LPA-IPN: IPN geparst: ". print_r($notification, true), JTLLOG_LEVEL_DEBUG);
} catch (Exception $ex) {
    Jtllog::writeLog('LPA: LPA-IPN-Fehler: Invalide Nachricht empfangen: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
    header("HTTP/1.1 503 Service Unavailable");
    exit(0);
}

try {
    $handler = new LPAStatusHandler();
    $adapter = new LPAAdapter();
    $notificationType = $notification['NotificationType'];
    /*
     * Acting according to best practices: on receipt of an IPN for an object, use the respected getX-Call to get the details.
     */
    switch ($notificationType) {
        case 'OrderReferenceNotification':
            $orid = $notification['OrderReference']['AmazonOrderReferenceId'];
            Jtllog::writeLog("LPA: LPA-IPN: Notification für Order {$orid} empfangen.", JTLLOG_LEVEL_DEBUG);
            if($orid === S360_LPA_TEST_IPN_ID) {
                Jtllog::writeLog("LPA: LPA-IPN: {$orid} wurde als Test-IPN erkannt. Empfang OK.", JTLLOG_LEVEL_NOTICE);
                header( "HTTP/1.1 200 OK" );
                exit(0);
            }
            $details = $adapter->getRemoteOrderReferenceDetails($orid);
            $handler->handleOrderReferenceDetails($details);
            break;
        case 'AuthorizationNotification':
        case 'PaymentAuthorize':
            $authid = $notification['AuthorizationDetails']['AmazonAuthorizationId'];
            Jtllog::writeLog("LPA: LPA-IPN: Notification für Authorization {$authid} empfangen.", JTLLOG_LEVEL_DEBUG);
            $details = $adapter->getRemoteAuthorizationDetails($authid);
            $handler->handleAuthorizationDetails($details);
            break;
        case 'CaptureNotification':
        case 'PaymentCapture':
            $capid = $notification['CaptureDetails']['AmazonCaptureId'];
            Jtllog::writeLog("LPA: LPA-IPN: Notification für Capture {$capid} empfangen.", JTLLOG_LEVEL_DEBUG);
            $details = $adapter->getRemoteCaptureDetails($capid);
            $handler->handleCaptureDetails($details);
            break;
        case 'RefundNotification':
        case 'PaymentRefund':
            $refid = $notification['RefundDetails']['AmazonRefundId'];
            Jtllog::writeLog("LPA: LPA-IPN: Notification für Refund {$refid} empfangen.", JTLLOG_LEVEL_DEBUG);
            $details = $adapter->getRemoteRefundDetails($refid);
            $handler->handleRefundDetails($details);
            break;
        default:
            Jtllog::writeLog('LPA: LPA-IPN: Unerwarteten NotificationType empfangen: ' . $notificationType, JTLLOG_LEVEL_NOTICE);
            header("HTTP/1.1 400 Bad Request");
            exit(0);
            break;
    }
} catch (Exception $ex) {
    Jtllog::writeLog('LPA: LPA-IPN-Fehler: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
    header("HTTP/1.1 503 Service Unavailable");
    exit(0);
}