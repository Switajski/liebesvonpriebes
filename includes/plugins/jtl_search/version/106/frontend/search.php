<?php
/**
 *-------------------------------------------------------------------------------
 *	JTL-Shop 3
 *	File: search.php, php file
 *
 *	JTL-Shop 3
 *
 * Do not use, modify or sell this code without permission / licence.
 *
 * @author JTL-Software <daniel.boehmer@jtl-software.de>
 * @copyright 2010, JTL-Software
 * @link http://jtl-software.de/jtlshop.php
 * @version 1.0
 *-------------------------------------------------------------------------------
*/

global $smarty;

// Bot?
$bBot = false;
if (isset($_SESSION['oBesucher']->kBesucherBot) && $_SESSION['oBesucher']->kBesucherBot > 0) {
    $bBot = true;
}

if (!$oPlugin->getConf("cProjectId")) {
    $oObj_arr = $GLOBALS['DB']->executeQuery("SELECT * FROM tjtlsearchserverdata", 2);
    foreach ($oObj_arr as $oObj) {
        if (isset($oObj->cKey) && strlen($oObj->cKey) > 0) {
            switch ($oObj->cKey) {
                case "cProjectId":
                    $oPlugin->setConf("cProjectId", $oObj->cValue);
                    break;
                    
                case "cAuthHash":
                    $oPlugin->setConf("cAuthHash", $oObj->cValue);
                    break;
                        
                case "cServerUrl":
                    $oPlugin->setConf("cServerUrl", $oObj->cValue);
                    break;
            }
        }
    }
}

if (!$bBot && isset($args_arr['bExtendedJTLSearch']) && $args_arr['bExtendedJTLSearch'] && isset($args_arr['cValue']) && $args_arr['nArtikelProSeite'] && $args_arr['nSeite'] && strlen($oPlugin->getConf("cProjectId")) > 0 && strlen($oPlugin->getConf("cAuthHash")) > 0 && strlen($oPlugin->getConf("cServerUrl")) > 0) {
    require_once("{$oPlugin->cFrontendPfad}../includes/defines_inc.php");
    require_once("{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php");
    require_once("{$oPlugin->cFrontendPfad}../classes/class.QueryTracking.php");
    
    // Filter and url extending
    $cFilter_arr = JtlSearch::getFilter($_GET);
    $cFilter = JtlSearch::buildFilterURL($cFilter_arr);
    
    // Sorting
    $cSort = JtlSearch::getSorting($args_arr['nSortierung'], $_SESSION['Waehrung']->cISO, $_SESSION['Kundengruppe']->kKundengruppe);
    
    // Overall time start
    $nOverallTime = microtime_float();
    
    // Main search call
    $args_arr['oExtendedJTLSearchResponse'] = JtlSearch::doSearch(md5(session_id()), $_SESSION['Kundengruppe']->kKundengruppe, $_SESSION['cISOSprache'], $_SESSION['Waehrung']->cISO, $args_arr['cValue'], $oPlugin->getConf("cProjectId"), $oPlugin->getConf("cAuthHash"), urldecode($oPlugin->getConf("cServerUrl")), $args_arr['nArtikelProSeite'], $args_arr['nSeite'], $args_arr['bLagerbeachten'], $cFilter, $cSort);

    if (isset($args_arr['oExtendedJTLSearchResponse']->oSearch->nStatus) && $args_arr['oExtendedJTLSearchResponse']->oSearch->nStatus == 1) {
        $cQuery = $args_arr['oExtendedJTLSearchResponse']->oSearch->cQuery;
        
        // QueryTracking
        if (!isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[strtolower($cQuery)])) {
            $oQT = new stdClass();
            $oQT->cQuery = $cQuery;
            $oQT->kQuery = $args_arr['oExtendedJTLSearchResponse']->oSearch->kQuery;
            $oQT->nProduct_arr = QueryTracking::filterProductKeys($args_arr['oExtendedJTLSearchResponse']->oSearch->oItem_arr);
            $oQT->nQueryTracking = count($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr) + 1;
            
            $_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[strtolower($cQuery)] = $oQT;
        } else {
            $oQT = $_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[strtolower($cQuery)];
            QueryTracking::addProducts(QueryTracking::filterProductKeys($args_arr['oExtendedJTLSearchResponse']->oSearch->oItem_arr), $oQT->nProduct_arr);
        }
                
        $cShopURL = URL_SHOP . "/navi.php?suchausdruck=" . urlencode($args_arr['cValue']);
        JtlSearch::extendFilterItemURL($args_arr['oExtendedJTLSearchResponse']->oSearch->oFilterGroup_arr, count($cFilter_arr), $cShopURL);
        $args_arr['bExtendedJTLSearch'] = true;
        
        $smarty->assign("cExtendedJTLSearchURL", JtlSearch::extendFilterStandaloneURL($args_arr['oExtendedJTLSearchResponse']->oSearch->oFilterGroup_arr, $cShopURL));
        $smarty->assign("oExtendedJTLSearchResponse", $args_arr['oExtendedJTLSearchResponse']);
        $smarty->assign("nStatedFilterCount", count($cFilter_arr));
        $smarty->assign("nOverallTime", microtime_float() - $nOverallTime);
        $smarty->assign("cJTLSearchStatedFilter", JtlSearch::buildFilterShopURL($cFilter_arr));
        $smarty->assign("cJTLSearchStatedFilter_arr", $cFilter_arr);
    }
}
