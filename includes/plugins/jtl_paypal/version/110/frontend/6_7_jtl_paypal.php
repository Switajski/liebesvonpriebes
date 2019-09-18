<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE
 * HOOK_BESTELLVORGANG_PAGE_STEPVERSAND.
 */
global $smarty, $step;

/*
if (isset($_SESSION['paypalexpress']) && !isset($_SESSION['reshash']['EMAIL'])) {
    unset($_SESSION['reshash']);
    unset($_SESSION['paypalexpress']);
}

if (isset($_SESSION['paypalexpress']) && $step === 'Lieferadresse') {
    $step                    = 'Bestaetigung';
    $GLOBALS['hinweis']      = $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_lieferadresse_nicht_aenderbar'];
    $_SESSION['Zahlungsart'] = $_SESSION['paypalexpress']->sZahlungsart;
    $_SESSION['Versandart']  = $_SESSION['paypalexpress']->sVersandart;
}
*/

if (isset($_SESSION['paypalexpress']) && $step === 'Versand') {
    unset($_SESSION['TrustedShopsZahlung']);
    pruefeVersandkostenfreiKuponVorgemerkt();
    $lieferland = $_SESSION['Lieferadresse']->cLand;
    if (!$lieferland) {
        $lieferland = $_SESSION['Kunde']->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ;
    if (!$plz) {
        $plz = $_SESSION['Kunde']->cPLZ;
    }

    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
    if (!$kKundengruppe) {
        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
    }
    $shippingClasses = VersandartHelper::getShippingClasses($_SESSION['Warenkorb']);
    $oVersandart_arr = VersandartHelper::getPossibleShippingMethods($lieferland, $plz, $shippingClasses,
        $kKundengruppe);
    $oVerpackung_arr = gibMoeglicheVerpackungen($_SESSION['Kundengruppe']->kKundengruppe);

    foreach ($oVersandart_arr as $key => $oVersandart) {
        $pp_in            = false;
        $oZahlungsart_arr = gibZahlungsarten($oVersandart->kVersandart, $_SESSION['Kundengruppe']->kKundengruppe);
        foreach ($oZahlungsart_arr as $oZahlungsart) {
            if ($oZahlungsart->kZahlungsart == $_SESSION['paypalexpress']->sZahlungsart->kZahlungsart) {
                $pp_in = true;
            }
        }
        if ($pp_in === false) {
            unset($oVersandart_arr[$key]);
        }
    }
    sort($oVersandart_arr);

    if ((is_array($oVersandart_arr) && count($oVersandart_arr) > 1) ||
        (is_array($oVersandart_arr) && count($oVersandart_arr) === 1 && is_array($oVerpackung_arr) && count($oVerpackung_arr) > 0)
    ) {
        $smarty->assign('Versandarten', $oVersandart_arr);
        $smarty->assign('Verpackungsarten', $oVerpackung_arr);
    } elseif (is_array($oVersandart_arr) && count($oVersandart_arr) === 1 && (is_array($oVerpackung_arr) && count($oVerpackung_arr) === 0)) {
        pruefeVersandartWahl($oVersandart_arr[0]->kVersandart);
    } elseif (!is_array($oVersandart_arr) || count($oVersandart_arr) === 0) {
        Jtllog::writeLog('Es konnte keine Versandart fuer folgende Daten gefunden werden: ' .
            'Lieferland: ' . $lieferland .
            ', PLZ: ' . $plz .
            ', Versandklasse: ' . $shippingClasses .
            ', Kundengruppe: ' . $kKundengruppe, JTLLOG_LEVEL_ERROR);
    }
}
