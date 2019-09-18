<?php

try {
    // benötigt, um alle JTL-Funktionen zur Verfügung zu haben
    require_once(dirname(__FILE__) . '/../../../../../../globalinclude.php');
    require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
    require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");

    $oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
    require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');

    // die Antwort ist im JSON Format
    header('Content-Type: application/json');


    if (!$oPlugin || $oPlugin->kPlugin == 0) {
        Jtllog::writeLog('LPA: Fehler beim Prüfen der MWS-Zugangsdaten: Plugin-Objekt konnte nicht geladen werden!', JTLLOG_LEVEL_ERROR);
        echo json_encode(array('status' => 'error'));
        return;
    }

    $controller = new LPAController();

    /*
     * First load the current config.
     */
    $config = $controller->getInitialConfig();

    /*
     * And override some settings with the values given from the JS.
     */
    $config['merchant_id'] = StringHandler::filterXSS($_POST[S360_LPA_CONFKEY_MERCHANT_ID]);
    $config['access_key'] = StringHandler::filterXSS($_POST[S360_LPA_CONFKEY_ACCESS_KEY]);
    $config['secret_key'] = StringHandler::filterXSS($_POST[S360_LPA_CONFKEY_SECRET_KEY]);

    $config['region'] = StringHandler::filterXSS($_POST[S360_LPA_CONFKEY_REGION]);
    $config['sandbox'] = (StringHandler::filterXSS($_POST[S360_LPA_CONFKEY_ENVIRONMENT]) === 'sandbox');

    $config['client_id'] = StringHandler::filterXSS($_POST[S360_LPA_CONFKEY_CLIENT_ID]);

    /*
     * Get client instance for modified config.
     */
    $client = $controller->getClient($config);

    /*
     * Set request with specific, non-existent order reference id.
     */
    $getOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'],
        'amazon_order_reference_id' => 'S00-0000000-0000000'
    );

    /*
     * Send off the request - the result is always an error.
     */
    $checkResult = 'unknown';

    $result = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
    $resultObj = $result->toArray();


    if (isset($resultObj['Error'])) {
        $errorCode = $resultObj['Error']['Code'];
        $errorMessage = $resultObj['Error']['Message'];
        if ($errorCode === 'InvalidOrderReferenceId') {
            // Check was successful!
            $checkResult = 'success';
        } elseif ($errorCode === 'InvalidAccessKeyId') {
            $checkResult = 'accessKey';
        } elseif ($errorCode === 'SignatureDoesNotMatch') {
            $checkResult = 'secretKey';
        } elseif ($errorCode === 'InvalidParameterValue' && strstr($errorMessage, 'Invalid seller id:')) {
            $checkResult = 'merchantId';
        } else {
            Jtllog::writeLog('LPA: Unbekannter MWS-Fehler beim Prüfen der MWS-Zugangsdaten: ' . $errorCode . ', ' . $errorMessage, JTLLOG_LEVEL_ERROR);
            $checkResult = 'other';
        }
    }

    if ($checkResult === 'success') {
        echo json_encode(array('status' => 'success'));
    } else {
        echo json_encode(array('status' => 'fail', 'error' => $checkResult));
    }
} catch (Exception $e) {
    Jtllog::writeLog('LPA: Unbekannter technischer Fehler beim Prüfen der MWS-Zugangsdaten: ' . $e->getCode() . ', ' . $e->getMessage(), JTLLOG_LEVEL_ERROR);
    $checkResult = 'technicalError';
    echo json_encode(array('status' => 'fail', 'error' => $checkResult));
}