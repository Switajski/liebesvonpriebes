<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// Bot?
$bBot = (isset($_SESSION['oBesucher']->kBesucherBot) && $_SESSION['oBesucher']->kBesucherBot > 0);

if (!$oPlugin->getConf('cProjectId')) {
    $oObj_arr = Shop::DB()->query("SELECT * FROM tjtlsearchserverdata", 2);
    foreach ($oObj_arr as $oObj) {
        if (isset($oObj->cKey) && strlen($oObj->cKey) > 0) {
            switch ($oObj->cKey) {
                case 'cProjectId':
                    $oPlugin->setConf('cProjectId', $oObj->cValue);
                    break;

                case 'cAuthHash':
                    $oPlugin->setConf('cAuthHash', $oObj->cValue);
                    break;

                case 'cServerUrl':
                    $oPlugin->setConf('cServerUrl', $oObj->cValue);
                    break;
            }
        }
    }
}

if (!$bBot && isset($args_arr['bExtendedJTLSearch']) && $args_arr['bExtendedJTLSearch'] &&
    isset($args_arr['cValue']) && $args_arr['nArtikelProSeite'] && $args_arr['nSeite'] &&
    strlen($oPlugin->getConf('cProjectId')) > 0 && strlen($oPlugin->getConf('cAuthHash')) > 0 && strlen($oPlugin->getConf('cServerUrl')) > 0
) {
    require_once "{$oPlugin->cFrontendPfad}../includes/defines_inc.php";
    require_once "{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php";
    require_once "{$oPlugin->cFrontendPfad}../classes/class.QueryTracking.php";
    // Filter and url extending
    $cFilter_arr = JtlSearch::getFilter($_GET);
    $cFilter     = JtlSearch::buildFilterURL($cFilter_arr);
    // Sorting
    $cSort = JtlSearch::getSorting($args_arr['nSortierung'], $_SESSION['Waehrung']->cISO, $_SESSION['Kundengruppe']->kKundengruppe);
    // Overall time start
    $nOverallTime = microtime(true);
    // Main search call
    $args_arr['oExtendedJTLSearchResponse'] = JtlSearch::doSearch(
        md5(session_id()),
        $_SESSION['Kundengruppe']->kKundengruppe,
        $_SESSION['cISOSprache'],
        $_SESSION['Waehrung']->cISO,
        $args_arr['cValue'],
        $oPlugin->getConf('cProjectId'),
        $oPlugin->getConf('cAuthHash'),
        urldecode($oPlugin->getConf('cServerUrl')),
        $args_arr['nArtikelProSeite'],
        $args_arr['nSeite'],
        $args_arr['bLagerbeachten'],
        $cFilter,
        $cSort
    );

    if (isset($args_arr['oExtendedJTLSearchResponse']->oSearch->nStatus) && $args_arr['oExtendedJTLSearchResponse']->oSearch->nStatus == 1) {
        $cQuery = $args_arr['oExtendedJTLSearchResponse']->oSearch->cQuery;
        // QueryTracking
        if (!isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[strtolower($cQuery)])) {
            $oldCount = 0;
            if (isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr)) {
                $oldCount = count($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr);
            }
            $oQT                 = new stdClass();
            $oQT->cQuery         = $cQuery;
            $oQT->kQuery         = $args_arr['oExtendedJTLSearchResponse']->oSearch->kQuery;
            $oQT->nProduct_arr   = QueryTracking::filterProductKeys($args_arr['oExtendedJTLSearchResponse']->oSearch->oItem_arr);
            $oQT->nQueryTracking = $oldCount++;

            $_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[strtolower($cQuery)] = $oQT;
        } else {
            $oQT = $_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[strtolower($cQuery)];
            QueryTracking::addProducts(QueryTracking::filterProductKeys($args_arr['oExtendedJTLSearchResponse']->oSearch->oItem_arr), $oQT->nProduct_arr);
        }

        $cShopURL = Shop::getURL() . '/navi.php?suchausdruck=' . urlencode($args_arr['cValue']);
        JtlSearch::extendFilterItemURL($args_arr['oExtendedJTLSearchResponse']->oSearch->oFilterGroup_arr, count($cFilter_arr), $cShopURL);
        $args_arr['bExtendedJTLSearch'] = true;

        Shop::Smarty()->assign('cExtendedJTLSearchURL', JtlSearch::extendFilterStandaloneURL($args_arr['oExtendedJTLSearchResponse']->oSearch->oFilterGroup_arr, $cShopURL))
            ->assign('oExtendedJTLSearchResponse', $args_arr['oExtendedJTLSearchResponse'])
            ->assign('nStatedFilterCount', count($cFilter_arr))
            ->assign('nOverallTime', (microtime(true) - $nOverallTime))
            ->assign('cJTLSearchStatedFilter', JtlSearch::buildFilterShopURL($cFilter_arr))
            ->assign('cJTLSearchStatedFilter_arr', $cFilter_arr);
    }
}
