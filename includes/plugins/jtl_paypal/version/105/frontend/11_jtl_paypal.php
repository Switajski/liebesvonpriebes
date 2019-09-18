<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * HOOK_BESTELLVORGANG_PAGE_STEPVERSAND_PLAUSI.
 */
if (isset($_SESSION['paypalexpress']) && isset($_SESSION['Versandart']->kVersandart)) {
    require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalExpress.class.php';
    require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

    $paypalexpress = new PayPalExpress();
    $paypalexpress->zahlungsartsession($_SESSION['Versandart']->kVersandart);

    PayPalHelper::addSurcharge();
}
