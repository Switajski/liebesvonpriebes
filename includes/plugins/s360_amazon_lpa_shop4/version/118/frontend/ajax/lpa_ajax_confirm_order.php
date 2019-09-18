<?php

/*
 * Solution 360 GmbH
 *
 * Handles order confirmation of an Amazon Order via AJAX
 */
if (!isset($_POST['lpa_ajax'])) {
    header("HTTP/1.1 400 Bad Request");
    exit(0);
}
// benötigt, um alle JTL-Funktionen zur Verfügung zu haben
require_once(__DIR__ . '/../lib/lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "smartyInclude.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "bestellabschluss_inc.php");
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.CheckBox.php");
require_once($oPlugin->cFrontendPfad . 'lib/class.LPACurrencyHelper.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAHelper.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPALinkHelper.php');

$session = Session::getInstance();

$kKundengruppe = Kundengruppe::getCurrent();

$oCheckBox = new CheckBox();
$cPlausi_arr = $oCheckBox->validateCheckBox(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $_POST, true);
$cPost_arr = $_POST;
$_SESSION['lpa_last_post_array'] = $_POST;

// die Antwort ist im JSON Format
header('Content-Type: application/json');
$reply = array();

// check checksum
$checksum = md5(serialize($_SESSION['Warenkorb']->gibGesamtsummeWaren(true) . $_SESSION['Versandart']->kVersandart));

if(!isset($_SESSION['lpa_basket_checksum']) || $_SESSION['lpa_basket_checksum'] !== $checksum) {
    $reply['error'] = array(
        'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_CHECKSUM]),
        'type' => 'Checksum'
    );
    $reply['state'] = 'error';
    echo json_encode($reply);
    exit;
}

$_SESSION['kommentar'] = substr(strip_tags(Shop::DB()->escape($_POST['kommentar'])), 0, 1000);

if (count($cPlausi_arr) == 0) {
    try {
        //  confirm order to amazon
        $confirmationSuccessful = false;

        // the amount is always in shop default currency
        $amount = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);

        $orid = StringHandler::filterXSS($_REQUEST['orid']);
        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Beginne Confirm Order für Order-Referenz: $orid", JTLLOG_LEVEL_DEBUG);
        $retryAuth = false;
        if (!empty($_REQUEST['retryAuth'])) {
            $retryAuth = true;
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Es handelt sich um einen Retry nach abgelehnter Autorisierung.", JTLLOG_LEVEL_DEBUG);
        }

        $controller = new LPAController();
        $database = new LPADatabase();
        $config = $controller->getConfig();
        $client = $controller->getClient();

        /*
         * MultiCurrency handling
         */
        $presentmentCurrency = LPACurrencyHelper::getCurrentCurrency();
        if (!LPACurrencyHelper::isSupportedCurrency($presentmentCurrency->cISO)) {
            // Error - the user seems to have changed the session currency - abort
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Abbruch: Session-Currency '{$presentmentCurrency->cISO}' ist nicht valide für Amazon Pay.", JTLLOG_LEVEL_DEBUG);
            throw new Exception("Currency '{$presentmentCurrency->cISO}' is invalid for Amazon Pay.");
        }

        // Amazon Pay needs to total amount for the order based on the presentment currency
        $currencyISO = $presentmentCurrency->cISO;
        $defaultCurrency = LPACurrencyHelper::getDefaultCurrency();
        $orderAmount = LPACurrencyHelper::convertAmount($amount, $defaultCurrency->cISO, $presentmentCurrency->cISO);

        /*
         * First set relevant data in the OrderReference object, including once more updating the total amount and including our internal order id.
         * Do not do this on a retry.
         */
        if (!$retryAuth) {
            $setOrderReferenceDetailsParameter = array(
                'merchant_id' => $config['merchant_id'], //your merchant/seller ID
                'amazon_order_reference_id' => $orid, //unique identifier for the order reference
                'amount' => $orderAmount,
                'currency_code' => $currencyISO,
                'platform_id' => S360_LPA_PLATFORM_ID
            );
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: OrderReferenceDetails werden geupdated mit: " . print_r($setOrderReferenceDetailsParameter, true), JTLLOG_LEVEL_DEBUG);
            $result = $client->setOrderReferenceDetails($setOrderReferenceDetailsParameter);
            $result = $result->toArray();
            if (isset($result['Error'])) {
                throw new Exception($result['Error']['Message']);
            }
        } else {
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: OrderReferenceDetails werden nicht nochmal geupdated da es sich um einen Retry handelt.", JTLLOG_LEVEL_DEBUG);
        }

        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Prüfe OrderReferenceDetails auf Constraints.", JTLLOG_LEVEL_DEBUG);
        // check if any constraints are present... there should not be constraints, but in exceptional cases a PaymentMethodNotAllowed constraint may result
        $getOrderReferenceDetailsParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_order_reference_id' => $orid
        );
        $result = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Constraintprüfung lieferte Ergebnis-Objekt.", JTLLOG_LEVEL_DEBUG);
        $result = $result->toArray();
        if (isset($result['Error'])) {
            throw new Exception($result['Error']['Message']);
        }
        if (isset($result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Constraints'])) {
            $result = $result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Constraints'];
        } else {
            $result = null;
        }

        if (!empty($result)) {
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Constraintprüfung hat Constraints zurückgeliefert!", JTLLOG_LEVEL_DEBUG);
            /*
             * The following Constraints exist:
             *
             * ShippingAddressNotSet    The buyer has not selected a shipping address from the Amazon AddressBook widget.	Display the Amazon AddressBook widget to the buyer to collect shipping information.
             * PaymentPlanNotSet	The buyer has not set a payment method for the given order reference.	Display the Amazon Wallet widget to the buyer to collect payment information.
             * AmountNotSet             You have not set the amount for the order reference.	Call the SetOrderReferenceDetails operation and specify the order amount in the OrderTotal request parameter.
             * PaymentMethodNotAllowed  The payment method selected by the buyer is not allowed for this order reference.	Display the Amazon Wallet widget and request the buyer to select a different payment method.
             */

            // ERROR case! there should be no constraints
            $constraint = array_shift($result);
            Jtllog::writeLog('LPA: LPA-Payment: ' . $constraint['ConstraintID'] . ' von Amazon Pay zurückgegeben.', JTLLOG_LEVEL_NOTICE);
            if ($constraint['ConstraintID'] === 'ShippingAddressNotSet') {
                $reply['error'] = array(
                    'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_SHIPPING_ADDRESS]),
                    'type' => 'ShippingAddressNotSet'
                );
            } elseif ($constraint['ConstraintID'] === 'PaymentPlanNotSet') {
                $reply['error'] = array(
                    'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_PAYMENT_METHOD]),
                    'type' => 'PaymentPlanNotSet'
                );
            } elseif ($constraint['ConstraintID'] === 'AmountNotSet') {
                Jtllog::writeLog('LPA: LPA-Payment: ' . $constraint['ConstraintID'] . ' von Amazon Pay zurückgegeben.', JTLLOG_LEVEL_ERROR);
                $reply['error'] = array(
                    'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                    'type' => 'AmountNotSet'
                );
            } elseif ($constraint['ConstraintID'] === 'PaymentMethodNotAllowed') {
                $reply['error'] = array(
                    'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_SOFT_DECLINE]),
                    'type' => 'PaymentMethodNotAllowed'
                );
            } else {
                $reply['error'] = array(
                    'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                    'type' => 'TechnicalError'
                );
            }
            $reply['state'] = 'error';
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
            echo json_encode($reply);
            return;
        }


        /*
         * Confirm order, even if this is a retry for authorization! This reopens the order from SUSPENDED to OPEN.
         */
        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: ConfirmOrder wird gegen Amazon ausgelöst.", JTLLOG_LEVEL_DEBUG);
        $confirmOrderReferenceParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_order_reference_id' => $orid,
            'success_url' => LPALinkHelper::getFrontendLinkUrl(S360_LPA_FRONTEND_LINK_PROCESS_SUCCESS),
            'failure_url' => LPALinkHelper::getFrontendLinkUrl(S360_LPA_FRONTEND_LINK_PROCESS_FAILURE)
        );
        //send API call
        $response = $client->confirmOrderReference($confirmOrderReferenceParameter);
        $response = $response->toArray();
        if (empty($response) || isset($response['Error'])) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: Confirmation für Bestellung fehlgeschlagen.' . $response['Error']['Message'], JTLLOG_LEVEL_ERROR);
            // ERROR case! there should be no constraints
            $reply['error'] = array(
                'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                'type' => 'Technical'
            );
            $reply['state'] = 'error';
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Fehler beim ConfirmOrder - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
            echo json_encode($reply);
            return;
        }

        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: ConfirmOrder erfolgreich oder nicht notwendig gewesen, weil es sich um RetryAuth handelte.", JTLLOG_LEVEL_DEBUG);

        /**
         * PSD-2:
         *
         * We now return with success (or failure) and let JS handle the further steps.
         * Amazon will redirect the user to the successUrl (or failureUrl) where we will continue processing the order.
         *
         * Processing the order means:
         * - We try to authorize the order (if authorization is not set to manual) because the user might, after all, have called the URL maliciously
         * - We do a capture if applicable.
         */

        /**
         * All checks and order confirmation so far were successful.
         * We return control to the JS Script that called us to enable Amazon Pay to initiate additional confirmation flow operations.
         */
        $reply['snippet'] = '';
        $reply['state'] = 'success';
        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Vorgang abgeschlossen. Liefere Ergebnis an das aufrufende Skript zurück: " . print_r($reply, true), JTLLOG_LEVEL_DEBUG);
        echo json_encode($reply);
        exit;

    } catch (Exception $ex) {
        $cFehler = $ex->getMessage();
        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Technischer Fehler beim Bestellabschluss: ' . $cFehler, JTLLOG_LEVEL_ERROR);
        $reply['error'] = array(
            'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
            'type' => 'Technical'
        );
        $reply['state'] = 'error';
        echo json_encode($reply);
        exit;
    }
} else {
    $reply['error'] = array(
        'message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_CHECKBOXES]),
        'type' => 'Plausi',
        'plausi' => $cPlausi_arr
    );
    $reply['state'] = 'error';
    echo json_encode($reply);
    exit;
}
