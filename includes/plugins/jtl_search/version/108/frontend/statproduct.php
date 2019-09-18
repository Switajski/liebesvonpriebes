<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if (isset($args_arr['AktuellerArtikel'])) {
    $AktuellerArtikel = $args_arr['AktuellerArtikel'];
} else {
    $AktuellerArtikel = $GLOBALS['AktuellerArtikel'];
}

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

if (isset($_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch) && $_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch &&
    strlen($oPlugin->getConf('cProjectId')) > 0 && strlen($oPlugin->getConf('cAuthHash')) > 0 && strlen($oPlugin->getConf('cServerUrl')) > 0
) {
    Jtllog::writeLog(utf8_decode("Producthit wurde aufgerufen für Artikel {$AktuellerArtikel->cName} ({$AktuellerArtikel->kArtikel})"), JTLLOG_LEVEL_DEBUG, false, 'kPlugin', $oPlugin->kPlugin);

    require_once "{$oPlugin->cFrontendPfad}../includes/defines_inc.php";
    require_once "{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php";
    require_once "{$oPlugin->cFrontendPfad}../classes/class.QueryTracking.php";

    $nQueryTracking = (isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr)) ? count($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr) : 0;
    if (isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr) && is_array($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr) && $nQueryTracking > 0) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Producthit mit oQueryTracking_arr: ' . print_r($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr, 1), JTLLOG_LEVEL_DEBUG, false, 'kPlugin', $oPlugin->kPlugin);
        }

        $oQueryTracking_arr = QueryTracking::orderQueryTrackings($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr);
        if ($oQueryTracking_arr !== null) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Producthit mit oQueryTracking_arr sorted: ' . print_r($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr, 1), JTLLOG_LEVEL_DEBUG, false, 'kPlugin', $oPlugin->kPlugin);
            }

            foreach ($oQueryTracking_arr as $oQueryTracking) {
                if (in_array($AktuellerArtikel->kArtikel, $oQueryTracking->nProduct_arr)) {
                    $kArtikelTmp = $AktuellerArtikel->kArtikel;
                    $nHitType    = JTLSEARCH_STAT_TYPE_VIEWED;
                    if (isset($_POST['fragezumprodukt']) && intval($_POST['fragezumprodukt']) === 1) {
                        $nHitType = JTLSEARCH_STAT_TYPE_NOTIFY;
                    } else {
                        if (isset($_POST['benachrichtigung_verfuegbarkeit']) && intval($_POST['benachrichtigung_verfuegbarkeit']) === 1) {
                            $nHitType = JTLSEARCH_STAT_TYPE_DEMAND;
                        } else {
                            if (isset($_POST['artikelweiterempfehlen']) && intval($_POST['artikelweiterempfehlen']) === 1) {
                                $nHitType = JTLSEARCH_STAT_TYPE_RECOMMEND;
                            } else {
                                if (isset($_POST['Wunschliste']) || isset($_GET['Wunschliste'])) {
                                    $nHitType = JTLSEARCH_STAT_TYPE_WISHLIST;
                                } else {
                                    if (isset($_POST['Vergleichsliste'])) {
                                        $nHitType = JTLSEARCH_STAT_TYPE_COMPARE;
                                    } else {
                                        if (isset($_POST['wke']) && intval($_POST['wke']) === 1 && !isset($_POST['Vergleichsliste']) && !isset($_POST['Wunschliste'])) {
                                            if (ArtikelHelper::isParent($AktuellerArtikel->kArtikel)) { // Varikombi
                                                $kArtikelTmp = ArtikelHelper::getArticleForParent($AktuellerArtikel->kArtikel);
                                            }
                                            $nHitType = JTLSEARCH_STAT_TYPE_BASKET;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $cReturn = JtlSearch::doProductStats(
                        $oQueryTracking->kQuery,
                        $kArtikelTmp,
                        $nHitType,
                        $oPlugin->getConf('cProjectId'),
                        $oPlugin->getConf('cAuthHash'),
                        $oPlugin->getConf('cServerUrl')
                    );
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        if (is_object($cReturn)) {
                            Jtllog::writeLog('Producthit doProductStats mit Return: ' . print_r($cReturn, 1),
                                JTLLOG_LEVEL_DEBUG, false, 'kPlugin', $oPlugin->kPlugin);
                        } else {
                            Jtllog::writeLog('Producthit doProductStats mit Return: {$cReturn}', JTLLOG_LEVEL_DEBUG,
                                false, 'kPlugin', $oPlugin->kPlugin);
                        }
                    }

                    break;
                }
            }
        } else {
            Jtllog::writeLog('Producthit orderQueryTrackings konnte oQueryTracking_arr nicht sortieren', JTLLOG_LEVEL_DEBUG, false, 'kPlugin', $oPlugin->kPlugin);
        }
    }
}
