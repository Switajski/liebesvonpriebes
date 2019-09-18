<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalFinance.class.php';

$message = null;
$ordersPerPage = 10;
$finance = new PayPalFinance();

if (isset($_GET['capture']) && (int)$_GET['capture'] > 0) {
    $order = new Bestellung((int)$_GET['capture']);
    $order->fuelleBestellung(1, 0, false);

    if ((int)$order->kBestellung > 0 && (int)$finance->paymentId === (int)$order->kZahlungsart) {
        try {
            $capture = $finance->capture($order->kBestellung, true);
            $message = ['success' => "Zahlung wurde erfolgreich erfasst '{$capture->getId()}'"];
        }
        catch (Exception $e) {
            $message = ['danger' => $e->getMessage()];
        }
    }
}
elseif (isset($_GET['details']) && (int)$_GET['details'] > 0) {
    $order = new Bestellung((int)$_GET['details']);
    $order->fuelleBestellung(1, 0, false);

    if ((int)$order->kBestellung > 0 && (int)$finance->paymentId === (int)$order->kZahlungsart) {
        $payment = $finance->get($order->cSession);
        dump($payment->toArray());
    }
}

$ids = array_map(function($method) {
    return (int)$method->kZahlungsart;
}, $oPlugin->oPluginZahlungsmethodeAssoc_arr);

$sqlFilter = sprintf('IN(%s)', implode(', ', array_values($ids)));

$res = Shop::DB()->query("SELECT COUNT(*) AS cnt FROM tbestellung WHERE kZahlungsart {$sqlFilter}", 1);

$config = baueBlaetterNaviGetterSetter(1, $ordersPerPage);
$pagination = baueBlaetterNavi($config->nAktuelleSeite1, $res->cnt, $ordersPerPage);

$orderKeys = Shop::DB()->query(
    "SELECT kBestellung as id FROM tbestellung WHERE kZahlungsart {$sqlFilter} ORDER BY dErstellt DESC {$config->cSQL1}", 2);

$receivedPaymentSql = <<<SQL
    SELECT
        SUM(fBetrag) AS fBetrag,
        SUM(fZahlungsgebuehr) AS fZahlungsgebuehr,
        cISO, cZahler, dZeit, cHinweis, cAbgeholt
    FROM
        tzahlungseingang
    WHERE
        kBestellung = :id
    GROUP BY
        kBestellung
SQL;

$orders = [];
$payments = [];

foreach ($orderKeys as $key) {
    $order = new Bestellung($key->id);
    $order->fuelleBestellung(1, 0, false);

    if ((int)$order->kBestellung === 0)
        continue;

    $orders[$key->id] = $order;

    $payment = Shop::DB()->executeQueryPrepared(
        $receivedPaymentSql, ['id' => $key->id], 1);

    if ($payment) {
        $payments[$key->id] = $payment;
    }
}

$smarty->assign('orders', $orders)
    ->assign('payments', $payments)
    ->assign('finance', $finance)
    ->assign('message', $message)
    ->assign('pagination', $pagination)
    ->assign('hash', '#plugin-tab-' . ($_adminMenu ? $_adminMenu->kPluginAdminMenu : '0'))
    ->display($oPlugin->cAdminmenuPfad . 'templates/orders.tpl');