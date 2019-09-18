<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// HOOK_SMARTY_FETCH_TEMPLATE

$tpls = ['checkout/step4_payment_options.tpl', 'tpl_inc/bestellvorgang_zahlung.tpl'];

if (in_array($args_arr['original'], $tpls) && Shop::Smarty()->get_template_vars('payPalPlus') === true) {
    $args_arr['out'] = PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis.'/'.PFAD_PLUGIN_VERSION.$oPlugin->nVersion.'/'.PFAD_PLUGIN_FRONTEND.'template/paypalplus.tpl';
}
