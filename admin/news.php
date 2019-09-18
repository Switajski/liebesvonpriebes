<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global JTLSmarty $smarty
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

$oAccount->permission('CONTENT_NEWS_SYSTEM_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$Einstellungen         = Shop::getSettings([CONF_NEWS]);
$cHinweis              = '';
$cFehler               = '';
$step                  = 'news_uebersicht';
$cUploadVerzeichnis    = PFAD_ROOT . PFAD_NEWSBILDER;
$cUploadVerzeichnisKat = PFAD_ROOT . PFAD_NEWSKATEGORIEBILDER;
$oNewsKategorie_arr    = [];
$continueWith          = false;
setzeSprache();

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $backTab  = verifyGPDataString('tab');
    $smarty->assign('cTab', $backTab);

    switch ($backTab) {
        case 'inaktiv':
            if (verifyGPCDataInteger('s1') > 1) {
                $smarty->assign('cBackPage', 'tab=inaktiv&s1=' . verifyGPCDataInteger('s1'))
                       ->assign('cSeite', verifyGPCDataInteger('s1'));
            }
            break;
        case 'aktiv':
            if (verifyGPCDataInteger('s2') > 1) {
                $smarty->assign('cBackPage', 'tab=aktiv&s2=' . verifyGPCDataInteger('s2'))
                       ->assign('cSeite', verifyGPCDataInteger('s2'));
            }
            break;
        case 'kategorien':
            if (verifyGPCDataInteger('s3') > 1) {
                $smarty->assign('cBackPage', 'tab=kategorien&s3=' . verifyGPCDataInteger('s3'))
                       ->assign('cSeite', verifyGPCDataInteger('s3'));
            }
            break;
    }
}
$Sprachen     = gibAlleSprachen();
$oSpracheNews = Shop::Lang()->getIsoFromLangID($_SESSION['kSprache']);
if (!$oSpracheNews) {
    $oSpracheNews = Shop::DB()->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
}
// News
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0 && validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NEWS, $_POST, [CACHING_GROUP_OPTION, CACHING_GROUP_NEWS]);
    if (count($Sprachen) > 0) {
        // tnewsmonatspraefix loeschen
        Shop::DB()->query("TRUNCATE tnewsmonatspraefix", 3);

        foreach ($Sprachen as $oSpracheTMP) {
            $oNewsMonatsPraefix           = new stdClass();
            $oNewsMonatsPraefix->kSprache = $oSpracheTMP->kSprache;
            if (strlen($_POST['praefix_' . $oSpracheTMP->cISO]) > 0) {
                $oNewsMonatsPraefix->cPraefix = htmlspecialchars($_POST['praefix_' . $oSpracheTMP->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            } else {
                $oNewsMonatsPraefix->cPraefix = ($oSpracheTMP->cISO === 'ger')
                    ? 'Newsuebersicht'
                    : 'Newsoverview';
            }
            Shop::DB()->insert('tnewsmonatspraefix', $oNewsMonatsPraefix);
        }
    }
}

if (verifyGPCDataInteger('news') === 1 && validateToken()) {
    // Neue News erstellen
    if ((isset($_POST['erstellen']) && (int)$_POST['erstellen'] === 1 && isset($_POST['news_erstellen'])) ||
        (isset($_POST['news_erstellen']) && (int)$_POST['news_erstellen'] === 1)) {
        $oNewsKategorie_arr = holeNewskategorie($_SESSION['kSprache']);
        // News erstellen, $oNewsKategorie_arr leer = Fehler ausgeben
        if (count($oNewsKategorie_arr) > 0) {
            $step = 'news_erstellen';
            $smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                   ->assign('oPossibleAuthors_arr', ContentAuthor::getInstance()->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step = 'news_uebersicht';
        }
    } elseif ((isset($_POST['erstellen']) && (int)$_POST['erstellen'] === 1 && isset($_POST['news_kategorie_erstellen'])) ||
        (isset($_POST['news_kategorie_erstellen']) && (int)$_POST['news_kategorie_erstellen'] === 1)) {
        $step = 'news_kategorie_erstellen';
    } elseif (verifyGPCDataInteger('nkedit') === 1) { // Newskommentar editieren
        if (verifyGPCDataInteger('kNews') > 0) {
            if (isset($_POST['newskommentarsavesubmit'])) {
                if (speicherNewsKommentar(verifyGPCDataInteger('kNewsKommentar'), $_POST)) {
                    $step = 'news_vorschau';
                    $cHinweis .= 'Der Newskommentar wurde erfolgreich editiert.<br />';

                    if (verifyGPCDataInteger('nFZ') === 1) {
                        header('Location: freischalten.php');
                        exit();
                    } else {
                        $tab = verifyGPDataString('tab');
                        if ($tab === 'aktiv') {
                            newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis, [
                                'news'  => '1',
                                'nd'    => '1',
                                'kNews' => verifyGPCDataInteger('kNews'),
                                'token' => $_SESSION['jtl_token'],
                            ]);
                        } else {
                            newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis);
                        }
                    }
                } else {
                    $step = 'news_kommentar_editieren';
                    $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben.<br />';
                    $oNewsKommentar                 = new stdClass();
                    $oNewsKommentar->kNewsKommentar = $_POST['kNewsKommentar'];
                    $oNewsKommentar->kNews          = $_POST['kNews'];
                    $oNewsKommentar->cName          = $_POST['cName'];
                    $oNewsKommentar->cKommentar     = $_POST['cKommentar'];
                    $smarty->assign('oNewsKommentar', $oNewsKommentar);
                }
            } else {
                $step = 'news_kommentar_editieren';
                $smarty->assign('oNewsKommentar', Shop::DB()->select(
                    'tnewskommentar',
                    'kNewsKommentar',
                    verifyGPCDataInteger('kNewsKommentar'))
                );
                if (verifyGPCDataInteger('nFZ') === 1) {
                    $smarty->assign('nFZ', 1);
                }
            }
        }
    } elseif (isset($_POST['news_speichern']) && (int)$_POST['news_speichern'] === 1) { // News speichern
        $kKundengruppe_arr  = (isset($_POST['kKundengruppe']) ? $_POST['kKundengruppe'] : null);
        $kNewsKategorie_arr = (isset($_POST['kNewsKategorie']) ? $_POST['kNewsKategorie'] : null);
        $cBetreff           = $_POST['betreff'];
        $cSeo               = $_POST['seo'];
        $cText              = $_POST['text'];
        $cVorschauText      = $_POST['cVorschauText'];
        $nAktiv             = (int)$_POST['nAktiv'];
        $cMetaTitle         = $_POST['cMetaTitle'];
        $cMetaDescription   = $_POST['cMetaDescription'];
        $cMetaKeywords      = $_POST['cMetaKeywords'];
        $dGueltigVon        = $_POST['dGueltigVon'];
        $cPreviewImage      = $_POST['previewImage'];
        $kAuthor            = (int)$_POST['kAuthor'];
        //$dGueltigBis      = $_POST['dGueltigBis'];

        $cPlausiValue_arr = pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr);

        if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) === 0) {
            $oNews                   = new stdClass();
            $oNews->kSprache         = $_SESSION['kSprache'];
            $oNews->cKundengruppe    = ';' . implode(';', $kKundengruppe_arr) . ';';
            $oNews->cBetreff         = htmlspecialchars($cBetreff, ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oNews->cText            = $cText;
            $oNews->cVorschauText    = $cVorschauText;
            $oNews->nAktiv           = $nAktiv;
            $oNews->cMetaTitle       = htmlspecialchars($cMetaTitle, ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oNews->cMetaDescription = htmlspecialchars($cMetaDescription, ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oNews->cMetaKeywords    = htmlspecialchars($cMetaKeywords, ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oNews->dErstellt        = (new DateTime())->format('Y-m-d H:i:s');
            $oNews->dGueltigVon      = DateTime::createFromFormat('d.m.Y H:i', $dGueltigVon)->format('Y-m-d H:i:00');
            $oNews->cPreviewImage    = $cPreviewImage;

            $nNewsOld = 0;
            if (isset($_POST['news_edit_speichern']) && (int)$_POST['news_edit_speichern'] === 1) {
                $nNewsOld = 1;
                $kNews    = (int)$_POST['kNews'];
                $revision = new Revision();
                $revision->addRevision('news', $kNews);
                Shop::DB()->delete('tnews', 'kNews', $kNews);
                // tseo loeschen
                Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kNews', $kNews]);
            }
            $oNews->cSeo = (strlen($cSeo) > 0) ? checkSeo(getSeo($cSeo)) : checkSeo(getSeo($cBetreff));
            if (isset($kNews) && $kNews > 0) {
                $oNews->kNews = $kNews;
                Shop::DB()->insert('tnews', $oNews);
            } else {
                $kNews = Shop::DB()->insert('tnews', $oNews);
            }
            if ($kAuthor > 0) {
                ContentAuthor::getInstance()->setAuthor('NEWS', $kNews, $kAuthor);
            } else {
                ContentAuthor::getInstance()->clearAuthor('NEWS', $kNews);
            }
            $kNews           = (int)$kNews;
            $oAlteBilder_arr = [];
            // Bilder hochladen
            if (!is_dir($cUploadVerzeichnis . $kNews)) {
                mkdir($cUploadVerzeichnis . $kNews);
            } else {
                $oAlteBilder_arr = holeNewsBilder($oNews->kNews, $cUploadVerzeichnis);
            }
            if (isset($_FILES['previewImage']['name']) && strlen($_FILES['previewImage']['name']) > 0) {
                $extension = substr(
                    $_FILES['previewImage']['type'],
                    strpos($_FILES['previewImage']['type'], '/') + 1,
                    strlen($_FILES['previewImage']['type'] - strpos($_FILES['previewImage']['type'], '/')) + 1
                );
                //not elegant, but since it's 99% jpg..
                if ($extension === 'jpe') {
                    $extension = 'jpg';
                }
                //check if preview exists and delete
                foreach ($oAlteBilder_arr as $oBild) {
                    if (strpos($oBild->cDatei, 'preview') !== false) {
                        loescheNewsBild($oBild->cName, $kNews, $cUploadVerzeichnis);
                    }
                }
                $cUploadDatei = $cUploadVerzeichnis . $kNews . '/preview.' . $extension;
                move_uploaded_file($_FILES['previewImage']['tmp_name'], $cUploadDatei);
                $oNews->cPreviewImage = PFAD_NEWSBILDER . $kNews . '/preview.' . $extension;
            }
            if (is_array($_FILES['Bilder']['name']) && count($_FILES['Bilder']['name']) > 0) {
                $nLetztesBild = gibLetzteBildNummer($kNews);
                $nZaehler     = 0;
                if ($nLetztesBild > 0) {
                    $nZaehler = $nLetztesBild;
                }

                for ($i = $nZaehler; $i < (count($_FILES['Bilder']['name']) + $nZaehler); $i++) {
                    $extension = substr(
                        $_FILES['Bilder']['type'][$i - $nZaehler],
                        strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/') + 1,
                        strlen($_FILES['Bilder']['type'][$i - $nZaehler] -
                            strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/')) + 1
                    );
                    //not elegant, but since it's 99% jpg..
                    if ($extension === 'jpe') {
                        $extension = 'jpg';
                    }
                    //check if image exists and delete
                    foreach ($oAlteBilder_arr as $oBild) {
                        if (strpos($oBild->cDatei, 'Bild' . ($i + 1) . '.') !== false &&
                            $_FILES['Bilder']['name'][$i - $nZaehler] != '') {
                            loescheNewsBild($oBild->cName, $kNews, $cUploadVerzeichnis);
                        }
                    }
                    $cUploadDatei = $cUploadVerzeichnis . $kNews . '/Bild' . ($i + 1) . '.' . $extension;
                    move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
                }
            }
            $upd                = new stdClass();
            $upd->cText         = parseText($cText, $kNews);
            $upd->cVorschauText = parseText($cVorschauText, $kNews);
            $upd->cPreviewImage = $oNews->cPreviewImage;
            Shop::DB()->update('tnews', 'kNews', $kNews, $upd);
            Shop::DB()->delete('tseo', ['cKey', 'kKey', 'kSprache'], ['kNews', $kNews, (int)$_SESSION['kSprache']]);
            // SEO tseo eintragen
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $oNews->cSeo;
            $oSeo->cKey     = 'kNews';
            $oSeo->kKey     = $kNews;
            $oSeo->kSprache = $_SESSION['kSprache'];
            Shop::DB()->insert('tseo', $oSeo);
            // tnewskategorienews fuer aktuelle news loeschen
            Shop::DB()->delete('tnewskategorienews', 'kNews', $kNews);
            // tnewskategorienews eintragen
            foreach ($kNewsKategorie_arr as $kNewsKategorie) {
                $oNewsKategorieNews                 = new stdClass();
                $oNewsKategorieNews->kNews          = $kNews;
                $oNewsKategorieNews->kNewsKategorie = (int)$kNewsKategorie;
                Shop::DB()->insert('tnewskategorienews', $oNewsKategorieNews);
            }
            // tnewsmonatsuebersicht updaten
            if ($nAktiv === 1) {
                $oDatum = DateTime::createFromFormat('Y-m-d H:i:s', $oNews->dGueltigVon);
                $dMonat = (int)$oDatum->format('m');
                $dJahr = (int)$oDatum->format('Y');

                $oNewsMonatsUebersicht = Shop::DB()->select(
                    'tnewsmonatsuebersicht',
                    'kSprache',
                    (int)$_SESSION['kSprache'],
                    'nMonat',
                    (int)$dMonat,
                    'nJahr',
                    (int)$dJahr
                );
                // Falls dies die erste News des Monats ist, neuen Eintrag in tnewsmonatsuebersicht, ansonsten updaten
                if (isset($oNewsMonatsUebersicht->kNewsMonatsUebersicht) && $oNewsMonatsUebersicht->kNewsMonatsUebersicht > 0) {
                    unset($oNewsMonatsPraefix);
                    $oNewsMonatsPraefix = Shop::DB()->select('tnewsmonatspraefix', 'kSprache', (int)$_SESSION['kSprache']);
                    if (empty($oNewsMonatsPraefix->cPraefix)) {
                        $oNewsMonatsPraefix->cPraefix = 'Newsuebersicht';
                    }
                    Shop::DB()->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', (int)$oNewsMonatsUebersicht->kNewsMonatsUebersicht, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = checkSeo(getSeo($oNewsMonatsPraefix->cPraefix . '-' . (string)$dMonat . '-' . $dJahr));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $oNewsMonatsUebersicht->kNewsMonatsUebersicht;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    Shop::DB()->insert('tseo', $oSeo);
                } else {
                    $oNewsMonatsPraefix = new stdClass();
                    $oNewsMonatsPraefix = Shop::DB()->select('tnewsmonatspraefix', 'kSprache', (int)$_SESSION['kSprache']);
                    if (empty($oNewsMonatsPraefix->cPraefix)) {
                        $oNewsMonatsPraefix->cPraefix = 'Newsuebersicht';
                    }
                    $oNewsMonatsUebersichtTMP           = new stdClass();
                    $oNewsMonatsUebersichtTMP->kSprache = (int)$_SESSION['kSprache'];
                    $oNewsMonatsUebersichtTMP->cName    = mappeDatumName((string)$dMonat, $dJahr, $oSpracheNews->cISO);
                    $oNewsMonatsUebersichtTMP->nMonat   = (int)$dMonat;
                    $oNewsMonatsUebersichtTMP->nJahr    = (int)$dJahr;

                    $kNewsMonatsUebersicht = Shop::DB()->insert('tnewsmonatsuebersicht', $oNewsMonatsUebersichtTMP);

                    Shop::DB()->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', (int)$kNewsMonatsUebersicht, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = checkSeo(getSeo($oNewsMonatsPraefix->cPraefix . '-' . (string)$dMonat . '-' . $dJahr));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = (int)$kNewsMonatsUebersicht;
                    $oSeo->kSprache = (int)$_SESSION['kSprache'];
                    Shop::DB()->insert('tseo', $oSeo);
                }
            }
            $cHinweis .= 'Ihre News wurde erfolgreich gespeichert.<br />';
            if (isset($_POST['continue']) && $_POST['continue'] === '1') {
                $step         = 'news_editieren';
                $continueWith = (int)$kNews;
            } else {
                $tab = verifyGPDataString('tab');
                newsRedirect(empty($tab) ? 'aktiv' : $tab, $cHinweis);
            }
        } else {
            $step = 'news_editieren';
            $smarty->assign('cPostVar_arr', $_POST)
                   ->assign('cPlausiValue_arr', $cPlausiValue_arr);
            $cFehler .= 'Fehler: Bitte f&uuml;llen Sie alle Pflichtfelder aus.<br />';

            if (isset($_POST['kNews']) && is_numeric($_POST['kNews'])) {
                $continueWith = (int)$_POST['kNews'];
            } else {
                $oNewsKategorie_arr = holeNewskategorie($_SESSION['kSprache']);
                $smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                       ->assign('oPossibleAuthors_arr', ContentAuthor::getInstance()->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            }
        }
    } elseif (isset($_POST['news_loeschen']) && (int)$_POST['news_loeschen'] === 1) { // News loeschen
        if (is_array($_POST['kNews']) && count($_POST['kNews']) > 0) {
            foreach ($_POST['kNews'] as $kNews) {
                $kNews = (int)$kNews;

                if ($kNews > 0) {
                    ContentAuthor::getInstance()->clearAuthor('NEWS', $kNews);
                    $oNewsTMP = Shop::DB()->select('tnews', 'kNews', $kNews);
                    Shop::DB()->delete('tnews', 'kNews', $kNews);
                    // Bilderverzeichnis loeschen
                    loescheNewsBilderDir($kNews, $cUploadVerzeichnis);
                    // Kommentare loeschen
                    Shop::DB()->delete('tnewskommentar', 'kNews', $kNews);
                    // tseo loeschen
                    Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kNews', $kNews]);
                    // tnewskategorienews loeschen
                    Shop::DB()->delete('tnewskategorienews', 'kNews', $kNews);
                    // War das die letzte News fuer einen bestimmten Monat?
                    // => Falls ja, tnewsmonatsuebersicht Monat loeschen
                    $oDatum = DateTime::createFromFormat('Y-m-d H:i:s', $oNewsTMP->dGueltigVon);
                    $dMonat = (int)$oDatum->format('m');
                    $dJahr = (int)$oDatum->format('Y');
                    $kSpracheTMP  = (int)$oNewsTMP->kSprache;
                    $oNewsTMP_arr = Shop::DB()->query(
                        "SELECT kNews
                            FROM tnews
                            WHERE month(dGueltigVon) = " . $dMonat . "
                                AND year(dGueltigVon) = " . $dJahr . "
                                AND kSprache = " . $kSpracheTMP, 2
                    );
                    if (is_array($oNewsTMP_arr) && count($oNewsTMP_arr) === 0) {
                        Shop::DB()->query(
                            "DELETE tnewsmonatsuebersicht, tseo FROM tnewsmonatsuebersicht
                                LEFT JOIN tseo 
                                    ON tseo.cKey = 'kNewsMonatsUebersicht'
                                    AND tseo.kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                                    AND tseo.kSprache = tnewsmonatsuebersicht.kSprache
                                WHERE tnewsmonatsuebersicht.nMonat = " . $dMonat . "
                                    AND tnewsmonatsuebersicht.nJahr = " . $dJahr . "
                                    AND tnewsmonatsuebersicht.kSprache = " . $kSpracheTMP, 4
                        );
                    }
                }
            }

            $cHinweis .= 'Ihre markierten News wurden erfolgreich gel&ouml;scht.<br />';
            newsRedirect('aktiv', $cHinweis);
        } else {
            $cFehler .= 'Fehler: Sie m&uuml;ssen mindestens eine News ausgew&auml;hlt haben.<br />';
        }
    } elseif (isset($_POST['news_kategorie_speichern']) && (int)$_POST['news_kategorie_speichern'] === 1) {
        //Newskategorie speichern
        $step             = 'news_uebersicht';
        $cName            = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $cSeo             = $_POST['cSeo'];
        $nSort            = (int)$_POST['nSort'];
        $nAktiv           = (int)$_POST['nAktiv'];
        $cMetaTitle       = htmlspecialchars($_POST['cMetaTitle'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $cMetaDescription = htmlspecialchars($_POST['cMetaDescription'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $cBeschreibung    = $_POST['cBeschreibung'];
        $cPreviewImage    = $_POST['previewImage'];
        $cPlausiValue_arr = pruefeNewsKategorie($_POST['cName'], isset($_POST['newskategorie_edit_speichern'])
            ? (int)$_POST['newskategorie_edit_speichern']
            : 0
        );
        if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) === 0) {
            $kNewsKategorie = 0;

            if (isset($_POST['newskategorie_edit_speichern'], $_POST['kNewsKategorie']) &&
                (int)$_POST['newskategorie_edit_speichern'] === 1 && (int)$_POST['kNewsKategorie'] > 0
            ) {
                $kNewsKategorie = (int)$_POST['kNewsKategorie'];
                Shop::DB()->delete('tnewskategorie', 'kNewsKategorie', $kNewsKategorie);
                // tseo loeschen
                Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $kNewsKategorie]);
            }
            $oNewsKategorie                        = new stdClass();
            $oNewsKategorie->kSprache              = (int)$_SESSION['kSprache'];
            $oNewsKategorie->cName                 = $cName;
            $oNewsKategorie->cBeschreibung         = $cBeschreibung;
            $oNewsKategorie->nSort                 = ((int)$nSort > -1) ? (int)$nSort : 0;
            $oNewsKategorie->nAktiv                = $nAktiv;
            $oNewsKategorie->cMetaTitle            = $cMetaTitle;
            $oNewsKategorie->cMetaDescription      = $cMetaDescription;
            $oNewsKategorie->dLetzteAktualisierung = (new DateTime())->format('Y-m-d H:i:s');
            $oNewsKategorie->cSeo                  = (strlen($cSeo) > 0)
                ? checkSeo(getSeo($cSeo))
                : checkSeo(getSeo($cName));
            $oNewsKategorie->cPreviewImage         = $cPreviewImage;

            if ($kNewsKategorie > 0) {
                $oNewsKategorie->kNewsKategorie = $kNewsKategorie;
                Shop::DB()->insert('tnewskategorie', $oNewsKategorie);
            } else {
                $kNewsKategorie = Shop::DB()->insert('tnewskategorie', $oNewsKategorie);
            }
            Shop::DB()->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kNewsKategorie', (int)$kNewsKategorie, (int)$oNewsKategorie->kSprache]
            );
            // SEO tseo eintragen
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $oNewsKategorie->cSeo;
            $oSeo->cKey     = 'kNewsKategorie';
            $oSeo->kKey     = $kNewsKategorie;
            $oSeo->kSprache = $oNewsKategorie->kSprache;
            Shop::DB()->insert('tseo', $oSeo);
            // Vorschaubild hochladen
            if (!is_dir($cUploadVerzeichnisKat . $kNewsKategorie)) {
                mkdir($cUploadVerzeichnisKat . $kNewsKategorie, 0777, true);
            }
            if (isset($_FILES['previewImage']['name']) && strlen($_FILES['previewImage']['name']) > 0) {
                $extension = substr(
                    $_FILES['previewImage']['type'],
                    strpos($_FILES['previewImage']['type'], '/') + 1,
                    strlen($_FILES['previewImage']['type'] - strpos($_FILES['previewImage']['type'], '/')) + 1
                );
                //not elegant, but since it's 99% jpg..
                if ($extension === 'jpe') {
                    $extension = 'jpg';
                }
                $cUploadDatei = $cUploadVerzeichnisKat . $kNewsKategorie . '/preview.' . $extension;
                move_uploaded_file($_FILES['previewImage']['tmp_name'], $cUploadDatei);
                $oNewsKategorie->cPreviewImage = PFAD_NEWSKATEGORIEBILDER . $kNewsKategorie . '/preview.' . $extension;
                $upd = new stdClass();
                $upd->cPreviewImage = $oNewsKategorie->cPreviewImage;
                Shop::DB()->update('tnewskategorie', 'kNewsKategorie', $kNewsKategorie, $upd);
            }

            $cHinweis .= 'Ihre Newskategorie "' . $cName . '" wurde erfolgreich eingetragen.<br />';
            newsRedirect('kategorien', $cHinweis);
        } else {
            $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben.<br />';
            $step = 'news_kategorie_erstellen';

            $oNewsKategorie = editiereNewskategorie(verifyGPCDataInteger('kNewsKategorie'), $_SESSION['kSprache']);

            if (isset($oNewsKategorie->kNewsKategorie) && (int)$oNewsKategorie->kNewsKategorie > 0) {
                $smarty->assign('oNewsKategorie', $oNewsKategorie);
            } else {
                $step = 'news_uebersicht';
                $cFehler .= 'Fehler: Die Newskategorie mit der ID "' . verifyGPCDataInteger('kNewsKategorie') .
                    '" konnte nicht gefunden werden.<br />';
            }

            $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                   ->assign('cPostVar_arr', $_POST);
        }
    } elseif (isset($_POST['news_kategorie_loeschen']) && (int)$_POST['news_kategorie_loeschen'] === 1) {
        // Newskategorie loeschen
        $step = 'news_uebersicht';
        if (loescheNewsKategorie($_POST['kNewsKategorie'])) {
            $cHinweis .= 'Ihre markierten Newskategorien wurden erfolgreich gel&ouml;scht.<br />';
            newsRedirect('kategorien', $cHinweis);
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Newskategorie.<br />';
        }
    } elseif (isset($_GET['newskategorie_editieren']) && (int)$_GET['newskategorie_editieren'] === 1) {
        // Newskategorie editieren
        // Soll Preview geloescht werden?
        $kNewsKategorie = (int) $_GET['kNewsKategorie'];
        if (strlen(verifyGPDataString('delpic')) > 0) {
            if (loescheNewsBild(verifyGPDataString('delpic'), $kNewsKategorie, $cUploadVerzeichnisKat)) {
                $cHinweis .= 'Ihr ausgew&auml;hltes Newsbild wurde erfolgreich gel&ouml;scht.';
            } else {
                $cFehler .= 'Fehler: Ihr ausgew&auml;hltes Newsbild konnte nicht gel&ouml;scht werden.';
            }
        }
        if (isset($_GET['kNewsKategorie']) && (int)$_GET['kNewsKategorie'] > 0) {
            $step           = 'news_kategorie_erstellen';
            $oNewsKategorie = editiereNewskategorie($_GET['kNewsKategorie'], $_SESSION['kSprache']);
            if (isset($oNewsKategorie->kNewsKategorie) && (int)$oNewsKategorie->kNewsKategorie > 0) {
                $smarty->assign('oNewsKategorie', $oNewsKategorie);
                // Hole Bilder
                if (is_dir($cUploadVerzeichnisKat . $oNewsKategorie->kNewsKategorie)) {
                    $smarty->assign('oDatei_arr', holeNewsKategorieBilder($oNewsKategorie->kNewsKategorie, $cUploadVerzeichnisKat));
                }
            } else {
                $step = 'news_uebersicht';
                $cFehler .= 'Fehler: Die Newskategorie mit der ID "' . (int)$_GET['kNewsKategorie'] .
                    '" konnte nicht gefunden werden.<br />';
            }
        }
    } elseif (isset($_POST['newskommentar_freischalten']) &&
        (int)$_POST['newskommentar_freischalten'] &&
        !isset($_POST['kommentareloeschenSubmit'])
    ) { // Kommentare freischalten
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                $kNewsKommentar = (int)$kNewsKommentar;
                $upd            = new stdClass();
                $upd->nAktiv    = 1;
                Shop::DB()->update('tnewskommentar', 'kNewsKommentar', $kNewsKommentar, $upd);
            }
            $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich freigeschaltet.<br />';
            $tab = verifyGPDataString('tab');
            newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis);
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
        }
    } elseif (isset($_POST['newskommentar_freischalten'], $_POST['kommentareloeschenSubmit'])) {
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                Shop::DB()->delete('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar);
            }

            $cHinweis .= 'Ihre markierten Kommentare wurden erfolgreich gel&ouml;scht.<br />';
            $tab = verifyGPDataString('tab');
            newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis);
        } else {
            $cFehler .= 'Fehler: Sie m&uuml;ssen mindestens einen Kommentar markieren.<br />';
        }
    }
    if ((isset($_GET['news_editieren']) && (int)$_GET['news_editieren'] === 1) ||
        ($continueWith !== false && $continueWith > 0)) {
        // News editieren
        $oNewsKategorie_arr = holeNewskategorie($_SESSION['kSprache']);
        $kNews              = ($continueWith !== false && $continueWith > 0)
            ? $continueWith
            : (int)$_GET['kNews'];
        // Sollen einzelne Newsbilder geloescht werden?
        if (strlen(verifyGPDataString('delpic')) > 0) {
            if (loescheNewsBild(verifyGPDataString('delpic'), $kNews, $cUploadVerzeichnis)) {
                $cHinweis .= 'Ihr ausgew&auml;hltes Newsbild wurde erfolgreich gel&ouml;scht.';
            } else {
                $cFehler .= 'Fehler: Ihr ausgew&auml;hltes Newsbild konnte nicht gel&ouml;scht werden.';
            }
        }

        if ($kNews > 0 && count($oNewsKategorie_arr) > 0) {
            $smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                   ->assign('oAuthor', ContentAuthor::getInstance()->getAuthor('NEWS', $kNews))
                   ->assign('oPossibleAuthors_arr', ContentAuthor::getInstance()->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            $step  = 'news_editieren';
            $oNews = Shop::DB()->query(
                "SELECT DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y %H:%i') AS Datum, 
                    DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de,
                    tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
                    tnews.cVorschauText, tnews.cMetaTitle, tnews.cMetaDescription, tnews.cMetaKeywords, 
                    tnews.nAktiv, tnews.dErstellt, tseo.cSeo, tnews.cPreviewImage
                    FROM tnews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE kNews = " . $kNews, 1
            );

            if (!empty($oNews->kNews)) {
                $oNews->kKundengruppe_arr = gibKeyArrayFuerKeyString($oNews->cKundengruppe, ';');
                // Hole Bilder
                if (is_dir($cUploadVerzeichnis . $oNews->kNews)) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($oNews->kNews, $cUploadVerzeichnis));
                }
                // NewskategorieNews
                $oNewsKategorieNews_arr = Shop::DB()->query(
                    "SELECT DISTINCT(kNewsKategorie)
                        FROM tnewskategorienews
                        WHERE kNews = " . (int)$oNews->kNews, 2
                );

                $smarty->assign('oNewsKategorieNews_arr', $oNewsKategorieNews_arr)
                       ->assign('oNews', $oNews);
            }
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step = 'news_uebersicht';
        }
    }

    // News Vorschau
    if (verifyGPCDataInteger('nd') === 1 || $step === 'news_vorschau') {
        if (verifyGPCDataInteger('kNews')) {
            $step  = 'news_vorschau';
            $kNews = verifyGPCDataInteger('kNews');
            $oNews = Shop::DB()->query(
                "SELECT DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y %H:%i') AS Datum, 
                    DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de,
                    tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
                    tnews.cVorschauText, tnews.cMetaTitle, tnews.cMetaDescription, 
                    tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo
                    FROM tnews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE kNews = " . $kNews, 1
            );

            if ($oNews->kNews > 0) {
                $oNews->kKundengruppe_arr = gibKeyArrayFuerKeyString($oNews->cKundengruppe, ';');

                if (is_dir($cUploadVerzeichnis . $oNews->kNews)) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($oNews->kNews, $cUploadVerzeichnis));
                }
                $smarty->assign('oNews', $oNews);
                // Kommentare loeschen
                if ((isset($_POST['kommentare_loeschen']) && (int)$_POST['kommentare_loeschen'] === 1) ||
                    isset($_POST['kommentareloeschenSubmit'])) {
                    if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
                        foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                            Shop::DB()->delete('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar);
                        }

                        $cHinweis .= "Ihre markierten Kommentare wurden erfolgreich gel&ouml;scht.<br />";
                        $tab = verifyGPDataString('tab');
                        newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis, [
                            'news'  => '1',
                            'nd'    => '1',
                            'kNews' => $oNews->kNews,
                            'token' => $_SESSION['jtl_token'],
                        ]);
                    } else {
                        $cFehler .= "Fehler: Sie m&uuml;ssen mindestens einen Kommentar markieren.<br />";
                    }
                }
                // Newskommentare
                $oNewsKommentar_arr = Shop::DB()->query(
                    "SELECT tnewskommentar.*, tkunde.kKunde, tkunde.cVorname, tkunde.cNachname, 
                        DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
                        FROM tnewskommentar
                        JOIN tnews 
                            ON tnews.kNews = tnewskommentar.kNews
                        LEFT JOIN tkunde 
                            ON tkunde.kKunde = tnewskommentar.kKunde
                        WHERE tnewskommentar.nAktiv = 1
                            AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
                            AND tnewskommentar.kNews = " . (int)$oNews->kNews, 2
                );

                if (is_array($oNewsKommentar_arr) && count($oNewsKommentar_arr) > 0) {
                    foreach ($oNewsKommentar_arr as $i => $oNewsKommentar) {
                        $oKunde = new Kunde($oNewsKommentar->kKunde);

                        $oNewsKommentar_arr[$i]->cNachname = $oKunde->cNachname;
                    }
                }

                $smarty->assign('oNewsKommentar_arr', $oNewsKommentar_arr);
            }
        }
    }
    Shop::Cache()->flushTags([CACHING_GROUP_NEWS]);
}
// Hole News aus DB
if ($step === 'news_uebersicht') {
    $oNews_arr = Shop::DB()->query(
        "SELECT SQL_CALC_FOUND_ROWS tnews.*, 
            count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl,
            DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y %H:%i') AS Datum, 
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            LEFT JOIN tnewskommentar 
                ON tnewskommentar.kNews = tnews.kNews
            WHERE tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
            GROUP BY tnews.kNews
            ORDER BY tnews.dGueltigVon DESC", 2
    );
    $oNewsAnzahl = Shop::DB()->query(
        "SELECT FOUND_ROWS() AS nAnzahl", 1
    );

    if (is_array($oNews_arr) && count($oNews_arr) > 0) {
        foreach ($oNews_arr as $i => $oNews) {
            $oNews_arr[$i]->cKundengruppe_arr = [];
            $kKundengruppe_arr                = [];
            $kKundengruppe_arr                = gibKeyArrayFuerKeyString($oNews->cKundengruppe, ';');

            foreach ($kKundengruppe_arr as $kKundengruppe) {
                if ($kKundengruppe == -1) {
                    $oNews_arr[$i]->cKundengruppe_arr[] = 'Alle';
                } else {
                    $oKundengruppe = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
                    if (!empty($oKundengruppe->cName)) {
                        $oNews_arr[$i]->cKundengruppe_arr[] = $oKundengruppe->cName;
                    }
                }
            }
            //add row "Kategorie" to news
            $oCategorytoNews_arr = Shop::DB()->query(
                "SELECT tnewskategorie.cName
                    FROM tnewskategorie
                    LEFT JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    WHERE tnewskategorienews.kNews = " . (int)$oNews->kNews ." 
                    ORDER BY tnewskategorie.nSort", 2
            );
            $Kategoriearray = [];
            foreach ($oCategorytoNews_arr as $j => $KategorieAusgabe) {
                $Kategoriearray[] = $KategorieAusgabe->cName;
            }
            $oNews_arr[$i]->KategorieAusgabe = implode(',<br />', $Kategoriearray);
            // Limit News comments on aktiv comments
            $oNewsKommentarAktiv = Shop::DB()->query(
                "SELECT count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahlAktiv
                    FROM tnews
                    LEFT JOIN tnewskommentar 
                        ON tnewskommentar.kNews = tnews.kNews
                    WHERE tnewskommentar.nAktiv = 1 
                        AND tnews.kNews = " . (int)$oNews->kNews . "
                        AND tnews.kSprache = " . (int)$_SESSION['kSprache'], 1
            );
            $oNews_arr[$i]->nNewsKommentarAnzahl = $oNewsKommentarAktiv->nNewsKommentarAnzahlAktiv;
        }
    }
    // Newskommentare die auf eine Freischaltung warten
    $oNewsKommentar_arr = Shop::DB()->query(
        "SELECT SQL_CALC_FOUND_ROWS tnewskommentar.*, 
            DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            tkunde.kKunde, tkunde.cVorname, tkunde.cNachname, tnews.cBetreff
            FROM tnewskommentar
            JOIN tnews 
                ON tnews.kNews = tnewskommentar.kNews
            LEFT JOIN tkunde 
                ON tkunde.kKunde = tnewskommentar.kKunde
            WHERE tnewskommentar.nAktiv = 0
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'], 2
    );
    $oNewsKommentarAnzahl = Shop::DB()->query(
        "SELECT FOUND_ROWS() AS nAnzahl", 1
    );

    if (is_array($oNewsKommentar_arr) && count($oNewsKommentar_arr) > 0) {
        foreach ($oNewsKommentar_arr as $i => $oNewsKommentar) {
            $oKunde = new Kunde($oNewsKommentar->kKunde);

            $oNewsKommentar_arr[$i]->cNachname = $oKunde->cNachname;
        }
    }
    // Einstellungen
    $oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_NEWS, '*', 'nSort');
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$oConfig_arr[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        $oSetValue = Shop::DB()->select(
            'teinstellungen',
            'kEinstellungenSektion',
            CONF_NEWS,
            'cName',
            $oConfig_arr[$i]->cWertName
        );
        $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert)
            ? $oSetValue->cWert
            : null;
    }

    // Praefix
    if (count($Sprachen) > 0) {
        $oNewsMonatsPraefix_arr = [];
        foreach ($Sprachen as $i => $oSprache) {
            $oNewsMonatsPraefix_arr[$i]                = new stdClass();
            $oNewsMonatsPraefix_arr[$i]->kSprache      = $oSprache->kSprache;
            $oNewsMonatsPraefix_arr[$i]->cNameEnglisch = $oSprache->cNameEnglisch;
            $oNewsMonatsPraefix_arr[$i]->cNameDeutsch  = $oSprache->cNameDeutsch;
            $oNewsMonatsPraefix_arr[$i]->cISOSprache   = $oSprache->cISO;
            $oNewsMonatsPraefix                        = Shop::DB()->select('tnewsmonatspraefix', 'kSprache', (int)$oSprache->kSprache);
            $oNewsMonatsPraefix_arr[$i]->cPraefix      = isset($oNewsMonatsPraefix->cPraefix)
                ? $oNewsMonatsPraefix->cPraefix
                : null;
        }
        $smarty->assign('oNewsMonatsPraefix_arr', $oNewsMonatsPraefix_arr);
    }
    // Newskategorie
    $oNewsKategorie_arr = holeNewskategorie($_SESSION['kSprache']);
    $oNewsKatsAnzahl    = Shop::DB()->query("SELECT FOUND_ROWS() AS nAnzahl", 1);
    // Paginationen
    $oPagiKommentar = (new Pagination('kommentar'))
        ->setItemArray($oNewsKommentar_arr)
        ->assemble();
    $oPagiNews = (new Pagination('news'))
        ->setItemArray($oNews_arr)
        ->assemble();
    $oPagiKats = (new Pagination('kats'))
        ->setItemArray($oNewsKategorie_arr)
        ->assemble();

    $smarty->assign('oConfig_arr', $oConfig_arr)
           ->assign('oNewsKommentar_arr', $oPagiKommentar->getPageItems())
           ->assign('oNews_arr', $oPagiNews->getPageItems())
           ->assign('oNewsKategorie_arr', $oPagiKats->getPageItems())
           ->assign('oPagiKommentar', $oPagiKommentar)
           ->assign('oPagiNews', $oPagiNews)
           ->assign('oPagiKats', $oPagiKats);
}

if (!empty($_SESSION['news.cHinweis'])) {
    $cHinweis .= $_SESSION['news.cHinweis'];
    unset($_SESSION['news.cHinweis']);
}

$nMaxFileSize      = getMaxFileSize(ini_get('upload_max_filesize'));
$oKundengruppe_arr = Shop::DB()->query(
    "SELECT kKundengruppe, cName
        FROM tkundengruppe
        ORDER BY cStandard DESC", 2
);

$smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', $Sprachen)
       ->assign('nMaxFileSize', $nMaxFileSize)
       ->assign('kSprache', (int)$_SESSION['kSprache'])
       ->display('news.tpl');
