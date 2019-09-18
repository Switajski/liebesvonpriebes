<?php

/*
 * Solution 360 GmbH
 *
 * Handles order confirmation of an Amazon Order via AJAX
 */
if (!isset($_REQUEST['lpa_ajax'])) {
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

$session = Session::getInstance();

$kKundengruppe = Kundengruppe::getCurrent();

$oCheckBox = new CheckBox();
$cPlausi_arr = $oCheckBox->validateCheckBox(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $_POST, true);
$cPost_arr = $_POST;


// die Antwort ist im JSON Format
header('Content-Type: application/json');
$reply = array();

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
        $authType = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTHMODE];
        $captureMode = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE];
        $stateOnAuth = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTHSTATE];

        $controller = new LPAController();
        $database = new LPADatabase();
        $config = $controller->getConfig();
        $client = $controller->getClient();


        /*
         * MultiCurrency handling
         */
        $presentmentCurrency = LPACurrencyHelper::getCurrentCurrency();
        if(!LPACurrencyHelper::isSupportedCurrency($presentmentCurrency->cISO)) {
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
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_SHIPPING_ADDRESS]),
                    'type' => 'ShippingAddressNotSet');
            } elseif ($constraint['ConstraintID'] === 'PaymentPlanNotSet') {
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_PAYMENT_METHOD]),
                    'type' => 'PaymentPlanNotSet');
            } elseif ($constraint['ConstraintID'] === 'AmountNotSet') {
                Jtllog::writeLog('LPA: LPA-Payment: ' . $constraint['ConstraintID'] . ' von Amazon Pay zurückgegeben.', JTLLOG_LEVEL_ERROR);
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                    'type' => 'AmountNotSet');
            } elseif ($constraint['ConstraintID'] === 'PaymentMethodNotAllowed') {
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_SOFT_DECLINE]),
                    'type' => 'PaymentMethodNotAllowed');
            } else {
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                    'type' => 'TechnicalError');
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
            'amazon_order_reference_id' => $orid
        );
        //send API call
        $response = $client->confirmOrderReference($confirmOrderReferenceParameter);
        $response = $response->toArray();
        if (empty($response) || isset($response['Error'])) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: Confirmation für Bestellung fehlgeschlagen.' . $response['Error']['Message'], JTLLOG_LEVEL_ERROR);
            // ERROR case! there should be no constraints
            $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                'type' => 'Technical');
            $reply['state'] = 'error';
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Fehler beim ConfirmOrder - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
            echo json_encode($reply);
            return;
        }

        /*
         * The confirmation is done, we request the authorization now. We need to remember the AmazonAuthorizationId returned for this order!
         */
        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: ConfirmOrder erfolgreich oder nicht notwendig gewesen, weil es sich um RetryAuth handelte.", JTLLOG_LEVEL_DEBUG);

        $simulator = ''; // this is needed if we simulate (or as seller note)
        if ($_POST['sandbox_auth']) {
            $simulator = $_POST['sandbox_auth'];
        }
        $authorizationTimeout = S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT; // 1440 seconds = 24 hours

        $amazonAuthorizationId = '';
        $authorizationStatus = '';

        if ($authType !== 'manual') {

            /*
             * Immediate capture depending on configuration.
             */
            $captureOnAuth = false;
            if ($captureMode === 'immediate') {
                $captureOnAuth = true;
            }
            if ($authType === 'sync' || $authType === 'omni') {
                // on synchronous or omnicron, we try a synchronous authorization first
                // the difference between sync and omni is: on omnicron we will fall back to async if omni fails with a timeout error
                $authorizationTimeout = 0;
            }
            // set parameters, Capture funds immedately if authorization was successful
            $authorizeParameters = array(
                'merchant_id' => $config['merchant_id'],
                'amazon_order_reference_id' => $orid,
                'authorization_reference_id' => $orid . "-A-" . time(),
                'authorization_amount' => $orderAmount,
                'currency_code' => $currencyISO,
                'seller_authorization_note' => $simulator,
                'transaction_timeout' => $authorizationTimeout,
                'capture_now' => $captureOnAuth
            );
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: AuthType ist $authType, Autorisierung wird versucht mit: " . print_r($authorizeParameters, true), JTLLOG_LEVEL_DEBUG);

            try {
                $authorizationResponseWrapper = $client->authorize($authorizeParameters);
                $authorizationResponseWrapper = $authorizationResponseWrapper->toArray();

                if (isset($authorizationResponseWrapper['Error'])) {
                    throw new Exception($authorizationResponseWrapper['Error']['Message']);
                }

                $authorizationDetails = $authorizationResponseWrapper['AuthorizeResult']['AuthorizationDetails'];
                $amazonAuthorizationId = $authorizationDetails['AmazonAuthorizationId'];
                $authorizationStatus = $authorizationDetails['AuthorizationStatus'];
            } catch (Exception $e) {
                Jtllog::writeLog('LPA: LPA-Payment-Fehler: Authorization fehlgeschlagen:' . $e->getMessage(), JTLLOG_LEVEL_ERROR);
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                    'type' => 'Technical');
                $reply['state'] = 'error';
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Autorisierung fehlgeschlagen - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
                echo json_encode($reply);
                exit;
            }


            /*
             * If authorization is set to synchronous, we try to authorize now, else we consider the order a success immediately
             */
            $state = $authorizationStatus['State'];
            $reason = "";
            if (isset($authorizationStatus['ReasonCode'])) {
                $reason = $authorizationStatus['ReasonCode'];
            }

            /**
             * OMNI handling
             */
            if ($authType === 'omni') {
                if ($state === S360_LPA_STATUS_DECLINED && $reason === S360_LPA_REASON_TRANSACTION_TIMED_OUT) {
                    // synchronous request timed out, switch to asynchronous auth - this means we repeat the request from before and then continue as if this was an async auth in the first place
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Synchrone Autorisierung ist in Timeout gelaufen. Wechsele auf asynchrone Autorisierung und versuche erneut.", JTLLOG_LEVEL_DEBUG);
                    // switch auth method to asynchronous and set timeout
                    $authType = 'async';
                    $authorizationTimeout = S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT;

                    // reset auth parameters with new values, particularly with a timestamp forced to be increased by one second - this is necessary to avoid errors of the type "the ReferenceId S02-...-A-... already exists."
                    $authorizeParameters = array(
                        'merchant_id' => $config['merchant_id'],
                        'amazon_order_reference_id' => $orid,
                        'authorization_reference_id' => $orid . "-A-" . (time() + 1),
                        'authorization_amount' => $orderAmount,
                        'currency_code' => $currencyISO,
                        'seller_authorization_note' => $simulator,
                        'transaction_timeout' => $authorizationTimeout,
                        'capture_now' => $captureOnAuth
                    );
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: AuthType ist jetzt async, Autorisierung wird erneut versucht mit: " . print_r($authorizeParameters, true), JTLLOG_LEVEL_DEBUG);

                    // and retry auth request
                    try {
                        $authorizationResponseWrapper = $client->authorize($authorizeParameters);
                        $authorizationResponseWrapper = $authorizationResponseWrapper->toArray();

                        if (isset($authorizationResponseWrapper['Error'])) {
                            throw new Exception($authorizationResponseWrapper['Error']['Message']);
                        }
                        // reset data from the new response
                        $authorizationDetails = $authorizationResponseWrapper['AuthorizeResult']['AuthorizationDetails'];
                        $amazonAuthorizationId = $authorizationDetails['AmazonAuthorizationId'];
                        $authorizationStatus = $authorizationDetails['AuthorizationStatus'];
                    } catch (Exception $e) {
                        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Authorization fehlgeschlagen:' . $e->getMessage(), JTLLOG_LEVEL_ERROR);
                        $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                            'type' => 'Technical');
                        $reply['state'] = 'error';
                        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Autorisierung fehlgeschlagen - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
                        echo json_encode($reply);
                        exit;
                    }
                    // reload state and reason from the repeated request
                    $state = $authorizationStatus['State'];
                    $reason = "";
                    if (isset($authorizationStatus['ReasonCode'])) {
                        $reason = $authorizationStatus['ReasonCode'];
                    }
                } else {
                    // no error or the error was not a timeout, the remaining error/success handling can go on as if we tried a normal synchronous auth, in the first place.
                    $authType = 'sync';
                }
            }

            if ($authType === 'sync' && $state === S360_LPA_STATUS_DECLINED) {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Synchrone Autorisierungsanfrage - aber Autorisierung wurde abgelehnt. Kunde wird informiert und Bestellvorgang abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
                /*
                 * The request was synchronous and the authorization returned Declined - the user has to change his payment method selection.
                 *
                 * Differentiate between soft and hard decline.
                 */

                if ($reason === S360_LPA_REASON_INVALID_PAYMENT_METHOD) {
                    /*
                     * Soft Decline: have user select another payment method.
                     */
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: Synchrone Authorization fehlgeschlagen mit Soft-Decline.', JTLLOG_LEVEL_NOTICE);
                    $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_SOFT_DECLINE]),
                        'type' => $reason);
                    $reply['state'] = 'error';
                    echo json_encode($reply);
                    exit;
                } elseif ($reason === S360_LPA_REASON_AMAZON_REJECTED) {
                    /*
                     * Hard Decline: error message, Amazon cannot process, have user take the normal checkout process
                     */
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: Synchrone Authorization fehlgeschlagen mit Hard-Decline.', JTLLOG_LEVEL_NOTICE);
                    $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_HARD_DECLINE]),
                        'type' => $reason);
                    $reply['state'] = 'error';
                    echo json_encode($reply);
                    exit;
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: Synchrone Authorization fehlgeschlagen mit technischem Fehler. Status / Reason: ' . $state . ' / ' . $reason, JTLLOG_LEVEL_ERROR);
                    $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                        'type' => 'Technical');
                    $reply['state'] = 'error';
                    echo json_encode($reply);
                    exit;
                }
            } elseif ($authType === 'sync' && ($state === S360_LPA_STATUS_OPEN || ($state === S360_LPA_STATUS_CLOSED && $reason === S360_LPA_REASON_MAX_CAPTURES_PROCESSED))) {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Synchrone Autorisierungsanfrage - erfolgreich.", JTLLOG_LEVEL_DEBUG);
                $confirmationSuccessful = true;
            } elseif ($authType !== 'sync' && $state === S360_LPA_STATUS_PENDING) {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Asynchrone Autorisierungsanfrage - Autosierung ist wie erwartet PENDING.", JTLLOG_LEVEL_DEBUG);
                $confirmationSuccessful = true;
            } else {
                Jtllog::writeLog('LPA: LPA-Payment-Fehler: Authorization fehlgeschlagen mit technischen Fehler. Status / Reason: ' . $state . ' / ' . $reason, JTLLOG_LEVEL_ERROR);
                $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
                    'type' => 'Technical');
                $reply['state'] = 'error';
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Autorisierungsanfrage hat unerwartetes Ergebnis geliefert - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
                echo json_encode($reply);
                exit;
            }
        } else {
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Manuelle Autorisierung - keine Autorisierungsanfrage erforderlich.", JTLLOG_LEVEL_DEBUG);
            $confirmationSuccessful = true;
        }

        if ($confirmationSuccessful) {
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Confirmation erfolgreich durchgeführt.", JTLLOG_LEVEL_DEBUG);
            /*
             * Order confirmed successfully and authorization requested successfully, handle the order like a normal shop order.
             * Save order to plugin db for backend handling and status-following, also remember the authorizationId returned from Amazon.
             */
            unset($_SESSION['Lieferadresse']);

            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Hole erneut OrderReferenceDetails von Amazon Pay, da diese nun mehr Informationen enthalten.", JTLLOG_LEVEL_DEBUG);
            // Get complete delivery address now and set it in the session (save whole AMA name to Nachname only, don't try to guess first/last names)
            $getOrderReferenceDetailsParameter = array(
                'merchant_id' => $config['merchant_id'],
                'amazon_order_reference_id' => $orid
            );
            $response = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: OrderReferenceDetails empfangen.", JTLLOG_LEVEL_DEBUG);

            $response = $response->toArray();
            if (isset($response['Error'])) {
                throw new Exception($response['Error']['Message']);
            }

            $destination = $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];
            $buyer = $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer'];


            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Lieferadresse und Käuferdaten aus OrderReferenceDetails empfangen.", JTLLOG_LEVEL_DEBUG);
            /*
             * The orderReferenceDetails now, after the successful confirmation, contain a lot more information on the customer.
             */
            $aName_arr = explode(" ", utf8_decode($destination['Name']));

            if (!isset($_SESSION['Lieferadresse'])) {
                $_SESSION['Lieferadresse'] = new Lieferadresse();
            }

            if (count($aName_arr) === 2) {
                $_SESSION['Lieferadresse']->cVorname = $aName_arr[0];
                $_SESSION['Lieferadresse']->cNachname = $aName_arr[1];
            } else {
                $_SESSION['Lieferadresse']->cNachname = utf8_decode($destination['Name']);
            }
            $_SESSION['Lieferadresse']->cMail = utf8_decode($buyer['Email']);
            /*
             * The address format is somewhat of a problem: Amazon has 3 different "address lines", whereas JTL has
             * specific fields for street, number, additional address field. What we do is: if only one of the fiels is set,
             * we assume it to be the Strasse, else line 1 goes to Firma, line 2 goes to Strasse, line 3 goes to AdressZusatz
             */
            $cStrasse_arr = array();
            if (isset($destination['AddressLine1']) && is_string($destination['AddressLine1']) && strlen(trim($destination['AddressLine1'])) > 0) {
                $cStrasse_arr[] = utf8_decode($destination['AddressLine1']);
            }
            if (isset($destination['AddressLine2']) && is_string($destination['AddressLine2']) && strlen(trim($destination['AddressLine2'])) > 0) {
                $cStrasse_arr[] = utf8_decode($destination['AddressLine2']);
            }
            if (isset($destination['AddressLine3']) && is_string($destination['AddressLine3']) && strlen(trim($destination['AddressLine3'])) > 0) {
                $cStrasse_arr[] = utf8_decode($destination['AddressLine3']);
            }
            if (count($cStrasse_arr) === 1) {
                $_SESSION['Lieferadresse']->cStrasse = $cStrasse_arr[0];
            } else {
                $_SESSION['Lieferadresse']->cFirma = utf8_decode($destination['AddressLine1']);
                $_SESSION['Lieferadresse']->cStrasse = utf8_decode($destination['AddressLine2']);
                $_SESSION['Lieferadresse']->cAdressZusatz = utf8_decode($destination['AddressLine3']);
            }

            /*
             * heuristic correction for the street and streetnumber in the shop backend. same is done by wawi sync when
             * addresses come from the wawi (see function extractStreet in syncinclude.php)
             */
            $cData_arr = explode(' ', $_SESSION['Lieferadresse']->cStrasse);
            if (count($cData_arr) > 1) {
                $_SESSION['Lieferadresse']->cHausnummer = $cData_arr[count($cData_arr) - 1];
                unset($cData_arr[count($cData_arr) - 1]);
                $_SESSION['Lieferadresse']->cStrasse = implode(' ', $cData_arr);
            }

            if (isset($destination['County'])) {
                $_SESSION['Lieferadresse']->cBundesland = utf8_decode($destination['County']);
            }
            $_SESSION['Lieferadresse']->cOrt = utf8_decode($destination['City']);
            $_SESSION['Lieferadresse']->cPLZ = utf8_decode($destination['PostalCode']);
            $_SESSION['Lieferadresse']->cLand = utf8_decode($destination['CountryCode']);
            if (isset($destination['Phone'])) {
                $_SESSION['Lieferadresse']->cTel = utf8_decode($destination['Phone']);
            }

            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Lieferadresse in Session wurde in JTL Format konvertiert.", JTLLOG_LEVEL_DEBUG);
            /*
             * Only set Kunde in Session to Lieferadresse if there is no user logged in.
             * for guests kKunde might be 0
             */
            if (empty($_SESSION['Kunde'])) {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Kein Kunde eingeloggt, Setze Lieferadresse in Session für Kunde in Session.", JTLLOG_LEVEL_DEBUG);
                $_SESSION['Kunde'] = $_SESSION['Lieferadresse'];
            }
            $_SESSION["Zahlungsart"] = new Zahlungsart();
            $_SESSION["Zahlungsart"]->angezeigterName[$_SESSION['cISOSprache']] = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_PAYMETHOD_NAME]; // change name of paymethod to match up with wawi setting.

            /*
             *  save order in database
             */
            $bezahlt = 0;
            if ($captureOnAuth) {
                $bezahlt = 1;
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Da sofort-Capture aktiv ist, wird Bestellung als bezahlt angesehen.", JTLLOG_LEVEL_DEBUG);
            } else {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Da sofort-Capture nicht aktiv ist, wird Bestellung noch nicht als bezahlt angesehen.", JTLLOG_LEVEL_DEBUG);
            }

            /*
             * Set delivery address key
             */
            if (!isset($_SESSION['Bestellung'])) {
                $_SESSION['Bestellung'] = new stdClass();
            }

            if (empty($_SESSION['Kunde']) || $_SESSION['Kunde']->kKunde == 0) {
                // non-existing customer, this is per definition a new delivery address
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Lieferadresse wird neu angelegt.", JTLLOG_LEVEL_DEBUG);
                $_SESSION['Bestellung']->kLieferadresse = -1; // force new delivery address for customer 
            } else {
                // try to find a matching delivery address in the database
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Versuche, Lieferadresse gegen existierende Lieferadressen des Kunden zu matchen.", JTLLOG_LEVEL_DEBUG);
                $_SESSION['Bestellung']->kLieferadresse = $database->getKeyForLieferadresse($_SESSION['Kunde'], $_SESSION['Lieferadresse']);
            }
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Key der Lieferadresse (-1 = neu, ansonsten ist die Lieferadresse vorher bekannt gewesen): " . $_SESSION['Bestellung']->kLieferadresse, JTLLOG_LEVEL_DEBUG);

            /*
             * finalisiere Bestellung
             */
            $obj = new stdClass();
            $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();

            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung wird finalisiert.", JTLLOG_LEVEL_DEBUG);
            /*
             * Add Amazon-Order-Reference-ID to comment field.
             */
            if (!empty($_POST['kommentar'])) {
                $_POST['kommentar'] .= ' Amazon-Referenz: ' . $orid;
            } else {
                $_POST['kommentar'] = 'Amazon-Referenz: ' . $orid;
            }
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung wird in Shop Datenbank geschrieben.", JTLLOG_LEVEL_DEBUG);
            bestellungInDB(0);
            // note: beyond this point, a guest and a registered customer have a kKunde set
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung wird geladen, um die Bestellbestätigungsseite anzuzeigen.", JTLLOG_LEVEL_DEBUG);
            $bestellung = new Bestellung($_SESSION['kBestellung']);
            $bestellung->fuelleBestellung(0);
            /* set Wawi Bestellattribut, additionally */
            if(Shop::getShopVersion() > 405) {
                $orderAttribute = new stdClass();
                $orderAttribute->kBestellung = $bestellung->kBestellung;
                $orderAttribute->cName = 'AmazonPay-Referenz';
                $orderAttribute->cValue = $orid;
                Shop::DB()->insert('tbestellattribut', $orderAttribute);
            }
            Shop::DB()->query("update tbesucher set kKunde=" . (int) $_SESSION["Warenkorb"]->kKunde . ", kBestellung=" .(int)$bestellung->kBestellung. " where cIP=\"" . gibIP() . "\"", 4);
            //mail raus
            $obj->tkunde = $_SESSION["Kunde"];
            $obj->tbestellung = $bestellung;
            // avoid notice when confirmation mail is sent by adding a dummy Zahlungsart-object
            $obj->tbestellung->Zahlungsart = new stdClass();
            $obj->tbestellung->Zahlungsart->cModulId = "";
            //$obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit($bestellung);
            // Work Around cLand
            $oKunde = new Kunde();
            $oKunde->kopiereSession();
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Versende Bestellbestätigung an Kunden.", JTLLOG_LEVEL_DEBUG);
            sendeMail(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj);
            $_SESSION["Kunde"] = $oKunde;
            $kKundengruppe = Kundengruppe::getCurrent();
            $oCheckBox = new CheckBox();
            // CheckBox Spezialfunktion ausführen
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Führe Checkbox-Spezialfunktionen aus und logge Checkbox-Einträge.", JTLLOG_LEVEL_DEBUG);

            $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, true, $_POST, array("oBestellung" => $bestellung, "oKunde" => $oKunde));
            $oCheckBox->checkLogging(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $_POST, true);

            Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Checkbox-Spezialfunktionen ausgeführt.", JTLLOG_LEVEL_DEBUG);

            /*
             * Save Amazon Order object
             */
            $orderId = $_SESSION["BestellNr"];
            $kBestellung = $_SESSION["kBestellung"];

            // send order id and store name to amazon
            try {
                $setOrderAttributesParameter = array(
                    'merchant_id' => $config['merchant_id'], //your merchant/seller ID
                    'amazon_order_reference_id' => $orid, //unique identifier for the order reference
                    'seller_order_id' => $orderId, // order ID from JTL Shop
                    'custom_information' => S360_LPA_CUSTOM_INFORMATION
                );
                $shopConfigGlobal = Shop::getSettings([CONF_GLOBAL]);
                if(isset($shopConfigGlobal['global']['global_shopname']) && $shopConfigGlobal['global']['global_shopname'] != '') {
                    $setOrderAttributesParameter['store_name'] = utf8_encode($shopConfigGlobal['global']['global_shopname']);
                }
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: OrderAttributes werden geupdated mit: " . print_r($setOrderAttributesParameter, true), JTLLOG_LEVEL_DEBUG);
                $result = $client->setOrderAttributes($setOrderAttributesParameter);
                $result = $result->toArray();
                if (isset($result['Error'])) {
                    throw new Exception($result['Error']['Message']);
                }
            } catch(Exception $ex) {
                // log exceptions but this is no fatal problem
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Exception beim Versuch, die Bestellnummer $orderId an AmazonPay zu senden: " . $ex->getMessage(), JTLLOG_LEVEL_NOTICE);
            }

            $expiration = $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['ExpirationTimestamp'];
            if (!empty($expiration)) {
                Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Expiration Timestamp von Amazon: $expiration", JTLLOG_LEVEL_DEBUG);
                $timezone = ini_get("date.timezone");
                if (empty($timezone)) {
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Timezone-Setting nicht in php.ini vorhanden. Erzwinge Europe/Berlin.", JTLLOG_LEVEL_DEBUG);
                    date_default_timezone_set("Europe/Berlin");
                }
                $expiration = date_timestamp_get(new DateTime($expiration));
                Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Expiration Timestamp nach Konvertierung: $expiration", JTLLOG_LEVEL_DEBUG);
            } else {
                Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Kein Expiration Timestamp von Amazon empfangen.", JTLLOG_LEVEL_DEBUG);
            }
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Speichere Order-Objekt und Zuordnung für das Plugin-Backend.", JTLLOG_LEVEL_DEBUG);
            $database->saveOrder($kBestellung, $orid, $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderTotal']['Amount'], $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderTotal']['CurrencyCode'], S360_LPA_STATUS_OPEN, null, $expiration);

            /*
             * Save authorization to database.
             */
            if (!empty($amazonAuthorizationId)) {
                $expiration = $authorizationDetails['ExpirationTimestamp'];
                if (!empty($expiration)) {
                    $timezone = ini_get("date.timezone");
                    if (empty($timezone)) {
                        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Timezone-Setting nicht in php.ini vorhanden. Erzwinge Europe/Berlin.", JTLLOG_LEVEL_DEBUG);
                        date_default_timezone_set("Europe/Berlin");
                    }
                    $expiration = date_timestamp_get(new DateTime($expiration));
                }
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Speichere Authorization-Objekt und Zuordnung für das Plugin-Backend.", JTLLOG_LEVEL_DEBUG);
                $database->saveAuthorization($orid, $amazonAuthorizationId, $authorizationDetails['AuthorizationAmount']['Amount'], $authorizationDetails['AuthorizationAmount']['CurrencyCode'], (int)$captureOnAuth, $authorizationDetails['CapturedAmount']['Amount'], $authorizationDetails['CapturedAmount']['CurrencyCode'], $authorizationStatus['State'], isset($authorizationStatus['ReasonCode']) ? $authorizationStatus['ReasonCode'] : "", $expiration);
            }

            /*
             * Save additional bestellung data in database...
             */
            if (!empty($amazonAuthorizationId) && ($authorizationStatus['State'] === S360_LPA_STATUS_OPEN || ($authorizationStatus['State'] === S360_LPA_STATUS_CLOSED && $authorizationStatus['ReasonCode'] === S360_LPA_REASON_MAX_CAPTURES_PROCESSED))) {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung erfolgreich autorisiert und die Autorisierung ist {$authorizationStatus['State']}.", JTLLOG_LEVEL_DEBUG);
                // authorization is successfully completed.
                $rechnungsAdresse = null;
                if ($bezahlt) {
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung ist als bezahlt angesehen, hole daher Rechnungsdaten von Amazon Pay.", JTLLOG_LEVEL_DEBUG);
                    // we can request the billing address immediately
                    $adapter = new LPAAdapter();
                    $billingaddress = $adapter->getRemoteAuthorizationDetails($amazonAuthorizationId);
                    $billingaddress = $billingaddress['AuthorizationBillingAddress'];
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Rechnungsadresse von Amazon Pay empfangen.", JTLLOG_LEVEL_DEBUG);
                    if (!empty($billingaddress)) {
                        $rechnungsAdresse = new stdClass();

                        $aName_arr = explode(" ", utf8_decode($billingaddress['Name']), 2);
                        if (count($aName_arr) === 2) {
                            $rechnungsAdresse->cVorname = $aName_arr[0];
                            $rechnungsAdresse->cNachname = $aName_arr[1];
                        } else {
                            $rechnungsAdresse->cNachname = utf8_decode($billingaddress['Name']);
                        }

                        $cStrasse_arr = array();

                        if (isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) && strlen(trim($billingaddress['AddressLine1'])) > 0) {
                            $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine1']);
                        }
                        if (isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) && strlen(trim($billingaddress['AddressLine2'])) > 0) {
                            $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine2']);
                        }
                        if (isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) && strlen(trim($billingaddress['AddressLine3'])) > 0) {
                            $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine3']);
                        }

                        if (count($cStrasse_arr) === 1) {
                            $rechnungsAdresse->cStrasse = $cStrasse_arr[0];
                        } else {
                            $rechnungsAdresse->cFirma = utf8_decode($billingaddress['AddressLine1']);
                            $rechnungsAdresse->cStrasse = utf8_decode($billingaddress['AddressLine2']);
                            $rechnungsAdresse->cAdressZusatz = utf8_decode($billingaddress['AddressLine3']);
                        }

                        /*
                         * heuristic correction for the street and streetnumber in the shop backend. same is done by wawi sync when
                         * addresses come from the wawi (see function extractStreet in syncinclude.php)
                         */
                        $cData_arr = explode(' ', $rechnungsAdresse->cStrasse);
                        if (count($cData_arr) > 1) {
                            $rechnungsAdresse->cHausnummer = $cData_arr[count($cData_arr) - 1];
                            unset($cData_arr[count($cData_arr) - 1]);
                            $rechnungsAdresse->cStrasse = implode(' ', $cData_arr);
                        }

                        $rechnungsAdresse->cOrt = utf8_decode($billingaddress['City']);
                        $rechnungsAdresse->cPLZ = utf8_decode($billingaddress['PostalCode']);
                        $rechnungsAdresse->cLand = ISO2land($billingaddress['CountryCode']);
                        $rechnungsAdresse->cMail = $buyer['Email'];
                    }
                    Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Rechnungsadresse konvertiert.", JTLLOG_LEVEL_DEBUG);
                }
                Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Setze Bestellung auf autorisiert.", JTLLOG_LEVEL_DEBUG);
                $database->setBestellungAuthorized($orid, $rechnungsAdresse);

                if ($bezahlt) {
                    // on immediate capture and successful authorization we assume that the order is paid.
                    // Capture information is acquired via IPN/Cron... however, we already get the CaptureID in the IdList of the Authorization Details.
                    // We can also already request the Billing Address now (we did this in the block before).
                    // CaptureId in ID List
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Da Bestellung als bezahlt angesehen wird, wird ein Proforma-Capture-Objekt in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);

                    $captureIdList = $authorizationDetails['IdList']['member'];
                    if (!is_array($captureIdList) && !empty($captureIdList)) {
                        $captureIdList = array($captureIdList);
                    }
                    foreach ($captureIdList as $capid) {
                        $cap = new stdClass();
                        $cap->cCaptureId = $capid;
                        $cap->cAuthorizationId = $amazonAuthorizationId;
                        $cap->cCaptureStatus = S360_LPA_STATUS_PENDING;
                        $cap->cCaptureStatusReason = '';
                        $cap->fCaptureAmount = $authorizationDetails['AuthorizationAmount']['Amount'];
                        $cap->cCaptureCurrencyCode = $authorizationDetails['AuthorizationAmount']['CurrencyCode'];
                        $cap->fRefundedAmount = 0;
                        $cap->cRefundedCurrencyCode = $cap->cCaptureCurrencyCode;
                        $cap->bSandbox = (int)($config['sandbox']);
                        Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Speichere Capture-Objekt: " . print_r($cap, true), JTLLOG_LEVEL_DEBUG);

                        $database->saveCaptureObject($cap);
                    }
                }
            } else {
                Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung wird als noch nicht bezahlt angesehen (manuelle oder asynchrone Autorisation) und Autorisation wird auf PENDING gesetzt.", JTLLOG_LEVEL_DEBUG);

                // this is the state the order is in on async or manual authorization
                $database->setBestellungAuthorizationPending($orid);


                /*
                 *  However, on immediate capture, we also have to save the capture object right now - it is in the state PENDING.
                 * 
                 *  We assume the following values:
                 *  cAuthorizationId VARCHAR(50) - current authorization
                 *  cCaptureId VARCHAR(50) - from the authorization details
                 *  cCaptureStatus VARCHAR(50) - PENDING
                 *  cCaptureStatusReason VARCHAR(50) - empty
                 *  fCaptureAmount DECIMAL(18,2) - authorization amount
                 *  cCaptureCurrencyCode VARCHAR(50) - authorization currency
                 *  fRefundedAmount DECIMAL(18,2) - 0
                 *  cRefundedCurrencyCode VARCHAR(50) - authorization currency
                 *  bSandbox INT(1) - as set by config
                 */
                if ($captureOnAuth && !empty($amazonAuthorizationId)) {
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Bestellung ist zwar noch nicht autorisiert, aber durch Sofort-Capture kann schon ein Capture-Objekt angelegt werden.", JTLLOG_LEVEL_DEBUG);

                    $captureIdList = $authorizationDetails['IdList']['member'];
                    if (!is_array($captureIdList) && !empty($captureIdList)) {
                        $captureIdList = array($captureIdList);
                    }
                    Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Capture-Ids zum Order: " . print_r($captureIdList, true), JTLLOG_LEVEL_DEBUG);

                    foreach ($captureIdList as $capid) {
                        $cap = new stdClass();
                        $cap->cCaptureId = $capid;
                        $cap->cAuthorizationId = $amazonAuthorizationId;
                        $cap->cCaptureStatus = S360_LPA_STATUS_PENDING;
                        $cap->cCaptureStatusReason = '';
                        $cap->fCaptureAmount = $authorizationDetails['AuthorizationAmount']['Amount'];
                        $cap->cCaptureCurrencyCode = $authorizationDetails['AuthorizationAmount']['CurrencyCode'];
                        $cap->fRefundedAmount = 0;
                        $cap->cRefundedCurrencyCode = $cap->cCaptureCurrencyCode;
                        $cap->bSandbox = (int)(($config['sandbox']));
                        Jtllog::writeLog("LPA-AJAX-CONFIRM-$orid: Speichere Capture-Objekt: " . print_r($cap, true), JTLLOG_LEVEL_DEBUG);
                        $database->saveCaptureObject($cap);
                    }
                }
            }

            $reply['snippet'] = '';
            $reply['state'] = 'success';
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Vorgang abgeschlossen. Liefere Ergebnis an das aufrufende Skript zurück: " . print_r($reply, true), JTLLOG_LEVEL_DEBUG);
            echo json_encode($reply);
            exit;
        }
    } catch (Exception $ex) {
        $cFehler = $ex->getMessage();
        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Technischer Fehler beim Bestellabschluss: ' . $cFehler, JTLLOG_LEVEL_ERROR);
        $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TECHNICAL_ERROR]),
            'type' => 'Technical');
        $reply['state'] = 'error';
        echo json_encode($reply);
        exit;
    }
} else {
    $reply['error'] = array('message' => utf8_encode($oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CONFIRMATION_CHECKBOXES]),
        'type' => 'Plausi', 'plausi' => $cPlausi_arr);
    $reply['state'] = 'error';
    echo json_encode($reply);
    exit;
}
