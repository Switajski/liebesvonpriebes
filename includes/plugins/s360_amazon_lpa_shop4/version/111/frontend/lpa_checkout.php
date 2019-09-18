<?php

/*
 * This script handles the amazon specific checkout.
 */
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');

$session = Session::getInstance();

pruefeHttps();
unset($_SESSION['lpa-from-checkout']);

/*
 * If the Warenkorb is empty, this site should not be visited. Redirect to warenkorb.
 */
if (empty($_SESSION['Warenkorb']) || empty($_SESSION['Warenkorb']->PositionenArr)) {
    header('Location: ' . Shop::getURL() . '/warenkorb.php', true, 303);
    return;
}


// Use custom template if it exists.
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_checkout_custom.tpl')) {
    Shop::Smarty()->assign('cPluginTemplate', $oPlugin->cFrontendPfad . 'template/lpa_checkout_custom.tpl');
}


// Determine if this is a mobile template
$isMobileTemplate = false;
$template = Template::getInstance();
if ($template->isMobileTemplateActive()) {
    $isMobileTemplate = true;
}
Shop::Smarty()->assign('lpa_template_mobile', $isMobileTemplate);

/*
 * If the user is not logged in at all, we send him to the login site.
 * The actual redirection happens in the JS of the head snippet.
 */
if (isset($_SESSION['lpa-redirect-to-checkout'])) {
    // if the redirection method sent us here, make sure to register this and do not set our own redirection flag
    unset($_SESSION['lpa-redirect-to-checkout']);
} else {
    // make sure to flag ourselves as the priority redirection target
    $_SESSION['lpa-from-checkout'] = true;
}
if (empty($_SESSION['Kunde'])) {
    // no customer is logged in, at all... JS will handle the redirect to the login page (IFF the correct token is set, else we just show a message to the customer
    Shop::Smarty()->assign('lpa_not_logged_in', 1);
    return;
}

$controller = new LPAController();
$config = $controller->getConfig();
$client = $controller->getClient($config);

Shop::Smarty()->assign('lpa_seller_id', $config['merchant_id'])
    ->assign('lpa_charge_on_order', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE])
    ->assign('lpa_shop3_compatibility', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY])
    ->assign('lpa_sandbox_mode', (int)$config['sandbox'])
    ->assign('PluginFrontendUrl', $oPlugin->cFrontendPfadURLSSL);
$checkoutUrl = str_replace("http://", "https://", Shop::getURL()) . '/lpacheckout';
if (Shop::getLanguage(true) === "eng") {
    $checkoutUrl .= '-en';
}
Shop::Smarty()->assign('lpa_checkout_url_localized', $checkoutUrl);

if (isset($_POST)) {
    Shop::Smarty()->assign('cPost_arr', $_POST);
} else {
    Shop::Smarty()->assign('cPost_arr', array());
}

$lpa_step = null;
if (isset($_REQUEST['lpa_step'])) {
    $lpa_step = StringHandler::filterXSS($_REQUEST['lpa_step']);
}

$orid = null;
if (isset($_REQUEST['orid'])) {
    $orid = StringHandler::filterXSS($_REQUEST['orid']);
}

// set a custom einstellungen variable because JTL would overwrite it else
Shop::Smarty()->assign('lpaEinstellungen', Shop::getSettings(array(CONF_GLOBAL, CONF_ARTIKELDETAILS, CONF_RSS, CONF_KUNDEN, CONF_KAUFABWICKLUNG, CONF_KUNDENFELD, CONF_TRUSTEDSHOPS)));

if ($lpa_step === 'lpaselected' && !isset($_POST['lpa_charge_on_order_ack']) && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE] === 'immediate') {
    Shop::Smarty()->assign('cError', 'Bitte best&auml;tigen Sie, dass die Zahlung sofort bei Bestellung erfolgt.');
    $lpa_step = 'error';
}

if ($lpa_step === 'lpaselected' && empty($_SESSION['Versandart'])) {
    Shop::Smarty()->assign('cError', 'Bitte w&auml;hlen Sie eine g&uuml;ltige Versandart und Lieferadresse aus.');
    $lpa_step = 'error';
}

