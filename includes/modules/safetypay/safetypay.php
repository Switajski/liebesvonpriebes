<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Kunde     $Kunde
 * @param Warenkorb $Warenkorb
 * @param array     $Einstellungen
 * @return string
 */
function gib_safetypay_form($Kunde, $Warenkorb, $Einstellungen)
{
    define('SAFETYPAY_APIKEY', $Einstellungen['zahlungsart_safetypay_apikey']);
    define('SAFETYPAY_SIGNTATURE_KEY', $Einstellungen['zahlungsart_safetypay_signaturekey']);

    require_once __DIR__ . '/class/safetypayProxyAPI.php';
    require_once __DIR__ . '/include/safetypay_functions.php';

    if ($Warenkorb->gibGesamtsummeWaren(true, false) > 0 && $Kunde) {
        // Gets Values
        $pLanguageShop          = 'DE';
        $pCurrency              = 'EUR';
        $pToCurrency            = 'EUR';
        $pBankID                = (isset($_REQUEST['slcBankID']) ? $_REQUEST['slcBankID'] : $_POST['slcBankID']);
        $txtAmount              = $Warenkorb->gibGesamtsummeWaren(true, false);
        $pTrackingCode          = (isset($_REQUEST['TrackingCode']) ? $_REQUEST['TrackingCode'] : $_POST['TrackingCode']);
        $pCalculationQuoteRefNo = (isset($_REQUEST['CalcQuoteReferenceNo']) ? $_REQUEST['CalcQuoteReferenceNo'] : $_POST['CalcQuoteReferenceNo']);
        $pMerchantReferenceNo   = '';
        $pURLPaymentSuccesfully = (isset($_REQUEST['URLPaymentSuccesfully']) ? $_REQUEST['URLPaymentSuccesfully'] : $_POST['URLPaymentSuccesfully']);
        $pURLPaymentFailed      = (isset($_REQUEST['URLPaymentFailed']) ? $_REQUEST['URLPaymentFailed'] : $_POST['URLPaymentFailed']);

        $pSubmit = (isset($_REQUEST['Submit']) ? $_REQUEST['Submit'] : $_POST['Submit']);

        // Instance of SafetyPay Proxy Class
        $proxySTP = new SafetyPayProxy();
        // Test- oder Produktionsumgebung
        $proxySTP->SetEnvironment($Einstellungen['zahlungsart_safetypay_testumgebung']);

        // Get Currencies List
        $optionsCurrencies = stp_GetCurrencies($proxySTP, $pLanguageShop, $pCurrency, $txtAmount, $pToCurrency, $pCalculationQuoteRefNo, $calculationQuoteToAmount);

        // Get Banks List
        $optionsBanks = stp_GetBanks($proxySTP, $pToCurrency);

        return '' .
        '<script language="JavaScript" type="text/javascript" src="includes/modules/safetypay/js/safetypay_js_mails.js"></script>' .
        '<script language="JavaScript" type="text/javascript" src="includes/modules/safetypay/js/safetypay_js_ajax.js"></script>' .
        '<img src="includes/modules/safetypay/gfx/safetypay_logo.png" alt="SafetyPay" /><br />' .

        '<table>' .
        '<tr>' .
        '<td width="150">' . Shop::Lang()->get('safetypayTotalSum', 'paymentMethods') . ':</td>' .
        '<td>' . $pCurrency . ' ' . $txtAmount . '</td>' .
        '</tr>' .
        '<tr>' .
        '<td width="150">' . Shop::Lang()->get('safetypaySelectCurrency', 'paymentMethods') . ':</td>' .
        '<td><select name="slcToCurrency" onchange="loadData(\'includes/modules/safetypay/safetypayAjax.php?curr=' . $pCurrency . '&amount=' . $txtAmount . '&tocurr=\'+slcToCurrency.value, \'slcBankID\');" style="width: 300px">' .
        $optionsCurrencies .
        '</select></td>' .
        '</tr>' .
        '<tr>' .
        '<td width="150">' . Shop::Lang()->get('safetypayTotalSumOwnCurrency', 'paymentMethods') . ':</td>' .
        '<td><div id="amountCalc">' . $pToCurrency . ' ' . $txtAmount . '</div></td>' .
        '</tr>' .
        '<tr>' .
        '<td width="150">' . Shop::Lang()->get('safetypaySelectBank', 'paymentMethods') . ':</td>' .
        '<td><div id="divBanksList"><select name="slcBankID" id="slcBankID" style="width: 300px">' .
        $optionsBanks .
        '</select></div></td>' .
        '</tr>' .
        '<tr>' .
        '<td></td>' .
        '<td>' .
        '<input type="hidden" name="Currency" value="' . $pCurrency . '" />' .
        '<input type="hidden" name="txtAmount" value="' . $txtAmount . '" />' .
        '<input type="hidden" name="MerchantReferenceNo" value="' . $pMerchantReferenceNo . '" />' .
        '<input type="hidden" name="languageShop" value="' . $pLanguageShop . '" />' .
        '<input type="hidden" name="CalcQuoteReferenceNo" value="' . $pCalculationQuoteRefNo . '" />' .
        '<input type="hidden" name="TrackingCode" value="' . $pTrackingCode . '" />' .
        '<input type="hidden" name="URLPaymentSuccesfully" value="' . Shop::getURL() . '" />' .
        '<input type="hidden" name="URLPaymentFailed" value="' . Shop::getURL() . '/index.php?s=15" />' .
        '</td>' .
        '</tr>' .
        '</table>';
    }

    return 'Zahlung mit SafetyPay nicht m&oouml;glich.';
}
