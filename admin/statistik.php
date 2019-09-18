<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';

$nStatsType = verifyGPCDataInteger('s');
switch ($nStatsType) {
    case 1:
        $oAccount->permission('STATS_VISITOR_VIEW', true, true);
        break;
    case 2:
        $oAccount->permission('STATS_VISITOR_LOCATION_VIEW', true, true);
        break;
    case 3:
        $oAccount->permission('STATS_CRAWLER_VIEW', true, true);
        break;
    case 4:
        $oAccount->permission('STATS_EXCHANGE_VIEW', true, true);
        break;
    case 5:
        $oAccount->permission('STATS_LANDINGPAGES_VIEW', true, true);
        break;
    default:
        $oAccount->redirectOnFailure();
        break;
}
/** @global JTLSmarty $smarty */
$cHinweis          = '';
$cFehler           = '';
$nAnzeigeIntervall = 0;

if (!isset($_SESSION['Statistik'])) {
    $_SESSION['Statistik']       = new stdClass();
    $_SESSION['Statistik']->nTyp = STATS_ADMIN_TYPE_BESUCHER;
}
// Stat Typ
if (verifyGPCDataInteger('s') > 0) {
    $_SESSION['Statistik']->nTyp = verifyGPCDataInteger('s');
}

$oFilter    = new Filter('statistics');
$oDateRange = $oFilter->addDaterangefield(
    'Zeitraum', '',
    date_create()->modify('-1 year')->modify('+1 day')->format('d.m.Y') . ' - ' .
    date('d.m.Y')
);
$oFilter->assemble();
$nDateStampVon = strtotime($oDateRange->getStart());
$nDateStampBis = strtotime($oDateRange->getEnd());

$oStat_arr = gibBackendStatistik($_SESSION['Statistik']->nTyp, $nDateStampVon, $nDateStampBis, $nAnzeigeIntervall);
// Highchart
if ($_SESSION['Statistik']->nTyp == STATS_ADMIN_TYPE_KUNDENHERKUNFT ||
    $_SESSION['Statistik']->nTyp == STATS_ADMIN_TYPE_SUCHMASCHINE || $_SESSION['Statistik']->nTyp == STATS_ADMIN_TYPE_EINSTIEGSSEITEN) {
    $smarty->assign('piechart', preparePieChartStats(
        $oStat_arr,
        GetTypeNameStats($_SESSION['Statistik']->nTyp),
        getAxisNames($_SESSION['Statistik']->nTyp))
    );
} else {
    $smarty->assign('linechart', prepareLineChartStats(
        $oStat_arr,
        GetTypeNameStats($_SESSION['Statistik']->nTyp),
        getAxisNames($_SESSION['Statistik']->nTyp))
    );
    $member_arr = gibMappingDaten($_SESSION['Statistik']->nTyp);
    $smarty->assign('ylabel', $member_arr['nCount']);
}
// Table
$cMember_arr = [];
if (is_array($oStat_arr) && count($oStat_arr) > 0) {
    foreach ($oStat_arr as $oStat) {
        $cMember_arr[] = array_keys(get_object_vars($oStat));
    }
}

$oPagination = (new Pagination())
    ->setItemCount(count($oStat_arr))
    ->assemble();

$smarty->assign('headline', GetTypeNameStats($_SESSION['Statistik']->nTyp))
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('nTyp', $_SESSION['Statistik']->nTyp)
    ->assign('oStat_arr', $oStat_arr)
    ->assign('oStatJSON', getJSON($oStat_arr, $nAnzeigeIntervall, $_SESSION['Statistik']->nTyp))
    ->assign('cMember_arr', mappeDatenMember($cMember_arr, gibMappingDaten($_SESSION['Statistik']->nTyp)))
    ->assign('STATS_ADMIN_TYPE_BESUCHER', STATS_ADMIN_TYPE_BESUCHER)
    ->assign('STATS_ADMIN_TYPE_KUNDENHERKUNFT', STATS_ADMIN_TYPE_KUNDENHERKUNFT)
    ->assign('STATS_ADMIN_TYPE_SUCHMASCHINE', STATS_ADMIN_TYPE_SUCHMASCHINE)
    ->assign('STATS_ADMIN_TYPE_UMSATZ', STATS_ADMIN_TYPE_UMSATZ)
    ->assign('STATS_ADMIN_TYPE_EINSTIEGSSEITEN', STATS_ADMIN_TYPE_EINSTIEGSSEITEN)
    ->assign('nPosAb', $oPagination->getFirstPageItem())
    ->assign('nPosBis', $oPagination->getFirstPageItem() + $oPagination->getPageItemCount())
    ->assign('oPagination', $oPagination)
    ->assign('oFilter', $oFilter)
    ->display('statistik.tpl');
