<?php

require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalPlus.class.php';
$api = new PayPalPlus();

use PayPal\Api\Payment;
use PayPal\Api\WebhookEvent;

/////////////////////////////////////////////////////////////////////////

if (!isset($_GET['a'])) {
    exit;
}

$apiContext = $api->getContext();
$action     = isset($_GET['a']) ? $_GET['a'] : '';

switch ($action) {
    case 'payment_method':
    {
        if (!isset($_GET['id'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit;
        }

        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

        pruefeZahlungsartwahlStep(array(
            'Zahlungsart'     => $_GET['id'],
            'zahlungsartwahl' => 1,
        ));

        header('location: bestellvorgang.php');

        break;
    }

    case 'payment_patch':
    {
        if (!isset($_GET['id'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit;
        }

        $address = json_decode(utf8_encode(sprintf('{
            "recipient_name": "%s %s",
            "line1": "%s %s",
            "city": "%s",
            "postal_code": "%s",
            "country_code": "%s"
        }', $_SESSION['Lieferadresse']->cVorname, $_SESSION['Lieferadresse']->cNachname,
            $_SESSION['Lieferadresse']->cStrasse, $_SESSION['Lieferadresse']->cHausnummer,
            $_SESSION['Lieferadresse']->cOrt,
            $_SESSION['Lieferadresse']->cPLZ,
            $_SESSION['Lieferadresse']->cLand)));

        try {
            $payment = Payment::get($_GET['id'], $apiContext);

            $patchShipping = new \PayPal\Api\Patch();
            $patchShipping->setOp('add')
                ->setPath('/transactions/0/item_list/shipping_address')
                ->setValue($address);

            $patchRequest = new \PayPal\Api\PatchRequest();
            $patchRequest->setPatches(array($patchShipping));

            $payment->update($patchRequest, $apiContext);

            $api->logResult('Patch', $patchRequest, $payment);
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            exit;
        } catch (Exception $ex) {
            $api->handleException('Patch', $patchRequest, $ex);

            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            exit;
        }
    }

    case 'return':
    {
        $success = isset($_GET['r']) && $_GET['r'] === 'true';

        if (!$success) {
            header('location: bestellvorgang.php?editZahlungsart=1');
            exit;
        }

        $paymentId = $_GET['paymentId'];
        $token     = $_GET['token'];
        $payerId   = $_GET['PayerID'];

        $api->addCache('paymentId', $paymentId)
            ->addCache('token', $token)
            ->addCache('payerId', $payerId);

        try {
            $payment = Payment::get($paymentId, $apiContext);
            $api->createPaymentSession();

            $api->logResult('GetPayment', $paymentId, $payment);
            header('location: bestellvorgang.php');
            exit;
        } catch (Exception $ex) {
            $api->handleException('GetPayment', $paymentId, $ex);

            header('location: bestellvorgang.php?editZahlungsart=1');
            exit;
        }
    }

    case 'webhook':
    {
        try {
            $bodyReceived = file_get_contents('php://input');
            $event        = WebhookEvent::validateAndGetReceivedEvent($bodyReceived, $apiContext);

            $resource = $event->getResource();
            if ($resource->getState() == 'completed') {
                $amount = $event->getAmount();

                $oIncomingPayment          = new stdClass();
                $oIncomingPayment->fBetrag = $amount->getTotal();
                $oIncomingPayment->cISO    = $amount->getCurrency();
                $this->addIncomingPayment('', $oIncomingPayment);
            }
            $api->logResult('GetPayment', null, $event);
        } catch (Exception $ex) {
            $api->handleException('Webhook', null, $ex);
        }
        break;
    }
}

exit;
