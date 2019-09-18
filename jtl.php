<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global JTLSmarty $smarty
 * @global Session $session
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kundenwerbenkeunden_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$AktuelleSeite = 'MEIN KONTO';
$cBrotNavi     = '';
$linkHelper    = LinkHelper::getInstance();
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDENFELD,
    CONF_KUNDENWERBENKUNDEN,
    CONF_TRUSTEDSHOPS
]);
$kLink         = $linkHelper->getSpecialPageLinkKey(LINKTYP_LOGIN);
$cHinweis      = '';
$hinweis       = '';
$cFehler       = '';

if (verifyGPCDataInteger('wlidmsg') > 0) {
    $cHinweis .= mappeWunschlisteMSG(verifyGPCDataInteger('wlidmsg'));
}
//Kunden in session aktualisieren
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $Kunde = new Kunde($_SESSION['Kunde']->kKunde);
    if ($Kunde->kKunde > 0) {
        $Kunde->angezeigtesLand = ISO2land($Kunde->cLand);
        $session->setCustomer($Kunde);
    }
}
// Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
// wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt.
// Nach dem erfolgreichen einloggen wird die zuvor angestrebte Aktion durchgeführt.
if (isset($_SESSION['JTL_REDIRECT']) || verifyGPCDataInteger('r') > 0) {
    $smarty->assign('oRedirect', isset($_SESSION['JTL_REDIRECT']) ? $_SESSION['JTL_REDIRECT'] : gibRedirect(verifyGPCDataInteger('r')));
    executeHook(HOOK_JTL_PAGE_REDIRECT_DATEN);
}
// Upload zum Download freigeben
if (isset($_POST['kUpload']) &&
    (int)$_POST['kUpload'] > 0 &&
    !empty($_SESSION['Kunde']->kKunde) &&
    validateToken()
) {
    $oUploadDatei = new UploadDatei((int)$_POST['kUpload']);
    if ($oUploadDatei->validateOwner((int)$_SESSION['Kunde']->kKunde)) {
        UploadDatei::send_file_to_browser(
            PFAD_UPLOADS . $oUploadDatei->cPfad,
            'application/octet-stream',
            true,
            $oUploadDatei->cName
        );
    }
}

unset($_SESSION['JTL_REDIRECT']);

if (isset($_GET['updated_pw']) && $_GET['updated_pw'] === 'true') {
    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
}
//loginbenutzer?
if (isset($_POST['login']) && (int)$_POST['login'] === 1 && !empty($_POST['email']) && !empty($_POST['passwort'])) {
    fuehreLoginAus($_POST['email'], $_POST['passwort']);
}

$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
$editRechnungsadresse = 0;

if (isset($Kunde) && !empty($Kunde->kKunde) && (
        (isset($_GET['editRechnungsadresse']) && (int)$_GET['editRechnungsadresse'] > 0) ||
        (isset($_POST['editRechnungsadresse']) && (int)$_POST['editRechnungsadresse'] > 0))
) {
    $editRechnungsadresse = 1;
}

