<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalBasic.class.php';

$paypal = new PayPalBasic();
$type   = isset($_GET['t']) ? $_GET['t'] : null;

switch ($type) {
    default:
        d('ERROR');
        break;

    case 's': {
        $type   = isset($_GET['t']) ? $_GET['t'] : null;
        $return = isset($_GET['r']) && (int)$_GET['r'] > 0;

        if ($return === true) {
            $token   = isset($_GET['token']) ? $_GET['token'] : null;
            $payerID = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;

            $result = $paypal->getExpressCheckoutDetails($token);

            $_SESSION['Zahlungsart'] = $paypal->zahlungsartsession();
            PayPalHelper::addSurcharge();

            header('Location: bestellvorgang.php');
        } else {
            header('Location: bestellvorgang.php?editZahlungsart=1');
        }

        break;
    }
}
