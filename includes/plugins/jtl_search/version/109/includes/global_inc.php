<?php

if (!isset($oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_installed_' . $oPlugin->nVersion]) ||
    $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_installed_' . $oPlugin->nVersion] != 1
) {
    require_once 'install_inc.php';
}

//Debugger erstellen
require_once JTLSEARCH_PFAD_CLASSES . 'class.Debugger.php';
$oDebugger = new Debugger();

$oServerinfo = Shop::DB()->query("SELECT cKey, cValue FROM tjtlsearchserverdata WHERE cKey = 'cServerUrl' OR cKey = 'cAuthHash' OR cKey = 'cProjectId'", 2);
if (count($oServerinfo) > 0) {
    $oServerSettings = new stdClass();
    foreach ($oServerinfo as $oServerSetting) {
        $oServerSettings->{$oServerSetting->cKey} = $oServerSetting->cValue;
    }
    //$oDebugger->doDebug('oServerSettings: '. var_export($oServerSettings, true), JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
}
