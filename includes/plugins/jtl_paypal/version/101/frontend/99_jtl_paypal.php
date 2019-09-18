<?php
/**
 * HOOK_LETZTERINCLUDE_INC.
 */
if (isset($_POST['jtl_paypal_redirect']) && $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_shipping_pre'] === 'Y') {
    $smarty->assign('bWarenkorbHinzugefuegt', false);
}
