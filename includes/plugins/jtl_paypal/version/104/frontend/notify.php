<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

$path         = str_replace('\\', '/', dirname(__FILE__));
$basePath     = strstr($path, '/includes/plugins/', true);
$bootstrapper = $basePath . '/includes/globalinclude.php';

$exit = function ($error = false) {
    ob_end_clean();
    http_response_code(($error !== true) ? 200 : 503);
    exit;
};

if (!file_exists($bootstrapper)) {
    $exit(true);
}

require_once $bootstrapper;
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

Jtllog::writeLog("PayPal Notify: Received an IPN from {$_SERVER['REMOTE_ADDR']}", JTLLOG_LEVEL_DEBUG, false);

$oPlugin = Plugin::getPluginById('jtl_paypal');

if ($oPlugin === null) {
    Jtllog::writeLog("PayPal Notify: Plugin 'jtl_paypal' not found", false);
    $exit(true);
}

$payment = null;

switch (@$_GET['type']) {
    case 'basic':
        require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalBasic.class.php';
        $payment = new PayPalBasic();
        break;

    case 'express':
        require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalExpress.class.php';
        $payment = new PayPalExpress();
        break;
}

if ($payment === null) {
    Jtllog::writeLog('PayPal Notify: Missing payment provider', false);
    $exit(true);
}

$mode   = ucfirst($payment->getMode());
$notify = $payment->handleNotify();
$result = $notify->getRawData();

$r = print_r($result, true);
$payment->doLog("PayPal Notify: GetRawData:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

if ($payment->isLive() === true && $notify->validate() === false) {
    $payment->doLog('PayPal Notify: Validation failed', LOGLEVEL_ERROR);
    $exit(true);
}

$paymentStatus = $result['payment_status'];
$txId          = $notify->getTransactionId();
$invoice       = $result['invoice'];

$payment->doLog("PayPal Notify: ({$mode}) Received new status '{$paymentStatus}' for transaction '{$txId}'", LOGLEVEL_NOTICE);

if (strcasecmp($paymentStatus, 'Completed') !== 0) {
    $exit();
}

$orderId = $result['custom'];
$order   = new Bestellung((int) $orderId);
$order->fuelleBestellung(0, 0, false);

if ((int) $order->kBestellung === 0) {
    $payment->doLog("PayPal Notify: Order id '{$orderId}' not found", LOGLEVEL_ERROR);
    $exit(503);
}

// validation

// $order->Waehrung->cISO               $result['mc_currency']
// $order->fGesamtsummeKundenwaehrung   $result['mc_gross']
// $order->cStatus                      BESTELLUNG_STATUS_OFFEN || BESTELLUNG_STATUS_IN_BEARBEITUNG

if (!in_array((int) $order->cStatus, [BESTELLUNG_STATUS_OFFEN, BESTELLUNG_STATUS_IN_BEARBEITUNG])) {
    $payment->doLog("PayPal Notify: Order status '{$order->cStatus}' did not match the requirements", LOGLEVEL_ERROR);
    $exit(503);
}

$payment->addIncomingPayment($order, [
    'fBetrag'          => $result['mc_gross'],
    'fZahlungsgebuehr' => $result['mc_fee'],
    'cISO'             => $result['mc_currency'],
    'cEmpfaenger'      => $result['receiver_email'],
    'cZahler'          => $result['payer_email'],
    'cHinweis'         => $notify->getTransactionId(),
]);

$diffAmount = abs(floatval($order->fGesamtsummeKundenwaehrung) - floatval($result['mc_gross']));

if ($diffAmount <= 1) {
    $payment->setOrderStatusToPaid($order);
    $payment->doLog("PayPal Notify: Order status changed to 'paid'", LOGLEVEL_NOTICE);
} else {
    $payment->doLog('PayPal Notify: Order payment has been received', LOGLEVEL_NOTICE);
}

$exit();
