<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * HOOK_LASTJOBS_HOLEJOBS.
 *
 * capture installments
 */

if (!isset($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_capture']) || $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_capture'] != 'W') {
    return;
}

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalFinance.class.php';

$payPalFinance = new PayPalFinance();

if (!$payPalFinance->isConfigured(false)) {
    return;
}

$orders = Shop::DB()->executeQueryPrepared(
    "SELECT kBestellung as id FROM tbestellung WHERE kZahlungsart = :paymentId AND cStatus IN(:open, :pending) ORDER BY dErstellt DESC LIMIT :limit",
    [
        'paymentId' => $payPalFinance->paymentId,
        'open' => BESTELLUNG_STATUS_OFFEN,
        'pending' => BESTELLUNG_STATUS_IN_BEARBEITUNG,
        'limit' => 10
    ],
    2
);

if (!$orders) {
    return;
}

array_walk($orders, function(&$value) {
    $value = (int)$value->id;
});

$max_execution_time = 60;
$execution_time = (int)ini_get('max_execution_time');
$execution_time = ($execution_time < 1)
    ? $max_execution_time
    : min($max_execution_time, $execution_time);

$start_time = time();
foreach ($orders as $id) {
    try {
        $capture = $payPalFinance->capture($id);
    }
    catch (Exception $e) { }
    if ((time() - $start_time) >= $execution_time) {
        break;
    }
}