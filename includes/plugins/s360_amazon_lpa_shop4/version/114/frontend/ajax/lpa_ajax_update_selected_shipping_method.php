<?php

/*
 * Solution 360 GmbH
 *
 * Updates the selected shipping method:
 * - sets it in the session
 * - updates the order reference and sets the amount of it
 */

try {
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
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
    require_once($oPlugin->cFrontendPfad . 'lib/class.LPACurrencyHelper.php');

    $session = Session::getInstance();

    unset($_SESSION['Versandart']);

// die Antwort ist im JSON Format
    header('Content-Type: application/json');


// Shipping method specific cart positions should be reset
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);

    $orid = StringHandler::filterXSS($_REQUEST['orid']);
    $kVersandart = StringHandler::filterXSS($_REQUEST['kVersandart']);

    if (empty($orid) || empty($kVersandart)) {
        return;
    }


    $oVersandart = new Versandart($kVersandart);

    // add shipping time to cart and session, get location data
    $controller = new LPAController();
    $config = $controller->getConfig();
    $client = $controller->getClient();

    $getOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'],
        'amazon_order_reference_id' => $orid
    );

    if (isset($_COOKIE[S360_LPA_ADDRESS_CONSENT_TOKEN_COOKIE])) {
        $token = $_COOKIE[S360_LPA_ADDRESS_CONSENT_TOKEN_COOKIE];
        $getOrderReferenceDetailsParameter['AddressConsentToken'] = $token;
    }

    $result = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
    $result = $result->toArray();

    if (isset($result['Error'])) {
        throw new Exception($result['Error']['Message']);
    }

    $result = $result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];

    $cPLZ = $result['PostalCode'];
    $cLand = $result['CountryCode'];

    // Add article dependend costs
    $arrArtikelabhaengigeVersandkosten = VersandartHelper::gibArtikelabhaengigeVersandkostenImWK($cLand, $_SESSION['Warenkorb']->PositionenArr);
    foreach ($arrArtikelabhaengigeVersandkosten as $oVersandPos) {
        $_SESSION['Warenkorb']->erstelleSpezialPos($oVersandPos->cName, 1, $oVersandPos->fKosten, $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLand), C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG, false);
    }


    Jtllog::writeLog('LPA: Hole Versandzuschlag für: Land: ' . $cLand . ', PLZ: ' . $cPLZ . ', Versandart-Objekt: ' . print_r($oVersandart, true), JTLLOG_LEVEL_DEBUG);

    $oVersandart->Zuschlag = gibVersandZuschlag($oVersandart, $cLand, $cPLZ);

    Jtllog::writeLog('LPA: Geholter Versandzuschlag: ' . print_r($oVersandart->Zuschlag, true), JTLLOG_LEVEL_DEBUG);

    if (!isset($oVersandart->Zuschlag)) {
        $oVersandart->Zuschlag = new stdClass();
        $oVersandart->Zuschlag->fZuschlag = 0;
    }
    $oVersandart->fEndpreis = berechneVersandpreis($oVersandart, $cLand, null);
    if (isset($_SESSION['VersandKupon']) && $_SESSION['VersandKupon']) {
        $oVersandart->fEndpreis = 0;
    }

    if ($oVersandart->fEndpreis != -1) {
        $oVersandartpos = new stdClass();
        $oVersandartpos->cName = array();
        foreach ($_SESSION["Sprachen"] as $sprache) {
            $oVersandartName = Shop::DB()->query("select cName, cHinweistext from tversandartsprache where kVersandart='{$oVersandart->kVersandart}' and cISOSprache='{$sprache->cISO}'", 1);
            $oVersandartpos->cName[$sprache->cISO] = $oVersandartName->cName;
            $oVersandart->angezeigterName[$sprache->cISO] = $oVersandartName->cName;
            $oVersandart->angezeigterHinweistext[$sprache->cISO] = $oVersandartName->cHinweistext;
        }

        $bSteuerPos = ($oVersandart->eSteuer === "netto") ? false : true;

        $versandartCost = $oVersandart->fEndpreis - $oVersandart->Zuschlag->fZuschlag;
        if ($oVersandart->fEndpreis == 0) {
            // If Versandart is free of charge, fEndpreis does NOT contain the additional island costs and we may not subtract the additional charge, otherwise it does contain the additional charge which must be deducted
            $versandartCost = 0;
        }
        $_SESSION['Warenkorb']->erstelleSpezialPos($oVersandartpos->cName, 1, $versandartCost, $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLand), C_WARENKORBPOS_TYP_VERSANDPOS, true, $bSteuerPos);

        // Add Zuschlag if it is greater than 0
        if (isset($oVersandart->Zuschlag->fZuschlag) && $oVersandart->Zuschlag->fZuschlag != 0) {
            //posname lokalisiert ablegen
            $Spezialpos = new stdClass();
            $Spezialpos->cName = [];
            foreach ($_SESSION['Sprachen'] as $sprache) {
                $name_spr = Shop::DB()->select(
                    'tversandzuschlagsprache', 'kVersandzuschlag', (int)$oVersandart->Zuschlag->kVersandzuschlag, 'cISOSprache', $sprache->cISO, null, null, false, 'cName'
                );
                $Spezialpos->cName[$sprache->cISO] = $name_spr->cName;
            }
            $_SESSION['Warenkorb']->erstelleSpezialPos(
                $Spezialpos->cName, 1, $oVersandart->Zuschlag->fZuschlag, $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLand), C_WARENKORBPOS_TYP_VERSANDZUSCHLAG, true, $bSteuerPos
            );
        }
        $_SESSION['Versandart'] = $oVersandart;
    }

    // update orderreference details with the new amount, amount is always in default currency
    $amount = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);

    /*
     * MultiCurrency handling
     */
    $presentmentCurrency = LPACurrencyHelper::getCurrentCurrency();
    if (!LPACurrencyHelper::isSupportedCurrency($presentmentCurrency->cISO)) {
        // Error - the user seems to have changed the session currency to an unsupported currency - abort
        throw new Exception("Currency '{$presentmentCurrency->cISO}' is invalid for Amazon Pay.");
    }

    // Amazon Pay needs to total amount for the order based on the presentment currency
    $currencyISO = $presentmentCurrency->cISO;
    $defaultCurrency = LPACurrencyHelper::getDefaultCurrency();
    $orderAmount = LPACurrencyHelper::convertAmount($amount, $defaultCurrency->cISO, $presentmentCurrency->cISO);

    $setOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'], //your merchant/seller ID
        'amazon_order_reference_id' => $orid, //unique identifier for the order reference
        'amount' => $orderAmount,
        'currency_code' => $currencyISO
    );

    $orderReferenceDetails = $client->setOrderReferenceDetails($setOrderReferenceDetailsParameter);

    echo json_encode(array('amount' => $amount, 'wkpos' => '', 'currency' => $currencyISO, 'orderAmount' => $orderAmount));
    return;
} catch (Exception $e) {
    Jtllog::writeLog('LPA: Fehler beim Setzen der Warenkorbsumme: ' . $e->getMessage());
    return;
}