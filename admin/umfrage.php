<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'umfrage_inc.php';

$oAccount->permission('EXTENSION_VOTE_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_UMFRAGE]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'umfrage_uebersicht';
$kUmfrageTMP   = 0;
$kUmfrage      = 0;
if (verifyGPCDataInteger('kUmfrage') > 0) {
    $kUmfrageTMP = verifyGPCDataInteger('kUmfrage');
} else {
    $kUmfrageTMP = verifyGPCDataInteger('kU');
}
setzeSprache();

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
$Sprachen    = gibAlleSprachen();
$oSpracheTMP = Shop::DB()->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
// Modulueberpruefung
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UMFRAGE)) {
    // Umfrage
    if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
        $cHinweis .= saveAdminSectionSettings(CONF_UMFRAGE, $_POST);
    }
    // Umfrage
    if (verifyGPCDataInteger('umfrage') === 1 && validateToken()) {
        // Umfrage erstellen
        if (isset($_POST['umfrage_erstellen']) && (int)$_POST['umfrage_erstellen'] === 1) {
            $step = 'umfrage_erstellen';
        } elseif (isset($_GET['umfrage_editieren']) && (int)$_GET['umfrage_editieren'] === 1) {
            // Umfrage editieren
            $step     = 'umfrage_editieren';
            $kUmfrage = (int)$_GET['kUmfrage'];

            if ($kUmfrage > 0) {
                $oUmfrage = Shop::DB()->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $kUmfrage, 1
                );
                $oUmfrage->kKundengruppe_arr = gibKeyArrayFuerKeyString($oUmfrage->cKundengruppe, ';');

                $smarty->assign('oUmfrage', $oUmfrage)
                       ->assign('s1', verifyGPCDataInteger('s1'));
            } else {
                $cFehler .= 'Fehler: Ihre Umfrage konnte nicht gefunden werden.<br />';
                $step = 'umfrage_uebersicht';
            }
        }

        // Umfrage Antwort oder Option loeschen
        if (isset($_GET['a']) && $_GET['a'] === 'a_loeschen') {
            $step                 = 'umfrage_frage_bearbeiten';
            $kUmfrageFrage        = (int)$_GET['kUF'];
            $kUmfrageFrageAntwort = (int)$_GET['kUFA'];
            if ($kUmfrageFrageAntwort > 0) {
                Shop::DB()->query(
                    "DELETE tumfragefrageantwort, tumfragedurchfuehrungantwort
                        FROM tumfragefrageantwort
                        LEFT JOIN tumfragedurchfuehrungantwort
                            ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = tumfragefrageantwort.kUmfrageFrageAntwort
                        WHERE tumfragefrageantwort.kUmfrageFrageAntwort = " . $kUmfrageFrageAntwort, 3
                );
            }
            Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (isset($_GET['a']) && $_GET['a'] === 'o_loeschen') {
            $step                 = 'umfrage_frage_bearbeiten';
            $kUmfrageFrage        = (int)$_GET['kUF'];
            $kUmfrageMatrixOption = (int)$_GET['kUFO'];
            if ($kUmfrageMatrixOption > 0) {
                Shop::DB()->query(
                    "DELETE tumfragematrixoption, tumfragedurchfuehrungantwort
                        FROM tumfragematrixoption
                        LEFT JOIN tumfragedurchfuehrungantwort
                            ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = tumfragematrixoption.kUmfrageMatrixOption
                        WHERE tumfragematrixoption.kUmfrageMatrixOption = " . $kUmfrageMatrixOption, 3
                );
            }
            Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
        }

        // Umfrage speichern
        if (isset($_POST['umfrage_speichern']) && (int)$_POST['umfrage_speichern']) {
            $step = 'umfrage_erstellen';

            if (isset($_POST['umfrage_edit_speichern'], $_POST['kUmfrage']) &&
                (int)$_POST['umfrage_edit_speichern'] === 1 && (int)$_POST['kUmfrage'] > 0) {
                $kUmfrage = (int)$_POST['kUmfrage'];
            }
            $cName  = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $kKupon = isset($_POST['kKupon']) ? (int)$_POST['kKupon'] : 0;
            if ($kKupon <= 0 || !isset($kKupon)) {
                $kKupon = 0;
            }
            $cSeo              = $_POST['cSeo'];
            $kKundengruppe_arr = $_POST['kKundengruppe'];
            $cBeschreibung     = $_POST['cBeschreibung'];
            $fGuthaben         = isset($_POST['fGuthaben']) ?
                (float)str_replace(',', '.', $_POST['fGuthaben'])
                : 0;
            if ($fGuthaben <= 0 || !isset($kKupon)) {
                $fGuthaben = 0;
            }
            $nBonuspunkte = isset($_POST['nBonuspunkte'])
                ? (int)$_POST['nBonuspunkte'] 
                : 0;
            if ($nBonuspunkte <= 0 || !isset($kKupon)) {
                $nBonuspunkte = 0;
            }
            $nAktiv      = (int)$_POST['nAktiv'];
            $dGueltigVon = $_POST['dGueltigVon'];
            $dGueltigBis = $_POST['dGueltigBis'];

            // Sind die wichtigen Daten vorhanden?
            if (strlen($cName) > 0 && 
                (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) && 
                strlen($dGueltigVon) > 0) {
                if (($kKupon == 0 && $fGuthaben == 0 && $nBonuspunkte == 0) || 
                    ($kKupon > 0 && $fGuthaben == 0 && $nBonuspunkte == 0) ||
                    ($kKupon == 0 && $fGuthaben > 0 && $nBonuspunkte == 0) || 
                    ($kKupon == 0 && $fGuthaben == 0 && $nBonuspunkte > 0)) {
                    $step                    = 'umfrage_frage_erstellen';
                    $oUmfrage                = new stdClass();
                    $oUmfrage->kSprache      = $_SESSION['kSprache'];
                    $oUmfrage->kKupon        = $kKupon;
                    $oUmfrage->cName         = $cName;
                    $oUmfrage->cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
                    $oUmfrage->cBeschreibung = $cBeschreibung;
                    $oUmfrage->fGuthaben     = $fGuthaben;
                    $oUmfrage->nBonuspunkte  = $nBonuspunkte;
                    $oUmfrage->nAktiv        = $nAktiv;
                    $oUmfrage->dGueltigVon   = DateTime::createFromFormat('d.m.Y H:i', $dGueltigVon)->format('Y-m-d H:i:00');
                    $oUmfrage->dGueltigBis   = (strlen($dGueltigBis) > 0) 
                        ? DateTime::createFromFormat('d.m.Y H:i', $dGueltigBis)->format('Y-m-d H:i:00')
                        : null;
                    $oUmfrage->dErstellt     = (new DateTime())->format('Y-m-d H:i:s');

                    $nNewsOld = 0;
                    if (isset($_POST['umfrage_edit_speichern']) && (int)$_POST['umfrage_edit_speichern'] === 1) {
                        $nNewsOld = 1;
                        $step     = 'umfrage_uebersicht';

                        Shop::DB()->delete('tumfrage', 'kUmfrage', $kUmfrage);
                        // tseo loeschen
                        Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $kUmfrage]);
                    }

                    if (strlen($cSeo) > 0) {
                        $oUmfrage->cSeo = checkSeo(getSeo($cSeo));
                    } else {
                        $oUmfrage->cSeo = checkSeo(getSeo($cName));
                    }
                    if (isset($kUmfrage) && $kUmfrage > 0) {
                        $oUmfrage->kUmfrage = $kUmfrage;
                        Shop::DB()->insert('tumfrage', $oUmfrage);
                    } else {
                        $kUmfrage = Shop::DB()->insert('tumfrage', $oUmfrage);
                    }
                    Shop::DB()->delete(
                        'tseo', 
                        ['cKey', 'kKey', 'kSprache'], 
                        ['kUmfrage', $kUmfrage, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = $oUmfrage->cSeo;
                    $oSeo->cKey     = 'kUmfrage';
                    $oSeo->kKey     = $kUmfrage;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    Shop::DB()->insert('tseo', $oSeo);

                    $kUmfrageTMP = $kUmfrage;

                    $cHinweis .= 'Ihre Umfrage wurde erfolgreich gespeichert. Bitte folgen Sie nun den weiteren Schritten.<br />';
                    Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
                } else {
                    $cFehler .= 'Fehler: Bitte geben Sie nur eine Belohnungsart an.<br />';
                }
            } else {
                $cFehler .= 'Fehler: Bitte geben Sie einen Namen, mindestens eine Kundengruppe und ein g&uuml;ltiges Anfangsdatum ein.<br />';
            }
        } elseif (isset($_POST['umfrage_frage_speichern']) && (int)$_POST['umfrage_frage_speichern'] === 1 && validateToken()) { // Frage speichern
            $kUmfrage                 = (int)$_POST['kUmfrage'];
            $kUmfrageFrage            = isset($_POST['kUmfrageFrage']) ? (int)$_POST['kUmfrageFrage'] : 0;
            $cName                    = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $cTyp                     = $_POST['cTyp'];
            $nSort                    = isset($_POST['nSort']) ? (int)$_POST['nSort'] : 0;
            $cBeschreibung            = isset($_POST['cBeschreibung']) ? $_POST['cBeschreibung'] : '';
            $cNameOption              = isset($_POST['cNameOption']) ? $_POST['cNameOption'] : null;
            $cNameAntwort             = isset($_POST['cNameAntwort']) ? $_POST['cNameAntwort'] : null;
            $nFreifeld                = isset($_POST['nFreifeld']) ? $_POST['nFreifeld'] : null;
            $nNotwendig               = isset($_POST['nNotwendig']) ? $_POST['nNotwendig'] : null;
            $kUmfrageFrageAntwort_arr = isset($_POST['kUmfrageFrageAntwort']) ? $_POST['kUmfrageFrageAntwort'] : null;
            $kUmfrageMatrixOption_arr = isset($_POST['kUmfrageMatrixOption']) ? $_POST['kUmfrageMatrixOption'] : null;
            $nSortAntwort_arr         = isset($_POST['nSortAntwort']) ? $_POST['nSortAntwort'] : 0;
            $nSortOption_arr          = isset($_POST['nSortOption']) ? $_POST['nSortOption'] : null;

            if (isset($_POST['nocheinefrage'])) {
                $step = 'umfrage_frage_erstellen';
            }

            if ($kUmfrage > 0 && strlen($cName) > 0 && strlen($cTyp) > 0) {
                unset($oUmfrageFrage);
                $oUmfrageFrage                = new stdClass();
                $oUmfrageFrage->kUmfrage      = $kUmfrage;
                $oUmfrageFrage->cTyp          = $cTyp;
                $oUmfrageFrage->cName         = $cName;
                $oUmfrageFrage->cBeschreibung = $cBeschreibung;
                $oUmfrageFrage->nSort         = $nSort;
                $oUmfrageFrage->nFreifeld     = $nFreifeld;
                $oUmfrageFrage->nNotwendig    = $nNotwendig;

                $nNewsOld = 0;
                if (isset($_POST['umfrage_frage_edit_speichern']) && (int)$_POST['umfrage_frage_edit_speichern'] === 1) {
                    $nNewsOld      = 1;
                    $step          = 'umfrage_vorschau';
                    $kUmfrageFrage = (int)$_POST['kUmfrageFrage'];
                    if (!pruefeTyp($cTyp, $kUmfrageFrage)) {
                        $cFehler .= 'Fehler: Ihr Fragentyp ist leider nicht kompatibel mit dem voherigen. Um den Fragetyp zu &auml;ndern, resetten Sie bitte die Frage.';
                        $step = 'umfrage_frage_bearbeiten';
                    }
                    //loescheFrage($kUmfrageFrage);
                    Shop::DB()->delete('tumfragefrage', 'kUmfrageFrage', $kUmfrageFrage);
                }
                // Falls eine Frage geaendert wurde, gibt dieses Objekt die Anzahl an Antworten und Optionen an, die schon vorhanden waren.
                $oAnzahlAUndOVorhanden                   = new stdClass();
                $oAnzahlAUndOVorhanden->nAnzahlAntworten = 0;
                $oAnzahlAUndOVorhanden->nAnzahlOptionen  = 0;

                if ($kUmfrageFrage > 0 && $step !== 'umfrage_frage_bearbeiten') {
                    $oUmfrageFrage->kUmfrageFrage = $kUmfrageFrage;
                    Shop::DB()->insert('tumfragefrage', $oUmfrageFrage);
                    // Update vorhandene Antworten bzw. Optionen
                    $oAnzahlAUndOVorhanden = updateAntwortUndOption(
                        $kUmfrageFrage,
                        $cTyp,
                        $cNameOption,
                        $cNameAntwort,
                        $nSortAntwort_arr,
                        $nSortOption_arr,
                        $kUmfrageFrageAntwort_arr,
                        $kUmfrageMatrixOption_arr
                    );
                } else {
                    $kUmfrageFrage = Shop::DB()->insert('tumfragefrage', $oUmfrageFrage);
                }
                // Antwort bzw. Matrix speichern
                speicherAntwortZuFrage(
                    $kUmfrageFrage,
                    $cTyp,
                    $cNameOption,
                    $cNameAntwort,
                    $nSortAntwort_arr,
                    $nSortOption_arr,
                    $oAnzahlAUndOVorhanden
                );

                $cHinweis .= 'Ihr Frage wurde erfolgreich gespeichert.<br />';
                Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $step = 'umfrage_frage_erstellen';
                $cFehler .= 'Fehler: Bitte tragen Sie mindestens einen Namen und einen Typ ein.<br />';
            }
        } elseif (isset($_POST['umfrage_loeschen']) && (int)$_POST['umfrage_loeschen'] === 1 && validateToken()) {
            // Umfrage loeschen
            if (is_array($_POST['kUmfrage']) && count($_POST['kUmfrage']) > 0) {
                foreach ($_POST['kUmfrage'] as $kUmfrage) {
                    $kUmfrage = (int)$kUmfrage;
                    // tumfrage loeschen
                    Shop::DB()->delete('tumfrage', 'kUmfrage', $kUmfrage);

                    $oUmfrageFrage_arr = Shop::DB()->query(
                        "SELECT kUmfrageFrage
                            FROM tumfragefrage
                            WHERE kUmfrage = " . $kUmfrage, 2
                    );
                    if (is_array($oUmfrageFrage_arr) && count($oUmfrageFrage_arr) > 0) {
                        foreach ($oUmfrageFrage_arr as $oUmfrageFrage) {
                            loescheFrage($oUmfrageFrage->kUmfrageFrage);
                        }
                    }
                    // tseo loeschen
                    Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $kUmfrage]);
                    // Umfrage Durchfuehrung loeschen
                    Shop::DB()->query(
                        "DELETE tumfragedurchfuehrung, tumfragedurchfuehrungantwort 
                            FROM tumfragedurchfuehrung
                            LEFT JOIN tumfragedurchfuehrungantwort 
                              ON tumfragedurchfuehrungantwort.kUmfrageDurchfuehrung = tumfragedurchfuehrung.kUmfrageDurchfuehrung
                            WHERE tumfragedurchfuehrung.kUmfrage = " . $kUmfrage, 3
                    );
                }
                $cHinweis .= 'Ihre markierten Umfragen wurden erfolgreich gel&ouml;scht.<br />';
                Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Umfrage.<br />';
            }
        } // Frage loeschen
        elseif (isset($_POST['umfrage_frage_loeschen']) && (int)$_POST['umfrage_frage_loeschen'] === 1 && validateToken()) {
            $step = 'umfrage_vorschau';
            // Ganze Frage loeschen mit allen Antworten und Matrixen
            if (is_array($_POST['kUmfrageFrage']) && count($_POST['kUmfrageFrage']) > 0) {
                foreach ($_POST['kUmfrageFrage'] as $kUmfrageFrage) {
                    $kUmfrageFrage = (int)$kUmfrageFrage;

                    loescheFrage($kUmfrageFrage);
                }

                $cHinweis = 'Ihre markierten Fragen wurden erfolgreich gel&ouml;scht.<br>';
            }
            // Bestimmte Antworten loeschen
            if (is_array($_POST['kUmfrageFrageAntwort']) && count($_POST['kUmfrageFrageAntwort']) > 0) {
                foreach ($_POST['kUmfrageFrageAntwort'] as $kUmfrageFrageAntwort) {
                    $kUmfrageFrageAntwort = (int)$kUmfrageFrageAntwort;

                    Shop::DB()->query(
                        "DELETE tumfragefrageantwort, tumfragedurchfuehrungantwort 
                            FROM tumfragefrageantwort
                            LEFT JOIN tumfragedurchfuehrungantwort
                                ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = tumfragefrageantwort.kUmfrageFrageAntwort
                            WHERE tumfragefrageantwort.kUmfrageFrageAntwort = " . $kUmfrageFrageAntwort, 3
                    );
                }
                $cHinweis .= "Ihre markierten Antworten wurden erfolgreich gel&ouml;scht.<br>";
            }
            // Bestimmte Optionen loeschen
            if (isset($_POST['kUmfrageMatrixOption']) &&
                is_array($_POST['kUmfrageMatrixOption']) &&
                count($_POST['kUmfrageMatrixOption']) > 0) {
                foreach ($_POST['kUmfrageMatrixOption'] as $kUmfrageMatrixOption) {
                    $kUmfrageMatrixOption = (int)$kUmfrageMatrixOption;
                    Shop::DB()->query(
                        "DELETE tumfragematrixoption, tumfragedurchfuehrungantwort 
                            FROM tumfragematrixoption
                            LEFT JOIN tumfragedurchfuehrungantwort
                                ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = tumfragematrixoption.kUmfrageMatrixOption
                            WHERE tumfragematrixoption.kUmfrageMatrixOption = " . $kUmfrageMatrixOption, 3
                    );
                }

                $cHinweis .= 'Ihre markierten Optionen wurden erfolgreich gel&ouml;scht.<br />';
            }
            Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (isset($_POST['umfrage_frage_hinzufuegen']) &&
            (int)$_POST['umfrage_frage_hinzufuegen'] === 1 &&
            validateToken()
        ) { // Frage hinzufuegen
            $step = 'umfrage_frage_erstellen';
            $smarty->assign('kUmfrageTMP', $kUmfrageTMP);
        } elseif (verifyGPCDataInteger('umfrage_statistik') === 1) {
            // Umfragestatistik anschauen
            $oUmfrageDurchfuehrung_arr = Shop::DB()->query(
                "SELECT kUmfrageDurchfuehrung
                    FROM tumfragedurchfuehrung
                    WHERE kUmfrage = " . $kUmfrageTMP, 2
            );

            if (count($oUmfrageDurchfuehrung_arr) > 0) {
                $step = 'umfrage_statistik';
                $smarty->assign('oUmfrageStats', holeUmfrageStatistik($kUmfrageTMP));
            } else {
                $step = 'umfrage_vorschau';
                $cFehler .= 'Fehler: F&uuml;r diese Umfrage gibt es noch keine Stastistik.';
            }
        } elseif (isset($_GET['a']) && $_GET['a'] === 'zeige_sonstige') {
            // Umfragestatistik Sonstige Texte anzeigen
            $step          = 'umfrage_statistik';
            $kUmfrageFrage = (int)$_GET['uf'];
            $nAnzahlAnwort = (int)$_GET['aa'];
            $nMaxAntworten = (int)$_GET['ma'];

            if ($kUmfrageFrage > 0 && $nMaxAntworten > 0) {
                $step = 'umfrage_statistik_sonstige_texte';
                $smarty->assign('oUmfrageFrage', holeSonstigeTextAntworten($kUmfrageFrage, $nAnzahlAnwort, $nMaxAntworten));
            }
        } elseif ((isset($_GET['fe']) && (int)$_GET['fe'] === 1) ||
            ($step === 'umfrage_frage_bearbeiten' && validateToken())
        ) { // Frage bearbeiten
            $step = 'umfrage_frage_erstellen';

            if (verifyGPCDataInteger('kUmfrageFrage') > 0) {
                $kUmfrageFrage = verifyGPCDataInteger('kUmfrageFrage');
            } else {
                $kUmfrageFrage = verifyGPCDataInteger('kUF');
            }
            $oUmfrageFrage = Shop::DB()->select('tumfragefrage', 'kUmfrageFrage', $kUmfrageFrage);
            if (isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0) {
                $oUmfrageFrage->oUmfrageFrageAntwort_arr = Shop::DB()->selectAll(
                    'tumfragefrageantwort', 
                    'kUmfrageFrage', 
                    (int)$oUmfrageFrage->kUmfrageFrage, 
                    '*', 
                    'nSort'
                );
                $oUmfrageFrage->oUmfrageMatrixOption_arr = Shop::DB()->selectAll(
                    'tumfragematrixoption', 
                    'kUmfrageFrage', 
                    (int)$oUmfrageFrage->kUmfrageFrage,
                    '*',
                    'nSort'
                );
            }

            $smarty->assign('oUmfrageFrage', $oUmfrageFrage)
                   ->assign('kUmfrageTMP', $kUmfrageTMP);
        }
        // Umfrage Detail
        if ((isset($_GET['ud']) && (int)$_GET['ud'] === 1) || $step === 'umfrage_vorschau') {
            $kUmfrage = verifyGPCDataInteger('kUmfrage');

            if ($kUmfrage > 0) {
                $step     = 'umfrage_vorschau';
                $oUmfrage = Shop::DB()->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                        DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $kUmfrage, 1
                );
                if ($oUmfrage->kUmfrage > 0) {
                    $oUmfrage->cKundengruppe_arr = [];
                    $kKundengruppe_arr           = [];

                    $kKundengruppe_arr = gibKeyArrayFuerKeyString($oUmfrage->cKundengruppe, ';');

                    foreach ($kKundengruppe_arr as $kKundengruppe) {
                        if ($kKundengruppe == -1) {
                            $oUmfrage->cKundengruppe_arr[] = 'Alle';
                        } else {
                            $oKundengruppe = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
                            if (!empty($oKundengruppe->cName)) {
                                $oUmfrage->cKundengruppe_arr[] = $oKundengruppe->cName;
                            }
                        }
                    }

                    $oUmfrage->oUmfrageFrage_arr = [];
                    $oUmfrage->oUmfrageFrage_arr = Shop::DB()->selectAll(
                        'tumfragefrage',
                        'kUmfrage',
                        $kUmfrage,
                        '*',
                        'nSort'
                    );
                    if (count($oUmfrage->oUmfrageFrage_arr) > 0) {
                        foreach ($oUmfrage->oUmfrageFrage_arr as $i => $oUmfrageFrage) {
                            // Mappe Fragentyp
                            $oUmfrage->oUmfrageFrage_arr[$i]->cTypMapped = mappeFragenTyp($oUmfrageFrage->cTyp);

                            $oUmfrage->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = [];
                            $oUmfrage->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = Shop::DB()->selectAll(
                                'tumfragefrageantwort',
                                'kUmfrageFrage',
                                (int)$oUmfrage->oUmfrageFrage_arr[$i]->kUmfrageFrage,
                                'kUmfrageFrageAntwort, kUmfrageFrage, cName',
                                'nSort'
                            );
                            $oUmfrage->oUmfrageFrage_arr[$i]->oUmfrageMatrixOption_arr = [];
                            $oUmfrage->oUmfrageFrage_arr[$i]->oUmfrageMatrixOption_arr = Shop::DB()->selectAll(
                                'tumfragematrixoption',
                                'kUmfrageFrage',
                                (int)$oUmfrage->oUmfrageFrage_arr[$i]->kUmfrageFrage,
                                'kUmfrageMatrixOption, kUmfrageFrage, cName',
                                'nSort'
                            );
                        }
                    }
                    $smarty->assign('oUmfrage', $oUmfrage);
                }
            } else {
                $cFehler .= 'Fehler: Bitte w&auml;hlen Sie eine korrekte Umfrage aus.<br>';
            }
        }

        if ($kUmfrageTMP > 0 && (!isset($_POST['umfrage_frage_edit_speichern']) || (int)$_POST['umfrage_frage_edit_speichern'] !== 1) &&
            (!isset($_GET['fe']) || (int)$_GET['fe']) !== 1 && validateToken()) {
            $smarty->assign('oUmfrageFrage_arr', Shop::DB()->selectAll(
                'tumfragefrage',
                'kUmfrage',
                (int)$kUmfrageTMP,
                '*',
                'nSort')
            )->assign('kUmfrageTMP', $kUmfrageTMP);
        }
    }
    // Hole Umfrage aus DB
    if ($step === 'umfrage_uebersicht') {
        $oUmfrageAnzahl = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tumfrage
                WHERE kSprache = " . (int)$_SESSION['kSprache'], 1
        );
        // Pagination
        $oPagination = (new Pagination())
            ->setItemCount($oUmfrageAnzahl->nAnzahl)
            ->assemble();
        $oUmfrage_arr = Shop::DB()->query(
            "SELECT tumfrage.*, DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                DATE_FORMAT(tumfrage.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de, 
                count(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                FROM tumfrage
                JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                GROUP BY tumfrage.kUmfrage
                ORDER BY dGueltigVon DESC
                LIMIT " . $oPagination->getLimitSQL(),
            2);

        if (is_array($oUmfrage_arr) && count($oUmfrage_arr) > 0) {
            foreach ($oUmfrage_arr as $i => $oUmfrage) {
                $oUmfrage_arr[$i]->cKundengruppe_arr = [];
                $kKundengruppe_arr                   = [];
                $kKundengruppe_arr                   = gibKeyArrayFuerKeyString($oUmfrage->cKundengruppe, ";");

                foreach ($kKundengruppe_arr as $kKundengruppe) {
                    if ($kKundengruppe == -1) {
                        $oUmfrage_arr[$i]->cKundengruppe_arr[] = 'Alle';
                    } else {
                        $oKundengruppe = Shop::DB()->query(
                            "SELECT cName
                                FROM tkundengruppe
                                WHERE kKundengruppe = " . (int)$kKundengruppe, 1
                        );
                        if (!empty($oKundengruppe->cName)) {
                            $oUmfrage_arr[$i]->cKundengruppe_arr[] = $oKundengruppe->cName;
                        }
                    }
                }
            }
        }
        $oConfig_arr = Shop::DB()->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            CONF_UMFRAGE,
            '*',
            'nSort'
        );
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
                CONF_UMFRAGE,
                'cName',
                $oConfig_arr[$i]->cWertName
            );
            $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert)
                ? $oSetValue->cWert
                : null;
        }

        $smarty->assign('oConfig_arr', $oConfig_arr)
               ->assign('oUmfrage_arr', $oUmfrage_arr)
               ->assign('oPagination', $oPagination);
    }
    // Vorhandene Kundengruppen
    $oKundengruppe_arr = Shop::DB()->query(
        "SELECT kKundengruppe, cName
            FROM tkundengruppe
            ORDER BY cStandard DESC", 2
    );
    // Gueltige Kupons
    $oKupon_arr = Shop::DB()->query(
        "SELECT tkupon.kKupon, tkuponsprache.cName
            FROM tkupon
            LEFT JOIN tkuponsprache 
                ON tkuponsprache.kKupon = tkupon.kKupon
            WHERE tkupon.dGueltigAb <= now()
                AND (tkupon.dGueltigBis >= now() || tkupon.dGueltigBis = '0000-00-00 00:00:00')
                AND (tkupon.nVerwendungenBisher <= tkupon.nVerwendungen OR tkupon.nVerwendungen=0)
                AND tkupon.cAktiv = 'Y'
                AND tkuponsprache.cISOSprache= '" . $oSpracheTMP->cISO . "'
            ORDER BY tkupon.cName", 2
    );

    $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
           ->assign('oKupon_arr', $oKupon_arr);
} else {
    $smarty->assign('noModule', true);
}

$smarty->assign('Sprachen', $Sprachen)
       ->assign('kSprache', $_SESSION['kSprache'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('umfrage.tpl');
