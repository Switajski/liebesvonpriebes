<?php
/**
 *-------------------------------------------------------------------------------
 *	JTL-Shop 3
 *	File: statproduct.php, php file
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

if (isset($args_arr["AktuellerArtikel"])) {
    $AktuellerArtikel = $args_arr["AktuellerArtikel"];
} else {
    $AktuellerArtikel = $GLOBALS["AktuellerArtikel"];
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

if (isset($_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch) && $_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch && strlen($oPlugin->getConf("cProjectId")) > 0 && strlen($oPlugin->getConf("cAuthHash")) > 0 && strlen($oPlugin->getConf("cServerUrl")) > 0) {
    // Debug
    Jtllog::writeLog("Producthit wurde aufgerufen für Artikel {$AktuellerArtikel->cName} ({$AktuellerArtikel->kArtikel})", JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
    
    require_once("{$oPlugin->cFrontendPfad}../includes/defines_inc.php");
    require_once("{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php");
    require_once("{$oPlugin->cFrontendPfad}../classes/class.QueryTracking.php");
    
    $nQueryTracking = count($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr);
    if (isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr) && is_array($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr) && $nQueryTracking > 0) {
        // Debug
        Jtllog::writeLog("Producthit mit oQueryTracking_arr: " . print_r($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr, 1), JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
        
        $oQueryTracking_arr = QueryTracking::orderQueryTrackings($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr);
        if ($oQueryTracking_arr != null) {
            // Debug
            Jtllog::writeLog("Producthit mit oQueryTracking_arr sorted: " . print_r($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr, 1), JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
            
            foreach ($oQueryTracking_arr as $oQueryTracking) {
                if (in_array($AktuellerArtikel->kArtikel, $oQueryTracking->nProduct_arr)) {
                    $nHitType = JTLSEARCH_STAT_TYPE_VIEWED;
                    if (intval($_POST['fragezumprodukt']) == 1) {
                        $nHitType = JTLSEARCH_STAT_TYPE_NOTIFY;
                    } elseif (intval($_POST['benachrichtigung_verfuegbarkeit']) == 1) {
                        $nHitType = JTLSEARCH_STAT_TYPE_DEMAND;
                    } elseif (intval($_POST['artikelweiterempfehlen']) == 1) {
                        $nHitType = JTLSEARCH_STAT_TYPE_RECOMMEND;
                    } elseif (isset($_POST['Wunschliste']) || isset($_GET['Wunschliste'])) {
                        $nHitType = JTLSEARCH_STAT_TYPE_WISHLIST;
                    } elseif (isset($_POST['Vergleichsliste'])) {
                        $nHitType = JTLSEARCH_STAT_TYPE_COMPARE;
                    } elseif (isset($_POST["wke"]) && intval($_POST["wke"]) == 1 && !isset($_POST['Vergleichsliste']) && !isset($_POST['Wunschliste'])) {
                        if (pruefeIstVaterArtikel($AktuellerArtikel->kArtikel)) {// Varikombi
                            $AktuellerArtikel->kArtikel = gibkArtikelZuVaterArtikel($AktuellerArtikel->kArtikel);
                        }
                        
                        $nHitType = JTLSEARCH_STAT_TYPE_BASKET;
                    }
                        
                    $cReturn = JtlSearch::doProductStats($oQueryTracking->kQuery, $AktuellerArtikel->kArtikel, $nHitType, $oPlugin->getConf("cProjectId"), $oPlugin->getConf("cAuthHash"), $oPlugin->getConf("cServerUrl"));
                    
                    // Debug
                    if (is_object($cReturn)) {
                        Jtllog::writeLog("Producthit doProductStats mit Return: " . print_r($cReturn, 1), JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
                    } else {
                        Jtllog::writeLog("Producthit doProductStats mit Return: {$cReturn}", JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
                    }
                    
                    break;
                }
            }
        } else {
            // Debug
            Jtllog::writeLog("Producthit orderQueryTrackings konnte oQueryTracking_arr nicht sortieren", JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
        }
    }
}
