<?php
/**
 * HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG.
 */
if (isset($_SESSION['paypalexpress'])) {
    $weiterleitung['Zahlungsart']     = $_SESSION['paypalexpress']->sZahlungsart->kZahlungsart;
    $weiterleitung['zahlungsartwahl'] = '1';

    pruefeZahlungsartwahlStep($weiterleitung);
    $_SESSION['paypalexpress']->hasZahlartrausgenommen = 1;

    header('Location: bestellvorgang.php');
} else {
    //remove paypal express from payment methods at normal checkout
    $oZahlungsart_arr = $smarty->get_template_vars('Zahlungsarten');
    if (count($oZahlungsart_arr) > 0) {
        foreach ($oZahlungsart_arr as $key => $oZahlungsart) {
            if (!isset($oZahlungsart->cModulId) || strpos($oZahlungsart->cModulId, 'paypalexpress') === false) {
                $Zahlungsart_arr[] = $oZahlungsart;
            }
        }
        $smarty->assign('Zahlungsarten', $Zahlungsart_arr);
    }
}
