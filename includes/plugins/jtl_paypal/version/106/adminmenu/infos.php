<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$results  = null;
$type     = isset($_POST['validate']) ? $_POST['validate'] : null;
$security = isset($_POST['security']) ? $_POST['security'] : null;

if ($type) {
    $module = "kPlugin_{$oPlugin->kPlugin}_paypal";

    switch ($type) {
        case 'basic':
            $module = "{$module}{$type}";
            require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalBasic.class.php';
            $payPal  = new PayPalBasic($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = $payPal->test();
            break;
        case 'express':
            $module = "{$module}{$type}";
            require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalExpress.class.php';
            $payPal  = new PayPalExpress($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = $payPal->test();
            break;
        case 'plus':
            $module = "{$module}{$type}";
            require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalPlus.class.php';
            $payPal  = new PayPalPlus($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = ['status' => 'success', 'msg' => ''];
            try {
                $payPal->isConfigured();
            } catch (Exception $ex) {
                $results = ['status' => 'Error', 'msg' => $ex->getMessage()];
            }
            break;
    }
    $results['type'] = $type;
} elseif (isset($security)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSLVERSION, 1);
    curl_setopt($ch, CURLOPT_URL, 'https://tlstest.paypal.com');
    $isValid = curl_exec($ch) === true;
    curl_close($ch);
    $smarty->assign('securityCheck', $isValid);
}

$smarty->assign('results', $results)
       ->assign('post_url', Shop::getURL(true) . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '')
       ->display($oPlugin->cAdminmenuPfad . 'templates/infos.tpl');
