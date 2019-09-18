<?php
global $smarty, $oPlugin;

require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion.'/includes/defines_inc.php');
require(JTLSEARCH_PFAD_INCLUDES.'global_inc.php');

$stepPlugin = "settings";
$cHinweis = "";
$cFehler = "";
$oSettings_arr = new stdClass();
$oSettings_arr->jtlsearch_suggest_align     = $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'];
$oSettings_arr->jtlsearch_export_languages  = array();

//Verfügbare Sprachen laden
$oLanguage_arr = $oDB->getAsObject("SELECT cISO, cNameDeutsch, cShopStandard FROM tsprache ORDER BY cShopStandard DESC, cNameDeutsch", 2);
$cLanguageISO_arr = array();
if (is_array($oLanguage_arr)) {
    foreach ($oLanguage_arr as $oLanguage) {
        array_push($cLanguageISO_arr, $oLanguage->cISO);
    }
}

//Neue Einstellungen speichern
if (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == $stepPlugin) {
    //Suggest-Ausrichtung speichern
    switch ($_POST['jtlsearch_suggest_align']) {
        case 'right':
            $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'] = 'right';
            break;
        case 'center':
            $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'] = 'center';
            break;
        case 'left':
        default:
            $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'] = 'left';
            break;
    }
    $oDB->execSQL("UPDATE tplugineinstellungen SET cWert = '{$oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align']}' WHERE cName = 'jtlsearch_suggest_align' AND kPlugin = {$oPlugin->kPlugin}");
    $oSettings_arr->jtlsearch_suggest_align     = $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'];
    
    
    //Exportsprachen speichern
    if (is_array($_POST['jtlsearch_export_languages'])) {
        $bFirst = true;
        foreach ($_POST['jtlsearch_export_languages'] as $cLanguage) {
            if (in_array($cLanguage, $cLanguageISO_arr)) {
                if ($bFirst == true) {
                    $cLanguageQuery = "INSERT INTO tjtlsearchexportlanguage (`cISO`) VALUES ('{$cLanguage}')";
                    $bFirst = false;
                } else {
                    $cLanguageQuery .= ", ('{$cLanguage}')";
                }
            }
        }
        if ($bFirst == false) {
            foreach ($oLanguage_arr as $oLanguage) {
                if ($oLanguage->cShopStandard == 'Y' && !strpos($cLanguageQuery, "('{$oLanguage->cISO}')")) {
                    $cLanguageQuery .= ", ('{$oLanguage->cISO}')";
                }
            }
            $oDB->execSQL('TRUNCATE TABLE tjtlsearchexportlanguage');
            $oDB->execSQL($cLanguageQuery);
        }
    }
}

//Zu Exportierende Sprachen Laden
$oExportLanguage = $oDB->getAsObject("SELECT cISO FROM tjtlsearchexportlanguage", 2);
if (is_array($oExportLanguage)) {
    foreach ($oExportLanguage as $oLanguage) {
        array_push($oSettings_arr->jtlsearch_export_languages, $oLanguage->cISO);
    }
}

$smarty->assign('oLanguage_arr', $oLanguage_arr);
$smarty->assign('oSettings_arr', $oSettings_arr);

$smarty->assign("cHinweis", $cHinweis);
$smarty->assign("cFehler", $cFehler);
$smarty->assign("URL_SHOP", URL_SHOP);
$smarty->assign("PFAD_ROOT", PFAD_ROOT);
$smarty->assign("URL_ADMINMENU", URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);
$smarty->assign("stepPlugin", $stepPlugin);
print($smarty->fetch(JTLSEARCH_PFAD_ADMINMENU_SETTINGS_TEMPLATES . "settings.tpl"));
