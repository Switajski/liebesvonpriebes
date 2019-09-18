<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$pageType = Shop::getPageType();

$presentmentLoader = function ($amount, $currency) use (&$oPlugin) {
    require_once str_replace(
        'frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalFinance.class.php';

    $payPalFinance = new PayPalFinance();

    if ($payPalFinance->isVisible($amount)) {
        $tplData = Shop::Smarty()
            ->assign('amount', $amount)
            ->assign('currency', $currency)
            ->fetch($oPlugin->cFrontendPfad . 'template/presentment-loader.tpl');

        return $tplData;
    }

    return null;
};

if ($pageType === PAGE_ARTIKEL && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_article'] === 'Y') {
    $article  = $smarty->getTemplateVars('Artikel');

    if (((int)$article->nIstVater == 0 || ((int)$article->nIstVater == 1 && (int)$article->kVariKindArtikel > 0)) && (int)$article->inWarenkorbLegbar == 1) {
        $amount   = $article->Preise->fVKBrutto;
        $currency = $_SESSION['Waehrung']->cISO;

        $tplData  = $presentmentLoader($amount, $currency);

        $selector = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_article_selector'];
        $method   = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_article_method'];

        if ($tplData)
            pq($selector)->{$method}($tplData);
    }
}

if ($pageType === PAGE_WARENKORB && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_cart_box'] === 'Y') {
    $amount   = $_SESSION['Warenkorb']->gibGesamtsummeWarenOhne(array(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG), true);
    $currency = $_SESSION['Waehrung']->cISO;

    $tplData = $presentmentLoader($amount, $currency);

    $selector = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_cart_box_selector'];
    $method   = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_finance_cart_box_method'];

    if ($tplData)
        pq($selector)->{$method}($tplData);
}

if ($pageType == PAGE_BESTELLVORGANG) {
    require_once str_replace(
        'frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalFinance.class.php';

    $payPalFinance = new PayPalFinance();

    $subtotal = $_SESSION['Warenkorb']->gibGesamtsummeWarenOhne(
        array(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG), true);

    if ($payPalFinance->isVisible($subtotal) && $payPalFinance->isSelected()) {
        $financing = $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(
            array(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG), true);

        $subtotalAmount  = gibPreisStringLocalized($subtotal, $_SESSION['Waehrung']->cISO);
        $financingAmount = gibPreisStringLocalized($financing, $_SESSION['Waehrung']->cISO);

        $tplData = Shop::Smarty()
            ->assign('subtotal', $subtotal)
            ->assign('tplscope', 'confirmation')
            ->assign('subtotalAmount', $subtotalAmount)
            ->assign('financingAmount', $financingAmount)
            ->fetch($oPlugin->cFrontendPfad . 'template/paypalfinance-order-items.tpl');

        pq('table.order-items tfoot, table.order-items tr.type-13')->remove();
        pq('table.order-items')->append($tplData);
        pq('#panel-edit-coupon')->parent()->remove();
    }
}

if ($pageType === PAGE_WARENKORB || ($pageType === PAGE_ARTIKEL && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article'] === 'Y')) {
    require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalExpress.class.php';

    $payPalExpress    = new PayPalExpress();
    $pqMethodCart     = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_method'];
    $pqSelectorCart   = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_selector'];
    $cartClass        = 'paypalexpress btn-ppe-cart';
    $pqMethodArticle  = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article_method'];
    $pqSeletorArticle = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article_selector'];
    $articleClass     = 'paypalexpress';
    $btnType          = ($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_type'] === 'silver') ? '-alt' : '';
    $btnSize          = (isset($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_size'])) ? $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_size'] : 'medium'; //@todo: remove check.
    $allowedISO       = ['de', 'en', 'es', 'fr', 'nl'];
    $iso              = StringHandler::convertISO2ISO639($_SESSION['cISOSprache']);
    $iso              = (!in_array($iso, $allowedISO)) ? 'de' : $iso;
    $countries        = '';
    $BoxOpen          = '';
    $ArtikelForm      = '';
    $paypal_btn       = $oPlugin->cFrontendPfadURLSSL . 'images/buttons/' . $iso . '/checkout-logo-' . $btnSize . $btnType . '-' . $iso . '.png';

    if ($pageType === PAGE_WARENKORB && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button'] === 'Y') {
        $oArtikel_arr = PayPalHelper::getProducts();
        if ($payPalExpress->zahlungErlaubt($oArtikel_arr)) {
            $link = PayPalHelper::getLinkByName($oPlugin, 'PayPalExpress');
            if ($link !== null) {
                pq($pqSelectorCart)->$pqMethodCart(
                    '<a href="index.php?s=' . $link->kLink . '&jtl_paypal_checkout_cart=1" class="' . $cartClass . '">' .
                    '  <img src="' . $paypal_btn . '" alt="' . $oPlugin->cName . '" />' .
                    '</a>'
                );
                $footer_class = $cartClass;
                $baseURL      = 'warenkorb.php?';
            }
        }
    } elseif ($pageType === PAGE_ARTIKEL && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article'] === 'Y') {
        $oArtikel = $smarty->get_template_vars('Artikel');
        if ($payPalExpress->zahlungErlaubt([$oArtikel])) {
            pq($pqSeletorArticle)->$pqMethodArticle(
                '<button name="jtl_paypal_redirect" type="submit" value="2" class="' . $articleClass . '">' .
                '  <img src="' . $paypal_btn . '" alt="' . $oPlugin->cName . '" />' .
                '</button>'
            );
        }
    }
}
