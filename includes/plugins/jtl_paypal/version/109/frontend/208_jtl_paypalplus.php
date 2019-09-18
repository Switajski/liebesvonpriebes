<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// HOOK_SMARTY_FETCH_TEMPLATE

$tpls = ['checkout/step4_payment_options.tpl', 'tpl_inc/bestellvorgang_zahlung.tpl'];

if (in_array($args_arr['original'], $tpls, true) && Shop::Smarty()->getTemplateVars('payPalPlus') === true) {

    // identify the template
    //
    $szTemplatePathParent = '';
    $oTemplate            = Template::getInstance();
    $szParentName         = $oTemplate->getParent();
    if (null !== $szParentName || '' !== $szParentName) {
        $szTemplatePathParent = PFAD_TEMPLATES  . $szParentName  . '/checkout/';
    }
    $szTemplatePathCurrent = PFAD_TEMPLATES . $oTemplate->getDir() . '/checkout/' ;

    // try to find the trustedshops-snippet
    //
    $szPluginTemplatePath      = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_FRONTEND . 'template/';
    $szTrustedShopsTplSnippet  = 'inc_payment_trustedshops.tpl';
    $ts_tpl                    = null;
    if (file_exists(PFAD_ROOT . $szTemplatePathCurrent . $szTrustedShopsTplSnippet)) {
        $ts_tpl = $szTemplatePathCurrent . $szTrustedShopsTplSnippet;
    } elseif (file_exists(PFAD_ROOT . $szTemplatePathParent . $szTrustedShopsTplSnippet)) {
        $ts_tpl = $szTemplatePathParent . $szTrustedShopsTplSnippet;
    } elseif (file_exists($szPluginTemplatePath . $szTrustedShopsTplSnippet)) {
        $ts_tpl = $szTrustedShopsTplSnippet;
    }
    Shop::Smarty()->assign('ts_tpl', $ts_tpl);

    $args_arr['out'] = $szPluginTemplatePath . 'paypalplus.tpl';
}

