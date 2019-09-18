<?php

/*
 * Solution 360 GmbH
 *
 * Takes an order reference and computes the valid delivery types (and sets the delivery type, if only one is available).
 */
// benötigt, um alle JTL-Funktionen zur Verfügung zu haben

try {
    require_once(dirname(__FILE__) . '/../../../../../../globalinclude.php');
    require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
    $oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
    require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
    require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');

    $session = Session::getInstance();

// die Antwort ist im JSON Format
    header('Content-Type: application/json');


    $orid = StringHandler::filterXSS($_REQUEST['orid']);

    if (empty($orid)) {
        return;
    }

    $controller = new LPAController();
    $config = $controller->getConfig();
    $client = $controller->getClient();

    $getOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'],
        'amazon_order_reference_id' => $orid,
        'address_consent_token' => $_COOKIE[S360_LPA_ADDRESS_CONSENT_TOKEN_COOKIE]
    );


    $result = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
    $result = $result->toArray();

    if (isset($result['Error'])) {
        throw new Exception($result['Error']['Message']);
    }

    $result = $result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];

// check if Packstation was selected as address.
    $addressString = '';
    $addressString .= isset($result['AddressLine1']) && is_string($result['AddressLine1']) ? $result['AddressLine1'] : '';
    $addressString .= isset($result['AddressLine2']) && is_string($result['AddressLine2']) ? $result['AddressLine2'] : '';
    $addressString .= isset($result['AddressLine3']) && is_string($result['AddressLine3']) ? $result['AddressLine3'] : '';
    if (stripos($addressString, 'packstation') !== FALSE) {
        /*
         * Found packstation in address, this is never allowed!
         */
        echo json_encode(array('status' => 'error', 'code' => 'packstation'));
        return;
    }

// get the data we need - we need to split name and addressfield from amazon
// get first and last name
    $sFullName = utf8_decode($result['Name']);
    $splitName = explode(" ", $sFullName, 2);
    if (count($splitName) > 1) {
        $sFirstName = $splitName[0];
        $sLastName = $splitName[1];
    } else {
        $sFirstName = "";
        $sLastName = $splitName[0];
    }

    //get street and streetnr 
    $sCompanyName = '';
    $addressLine3 = utf8_decode(isset($result['AddressLine3']) && is_string($result['AddressLine3']) ? $result['AddressLine3'] : '');
    $addressLine2 = utf8_decode(isset($result['AddressLine2']) && is_string($result['AddressLine2']) ? $result['AddressLine2'] : '');
    $addressLine1 = utf8_decode(isset($result['AddressLine1']) && is_string($result['AddressLine1']) ? $result['AddressLine1'] : '');
    if (!empty($addressLine3)) {
        // if the third address line is set, we interpret this line as street and number, and the first two lines as company part
        $split = explode(" ", $addressLine3);
        if (count($split) > 1) {
            $sStreetNr = $split[count($split) - 1];
            unset($split[count($split) - 1]);
            $sStreet = implode(" ", $split);
        } else {
            $sStreet = implode(" ", $split);
            if (strlen($sStreet) > 1) {
                $sStreetNr = substr($sStreet, -1);
                $sStreet = substr($sStreet, 0, -1);
            } else {
                $sStreetNr = "";
            }
        }
        $sCompanyName = trim($addressLine1 . " " . $addressLine2);
    } else if (!empty($addressLine2)) {
        // if no 3rd line is set, but the 2nd line is set, we interpret the second line as street and number, and the first line as company part
        $split = explode(" ", $addressLine2);
        if (count($split) > 1) {
            $sStreetNr = $split[count($split) - 1];
            unset($split[count($split) - 1]);
            $sStreet = implode(" ", $split);
        } else {
            $sStreet = implode(" ", $split);
            if (strlen($sStreet) > 1) {
                $sStreetNr = substr($sStreet, -1);
                $sStreet = substr($sStreet, 0, -1);
            } else {
                $sStreetNr = "";
            }
        }
        $sCompanyName = trim($addressLine1);
    } else {
        // only the first line is set, we interpret it as street and number, and no company name
        $split = explode(" ", $addressLine1);
        if (count($split) > 1) {
            $sStreetNr = $split[count($split) - 1];
            unset($split[count($split) - 1]);
            $sStreet = implode(" ", $split);
        } else {
            $sStreet = implode(" ", $split);
            if (strlen($sStreet) > 1) {
                $sStreetNr = substr($sStreet, -1);
                $sStreet = substr($sStreet, 0, -1);
            } else {
                $sStreetNr = "";
            }
        }
        $sCompanyName = "";
    }

    $address = array();

    $address['vorname'] = utf8_encode($sFirstName);
    $address['nachname'] = utf8_encode($sLastName);
    $address['strasse'] = utf8_encode($sStreet);
    $address['hausnummer'] = utf8_encode($sStreetNr);
    $address['firma'] = utf8_encode($sCompanyName);
    $address['land'] = $result['CountryCode']; // already in UTF-8
    $address['plz'] = $result['PostalCode']; // already in UTF-8
    $address['ort'] = $result['City']; // already in UTF-8
    $address['tel'] = $result['Phone']; // already in UTF-8

    echo json_encode(array('status' => 'success', 'address' => $address));
    return;
} catch (Exception $ex) {
    Jtllog::writeLog('LPA: Fehler bei Auswahl der Versandadresse: ' . $ex->getMessage(), JTLLOG_LEVEL_NOTICE);
    echo json_encode(array('status' => 'error', 'code' => 'technical', 'message' => $ex->getMessage()));
    return;
}