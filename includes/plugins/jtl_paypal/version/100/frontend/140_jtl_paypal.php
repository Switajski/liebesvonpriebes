<?php
/**
 * HOOK_SMARTY_OUTPUTFILTER.
 *
 * adds buttons to article or cart pages, inserts css
 */
$pageType = Shop::getPageType();

if ($pageType === PAGE_WARENKORB || ($pageType === PAGE_ARTIKEL && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article'] === 'Y')) {
    require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalExpress.class.php';

    $payPalExpress    = new PayPalExpress();
    $pqMethodCart     = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_method'];
    $pqSelectorCart   = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_selector'];
    $cartClass        = 'paypalexpress btn-ppe-cart';//$oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_class'];
    $pqMethodArticle  = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article_method'];
    $pqSeletorArticle = $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article_selector'];
    $articleClass     = 'paypalexpress';
    $btnType          = ($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_type'] === 'silver') ? '-alt' : '';
    $btnSize          = (isset($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_size'])) ? $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button_size'] : 'medium'; //@todo: remove check.
    $allowedISO       = array('de', 'en', 'es', 'fr', 'nl');
    $iso              = StringHandler::convertISO2ISO639($_SESSION['cISOSprache']);
    $iso              = (!in_array($iso, $allowedISO)) ? 'de' : $iso;
    $countries        = '';
    $BoxOpen          = '';
    $ArtikelForm      = '';
    $paypal_btn       = $oPlugin->cFrontendPfadURLSSL . 'images/buttons/' . $iso . '/checkout-logo-' . $btnSize . $btnType . '-' . $iso . '.png';

    if ($pageType === PAGE_WARENKORB && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_cart_button'] === 'Y') {
        $oArtikel_arr = array();
        foreach ($_SESSION['Warenkorb']->PositionenArr as $Positionen) {
            if ($Positionen->nPosTyp !== C_WARENKORBPOS_TYP_VERSANDPOS) {
                $oArtikel_arr[] = $Positionen->Artikel;
            }
        }
        if ($payPalExpress->zahlungErlaubt($oArtikel_arr)) {
            $link = PayPalHelper::getLinkByName($oPlugin, 'PayPalExpress');
            if ($link !== null) {
                pq($pqSelectorCart)->$pqMethodCart(
                    '<a href="index.php?s=' . $link->kLink . '&jtl_paypal_checkout_cart=1" class="' . $cartClass . '">' .
                    '  <img src="' . $paypal_btn . '" alt="' . $oPlugin->cName . '" />' .
                    '</a>'
                );
                pq('head')->append('<link type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/style.css" rel="stylesheet" media="screen">');
                $footer_class = $cartClass;
                $baseURL      = 'warenkorb.php?';
            }
        }
    } elseif ($pageType === PAGE_ARTIKEL && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article'] === 'Y') {
        $oArtikel = $smarty->get_template_vars('Artikel');
        if ($payPalExpress->zahlungErlaubt(array($oArtikel))) {
            pq($pqSeletorArticle)->$pqMethodArticle(
                '<button name="jtl_paypal_redirect" type="submit" value="2" class="' . $articleClass . '">' .
                '  <img src="' . $paypal_btn . '" alt="' . $oPlugin->cName . '" />' .
                '</button>'
            );
            pq('head')->append('<link type="text/css" href="' . $oPlugin->cFrontendPfadURLSSL . 'css/style.css" rel="stylesheet" media="screen">');
        }
    }
}
