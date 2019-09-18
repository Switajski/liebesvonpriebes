<?php

/*
 * Solution 360 GmbH
 *
 * Takes an order reference and computes the valid delivery types (and sets the delivery type, if only one is available).
 */
// benötigt, um alle JTL-Funktionen zur Verfügung zu haben


try {
    if(!isset($_REQUEST['lpa_ajax'])) {
        header("HTTP/1.1 400 Bad Request");
        exit(0);
    }
    require_once(dirname(__FILE__) . '/../lib/lpa_includes.php');
    require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
    require_once(PFAD_ROOT . PFAD_INCLUDES . "smartyInclude.php");
    require_once(PFAD_ROOT . PFAD_INCLUDES . "bestellvorgang_inc.php");
    $oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
    require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');

    $session = Session::getInstance();

// die Antwort ist im JSON Format
    header('Content-Type: application/json');


// reset
    unset($_SESSION['Zahlungsart']);
    unset($_SESSION['Lieferadresse']);
    unset($_SESSION['Versandart']);


    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);

    $orid = StringHandler::filterXSS($_REQUEST['orid']);

    if (empty($orid)) {
        echo json_encode(array('status' => 'error', 'code' => 'technical', 'message' => 'Order Reference ID is missing.'));
        return;
    }

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


    /**
     * getOrderReferenceDetails returned the full address, if the address consent token was sent
     */
    if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_EXCLUDE_PACKSTATION] === '1') {
        // check if Packstation was selected as delivery address.
        $addressString = '';
        $addressString .= isset($result['AddressLine1']) && is_string($result['AddressLine1']) ? $result['AddressLine1'] : '';
        $addressString .= isset($result['AddressLine2']) && is_string($result['AddressLine2']) ? $result['AddressLine2'] : '';
        $addressString .= isset($result['AddressLine3']) && is_string($result['AddressLine3']) ? $result['AddressLine3'] : '';
        if (stripos($addressString, 'packstation') !== FALSE) {
            /*
             * Found packstation in delivery address, but not allowed!
             */
            echo json_encode(array('status' => 'error', 'code' => 'packstation'));
            return;
        }
    }

    if (!isset($result['PostalCode']) || !isset($result['CountryCode']) || !isset($result['City'])) {
        echo json_encode(array('status' => 'error', 'code' => 'address'));
        return;
    }
    $cPLZ = $result['PostalCode'];
    $cLand = $result['CountryCode'];
    $cOrt = $result['City'];

    /*
     * To compute the tax rate correctly, we have to set the Lieferadresse temporarily so the function in tools.Global.php can use it.
     */
    $oAdresse = new stdClass();
    $oAdresse->cLand = $cLand;
    $_SESSION['Lieferadresse'] = $oAdresse;
    setzeSteuersaetze();
    unset($_SESSION['Lieferadresse']);

    pruefeVersandkostenfreiKuponVorgemerkt();

    $oVersandart_arr = VersandartHelper::getPossibleShippingMethods(StringHandler::filterXSS($cLand), StringHandler::filterXSS($cPLZ), VersandartHelper::getShippingClasses($_SESSION['Warenkorb']), Kundengruppe::getCurrent());

    /*
     * Filter excluded delivery methods
     */
    $excluded_delivery_methods = array();
    $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE "' . S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS . '" LIMIT 1', 1);
    if (!empty($result)) {
        $excluded_delivery_methods = explode(",", $result->cWert);
    }
    $allowed_delivery_methods = array();
    foreach ($oVersandart_arr as $versandart) {
        if (!in_array($versandart->kVersandart, $excluded_delivery_methods)) {
            $allowed_delivery_methods[] = $versandart;
        }
    }

    $einstellungen_shop = Shop::getSettings(
                    array(
                        CONF_GLOBAL
                    )
    );

    Shop::Smarty()->assign('oVersandart_arr', $allowed_delivery_methods)
                  ->assign('Einstellungen', $einstellungen_shop);

    $snippet = '';

    if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_shipping_method_selection_snippet_custom.tpl')) {
        $snippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/lpa_shipping_method_selection_snippet_custom.tpl');
    } else {
        $snippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/lpa_shipping_method_selection_snippet.tpl');
    }

    echo json_encode(array('status' => 'success', 'html' => utf8_encode($snippet)));
    return;
} catch (Exception $ex) {
    Jtllog::writeLog('LPA: Fehler beim Setzen der Versandart: ' . $ex->getMessage(), JTLLOG_LEVEL_NOTICE);
    echo json_encode(array('status' => 'error', 'code' => 'technical', 'message' => $ex->getMessage()));
    return;
}