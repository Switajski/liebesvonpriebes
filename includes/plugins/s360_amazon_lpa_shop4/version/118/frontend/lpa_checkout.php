<?php

/*
 * This script handles the amazon specific checkout.
 */
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPACurrencyHelper.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php');

$session = Session::getInstance();

// signal to other hooks
Shop::set('lpa_plugin_page', 'checkout');

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
    ->assign('PluginFrontendUrl', $oPlugin->cFrontendPfadURLSSL)
    ->assign('lpa_billing_override', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_USE_BILLING_ADDRESS])
    ->assign('Kunde', $_SESSION['Kunde']);
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

$wasSoftDecline = false;
if (isset($_SESSION['lpa_processing_error'])) {
    // we had a previous error from the processing links, if it was a soft decline type, we can allow the user to retry.
    // else, we send him to the basket with an error message
    if ($_SESSION['lpa_processing_error']['code'] === S360_LPA_EXCEPTION_CODE_SOFT_DECLINE) {
        Shop::Smarty()->assign('lpa_processing_error', $_SESSION['lpa_processing_error']);
        $wasSoftDecline = true;
        $lpa_step = 'lpasoftdecline';
        $orid = $_SESSION['lpa_processing_error']['orid'];
        unset($_SESSION['lpa_processing_error']);
    } else {
        unset($_SESSION['lpa_processing_error']);
        $_SESSION['lpa_basket_error_message'] = $oPlugin->oPluginSprachvariableAssoc_arr['lpa_processing_error_do_not_try_again'];
        $redirectUrl = LinkHelper::getInstance()->getStaticRoute('warenkorb.php', true, true);
        header('Location: ' . $redirectUrl);
        exit();
    }
}


// set a custom einstellungen variable because JTL would overwrite it else
Shop::Smarty()->assign('lpaEinstellungen', Shop::getSettings(array(CONF_GLOBAL, CONF_ARTIKELDETAILS, CONF_RSS, CONF_KUNDEN, CONF_KAUFABWICKLUNG, CONF_KUNDENFELD, CONF_TRUSTEDSHOPS)));

// try to add/remove neukundenkupon if necessary
plausiNeukundenKupon();


/*
 * If the users currency is not equal to the LPA-Currency, we have to inform the user about it.
 */
$currentCurrency = LPACurrencyHelper::getCurrentCurrency();
$lpaCurrencyISO = $controller->getCurrencyCode($config);

$presentmentCurrencyISO = $currentCurrency->cISO;
$validCurrency = LPACurrencyHelper::isSupportedCurrency($presentmentCurrencyISO) && !LPACurrencyHelper::isExcludedCurrency($presentmentCurrencyISO);

if ($validCurrency) {
    Shop::Smarty()->assign('lpa_presentment_currency_iso', $presentmentCurrencyISO);
} else {
    Shop::Smarty()->assign('lpa_presentment_currency_iso', $lpaCurrencyISO)
        ->assign('lpa_available_supported_currencies', LPACurrencyHelper::getAvailableSupportedCurrencies());

}
Shop::Smarty()->assign('lpa_is_valid_currency', $validCurrency);


if ($lpa_step === 'lpaselected' && !isset($_POST['lpa_charge_on_order_ack']) && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_CAPTUREMODE] === 'immediate') {
    Shop::Smarty()->assign('cError', $oPlugin->oPluginSprachvariableAssoc_arr['lpa_error_message_acknowledge_immediate_capture']);
    $lpa_step = 'error';
}

if ($lpa_step === 'lpaselected' && empty($_SESSION['Versandart'])) {
    Shop::Smarty()->assign('cError', $oPlugin->oPluginSprachvariableAssoc_arr['lpa_error_message_select_delivery_address_and_method']);
    $lpa_step = 'error';
}

if (($lpa_step === 'lpasoftdecline' || $lpa_step === 'lpaselected') && !empty($_SESSION['Versandart'])) {

    /*
     * The user has submit the form so he should have set everything (address and payment method) now.
     * However, we can validate this against amazon beforehand by looking for Contraints in the GetOrderReferenceDetails.
     */
    $confirmOrder = true;

    if (empty($orid)) {
        Jtllog::writeLog('LPA: LPA-Payment-Fehler: Im Checkout wurde keine ORID übergeben.', JTLLOG_LEVEL_NOTICE);
        Shop::Smarty()->assign('cError', $oPlugin->oPluginSprachvariableAssoc_arr['lpa_technical_error']);
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
        Shop::Smarty()->assign('cError', $oPlugin->oPluginSprachvariableAssoc_arr['lpa_technical_error']);
        $confirmOrder = false;
    }


    if (isset($result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Constraints'])) {
        $result = $result['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Constraints'];
        $constraint = array_shift($result);
        Jtllog::writeLog('LPA: LPA-Payment: ' . $constraint['ConstraintID'] . ' von Amazon Pay empfangen.', JTLLOG_LEVEL_DEBUG);
        if ($constraint['ConstraintID'] === 'PaymentMethodNotAllowed' || $constraint['ConstraintID'] === 'PaymentPlanNotSet') {
            // this constraint is ok, we can handle this by letting the user choose a different payment method
            $wasSoftDecline = true;
            $lpa_step = 'lpasoftdecline';
            if(null === Shop::Smarty()->getConfigVars(('lpa_processing_error'))) {
                Shop::Smarty()->assign('lpa_processing_error', $oPlugin->oPluginSprachvariableAssoc_arr['lpa_confirmation_soft_decline']);
            }
        } else {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: Checkout versucht, aber unerwartete Constraint empfangen.', JTLLOG_LEVEL_NOTICE);
            Shop::Smarty()->assign('cError', $oPlugin->oPluginSprachvariableAssoc_arr['lpa_technical_error']);
            $confirmOrder = false;
        }
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
        ->assign('lpa_wassoftdecline', $wasSoftDecline)
        ->assign('lpa_orid', $orid)
        ->assign('AGB', gibAGBWRB($_SESSION['kSprache'], $_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)// Assign smarty constants that *may* be used by the overview of ordered articles
        ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK);
}
