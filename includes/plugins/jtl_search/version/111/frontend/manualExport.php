<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if (verifyGPDataString('a') === 'delexport') {
    require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
    require_once JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
    require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_INCLUDES . 'defines_inc.php';
    require_once JTLSEARCH_PFAD_CLASSES . 'class.Security.php';

    $oDebugger->doDebug(__FILE__ . ": Url:'" . verifyGPDataString('url') . "' p: '" . verifyGPDataString('p') . "'", JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
    $oSecurity = new Security($oServerSettings->cProjectId, $oServerSettings->cAuthHash);
    $oSecurity->setParam_arr(array('delexport', verifyGPDataString('url')));
    if ($oSecurity->createKey() == verifyGPDataString('p')) {
        $cURL_arr = parse_url(urldecode(verifyGPDataString('url')));
        if (isset($cURL_arr['path']) && strlen(substr($cURL_arr['path'], strrpos($cURL_arr['path'], '/') + 1)) > 3) {
            if (file_exists(JTLSEARCH_PFAD_EXPORTFILE_DIR . substr($cURL_arr['path'], strrpos($cURL_arr['path'], '/') + 1))) {
                ob_start();
                if (unlink(JTLSEARCH_PFAD_EXPORTFILE_DIR . substr($cURL_arr['path'], strrpos($cURL_arr['path'], '/') + 1))) {
                    ob_clean();
                    $oDebugger->doDebug(
                            __FILE__ . ': Datei gelöscht. (' . JTLSEARCH_PFAD_EXPORTFILE_DIR .
                            substr($cURL_arr['path'], strrpos($cURL_arr['path'], '/') + 1) . ')', JTLSEARCH_DEBUGGING_LEVEL_NOTICE
                    );
                } else {
                    $cWarning = ob_get_flush();
                    $oDebugger->doDebug(__FILE__ . ': ' . $cWarning, JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                }

                die('1');
            } else {
                $oDebugger->doDebug(
                        __FILE__ . ': Zu löschende Datei nicht vorhanden. (' .
                        JTLSEARCH_PFAD_EXPORTFILE_DIR . substr($cURL_arr['path'], strrpos($cURL_arr['path'], '/') + 1) . ')', JTLSEARCH_DEBUGGING_LEVEL_ERROR
                );
            }
        } else {
            $oDebugger->doDebug(
                    __FILE__ . ': Kein Dateiname vorhanden oder zu kurz. (' .
                    strlen(substr($cURL_arr['path'], strrpos($cURL_arr['path'], '/') + 1)) . ')', JTLSEARCH_DEBUGGING_LEVEL_ERROR
            );
        }
    } else {
        $oDebugger->doDebug(
                __FILE__ . ': Konnte Datei nicht löschen! a: ' . verifyGPDataString('a') . '; p: ' .
                verifyGPDataString('p') . '; url: ' . verifyGPDataString('url') . '; Security->createKey: ' . $oSecurity->createKey(), JTLSEARCH_DEBUGGING_LEVEL_ERROR
        );
    }
    die('0');
} elseif (isset($_GET['jtlsearch'])) {
    require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
    require_once JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
    $oAccount = new AdminAccount();

    if ($oAccount->logged()) {
        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_INCLUDES . 'defines_inc.php';
        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES . '/export/class.JTLSEARCH_Verwaltung_export.php';

        $oExport = new JTLSEARCH_Verwaltung_export($oDebugger);
        $oExport->doExport(intval($_GET['nExportMethod']));
    } else {
        $oDebugger->doDebug(__FILE__ . ': Nicht als Admin angemeldet.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        echo "Nicht als Admin angemeldet.";
    }
    die();
} elseif (isset($_GET['jtlsearchsetqueue'])) {
    require PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
    require JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
    $oAccount = new AdminAccount();

    if ($oAccount->logged()) {
        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_INCLUDES . 'defines_inc.php';
        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES . '/export/class.JTLSEARCH_Verwaltung_export.php';

        $oExport = new JTLSEARCH_Verwaltung_export($oDebugger);
        $oExport->newQueue(intval($_GET['jtlsearchsetqueue']));
    } else {
        $oDebugger->doDebug(__FILE__ . ': Nicht als Admin angemeldet.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        echo "Nicht als Admin angemeldet.";
    }
    die();
} elseif (isset($_POST['jtlsearch_change_cron'])) {
    require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
    require_once JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . "benutzerverwaltung_inc.php";
    $oAccount = new AdminAccount();

    if ($oAccount->logged()) {
        if (preg_match("/^([0-3]{1}[0-9]{1}[.]{1}[0-1]{1}[0-9]{1}[.]{1}[0-9]{4}[ ]{1}[0-2]{1}[0-9]{1}[:]{1}[0-6]{1}[0-9]{1})/", $_POST['dStart'])) {
            $cStart_arr = explode(' ', $_POST['dStart']);
            $nTime_arr  = explode(':', $cStart_arr[1]);
            $nDate_arr  = explode('.', $cStart_arr[0]);
            $dStart     = mktime($nTime_arr[0], $nTime_arr[1], 0, $nDate_arr[1], $nDate_arr[0], $nDate_arr[2]);
            Shop::DB()->query("
                UPDATE tcron 
                    SET dStart = FROM_UNIXTIME(" . $dStart . "), dLetzterStart = 0, dStartZeit = FROM_UNIXTIME(" . $dStart . ") 
                    WHERE cJobArt = 'JTLSearchExport'", 3
            );
            $oResult         = new stdClass();
            $oResult->bError = 0;
            $oResult->cDatum = date('d.m.Y', $dStart);
            $oResult->cZeit  = date('H:i', $dStart);

            die(json_encode($oResult));
        } else {
            $oResult = new stdClass();
            $oResult->bError = 1;
            $oResult->cMessage = "Bitte geben Sie ein g&uuml;ltiges Datum ein";
            die(json_encode($oResult));
        }
    } else {
        $oDebugger->doDebug(__FILE__ . ': Nicht als Admin angemeldet.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        echo "Nicht als Admin angemeldet.";
    }
    die();
}
