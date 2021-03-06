<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalFinance.class.php';

use PayPal\Api\Payment;
use PayPal\Api\WebhookEvent;
use PayPal\Api\Order;
use PayPal\Api\Capture;
use PayPal\Api\Authorization;
use PayPal\Api\Amount;

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

$api        = new PayPalFinance();
$apiContext = $api->getContext();
$action     = isset($_GET['a']) ? $_GET['a'] : '';

switch ($action) {
    case 'return':
    {
        $success = isset($_GET['r']) && $_GET['r'] === 'true';

        if ($success) {
            $paymentId = isset($_GET['paymentId']) ? $_GET['paymentId'] : null;
            $token     = isset($_GET['token']) ? $_GET['token'] : null;
            $payerId   = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;

            if (!$api->isSelected() || empty($paymentId) || empty($token) || empty($payerId)) {
                _redirect('bestellvorgang.php?editZahlungsart=1');
            }

            $api->addCache('paymentId', $paymentId)
                ->addCache('token', $token)
                ->addCache('payerId', $payerId);

            try {
                $payment = Payment::get($paymentId, $apiContext);

                $api->updateAddress($payment, $_SESSION['Kunde'], $_SESSION['Lieferadresse']);
                $api->logResult('GetPayment', $paymentId, $payment);

                if ($offer = $payment->getCreditFinancingOffered()) {
                    $api->addSurcharge($offer);
                    _redirect('bestellvorgang.php');
                }
            } catch (Exception $ex) {
                $api->handleException('GetPayment', $paymentId, $ex);
            }
        }

        _redirect('bestellvorgang.php?editZahlungsart=1');
    }

    case 'webhook':
    {
        try {
            $bodyReceived = file_get_contents('php://input');
            if (empty($bodyReceived)) {
                _exit(500, 'Body cannot be null or empty');
            }
            $event = WebhookEvent::validateAndGetReceivedEvent($bodyReceived, $apiContext);
            $api->logResult('Webhook', $event);
            _exit(200);
        } catch (Exception $ex) {
            $api->handleException('Webhook', $bodyReceived, $ex);
        }
        break;
    }
}

_exit(400);
