<?php

// Helper functions
if (!function_exists('json_encode')) {
    function json_encode($cData)
    {
        if (!class_exists('Services_JSON')) {
            require_once(PFAD_ROOT . PFAD_CLASSES . "class.JSON.php");
        }

        $oJSON = new Services_JSON;
        return $oJSON->encode($cData);
    }
}

if (!function_exists('json_decode')) {
    function json_decode($cData)
    {
        if (!class_exists('Services_JSON')) {
            require_once(PFAD_ROOT . PFAD_CLASSES . "class.JSON.php");
        }

        $oJSON = new Services_JSON;
        return $oJSON->decode($cData);
    }
}

//Datenbankverbindung erstellen
require_once(JTLSEARCH_PFAD_CLASSES.'class.JTLSearchDB.php');
require_once(JTLSEARCH_PFAD_CLASSES.'class.JTLSearchDBInfo.php');

$oDBInfo = new JTLSearchDBInfo(DB_HOST, DB_USER, DB_PASS, DB_NAME, 'latin1');
$oDB = new JTLSearchDB($oDBInfo);


if (!isset($oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_installed_'.$oPlugin->nVersion]) || $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_installed_'.$oPlugin->nVersion] != 1) {
    require_once('install_inc.php');
}

//Debugger erstellen
require_once(JTLSEARCH_PFAD_CLASSES.'class.Debugger.php');
$oDebugger = new Debugger();

$oServerinfo = $oDB->getAsObject("SELECT cKey, cValue FROM tjtlsearchserverdata WHERE cKey = 'cServerUrl' OR cKey = 'cAuthHash' OR cKey = 'cProjectId'", 2);
if (count($oServerinfo) > 0) {
    $oServerSettings = new stdClass();
    foreach ($oServerinfo as $oServerSetting) {
        $oServerSettings->{$oServerSetting->cKey} = $oServerSetting->cValue;
    }
    $oDebugger->doDebug('oServerSettings: '. var_export($oServerSettings, true), JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
}
