<?php
// HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG

require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalPlus.class.php';

if (isset($_GET['refresh'])) {
    header('location: bestellvorgang.php');
    exit;
}

$Zahlungsart_arr  = array();
$oZahlungsart_arr = $smarty->get_template_vars('Zahlungsarten');
if (count($oZahlungsart_arr) > 0) {
    foreach ($oZahlungsart_arr as $key => $oZahlungsart) {
        if (!isset($oZahlungsart->cModulId) || strpos($oZahlungsart->cModulId, 'paypalexpress') === false) {
            $Zahlungsart_arr[] = $oZahlungsart;
        }
    }
    Shop::Smarty()->assign('Zahlungsarten', $Zahlungsart_arr);
}

$api   = new PayPalPlus();
$items = PayPalHelper::getProducts();

if ($api->isConfigured(false) && $api->isUseable($items)) {
    $payment = $api->createPayment();

    if ($payment !== null) {
        $approvalUrl = $payment->getApprovalLink();

        $availablePayments = Shop::DB()->query('SELECT * FROM xplugin_jtl_paypal_additional_payment', 2);
        $defaultPayments   = Shop::Smarty()->get_template_vars('Zahlungsarten');

        $sortedPayments = array();

        foreach ($availablePayments as $p) {
            foreach ($defaultPayments as $d) {
                if (intval($p->paymentId) == intval($d->kZahlungsart)) {
                    $sortedPayments[] = $d;
                    break;
                }
            }
        }

        $language = StringHandler::convertISO2ISO639(Shop::$cISO);
        $language = sprintf('%s_%s', strtolower($language), strtoupper($language));
        $country  = $_SESSION['cLieferlandISO'];

        $link = PayPalHelper::getLinkByName($oPlugin, 'PayPalPLUS');

        Shop::Smarty()->assign('language', $language)
            ->assign('country', $country)
            ->assign('payPalPlus', true)
            ->assign('mode', $api->getModus())
            ->assign('approvalUrl', $approvalUrl)
            ->assign('paymentId', $payment->getId())
            ->assign('defaultPayments', $sortedPayments)
            ->assign('linkId', $link->kLink);
    }
}
