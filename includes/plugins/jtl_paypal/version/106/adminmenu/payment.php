<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$table = 'xplugin_jtl_paypal_additional_payment';

if (isset($_POST['save'])) {
    Shop::DB()->query("truncate {$table}", 4);
    $items = array_slice($_POST['payment'], 0, 5);
    foreach ($items as $i => $id) {
        $item = [
            'paymentId' => $id,
            'sort'      => $i,
        ];
        Shop::DB()->insert($table, (object) $item);
    }
}

$payments         = Shop::DB()->query("SELECT * FROM tzahlungsart WHERE nActive = 1 AND cAnbieter != 'PayPal' ORDER BY cAnbieter, cName, nSort, kZahlungsart", 2);
$selectedPayments = Shop::DB()->query("SELECT * FROM {$table}", 2);

$selectedPaymentKeys = array_map(function ($o) {
  return intval($o->paymentId);
}, $selectedPayments);

$payments = array_map(function ($o) use ($selectedPayments) {
    $o->sort = 999;
    $o->checked = false;
    foreach ($selectedPayments as $p) {
        if ($p->paymentId == $o->kZahlungsart) {
            $o->sort = $p->sort;
            $o->checked = true;
            break;
        }
    }

    return $o;
}, $payments);

usort($payments, function ($a, $b) {
    if ($a->sort > $b->sort) {
        return 1;
    } elseif ($a->sort < $b->sort) {
        return -1;
    }

    return 0;
});

$pspType = isset($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_psp_type']) ?
    (int)$oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_psp_type'] : 0;

$smarty
       ->assign('pspType', $pspType)
       ->assign('payments', $payments)
       ->assign('saved', isset($_POST['save']))
       ->assign('selectedPayments', $selectedPayments)
       ->assign('selectedPaymentKeys', $selectedPaymentKeys)
       ->assign('postUrl', Shop::getURL(true) . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '#payment')
       ->display($oPlugin->cAdminmenuPfad . 'templates/payment.tpl');
