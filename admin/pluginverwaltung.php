<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';

$reload   = false;
$cHinweis = '';
$cFehler  = '';
$step     = 'pluginverwaltung_uebersicht';
if (isset($_SESSION['plugin_msg'])) {
    $cHinweis = $_SESSION['plugin_msg'];
    unset($_SESSION['plugin_msg']);
} elseif (strlen(verifyGPDataString('h')) > 0) {
    $cHinweis = StringHandler::filterXSS(base64_decode(verifyGPDataString('h')));
}
if (!empty($_FILES['file_data'])) {
    /**
     * sanitize names from plugins downloaded via gitlab
     *
     * @param array $p_event
     * @param array $p_header
     * @return int
     */
    function pluginPreExtractCallBack($p_event, &$p_header)
    {
        //plugins downloaded from gitlab have -[BRANCHNAME]-[COMMIT_ID] in their name.
        //COMMIT_ID should be 40 characters
        preg_match('/(.*)-master-([a-zA-Z0-9]{40})\/(.*)/', $p_header['filename'], $hits);
        if (count($hits) >= 3) {
            $p_header['filename'] = str_replace('-master-' . $hits[2], '', $p_header['filename']);
        }

        return 1;
    }

    require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';

    $response                 = new stdClass();
    $response->status         = 'OK';
    $response->error          = null;
    $response->files_unpacked = [];
    $response->file_failed    = [];
    $response->messages       = [];

    $zip     = new PclZip($_FILES['file_data']['tmp_name']);
    $content = $zip->listContent();
    if (!is_array($content) || !isset($content[0]['filename']) || strpos($content[0]['filename'], '.') !== false) {
        $response->status     = 'FAILED';
        $response->messages[] = 'Invalid archive';
    } else {
        $unzipPath = PFAD_ROOT . PFAD_PLUGIN;
        $res       = $zip->extract(PCLZIP_OPT_PATH, $unzipPath, PCLZIP_CB_PRE_EXTRACT, 'pluginPreExtractCallBack');
        $success   = [];
        $fail      = [];
        if ($res !== 0) {
            foreach ($res as $_file) {
                if ($_file['status'] === 'ok' || $_file['status'] === 'already_a_directory') {
                    $response->files_unpacked[] = $_file;
                } else {
                    $response->file_failed[] = $_file;
                }
            }
        } else {
            $response->status   = 'FAILED';
            $response->errors[] = 'Got unzip error code: ' . $zip->errorCode();
        }
    }
    $PluginInstalliertByStatus_arr = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    $PluginInstalliert_arr = gibInstalliertePlugins();
    $allPlugins            = gibAllePlugins($PluginInstalliert_arr);
    foreach ($PluginInstalliert_arr as $_plugin) {
        $PluginInstalliertByStatus_arr['status_' . $_plugin->nStatus][] = $_plugin;
    }
    $PluginVerfuebar_arr  = $allPlugins->verfuegbar;
    $PluginFehlerhaft_arr = $allPlugins->fehlerhaft;
    // Version mappen und Update (falls vorhanden) anzeigen
    if (count($PluginInstalliert_arr) > 0) {
        /**
         * @var int $i
         * @var Plugin $PluginInstalliert
         */
        foreach ($PluginInstalliert_arr as $i => $PluginInstalliert) {
            $nVersion = $PluginInstalliert->getCurrentVersion();
            if ($nVersion > $PluginInstalliert->nVersion) {
                $nReturnValue                       = pluginPlausi($PluginInstalliert->kPlugin);
                $PluginInstalliert_arr[$i]->dUpdate = number_format((float)$nVersion / 100, 2);

                if ($nReturnValue === 1 || $nReturnValue === 90) {
                    $PluginInstalliert_arr[$i]->cUpdateFehler = 1;
                } else {
                    $PluginInstalliert_arr[$i]->cUpdateFehler =
                        StringHandler::htmlentities(mappePlausiFehler($nReturnValue, $PluginInstalliert));
                }
            }
            $PluginInstalliert_arr[$i]->dVersion = number_format((float)$PluginInstalliert->nVersion / 100, 2);
            $PluginInstalliert_arr[$i]->cStatus  = $PluginInstalliert->mapPluginStatus($PluginInstalliert->nStatus);
        }
    }

    $errorCount = count($PluginInstalliertByStatus_arr['status_3']) +
        count($PluginInstalliertByStatus_arr['status_4']) +
        count($PluginInstalliertByStatus_arr['status_5']) +
        count($PluginInstalliertByStatus_arr['status_6']);

    $smarty->configLoad('german.conf', 'pluginverwaltung')
           ->assign('PluginInstalliertByStatus_arr', $PluginInstalliertByStatus_arr)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $PluginInstalliert_arr)
           ->assign('PluginVerfuebar_arr', $PluginVerfuebar_arr)
           ->assign('PluginFehlerhaft_arr', $PluginFehlerhaft_arr);

    $response->html                   = new stdClass();
    $response->html->verfuegbar       = utf8_encode($smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl'));
    $response->html->verfuegbar_count = count($PluginVerfuebar_arr);
    $response->html->fehlerhaft       = utf8_encode($smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl'));
    $response->html->fehlerhaft_count = count($PluginFehlerhaft_arr);
    die(json_encode($response));
}

if (verifyGPCDataInteger('pluginverwaltung_uebersicht') === 1 && validateToken()) {
    // Eine Aktion wurde von der Uebersicht aus gestartet
    $kPlugin_arr = isset($_POST['kPlugin']) ? $_POST['kPlugin'] : [];

    // Lizenzkey eingeben
    if (isset($_POST['lizenzkey']) && (int)$_POST['lizenzkey'] > 0) {
        $kPlugin = (int)$_POST['lizenzkey'];
        $step    = 'pluginverwaltung_lizenzkey';
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        $smarty->assign('oPlugin', $oPlugin)
               ->assign('kPlugin', $kPlugin);
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } elseif (isset($_POST['lizenzkeyadd']) &&
        (int)$_POST['lizenzkeyadd'] === 1 &&
        (int)$_POST['kPlugin'] > 0 &&
        validateToken()
    ) { // Lizenzkey eingeben
        $step    = 'pluginverwaltung_lizenzkey';
        $kPlugin = (int)$_POST['kPlugin'];
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
            $oPlugin = new Plugin($kPlugin, true);
            require_once $oPlugin->cLicencePfad . $oPlugin->cLizenzKlasseName;
            $oPluginLicence = new $oPlugin->cLizenzKlasse();
            $cLicenceMethod = PLUGIN_LICENCE_METHODE;
            if ($oPluginLicence->$cLicenceMethod(StringHandler::filterXSS($_POST['cKey']))) {
                $oPlugin->cFehler = '';
                $oPlugin->nStatus = 2;
                $oPlugin->cLizenz = StringHandler::filterXSS($_POST['cKey']);
                $oPlugin->updateInDB();
                $cHinweis = 'Ihr Plugin-Lizenzschl&uuml;ssel wurde gespeichert.';
                $step     = 'pluginverwaltung_uebersicht';
                $reload   = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                aenderPluginZahlungsartStatus($oPlugin, 1);
            } else {
                $cFehler = 'Fehler: Ihr Lizenzschl&uuml;ssel ist ung&uuml;ltig.';
            }
        } else {
            $cFehler = 'Fehler: Ihr Plugin wurde nicht in der Datenbank gefunden.';
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        $smarty->assign('kPlugin', $kPlugin)
               ->assign('oPlugin', $oPlugin);
    } elseif (is_array($kPlugin_arr) && count($kPlugin_arr) > 0 && validateToken()) {
        foreach ($kPlugin_arr as $kPlugin) {
            $kPlugin = (int)$kPlugin;
            // Aktivieren
            if (isset($_POST['aktivieren'])) {
                $nReturnValue = aktivierePlugin($kPlugin);

                switch ($nReturnValue) {
                    case 1: // Alles O.K. Plugin wurde aktiviert
                        if ($cHinweis !== 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich aktiviert.') {
                            $cHinweis .= 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich aktiviert.';
                        }
                        $reload = true;
                        break;
                    case 2: // $kPlugin wurde nicht uebergeben
                        $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
                        break;
                    case 3: // SQL Fehler bzw. Plugin nicht gefunden
                        $cFehler = 'Fehler: Ihr ausgew&auml;hltes Plugin konnte nicht in der Datenbank gefunden werden oder ist schon aktiv.';
                        break;
                }

                if ($nReturnValue > 3) {
                    $cFehler = mappePlausiFehler($nReturnValue, null);
                }
            } elseif (isset($_POST['deaktivieren'])) { // Deaktivieren
                $nReturnValue = deaktivierePlugin($kPlugin);

                switch ($nReturnValue) {
                    case 1: // Alles O.K. Plugin wurde deaktiviert
                        if ($cHinweis !== 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich deaktiviert.') {
                            $cHinweis .= 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich deaktiviert.';
                        }
                        $reload = true;
                        break;
                    case 2: // $kPlugin wurde nicht uebergeben
                        $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
                        break;
                    case 3: // SQL Fehler bzw. Plugin nicht gefunden
                        $cFehler = 'Fehler: Ihr ausgew&auml;hltes Plugin konnte nicht in der Datenbank gefunden werden.';
                        break;
                }
            } elseif (isset($_POST['deinstallieren'])) { // Deinstallieren
                $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $nReturnValue = deinstallierePlugin($kPlugin, $oPlugin->nXMLVersion);

                    switch ($nReturnValue) {
                        case 1: // Alles O.K. Plugin wurde deinstalliert
                            $cHinweis = 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich deinstalliert.';
                            $reload   = true;
                            break;
                        case 2: // $kPlugin wurde nicht uebergeben
                            $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
                            break;
                        case 3: // SQL Fehler bzw. Plugin nicht gefunden
                            $cFehler = 'Fehler: Plugin konnte aufgrund eines SQL-Fehlers nicht deinstalliert werden.';
                            break;
                        case 4: // SQL Fehler bzw. Plugin nicht gefunden
                            $cFehler = 'Fehler: Plugin wurde nicht in der Datenbank gefunden.';
                            break;
                    }
                } else {
                    $cFehler = 'Fehler: Ein oder mehrere Plugins wurden nicht in der Datenbank gefunden.';
                }
            } elseif (isset($_POST['reload'])) { // Reload
                $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);

                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $nReturnValue = reloadPlugin($oPlugin, true);

                    if ($nReturnValue === 1 || $nReturnValue === 126) {
                        $cHinweis = 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich neu geladen.';
                        $reload = true;
                    } else {
                        $cFehler = 'Fehler: Ein Plugin konnte nicht neu geladen werden.';
                    }
                } else {
                    $cFehler = 'Fehler: Ein oder mehrere Plugins wurden nicht in der Datenbank gefunden.';
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX]);
    } elseif (verifyGPCDataInteger('updaten') === 1 && validateToken()) { // Updaten
        $kPlugin      = verifyGPCDataInteger('kPlugin');
        $nReturnValue = updatePlugin($kPlugin);
        if ($nReturnValue === 1) {
            $cHinweis .= 'Ihr Plugin wurde erfolgreich geupdated.';
            $reload = true;
            Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
            //header('Location: pluginverwaltung.php?h=' . base64_encode($cHinweis));
        } elseif ($nReturnValue > 1) {
            $cFehler = 'Fehler: Beim Update ist ein Fehler aufgetreten. Fehlercode: ' . $nReturnValue;
        }
    } elseif (verifyGPCDataInteger('sprachvariablen') === 1) { // Sprachvariablen editieren
        $step = 'pluginverwaltung_sprachvariablen';
    } elseif (isset($_POST['installieren']) && validateToken()) {
        // Installieren
        // ### pluginPlausiIntern
        // 1    = Alles O.K.
        // 2    = $cVerzeichnis wurde nicht uebergeben
        // 3    = info.xml existiert nicht
        // 4    = Plugin wurde schon installiert
        // 6    = Der Pluginname entspricht nicht der Konvention
        // 7    = Die PluginID entspricht nicht der Konvention
        // 8    = Der Installationsknoten ist nicht vorhanden
        // 9    = Erste Versionsnummer entspricht nicht der Konvention
        // 10   = Die Versionsnummer entspricht nicht der Konvention
        // 11   = Das Versionsdatum entspricht nicht der Konvention
        // 12   = SQL Datei fuer die aktuelle Version existiert nicht
        // 13   = Keine Hooks vorhanden
        // 14   = Die Hook Werte entsprechen nicht den Konventionen
        // 15   = CustomLink Name entspricht nicht der Konvention
        // 16   = CustomLink Dateiname entspricht nicht der Konvention
        // 17   = CustomLink Datei existiert nicht
        // 18   = EinstellungsLink Name entspricht nicht der Konvention
        // 19   = Einstellungen fehlen
        // 20   = Einstellungen type entspricht nicht der Konvention
        // 21   = Einstellungen initialValue entspricht nicht der Konvention
        // 22   = Einstellungen sort entspricht nicht der Konvention
        // 23   = Einstellungen Name entspricht nicht der Konvention
        // 24   = Keine SelectboxOptionen vorhanden
        // 25   = Die Option entspricht nicht der Konvention
        // 26   = Keine Sprachvariablen vorhanden
        // 27   = Variable Name entspricht nicht der Konvention
        // 28   = Keine lokalisierte Sprachvariable vorhanden
        // 29   = Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
        // 30   = Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention
        // 31   = Die Hook Datei ist nicht vorhanden
        // 32   = Version existiert nicht im Versionsordner
        // 33   = Einstellungen conf entspricht nicht der Konvention
        // 34   = Einstellungen ValueName entspricht nicht der Konvention
        // 35   = XML Version entspricht nicht der Konvention
        // 36   = Shop Version entspricht nicht der Konvention
        // 37   = Shop Version ist zu niedrig
        // 38   = Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde
        // 39   = Link Filename entspricht nicht der Konvention
        // 40   = LinkName entspricht nicht der Konvention
        // 41   = Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention
        // 42   = Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention
        // 43   = Die ISO der Linksprache entspricht nicht der Konvention
        // 44   = Der Seo Name entspricht nicht der Konvention
        // 45   = Der Name entspricht nicht der Konvention
        // 46   = Der Title entspricht nicht der Konvention
        // 47   = Der MetaTitle entspricht nicht der Konvention
        // 48   = Die MetaKeywords entsprechen nicht der Konvention
        // 49   = Die MetaDescription entspricht nicht der Konvention
        // 50   = Der Name in den Zahlungsmethoden entspricht nicht der Konvention
        // 51   = Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention
        // 52   = TSCode in den Zahlungsmethoden entspricht nicht der Konvention
        // 53   = PreOrder in den Zahlungsmethoden entspricht nicht der Konvention
        // 54   = ClassFile in den Zahlungsmethoden entspricht nicht der Konvention
        // 55   = Die Datei fuer die Klasse der Zahlungsmethode existiert nicht
        // 56   = TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
        // 57   = Die Datei fuer das Template der Zahlungsmethode existiert nicht
        // 58   = Keine Sprachen in den Zahlungsmethoden hinterlegt
        // 59   = Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention
        // 60   = Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention
        // 61   = Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention
        // 62   = Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention
        // 63   = Zahlungsmethode Einstellungen type entspricht nicht der Konvention
        // 64   = Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention
        // 65   = Zahlungsmethode Einstellungen sort entspricht nicht der Konvention
        // 66   = Zahlungsmethode Einstellungen conf entspricht nicht der Konvention
        // 67   = Zahlungsmethode Einstellungen Name entspricht nicht der Konvention
        // 68   = Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention
        // 69   = Keine SelectboxOptionen vorhanden
        // 70   = Die Option entspricht nicht der Konvention
        // 71   = Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention
        // 72   = Soap in den Zahlungsmethoden entspricht nicht der Konvention
        // 73   = Curl in den Zahlungsmethoden entspricht nicht der Konvention
        // 74 	= Sockets in den Zahlungsmethoden entspricht nicht der Konvention
        // 75	= ClassName in den Zahlungsmethoden entspricht nicht der Konvention
        // 76	= Der Templatename entspricht nicht der Konvention
        // 77	= Die Templatedatei fuer den Frontend Link existiert nicht
        // 78	= Es darf nur ein Templatename oder ein Fullscreen Templatename existieren
        // 79	= Der Fullscreen Templatename entspricht nicht der Konvention
        // 80	= Die Fullscreen Templatedatei fuer den Frontend Link existiert nicht
        // 81	= fuer ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden
        // 82 	= Keine Box vorhanden
        // 83 	= Box Name entspricht nicht der Konvention
        // 84 	= Box Templatedatei entspricht nicht der Konvention
        // 85 	= Box Templatedatei existiert nicht
        // 86	= Lizenzklasse existiert nicht
        // 87	= Name der Lizenzklasse entspricht nicht der konvention
        // 88	= Lizenklasse ist nicht definiert
        // 89	= Methode checkLicence in der Lizenzklasse ist nicht definiert
        // 90	= PluginID bereits in der Datenbank vorhanden
        // 91	= Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde
        // 92	= Template Name entspricht nicht der Konvention
        // 93	= Template Type entspricht nicht der Konvention
        // 94 	= Template ModulId entspricht nicht der Konvention
        // 95 	= Template Active entspricht nicht der Konvention
        // 96 	= Template AKZ entspricht nicht der Konvention
        // 97 	= Template AGB entspricht nicht der Konvention
        // 98	= Template WRB entspricht nicht der Konvention
        // 99	= Die ISO der Emailtemplate Sprache entspricht nicht der Konvention
        // 100	= Der Subject Name entspricht nicht der Konvention
        // 101	= Keine Templatesprachen vorhanden

        // ### installierePlugin
        // 152  = Main Plugindaten nicht korrekt
        // 153  = Ein Hook konnte nicht in die Datenbank gespeichert werden
        // 154  = Ein Adminmenue Customlink konnte nicht in die Datenbank gespeichert werden
        // 155  = Ein Adminmenue Settingslink konnte nicht in die Datenbank gespeichert werden
        // 156  = Eine Einstellung konnte nicht in die Datenbank gespeichert werden
        // 157  = Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
        // 158  = Ein Link konnte nicht in die Datenbank gespeichert werden
        // 159  = Eine Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
        // 160  = Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
        // 161  = Eine Einstellung der Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
        // 162	= Es konnte keine Linkgruppe im Shop gefunden werden
        // 163 	= Eine Boxvorlage konnte nicht in die Datenbank gespeichert werden
        // 164	= Eine Emailvorlage konnte nicht in die Datenbank gespeichert werden
        // 165	= Ein AdminWidget konnte nicht in die Datenbank gespeichert werden

        // ### logikSQLDatei
        // 202  = Plugindaten fehlen
        // 203  = SQL hat einen Fehler verursacht
        // 204  = Versuch eine nicht Plugintabelle zu loeschen
        // 205  = Versuch eine nicht Plugintabelle anzulegen
        // 206  = SQL Datei ist leer oder konnte nicht geparsed werden
        // 207  = Sync Uebergabeparameter nicht korrekt
        // 208  = Update konnte nicht gesynct werden
        $cVerzeichnis_arr = $_POST['cVerzeichnis'];

        if (is_array($cVerzeichnis_arr) && count($cVerzeichnis_arr) > 0) {
            foreach ($cVerzeichnis_arr as $cVerzeichnis) {
                $nReturnValue = installierePluginVorbereitung(basename($cVerzeichnis));
                if ($nReturnValue === 1 || $nReturnValue === 126) {
                    $cHinweis = 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich installiert.';
                    $reload   = true;
                } elseif ($nReturnValue > 1 && $nReturnValue !== 126) {
                    $cFehler = 'Fehler: Bei der Installation ist ein Fehler aufgetreten. Fehlercode: ' . $nReturnValue;
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } else {
        $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
    }
} elseif (verifyGPCDataInteger('pluginverwaltung_sprachvariable') === 1 && validateToken()) { // Plugin Sprachvariablen
    $step = 'pluginverwaltung_sprachvariablen';
    if (verifyGPCDataInteger('kPlugin') > 0) {
        $kPlugin = verifyGPCDataInteger('kPlugin');
        // Zuruecksetzen
        if (verifyGPCDataInteger('kPluginSprachvariable') > 0) {
            $oPluginSprachvariable = Shop::DB()->select(
                'tpluginsprachvariable',
                'kPlugin',
                $kPlugin,
                'kPluginSprachvariable',
                verifyGPCDataInteger('kPluginSprachvariable')
            );
            if (isset($oPluginSprachvariable->kPluginSprachvariable) && $oPluginSprachvariable->kPluginSprachvariable > 0) {
                $nRow = Shop::DB()->delete(
                    'tpluginsprachvariablecustomsprache',
                    ['kPlugin', 'cSprachvariable'],
                    [$kPlugin, $oPluginSprachvariable->cName]
                );
                if ($nRow >= 0) {
                    $cHinweis = 'Sie haben den Installationszustand der ausgew&auml;hlten Variable erfolgreich wiederhergestellt.';
                } else {
                    $cFehler = 'Fehler: Ihre ausgew&auml;hlte Sprachvariable wurde nicht gefunden.';
                }
            } else {
                $cFehler = 'Fehler: Die Sprachvariable konnte nicht gefunden werden.';
            }
        } else { // Editieren
            $oSprache_arr              = Shop::DB()->query("SELECT * FROM tsprache", 2);
            $oPluginSprachvariable_arr = gibSprachVariablen($kPlugin);
            if (count($oSprache_arr) > 0) {
                foreach ($oSprache_arr as $oSprache) {
                    if (count($oPluginSprachvariable_arr) > 0) {
                        foreach ($oPluginSprachvariable_arr as $oPluginSprachvariable) {
                            $kPluginSprachvariable = $oPluginSprachvariable->kPluginSprachvariable;
                            $cSprachvariable       = $oPluginSprachvariable->cName;
                            $cISO                  = strtoupper($oSprache->cISO);

                            if (strlen($_POST[$kPluginSprachvariable . '_' . $cISO]) >= 0) {
                                Shop::DB()->delete(
                                    'tpluginsprachvariablecustomsprache',
                                    ['kPlugin', 'cSprachvariable', 'cISO'],
                                    [$kPlugin, $cSprachvariable, $cISO]
                                );
                                $oPluginSprachvariableCustomSprache                        = new stdClass();
                                $oPluginSprachvariableCustomSprache->kPlugin               = $kPlugin;
                                $oPluginSprachvariableCustomSprache->cSprachvariable       = $cSprachvariable;
                                $oPluginSprachvariableCustomSprache->cISO                  = $cISO;
                                $oPluginSprachvariableCustomSprache->kPluginSprachvariable = $kPluginSprachvariable;
                                $oPluginSprachvariableCustomSprache->cName                 = $_POST[$kPluginSprachvariable . '_' . $cISO];

                                Shop::DB()->insert('tpluginsprachvariablecustomsprache', $oPluginSprachvariableCustomSprache);
                            }
                        }
                    }
                }
            }
            $cHinweis = 'Ihre &Auml;nderungen wurden erfolgreich &uuml;bernommen.';
            $step     = 'pluginverwaltung_uebersicht';
            $reload   = true;
        }
        Shop::Cache()->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);
    }
}

if ($step === 'pluginverwaltung_uebersicht') {
    $PluginInstalliertByStatus_arr = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    $PluginInstalliert_arr = gibInstalliertePlugins();
    $allPlugins            = gibAllePlugins($PluginInstalliert_arr);
    foreach ($PluginInstalliert_arr as $_plugin) {
        $PluginInstalliertByStatus_arr['status_' . $_plugin->nStatus][] = $_plugin;
    }
    $PluginVerfuebar_arr  = $allPlugins->verfuegbar;
    $PluginFehlerhaft_arr = $allPlugins->fehlerhaft;
    // Version mappen und Update (falls vorhanden) anzeigen
    if (count($PluginInstalliert_arr) > 0) {
        /**
         * @var int $i
         * @var Plugin $PluginInstalliert
         */
        foreach ($PluginInstalliert_arr as $i => $PluginInstalliert) {
            $nVersion = $PluginInstalliert->getCurrentVersion();
            if ($nVersion > $PluginInstalliert->nVersion) {
                $nReturnValue                       = pluginPlausi($PluginInstalliert->kPlugin);
                $PluginInstalliert_arr[$i]->dUpdate = number_format((float)$nVersion / 100, 2);

                $PluginInstalliert_arr[$i]->cUpdateFehler = ($nReturnValue === 1 || $nReturnValue === 90)
                    ? 1
                    : StringHandler::htmlentities(mappePlausiFehler($nReturnValue, $PluginInstalliert));
            }
            $PluginInstalliert_arr[$i]->dVersion = number_format((float)$PluginInstalliert->nVersion / 100, 2);
            $PluginInstalliert_arr[$i]->cStatus  = $PluginInstalliert->mapPluginStatus($PluginInstalliert->nStatus);
        }
    }

    if (count($PluginVerfuebar_arr) > 0) {
        foreach ($PluginVerfuebar_arr as $i => $PluginVerfuebar) {
            // searching for multiple names of license files (e.g. LICENSE.md or License.md and so on)
            $szFolder = PFAD_ROOT . PFAD_PLUGIN . $PluginVerfuebar_arr[$i]->cVerzeichnis . '/';
            $vPossibleLicenseNames = [
                  '',
                  'license.md',
                  'License.md',
                  'LICENSE.md'
            ];
            $j = count($vPossibleLicenseNames) -1;
            for (; $j !== 0 && !file_exists($szFolder.$vPossibleLicenseNames[$j]); $j--) {
                // we're only couting up to our find
            }
            // only if we found something, we add it to our array
            if ('' !== $vPossibleLicenseNames[$j]) {
                $vLicenseFiles[$PluginVerfuebar_arr[$i]->cVerzeichnis] = $szFolder.$vPossibleLicenseNames[$j];
            }
        }
        if (!empty($vLicenseFiles)) {
            $smarty->assign('szLicenses', json_encode($vLicenseFiles));
        }
    }
    $errorCount = count($PluginInstalliertByStatus_arr['status_3']) +
        count($PluginInstalliertByStatus_arr['status_4']) +
        count($PluginInstalliertByStatus_arr['status_5']) +
        count($PluginInstalliertByStatus_arr['status_6']);

    $smarty->assign('PluginInstalliertByStatus_arr', $PluginInstalliertByStatus_arr)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $PluginInstalliert_arr)
           ->assign('PluginVerfuebar_arr', $PluginVerfuebar_arr)
           ->assign('PluginFehlerhaft_arr', $PluginFehlerhaft_arr)
           ->assign('PluginIndex_arr', $allPlugins->index);
} elseif ($step === 'pluginverwaltung_sprachvariablen') { // Sprachvariablen
    $kPlugin      = verifyGPCDataInteger('kPlugin');
    $oSprache_arr = Shop::DB()->query("SELECT * FROM tsprache", 2);

    $smarty->assign('oSprache_arr', $oSprache_arr)
           ->assign('kPlugin', $kPlugin)
           ->assign('oPluginSprachvariable_arr', gibSprachVariablen($kPlugin));
}

if ($reload === true) {
    $_SESSION['plugin_msg'] = $cHinweis;
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'pluginverwaltung.php', true, 303);
    exit();
}
if (defined('PLUGIN_DEV_MODE') && PLUGIN_DEV_MODE === true) {
    $pluginDevNotice = 'Ihr Shop befindet sich im Plugin-Entwicklungsmodus. ' .
        '&Auml;nderungen an der XML-Datei eines aktivierten Plugins bewirken ein automatisches Update.';
    $cHinweis        = empty($cHinweis)
        ? $pluginDevNotice
        : $pluginDevNotice . '<br>' . $cHinweis;
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('hinweis64', base64_encode($cHinweis))
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('pluginverwaltung.tpl');
