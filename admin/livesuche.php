<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_LIVESEARCH_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

setzeSprache();

$hinweis           = '';
$fehler            = '';
$settingsIDs       = [423, 425, 422, 437, 438];

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}

// Suchanfrage Suche
$cLivesucheSQL = new stdClass();
$cLivesucheSQL->cWhere = '';
$cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
if (strlen(verifyGPDataString('cSuche')) > 0) {
    $cSuche = Shop::DB()->escape(StringHandler::filterXSS(verifyGPDataString('cSuche')));

    if (strlen($cSuche) > 0) {
        $cLivesucheSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $cSuche . "%'";
        $smarty->assign('cSuche', $cSuche);
    } else {
        $fehler = 'Fehler: Bitte geben Sie einen Suchbegriff ein.';
    }
}

// Einstellungen
if (verifyGPCDataInteger('einstellungen') === 1) {
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
    $smarty->assign('tab', 'einstellungen');
}

// Suchanfragen Sortierung
if (verifyGPCDataInteger('nSort') > 0) {
    $smarty->assign('nSort', verifyGPCDataInteger('nSort'));

    switch (verifyGPCDataInteger('nSort')) {
        case 1:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv DESC ';
            break;
        case 33:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv ASC ';
            break;
    }
} else {
    $smarty->assign('nSort', -1);
}

