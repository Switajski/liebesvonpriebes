<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::run();
$cParameter_arr   = Shop::getParameters();
$cURLID           = StringHandler::filterXSS(verifyGPDataString('wlid'));
$Einstellungen    = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$kWunschliste     = (verifyGPCDataInteger('wl') > 0 && verifyGPCDataInteger('wlvm') === 0)
    ? verifyGPCDataInteger('wl') //one of multiple customer wishlists
    : (isset($cParameter_arr['kWunschliste'])
        ? $cParameter_arr['kWunschliste'] //default wishlist from Shop class
        : $cURLID); //public link
$AktuelleSeite    = 'WUNSCHLISTE';
$cHinweis         = '';
$cFehler          = '';
$cSuche           = null;
$step             = null;
$CWunschliste     = null;
$action           = null;
$kWunschlistePos  = null;
$oWunschliste_arr = [];
$linkHelper       = LinkHelper::getInstance();

if ($kWunschliste === 0 && !empty($_SESSION['Kunde']->kKunde) && empty($_SESSION['Wunschliste']->kWunschliste)) {
    //create new wishlist at very first visit
    $_SESSION['Wunschliste'] = new Wunschliste();
    $_SESSION['Wunschliste']->schreibeDB();
    $kWunschliste = (int)$_SESSION['Wunschliste']->kWunschliste;
}

