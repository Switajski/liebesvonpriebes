<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * HOOK_NOTIFY_HASHPARAMETER_DEFINITION.
 *
 * called when paypal return url is visited.
 * should contain the token as GET parameter
 *
 * used to initialize the plugin and call handleNotification()
 */
if (isset($_GET['payment_method']) && $_GET['payment_method'] === 'jtl_paypal' && isset($_GET['token'])) {
    $session = Session::getInstance();
    require_once PFAD_ROOT.PFAD_INCLUDES.'bestellabschluss_inc.php';
    if (isset($_SESSION['jtl_paypal']['Token']) && $_SESSION['jtl_paypal']['Token'] === $_GET['token']) {
        global $moduleId, $order, $paymentHash;

        $paymentHash = null;
        $moduleId = $_SESSION['Zahlungsart']->cModulId;
        $order = finalisiereBestellung();

        $session->cleanUp();
    }
}
