<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$results  = null;
$type     = isset($_POST['validate']) ? $_POST['validate'] : null;
$security = isset($_POST['security']) ? $_POST['security'] : null;

require_once dirname(__FILE__) . '/tlscheck.php';

if ($type) {
    $module = "kPlugin_{$oPlugin->kPlugin}_paypal";
    $method = "/" . str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad);

    switch ($type) {
        case 'basic':
            $module = "{$module}{$type}";
            require_once $method . 'class/PayPalBasic.class.php';
            $payPal  = new PayPalBasic($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = $payPal->test();
            break;
        case 'express':
            $module = "{$module}{$type}";
            require_once $method . 'class/PayPalExpress.class.php';
            $payPal  = new PayPalExpress($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = $payPal->test();
            break;
        case 'plus':
            $module = "{$module}{$type}";
            require_once $method . 'class/PayPalPlus.class.php';
            $payPal  = new PayPalPlus($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = ['status' => $payPal->isConfigured(true) ? 'success' : 'error'];
            break;
        case 'finance':
            $module = "{$module}{$type}";
            require_once $method . 'class/PayPalFinance.class.php';
            $payPal  = new PayPalFinance($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = ['status' => $payPal->isConfigured(true) ? 'success' : 'error'];
            break;
    }
    $results['type'] = $type;
} elseif (isset($security)) {
    $smarty->assign('tlsResponse', paypal_tlstest());
}

$smarty->assign('results', $results)
       ->assign('post_url', Shop::getURL(true) . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '')
       ->display($oPlugin->cAdminmenuPfad . 'templates/infos.tpl');