Shop::setPageType(PAGE_WUNSCHLISTE);
if (!empty($_POST['addToCart'])) {
    $action          = 'addToCart';
    $kWunschlistePos = (int)$_POST['addToCart'];
} elseif (!empty($_POST['remove'])) {
    $action          = 'remove';
    $kWunschlistePos = (int)$_POST['remove'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if ($action !== null && isset($_POST['kWunschliste'], $_SESSION['Kunde']->kKunde) && validateToken()) {
    $kWunschliste = (int)$_POST['kWunschliste'];
    // check if wishlist belongs to logged in customer
    $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', $kWunschliste);
    $userOK       = (int)$_SESSION['Kunde']->kKunde === (int)$oWunschliste->kKunde;

    switch ($action) {
        case 'addToCart':
            $oWunschliste    = new Wunschliste($kWunschliste);
            $oWunschlistePos = giboWunschlistePos($kWunschlistePos);
            if ($oWunschliste->kWunschliste === (int)$oWunschlistePos->kWunschliste
                && isset($oWunschlistePos->kArtikel) && $oWunschlistePos->kArtikel > 0
            ) {
                $oEigenschaftwerte_arr = ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)
                    ? gibVarKombiEigenschaftsWerte($oWunschlistePos->kArtikel)
                    : gibEigenschaftenZuWunschliste($kWunschliste, $oWunschlistePos->kWunschlistePos);
                if (!$oWunschlistePos->bKonfig) {
                    fuegeEinInWarenkorb($oWunschlistePos->kArtikel, $oWunschlistePos->fAnzahl, $oEigenschaftwerte_arr);
                }
                $cHinweis = Shop::Lang()->get('basketAdded', 'messages');
            }
            break;

        case 'sendViaMail':
            // Pruefen, ob der MD5 vorhanden ist
            $oWunschliste = Shop::DB()->select(
                'twunschliste',
                ['kWunschliste', 'kKunde'],
                [$kWunschliste, (int)$_SESSION['Kunde']->kKunde]
            );
            if (!empty($oWunschliste->kWunschliste) && strlen($oWunschliste->cURLID) > 0) {
                $step = 'wunschliste anzeigen';
                require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
                // Soll die Wunschliste nun an die Emailempfaenger geschickt werden?
                if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                    if ($Einstellungen['global']['global_wunschliste_anzeigen'] === 'Y') {
                        $cEmail_arr = explode(' ', StringHandler::htmlentities(StringHandler::filterXSS($_POST['email'])));
                        $cHinweis  .= wunschlisteSenden($cEmail_arr, $kWunschliste);
                        // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                        $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
                    }
                } else {
                    // Maske aufbauen
                    $step = 'wunschliste versenden';
                    // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                    $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
                }
            }
            break;

        case 'addAllToCart':
            $oWunschliste = new Wunschliste($kWunschliste);
            if (count($oWunschliste->CWunschlistePos_arr) > 0) {
                foreach ($oWunschliste->CWunschlistePos_arr as $oWunschlistePos) {
                    $oEigenschaftwerte_arr = ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)
                        ? gibVarKombiEigenschaftsWerte($oWunschlistePos->kArtikel)
                        : gibEigenschaftenZuWunschliste($kWunschliste, $oWunschlistePos->kWunschlistePos);
                    if (!$oWunschlistePos->Artikel->bHasKonfig && empty($oWunschlistePos->bKonfig) &&
                        isset($oWunschlistePos->Artikel->inWarenkorbLegbar) &&
                        $oWunschlistePos->Artikel->inWarenkorbLegbar > 0
                    ) {
                        fuegeEinInWarenkorb(
                            $oWunschlistePos->kArtikel,
                            $oWunschlistePos->fAnzahl,
                            $oEigenschaftwerte_arr
                        );
                    }
                }
                $cHinweis .= Shop::Lang()->get('basketAllAdded', 'messages');
            }
            break;

        case 'remove':
            if ($userOK === true && $kWunschlistePos > 0) {
                $oWunschliste = new Wunschliste($kWunschliste);
                $oWunschliste->entfernePos($kWunschlistePos);
                $cHinweis .= Shop::Lang()->get('wishlistUpdate', 'messages');
            }
            break;

        case 'removeAll':
            if ($userOK === true) {
                $oWunschliste = new Wunschliste($kWunschliste);
                if ((int)$oWunschliste->kKunde === (int)$_SESSION['Kunde']->kKunde && $oWunschliste->kKunde) {
                    $oWunschliste->entferneAllePos();
                    if ((int)$_SESSION['Wunschliste']->kWunschliste === (int)$oWunschliste->kWunschliste) {
                        $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                    }
                    $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
                }
            }
            break;

        case 'update':
            if ($userOK === true) {
                $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', $kWunschliste);
                if (!empty($_POST['wishlistName']) && $_POST['wishlistName'] !== $oWunschliste->cName) {
                    $oWunschliste->cName = StringHandler::htmlentities(StringHandler::filterXSS($_POST['wishlistName']));
                    Shop::DB()->update('twunschliste', 'kWunschliste', $kWunschliste, $oWunschliste);
                }
                if (!empty($oWunschliste->kKunde) && !empty($_SESSION['Kunde']->kKunde) &&
                    (int)$oWunschliste->kKunde === (int)$_SESSION['Kunde']->kKunde
                ) {
                    $cHinweis               .= wunschlisteAktualisieren($kWunschliste);
                    $CWunschliste            = isset($_SESSION['Wunschliste']->kWunschliste)
                        ? new Wunschliste($_SESSION['Wunschliste']->kWunschliste)
                        : new Wunschliste($kWunschliste);
                    $_SESSION['Wunschliste'] = $CWunschliste;
                }
            }
            break;

        case 'setPublic':
            if ($userOK === true && isset($_POST['kWunschlisteTarget'])) {
                $cURLID = gibUID(32, substr(md5($kWunschliste), 0, 16) . time());
                // Kampagne
                $oKampagne = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
                if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                    $cURLID .= '&' . $oKampagne->cParameter . '=' . $oKampagne->cWert;
                }
                $upd               = new stdClass();
                $upd->nOeffentlich = 1;
                $upd->cURLID       = $cURLID;
                Shop::DB()->update('twunschliste', 'kWunschliste', (int)$_POST['kWunschlisteTarget'], $upd);
                $cHinweis .= Shop::Lang()->get('wishlistSetPublic', 'messages');
            }
            break;

        case 'setPrivate':
            if ($userOK === true && isset($_POST['kWunschlisteTarget'])) {
                $upd               = new stdClass();
                $upd->nOeffentlich = 0;
                $upd->cURLID       = '';
                Shop::DB()->update('twunschliste', 'kWunschliste', (int)$_POST['kWunschlisteTarget'], $upd);
                $cHinweis .= Shop::Lang()->get('wishlistSetPrivate', 'messages');
            }
            break;

        case 'createNew':
            $CWunschlisteName = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cWunschlisteName']));
            $cHinweis        .= wunschlisteSpeichern($CWunschlisteName);
            break;

        case 'delete':
            if ($userOK === true && isset($_POST['kWunschlisteTarget'])) {
                $cHinweis .= wunschlisteLoeschen((int)$_POST['kWunschlisteTarget']);
                if ((int)$_POST['kWunschlisteTarget'] === $kWunschliste) {
                    //the currently active one was deleted, search for a new one
                    $newWishlist = Shop::DB()->select('twunschliste', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                    if (isset($newWishlist->kWunschliste)) {
                        $kWunschliste           = (int)$newWishlist->kWunschliste;
                        $newWishlist->nStandard = 1;
                        Shop::DB()->update('twunschliste', 'kWunschliste', $kWunschliste, $newWishlist);
                    } elseif (empty($_SESSION['Wunschliste']->kWunschliste)) {
                        $_SESSION['Wunschliste'] = new Wunschliste();
                        $_SESSION['Wunschliste']->schreibeDB();
                        $kWunschliste = $_SESSION['Wunschliste']->kWunschliste;
                    }
                }
            }
            break;

        case 'setAsDefault':
            if ($userOK === true && isset($_POST['kWunschlisteTarget'])) {
                $cHinweis    .= wunschlisteStandard((int)$_POST['kWunschlisteTarget']);
                $kWunschliste = (int)$_POST['kWunschlisteTarget'];
            }
            break;

        case 'search':
            $cSuche = StringHandler::filterXSS(verifyGPDataString('cSuche'));
            if ($userOK === true && strlen($cSuche) > 0) {
                $oWunschliste                      = new Wunschliste($kWunschliste);
                $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
                $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
                $CWunschliste                      = $oWunschliste;
            }
            break;

        default:
            break;
    }
} elseif ($action === 'search' && $kWunschliste > 0 && validateToken()) {
    // Suche in einer öffentlichen Wunschliste
    $cSuche = StringHandler::filterXSS(verifyGPDataString('cSuche'));
    if (strlen($cSuche) > 0) {
        $oWunschliste                      = new Wunschliste($kWunschliste);
        $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
        $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
        $CWunschliste                      = $oWunschliste;
    }
}