if (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 1) { //Formular wurde abgeschickt
    // Suchanfragen aktualisieren
    if (isset($_POST['suchanfragenUpdate'])) {
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            foreach ($_POST['kSuchanfrageAll'] as $kSuchanfrage) {
                if (strlen($_POST['nAnzahlGesuche_' . $kSuchanfrage]) > 0 && (int)$_POST['nAnzahlGesuche_' . $kSuchanfrage] > 0) {
                    $_upd                 = new stdClass();
                    $_upd->nAnzahlGesuche = (int)$_POST['nAnzahlGesuche_' . $kSuchanfrage];
                    Shop::DB()->update('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage, $_upd);
                }
            }
        }
        // Eintragen in die Mapping Tabelle
        $Suchanfragen = Shop::DB()->selectAll('tsuchanfrage', 'kSprache', (int)$_SESSION['kSprache'], '*', 'nAnzahlGesuche DESC');
        // Wurde ein Mapping durchgefuehrt
        $nMappingVorhanden = 0;

        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            $cSQLDel = ' IN (';

            // nAktiv Reihe updaten
            foreach ($_POST['kSuchanfrageAll'] as $i => $kSuchanfrage) {
                $upd         = new stdClass();
                $upd->nAktiv = 0;
                Shop::DB()->update('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage, $upd);
                // Loeschequery vorbereiten
                if ($i > 0) {
                    $cSQLDel .= ', ' . (int)$kSuchanfrage;
                } else {
                    $cSQLDel .= (int)$kSuchanfrage;
                }
            }

            $cSQLDel .= ')';
            // Deaktivierte Suchanfragen aus tseo loeschen
            Shop::DB()->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey" . $cSQLDel, 3
            );
            // Deaktivierte Suchanfragen in tsuchanfrage updaten
            Shop::DB()->query(
                "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $cSQLDel, 3
            );
            if (isset($_POST['nAktiv']) && is_array($_POST['nAktiv'])) {
                foreach ($_POST['nAktiv'] as $i => $nAktiv) {
                    $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', (int)$nAktiv);
                    Shop::DB()->delete('tseo', ['cKey', 'kKey', 'kSprache'], ['kSuchanfrage', (int)$nAktiv, (int)$_SESSION['kSprache']]);
                    // Aktivierte Suchanfragen in tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = checkSeo(getSeo($oSuchanfrage->cSuche));
                    $oSeo->cKey     = 'kSuchanfrage';
                    $oSeo->kKey     = $nAktiv;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    Shop::DB()->insert('tseo', $oSeo);
                    // Aktivierte Suchanfragen in tsuchanfrage updaten
                    $upd         = new stdClass();
                    $upd->nAktiv = 1;
                    $upd->cSeo   = $oSeo->cSeo;
                    Shop::DB()->update('tsuchanfrage', 'kSuchanfrage', (int)$nAktiv, $upd);
                }
            }
        }

        if (count($Suchanfragen) > 0) {
            foreach ($Suchanfragen as $sucheanfrage) {
                if (!isset($_POST['mapping_' . $sucheanfrage->kSuchanfrage]) ||
                    strtolower($sucheanfrage->cSuche) !== strtolower($_POST['mapping_' . $sucheanfrage->kSuchanfrage])) {
                    if (!empty($_POST['mapping_' . $sucheanfrage->kSuchanfrage])) {
                        $nMappingVorhanden                      = 1;
                        $suchanfragemapping_obj                 = new stdClass();
                        $suchanfragemapping_obj->kSprache       = $_SESSION['kSprache'];
                        $suchanfragemapping_obj->cSuche         = $sucheanfrage->cSuche;
                        $suchanfragemapping_obj->cSucheNeu      = $_POST['mapping_' . $sucheanfrage->kSuchanfrage];
                        $suchanfragemapping_obj->nAnzahlGesuche = $sucheanfrage->nAnzahlGesuche;
                        $Neuesuche = Shop::DB()->select('tsuchanfrage', 'cSuche', $suchanfragemapping_obj->cSucheNeu);
                        if (isset($Neuesuche->kSuchanfrage) && $Neuesuche->kSuchanfrage > 0) {
                            Shop::DB()->insert('tsuchanfragemapping', $suchanfragemapping_obj);
                            Shop::DB()->query(
                                "UPDATE tsuchanfrage
                                    SET nAnzahlGesuche = nAnzahlGesuche+" . $sucheanfrage->nAnzahlGesuche . "
                                    WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                                        AND cSuche = '" . Shop::DB()->escape($_POST['mapping_' . $sucheanfrage->kSuchanfrage]) . "'", 4
                            );
                            Shop::DB()->delete('tsuchanfrage', 'kSuchanfrage', (int)$sucheanfrage->kSuchanfrage);
                            $upd       = new stdClass();
                            $upd->kKey = (int)$Neuesuche->kSuchanfrage;
                            Shop::DB()->update('tseo', ['cKey', 'kKey'], ['kSuchanfrage', (int)$sucheanfrage->kSuchanfrage], $upd);

                            $hinweis .= 'Die Suchanfrage "' . $suchanfragemapping_obj->cSuche .
                                '" wurde erfolgreich auf "' . $suchanfragemapping_obj->cSucheNeu . '" gemappt.<br />';
                        }
                    }
                } else {
                    $fehler .= 'Die Suchanfrage "' . $sucheanfrage->cSuche .
                        '" kann nicht auf den gleichen Suchebegriff gemappt werden.';
                }
            }
        }

        $hinweis .= 'Die Suchanfragen wurden erfolgreich aktualisiert.<br />';
    } elseif (isset($_POST['submitMapping'])) { // Auswahl mappen
        $cMapping = verifyGPDataString('cMapping');

        if (strlen($cMapping) > 0) {
            if (is_array($_POST['kSuchanfrage']) && count($_POST['kSuchanfrage']) > 0) {
                foreach ($_POST['kSuchanfrage'] as $kSuchanfrage) {
                    $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);

                    if ($oSuchanfrage->kSuchanfrage > 0) {
                        if (strtolower($oSuchanfrage->cSuche) !== strtolower($cMapping)) {
                            $oSuchanfrageNeu = Shop::DB()->select('tsuchanfrage', 'cSuche', Shop::DB()->escape($cMapping));
                            if (isset($oSuchanfrageNeu->kSuchanfrage) && $oSuchanfrageNeu->kSuchanfrage > 0) {
                                $oSuchanfrageMapping                 = new stdClass();
                                $oSuchanfrageMapping->kSprache       = $_SESSION['kSprache'];
                                $oSuchanfrageMapping->cSuche         = $oSuchanfrage->cSuche;
                                $oSuchanfrageMapping->cSucheNeu      = $cMapping;
                                $oSuchanfrageMapping->nAnzahlGesuche = $oSuchanfrage->nAnzahlGesuche;

                                $kSuchanfrageMapping = Shop::DB()->insert('tsuchanfragemapping', $oSuchanfrageMapping);

                                if ($kSuchanfrageMapping > 0) {
                                    Shop::DB()->query(
                                        "UPDATE tsuchanfrage
                                            SET nAnzahlGesuche = nAnzahlGesuche+" . $oSuchanfrage->nAnzahlGesuche . "
                                            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                                                AND kSuchanfrage = " . $oSuchanfrageNeu->kSuchanfrage, 4
                                    );
                                    Shop::DB()->delete('tsuchanfrage', 'kSuchanfrage', (int)$oSuchanfrage->kSuchanfrage);
                                    Shop::DB()->query(
                                        "UPDATE tseo
                                            SET kKey = " . (int)$oSuchanfrageNeu->kSuchanfrage . "
                                            WHERE cKey = 'kSuchanfrage'
                                                AND kKey = " . (int)$oSuchanfrage->kSuchanfrage, 4
                                    );

                                    $hinweis = 'Ihre markierten Suchanfragen wurden erfolgreich auf "' . $cMapping . '" gemappt.';
                                }
                            } else {
                                $fehler = 'Fehler: Sie haben versucht auf eine nicht existierende Suchanfrage zu mappen.';
                                break;
                            }
                        } else {
                            $fehler = 'Die Suchanfrage "' . $oSuchanfrage->cSuche . '" kann nicht auf den gleichen Suchebegriff gemappt werden.';
                            break;
                        }
                    } else {
                        $fehler = 'Fehler: Sie haben versucht eine nicht existierende Suchanfrage zu mappen.';
                        break;
                    }
                }
            } else {
                $fehler = 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.';
            }
        } else {
            $fehler = 'Fehler: Bitte geben Sie ein Mappingname an.';
        }
    } elseif (isset($_POST['delete'])) { // Auswahl loeschen
        if (is_array($_POST['kSuchanfrage'])) {
            foreach ($_POST['kSuchanfrage'] as $kSuchanfrage) {
                $kSuchanfrage_obj = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);
                $obj              = new stdClass();
                $obj->kSprache    = (int)$kSuchanfrage_obj->kSprache;
                $obj->cSuche      = $kSuchanfrage_obj->cSuche;

                Shop::DB()->delete('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);
                Shop::DB()->insert('tsuchanfrageblacklist', $obj);
                // Aus tseo loeschen
                Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', (int)$kSuchanfrage]);
                $hinweis .= 'Die Suchanfrage "' . $kSuchanfrage_obj->cSuche . '" wurde erfolgreich gel&ouml;scht.<br />';
                $hinweis .= 'Die Suchanfrage "' . $kSuchanfrage_obj->cSuche . '" wurde auf die Blacklist hinzugef&uuml;gt.<br />';
            }
        } else {
            $fehler .= 'Bitte w&auml;hlen Sie mindestens eine Suchanfrage aus.<br />';
        }
    }
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 2) { // Erfolglos mapping
    if (isset($_POST['erfolglosEdit'])) { // Editieren
        $smarty->assign('nErfolglosEditieren', 1);
    } elseif (isset($_POST['erfolglosUpdate'])) { // Update
        $Suchanfragenerfolglos = Shop::DB()->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            (int)$_SESSION['kSprache'],
            '*',
            'nAnzahlGesuche DESC'
        );
        if (count($Suchanfragenerfolglos) > 0) {
            foreach ($Suchanfragenerfolglos as $Suchanfrageerfolglos) {
                if (isset($_POST['mapping_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos]) &&
                    strlen($_POST['mapping_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos]) > 0) {
                    if (strtolower($Suchanfrageerfolglos->cSuche) != strtolower($_POST['mapping_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos])) {
                        if (strlen($_POST['mapping_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos]) > 0) {
                            $suchanfragemapping_obj                 = new stdClass();
                            $suchanfragemapping_obj->kSprache       = $_SESSION['kSprache'];
                            $suchanfragemapping_obj->cSuche         = $Suchanfrageerfolglos->cSuche;
                            $suchanfragemapping_obj->cSucheNeu      = $_POST['mapping_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos];
                            $suchanfragemapping_obj->nAnzahlGesuche = $Suchanfrageerfolglos->nAnzahlGesuche;

                            $oAlteSuche = Shop::DB()->select('tsuchanfrageerfolglos', 'cSuche', $suchanfragemapping_obj->cSuche);
                            if (isset($oAlteSuche->kSuchanfrageErfolglos) && $oAlteSuche->kSuchanfrageErfolglos > 0) {
                                Shop::DB()->insert('tsuchanfragemapping', $suchanfragemapping_obj);
                                Shop::DB()->delete('tsuchanfrageerfolglos', 'kSuchanfrageErfolglos', (int)$oAlteSuche->kSuchanfrageErfolglos);

                                $hinweis .= 'Die Suchanfrage "' . $suchanfragemapping_obj->cSuche .
                                    '" wurde erfolgreich auf "' . $suchanfragemapping_obj->cSucheNeu . '" gemappt.<br />';
                            }
                        }
                    } else {
                        $fehler .= 'Die Suchanfrage "' . $Suchanfrageerfolglos->cSuche .
                            '" kann nicht auf den gleichen Suchbegriff gemappt werden.';
                    }
                } elseif ((int)$_POST['nErfolglosEditieren'] === 1) {
                    $Suchanfrageerfolglos->cSuche = StringHandler::filterXSS($_POST['cSuche_' . $Suchanfrageerfolglos->kSuchanfrageErfolglos]);
                    $upd                          = new stdClass();
                    $upd->cSuche                  = $Suchanfrageerfolglos->cSuche;
                    Shop::DB()->update('tsuchanfrageerfolglos', 'kSuchanfrageErfolglos', (int)$Suchanfrageerfolglos->kSuchanfrageErfolglos, $upd);
                }
            }
        }
    } elseif (isset($_POST['erfolglosDelete'])) { // Loeschen
        $kSuchanfrageErfolglos_arr = $_POST['kSuchanfrageErfolglos'];
        if (is_array($kSuchanfrageErfolglos_arr) && count($kSuchanfrageErfolglos_arr) > 0) {
            foreach ($kSuchanfrageErfolglos_arr as $kSuchanfrageErfolglos) {
                $kSuchanfrageErfolglos = (int)$kSuchanfrageErfolglos;
                Shop::DB()->delete('tsuchanfrageerfolglos', 'kSuchanfrageErfolglos', (int)$kSuchanfrageErfolglos);
            }
            $hinweis = 'Ihre markierten Suchanfragen wurden erfolgreich gel&ouml;scht.';
        } else {
            $fehler = 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.';
        }
    }
    $smarty->assign('tab', 'erfolglos');
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 3) { // Blacklist
    $suchanfragenblacklist = $_POST['suchanfrageblacklist'];
    $suchanfragenblacklist = explode(';', $suchanfragenblacklist);
    $count                 = count($suchanfragenblacklist);

    Shop::DB()->delete('tsuchanfrageblacklist', 'kSprache', (int)$_SESSION['kSprache']);
    for ($i = 0; $i < $count; $i++) {
        if (!empty($suchanfragenblacklist[$i])) {
            $blacklist_obj           = new stdClass();
            $blacklist_obj->cSuche   = $suchanfragenblacklist[$i];
            $blacklist_obj->kSprache = (int)$_SESSION['kSprache'];
            Shop::DB()->insert('tsuchanfrageblacklist', $blacklist_obj);
        }
    }
    $smarty->assign('tab', 'blacklist');
    $hinweis .= 'Die Blacklist wurde erfolgreich aktualisiert.';
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 4) { // Mappinglist
    if (isset($_POST['delete'])) {
        if (is_array($_POST['kSuchanfrageMapping'])) {
            foreach ($_POST['kSuchanfrageMapping'] as $kSuchanfrageMapping) {
                $oSuchanfrageMapping = Shop::DB()->select('tsuchanfragemapping', 'kSuchanfrageMapping', (int)$kSuchanfrageMapping);
                if (isset($oSuchanfrageMapping->cSuche) && strlen($oSuchanfrageMapping->cSuche) > 0) {
                    Shop::DB()->delete('tsuchanfragemapping', 'kSuchanfrageMapping', (int)$kSuchanfrageMapping);
                    $hinweis .= 'Das Mapping "' . $oSuchanfrageMapping->cSuche . '" wurde erfolgreich gel&ouml;scht.<br />';
                } else {
                    $fehler .= 'Es wurde kein Mapping mit der ID "' . $kSuchanfrageMapping . '" gefunden.<br />';
                }
            }
        } else {
            $fehler .= 'Bitte w&auml;hlen Sie mindestens ein Mapping aus.<br />';
        }
    }
    $smarty->assign('tab', 'mapping');
}

$Sprachen = gibAlleSprachen();
// Anzahl Suchanfragen
$nAnzahlSuchanfragen = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM tsuchanfrage
        WHERE kSprache = " . (int)$_SESSION['kSprache'] . $cLivesucheSQL->cWhere, 1
);
// Anzahl Suchanfrageerfolglos
$nAnzahlSuchanfrageerfolglos = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM tsuchanfrageerfolglos
        WHERE kSprache = " . (int)$_SESSION['kSprache'], 1
);

// Anzahl SuchanfragenMapping
$nAnzahlSuchanfragenMapping = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM tsuchanfragemapping
        WHERE kSprache = " . (int)$_SESSION['kSprache'], 1
);

// Paginationen
$oPagiSuchanfragen = (new Pagination('suchanfragen'))
    ->setItemCount($nAnzahlSuchanfragen->nAnzahl)
    ->assemble();
$oPagiErfolglos = (new Pagination('erfolglos'))
    ->setItemCount($nAnzahlSuchanfrageerfolglos->nAnzahl)
    ->assemble();
$oPagiMapping = (new Pagination('mapping'))
    ->setItemCount($nAnzahlSuchanfragenMapping->nAnzahl)
    ->assemble();

$Suchanfragen = Shop::DB()->query(
    "SELECT tsuchanfrage.*, tseo.cSeo AS tcSeo
        FROM tsuchanfrage
        LEFT JOIN tseo ON tseo.cKey = 'kSuchanfrage'
            AND tseo.kKey = tsuchanfrage.kSuchanfrage
            AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
        WHERE tsuchanfrage.kSprache = " . (int)$_SESSION['kSprache'] . "
            " . $cLivesucheSQL->cWhere . "
        GROUP BY tsuchanfrage.kSuchanfrage
        ORDER BY " . $cLivesucheSQL->cOrder . "
        LIMIT " . $oPagiSuchanfragen->getLimitSQL(), 2
);

if (isset($Suchanfragen->tcSeo) && strlen($Suchanfragen->tcSeo) > 0) {
    $Suchanfragen->cSeo = $Suchanfragen->tcSeo;
}
unset($Suchanfragen->tcSeo);

$Suchanfragenerfolglos = Shop::DB()->query("
    SELECT *
        FROM tsuchanfrageerfolglos
        WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
        ORDER BY nAnzahlGesuche DESC
        LIMIT " . $oPagiErfolglos->getLimitSQL(), 2
);
$Suchanfragenblacklist = Shop::DB()->query("
    SELECT *
        FROM tsuchanfrageblacklist
        WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
        ORDER BY kSuchanfrageBlacklist", 2
);
$Suchanfragenmapping = Shop::DB()->query("
    SELECT *
        FROM tsuchanfragemapping
        WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
        LIMIT " . $oPagiMapping->getLimitSQL(), 2
);

// Config holen
$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenConf IN (" . implode(',', $settingsIDs) . ")
        ORDER BY nSort", 2
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconfwerte
            WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
            ORDER BY nSort", 2
    );
    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . (int)$oConfig_arr[$i]->kEinstellungenSektion . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );

    $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert) ? $oSetValue->cWert : null;
}
$smarty->assign('oConfig_arr', $oConfig_arr)
    ->assign('Sprachen', $Sprachen)
    ->assign('Suchanfragen', $Suchanfragen)
    ->assign('Suchanfragenerfolglos', $Suchanfragenerfolglos)
    ->assign('Suchanfragenblacklist', $Suchanfragenblacklist)
    ->assign('Suchanfragenmapping', $Suchanfragenmapping)
    ->assign('oPagiSuchanfragen', $oPagiSuchanfragen)
    ->assign('oPagiErfolglos', $oPagiErfolglos)
    ->assign('oPagiMapping', $oPagiMapping)
    ->assign('hinweis', $hinweis)
    ->assign('fehler', $fehler)
    ->display('livesuche.tpl');
