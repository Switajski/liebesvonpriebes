<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Bestellung $Bestellung
 * @param string     $email
 * @param string     $returnURL
 * @return string
 */
function gib_moneybookers_form($Bestellung, $email, $returnURL)
{
    if ($Bestellung->fWarensumme > 0 && $email) {
        return '
    <form action="https://www.skrill.com/app/payment.pl" method="post" target="_blank">
    <input type="hidden" name="pay_to_email" value="' . $email . '">
    <input type="hidden" name="transaction_id" value="' . $Bestellung->cBestellNr . '">
    <input type="hidden" name="status_url" value="' . $email . '">
    <input type="hidden" name="language" value="' . StringHandler::convertISO2ISO639($_SESSION['cISOSprache']) . '">
    <input type="hidden" name="amount" value="' . round($Bestellung->fWarensummeKundenwaehrung + $Bestellung->fVersandKundenwaehrung, 2) . '">
    <input type="hidden" name="currency" value="' . $Bestellung->Waehrung->cISO . '">
    <input type="hidden" name="detail1_description" value="' . Shop::Lang()->get('order', 'global') . ':">
    <input type="hidden" name="detail1_text" value="' . $Bestellung->cBestellNr . '">
    <input type="hidden" name="return_url" value="' . $returnURL . '">
    <input type="submit" value="' . Shop::Lang()->get('payWithMoneybookers', 'global') . '">
    </form>
	';
    }

    return 'Moneybookerszahlung nicht m&ouml;glich.';
}
