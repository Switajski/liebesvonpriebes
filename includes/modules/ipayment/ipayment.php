<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Bestellung $Bestellung
 * @param string     $trxaccount_id
 * @param string     $trxuser_id
 * @param string     $trxpassword
 * @param string     $returnURL
 * @return string
 */
function gib_ipayment_form($Bestellung, $trxaccount_id, $trxuser_id, $trxpassword, $returnURL)
{
    if ($Bestellung->fWarensumme > 0) {
        return '
        <form action="https://ipayment.de/merchant/' . $trxaccount_id . '/processor.php" method="post">
        <input type="hidden" name="trxuser_id" value="' . $trxuser_id . '">
        <input type="hidden" name="trxpassword" value="' . $trxpassword . '"
        <input type="hidden" name="trx_paymenttyp" value="cc">
        <input type="hidden" name="trx_typ" value="auth">
        <input type="hidden" name="addr_name" value="' . $_SESSION['Kunde']->cVorname . ' ' . $_SESSION['Kunde']->cNachname . '">
        <input type="hidden" name="addr_street" value="' . $_SESSION['Kunde']->cStrasse . ' ' . $_SESSION['Kunde']->cHausnumme . '">
        <input type="hidden" name="addr_zip" value="' . $_SESSION['Kunde']->cPLZ . '">
        <input type="hidden" name="addr_city" value="' . $_SESSION['Kunde']->cOrt . '">
        <input type="hidden" name="addr_country" value="' . $_SESSION['Kunde']->cLand . '">
        <input type="hidden" name="addr_email" value="' . $_SESSION['Kunde']->cMail . '">
        <input type="hidden" name="trx_amount" value="' . round(($Bestellung->fWarensummeKundenwaehrung + $Bestellung->fVersandKundenwaehrung) * 100, 0) . '">
        <input type="hidden" name="trx_currency" value="EUR">
        <input type="hidden" name="invoice_text" value="' . Shop::Lang()->get('order', 'global') . ':">
        <input type="hidden" name="trx_user_comment" value="' . $Bestellung->cBestellNr . '">' .
        '<input type="hidden" name="redirect_url" value="' . $returnURL . '">
        <input type="hidden" name="redirect_action" value="POST">
        <input type="submit" value="' . Shop::Lang()->get('payWithIpayment', 'global') . '">
        </form>
	';
    }

    return 'iPayment-zahlung nicht m&ouml;glich.';
}
