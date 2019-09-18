<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'umfrage_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::run();
Shop::setPageType(PAGE_UMFRAGE);
$cParameter_arr       = Shop::getParameters();
$cHinweis             = '';
$cFehler              = '';
$cCanonicalURL        = '';
$step                 = 'umfrage_uebersicht';
$nAktuelleSeite       = 1;
$oUmfrageFrageTMP_arr = [];
$Einstellungen        = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_UMFRAGE]);
$linkHelper           = LinkHelper::getInstance();
$kLink                = $linkHelper->getSpecialPageLinkKey(LINKTYP_UMFRAGE);

//hole alle OberKategorien
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$startKat->kKategorie   = 0;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

// Umfrage durchführen
if (isset($cParameter_arr['kUmfrage']) && $cParameter_arr['kUmfrage'] > 0) {
    $step = 'umfrage_uebersicht';
    // Umfrage durchführen
    if (($Einstellungen['umfrage']['umfrage_einloggen'] === 'Y' && isset($_SESSION['Kunde']->kKunde) &&
            $_SESSION['Kunde']->kKunde > 0) || $Einstellungen['umfrage']['umfrage_einloggen'] === 'N') {
        // Umfrage holen
        $oUmfrage = holeAktuelleUmfrage($cParameter_arr['kUmfrage']);
        if ($oUmfrage->kUmfrage > 0) {
            if (pruefeUserUmfrage($oUmfrage->kUmfrage, $_SESSION['Kunde']->kKunde, $_SESSION['oBesucher']->cID)) {
                $step = 'umfrage_durchfuehren';
                // Auswertung
                if (isset($_POST['end'])) {
                    speicherFragenInSession($_POST);

                    if (pruefeEingabe($_POST) > 0) {
                        $cFehler .= Shop::Lang()->get('pollRequired', 'errorMessages') . '<br>';
                    } elseif ($_SESSION['Umfrage']->nEnde == 0) {
                        $step = 'umfrage_ergebnis';
                        executeHook(HOOK_UMFRAGE_PAGE_UMFRAGEERGEBNIS);
                        // Auswertung
                        bearbeiteUmfrageAuswertung($oUmfrage);
                    } else {
                        $step = 'umfrage_uebersicht';
                    }
                }

                if ($step === 'umfrage_durchfuehren') {
                    $oNavi_arr = [];
                    // Durchfuehrung
                    bearbeiteUmfrageDurchfuehrung(
                        $cParameter_arr['kUmfrage'],
                        $oUmfrage,
                        $oUmfrageFrageTMP_arr,
                        $oNavi_arr,
                        $cParameter_arr['kSeite']
                    );
                }
                $_SESSION['Umfrage']->kUmfrage = $oUmfrage->kUmfrage;
                $smarty->assign('oUmfrage', $oUmfrage)
                       ->assign('Navigation', createNavigation(
                           Shop::getPageType(),
                           0,
                           0,
                           Shop::Lang()->get('umfrage', 'breadcrumb') .
                           ' - ' . $oUmfrage->cName, baueURL($oUmfrage, URLART_UMFRAGE)
                           )
                       )
                       ->assign('oNavi_arr', baueSeitenNavi($oUmfrageFrageTMP_arr, $oUmfrage->nAnzahlFragen))
                       ->assign('nAktuelleSeite', $cParameter_arr['kSeite'])
                       ->assign('nAnzahlSeiten', bestimmeAnzahlSeiten($oUmfrageFrageTMP_arr));

                executeHook(HOOK_UMFRAGE_PAGE_DURCHFUEHRUNG);
            } else {
                $cFehler .= Shop::Lang()->get('pollAlreadydid', 'errorMessages') . '<br />';
            }
        }
    } else {
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) .
            '?u=' . $cParameter_arr['kUmfrage'] . '&r=' . R_LOGIN_UMFRAGE);
        exit();
    }
}

if ($step === 'umfrage_uebersicht') {
    // Umfrage Übersicht
    $oUmfrage_arr = holeUmfrageUebersicht();
    if (is_array($oUmfrage_arr) && count($oUmfrage_arr) > 0) {
        foreach ($oUmfrage_arr as $i => $oUmfrage) {
            $oUmfrage_arr[$i]->cURL = baueURL($oUmfrage, URLART_UMFRAGE);
        }
    } else {
        $cFehler .= Shop::Lang()->get('pollNopoll', 'errorMessages') . '<br />';
    }
    // Canonical
    $cCanonicalURL = Shop::getURL() . '/umfrage.php';

    $smarty->assign('Navigation', createNavigation(
            Shop::$AktuelleSeite,
            0,
            0,
            Shop::Lang()->get('umfragen', 'breadcrumb'),
            'umfrage.php?'
        )
    )->assign('oUmfrage_arr', $oUmfrage_arr);

    executeHook(HOOK_UMFRAGE_PAGE_UEBERSICHT);
}

$smarty->assign('Einstellungen', $Einstellungen)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_UMFRAGE_PAGE);

$smarty->display('poll/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
