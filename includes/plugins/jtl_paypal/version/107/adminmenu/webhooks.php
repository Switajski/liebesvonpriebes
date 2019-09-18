<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalPlus.class.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalFinance.class.php';

$webhooks = array();
$restPaymentMethods = array(new PayPalPlus(), new PayPalFinance());

if (isset($_POST['reset'])) {
    foreach ($restPaymentMethods as $paymentMethod) {
        PayPalHelper::deleteWebhook($paymentMethod);
        PayPalHelper::setWebhook($paymentMethod);
    }
}

foreach ($restPaymentMethods as $paymentMethod) {
    $webhooks[] = (object)[
        'name' => $paymentMethod->name,
        'configured' => $paymentMethod->isConfigured(false),
        'url' => PayPalHelper::getWebhookUrl($paymentMethod),
        'hook' => PayPalHelper::getWebhook($paymentMethod),
    ];
}

$postUrl = Shop::getURL(true) . '/' . PFAD_ADMIN .
    'plugin.php?kPlugin=' . $oPlugin->kPlugin . '#plugin-tab-' . ($_adminMenu ? $_adminMenu->kPluginAdminMenu : '0');

$smarty->assign('reset', isset($_POST['reset']))
       ->assign('postUrl', $postUrl)
       ->assign('webhooks', $webhooks)
       ->display($oPlugin->cAdminmenuPfad . 'templates/webhooks.tpl');
