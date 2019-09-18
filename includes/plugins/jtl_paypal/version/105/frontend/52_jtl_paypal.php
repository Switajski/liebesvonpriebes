<?php

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

/*
 * HOOK_WARENKORB_PAGE
 */
if (isset($_GET['jtl_paypal_redirect'])) {
    $link = PayPalHelper::getLinkByName($oPlugin, 'PayPalExpress');
    if ($link !== null) {
        if (isset($_GET['return'])) {
            header('Location: ' . URL_SHOP . '/index.php?s=' . $link->kLink . '&return=1&jtl_paypal_redirect=1');
        } else {
            header('Location: ' . URL_SHOP . '/index.php?s=' . $link->kLink . '&jtl_paypal_redirect=1');
        }
    }
    exit;
}

if (isset($_GET['fillOut'])) {
    $message = '';
    switch ($_GET['fillOut']) {
        case 'ppexpress_max':
            $summe   = gibPreisStringLocalized($_GET['max']);
            $message = str_replace('%Wert%', $summe, $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_warenkorb_max']);
            break;
        case 'ppexpress_min':
            $summe   = gibPreisStringLocalized($_GET['min']);
            $message = str_replace('%Wert%', $summe, $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_warenkorb_min']);
            break;
        case 'ppexpress_notallowed':
            $message = $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_notallowed'];
            break;
        case 'ppexpress_blocked':
            $message = Shop::Lang()->get('accountLocked', 'global');
            break;
        case 'ppexpress_inactive':
            $message = Shop::Lang()->get('accountInactive', 'global');
            break;
        case 'ppexpress_internal':
            $message = Shop::Lang()->get('paypalHttpError', 'paymentMethods');
            break;
        case 'ppbasic_internal':
            $message = Shop::Lang()->get('paypalHttpError', 'paymentMethods');
            break;
    }
    if (!empty($message)) {
        $smarty->assign('MsgWarning', $message);
    }
}
