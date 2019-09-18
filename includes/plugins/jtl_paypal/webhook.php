<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__.'/../../globalinclude.php';
require_once PFAD_ROOT.PFAD_INCLUDES_MODULES.'PaymentMethod.class.php';

$oPlugin = Plugin::getPluginById('jtl_paypal');
$cPluginClassFile = $oPlugin->cPluginPfad.PFAD_PLUGIN_PAYMENTMETHOD.'class/PayPalPlus.class.php';

require_once $cPluginClassFile;

use PayPal\Api\Webhook;
use PayPal\Api\WebhookEvent;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Validation\JsonValidator;

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

function _validateAndGetReceivedEvent($body, $apiContext = null, $restCall = null)
{
    if ($body == null | empty($body)) {
        throw new \InvalidArgumentException('Body cannot be null or empty');
    }

    if (!JsonValidator::validate($body, true)) {
        throw new \InvalidArgumentException('Request Body is not a valid JSON.');
    }

    $object = new WebhookEvent($body);
    if ($object->getId() == null) {
        throw new \InvalidArgumentException('Id attribute not found in JSON. Possible reason could be invalid JSON Object');
    }

    $headers = array(
        'auth_algo' => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'],
        'cert_url' => $_SERVER['HTTP_PAYPAL_CERT_URL'],
        'transmission_id' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'],
        'transmission_sig' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'],
        'transmission_time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'],
    );

    $verify = new VerifyWebhookSignature();
    $verify->fromArray($headers);
    $verify->setWebhookEvent($object);

    $webhookList = Webhook::getAll($apiContext);
    if (count($webhookList->getWebhooks()) > 0) {
        $webhook = $webhookList->getWebhooks()[0];
        $verify->setWebhookId($webhook->getId());
    }

    $response = $verify->post($apiContext);

    if (($status = $response->getVerificationStatus()) !== 'SUCCESS') {
        throw new \UnexpectedValueException("Invalid status '{$status}'");
    }

    return $object;
}

/////////////////////////////////////////////////////////////////////////

try {
    $api = new PayPalPlus();
    $context = $api->getContext();

    $bodyReceived = file_get_contents('php://input');
    if (empty($bodyReceived)) {
        _exit(500, 'Body cannot be null or empty');
    }

    $event = _validateAndGetReceivedEvent($bodyReceived, $context);

    $resource = $event->getResource();
    $type = $event->getResourceType();

    //$api->logResult('Webhook', $event);
    $api->doLog("Webhook: {$event->getSummary()}", LOGLEVEL_NOTICE);

    if ($type == 'sale' && $resource->state == 'completed') {
        $paymentId = $resource->parent_payment;
        $order = Shop::DB()->select('tbestellung', 'cSession', $paymentId);
        if (is_object($order) && intval($order->kBestellung) > 0) {
            $incomingPayment = Shop::DB()->select(
                'tzahlungseingang',
                'kBestellung', $order->kBestellung,
                'cHinweis', $resource->id
            );
            if (is_object($incomingPayment) && intval($incomingPayment->kZahlungseingang) > 0) {
                $api->doLog("Incoming payment '{$resource->id}' already exists", LOGLEVEL_NOTICE);
            } else {
                $amount = $resource->amount;
                $incomingPayment = (object) [
                    'cISO' => $amount->currency,
                    'cHinweis' => $resource->id,
                    'fBetrag' => floatval($amount->total),
                    'dZeit' => date('Y-m-d H:i:s', strtotime($resource->create_time)),
                    'fZahlungsgebuehr' => floatval($amount->details->handling_fee),
                ];
                $api->addIncomingPayment($order, $incomingPayment);
                $api->sendConfirmationMail($order);
                $api->doLog("Incoming payment '{$resource->id}' added", LOGLEVEL_NOTICE);
            }
        } else {
            $api->doLog("Order '{$paymentId}' not found", LOGLEVEL_ERROR);
        }
        _exit(200);
    }
} catch (Exception $ex) {
    $api->handleException('Webhook', $bodyReceived, $ex);
}
