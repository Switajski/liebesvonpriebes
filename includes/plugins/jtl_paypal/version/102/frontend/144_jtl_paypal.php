<?php
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
    if (isset($_SESSION['jtl_paypal']['Token']) && $_SESSION['jtl_paypal']['Token'] === $_GET['token']) {
        global $moduleId, $order, $paymentHash;
        $moduleId    = $_SESSION['Zahlungsart']->cModulId;
        $order       = null;
        $paymentHash = null;
        $order       = new Bestellung($_SESSION['jtl_paypal']['kBestellung'], true);
    }
}
