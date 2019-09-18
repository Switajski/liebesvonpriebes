<?php
// HOOK_SMARTY_FETCH_TEMPLATE

if ($args_arr['original'] == 'checkout/step4_payment_options.tpl' && Shop::Smarty()->get_template_vars('payPalPlus') === true) {
    $args_arr['out'] = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_FRONTEND . 'template/paypalplus.tpl';
}
