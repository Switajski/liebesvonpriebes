<?php

/**
 * Hook 140: Adds elements to HTML-DOM-Structure.
 */
require_once(__DIR__ . '/lib/lpa_defines.php');
require_once(__DIR__ . '/lib/lpa_utils.php');
require_once(__DIR__ . '/lib/class.LPAController.php');
require_once(__DIR__ . '/lib/class.LPARenderHelper.php');

if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_GENERAL_ACTIVE] === '0') {
    /*
     * Plugin disabled, do nothing.
     */
    return;
}

$controller = new LPAController();
$config = $controller->getConfig();

if (empty($config) || empty($config['access_key']) || empty($config['merchant_id']) || empty($config['secret_key'])) {
    /*
     * Plugin is not configured yet, stop here.
     */
    return;
}

try {
    /*
     * Application Scope is always the same as it is needed during login and checkout.
     */
    $scope = "profile payments:widget payments:shipping_address";
    $mode = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_GENERAL_MODE];
    $language_suffix = '';
    $lpa_language_code = "de-DE";
    $renderHelper = new LPARenderHelper();

    if (Shop::getLanguage(true) === "eng") {
        $language_suffix = '-en';
        $lpa_language_code = 'en-GB';
    }

    // get the shop version (of the files) in order to correctly handle 3 step checkout but retain compatibility to older templates
    $shopVersion = Shop::getVersion();


    /*
     * Set the generic redirection cookie
     */
    setLPARedirectionCookie();

    /*
     * Import generic javascript in head.
     */
    // set config values
    Shop::Smarty()->assign('lpa_client_id', $config['client_id'])
        ->assign('lpa_widget_endpoint', $controller->getEndpointFor($config, 'widgetURL'))
        ->assign('lpa_login_redirect_uri', Shop::getURL(true) . '/lpalogin' . $language_suffix)// MUST BE SSL!
        ->assign('lpa_seller_id', $config['merchant_id'])
        ->assign('lpa_shop_version', $shopVersion)
        ->assign('lpa_button_type', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_LOGINBUTTON_TYPE])
        ->assign('lpa_button_color', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_LOGINBUTTON_COLOR])
        ->assign('lpa_button_size', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_LOGINBUTTON_SIZE]);
    $hideButtons = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_HIDDENBUTTONS_ACTIVE];
    $hideButtons = $hideButtons && !isset($_GET['lpa-show']);
    Shop::Smarty()->assign('lpa_general_hiddenbuttons_active', $hideButtons)
        ->assign('lpa_sandbox_mode', (int)$config['sandbox'])
        ->assign('lpa_button_scope', $scope)
        ->assign('lpa_button_tooltip', $oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_TOOLTIP]);
    // set ajax urls
    $lpa_ajax_urls = array();
    $lpa_ajax_urls['delivery_selection'] = $oPlugin->cFrontendPfadURLSSL . 'ajax/lpa_ajax_update_delivery_selection.php';
    $lpa_ajax_urls['update_selected_shipping_method'] = $oPlugin->cFrontendPfadURLSSL . 'ajax/lpa_ajax_update_selected_shipping_method.php';
    $lpa_ajax_urls['confirm_order'] = $oPlugin->cFrontendPfadURLSSL . 'ajax/lpa_ajax_confirm_order.php';
    $lpa_ajax_urls['select_account_address'] = $oPlugin->cFrontendPfadURLSSL . 'ajax/lpa_ajax_select_account_address.php';
    Shop::Smarty()->assign('lpa_ajax_urls', $lpa_ajax_urls);

    /*
     * Checkout URL for redirects
     */
    $lpa_other_urls['checkout'] = Shop::getURL(true) . '/lpacheckout' . $language_suffix;

    /*
     * Complete URL for redirect after order completion - localized!
     */
    $completeUrlLocalized = str_replace("http://", "https://", Shop::getURL()) . '/lpacomplete' . $language_suffix;
    $lpa_other_urls['complete_localized'] = $completeUrlLocalized;

    Shop::Smarty()->assign('lpa_other_urls', $lpa_other_urls)
        ->assign('lpa_language_code', $lpa_language_code);

    /*
     * Set the shop base path visible for the JS as well to correctly redirect.
     */
    $basePath = lpaGetShopBasePath();
    if (empty($basePath) || $basePath === '/') {
        $basePath = '';
    }
    Shop::Smarty()->assign('lpa_shop_base_path', $basePath);

    $popup = 'false';
    Shop::Smarty()->assign('lpa_button_popup', $popup); // popup is always disabled, because JTL is sometimes not fully SSL secured

    $headRedirectSnippet = '';
    if (file_exists($oPlugin->cFrontendPfad . 'template/head_redirect_snippet_custom.tpl')) {
        $headRedirectSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/head_redirect_snippet_custom.tpl');
    } else {
        $headRedirectSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/head_redirect_snippet.tpl');
    }
    $headSnippet = '';
    if (file_exists($oPlugin->cFrontendPfad . 'template/head_snippet_custom.tpl')) {
        $headSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/head_snippet_custom.tpl');
    } else {
        $headSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/head_snippet.tpl');
    }

    $bodySnippet = '';
    if (file_exists($oPlugin->cFrontendPfad . 'template/body_snippet_custom.tpl')) {
        $bodySnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/body_snippet_custom.tpl');
    } else {
        $bodySnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/body_snippet.tpl');
    }

    $lpa_button_idx = 0;

    if ($mode !== 'p') {
        $pqMethod = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_LOGINBUTTON_INSERT_METHOD];
        $pqSelectors = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_LOGINBUTTON_INSERT_SELECTORS];

        $insertInParentMode = false;
        if (empty($pqSelectors)) {
            $pqSelectors = S360_LPA_SELECTOR_LOGIN_INPUT;
            $pqMethod = 'after';
            $insertInParentMode = true;
        }
        $loginInputs = pq($pqSelectors);

        Shop::Smarty()->assign('lpa_button_type_class', 'lpa-login-button');
        foreach ($loginInputs as $loginInput) {
            $lpa_button_idx++;
            $renderHelper->addLoginButton();

            Shop::Smarty()->assign('lpa_button_idx', $lpa_button_idx);
            $loginButtonSnippet = '';
            if (file_exists($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl')) {
                $loginButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl');
            } else {
                $loginButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet.tpl');
            }
            if ($insertInParentMode) {
                $loginForm = pq($loginInput)->parents('form');
                $loginForm->after($loginButtonSnippet);
            } else {
                pq($loginInput)->$pqMethod($loginButtonSnippet);
            }
        }
    }

    $checkoutPossible = $renderHelper->isCheckoutPossible();
    $showDetailExpressButton = $renderHelper->isDetailExpressButtonPossible($oPlugin);

    /*
     * Add pay with amazon button, only if mode is not Login Only
     */
    if ($mode !== 'l') {

        if ($checkoutPossible && isset($_SESSION['Warenkorb']) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
            // Sind Produkte mit Attribut 'exclude_amapay' vorhanden
            // darf Amazon Payments nicht angeboten werden
            $bExclude = false;
            if (isset($_SESSION['Warenkorb']) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
                foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
                    if ((int)$oPosition->nPosTyp === (int)C_WARENKORBPOS_TYP_ARTIKEL && is_object($oPosition->Artikel)) {
                        if (isset($oPosition->Artikel->FunktionsAttribute['exclude_amapay']) || isset($oPosition->Artikel->AttributeAssoc['exclude_amapay'])) {
                            $bExclude = true;
                            break;
                        }
                    }
                }
            }

            if ($bExclude) {
                Jtllog::writeLog("LPA: Auswahl der Zahlungsart unterbunden (Attribut 'exclude_amapay' in Warenkorbposition vorhanden)", JTLLOG_LEVEL_DEBUG);
            } else {
                /*
                 * add pay with amazon
                 */
                $pqMethod = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_INSERT_METHOD];
                $pqSelectors = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_INSERT_SELECTORS];
                if (empty($pqSelectors)) {
                    // default to standard selectors if no selector is selected (else, pq() will add the element before the HTML)
                    $pqSelectors = S360_LPA_SELECTOR_PAY_BUTTON;
                }
                Shop::Smarty()->assign('lpa_button_type_class', 'lpa-pay-button')
                    ->assign('lpa_login_redirect_uri', Shop::getURL(true) . '/lpacheckout' . $language_suffix)// MUST BE SSL!
                    ->assign('lpa_button_type', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_TYPE])
                    ->assign('lpa_button_color', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_COLOR])
                    ->assign('lpa_button_size', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_SIZE])
                    ->assign('lpa_express_button', 0);
                $selectionPositions = pq($pqSelectors);
                foreach ($selectionPositions as $position) {
                    $lpa_button_idx++;
                    $renderHelper->addPayButton();
                    Shop::Smarty()->assign('lpa_button_idx', $lpa_button_idx);
                    $payButtonSnippet = '';
                    if (file_exists($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl')) {
                        $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl');
                    } else {
                        $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet.tpl');
                    }
                    pq($position)->$pqMethod($payButtonSnippet);
                }

                // also render the pay button on internally forced positions, if necessary
                $lpa_button_idx++;
                Shop::Smarty()->assign('lpa_button_idx', $lpa_button_idx);
                $payButtonSnippet = '';
                if (file_exists($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl')) {
                    $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl');
                } else {
                    $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet.tpl');
                }
                if (pq(S360_LPA_SELECTOR_PAY_BUTTON_FORCED)->length > 0) {
                    $renderHelper->addPayButton();
                    pq(S360_LPA_SELECTOR_PAY_BUTTON_FORCED)->append($payButtonSnippet);
                }

                /*
                 * If we are looking at the default checkout process, we add the pay with amazon alternate checkout info
                 */
                if (Shop::getPageType() === PAGE_BESTELLVORGANG) {

                    $step = Shop::Smarty()->get_template_vars('step');
                    $bestellschritt = Shop::Smarty()->get_template_vars('bestellschritt');

                    $showDuringCheckout = false;
                    $configBestellschritte = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT];
                    if ($step === 'accountwahl' && !(isset($configBestellschritte) && $configBestellschritte == 0)) {
                        $showDuringCheckout = true;
                    } else {
                        if (!isset($configBestellschritte)) {
                            $showDuringCheckout = true;
                        } else {
                            $validBestellschritte = str_split($configBestellschritte);
                            if (!empty($validBestellschritte) && count($validBestellschritte) > 0) {
                                foreach ($validBestellschritte as $schritt) {
                                    if ($bestellschritt[(int)$schritt] === 1) {
                                        $showDuringCheckout = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if ($showDuringCheckout) {

                        $pqMethod = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_METHOD];
                        $pqSelectors = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_DURING_CHECKOUT_INSERT_SELECTORS];

                        if (empty($pqSelectors)) {
                            // default to standard selectors if no selector is selected (else, pq() will add the element before the HTML)
                            $pqSelectors = S360_LPA_SELECTOR_PAY_BUTTON_DURING_CHECKOUT;
                        }

                        $selectionPositions = pq($pqSelectors);

                        foreach ($selectionPositions as $position) {

                            $lpa_button_idx++;
                            $renderHelper->addPayButton();
                            Shop::Smarty()->assign('lpa_button_idx', $lpa_button_idx);
                            $payButtonSnippet = '';
                            if (file_exists($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl')) {
                                $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl');
                            } else {
                                $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet.tpl');
                            }
                            Shop::Smarty()->assign('lpa_button_snippet', $payButtonSnippet)
                                ->assign('lpa_checkout_hint', $oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_CHECKOUT_HINT]);
                            $checkoutHTML = '';
                            if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_alternate_checkout_snippet_custom.tpl')) {
                                $checkoutHTML = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/lpa_alternate_checkout_snippet_custom.tpl');
                            } else {
                                $checkoutHTML = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/lpa_alternate_checkout_snippet.tpl');
                            }
                            pq($position)->$pqMethod($checkoutHTML);
                        }
                    }
                }
            }
        }

        if ($showDetailExpressButton) {
            /*
             * add express pay with amazon
             */
            $pqMethod = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_INSERT_METHOD];
            $pqSelectors = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_INSERT_SELECTORS];
            if (!empty($pqSelectors)) {
                Shop::Smarty()->assign('lpa_button_type_class', 'lpa-pay-button')
                    ->assign('lpa_login_redirect_uri', Shop::getURL(true) . '/lpacheckout' . $language_suffix)// MUST BE SSL!
                    ->assign('lpa_button_type', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_TYPE])
                    ->assign('lpa_button_color', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_COLOR])
                    ->assign('lpa_button_size', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_SIZE])
                    ->assign('lpa_required_fields_message', $oPlugin->oPluginSprachvariableAssoc_arr[S360_LPA_LANGKEY_EXPRESS_REQUIRED_FIELDS])
                    ->assign('lpa_express_button', 1); // enables special JS in the template
                // on Shops below version 4.06.0 we have to fall back to a different AJAX push method because pure variations will not work otherwise
                if (Shop::getVersion() < 406) {
                    Shop::Smarty()->assign('lpa_express_push_method', 'lpa_fallback_pushToBasket');
                } else {
                    Shop::Smarty()->assign('lpa_express_push_method', 'pushToBasket');
                }
                $selectionPositions = pq($pqSelectors);
                foreach ($selectionPositions as $position) {
                    $lpa_button_idx++;
                    $renderHelper->addPayButton();
                    Shop::Smarty()->assign('lpa_button_idx', $lpa_button_idx);
                    $payButtonSnippet = '';
                    if (file_exists($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl')) {
                        $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet_custom.tpl');
                    } else {
                        $payButtonSnippet = Shop::Smarty()->fetch($oPlugin->cFrontendPfad . 'template/button_snippet.tpl');
                    }
                    pq($position)->$pqMethod($payButtonSnippet);
                }
            }
        }
    }

    // actual adding of JS and snippets
    /*
     * Add head and head redirect snippets if needed.
     */
    if ($renderHelper->headRedirectSnippetNeeded()) {
        pq('head')->append($headRedirectSnippet);
    }
    // add headsnippet only if we are not on an ajax request, otherwise adding this code might break jquery completely (Widget.js unsets $ iff the requesting JS writes it into the head of the document)
    if ($renderHelper->headSnippetNeeded() && !isAjaxRequest()) {
        pq('head')->append($headSnippet);

        /* try to put the body snippet not at the very end to prioritize loading widgets over other JS */
        $mainWrapper = pq(S360_LPA_SELECTOR_MAIN_WRAPPER);
        if ($mainWrapper->length) {
            $mainWrapper->after($bodySnippet);
        } else {
            pq('body')->append($bodySnippet);
        }

    }

    /*
     * Insert css files manually and only if needed.
     */
    if ($renderHelper->cssCheckoutNeeded()) {
        pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-checkout.css" />');
        if (file_exists($oPlugin->cFrontendPfad . 'css/lpa-checkout_custom.css')) {
            pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-checkout_custom.css" />');
        }
    }
    if ($renderHelper->cssPayButtonsNeeded()) {
        pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-pay-button.css" />');
        if (file_exists($oPlugin->cFrontendPfad . 'css/lpa-pay-button_custom.css')) {
            pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-pay-button_custom.css" />');
        }
        pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-tooltip.css" />');
        if (file_exists($oPlugin->cFrontendPfad . 'css/lpa-tooltip_custom.css')) {
            pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-tooltip_custom.css" />');
        }
    }
    if ($renderHelper->cssLoginButtonsNeeded()) {
        pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-login-button.css" />');
        if (file_exists($oPlugin->cFrontendPfad . 'css/lpa-login-button_custom.css')) {
            pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-login-button_custom.css" />');
        }
    }
    if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY] === "1") {
        pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-shop3-compatibility.css" />');
        if (file_exists($oPlugin->cFrontendPfad . 'css/lpa-shop3-compatibility_custom.css')) {
            pq('head')->append('<link rel="stylesheet" type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/lpa-shop3-compatibility_custom.css" />');
        }
    }

    /*
     * Insert generic lpa-utils.js everywhere in the body, but not in AJAX requests.
     */
    if(!isAjaxRequest()) {
        pq('body')->append('<script src="' . $oPlugin->cFrontendPfadURLSSL . 'js/lpa-utils.js" type="text/javascript" />');
    }

    /*
     * Insert login and checkout js if needed, only.
     */
    if ($renderHelper->checkoutJSNeeded()) {
        pq('body')->append('<script src="' . $oPlugin->cFrontendPfadURLSSL . 'js/lpa-checkout.js" type="text/javascript" />');
    }
    if ($renderHelper->loginJSNeeded()) {
        pq('body')->append('<script src="' . $oPlugin->cFrontendPfadURLSSL . 'js/lpa-login.js" type="text/javascript" />');
    }
} catch (Exception $ex) {
    Jtllog::writeLog('LPA: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
}