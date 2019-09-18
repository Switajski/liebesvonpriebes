<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalPlus.class.php';
require_once PFAD_ROOT.PFAD_INCLUDES.'bestellabschluss_inc.php';

use PayPal\Api\Error;
use PayPal\Api\Payment;
use PayPal\Api\WebhookEvent;

/////////////////////////////////////////////////////////////////////////

function _exit($code = 500, $content = null)
{
    $headers = [
        200 => 'OK',
        400 => 'Bad Request',
        500 => 'Internal Server Error',
    ];
    if (!array_key_exists($code, $headers)) {
        $code = 500;
    }
    header(sprintf('%s %d %s', $_SERVER['SERVER_PROTOCOL'], $code, $headers[$code]));
    if (is_string($content)) {
        ob_end_clean();
        echo $content;
    }
    exit;
}

function _redirect($to)
{
    header(sprintf('location: %s', $to));
    exit;
}

/////////////////////////////////////////////////////////////////////////

if (!isset($_GET['a'])) {
    _exit(400);
}

$api        = new PayPalPlus();
$apiContext = $api->getContext();
$action     = isset($_GET['a']) ? $_GET['a'] : '';

$verifyTrustedShops = function() {
    if (isset($_GET['ts'])) {
        $conf = Shop::getSettings([CONF_TRUSTEDSHOPS]);
        if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y') {
            PayPalHelper::addTrustedShops(StringHandler::filterXSS($_GET['ts']));
        }
    }
};

