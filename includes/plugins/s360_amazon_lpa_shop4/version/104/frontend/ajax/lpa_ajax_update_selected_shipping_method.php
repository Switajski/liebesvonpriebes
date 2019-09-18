<?php

/*
 * Solution 360 GmbH
 *
 * Updates the selected shipping method:
 * - sets it in the session
 * - updates the order reference and sets the amount of it
 */

try {
// benötigt, um alle JTL-Funktionen zur Verfügung zu haben
    require_once(dirname(__FILE__) . '/../lib/lpa_includes.php');
    require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
    require_once(PFAD_ROOT . PFAD_INCLUDES . "smartyInclude.php");
    require_once(PFAD_ROOT . PFAD_INCLUDES . "bestellabschluss_inc.php");
    $oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
    require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');

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

    $oVersandart->Zuschlag = gibVersandZuschlag($oVersandart, $cLand, $cPLZ);
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
        $_SESSION['Warenkorb']->erstelleSpezialPos($oVersandartpos->cName, 1, $oVersandart->fEndpreis - $oVersandart->Zuschlag->fZuschlag, $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLand), C_WARENKORBPOS_TYP_VERSANDPOS, true, $bSteuerPos);
        $_SESSION['Versandart'] = $oVersandart;
    }

// update orderreference details with the new amount
    $amount = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);

    $currency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
    $currencyISO = $currency->cISO;
    $configCurrencyISO = $controller->getCurrencyCode($config);

    if ($configCurrencyISO !== $currencyISO) {
        /*
         * The standard currency is not the currency the endpoint needs.
         * We need to convert.
         */
        $orderAmount = lpaConvertAmount($amount, $currencyISO, $configCurrencyISO);
    } else {
        $orderAmount = $amount;
    }

    $setOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'], //your merchant/seller ID
        'amazon_order_reference_id' => $orid, //unique identifier for the order reference
        'amount' => $orderAmount,
        'currency_code' => $configCurrencyISO
    );

    $orderReferenceDetails = $client->setOrderReferenceDetails($setOrderReferenceDetailsParameter);

    echo json_encode(array('amount' => $amount, 'wkpos' => ''));
    return;
} catch (Exception $e) {
    Jtllog::writeLog('LPA: Fehler beim Setzen der Warenkorbsumme: ' . $e->getMessage());
    return;
}