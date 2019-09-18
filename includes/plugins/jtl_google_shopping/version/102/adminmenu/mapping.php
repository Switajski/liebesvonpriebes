<?php
global $smarty, $oPlugin;

$stepPlugin = "mapping";
$cHinweis = "";
$cFehler = "";
if (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == $stepPlugin) {
    if (isset($_POST['btn_delete'])) {
        $kMapping = key($_POST['btn_delete']);
        $oRes = $GLOBALS["DB"]->executeQuery("DELETE FROM xplugin_".$oPlugin->cPluginID."_mapping WHERE kMapping = ". $kMapping, 10);
        $cHinweis = 'Mapping mit der ID: '. $kMapping.' wurde erfolgreich gelöscht.';
    } elseif (isset($_POST['btn_save_new'])) {
        if (isset($_POST['cType']) && strlen($_POST['cType']) > 0) {
            if (isset($_POST['cVon']) && strlen($_POST['cVon']) > 0 && isset($_POST['cZu'.$_POST['cType']]) && strlen($_POST['cZu'.$_POST['cType']]) > 0) {
                $cSQL = "INSERT INTO xplugin_".$oPlugin->cPluginID."_mapping
                    (cVon, cZu, cType) VALUES
                    ('".$GLOBALS["DB"]->realEscape(strtolower($_POST['cVon']))."', '".$GLOBALS["DB"]->realEscape(strtolower($_POST['cZu'.$_POST['cType']]))."', '".$GLOBALS["DB"]->realEscape($_POST['cType'])."')";
                $GLOBALS["DB"]->executeQuery($cSQL, 10);
            } else {
                $cFehler = 'Leere Felder Können nicht gespeichert werden.';
            }
        } else {
            $cFehler = 'Es muss ein Typ ausgewählt werden.<br />';
        }
        if (empty($cFehler)) {
            $cHinweis = "Daten wurden erfolgreich hinzugefügt";
        }
    }
}

$oRes = $GLOBALS["DB"]->executeQuery("SELECT * FROM xplugin_".$oPlugin->cPluginID."_mapping ORDER BY cType, cZu", 9);

$smarty->assign('mapping_arr', $oRes);

$smarty->assign("cHinweis", $cHinweis);
$smarty->assign("cFehler", $cFehler);
$smarty->assign("URL_SHOP", URL_SHOP);
$smarty->assign("PFAD_ROOT", PFAD_ROOT);
$smarty->assign("URL_ADMINMENU", URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);
$smarty->assign("stepPlugin", $stepPlugin);
print($smarty->fetch($oPlugin->cAdminmenuPfad . "templates/mapping.tpl"));
