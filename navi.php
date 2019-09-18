<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
/** @global JTLSmarty $smarty */
$cart = isset($_SESSION['Warenkorb'])
    ? $_SESSION['Warenkorb']
    : new Warenkorb();

if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
    //falls Artikel/Kategorien nicht gesehen werden dürfen -> login
    $linkHelper = LinkHelper::getInstance();
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) . '?li=1', true, 303);
    exit;
}
// Wurde ein Kindartikel zum Vater umgeleitet? Falls ja => Redirect POST Daten entpacken und zuweisen
if (isset($_GET['cRP']) && strlen($_GET['cRP']) > 0) {
    $cRP_arr = explode('&', base64_decode($_GET['cRP']));
    if (is_array($cRP_arr) && count($cRP_arr) > 0) {
        foreach ($cRP_arr as $cRP) {
            list($cName, $cWert) = explode('=', $cRP);
            $_POST[$cName]       = $cWert;
        }
    }
}
Shop::run();
$cParameter_arr  = Shop::getParameters();
$NaviFilter      = Shop::buildNaviFilter($cParameter_arr);
$oSuchergebnisse = new stdClass();
$cFehler         = '';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);

//support for artikel_after_cart_add
if (isset($_POST['a'], $_POST['wke'])) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    if (function_exists('gibArtikelXSelling')) {
        $smarty->assign('Xselling', gibArtikelXSelling($_POST['a']));
    }
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
// Usersortierung
setzeUsersortierung($NaviFilter);
$doSearch      = true;
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_VERGLEICHSLISTE,
    CONF_ARTIKELUEBERSICHT,
    CONF_ARTIKELDETAILS,
    CONF_BEWERTUNG,
    CONF_NAVIGATIONSFILTER,
    CONF_BOXEN,
    CONF_METAANGABEN,
    CONF_SUCHSPECIAL,
    CONF_BILDER,
    CONF_SONSTIGES,
    CONF_AUSWAHLASSISTENT
]);
// Suche prüfen
if (strlen($cParameter_arr['cSuche']) > 0 || (isset($_GET['qs']) && strlen($_GET['qs']) === 0)) {
    $nMindestzeichen = ((int)$Einstellungen['artikeluebersicht']['suche_min_zeichen'] > 0) ?
        (int)$Einstellungen['artikeluebersicht']['suche_min_zeichen'] :
        3;
    preg_match(
        "/[\w" . utf8_decode('äÄüÜöÖß') . "\.\-]{" . $nMindestzeichen . ",}/",
        str_replace(' ', '', $cParameter_arr['cSuche']),
        $cTreffer_arr);
    if (count($cTreffer_arr) === 0) {
        $cFehler                 = Shop::Lang()->get('expressionHasTo', 'global') . ' ' .
            $nMindestzeichen . ' ' .
            Shop::Lang()->get('lettersDigits', 'global');
        $oSuchergebnisse->Fehler = $cFehler;
        $doSearch                = false;
    }
}
//wurde was in den Warenkorb gelegt?
checkeWarenkorbEingang();
// Prüfe Variationskombi
if (ArtikelHelper::isVariChild($cParameter_arr['kArtikel'])) {
    Shop::$kVariKindArtikel             = $cParameter_arr['kArtikel'];
    Shop::$kArtikel                     = ArtikelHelper::getParent($cParameter_arr['kArtikel']);
    $cParameter_arr['kVariKindArtikel'] = Shop::$kVariKindArtikel;
    $cParameter_arr['kArtikel']         = Shop::$kArtikel;
}
if (!$cParameter_arr['kWunschliste'] && strlen(verifyGPDataString('wlid')) > 0) {
    header('Location: ' . $linkHelper->getStaticRoute('wunschliste.php') .
        '?wlid=' . StringHandler::filterXSS(verifyGPDataString('wlid')) . '&error=1', true, 303);
    exit();
}
$smarty->assign('NaviFilter', $NaviFilter);
Shop::setPageType(PAGE_ARTIKELLISTE);