if (verifyGPCDataInteger('wlidmsg') > 0) {
    $cHinweis .= mappeWunschlisteMSG(verifyGPCDataInteger('wlidmsg'));
}
// Falls Wunschliste vielleicht vorhanden aber nicht öffentlich
if (verifyGPCDataInteger('error') === 1) {
    if (strlen($cURLID) > 0) {
        $oWunschliste = Shop::DB()->select('twunschliste', 'cURLID', $cURLID);
        if (!isset($oWunschliste->kWunschliste, $oWunschliste->nOeffentlich) ||
            $oWunschliste->kWunschliste >= 0 ||
            $oWunschliste->nOeffentlich <= 0
        ) {
            $cFehler = sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID);
        }
    } else {
        $cFehler = sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID);
    }
} elseif (!$kWunschliste) {
    if (!empty($_SESSION['Kunde']->kKunde)) {
        $wishLists = Shop::DB()->selectAll('twunschliste', 'kKunde', $_SESSION['Kunde']->kKunde);
        //try to find active wishlist
        foreach ($wishLists as $wishList) {
            if ($wishList->nStandard === '1') {
                $kWunschliste = isset($wishList->kWunschliste)
                    ? (int)$wishList->kWunschliste
                    : 0;
                break;
            }
        }
        //take the first non-active wishlist if no active one was found
        if (!$kWunschliste && count($wishLists) > 0) {
            $newWishlist            = $wishLists[0];
            $kWunschliste           = (int)$newWishlist->kWunschliste;
            $newWishlist->nStandard = 1;
            Shop::DB()->update('twunschliste', 'kWunschliste', $kWunschliste, $newWishlist);
        }
    }
    if (!$kWunschliste) {
        header('Location: ' .
            $linkHelper->getStaticRoute('jtl.php') .
            '?u=' . $cParameter_arr['kUmfrage'] . '&r=' . R_LOGIN_WUNSCHLISTE);
        exit;
    }
}
$link       = ($cParameter_arr['kLink'] > 0) ? $linkHelper->getPageLink($cParameter_arr['kLink']) : null;
$requestURL = baueURL($link, URLART_SEITE);
$sprachURL  = isset($link->languageURLs) ? $link->languageURLs : baueSprachURLS($link, URLART_SEITE);
// Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
if (empty($CWunschliste)) {
    $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
}
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $oWunschliste_arr = Shop::DB()->selectAll(
        'twunschliste',
        'kKunde',
        (int)$_SESSION['Kunde']->kKunde,
        '*',
        'dErstellt DESC'
    );
}
$smarty->assign('CWunschliste', $CWunschliste)
       ->assign('oWunschliste_arr', $oWunschliste_arr)
       ->assign('wlsearch', $cSuche)
       ->assign('hasItems', !empty($CWunschliste->CWunschlistePos_arr))
       ->assign(
           'isCurrenctCustomer',
           isset($CWunschliste->kKunde, $_SESSION['Kunde']->kKunde)
               && (int)$CWunschliste->kKunde === (int)$_SESSION['Kunde']->kKunde
       )
       ->assign('Einstellungen', $Einstellungen)
       ->assign('cURLID', $cURLID)
       ->assign('step', $step)
       ->assign('cFehler', $cFehler)
       ->assign('cHinweis', $cHinweis);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

// Kampagne oeffentlicher Wunschzettel
if (isset($CWunschliste->kWunschliste) && $CWunschliste->kWunschliste > 0) {
    $oKampagne = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);

    if (isset($oKampagne->kKampagne, $oKampagne->cWert) &&
        strtolower($oKampagne->cWert) === strtolower(verifyGPDataString($oKampagne->cParameter))
    ) {
        $oKampagnenVorgang               = new stdClass();
        $oKampagnenVorgang->kKampagne    = $oKampagne->kKampagne;
        $oKampagnenVorgang->kKampagneDef = KAMPAGNE_DEF_HIT;
        $oKampagnenVorgang->kKey         = $_SESSION['oBesucher']->kBesucher;
        $oKampagnenVorgang->fWert        = 1.0;
        $oKampagnenVorgang->cParamWert   = $oKampagne->cWert;
        $oKampagnenVorgang->dErstellt    = 'now()';

        Shop::DB()->insert('tkampagnevorgang', $oKampagnenVorgang);
        $_SESSION['Kampagnenbesucher'] = $oKampagne;
    }
}

$smarty->display('snippets/wishlist.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