if ($lpa_step === 'lpaselected' && !empty($_SESSION['Versandart'])) {

    /*
     * The user has submit the form so he should have set everything (address and payment method) now.
     * However, we can validate this against amazon beforehand by looking for Contraints in the GetOrderReferenceDetails.
     */
    $confirmOrder = true;

    if (empty($orid)) {
        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Im Checkout wurde keine ORID übergeben.', JTLLOG_LEVEL_NOTICE);
        Shop::Smarty()->assign('cError', 'Technischer Fehler: Bitte versuchen Sie es erneut oder nutzen Sie den normalen Checkout.');
        $confirmOrder = false;
    }
    $getOrderReferenceDetailsParameter = array(
        'merchant_id' => $config['merchant_id'],
        'amazon_order_reference_id' => $orid
    );

    $result = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
    $result = $result->toArray();

    if (isset($result['Error'])) {
        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Fehler beim Weitergehen zum letzten Bestellschritt: ' . $result['Error']['Message'], JTLLOG_LEVEL_ERROR);
        Shop::Smarty()->assign('cError', 'Es ist ein technischer Fehler aufgetreten.');
        $confirmOrder = false;
    }


    if (isset($result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Constraints'])) {
        $result = $result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Constraints'];
    } else {
        $result = null;
    }

    if (!empty($result)) {
        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Checkout versucht ohne Auswahl der Adresse und der Zahlart.', JTLLOG_LEVEL_NOTICE);
        Shop::Smarty()->assign('cError', 'Bitte w&auml;hlen Sie eine Adresse und Zahlart aus!');
        $confirmOrder = false;
    }

    /*
     * The customer entered the desired data. Show the order confirmation page.
     *
     * We do not get the address just yet - we show the read only versions of the widgets, as well as the positions of the WK after selection of delivery address and payment
     * method.
     *
     * If confirmOrder is true, we can assume that a subsequent confirm-order call to amazon will also be successful.
     */
    Shop::Smarty()->assign('confirmOrder', $confirmOrder)
        ->assign('lpa_orid', $orid)
        ->assign('AGB', gibAGBWRB($_SESSION['kSprache'], $_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)// Assign smarty constants thay *may* be used by the overview of ordered articles
        ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK);

    /*
     * If the users currency is not equal to the LPA-Currency, we have to inform the user about it.
     */
    $currentCurrency = $_SESSION['Waehrung'];
    if (!$currentCurrency->kWaehrung) {
        $currentCurrency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
    }
    $lpaCurrencyISO = $controller->getCurrencyCode($config);
    if ($currentCurrency->cISO !== $lpaCurrencyISO) {
        $hint = $oPlugin->oPluginSprachvariableAssoc_arr['lpa_currency_hint'];
        $hint = str_replace('%LPA_CURRENCY%', $lpaCurrencyISO, $hint);
        $hint = str_replace('%SHOP_CURRENCY%', $currentCurrency->cISO, $hint);

        $shopCurrencySign = $currentCurrency->cNameHTML;
        $lpaCurrencySign = $lpaCurrencyISO;
        if ($lpaCurrencyISO === 'EUR') {
            $lpaCurrencySign = '&euro;';
        } elseif ($lpaCurrencyISO === 'USD') {
            $lpaCurrencySign = 'US$';
        } elseif ($lpaCurrencyISO === 'GBP') {
            $lpaCurrencySign = '&pound;';
        }

        $hint = str_replace('%LPA_CURRENCY_SIGN%', $lpaCurrencySign, $hint);
        $hint = str_replace('%SHOP_CURRENCY_SIGN%', $shopCurrencySign, $hint);

        /*
         * Note that shop amount is ALWAYS the value in the STANDARD currency of the Shop!
         */
        $shopAmount = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);
        $shopAmountLocalized = number_format(lpaConvertAmount($shopAmount, 0, $currentCurrency->cISO), 2, $currentCurrency->cTrennzeichenCent, $currentCurrency->cTrennzeichenTausend);
        $lpaAmount = number_format(lpaConvertAmount($shopAmount, 0, $lpaCurrencyISO), 2, $currentCurrency->cTrennzeichenCent, $currentCurrency->cTrennzeichenTausend);

        $hint = str_replace('%LPA_AMOUNT%', $lpaAmount, $hint);
        $hint = str_replace('%SHOP_AMOUNT%', $shopAmountLocalized, $hint);

        Shop::Smarty()->assign('lpa_currency_hint', $hint);
    }
}
