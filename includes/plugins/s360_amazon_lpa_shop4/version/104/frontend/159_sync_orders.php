<?php

/*
 * Solution 360 GmbH
 *
 * Triggered when orders are synched FROM WaWi TO Shop.
 * $oBestellung is the order before the sync
 * $oBestellungNeu is loaded from the database which was updated during the sync.
 */
require_once(dirname(__FILE__) . '/lib/lpa_defines.php');
require_once(dirname(__FILE__) . '/lib/class.LPADatabase.php');
require_once(dirname(__FILE__) . '/lib/class.LPAAdapter.php');

if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_GENERAL_ACTIVE] === '0') {
    /*
     * Plugin disabled, do nothing.
     */
    return;
}

$oBestellung = $args_arr['oBestellung'];
$oBestellungNeu = new Bestellung($oBestellung->kBestellung);

if ($oBestellung->cZahlungsartName != 'Amazon Payments') {
    // ignore anything not paid with Amazon Payments
    return;
}

Jtllog::writeLog("LPA: WaWi-Abgleich für Bestellung {$oBestellungNeu->cBestellNr} gestartet.", JTLLOG_LEVEL_DEBUG);

if ($oBestellung->cStatus === $oBestellungNeu->cStatus) {
    // do nothing if the state did not change in the wawi
    Jtllog::writeLog("LPA: WaWi-Abgleich für Bestellung {$oBestellungNeu->cBestellNr} beendet: keine Statusänderung.", JTLLOG_LEVEL_DEBUG);
    return;
}

$database = new LPADatabase();
$adapter = new LPAAdapter();

$capMode = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE];

/*
 * Get state(s) that trigger captures.
 */
$capStates = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTURESTATE];
$capStates = str_split($capStates);


if (in_array($oBestellungNeu->cStatus, $capStates)) {
    /*
     * The order was DELIVERED, we may now capture the data.
     * However, we only capture, if capturing is NOT set to manual mode.
     */

    $cTmpStatus = $oBestellungNeu->cStatus;
    //Bestellstatus vorerst zurücksetzen
    $oBestellungNeu->cStatus = $oBestellung->cStatus;
    $oBestellungNeu->updateInDB();

        try {
            $orid = $database->getOrderReferenceIdByBestellNr($oBestellungNeu->cBestellNr);
            if ($capMode === 'shipment') {
                Jtllog::writeLog("LPA: WaWi-Abgleich für Bestellung {$oBestellungNeu->cBestellNr}: Bestellung wird gegen Amazon gecaptured.", JTLLOG_LEVEL_DEBUG);
                $auths = $database->getAuthorizationsForOrder($orid);
                foreach ($auths as $auth) {
                    if ($auth->cAuthorizationStatus === S360_LPA_STATUS_OPEN) {
                        $adapter->capture($auth->cAuthorizationId);
                        Jtllog::writeLog("LPA: WaWi-Abgleich für Bestellung {$oBestellungNeu->cBestellNr}: Capture-Anfrage wurde erfolgreich an Amazon übergeben.", JTLLOG_LEVEL_DEBUG);
                        /*
                         * setBestellungCaptured is not called here, because we will receive an IPN and/or update the capture state individually with the cron running on an Webshop-Abgleich.
                         * Right after the capture request the capture-object is in a Pending state, anyway.
                         */
                    } else {
                        Jtllog::writeLog("LPA: WaWi-Abgleich: Authorization {$auth->cAuthorizationId} konnte (noch) nicht gecaptured werden, weil der Status nicht OPEN ist.", JTLLOG_LEVEL_DEBUG);
                    }
                }
            } else {
                Jtllog::writeLog("LPA: WaWi-Abgleich: Captures für OrderReference {$orid} werden nicht durch WaWi-Abgleich ausgelöst, sondern manuell oder bei der Autorisierung.", JTLLOG_LEVEL_DEBUG);
            }

            //Bestellstatus wieder auf den capstatus setzen
            $oBestellungNeu->cStatus = $cTmpStatus;
            $oBestellungNeu->updateInDB();
        } catch (Exception $e) {
            Jtllog::writeLog('LPA: WaWi-Abgleich Fehler: ' . $e->getMessage(), JTLLOG_LEVEL_ERROR);
        }
} elseif ($oBestellungNeu->cStatus === '-1') {
    /*
     * Order was canceled - we also cancel the order against Amazon
     */
    Jtllog::writeLog("LPA: WaWi-Abgleich für Bestellung {$oBestellungNeu->cBestellNr}: Bestellung storniert - wird gegen Amazon gecanceled.", JTLLOG_LEVEL_DEBUG);
    $orid = $database->getOrderReferenceIdByBestellNr($oBestellungNeu->cBestellNr);
    $adapter->cancelOrder($orid, 'Storno');
} else {
    Jtllog::writeLog("LPA: WaWi-Abgleich für Bestellung {$oBestellungNeu->cBestellNr} beendet: kein relevanter Status erkannt", JTLLOG_LEVEL_DEBUG);
}