Shop::setPageType(PAGE_LOGIN);
$step = 'login';
if (isset($_GET['loggedout'])) {
    $cHinweis .= Shop::Lang()->get('loggedOut', 'global');
}
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    Shop::setPageType(PAGE_MEINKONTO);
    $step = 'mein Konto';
    // abmelden + meldung
    if (isset($_GET['logout']) && (int)$_GET['logout'] === 1 && !empty($_SESSION['Kunde']->kKunde)) {
        // Sprache und Waehrung beibehalten
        $kSprache    = Shop::getLanguage();
        $cISOSprache = Shop::getLanguage(true);
        $Waehrung    = $_SESSION['Waehrung'];
        // Kategoriecache loeschen
        unset(
            $_SESSION['kKategorieVonUnterkategorien_arr'],
            $_SESSION['oKategorie_arr'],
            $_SESSION['oKategorie_arr_new'],
            $_SESSION['Warenkorb']
        );

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 7000000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
        session_destroy();
        $session = new Session();
        session_regenerate_id(true);

        $_SESSION['kSprache']    = $kSprache;
        $_SESSION['cISOSprache'] = $cISOSprache;
        $_SESSION['Waehrung']    = $Waehrung;
        Shop::setLanguage($kSprache, $cISOSprache);

        header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) . '?loggedout=1', true, 303);
        exit();
    }

    if (isset($_GET['del']) && (int)$_GET['del'] === 1) {
        $step = 'account loeschen';
    }
    // Vorhandenen Warenkorb mit persistenten Warenkorb mergen?
    if (verifyGPCDataInteger('basket2Pers') === 1) {
        setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true), true, 303);
        exit();
    }
    // Wunschliste loeschen
    if (verifyGPCDataInteger('wllo') > 0 && validateToken()) {
        $step      = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        $cHinweis .= wunschlisteLoeschen(verifyGPCDataInteger('wllo'));
    }
    // Wunschliste Standard setzen
    if (isset($_POST['wls']) && (int)$_POST['wls'] > 0 && validateToken()) {
        $step      = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        $cHinweis .= wunschlisteStandard(verifyGPCDataInteger('wls'));
    }
    // Kunden werben Kunden
    if ($Einstellungen['kundenwerbenkunden']['kwk_nutzen'] === 'Y' && verifyGPCDataInteger('KwK') === 1) {
        $step = 'kunden_werben_kunden';
        if (verifyGPCDataInteger('kunde_werben') === 1) {
            if (!pruefeEmailblacklist($_POST['cEmail'])) {
                if (pruefeEingabe($_POST)) {
                    if (setzeKwKinDB($_POST, $Einstellungen)) {
                        $cHinweis .= sprintf(
                            Shop::Lang()->get('kwkAdd', 'messages') . '<br />',
                            StringHandler::filterXSS($_POST['cEmail'])
                        );
                    } else {
                        $cFehler .= sprintf(
                            Shop::Lang()->get('kwkAlreadyreg', 'errorMessages') . '<br />',
                            StringHandler::filterXSS($_POST['cEmail'])
                        );
                    }
                } else {
                    $cFehler .= Shop::Lang()->get('kwkWrongdata', 'errorMessages') . '<br />';
                }
            } else {
                $cFehler .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
            }
        }
    }
    // WunschlistePos in den Warenkorb adden
    if (isset($_GET['wlph']) && (int)$_GET['wlph'] > 0 && (int)$_GET['wl'] > 0) {
        $cURLID          = StringHandler::filterXSS(verifyGPDataString('wlid'));
        $kWunschlistePos = verifyGPCDataInteger('wlph');
        $kWunschliste    = verifyGPCDataInteger('wl');
        $step            = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0)
            ? 'mein Konto'
            : 'login';
        $oWunschlistePos = giboWunschlistePos($kWunschlistePos);
        if (isset($oWunschlistePos->kArtikel) || $oWunschlistePos->kArtikel > 0) {
            $oEigenschaftwerte_arr = ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)
                ? gibVarKombiEigenschaftsWerte($oWunschlistePos->kArtikel)
                : gibEigenschaftenZuWunschliste($kWunschliste, $oWunschlistePos->kWunschlistePos);
            if (!$oWunschlistePos->bKonfig) {
                fuegeEinInWarenkorb($oWunschlistePos->kArtikel, $oWunschlistePos->fAnzahl, $oEigenschaftwerte_arr);
            }
            $cParamWLID = (strlen($cURLID) > 0) ? ('&wlid=' . $cURLID) : '';
            header(
                'Location: ' . $linkHelper->getStaticRoute('jtl.php', true) .
                '?wl=' . $kWunschliste .
                '&wlidmsg=1' . $cParamWLID, true, 303
            );
            exit();
        }
    }
    // WunschlistePos alle in den Warenkorb adden
    if (isset($_GET['wlpah']) && (int)$_GET['wlpah'] === 1 && (int)$_GET['wl'] > 0) {
        $cURLID       = StringHandler::filterXSS(verifyGPDataString('wlid'));
        $kWunschliste = verifyGPCDataInteger('wl');
        $step         = 'mein Konto';
        $oWunschliste = new Wunschliste($kWunschliste);

        if (count($oWunschliste->CWunschlistePos_arr) > 0) {
            foreach ($oWunschliste->CWunschlistePos_arr as $oWunschlistePos) {
                $oEigenschaftwerte_arr = ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)
                    ? gibVarKombiEigenschaftsWerte($oWunschlistePos->kArtikel)
                    : gibEigenschaftenZuWunschliste($kWunschliste, $oWunschlistePos->kWunschlistePos);
                if (!$oWunschlistePos->Artikel->bHasKonfig &&
                    !$oWunschlistePos->bKonfig &&
                    isset($oWunschlistePos->Artikel->inWarenkorbLegbar) &&
                    $oWunschlistePos->Artikel->inWarenkorbLegbar > 0
                ) {
                    fuegeEinInWarenkorb($oWunschlistePos->kArtikel, $oWunschlistePos->fAnzahl, $oEigenschaftwerte_arr);
                }
            }
            header(
                'Location: ' . $linkHelper->getStaticRoute('jtl.php', true) .
                '?wl=' . $kWunschliste .
                '&wlid=' . $cURLID .
                '&wlidmsg=2', true, 303
            );
            exit();
        }
    }
    // Wunschliste aktualisieren bzw alle Positionen
    if (verifyGPCDataInteger('wla') > 0 && verifyGPCDataInteger('wl') > 0) {
        $step         = 'mein Konto';
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste) {
            // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
            $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', $kWunschliste);
            if (!empty($oWunschliste->kKunde) && $oWunschliste->kKunde == $_SESSION['Kunde']->kKunde) {
                $step                    = 'wunschliste anzeigen';
                $cHinweis               .= wunschlisteAktualisieren($kWunschliste);
                $_SESSION['Wunschliste'] = new Wunschliste(
                    isset($_SESSION['Wunschliste']->kWunschliste)
                    ? $_SESSION['Wunschliste']->kWunschliste
                    : $kWunschliste
                );
                $cBrotNavi               = createNavigation(
                    '',
                    0,
                    0,
                    $_SESSION['Wunschliste']->cName,
                    'jtl.php?wl=' . $_SESSION['Wunschliste']->kWunschliste
                );
            }
        }
    }
    // neue Wunschliste speichern
    if (isset($_POST['wlh']) && (int)$_POST['wlh'] > 0) {
        $step             = 'mein Konto';
        $cWunschlisteName = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cWunschlisteName']));
        $cHinweis        .= wunschlisteSpeichern($cWunschlisteName);
    }
    // Wunschliste via Email
    if (verifyGPCDataInteger('wlvm') > 0 && verifyGPCDataInteger('wl') > 0) {
        $kWunschliste = verifyGPCDataInteger('wl');
        $step         = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        // Pruefen, ob der MD5 vorhanden ist
        if ((int)$kWunschliste > 0) {
            $oWunschliste = Shop::DB()->select(
                'twunschliste',
                'kWunschliste',
                $kWunschliste,
                'kKunde',
                (int)$_SESSION['Kunde']->kKunde,
                null,
                null,
                false,
                'kWunschliste, cURLID'
            );
            if (isset($oWunschliste->kWunschliste) &&
                $oWunschliste->kWunschliste > 0 &&
                strlen($oWunschliste->cURLID) > 0
            ) {
                $step = 'wunschliste anzeigen';
                // Soll die Wunschliste nun an die Emailempfaenger geschickt werden?
                if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                    if ($Einstellungen['global']['global_wunschliste_anzeigen'] === 'Y') {
                        $cEmail_arr = explode(' ', StringHandler::htmlentities(StringHandler::filterXSS($_POST['email'])));
                        $cHinweis  .= wunschlisteSenden($cEmail_arr, $kWunschliste);
                        // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                        $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
                        $smarty->assign('CWunschliste', $CWunschliste);
                        $cBrotNavi = createNavigation(
                            '',
                            0,
                            0,
                            $CWunschliste->cName,
                            'jtl.php?wl=' . $CWunschliste->kWunschliste
                        );
                    }
                } else {
                    // Maske aufbauen
                    $step = 'wunschliste versenden';
                    // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                    $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
                    $smarty->assign('CWunschliste', $CWunschliste);
                    $cBrotNavi = createNavigation(
                        '',
                        0,
                        0,
                        $CWunschliste->cName,
                        'jtl.php?wl=' . $CWunschliste->kWunschliste
                    );
                }
            }
        }
    }
    // Wunschliste alle Positionen loeschen
    if (verifyGPCDataInteger('wldl') === 1) {
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste) {
            $oWunschliste = new Wunschliste($kWunschliste);

            if ($oWunschliste->kKunde == $_SESSION['Kunde']->kKunde && $oWunschliste->kKunde) {
                $step = 'wunschliste anzeigen';
                $oWunschliste->entferneAllePos();
                if ($_SESSION['Wunschliste']->kWunschliste == $oWunschliste->kWunschliste) {
                    $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                    $cBrotNavi                                    = createNavigation(
                        '',
                        0,
                        0,
                        $_SESSION['Wunschliste']->cName,
                        'jtl.php?wl=' . $_SESSION['Wunschliste']->kWunschliste
                    );
                }
                $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
            }
        }
    }
    // Wunschliste Artikelsuche
    if (verifyGPCDataInteger('wlsearch') === 1) {
        $cSuche       = StringHandler::filterXSS(verifyGPDataString('cSuche'));
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste) {
            $oWunschliste = new Wunschliste($kWunschliste);
            if ($oWunschliste->kKunde == $_SESSION['Kunde']->kKunde && $oWunschliste->kKunde) {
                $step = 'wunschliste anzeigen';
                $smarty->assign('wlsearch', $cSuche);
                $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
                $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
                $smarty->assign('CWunschliste', $oWunschliste);
                $cBrotNavi = createNavigation(
                    '',
                    0,
                    0,
                    $oWunschliste->cName,
                    'jtl.php?wl=' . $oWunschliste->kWunschliste
                );
            }
        }
    } elseif (verifyGPCDataInteger('wl') > 0 && verifyGPCDataInteger('wlvm') === 0) { // Wunschliste anzeigen
        $step         = (!empty($_SESSION['Kunde']->kKunde)) ? 'mein Konto' : 'login';
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste > 0) {
            // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
            $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', (int)$kWunschliste);
            if (isset($_SESSION['Kunde']->kKunde, $oWunschliste->kKunde) &&
                $oWunschliste->kKunde == $_SESSION['Kunde']->kKunde
            ) {
                // Wurde nOeffentlich verändert
                if (isset($_REQUEST['wlAction']) && validateToken()) {
                    $nOeffentlich = verifyGPDataString('wlAction');
                    // Wurde nstd auf 1 oder 0 gesetzt?
                    if ($nOeffentlich === 'setPrivate') {
                        $upd               = new stdClass();
                        $upd->nOeffentlich = 0;
                        $upd->cURLID       = '';
                        // nOeffentlich der Wunschliste updaten zu Privat
                        Shop::DB()->update('twunschliste', 'kWunschliste', $kWunschliste, $upd);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPrivate', 'messages');
                    } elseif ($nOeffentlich === 'setPublic') {
                        $cURLID = gibUID(32, substr(md5($kWunschliste), 0, 16) . time());
                        // Kampagne
                        $oKampagne = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
                        if ($oKampagne->kKampagne > 0) {
                            $cURLID .= '&' . $oKampagne->cParameter . '=' . $oKampagne->cWert;
                        }
                        // nOeffentlich der Wunschliste updaten zu öffentlich
                        $upd               = new stdClass();
                        $upd->nOeffentlich = 1;
                        $upd->cURLID       = $cURLID;
                        Shop::DB()->update('twunschliste', 'kWunschliste', $kWunschliste, $upd);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPublic', 'messages');
                    }
                }
                // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                $CWunschliste = bauecPreis(new Wunschliste($oWunschliste->kWunschliste));

                $smarty->assign('CWunschliste', $CWunschliste);
                $step      = 'wunschliste anzeigen';
                $cBrotNavi = createNavigation(
                    '',
                    0,
                    0,
                    $CWunschliste->cName,
                    'jtl.php?wl=' . $CWunschliste->kWunschliste
                );
            }
        }
    }
    if ($editRechnungsadresse === 1) {
        $step = 'rechnungsdaten';
    }
    if (isset($_GET['pass']) && (int)$_GET['pass'] === 1) {
        $step = 'passwort aendern';
    }
    // Kundendaten speichern
    if (isset($_POST['edit']) && (int)$_POST['edit'] === 1 && validateToken()) {
        $cPost_arr = StringHandler::filterXSS($_POST);
        $smarty->assign('cPost_arr', $cPost_arr);

        $fehlendeAngaben = checkKundenFormularArray($cPost_arr, 1, 0);
        $kKundengruppe   = Kundengruppe::getCurrent();
        // CheckBox Plausi
        $oCheckBox           = new CheckBox();
        $fehlendeAngaben     = array_merge(
            $fehlendeAngaben,
            $oCheckBox->validateCheckBox(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $cPost_arr, true)
        );
        $knd                 = getKundendaten($cPost_arr, 0, 0);
        $cKundenattribut_arr = getKundenattribute($cPost_arr);
        $nReturnValue        = angabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_JTL_PAGE_KUNDENDATEN_PLAUSI);

        if ($nReturnValue) {
            $knd->cAbgeholt = 'N';
            $knd->updateInDB();
            // CheckBox Spezialfunktion ausführen
            $oCheckBox->triggerSpecialFunction(
                CHECKBOX_ORT_KUNDENDATENEDITIEREN,
                $kKundengruppe,
                true,
                $cPost_arr,
                ['oKunde' => $knd]
            )->checkLogging(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $cPost_arr, true);
            // Kundendatenhistory
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $knd, Kundendatenhistory::QUELLE_MEINKONTO);
            $_SESSION['Kunde'] = $knd;
            // Update Kundenattribute
            if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
                $oKundenfeldNichtEditierbar_arr = getKundenattributeNichtEditierbar();
                $nonEditableCustomerfields_arr  = [];
                foreach ($oKundenfeldNichtEditierbar_arr as $i => $oKundenfeldNichtEditierbar) {
                    $nonEditableCustomerfields_arr[] = 'kKundenfeld != ' . (int)$oKundenfeldNichtEditierbar->kKundenfeld;
                }
                $cSQL = '';
                if (is_array($nonEditableCustomerfields_arr) && count($nonEditableCustomerfields_arr) > 0) {
                    $cSQL = ' AND ' . implode(' AND ', $nonEditableCustomerfields_arr);
                }
                Shop::DB()->query(
                    "DELETE FROM tkundenattribut
                        WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . $cSQL, 3
                );
                $nKundenattributKey_arr             = array_keys($cKundenattribut_arr);
                $oKundenAttributNichtEditierbar_arr = getNonEditableCustomerFields();
                if (is_array($oKundenAttributNichtEditierbar_arr) && count($oKundenAttributNichtEditierbar_arr) > 0) {
                    $attrKeys = array_keys($oKundenAttributNichtEditierbar_arr);
                    foreach (array_diff($nKundenattributKey_arr, $attrKeys) as $kKundenfeld) {
                        $oKundenattribut              = new stdClass();
                        $oKundenattribut->kKunde      = (int)$_SESSION['Kunde']->kKunde;
                        $oKundenattribut->kKundenfeld = (int)$cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                        $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                        $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                        Shop::DB()->insert('tkundenattribut', $oKundenattribut);
                    }
                } else {
                    foreach ($nKundenattributKey_arr as $kKundenfeld) {
                        $oKundenattribut              = new stdClass();
                        $oKundenattribut->kKunde      = (int)$_SESSION['Kunde']->kKunde;
                        $oKundenattribut->kKundenfeld = (int)$cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                        $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                        $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                        Shop::DB()->insert('tkundenattribut', $oKundenattribut);
                    }
                }
            }
            // $step = 'mein Konto';
            $cHinweis .= Shop::Lang()->get('dataEditSuccessful', 'login');
            setzeSteuersaetze();
            if (isset($_SESSION['Warenkorb']->kWarenkorb) &&
                $_SESSION['Warenkorb']->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized();
            }
        } else {
            $smarty->assign('fehlendeAngaben', $fehlendeAngaben);
        }
    }
    if (isset($_POST['pass_aendern']) && (int)$_POST['pass_aendern'] && validateToken()) {
        $step = 'passwort aendern';
        if (!isset($_POST['altesPasswort'], $_POST['neuesPasswort1']) ||
            !$_POST['altesPasswort'] ||
            !$_POST['neuesPasswort1']
        ) {
            $cHinweis .= Shop::Lang()->get('changepasswordFilloutForm', 'login');
        }
        if ((isset($_POST['neuesPasswort1']) && !isset($_POST['neuesPasswort2'])) ||
            (isset($_POST['neuesPasswort2']) && !isset($_POST['neuesPasswort1'])) ||
            $_POST['neuesPasswort1'] !== $_POST['neuesPasswort2']
        ) {
            $cFehler .= Shop::Lang()->get('changepasswordPassesNotEqual', 'login');
        }
        if (isset($_POST['neuesPasswort1']) &&
            strlen($_POST['neuesPasswort1']) < $Einstellungen['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $cFehler .= Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' ' .
                lang_passwortlaenge($Einstellungen['kunden']['kundenregistrierung_passwortlaenge']);
        }
        if (isset($_POST['neuesPasswort1'], $_POST['neuesPasswort2']) &&
            $_POST['neuesPasswort1'] && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2'] &&
            strlen($_POST['neuesPasswort1']) >= $Einstellungen['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $oKunde = new Kunde($_SESSION['Kunde']->kKunde);
            $oUser  = Shop::DB()->select(
                'tkunde',
                'kKunde',
                (int)$_SESSION['Kunde']->kKunde,
                null,
                null,
                null,
                null,
                false,
                'cPasswort, cMail'
            );
            if (isset($oUser->cPasswort, $oUser->cMail)) {
                $ok = $oKunde->checkCredentials($oUser->cMail, $_POST['altesPasswort']);
                if ($ok !== false) {
                    $oKunde->updatePassword($_POST['neuesPasswort1']);
                    $step      = 'mein Konto';
                    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
                } else {
                    $cFehler .= Shop::Lang()->get('changepasswordWrongPass', 'login');
                }
            }
        }
    }
    if (verifyGPCDataInteger('bestellungen') > 0) {
        if (isset($_SESSION['Kunde'], $_SESSION['Kunde']->kKunde) && (int)$_SESSION['Kunde']->kKunde > 0) {
            $step = 'bestellungen';
        }
    }
    if (verifyGPCDataInteger('wllist') > 0) {
        if (isset($_SESSION['Kunde'], $_SESSION['Kunde']->kKunde) && (int)$_SESSION['Kunde']->kKunde > 0) {
            $step = 'wunschliste';
        }
    }
    if (verifyGPCDataInteger('bestellung') > 0) {
        //bestellung von diesem Kunden?
        $bestellung = new Bestellung(verifyGPCDataInteger('bestellung'));
        $bestellung->fuelleBestellung();

        if (isset($bestellung->kKunde, $_SESSION['Kunde']->kKunde) &&
            (int)$bestellung->kKunde > 0 &&
            $bestellung->kKunde == $_SESSION['Kunde']->kKunde
        ) {
            // Download wurde angefordert?
            if (verifyGPCDataInteger('dl') > 0) {
                if (class_exists('Download')) {
                    $nReturn = Download::getFile(
                        verifyGPCDataInteger('dl'),
                        $_SESSION['Kunde']->kKunde,
                        $bestellung->kBestellung
                    );
                    if ($nReturn !== 1) {
                        $cFehler = Download::mapGetFileErrorCode($nReturn);
                    }
                }
            }
            $step                               = 'bestellung';
            $_SESSION['Kunde']->angezeigtesLand = ISO2land($_SESSION['Kunde']->cLand);
            krsort($_SESSION['Kunde']->cKundenattribut_arr);
            $smarty->assign('Bestellung', $bestellung)
                   ->assign('Kunde', $bestellung->oRechnungsadresse)// Work Around Daten von trechnungsadresse
                   ->assign('customerAttribute_arr', $_SESSION['Kunde']->cKundenattribut_arr)
                   ->assign('Lieferadresse', (isset($bestellung->Lieferadresse) ? $bestellung->Lieferadresse : null));
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $smarty->assign('oTrustedShopsBewertenButton', gibTrustedShopsBewertenButton(
                    $bestellung->oRechnungsadresse->cMail,
                    $bestellung->cBestellNr
                ));
            }
            if (isset($bestellung->oEstimatedDelivery->longestMin, $bestellung->oEstimatedDelivery->longestMax)) {
                $smarty->assign(
                    'cEstimatedDeliveryEx',
                    dateAddWeekday($bestellung->dErstellt, $bestellung->oEstimatedDelivery->longestMin)->format('d.m.Y')
                    . ' - ' .
                    dateAddWeekday($bestellung->dErstellt, $bestellung->oEstimatedDelivery->longestMax)->format('d.m.Y')
                );
            }
        } else {
            $step = 'login';
        }
    }
    if (isset($_POST['del_acc']) && (int)$_POST['del_acc'] === 1) {
        $csrfTest = validateToken();
        if ($csrfTest === false) {
            $cHinweis .= Shop::Lang()->get('csrfValidationFailed');
            Jtllog::writeLog('CSRF-Warnung fuer Account-Loeschung und kKunde ' . (int)$_SESSION['Kunde']->kKunde);
        } else {
            $oBestellung = Shop::DB()->query(
                "SELECT COUNT(kBestellung) AS countBestellung
                    FROM tbestellung
                    WHERE cStatus NOT IN (" . BESTELLUNG_STATUS_VERSANDT . ", " . BESTELLUNG_STATUS_STORNO . ")
                        AND kKunde = " . (int)$_SESSION['Kunde']->kKunde, 1
            );

            if (isset($oBestellung->countBestellung) && $oBestellung->countBestellung == 0) {
                // Keine Bestellungen die noch nicht verschickt oder storniert wurden mehr vorhanden - die Kundendaten werden gelöscht
                $cText = utf8_decode('Der Kunde ' . $_SESSION['Kunde']->cVorname . ' ' .
                    $_SESSION['Kunde']->cNachname . ' (' . $_SESSION['Kunde']->kKunde . ') hat am ' . date('d.m.Y') .
                    ' um ' . date('H:m:i') . ' Uhr sein Kundenkonto gelöscht. Es gab keine offenen Bestellungen mehr');

                Shop::DB()->delete('tlieferadresse', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->delete('trechnungsadresse', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->delete('tkundenattribut', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->delete('tkunde', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
            } else {
                // Es gibt noch Bestellungen, die noch nicht versandt oder storniert wurden - der Account wird in einen Gastzugang umgewandelt
                $cText = utf8_decode('Der Kunde ' . $_SESSION['Kunde']->cVorname . ' ' .
                    substr($_SESSION['Kunde']->cNachname, 0, 1) .
                    '. (' . $_SESSION['Kunde']->kKunde . ') hat am ' . date('d.m.Y') .
                    ' um ' . date('H:m:i') . ' Uhr sein Kundenkonto gelöscht. Es gab noch ' .
                    $oBestellung->countBestellung . ' offene Bestellungen.' .
                    ' Der Account wurde deshalb in einen temporären Gastzugang umgewandelt.');

                Shop::DB()->update('tkunde', 'kKunde', (int)$_SESSION['Kunde']->kKunde, (object)[
                    'cPasswort'    => '',
                    'nRegistriert' => 0,
                ]);
            }

            Jtllog::writeLog($cText, JTLLOG_LEVEL_NOTICE, true);
            // Newsletter
            Shop::DB()->delete('tnewsletterempfaenger', 'cEmail', $_SESSION['Kunde']->cMail);
            Shop::DB()->insert('tnewsletterempfaengerhistory', (object)[
                'kSprache'     => (int)$_SESSION['Kunde']->kSprache,
                'kKunde'       => (int)$_SESSION['Kunde']->kKunde,
                'cAnrede'      => $_SESSION['Kunde']->cAnrede,
                'cVorname'     => $_SESSION['Kunde']->cVorname,
                'cNachname'    => $_SESSION['Kunde']->cNachname,
                'cEmail'       => $_SESSION['Kunde']->cMail,
                'cOptCode'     => '',
                'cLoeschCode'  => '',
                'cAktion'      => 'Geloescht',
                'dAusgetragen' => 'now()',
                'dEingetragen' => '',
                'dOptCode'     => '',
            ]);

            // Wunschliste
            Shop::DB()->query(
                "DELETE twunschliste, twunschlistepos, twunschlisteposeigenschaft, twunschlisteversand
                        FROM twunschliste
                        LEFT JOIN twunschlistepos
                            ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                        LEFT JOIN twunschlisteposeigenschaft
                            ON twunschlisteposeigenschaft.kWunschlistePos = twunschlistepos.kWunschlistePos
                        LEFT JOIN twunschlisteversand
                            ON twunschlisteversand.kWunschliste = twunschliste.kWunschliste
                        WHERE twunschliste.kKunde = " . (int)$_SESSION['Kunde']->kKunde, 4
            );

            // Pers. Warenkorb
            Shop::DB()->query(
                "DELETE twarenkorbpers, twarenkorbperspos, twarenkorbpersposeigenschaft
                    FROM twarenkorbpers
                    LEFT JOIN twarenkorbperspos 
                        ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
                    LEFT JOIN twarenkorbpersposeigenschaft 
                        ON twarenkorbpersposeigenschaft.kWarenkorbPersPos = twarenkorbperspos.kWarenkorbPersPos
                    WHERE twarenkorbpers.kKunde = " . (int)$_SESSION['Kunde']->kKunde, 4
            );

            sendeMail(MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT, (object)[
                'tkunde' => $_SESSION['Kunde']
            ]);

            executeHook(HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN);
            session_destroy();
            header('Location: ' . Shop::getURL(), true, 303);
            exit;
        }
    }

    if ($step === 'mein Konto' || $step === 'bestellungen') {
        $oDownload_arr = [];
        if (class_exists('Download')) {
            $oDownload_arr = Download::getDownloads(['kKunde' => $_SESSION['Kunde']->kKunde], Shop::getLanguage());
            $smarty->assign('oDownload_arr', $oDownload_arr);
        }
        // Download wurde angefordert?
        if (verifyGPCDataInteger('dl') > 0) {
            if (class_exists('Download')) {
                $nReturn = Download::getFile(
                    verifyGPCDataInteger('dl'),
                    $_SESSION['Kunde']->kKunde,
                    verifyGPCDataInteger('kBestellung')
                );
                if ($nReturn !== 1) {
                    $cFehler = Download::mapGetFileErrorCode($nReturn);
                }
            }
        }

        $Bestellungen = [];
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $Bestellungen = Shop::DB()->selectAll(
                'tbestellung', 'kKunde', (int)$_SESSION['Kunde']->kKunde,
                '*, date_format(dErstellt,\'%d.%m.%Y\') AS dBestelldatum', 'kBestellung DESC'
            );
            if (is_array($Bestellungen) && count($Bestellungen) > 0) {
                foreach ($Bestellungen as $i => $oBestellung) {
                    $Bestellungen[$i]->bDownload = false;
                    if (is_array($oDownload_arr) && count($oDownload_arr) > 0) {
                        foreach ($oDownload_arr as $oDownload) {
                            if ($oBestellung->kBestellung == $oDownload->kBestellung) {
                                $Bestellungen[$i]->bDownload = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $orderCount = count($Bestellungen);
        $currencies = [];
        for ($i = 0; $i < $orderCount; $i++) {
            if ($Bestellungen[$i]->kWaehrung > 0) {
                if (isset($currencies[(int)$Bestellungen[$i]->kWaehrung])) {
                    $Bestellungen[$i]->Waehrung = $currencies[(int)$Bestellungen[$i]->kWaehrung];
                } else {
                    $Bestellungen[$i]->Waehrung                    = Shop::DB()->select(
                        'twaehrung',
                        'kWaehrung',
                        (int)$Bestellungen[$i]->kWaehrung
                    );
                    $currencies[(int)$Bestellungen[$i]->kWaehrung] = $Bestellungen[$i]->Waehrung;
                }
                if (isset($Bestellungen[$i]->fWaehrungsFaktor, $Bestellungen[$i]->Waehrung->fFaktor) &&
                    $Bestellungen[$i]->fWaehrungsFaktor !== 1
                ) {
                    $Bestellungen[$i]->Waehrung->fFaktor = $Bestellungen[$i]->fWaehrungsFaktor;
                }
            }
            $Bestellungen[$i]->cBestellwertLocalized = gibPreisStringLocalized(
                $Bestellungen[$i]->fGesamtsumme,
                $Bestellungen[$i]->Waehrung
            );
            $Bestellungen[$i]->Status                = lang_bestellstatus($Bestellungen[$i]->cStatus);
        }

        $orderPagination = (new Pagination('orders'))
            ->setItemArray($Bestellungen)
            ->setItemsPerPage(10)
            ->assemble();

        $smarty
            ->assign('orderPagination', $orderPagination)
            ->assign('Bestellungen', $Bestellungen);
    }

    if ($step === 'mein Konto' || $step === 'wunschliste') {
        // Hole Wunschliste für eingeloggten Kunden
        $oWunschliste_arr = [];
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $oWunschliste_arr = Shop::DB()->selectAll(
                'twunschliste',
                'kKunde',
                (int)$_SESSION['Kunde']->kKunde,
                '*',
                'dErstellt DESC'
            );
        }
        // Pruefen, ob der Kunde Wunschlisten hat
        if (count($oWunschliste_arr) > 0) {
            $smarty->assign('oWunschliste_arr', $oWunschliste_arr);
        }
    }

    if ($step === 'mein Konto') {
        $Lieferadressen      = [];
        $oLieferdatenTMP_arr = Shop::DB()->selectAll(
            'tlieferadresse',
            'kKunde',
            (int)$_SESSION['Kunde']->kKunde,
            'kLieferadresse'
        );
        foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
            if ($oLieferdatenTMP->kLieferadresse > 0) {
                $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
            }
        }

        $smarty->assign('Lieferadressen', $Lieferadressen);

        executeHook(HOOK_JTL_PAGE_MEINKKONTO);
    }

    if ($step === 'rechnungsdaten') {
        $knd = $_SESSION['Kunde'];
        if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
            $knd                 = getKundendaten($_POST, 0, 0);
            $cKundenattribut_arr = getKundenattribute($_POST);
        } else {
            $cKundenattribut_arr = $knd->cKundenattribut_arr;
        }
        if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $knd->dGeburtstag)) {
            list($jahr, $monat, $tag) = explode('-', $knd->dGeburtstag);
            $knd->dGeburtstag         = $tag . '.' . $monat . '.' . $jahr;
        }
        $smarty->assign('Kunde', $knd)
               ->assign('cKundenattribut_arr', $cKundenattribut_arr)
               ->assign('Einstellungen', $Einstellungen)
               ->assign('laender', gibBelieferbareLaender($_SESSION['Kunde']->kKundengruppe, false, true));
        // selbstdef. Kundenfelder
        $oKundenfeld_arr = Shop::DB()->selectAll('tkundenfeld', 'kSprache', Shop::getLanguage(), '*', 'nSort DESC');
        if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
            // tkundenfeldwert nachschauen ob dort Werte für tkundenfeld enthalten sind
            foreach ($oKundenfeld_arr as $i => $oKundenfeld) {
                if ($oKundenfeld->cTyp === 'auswahl') {
                    $oKundenfeld_arr[$i]->oKundenfeldWert_arr = Shop::DB()->selectAll(
                        'tkundenfeldwert',
                        'kKundenfeld',
                        (int)$oKundenfeld->kKundenfeld
                    );
                }
            }
        }

        $smarty->assign('oKundenfeld_arr', $oKundenfeld_arr);
    }


    if (isset($_SESSION['Kunde']->kKunde) && (int)$_SESSION['Kunde']->kKunde > 0) {
        $_SESSION['Kunde']->cGuthabenLocalized = gibPreisStringLocalized($_SESSION['Kunde']->fGuthaben);
        krsort($_SESSION['Kunde']->cKundenattribut_arr);
        $smarty->assign('Kunde', $_SESSION['Kunde'])
               ->assign('customerAttribute_arr', $_SESSION['Kunde']->cKundenattribut_arr);
    }
}
if (empty($cBrotNavi)) {
    $cBrotNavi = createNavigation($AktuelleSeite);
}
// Canonical
$cCanonicalURL = $linkHelper->getStaticRoute('jtl.php', true);
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_LOGIN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('hinweis', $cHinweis)
       ->assign('step', $step)
       ->assign('Navigation', $cBrotNavi)
       ->assign('requestURL', isset($requestURL) ? $requestURL : null)
       ->assign('Einstellungen', $Einstellungen)
       ->assign('BESTELLUNG_STATUS_BEZAHLT', BESTELLUNG_STATUS_BEZAHLT)
       ->assign('BESTELLUNG_STATUS_VERSANDT', BESTELLUNG_STATUS_VERSANDT)
       ->assign('BESTELLUNG_STATUS_OFFEN', BESTELLUNG_STATUS_OFFEN)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_KUNDENDATENEDITIEREN);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_JTL_PAGE);

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
