<?php

$table = 'xplugin_jtl_paypal_additional_payment';

if (isset($_POST['save'])) {
    Shop::DB()->query("truncate {$table}", 4);
    $items = array_slice($_POST['payment'], 0, 5);
    foreach ($items as $i => $id) {
        $item = array(
            'paymentId' => $id,
            'sort'      => $i,
        );
        Shop::DB()->insert($table, (object) $item);
    }
}

$payments         = Shop::DB()->query("SELECT * FROM tzahlungsart WHERE nActive = 1 AND cAnbieter != 'PayPal' AND cModulId NOT IN('za_kreditkarte_jtl', 'za_rechnung_jtl', 'za_lastschrift_jtl') ORDER BY cAnbieter, cName, nSort, kZahlungsart", 2);
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

$smarty->assign('payments', $payments)
    ->assign('saved', isset($_POST['save']))
    ->assign('selectedPayments', $selectedPayments)
    ->assign('selectedPaymentKeys', $selectedPaymentKeys)
    ->assign('postUrl', URL_SHOP . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '#payment');

$smarty->display($oPlugin->cAdminmenuPfad . 'templates/payment.tpl');
