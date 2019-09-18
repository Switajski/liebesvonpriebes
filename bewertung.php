<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
$AktuelleSeite = 'BEWERTUNG';

Shop::run();
Shop::setPageType(PAGE_BEWERTUNG);
$cParameter_arr = Shop::getParameters();
$Einstellungen  = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_BEWERTUNG]);
// Bewertung in die Datenbank speichern
if (isset($_POST['bfh']) && (int)$_POST['bfh'] === 1) {
    if (pruefeKundeArtikelBewertet($cParameter_arr['kArtikel'], $_SESSION['Kunde']->kKunde)) {
        $artikel = new Artikel();
        $artikel->fuelleArtikel($cParameter_arr['kArtikel'], Artikel::getDefaultOptions());
        $url = (!empty($artikel->cURLFull))
            ? ($artikel->cURLFull . '?')
            : (Shop::getURL() . '/?a=' . $cParameter_arr['kArtikel']);

        header('Location: ' . $url . 'bewertung_anzeigen=1&cFehler=f02', true, 301);
        exit();
    } else {
        // Versuche die Bewertung zu speichern
        speicherBewertung(
            $cParameter_arr['kArtikel'],
            $_SESSION['Kunde']->kKunde,
            Shop::getLanguage(),
            verifyGPDataString('cTitel'),
            verifyGPDataString('cText'),
            $cParameter_arr['nSterne']
        );
    }
} elseif (isset($_POST['bhjn']) && (int)$_POST['bhjn'] === 1) { // Hilfreich abspeichern
    // Bewertungen holen
    $bewertung_seite  = verifyGPCDataInteger('btgseite');
    $bewertung_sterne = verifyGPCDataInteger('btgsterne');
    speicherHilfreich(
        $cParameter_arr['kArtikel'],
        $_SESSION['Kunde']->kKunde,
        Shop::getLanguage(),
        $bewertung_seite,
        $bewertung_sterne
    );
} elseif (verifyGPCDataInteger('bfa') === 1) {
    // Prüfe ob Kunde eingeloggt
    if (empty($_SESSION['Kunde']->kKunde)) {
        $helper = LinkHelper::getInstance();
        header('Location: ' . $helper->getStaticRoute('jtl.php', true) .
                '?a=' . verifyGPCDataInteger('a') .
                '&bfa=1&r=' . R_LOGIN_BEWERTUNG,
            true,
            303
        );
        exit();
    }
    //hole aktuellen Artikel
    $AktuellerArtikel = new Artikel();
    $AktuellerArtikel->fuelleArtikel($cParameter_arr['kArtikel'], Artikel::getDefaultOptions());
    //falls kein Artikel vorhanden, zurück zum Shop
    if (!$AktuellerArtikel->kArtikel) {
        header('Location: ' . Shop::getURL() . '/', true, 303);
        exit;
    }
    //hole aktuelle Kategorie, falls eine gesetzt
    $AufgeklappteKategorien = new KategorieListe();
    $startKat               = new Kategorie();
    $startKat->kKategorie   = 0;
    if ($AktuellerArtikel->Bewertungen === null) {
        $AktuellerArtikel->holeBewertung(
            Shop::getLanguage(),
            $Einstellungen['bewertung']['bewertung_anzahlseite'],
            0,
            -1,
            $Einstellungen['bewertung']['bewertung_freischalten'],
            $cParameter_arr['nSortierung']
        );
        $AktuellerArtikel->holehilfreichsteBewertung(Shop::getLanguage());
    }

    if ($Einstellungen['bewertung']['bewertung_artikel_gekauft'] === 'Y') {
        $smarty->assign('nArtikelNichtGekauft', pruefeKundeArtikelGekauft(
            $AktuellerArtikel->kArtikel,
            $_SESSION['Kunde']->kKunde)
        );
    }
    //specific assigns
    $smarty->assign('BereitsBewertet', pruefeKundeArtikelBewertet(
        $AktuellerArtikel->kArtikel,
        $_SESSION['Kunde']->kKunde))
           ->assign('Navigation', createNavigation(
               $AktuelleSeite,
               0,
               0,
               Shop::Lang()->get('bewertung', 'breadcrumb'),
               'bewertung.php?a=' . $AktuellerArtikel->kArtikel . '&bfa=1'))
           ->assign('Einstellungen', $Einstellungen)
           ->assign('Artikel', $AktuellerArtikel)
           ->assign('requestURL', isset($requestURL) ? $requestURL : null)
           ->assign('sprachURL', isset($sprachURL) ? $sprachURL : null);

    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    $smarty->display('productdetails/review_form.tpl');
} else {
    header('Location: ' . Shop::getURL() . '/', true, 303);
    exit;
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