switch ($action) {
    case 'payment_method':
    {
        if (!isset($_GET['id'])) {
            _exit(400);
        }

        Shop::setPageType(PAGE_BESTELLVORGANG);

        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

        $formData = [
            'Zahlungsart'     => $_GET['id'],
            'zahlungsartwahl' => 1,
        ];

        $verifyTrustedShops();

        if (($res = pruefeZahlungsartwahlStep($formData)) === 1) {
            gibStepZahlungZusatzschritt($formData);

            require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

            $step = 'ZahlungZusatzschritt';

            $settings = Shop::Smarty()->getTemplateVars('Einstellungen');
            $settings = array_merge($settings, Shop::getSettings([CONF_ZAHLUNGSARTEN]));

            Shop::Smarty()
                ->assign('step', $step)
                ->assign('Einstellungen', $settings)
                ->assign('bestellschritt', gibBestellschritt($step))
                ->display('checkout/index.tpl');

            return;
        } else {
            _redirect('bestellvorgang.php');
        }
        break;
    }

    case 'payment_patch':
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'trustedshops_inc.php';

        if (!isset($_GET['id'])) {
            _exit(400);
        }

        $maps = [
            'city'  => 'city',
            'state' => 'state',
            'zip'   => 'plz',
            'line1' => 'street',
        ];

        $data = [
            'city'  => 'city',
            'state' => 'state',
            'zip'   => 'postal_code',
            'line1' => 'line1',
        ];

        $verifyTrustedShops();

        PayPalHelper::addSurcharge($api->getPaymentId());

        $basket       = PayPalHelper::getBasket();
        $patchRequest = new \PayPal\Api\PatchRequest();

        $shippingAddress = $api->prepareShippingAddress($_SESSION['Lieferadresse']);
        $billingAddress  = $api->prepareBillingAddress($_SESSION['Kunde']);
        $amount          = $api->prepareAmount($basket);

        try {
            $payment      = Payment::get($_GET['id'], $apiContext);
            $patchRequest = new \PayPal\Api\PatchRequest();

            $orderNumber = baueBestellnummer();
            $api->addCache('orderNumber', $orderNumber);
/*
            $patchOrderNumber = new \PayPal\Api\Patch();
            $patchOrderNumber->setOp('add')
                ->setPath('(/transactions/0/invoice_number')
                ->setValue($orderNumber);

            $patchRequest->addPatch($patchOrderNumber);
*/
            $patchBilling = new \PayPal\Api\Patch();
            $patchBilling->setOp('add')
                ->setPath('/potential_payer_info/billing_address')
                ->setValue($billingAddress);

            $patchRequest->addPatch($patchBilling);

            $patchShipping = new \PayPal\Api\Patch();
            $patchShipping->setOp('add')
                ->setPath('/transactions/0/item_list/shipping_address')
                ->setValue($shippingAddress);

            $patchRequest->addPatch($patchShipping);

            if ((float) $payment->getTransactions()[0]->getAmount()->getTotal() != (float) $amount->getTotal()) {
                $patchAmount = new \PayPal\Api\Patch();
                $patchAmount->setOp('replace')
                    ->setPath('/transactions/0/amount')
                    ->setValue($amount);
                $patchRequest->addPatch($patchAmount);
            }

            $payment->update($patchRequest, $apiContext);
            $api->logResult('Patch', $patchRequest, $payment);

            _exit(200);
        } catch (Exception $ex) {
            $api->handleException('Patch', $patchRequest, $ex);

            try {
                $error = new Error($ex->getData());

                if ($error->getName() !== 'VALIDATION_ERROR') {
                    throw $ex;
                }

                $address = $shippingAddress->toArray();

                foreach ($error->getDetails() as $detail) {
                    $field = end(explode('.', $detail->getField()));
                    if (!in_array($field, array_keys($maps))) {
                        throw $ex;
                    }
                    $detail->setField($maps[$field]);
                    $detail->setIssue($address[$data[$field]]);
                }

                Shop::Smarty()
                    ->assign('error', $error);
            } catch (Exception $ex) {
            }

            $tplData = Shop::Smarty()
                ->assign('l10n', $oPlugin->oPluginSprachvariableAssoc_arr)
                ->assign('sameAsBilling', $_SESSION['Bestellung']->kLieferadresse == 0)
                ->fetch($oPlugin->cFrontendPfad . 'template/paypalplus-patch.tpl');

            _exit(500, $tplData);
        }
    }

    case 'return':
    {
        $success = isset($_GET['r']) && $_GET['r'] === 'true';

        if (!$success) {
            $api->doLog("PayPal redirect (user canceled)", LOGLEVEL_NOTICE);

            _redirect('bestellvorgang.php?editZahlungsart=1');
        }

        $args = [
            'paymentId' => $_GET['paymentId'],
            'token' => $_GET['token'],
            'payerId' => $_GET['PayerID']
        ];

        $validArgs = array_filter($args);

        if (count($args) !== count($validArgs)) {
            $api->logResult("PayPal redirect (missing arguments)", (object)$args);

            _redirect('bestellvorgang.php?editZahlungsart=1');
        }

        foreach ($validArgs as $k => $v) {
            $api->addCache($k, $v);
        }

        try {
            $payment = Payment::get($validArgs['paymentId'], $apiContext);

            $api->addCache('payment', $payment->toJson());
            $api->createPaymentSession();

            _redirect('bestellvorgang.php');
        } catch (Exception $ex) {
            $api->handleException('GetPayment', $validArgs['paymentId'], $ex);

            _redirect('bestellvorgang.php?editZahlungsart=1');
        }
    }

    case 'webhook':
    {
        try {
            $bodyReceived = file_get_contents('php://input');
            if (empty($bodyReceived)) {
                _exit(500, 'Body cannot be null or empty');
            }
            $event    = WebhookEvent::validateAndGetReceivedEvent($bodyReceived, $apiContext);
            $resource = $event->getResource();
            $type     = $event->getResourceType();
            $api->logResult('Webhook', $event);
            if ($type == 'sale' && $resource->state == 'completed') {
                $paymentId = $resource->parent_payment;
                $order     = Shop::DB()->select('tbestellung', 'cSession', $paymentId);
                if (is_object($order) && intval($order->kBestellung) > 0) {
                    $api->doLog("Incoming payment for order (id: {$order->kBestellung} / paypal id: {$resource->id}) already received (skipped)", LOGLEVEL_NOTICE);

                    $incomingPayment = Shop::DB()->select(
                        'tzahlungseingang',
                        'kBestellung', $order->kBestellung,
                        'cHinweis', $resource->id
                    );
                    if (is_object($incomingPayment) && intval($incomingPayment->kZahlungseingang) > 0) {
                        $api->doLog("Incoming payment for order (id: {$order->kBestellung} / paypal id: {$resource->id}) already received (skipped)", LOGLEVEL_NOTICE);
                    } else {
                        $amount          = $resource->amount;
                        $incomingPayment = (object) [
                            'cISO'             => $amount->currency,
                            'cHinweis'         => $resource->id,
                            'fBetrag'          => floatval($amount->total),
                            'dZeit'            => date('Y-m-d H:i:s', strtotime($resource->create_time)),
                            'fZahlungsgebuehr' => floatval($amount->details->handling_fee),
                        ];
                        $api->addIncomingPayment($order, $incomingPayment);
                        $api->sendConfirmationMail($order);
                        $api->doLog("Incoming payment for order (id: {$order->kBestellung} / paypal id: {$resource->id}) added", LOGLEVEL_NOTICE);
                    }
                } else {
                    $api->doLog("No order found with paymentId '{$paymentId}'", LOGLEVEL_ERROR);
                }
                _exit(200);
            }
        } catch (Exception $ex) {
            $api->handleException('Webhook', $bodyReceived, $ex);
        }
        break;
    }
}

_exit(400);
