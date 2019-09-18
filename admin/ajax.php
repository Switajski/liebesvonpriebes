<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
$smarty->setForceCompile(true);
require PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.JSONAPI.php';

$oAccount->permission('BOXES_VIEW', true, true);

$jsonAPI = JSONAPI::getInstance();

if (isset($_GET['query'], $_GET['type']) && validateToken()) {
    switch ($_GET['type']) {
        case 'product' :
            die($jsonAPI->getProducts());
        case 'category':
            die($jsonAPI->getCategories());
        case 'page':
            die($jsonAPI->getPages());
        case 'manufacturer':
            die($jsonAPI->getManufacturers());
        case 'TwoFA':
            $oTwoFA = new TwoFA();
            $oTwoFA->setUserByName($_GET['userName']);

            $oUserData           = new stdClass();
            $oUserData->szSecret = $oTwoFA->createNewSecret()->getSecret();
            $oUserData->szQRcode = $oTwoFA->getQRcode();
            $szJSONuserData      = json_encode($oUserData);

            die($szJSONuserData);
        case 'TwoFAgenEmergCodes':
            $oTwoFA = new TwoFA();
            $oTwoFA->setUserByName($_GET['userName']);

            // create, what the user can print out
            $szText  = '<h4>JTL-shop Backend Notfall-Codes</h4>';
            $szText .= 'Account: <b>' . $oTwoFA->getUserTuple()->cLogin . '</b><br>';
            $szText .= 'Shop: <b>' . $oTwoFA->getShopName() . '</b><br><br>';

            $oTwoFAgenEmergCodes = new TwoFAEmergency();
            $oTwoFAgenEmergCodes->removeExistingCodes($oTwoFA->getUserTuple());

            $vCodes = $oTwoFAgenEmergCodes->createNewCodes($oTwoFA->getUserTuple());

            $szText      .= '<font face = "monospace" size = "+1">';
            $nCol         = 0;
            $iCodesLength = count($vCodes);
            for ($i = 0; $i < $iCodesLength; $i++) {
                if (1 > $nCol) {
                    $szText .= '<span style="padding:3px;">' . $vCodes[$i] . '</span>';
                    $nCol++;
                } else {
                    $szText .= $vCodes[$i] . '<br>';
                    $nCol    = 0;
                }
            }
            $szText .= '</font>';

            die($szText);
        default:
            die();
    }
}
