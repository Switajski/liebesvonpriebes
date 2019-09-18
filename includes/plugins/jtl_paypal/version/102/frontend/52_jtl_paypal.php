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

if (isset($_GET['fehler'])) {
    if ($_GET['fehler'] === 'gesperrt') {
        $smarty->assign('MsgWarning', (class_exists('Shop')) ? Shop::Lang()->get('accountLocked', 'global') : $GLOBALS['oSprache']->gibWert('accountLocked', 'global'));
    }

    if ($_GET['fehler'] === 'inaktiv') {
        $smarty->assign('MsgWarning', (class_exists('Shop')) ? Shop::Lang()->get('accountInactive', 'global') : $GLOBALS['oSprache']->gibWert('accountInactive', 'global'));
    }
}

if (isset($_GET['fillOut'])) {
    if ($_GET['fillOut'] === 'ppexpress_max') {
        $summe          = gibPreisStringLocalized($_GET['max']);
        $sprachvariable = str_replace('%Wert%', $summe, $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_warenkorb_max']);
        $smarty->assign('MsgWarning', $sprachvariable);
    }

    if ($_GET['fillOut'] === 'ppexpress_min') {
        $summe          = gibPreisStringLocalized($_GET['min']);
        $sprachvariable = str_replace('%Wert%', $summe, $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_warenkorb_min']);
        $smarty->assign('MsgWarning', $sprachvariable);
    }

    if ($_GET['fillOut'] === 'ppexpress_notallowed') {
        $smarty->assign('MsgWarning', $oPlugin->oPluginSprachvariableAssoc_arr['jtl_paypal_notallowed']);
    }
}
