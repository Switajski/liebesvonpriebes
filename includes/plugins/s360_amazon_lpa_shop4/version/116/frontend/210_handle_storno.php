<?php

/*
 * Solution 360 GmbH
 *
 * Triggered when orders are synched FROM WaWi TO Shop and being cancelled (STORNO).
 *
 * Call:
 *
 * executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
 *               'oBestellung' => &$bestellungTmp,
 *               'oKunde'      => &$kunde,
 *               'oModule'     => $oModule
 *           ]);
 */
try {
    require_once(__DIR__ . '/lib/lpa_defines.php');
    require_once(__DIR__ . '/lib/class.LPADatabase.php');
    require_once(__DIR__ . '/lib/class.LPAAdapter.php');

    if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_GENERAL_ACTIVE] === '0'
        || !isset($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_STORNO])
        || $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_STORNO] === 'NO') {
        /*
         * Plugin or automatic storno and mail sending disabled, do nothing.
         */
        return;
    }

    // oBestellung is the order object after fuelleBestellung() but without the changed order status!
    $oBestellung = $args_arr['oBestellung'];
    $oKunde = $args_arr['oKunde'];

    if ($oBestellung->cZahlungsartName != 'Amazon Pay' && $oBestellung->cZahlungsartName != 'Amazon Payments') {
        // ignore anything not paid with Amazon Pay
        return;
    }

    Jtllog::writeLog("LPA: WaWi-Abgleich für Stornierte Bestellung {$oBestellung->cBestellNr} gestartet.", JTLLOG_LEVEL_DEBUG);

    // send storno mail only if enabled by setting, note that this mail is independent of the automatic cancellation of the order itself
    if (!empty($oKunde->cMail)
        && isset($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_STORNO])
        && ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_STORNO] === 'YM' || $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_STORNO] === 'NM')
    ) {
        Jtllog::writeLog("LPA: Versende Storno-Email an Kunden.", JTLLOG_LEVEL_DEBUG);
        $oMail = new stdClass();
        $oMail->tkunde = $oKunde;
        $oMail->tbestellung = $oBestellung;
        sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $oMail);
    }

    $database = new LPADatabase();
    $adapter = new LPAAdapter(S360_LPA_ADAPTER_MODE_BACKEND);

    $order = $database->getOrderByJTLOrderId($oBestellung->kBestellung);
    if (!empty($order)) {
        $orid = $order->cOrderReferenceId;
        // Check if the order has captures (Completed, Closed or Pending) against it - it cannot be cancelled if that is the case! (Note: Declined Captures are allowed!)
        $auths = $database->getAuthorizationsForOrder($orid);
        $cancelPossible = true;
        if (!empty($auths)) {
            foreach ($auths as $auth) {
                $caps = $database->getCapturesForAuthorization($auth->cAuthorizationId);
                if (!empty($caps)) {
                    foreach ($caps as $cap) {
                        if ($cap->cCaptureStatus === S360_LPA_STATUS_COMPLETED || $cap->cCaptureStatus === S360_LPA_STATUS_CLOSED || $cap->cCaptureStatus === S360_LPA_STATUS_PENDING) {
                            // there are captures against the order that were not declined... we cannot cancel the order right now!
                            Jtllog::writeLog("LPA: Es sind bereits Zahlungseinzüge / Captures für Bestellnummer " . $oBestellung->cBestellNr . " aktiv. Automatische Stornierung nicht möglich.", JTLLOG_LEVEL_NOTICE);
                            $cancelPossible = false;
                        }
                    }
                }
            }
        }
        if($cancelPossible) {
            $adapter->cancelOrder($orid, 'Storno', false); // call adapter, don't make it throw an exception if an error occurs
            // To consider: We could refresh the order status here or just wait for the plugin to do it on the next refresh / IPN
        }
    } else {
        Jtllog::writeLog("LPA: Es konnte keine Amazon-Referenz zu Bestellnummer " . $oBestellung->cBestellNr . " gefunden werden. Automatische Stornierung nicht möglich.", JTLLOG_LEVEL_NOTICE);
    }
} catch (Exception $ex) {
    Jtllog::writeLog("LPA: Exception in Hook 213 (Storno Bestellung): " . $ex->getMessage() . "(" . $ex->getCode() . ")", JTLLOG_LEVEL_ERROR);
}
