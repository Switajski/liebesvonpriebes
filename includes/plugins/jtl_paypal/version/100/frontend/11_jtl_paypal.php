<?php
/**
 * HOOK_BESTELLVORGANG_PAGE_STEPVERSAND_PLAUSI.
 */
if (isset($_SESSION['paypalexpress']) && isset($_SESSION['Versandart']->kVersandart)) {
    require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalExpress.class.php';

    $paypalexpress = new PayPalExpress();
    $paypalexpress->zahlungsartsession($_SESSION['Versandart']->kVersandart);

    /*
    if (isset($_SESSION['Zahlungsart']->cAufpreisTyp) && $_SESSION['Zahlungsart']->cAufpreisTyp === 'prozent') {
        $Aufpreis = ($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array('1'), 1) * $_SESSION['Zahlungsart']->fAufpreis) / 100.0;
    } else {
        $Aufpreis = (isset($_SESSION['Zahlungsart']->fAufpreis)) ? $_SESSION['Zahlungsart']->fAufpreis : 0;
    }
    if ($Aufpreis != 0) {
        $_SESSION['Warenkorb']->erstelleSpezialPos(
            $_SESSION['Zahlungsart']->angezeigterName,
            1,
            $Aufpreis,
            $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse(),
            C_WARENKORBPOS_TYP_ZAHLUNGSART,
            true
        );
    }
    */
}
