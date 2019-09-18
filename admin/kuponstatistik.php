<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('STATS_COUPON_VIEW', true, true);
/** @global JTLSmarty $smarty */
$step        = 'kuponstatistik_uebersicht';
$cWhere      = '';
$coupons_arr = Shop::DB()->query("SELECT kKupon, cName FROM tkupon ORDER BY cName DESC", 9);
$oDateShop   = Shop::DB()->query("SELECT MIN(DATE(dZeit)) AS startDate FROM tbesucherarchiv", 1);
$startDate   = DateTime::createFromFormat('Y-m-j', $oDateShop->startDate);
$endDate     = DateTime::createFromFormat('Y-m-j', date('Y-m-j'));

if (isset($_POST['formFilter']) && $_POST['formFilter'] > 0 && validateToken()) {
    if ((int)$_POST['kKupon'] > -1) {
        $cWhere = "(SELECT kKupon 
                        FROM tkuponbestellung 
                        WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung 
                        LIMIT 0, 1
                    ) = " . (int)$_POST['kKupon'] . " AND";
        foreach ($coupons_arr as $key => $value) {
            if ($value['kKupon'] == (int)$_POST['kKupon']) {
                $coupons_arr[$key]['aktiv'] = 1;
                break;
            }
        }
    }

    $dateRange_arr = explode(' - ', $_POST['daterange']);
    $endDate       = (DateTime::createFromFormat('Y-m-j', $dateRange_arr[1])
        && (DateTime::createFromFormat('Y-m-j', $dateRange_arr[1]) > $startDate)
        && (DateTime::createFromFormat('Y-m-j', $dateRange_arr[1]) < DateTime::createFromFormat('Y-m-j', date('Y-m-j'))))
        ? DateTime::createFromFormat('Y-m-j', $dateRange_arr[1])
        : DateTime::createFromFormat('Y-m-j', date('Y-m-j'));

    if (DateTime::createFromFormat('Y-m-j', $dateRange_arr[0])
        && (DateTime::createFromFormat('Y-m-j', $dateRange_arr[0]) < $endDate)
        && (DateTime::createFromFormat('Y-m-j', $dateRange_arr[0]) >= $startDate)) {
        $startDate = DateTime::createFromFormat('Y-m-j', $dateRange_arr[0]);
    } else {
        $oneMonth  = clone $endDate;
        $oneMonth  = $oneMonth->modify('-1month');
        $startDate = DateTime::createFromFormat('Y-m-j', $oneMonth->format('Y-m-d'));
    }
} else {
    $oneMonth  = $endDate;
    $oneMonth  = $oneMonth->modify('-1week');
    $startDate = DateTime::createFromFormat('Y-m-j', $oneMonth->format('Y-m-d'));
    $endDate   = DateTime::createFromFormat('Y-m-j', date('Y-m-j'));
}

$dStart = $startDate->format('Y-m-d 00:00:00');
$dEnd   = $endDate->format('Y-m-d 23:59:59');

$usedCouponsOrder = KuponBestellung::getOrdersWithUsedCoupons($dStart, $dEnd, verifyGPDataString('kKupon'));

$nCountOrders_arr = Shop::DB()->query(
    "SELECT count(*) AS nCount
        FROM tbestellung
        WHERE dErstellt BETWEEN '" . $dStart . "'
            AND '" . $dEnd . "'
            AND tbestellung.cStatus != " . BESTELLUNG_STATUS_STORNO, 8
);

$nCountUsedCouponsOrder = 0;
$nCountCustomers        = 0;
$nShoppingCartAmountAll = 0;
$nCouponAmountAll       = 0;
$tmpUser                = [];
$date                   = [];
if (isset($usedCouponsOrder) && is_array($usedCouponsOrder)) {
    foreach ($usedCouponsOrder as $key => $usedCouponOrder) {
        $oKunde                              = new Kunde($usedCouponOrder['kKunde']);
        $usedCouponsOrder[$key]['cUserName'] = $oKunde->cVorname . ' ' . $oKunde->cNachname;
        unset($oKunde);
        $usedCouponsOrder[$key]['nCouponValue']        =
            gibPreisLocalizedOhneFaktor($usedCouponOrder['fKuponwertBrutto']);
        $usedCouponsOrder[$key]['nShoppingCartAmount'] =
            gibPreisLocalizedOhneFaktor($usedCouponOrder['fGesamtsummeBrutto']);
        $usedCouponsOrder[$key]['cOrderPos_arr']       = Shop::DB()->query("
            SELECT CONCAT_WS(' ',wk.cName,wk.cHinweis) AS cName,
                wk.fPreis+(wk.fPreis/100*wk.fMwSt) AS nPreis, wk.nAnzahl
                FROM twarenkorbpos AS wk
                LEFT JOIN tbestellung AS bs 
                    ON wk.kWarenkorb = bs.kWarenkorb
                WHERE bs.kBestellung = " . (int)$usedCouponOrder['kBestellung'], 9
        );
        foreach ($usedCouponsOrder[$key]['cOrderPos_arr'] as $posKey => $value) {
            $usedCouponsOrder[$key]['cOrderPos_arr'][$posKey]['nAnzahl']      =
                str_replace('.', ',', number_format($value['nAnzahl'], 2));
            $usedCouponsOrder[$key]['cOrderPos_arr'][$posKey]['nPreis']       =
                gibPreisLocalizedOhneFaktor($value['nPreis']);
            $usedCouponsOrder[$key]['cOrderPos_arr'][$posKey]['nGesamtPreis'] =
                gibPreisLocalizedOhneFaktor($value['nAnzahl'] * $value['nPreis']);
        }

        $nCountUsedCouponsOrder++;
        $nShoppingCartAmountAll += $usedCouponOrder['fGesamtsummeBrutto'];
        $nCouponAmountAll += (float)$usedCouponOrder['fKuponwertBrutto'];
        if (!in_array($usedCouponOrder['kKunde'], $tmpUser)) {
            $nCountCustomers++;
            $tmpUser[] = $usedCouponOrder['kKunde'];
        }
        $date[$key] = $usedCouponOrder['dErstellt'];
    }
    array_multisort($date, SORT_DESC, $usedCouponsOrder);
}

$nPercentCountUsedCoupons = (isset($nCountOrders_arr['nCount']) && (int)$nCountOrders_arr['nCount'] > 0)
    ? number_format(100 / (int)$nCountOrders_arr['nCount'] * $nCountUsedCouponsOrder, 2)
    : 0;
$overview_arr                  = [
    'nCountUsedCouponsOrder'   => $nCountUsedCouponsOrder,
    'nCountCustomers'          => $nCountCustomers,
    'nCountOrder'              => $nCountOrders_arr['nCount'],
    'nPercentCountUsedCoupons' => $nPercentCountUsedCoupons,
    'nShoppingCartAmountAll'   => gibPreisLocalizedOhneFaktor($nShoppingCartAmountAll),
    'nCouponAmountAll'         => gibPreisLocalizedOhneFaktor($nCouponAmountAll)
];

$smarty->assign('overview_arr', $overview_arr)
    ->assign('usedCouponsOrder', $usedCouponsOrder)
    ->assign('startDateShop', $oDateShop->startDate)
    ->assign('startDate', $startDate->format('Y-m-d'))
    ->assign('endDate', $endDate->format('Y-m-d'))
    ->assign('coupons_arr', $coupons_arr)
    ->assign('step', $step)
    ->display('kuponstatistik.tpl');