if ($cParameter_arr['kHersteller'] > 0 ||
    $cParameter_arr['kSuchanfrage'] > 0 ||
    $cParameter_arr['kMerkmalWert'] > 0 ||
    $cParameter_arr['kTag'] > 0 ||
    $cParameter_arr['kKategorie'] > 0 ||
    strlen($cParameter_arr['cPreisspannenFilter']) > 0 ||
    $cParameter_arr['nBewertungSterneFilter'] > 0 ||
    $cParameter_arr['kHerstellerFilter'] > 0 ||
    $cParameter_arr['kKategorieFilter'] > 0 ||
    strlen($cParameter_arr['cSuche']) > 0 ||
    $cParameter_arr['kSuchspecial'] > 0 ||
    $cParameter_arr['kSuchspecialFilter'] > 0 ||
    $cParameter_arr['kSuchFilter'] > 0 ||
    (isset($_GET['qs']) && strlen($_GET['qs']) === 0)
) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
    $suchanfrage = '';
    // setze Kat in Session
    if ($cParameter_arr['kKategorie'] > 0) {
        $_SESSION['LetzteKategorie'] = $cParameter_arr['kKategorie'];
    }
    $AktuelleKategorie = new Kategorie();
    //hole aktuelle Kategorie + bild, falls eine gesetzt
    if ($cParameter_arr['kKategorie'] > 0) {
        $AktuelleKategorie->loadFromDB($cParameter_arr['kKategorie']);
        if (!isset($AktuelleKategorie->kKategorie) || $AktuelleKategorie->kKategorie === null) {
            Shop::$is404             = true;
            $is404                   = true;
            $cParameter_arr['is404'] = true;
            if (!isset($seo)) {
                $seo = null;
            }
            executeHook(HOOK_INDEX_SEO_404, ['seo' => $seo]);
            if (!Shop::$kLink) {
                $hookInfos     = urlNotFoundRedirect([
                    'key'   => 'kLink',
                    'value' => $cParameter_arr['kLink']
                ]);
                $kLink         = (int)$hookInfos['value'];
                $bFileNotFound = $hookInfos['isFileNotFound'];
                if (!$kLink) {
                    $linkHelper  = LinkHelper::getInstance();
                    $kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_404);
                    Shop::$kLink = $kLink;
                }
            }
            require_once PFAD_ROOT . 'seite.php';
            exit;
        }
    }
    //Artikelanzahl pro Seite
    if ($cParameter_arr['nArtikelProSeite'] == 0) {
        $cParameter_arr['nArtikelProSeite'] = 20;
        if ($Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0) {
            $cParameter_arr['nArtikelProSeite'] =
                (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'];
        }
        if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            $cParameter_arr['nArtikelProSeite'] = (int)$_SESSION['ArtikelProSeite'];
        }
    }
    $oSuchergebnisse->Artikel           = new ArtikelListe();
    $oArtikel_arr                       = [];
    $oSuchergebnisse->MerkmalFilter     = [];
    $oSuchergebnisse->Herstellerauswahl = [];
    $oSuchergebnisse->Tags              = [];
    $oSuchergebnisse->Bewertung         = [];
    $oSuchergebnisse->Preisspanne       = [];
    $oSuchergebnisse->Suchspecial       = [];
    $oSuchergebnisse->SuchFilter        = [];
    // JTL Search
    $oExtendedJTLSearchResponse = null;
    $bExtendedJTLSearch         = false;

    executeHook(HOOK_NAVI_PRESUCHE, [
        'cValue'             => &$NaviFilter->EchteSuche->cSuche,
        'bExtendedJTLSearch' => &$bExtendedJTLSearch
    ]);
    // Keine Suche sondern vielleicht nur ein Filter?
    if (strlen($cParameter_arr['cSuche']) === 0) {
        $bExtendedJTLSearch = false;
    }
    // SuchFilter
    if (is_array($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0) {
        $sfCount = count($NaviFilter->SuchFilter);
        for ($i = 0; $i < $sfCount; $i++) {
            $oSuchanfrage = Shop::DB()->select(
                'tsuchanfrage',
                'kSuchanfrage',
                (int)$NaviFilter->SuchFilter[$i]->kSuchanfrage
            );
            if (strlen($oSuchanfrage->cSuche) > 0) {
                // Nicht vorhandene Suchcaches werden hierdurch neu generiert
                if (!isset($NaviFilter->Suche)) {
                    $NaviFilter->Suche = new stdClass();
                }
                $NaviFilter->Suche->cSuche              = $oSuchanfrage->cSuche;
                $NaviFilter->SuchFilter[$i]->kSuchCache = bearbeiteSuchCache($NaviFilter);
                //$oSuchanfrage->cSuche = $NaviFilter->Suche->cSuche;
                unset($NaviFilter->Suche->cSuche);

                $NaviFilter->SuchFilter[$i]->cSuche = $oSuchanfrage->cSuche;
            }
        }
    }
    if ($cParameter_arr['kSuchanfrage'] > 0) {
        $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', (int)$cParameter_arr['kSuchanfrage']);

        if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
            if (!isset($NaviFilter->Suche)) {
                $NaviFilter->Suche = new stdClass();
            }
            $NaviFilter->Suche->kSuchanfrage = $cParameter_arr['kSuchanfrage'];
            $NaviFilter->Suche->cSuche       = $oSuchanfrage->cSuche;
        }
    }
    if (!isset($NaviFilter->Suche)) {
        $NaviFilter->Suche = new stdClass();
    }
    $NaviFilter->Suche->bExtendedJTLSearch = $bExtendedJTLSearch;
    //Suche da? Dann bearbeiten
    if (!$bExtendedJTLSearch && isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
        //XSS abfangen
        $NaviFilter->Suche->cSuche     = StringHandler::filterXSS($NaviFilter->Suche->cSuche, 1);
        $NaviFilter->Suche->kSuchCache = bearbeiteSuchCache($NaviFilter);
    }
    // Usersortierung
    setzeUsersortierung($NaviFilter);
    // Filter SQL
    $FilterSQL = bauFilterSQL($NaviFilter);
    // Hook
    executeHook(HOOK_NAVI_CREATE, [
        'naviFilter' => &$NaviFilter,
        'filterSQL'  => &$FilterSQL
    ]);
    // Erweiterte Darstellung Artikelübersicht
    gibErweiterteDarstellung($Einstellungen, $NaviFilter, $cParameter_arr['nDarstellung']);

    if ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
        $cParameter_arr['nArtikelProSeite'] = $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
    }
    if (verifyGPCDataInteger('af') > 0) {
        $_SESSION['ArtikelProSeite'] = verifyGPCDataInteger('af');
        setFsession(0, 0, $_SESSION['ArtikelProSeite']);
        $cParameter_arr['nArtikelProSeite'] = $_SESSION['ArtikelProSeite'];
        if (isset($_SESSION['oErweiterteDarstellung'])) {
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
        }
    }
    if (!isset($_SESSION['ArtikelProSeite']) &&
        $Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N'
    ) {
        $_SESSION['ArtikelProSeite'] = min(
            (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_artikelproseite'],
            ARTICLES_PER_PAGE_HARD_LIMIT
        );
    }
    // $nArtikelProSeite auf max. ARTICLES_PER_PAGE_HARD_LIMIT beschränken
    $cParameter_arr['nArtikelProSeite'] = min(
        (int)$cParameter_arr['nArtikelProSeite'],
        ARTICLES_PER_PAGE_HARD_LIMIT
    );

    executeHook(HOOK_NAVI_SUCHE, [
        'bExtendedJTLSearch'         => $bExtendedJTLSearch,
        'oExtendedJTLSearchResponse' => &$oExtendedJTLSearchResponse,
        'cValue'                     => &$NaviFilter->EchteSuche->cSuche,
        'nArtikelProSeite'           => &$cParameter_arr['nArtikelProSeite'],
        'nSeite'                     => &$NaviFilter->nSeite,
        'nSortierung'                => isset($_SESSION['Usersortierung'])
            ? $_SESSION['Usersortierung']
            : null,
        'bLagerbeachten'             => $Einstellungen['global']['artikel_artikelanzeigefilter'] ==
            EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
    ]);
    //Ab diesen Artikel rausholen
    $nLimitN = ($NaviFilter->nSeite - 1) * $cParameter_arr['nArtikelProSeite'];
    if ($doSearch === false) {
        $oSuchergebnisse->Artikel->elemente           = [];
        $oSuchergebnisse->GesamtanzahlArtikel         = 0;
        $oSuchergebnisse->SucheErfolglos              = 1;
        $oSuchergebnisse->Seitenzahlen                = new stdClass();
        $oSuchergebnisse->Seitenzahlen->AktuelleSeite = 0;
        $oSuchergebnisse->Seitenzahlen->MaxSeiten     = 0;
        $oSuchergebnisse->Seitenzahlen->minSeite      = 0;
        $oSuchergebnisse->Seitenzahlen->maxSeite      = 0;
    } elseif (!$bExtendedJTLSearch) {
        baueArtikelAnzahl($FilterSQL, $oSuchergebnisse, $cParameter_arr['nArtikelProSeite'], $nLimitN);
    }

    $bEchteSuche = false;
    if (!$bExtendedJTLSearch && strlen($cParameter_arr['cSuche']) > 0) {
        $bEchteSuche = true;
    }
    if (!$bExtendedJTLSearch) {
        if (isset($NaviFilter->Suche->cSuche)) {
            suchanfragenSpeichern($NaviFilter->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel, $bEchteSuche);
            $NaviFilter->Suche->kSuchanfrage = gibSuchanfrageKey($NaviFilter->Suche->cSuche, Shop::getLanguage());
        } else {
            if (!isset($NaviFilter->Suche)) {
                $NaviFilter->Suche = new stdClass();
            }
            $NaviFilter->Suche->kSuchanfrage = 0;
        }
    }
    if ($doSearch === true) {
        // JTL Search
        if ($bExtendedJTLSearch) {
            $oSuchergebnisse->Artikel->elemente = gibArtikelKeysExtendedJTLSearch($oExtendedJTLSearchResponse);
            buildSearchResultPage(
                $oSuchergebnisse,
                $oExtendedJTLSearchResponse->oSearch->nItemFound,
                $nLimitN,
                $NaviFilter->nSeite,
                $cParameter_arr['nArtikelProSeite'],
                $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl']
            );
        } else {
            $oSuchergebnisse->Artikel->elemente = gibArtikelKeys(
                $FilterSQL,
                $cParameter_arr['nArtikelProSeite'],
                $NaviFilter,
                false,
                $oSuchergebnisse
            );
        }
    }
    // Umleiten falls SEO keine Artikel ergibt
    doMainwordRedirect($NaviFilter, count($oSuchergebnisse->Artikel->elemente), true);
    // Bestsellers
    if (isset($Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren']) &&
        $Einstellungen['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y'
    ) {
        $products = [];
        foreach ($oSuchergebnisse->Artikel->elemente as $product) {
            $products[] = $product->kArtikel;
        }
        $limit = isset($Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'])
            ? (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_bestseller_anzahl']
            : 3;
        $minsells = isset($Einstellungen['global']['global_bestseller_minanzahl'])
            ? (int)$Einstellungen['global']['global_bestseller_minanzahl']
            : 10;
        $bestsellers = Bestseller::buildBestsellers(
            $products,
            $_SESSION['Kundengruppe']->kKundengruppe,
            $_SESSION['Kundengruppe']->darfArtikelKategorienSehen,
            false,
            $limit,
            $minsells
        );
        Bestseller::ignoreProducts($oSuchergebnisse->Artikel->elemente, $bestsellers);
        $smarty->assign('oBestseller_arr', $bestsellers);
    }

    $cFilterShopURL = '';
    if ($bExtendedJTLSearch) {
        $cFilter_arr    = JtlSearch::getFilter($_GET);
        $cFilterShopURL = JtlSearch::buildFilterShopURL($cFilter_arr);
    }

    $smarty->assign(
        'oNaviSeite_arr',
        baueSeitenNaviURL(
            $NaviFilter,
            true,
            $oSuchergebnisse->Seitenzahlen,
            $Einstellungen['artikeluebersicht']['artikeluebersicht_max_seitenzahl'],
            $cFilterShopURL
        )
    );
    // Schauen ob die maximale Anzahl der Artikel >= der max. Anzahl die im Backend eingestellt wurde
    if ((int)$Einstellungen['artikeluebersicht']['suche_max_treffer'] > 0 &&
        $oSuchergebnisse->GesamtanzahlArtikel >= (int)$Einstellungen['artikeluebersicht']['suche_max_treffer']
    ) {
        $smarty->assign('nMaxAnzahlArtikel', 1);
    }
    //Filteroptionen rausholen
    if (!$bExtendedJTLSearch) {
        $oSuchergebnisse->Herstellerauswahl = gibHerstellerFilterOptionen($FilterSQL, $NaviFilter);
        $oSuchergebnisse->Bewertung         = gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter);
        $oSuchergebnisse->Tags              = gibTagFilterOptionen($FilterSQL, $NaviFilter);
        $oSuchergebnisse->TagsJSON          = gibTagFilterJSONOptionen($FilterSQL, $NaviFilter);
        $oSuchergebnisse->MerkmalFilter     = gibMerkmalFilterOptionen($FilterSQL,
            $NaviFilter,
            $AktuelleKategorie
        );
        $oSuchergebnisse->Preisspanne       = gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse);
        $oSuchergebnisse->Kategorieauswahl  = gibKategorieFilterOptionen($FilterSQL, $NaviFilter);
        $oSuchergebnisse->SuchFilter        = gibSuchFilterOptionen($FilterSQL, $NaviFilter);
        $oSuchergebnisse->SuchFilterJSON    = gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter);
    }
    if (!$cParameter_arr['kSuchspecial'] && !$cParameter_arr['kSuchspecialFilter']) {
        $oSuchergebnisse->Suchspecialauswahl = gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter);
    }
    //hole aktuelle Kategorie, falls eine gesetzt
    if ($cParameter_arr['kKategorie'] > 0) {
        $AktuelleKategorie = new Kategorie($cParameter_arr['kKategorie']);
    } elseif (verifyGPCDataInteger('kategorie') > 0) {
        $AktuelleKategorie = new Kategorie(verifyGPCDataInteger('kategorie'));
    }
    $AufgeklappteKategorien = new stdClass();
    if ($AktuelleKategorie->kKategorie > 0) {
        $AufgeklappteKategorien = new KategorieListe();
        $AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
        $startKat             = new Kategorie();
        $startKat->kKategorie = 0;
    }
    // Verfügbarkeitsbenachrichtigung allgemeiner CaptchaCode
    $smarty->assign(
        'code_benachrichtigung_verfuegbarkeit',
        isset($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha'])
            ? generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha'])
            : null
    );
    // Verfügbarkeitsbenachrichtigung pro Artikel
    if (is_array($oSuchergebnisse->Artikel->elemente)) {
        if (!isset($Einstellungen['artikeldetails']['benachrichtigung_nutzen'])) {
            $Einstellungen['artikeldetails']['benachrichtigung_nutzen'] = null;
        }
        foreach ($oSuchergebnisse->Artikel->elemente as $Artikel) {
            $Artikel->verfuegbarkeitsBenachrichtigung = gibVerfuegbarkeitsformularAnzeigen(
                $Artikel,
                $Einstellungen['artikeldetails']['benachrichtigung_nutzen']
            );
        }
    }
    if (count($oSuchergebnisse->Artikel->elemente) === 0) {
        if (!isset($NaviFilter->Kategorie->kKategorie)) {
            $oSuchergebnisse->SucheErfolglos = 1;
        }
    }
    if (count($oSuchergebnisse->Artikel->elemente) === 0) {
        if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
            // hole alle enthaltenen Kategorien
            if (!isset($KategorieInhalt)) {
                $KategorieInhalt = new stdClass();
            }
            $KategorieInhalt->Unterkategorien = new KategorieListe();
            $KategorieInhalt->Unterkategorien->getAllCategoriesOnLevel($NaviFilter->Kategorie->kKategorie);
            // wenn keine eigenen Artikel in dieser Kat,
            // Top Angebote / Bestseller aus unterkats + unterunterkats rausholen und anzeigen?
            if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Top' ||
                $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest'
            ) {
                $KategorieInhalt->TopArtikel = new ArtikelListe();
                $KategorieInhalt->TopArtikel->holeTopArtikel($KategorieInhalt->Unterkategorien);
            }
            if ($Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'Bestseller' ||
                $Einstellungen['artikeluebersicht']['topbest_anzeigen'] === 'TopBest'
            ) {
                if (isset($KategorieInhalt->TopArtikel)) {
                    $KategorieInhalt->BestsellerArtikel = new ArtikelListe();
                    $KategorieInhalt->BestsellerArtikel->holeBestsellerArtikel(
                        $KategorieInhalt->Unterkategorien,
                        $KategorieInhalt->TopArtikel
                    );
                }
            }
            $smarty->assign('KategorieInhalt', $KategorieInhalt);
        } else {
            // Suchfeld anzeigen
            $oSuchergebnisse->SucheErfolglos = 1;
        }
    }
    //URLs bauen, die Filter lösen
    erstelleFilterLoesenURLs(true, $oSuchergebnisse);
    // Header bauen
    $NaviFilter->cBrotNaviName          = gibBrotNaviName();
    $oSuchergebnisse->SuchausdruckWrite = gibHeaderAnzeige();
    $oSuchergebnisse->cSuche            = strip_tags(trim($cParameter_arr['cSuche']));
    // Mainword NaviBilder
    $oNavigationsinfo           = new stdClass();
    $oNavigationsinfo->cName    = '';
    $oNavigationsinfo->cBildURL = '';

    $AufgeklappteKategorien = new stdClass();
    if ($cParameter_arr['kKategorie'] > 0) {
        $AktuelleKategorie      = new Kategorie($cParameter_arr['kKategorie']);
        $AufgeklappteKategorien = new KategorieListe();
        $AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
    }
    $startKat             = new Kategorie();
    $startKat->kKategorie = 0;
    // Navigation
    $cBrotNavi = '';
    if (!isset($oMeta)) {
        $oMeta = new stdClass();
    }
    $oMeta->cMetaTitle       = '';
    $oMeta->cMetaDescription = '';
    $oMeta->cMetaKeywords    = '';

    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        $oNavigationsinfo->oKategorie = $AktuelleKategorie;
        if ($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'] === 'Y') {
            $oNavigationsinfo->cName = $AktuelleKategorie->cName;
        } elseif ($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'] === 'BT') {
            $oNavigationsinfo->cName    = $AktuelleKategorie->cName;
            $oNavigationsinfo->cBildURL = $AktuelleKategorie->getKategorieBild();
        } elseif ($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'] === 'B') {
            $oNavigationsinfo->cBildURL = $AktuelleKategorie->getKategorieBild();
        }
        $cBrotNavi = createNavigation('PRODUKTE', $AufgeklappteKategorien);
    } elseif (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
        $oNavigationsinfo->oHersteller = new Hersteller($NaviFilter->Hersteller->kHersteller);

        if ($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'] === 'Y') {
            $oNavigationsinfo->cName = $oNavigationsinfo->oHersteller->cName;
        } elseif ($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'] === 'BT') {
            $oNavigationsinfo->cName    = $oNavigationsinfo->oHersteller->cName;
            $oNavigationsinfo->cBildURL = $oNavigationsinfo->oHersteller->cBildpfadNormal;
        } elseif ($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'] === 'B') {
            $oNavigationsinfo->cBildURL = $oNavigationsinfo->oHersteller->cBildpfadNormal;
        }

        $oMeta->cMetaTitle       = $oNavigationsinfo->oHersteller->cMetaTitle;
        $oMeta->cMetaDescription = $oNavigationsinfo->oHersteller->cMetaDescription;
        $oMeta->cMetaKeywords    = $oNavigationsinfo->oHersteller->cMetaKeywords;

        $cBrotNavi = createNavigation(
            '',
            '',
            0,
            $NaviFilter->cBrotNaviName,
            gibNaviURL($NaviFilter, true, null)
        );
    } elseif (isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0) {
        $oNavigationsinfo->oMerkmalWert = new MerkmalWert($NaviFilter->MerkmalWert->kMerkmalWert);

        if ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'Y') {
            $oNavigationsinfo->cName = $oNavigationsinfo->oMerkmalWert->cName;
        } elseif ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'BT') {
            $oNavigationsinfo->cName    = isset($oNavigationsinfo->oMerkmalWert->cName)
                ? $oNavigationsinfo->oMerkmalWert->cName
                : null;
            $oNavigationsinfo->cBildURL = $oNavigationsinfo->oMerkmalWert->cBildpfadNormal;
        } elseif ($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'B') {
            $oNavigationsinfo->cBildURL = $oNavigationsinfo->oMerkmalWert->cBildpfadNormal;
        }
        if (isset($oNavigationsinfo->oMerkmalWert->cMetaTitle)) {
            $oMeta->cMetaTitle = $oNavigationsinfo->oMerkmalWert->cMetaTitle;
        }
        if (isset($oNavigationsinfo->oMerkmalWert->cMetaDescription)) {
            $oMeta->cMetaDescription = $oNavigationsinfo->oMerkmalWert->cMetaDescription;
        }
        if (isset($oNavigationsinfo->oMerkmalWert->cMetaKeywords)) {
            $oMeta->cMetaKeywords = $oNavigationsinfo->oMerkmalWert->cMetaKeywords;
        }

        $cBrotNavi = createNavigation(
            '',
            '',
            0,
            $NaviFilter->cBrotNaviName,
            gibNaviURL($NaviFilter, true, null)
        );
    } elseif (isset($NaviFilter->Tag->kTag) && $NaviFilter->Tag->kTag > 0) {
        $cBrotNavi = createNavigation(
            '',
            '',
            0,
            $NaviFilter->cBrotNaviName,
            gibNaviURL($NaviFilter, true, null)
        );
    } elseif (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0) {
        $cBrotNavi = createNavigation(
            '',
            '',
            0,
            $NaviFilter->cBrotNaviName,
            gibNaviURL($NaviFilter, true, null)
        );
    } elseif (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
        $cBrotNavi = createNavigation(
            '',
            '',
            0,
            Shop::Lang()->get('search', 'breadcrumb') . ': ' . $NaviFilter->cBrotNaviName,
            gibNaviURL($NaviFilter, true, null)
        );
    }
    // Canonical
    if (strpos(basename(gibNaviURL($NaviFilter, true, null)), '.php') === false) {
        $cSeite = '';
        if (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) &&
            $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1
        ) {
            $cSeite = SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
        }
        $cCanonicalURL = gibNaviURL($NaviFilter, true, null, 0, true) . $cSeite;
    }
    // Auswahlassistent
    if (TEMPLATE_COMPATIBILITY === true && function_exists('starteAuswahlAssistent')) {
        starteAuswahlAssistent(
            AUSWAHLASSISTENT_ORT_KATEGORIE,
            $cParameter_arr['kKategorie'],
            Shop::getLanguage(),
            $smarty,
            $Einstellungen['auswahlassistent']
        );
    } elseif (class_exists('AuswahlAssistent')) {
        AuswahlAssistent::startIfRequired(
            AUSWAHLASSISTENT_ORT_KATEGORIE, $cParameter_arr['kKategorie'], Shop::getLanguage(), $smarty
        );
    }
    // Work around fürs Template
    $smarty->assign('SEARCHSPECIALS_TOPREVIEWS', SEARCHSPECIALS_TOPREVIEWS)
           ->assign('PFAD_ART_ABNAHMEINTERVALL', PFAD_ART_ABNAHMEINTERVALL)
           ->assign('Navigation', $cBrotNavi)
           ->assign('cFehler', $cFehler)
           ->assign('Einstellungen', $Einstellungen)
           ->assign('Sortierliste', gibSortierliste($Einstellungen, $bExtendedJTLSearch))
           ->assign('Suchergebnisse', $oSuchergebnisse)
           ->assign('requestURL', isset($requestURL) ? $requestURL : null)
           ->assign('sprachURL', isset($sprachURL) ? $sprachURL : null)
           ->assign('oNavigationsinfo', $oNavigationsinfo)
           ->assign('SEO', false)
           ->assign('SESSION_NOTWENDIG', false);

    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    executeHook(HOOK_NAVI_PAGE);

    $oGlobaleMetaAngabenAssoc_arr = holeGlobaleMetaAngaben(); // Globale Metaangaben
    $oExcludedKeywordsAssoc_arr   = holeExcludedKeywords(); // Excluded Meta Keywords
    $keyWords                     = isset($oExcludedKeywordsAssoc_arr[$_SESSION['cISOSprache']]->cKeywords)
        ? $oExcludedKeywordsAssoc_arr[$_SESSION['cISOSprache']]->cKeywords
        : '';
    $smarty->assign(
        'meta_title', gibNaviMetaTitle(
            $NaviFilter,
            $oSuchergebnisse,
            $oGlobaleMetaAngabenAssoc_arr
        )
    )->assign(
        'meta_description', gibNaviMetaDescription(
            $oSuchergebnisse->Artikel->elemente,
            $NaviFilter,
            $oSuchergebnisse,
            $oGlobaleMetaAngabenAssoc_arr
        )
    )->assign(
        'meta_keywords', gibNaviMetaKeywords(
            $oSuchergebnisse->Artikel->elemente,
            $NaviFilter,
            explode(' ', $keyWords)
        )
    );

    executeHook(HOOK_NAVI_ENDE);

    $smarty->display('productlist/index.tpl');
} else {
    //Artikel
    if ($cParameter_arr['kArtikel'] > 0) {
        require_once PFAD_ROOT . 'artikel.php';
    } elseif ($cParameter_arr['kWunschliste'] > 0) {
        require_once PFAD_ROOT . 'wunschliste.php';
    } elseif ($cParameter_arr['vergleichsliste'] > 0) {
        require_once PFAD_ROOT . 'vergleichsliste.php';
    } elseif ($cParameter_arr['kNews'] > 0 ||
        $cParameter_arr['kNewsMonatsUebersicht'] > 0 ||
        $cParameter_arr['kNewsKategorie'] > 0
    ) {
        require_once PFAD_ROOT . 'news.php';
    } elseif ($cParameter_arr['kUmfrage'] > 0) {
        require_once PFAD_ROOT . 'umfrage.php';
    } elseif ($cParameter_arr['kLink'] > 0) {
        require_once PFAD_ROOT . 'seite.php';
    } else {
        Shop::getEntryPoint();
    }
    if (Shop::$is404 === true) {
        $cParameter_arr['is404'] = true;
    }
    if ($cParameter_arr['is404'] === true) {
        if (isset($seo) && strlen($seo) > 0) {
            executeHook(HOOK_INDEX_SEO_404, ['seo' => $seo]);
        }
        if (!Shop::$kLink) {
            $hookInfos     = urlNotFoundRedirect([
                'key'   => 'kLink',
                'value' => $cParameter_arr['kLink']
            ]);
            $kLink         = (int)$hookInfos['value'];
            $bFileNotFound = $hookInfos['isFileNotFound'];
            if (!$kLink) {
                $oLink       = Shop::DB()->select('tlink', 'nLinkart', LINKTYP_404);
                $kLink       = (int)$oLink->kLink;
                Shop::$kLink = $kLink;
            }
        }
        require_once PFAD_ROOT . 'seite.php';
    }
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';