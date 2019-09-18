<?php

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalPlus.class.php';
$module = "kPlugin_{$oPlugin->kPlugin}_paypalplus";
$payPal = new PayPalPlus($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);

if (isset($_POST['reset'])) {
    $payPal->clearWebhooks();
    $payPal->setWebhooks();
}

if ($payPal->isConfigured()) {
    $webhookList = $payPal->getWebhooks();
    $smarty->assign('webhookList', $webhookList);
}

$smarty->assign('reset', isset($_POST['reset']))
    ->assign('postUrl', URL_SHOP . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '#webhooks');

$smarty->display($oPlugin->cAdminmenuPfad . 'templates/webhooks.tpl');
