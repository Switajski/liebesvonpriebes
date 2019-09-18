<?php

require_once __DIR__ . '/lib/lpa_includes.php';
require_once __DIR__ . '/lib/class.LPALinkHelper.php';
require_once PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php";
require_once PFAD_ROOT . PFAD_INCLUDES . "smartyInclude.php";
require_once PFAD_ROOT . PFAD_INCLUDES . "bestellabschluss_inc.php";
$lpaPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once $lpaPlugin->cFrontendPfad . 'lib/class.LPAController.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/class.LPADatabase.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/lpa_defines.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/lpa_utils.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/class.LPACurrencyHelper.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/class.LPAHelper.php';
require_once $lpaPlugin->cFrontendPfad . 'lib/class.LPALinkHelper.php';

/**
 * Continue after PSD2
 */
try {

    if(!isset($_REQUEST['AuthenticationStatus']) || $_REQUEST['AuthenticationStatus'] !== 'Success') {
        throw new Exception('Authentication status missing or invalid.', S360_LPA_EXCEPTION_CODE_GENERIC);
    }

    $cPost_arr = $_SESSION['lpa_last_post_array'];
    unset($_SESSION['lpa_last_post_array']);

    $orid = $cPost_arr['orid'];

    $authType = $lpaPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTHMODE];

    // effectively disable async auth / manual auth completely by mapping them to omni
    if($authType === 'async' || $authType === 'manual') {
        $authType = 'omni';
    }

    $captureMode = $lpaPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE];
    $stateOnAuth = $lpaPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTHSTATE];

    $controller = new LPAController();
    $database = new LPADatabase();
    $config = $controller->getConfig();
    $client = $controller->getClient();

    Jtllog::writeLog('LPA: PROCESS SUCCESS: Beginne Erfolgsverarbeitung von Order-ID ' . $orid, JTLLOG_LEVEL_DEBUG);

    /**
     * Now that PSD2 is passed, we first load the order reference details from amazon to:
     * - Check if the order is really confirmed and now open (OPEN indicates that the confirmation flow was successful)
     * - Get the address data we need
     * - Get the currency and amounts from the order reference id in order to authorize the exact same amount
     * - BUT: We do have to check that the basket amount has not changed in the meantime
     */
    $getOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'],
        'amazon_order_reference_id' => $orid
    );
    $orderReferenceDetailsArray = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter)->toArray();

    if(isset($orderReferenceDetailsArray['Error'])) {
        Jtllog::writeLog('LPA: PROCESS SUCCESS: GetOrderReferenceDetails beim Success-Processing ist mit Error abgebrochen: ' . print_r($orderReferenceDetailsArray, true), JTLLOG_LEVEL_DEBUG);
        throw new Exception($orderReferenceDetailsArray['Error']['Message'], S360_LPA_EXCEPTION_CODE_GENERIC);
    }

    if(!isset($orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderReferenceStatus']['State']) || $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderReferenceStatus']['State'] !== S360_LPA_STATUS_OPEN) {
        /* Request failed or ORDER REFERENCE is not in OPEN state */
        Jtllog::writeLog('LPA: PROCESS SUCCESS: GetOrderReferenceDetails hat Order nicht im Status OPEN zurückgegeben.', JTLLOG_LEVEL_DEBUG);
        throw new Exception("Order Reference is not in OPEN state.", S360_LPA_EXCEPTION_CODE_ORDER_NOT_OPEN);
    }

    /*
     * MultiCurrency handling
     */
    $presentmentCurrency = LPACurrencyHelper::getCurrentCurrency();
    if (!LPACurrencyHelper::isSupportedCurrency($presentmentCurrency->cISO)) {
        // Error - the user seems to have changed the session currency - abort
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Abbruch: Session-Currency '{$presentmentCurrency->cISO}' ist nicht valide für Amazon Pay.", JTLLOG_LEVEL_DEBUG);
        throw new Exception("Currency '{$presentmentCurrency->cISO}' is invalid for Amazon Pay.", S360_LPA_EXCEPTION_CODE_CURRENCY_INVALID);
    }

    // the amount is always in shop default currency
    $amount = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);
    // Amazon Pay needs the total amount for the order based on the presentment currency
    $currencyISO = $presentmentCurrency->cISO;
    $defaultCurrency = LPACurrencyHelper::getDefaultCurrency();
    $orderAmount = LPACurrencyHelper::convertAmount($amount, $defaultCurrency->cISO, $presentmentCurrency->cISO);

    if((float) $orderAmount !== (float) $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderTotal']['Amount']) {
        /* Order Reference total amount deviates from basket - this is an error, too */
        throw new Exception("Order Reference total amount is not the same as the basket in the session", S360_LPA_EXCEPTION_CODE_ORDER_AMOUNT_CHANGED);
    }

    $simulationString = ''; // this is needed if we simulate (or as seller note)
    if ($cPost_arr['sandbox_auth']) {
        $simulationString = $cPost_arr['sandbox_auth'];
    }

    $amazonAuthorizationId = '';
    $authorizationStatus = '';

    /*
     * Determine immediate capture depending on configuration.
     */
    $captureOnAuth = false;
    if ($captureMode === 'immediate') {
        $captureOnAuth = true;
    }

    // set parameters, at first always do a synchronous auth call
    $authorizeParameters = array(
        'merchant_id' => $config['merchant_id'],
        'amazon_order_reference_id' => $orid,
        'authorization_reference_id' => $orid . "-A-" . time(),
        'authorization_amount' => $orderAmount,
        'currency_code' => $currencyISO,
        'seller_authorization_note' => $simulationString,
        'transaction_timeout' => 0,
        'capture_now' => $captureOnAuth
    );
    Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: AuthType ist $authType, Autorisierung wird versucht mit: " . print_r($authorizeParameters, true), JTLLOG_LEVEL_DEBUG);

    try {
        $authorizationResponseWrapper = $client->authorize($authorizeParameters);
        $authorizationResponseWrapper = $authorizationResponseWrapper->toArray();

        if (isset($authorizationResponseWrapper['Error'])) {
            throw new Exception($authorizationResponseWrapper['Error']['Message'], S360_LPA_EXCEPTION_CODE_GENERIC);
        }

        $authorizationDetails = $authorizationResponseWrapper['AuthorizeResult']['AuthorizationDetails'];
        $amazonAuthorizationId = $authorizationDetails['AmazonAuthorizationId'];
        $authorizationStatus = $authorizationDetails['AuthorizationStatus'];
    } catch (Exception $e) {
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Authorization fehlgeschlagen:" . $e->getMessage(), JTLLOG_LEVEL_ERROR);
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Autorisierung fehlgeschlagen - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
        throw $e;
    }

    $state = $authorizationStatus['State'];
    $reason = '';
    if (isset($authorizationStatus['ReasonCode'])) {
        $reason = $authorizationStatus['ReasonCode'];
    }

    /**
     * OMNI handling, if the synchronous authorization failed, we switch over to asynchronous authorization with max timeout
     */
    if ($authType === 'omni') {
        if ($state === S360_LPA_STATUS_DECLINED && $reason === S360_LPA_REASON_TRANSACTION_TIMED_OUT) {
            // synchronous request timed out, switch to asynchronous auth - this means we repeat the request from before and then continue as if this was an async auth in the first place
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Synchrone Autorisierung ist in Timeout gelaufen. Wechsele auf asynchrone Autorisierung und versuche erneut.", JTLLOG_LEVEL_DEBUG);
            // switch auth method to asynchronous and set timeout
            $authType = 'async';

            // reset auth parameters with new values, particularly with a timestamp forced to be increased by one second - this is necessary to avoid errors of the type "the ReferenceId S02-...-A-... already exists."
            $authorizeParameters = array(
                'merchant_id' => $config['merchant_id'],
                'amazon_order_reference_id' => $orid,
                'authorization_reference_id' => $orid . "-A-" . (time() + 1),
                'authorization_amount' => $orderAmount,
                'currency_code' => $currencyISO,
                'seller_authorization_note' => $simulationString,
                'transaction_timeout' => S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT,
                'capture_now' => $captureOnAuth
            );
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: AuthType ist jetzt async, Autorisierung wird erneut versucht mit: " . print_r($authorizeParameters, true), JTLLOG_LEVEL_DEBUG);

            // and retry auth request
            try {
                $authorizationResponseWrapper = $client->authorize($authorizeParameters);
                $authorizationResponseWrapper = $authorizationResponseWrapper->toArray();

                if (isset($authorizationResponseWrapper['Error'])) {
                    throw new Exception($authorizationResponseWrapper['Error']['Message'], S360_LPA_EXCEPTION_CODE_GENERIC);
                }
                // reset data from the new response
                $authorizationDetails = $authorizationResponseWrapper['AuthorizeResult']['AuthorizationDetails'];
                $amazonAuthorizationId = $authorizationDetails['AmazonAuthorizationId'];
                $authorizationStatus = $authorizationDetails['AuthorizationStatus'];
            } catch (Exception $e) {
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Authorization fehlgeschlagen:" . $e->getMessage(), JTLLOG_LEVEL_ERROR);
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Autorisierung fehlgeschlagen - Bestellvorgang wird abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
                throw $e;
            }
            // reload state and reason from the repeated request
            $state = $authorizationStatus['State'];
            $reason = '';
            if (isset($authorizationStatus['ReasonCode'])) {
                $reason = $authorizationStatus['ReasonCode'];
            }
        } else {
            // no error or the error was not a timeout, the remaining error/success handling can go on as if we tried a normal synchronous auth, in the first place.
            $authType = 'sync';
        }
    }

    /**
     * SYNC handling
     */
    if($authType === 'sync' && $state === S360_LPA_STATUS_DECLINED) {
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Synchrone Autorisierungsanfrage - aber Autorisierung wurde abgelehnt. Kunde wird informiert und Bestellvorgang abgebrochen. Es wurde KEINE Bestellung in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);
        /*
         * The request was synchronous and the authorization returned Declined - the user has to change his payment method selection.
         *
         * Differentiate between soft and hard decline.
         */

        if ($reason === S360_LPA_REASON_INVALID_PAYMENT_METHOD) {
            /*
             * Soft Decline: have user select another payment method.
             */
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Synchrone Authorization fehlgeschlagen mit Soft-Decline.", JTLLOG_LEVEL_DEBUG);
            throw new Exception('Synchronous authorization failed with soft decline', S360_LPA_EXCEPTION_CODE_SOFT_DECLINE);
        } elseif ($reason === S360_LPA_REASON_AMAZON_REJECTED) {
            /*
             * Hard Decline: error message, Amazon cannot process, have user take the normal checkout process
             */
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Synchrone Authorization fehlgeschlagen mit Hard-Decline.", JTLLOG_LEVEL_DEBUG);
            throw new Exception('Synchronous authorization failed with hard decline', S360_LPA_EXCEPTION_CODE_HARD_DECLINE);
        } else {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Synchrone Authorization fehlgeschlagen mit technischem Fehler. Status / Reason: " . $state . ' / ' . $reason, JTLLOG_LEVEL_ERROR);
            throw new Exception('Synchronous authorization failed with generic other reason', S360_LPA_EXCEPTION_CODE_GENERIC);
        }
    } elseif ($authType === 'sync' && ($state === S360_LPA_STATUS_OPEN || ($state === S360_LPA_STATUS_CLOSED && $reason === S360_LPA_REASON_MAX_CAPTURES_PROCESSED))) {
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Synchrone Autorisierungsanfrage - erfolgreich.", JTLLOG_LEVEL_DEBUG);
        $confirmationSuccessful = true;
    } elseif ($authType !== 'sync' && $state === S360_LPA_STATUS_PENDING) {
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Asynchrone Autorisierungsanfrage - Autorisierung ist wie erwartet PENDING.", JTLLOG_LEVEL_DEBUG);
        $confirmationSuccessful = true;
    } else {
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Authorization fehlgeschlagen mit technischen Fehler. Status / Reason: " . $state . ' / ' . $reason, JTLLOG_LEVEL_ERROR);
        throw new Exception('Authorization failed with generic other reason', S360_LPA_EXCEPTION_CODE_GENERIC);
    }

    if ($confirmationSuccessful) {
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Confirmation erfolgreich durchgeführt.", JTLLOG_LEVEL_DEBUG);
        /*
         * Order confirmed successfully and authorization requested successfully, handle the order like a normal shop order.
         * Save order to plugin db for backend handling and status-following, also remember the authorizationId returned from Amazon.
         */
        unset($_SESSION['Lieferadresse']);

        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Hole erneut OrderReferenceDetails von Amazon Pay, da diese nun mehr Informationen enthalten.", JTLLOG_LEVEL_DEBUG);
        $getOrderReferenceDetailsParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_order_reference_id' => $orid
        );

        $destination = $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];
        $buyer = $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer'];

        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Lieferadresse und Käuferdaten aus OrderReferenceDetails empfangen.", JTLLOG_LEVEL_DEBUG);

        /*
         * The orderReferenceDetails now, after the successful confirmation, contain a lot more information on the customer.
         */
        $aName_arr = explode(' ', utf8_decode($destination['Name']));

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

        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Lieferadresse in Session wurde in JTL Format konvertiert.", JTLLOG_LEVEL_DEBUG);
        /*
         * Only set Kunde in Session to Lieferadresse if there is no user logged in.
         * for guests kKunde might be 0
         */
        if (empty($_SESSION['Kunde'])) {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Kein Kunde eingeloggt, Setze Lieferadresse in Session für Kunde in Session.", JTLLOG_LEVEL_DEBUG);
            $_SESSION['Kunde'] = $_SESSION['Lieferadresse'];
        }
        $_SESSION["Zahlungsart"] = new Zahlungsart();
        $_SESSION["Zahlungsart"]->angezeigterName[$_SESSION['cISOSprache']] = $lpaPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_PAYMETHOD_NAME]; // change name of paymethod to match up with wawi setting.

        /*
         *  save order in database
         */
        $bezahlt = 0;
        if ($captureOnAuth) {
            $bezahlt = 1;
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Da sofort-Capture aktiv ist, wird Bestellung als bezahlt angesehen.", JTLLOG_LEVEL_DEBUG);
        } else {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Da sofort-Capture nicht aktiv ist, wird Bestellung noch nicht als bezahlt angesehen.", JTLLOG_LEVEL_DEBUG);
        }

        /*
         * Set delivery address key
         */
        if (!isset($_SESSION['Bestellung'])) {
            $_SESSION['Bestellung'] = new stdClass();
        }

        if (empty($_SESSION['Kunde']) || $_SESSION['Kunde']->kKunde == 0) {
            // non-existing customer, this is per definition a new delivery address
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Lieferadresse wird neu angelegt.", JTLLOG_LEVEL_DEBUG);
            $_SESSION['Bestellung']->kLieferadresse = -1; // force new delivery address for customer
        } else {
            // try to find a matching delivery address in the database
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Versuche, Lieferadresse gegen existierende Lieferadressen des Kunden zu matchen.", JTLLOG_LEVEL_DEBUG);
            $_SESSION['Bestellung']->kLieferadresse = $database->getKeyForLieferadresse($_SESSION['Kunde'], $_SESSION['Lieferadresse']);
        }
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Key der Lieferadresse (-1 = neu, ansonsten ist die Lieferadresse vorher bekannt gewesen): " . $_SESSION['Bestellung']->kLieferadresse, JTLLOG_LEVEL_DEBUG);

        /*
         * finalisiere Bestellung
         */
        $obj = new stdClass();
        $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();

        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Bestellung wird finalisiert.", JTLLOG_LEVEL_DEBUG);
        /*
         * Add Amazon-Order-Reference-ID to comment field.
         */
        if (!empty($cPost_arr['kommentar'])) {
            $_POST['kommentar'] = $cPost_arr['kommentar'] . ' Amazon-Referenz: ' . $orid;
        } else {
            $_POST['kommentar'] = 'Amazon-Referenz: ' . $orid;
        }
        Jtllog::writeLog("LPA:  PROCESS SUCCESS $orid: Bestellung wird in Shop-Datenbank geschrieben.", JTLLOG_LEVEL_DEBUG);
        bestellungInDB(0);
        // note: beyond this point, a guest and a registered customer have a kKunde set
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Bestellung wird geladen, um die Bestellbestätigungsseite anzuzeigen.", JTLLOG_LEVEL_DEBUG);
        $bestellung = new Bestellung($_SESSION['kBestellung']);
        $bestellung->fuelleBestellung(0);
        /* set Wawi Bestellattribut, additionally */
        if (Shop::getShopVersion() > 405) {
            $orderAttribute = new stdClass();
            $orderAttribute->kBestellung = $bestellung->kBestellung;
            $orderAttribute->cName = S360_LPA_ORDER_ATTRIBUTE_REFERENCE;
            $orderAttribute->cValue = $orid;
            Shop::DB()->insert('tbestellattribut', $orderAttribute);
        }
        Shop::DB()->query("UPDATE tbesucher SET kKunde=" . (int)$_SESSION["Warenkorb"]->kKunde . ", kBestellung=" . (int)$bestellung->kBestellung . " WHERE cIP=\"" . gibIP() . "\"", 4);
        //mail raus
        $obj->tkunde = $_SESSION["Kunde"];
        $obj->tbestellung = $bestellung;
        // avoid notice when confirmation mail is sent by adding a dummy Zahlungsart-object
        $obj->tbestellung->Zahlungsart = new stdClass();
        $obj->tbestellung->Zahlungsart->cModulId = '';
        $obj->tbestellung->Zahlungsart->cName = $lpaPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_PAYMETHOD_NAME];
        //$obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit($bestellung);
        // Work Around cLand
        $oKunde = new Kunde();
        $oKunde->kopiereSession();
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Versende Bestellbestätigung an Kunden.", JTLLOG_LEVEL_DEBUG);
        sendeMail(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj);
        $_SESSION["Kunde"] = $oKunde;
        $kKundengruppe = Kundengruppe::getCurrent();
        $oCheckBox = new CheckBox();
        // CheckBox Spezialfunktion ausführen
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Führe Checkbox-Spezialfunktionen aus und logge Checkbox-Einträge.", JTLLOG_LEVEL_DEBUG);

        $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, true, $cPost_arr, array("oBestellung" => $bestellung, "oKunde" => $oKunde));
        $oCheckBox->checkLogging(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $cPost_arr, true);

        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Checkbox-Spezialfunktionen ausgeführt.", JTLLOG_LEVEL_DEBUG);

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
            if (isset($shopConfigGlobal['global']['global_shopname']) && $shopConfigGlobal['global']['global_shopname'] != '') {
                $setOrderAttributesParameter['store_name'] = utf8_encode($shopConfigGlobal['global']['global_shopname']);
            }
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: OrderAttributes werden geupdated mit: " . print_r($setOrderAttributesParameter, true), JTLLOG_LEVEL_DEBUG);
            $result = $client->setOrderAttributes($setOrderAttributesParameter);
            $result = $result->toArray();
            if (isset($result['Error'])) {
                throw new Exception($result['Error']['Message'], S360_LPA_EXCEPTION_CODE_GENERIC);
            }
        } catch (Exception $ex) {
            // log exceptions but this is no fatal problem (we simply did not set optional information in Amazon Pay)
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Exception beim Versuch, die Bestellnummer $orderId an AmazonPay zu senden: " . $ex->getMessage(), JTLLOG_LEVEL_NOTICE);
        }

        $expiration = $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['ExpirationTimestamp'];
        if (!empty($expiration)) {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Expiration Timestamp von Amazon: $expiration", JTLLOG_LEVEL_DEBUG);
            $timezone = ini_get("date.timezone");
            if (empty($timezone)) {
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Timezone-Setting nicht in php.ini vorhanden. Erzwinge Europe/Berlin.", JTLLOG_LEVEL_DEBUG);
                date_default_timezone_set("Europe/Berlin");
            }
            $expiration = date_timestamp_get(new DateTime($expiration));
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Expiration Timestamp nach Konvertierung: $expiration", JTLLOG_LEVEL_DEBUG);
        } else {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Kein Expiration Timestamp von Amazon empfangen.", JTLLOG_LEVEL_DEBUG);
        }
        Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Speichere Order-Objekt und Zuordnung für das Plugin-Backend.", JTLLOG_LEVEL_DEBUG);
        $database->saveOrder(
            $kBestellung,
            $orid,
            $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderTotal']['Amount'],
            $orderReferenceDetailsArray['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderTotal']['CurrencyCode'],
            S360_LPA_STATUS_OPEN,
            null,
            $expiration
        );

        /*
         * Save authorization to database.
         */
        if (!empty($amazonAuthorizationId)) {
            $expiration = $authorizationDetails['ExpirationTimestamp'];
            if (!empty($expiration)) {
                $timezone = ini_get("date.timezone");
                if (empty($timezone)) {
                    Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Timezone-Setting nicht in php.ini vorhanden. Erzwinge Europe/Berlin.", JTLLOG_LEVEL_DEBUG);
                    date_default_timezone_set("Europe/Berlin");
                }
                $expiration = date_timestamp_get(new DateTime($expiration));
            }
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Speichere Authorization-Objekt und Zuordnung für das Plugin-Backend.", JTLLOG_LEVEL_DEBUG);
            $database->saveAuthorization(
                $orid,
                $amazonAuthorizationId,
                $authorizationDetails['AuthorizationAmount']['Amount'],
                $authorizationDetails['AuthorizationAmount']['CurrencyCode'],
                (int)$captureOnAuth,
                $authorizationDetails['CapturedAmount']['Amount'],
                $authorizationDetails['CapturedAmount']['CurrencyCode'],
                $authorizationStatus['State'],
                isset($authorizationStatus['ReasonCode']) ? $authorizationStatus['ReasonCode'] : '',
                $expiration
            );
        }

        /*
         * Save additional bestellung data in database...
         */
        if (!empty($amazonAuthorizationId) && ($authorizationStatus['State'] === S360_LPA_STATUS_OPEN || ($authorizationStatus['State'] === S360_LPA_STATUS_CLOSED && $authorizationStatus['ReasonCode'] === S360_LPA_REASON_MAX_CAPTURES_PROCESSED))) {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Bestellung erfolgreich autorisiert und die Autorisierung ist {$authorizationStatus['State']}.", JTLLOG_LEVEL_DEBUG);
            // authorization is successfully completed.
            $rechnungsAdresse = null;
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Bestellung ist autorisiert, hole daher Rechnungsdaten von Amazon Pay.", JTLLOG_LEVEL_DEBUG);
            // we can request the billing address immediately
            $adapter = new LPAAdapter();
            $billingaddress = $adapter->getRemoteAuthorizationDetails($amazonAuthorizationId);
            $billingaddress = $billingaddress['AuthorizationBillingAddress'];
            Jtllog::writeLog("LPA: LPA-AJAX-CONFIRM-$orid: Rechnungsadresse von Amazon Pay empfangen.", JTLLOG_LEVEL_DEBUG);
            if (!empty($billingaddress)) {
                $rechnungsAdresse = LPAHelper::convertBillingAddressFromAmazonToJtl($billingaddress, $buyer['Email']);
                if (!empty($rechnungsAdresse)) {
                    Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Rechnungsadresse konvertiert.", JTLLOG_LEVEL_DEBUG);
                } else {
                    Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Rechnungsadresse konnte nicht konvertiert werden, wird ignoriert.", JTLLOG_LEVEL_DEBUG);
                }
            } else {
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Keine Rechnungsadresse von Amazon Pay empfangen.", JTLLOG_LEVEL_DEBUG);
            }
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Setze Bestellung auf autorisiert.", JTLLOG_LEVEL_DEBUG);
            $database->setBestellungAuthorized($orid, $rechnungsAdresse);

            if ($bezahlt) {
                // on immediate capture and successful authorization we assume that the order is paid.
                // Capture information is acquired via IPN/Cron... however, we already get the CaptureID in the IdList of the Authorization Details.
                // We can also already request the Billing Address now (we did this in the block before).
                // CaptureId in ID List
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Da Bestellung als bezahlt angesehen wird, wird ein Proforma-Capture-Objekt in der Datenbank angelegt.", JTLLOG_LEVEL_DEBUG);

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
                    Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Speichere Capture-Objekt: " . print_r($cap, true), JTLLOG_LEVEL_DEBUG);

                    $database->saveCaptureObject($cap);
                }
            }
        } else {
            Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Bestellung wird als noch nicht bezahlt angesehen (manuelle oder asynchrone Autorisation) und Autorisation wird auf PENDING gesetzt.", JTLLOG_LEVEL_DEBUG);

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
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Bestellung ist zwar noch nicht autorisiert, aber durch Sofort-Capture kann schon ein Capture-Objekt angelegt werden.", JTLLOG_LEVEL_DEBUG);

                $captureIdList = $authorizationDetails['IdList']['member'];
                if (!is_array($captureIdList) && !empty($captureIdList)) {
                    $captureIdList = array($captureIdList);
                }
                Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Capture-Ids zum Order: " . print_r($captureIdList, true), JTLLOG_LEVEL_DEBUG);

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
                    Jtllog::writeLog("LPA: PROCESS SUCCESS $orid: Speichere Capture-Objekt: " . print_r($cap, true), JTLLOG_LEVEL_DEBUG);
                    $database->saveCaptureObject($cap);
                }
            }
        }
        // success, redirect to complete page
        header('Location: ' . LPALinkHelper::getFrontendLinkUrl(S360_LPA_FRONTEND_LINK_COMPLETE));
        exit();
    }
} catch (Exception $ex) {
    /**
     * Handle $ex->getCode() to determine the appropriate error message to display.
     */
    $errorCode = $ex->getCode();
    $errorMessage = '';
    $errorTryAgain = false;
    switch($errorCode) {
        case S360_LPA_EXCEPTION_CODE_GENERIC:
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_generic'];
            $errorTryAgain = false;
            break;
        case S360_LPA_EXCEPTION_CODE_HARD_DECLINE:
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_confirmation_hard_decline'];
            $errorTryAgain = false;
            break;
        case S360_LPA_EXCEPTION_CODE_CURRENCY_INVALID:
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_currency_hint_multicurrency'];
            $errorTryAgain = true;
            break;
        case S360_LPA_EXCEPTION_CODE_ORDER_AMOUNT_CHANGED:
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_order_amount'];
            $errorTryAgain = true;
            break;
        case S360_LPA_EXCEPTION_CODE_ORDER_NOT_OPEN:
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_generic'];
            $errorTryAgain = true;
            break;
        case S360_LPA_EXCEPTION_CODE_SOFT_DECLINE:
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_confirmation_soft_decline'];
            $errorTryAgain = true;
            break;
        default:
            Jtllog::writeLog('LPA: PROCESS SUCCESS: Unerwartete Exception: '.$ex->getMessage(), JTLLOG_LEVEL_DEBUG);
            $errorMessage = $lpaPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_generic'];
            $errorTryAgain = false;
            break;
    }
    if(!isset($orid)) {
        $orid = null;
    }
    $lpaProcessingError = array(
        'tryagain' => $errorTryAgain,
        'message' => $errorMessage,
        'code' => $errorCode,
        'orid' => $orid
    );
    $_SESSION['lpa_processing_error'] = $lpaProcessingError;
    header('Location: ' . LPALinkHelper::getFrontendLinkUrl(S360_LPA_FRONTEND_LINK_CHECKOUT));
    exit();
}