<?php

global $smarty, $oPlugin;

$stepPlugin = "sprachen";
$cHinweis = "";
$cFehler = "";

$oSprache_arr = $GLOBALS["DB"]->executeQuery("SELECT kSprache,cNameDeutsch FROM tsprache", 2);
$oKundengruppen_arr = $GLOBALS["DB"]->executeQuery("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
$oWaehrung_arr = $GLOBALS["DB"]->executeQuery("SELECT kWaehrung, cName FROM twaehrung", 2);

$oVersandLandIso = $GLOBALS["DB"]->executeQuery("SELECT cLaender FROM tversandart", 2);
$cVersandlandIso_arr = array();
foreach ($oVersandLandIso as $oVersandLandIso) {
    $cVersandlandIso_arr = array_merge($cVersandlandIso_arr, explode(' ', $oVersandLandIso->cLaender));
}
$cVersandlandIso_arr = array_unique($cVersandlandIso_arr);
sort($cVersandlandIso_arr);

if (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == $stepPlugin) {
    $oNewFeed = new stdClass();
    $oNewFeed->cName = trim($_POST['cName']);
    $oNewFeed->cDateiname = trim($_POST['cDateiname']);
    $oNewFeed->kSprache = intval($_POST['kSprache']);
    $oNewFeed->kKundengruppe = intval($_POST['kKundengruppe']);
    $oNewFeed->kWaehrung = intval($_POST['kWaehrung']);
    $oNewFeed->kPlugin = $oPlugin->kPlugin;
    $oNewFeed->cKopfzeile = " ";
    $oNewFeed->cContent = "PluginContentFile_googleShopping.php";
    $oNewFeed->cFusszeile = " ";
    if (strlen($oNewFeed->cName) > 0 && strlen($oNewFeed->cDateiname)) {
        $iRes = $GLOBALS['DB']->insertRow("texportformat", $oNewFeed);
        if ($iRes > 0) {
            $oConf_arr = $GLOBALS["DB"]->executeQuery("select cWertName from teinstellungenconf where kEinstellungenSektion=".intval(CONF_EXPORTFORMATE), 2);
            foreach ($oConf_arr as $oConf) {
                $oExportformatEinstellungen = new stdClass();
                $oExportformatEinstellungen->kExportformat = $iRes;
                $oExportformatEinstellungen->cName = $oConf->cWertName;
                if ($oConf->cWertName == "exportformate_lieferland") {
                    $oExportformatEinstellungen->cWert = $_POST["cLieferlandIso"];
                } else {
                    $oExportformatEinstellungen->cWert = "N";
                }
                $GLOBALS['DB']->insertRow("texportformateinstellungen", $oExportformatEinstellungen);
            }
        }
    }
}



$oExportformate = $GLOBALS["DB"]->executeQuery("
        SELECT
            texportformat.kExportformat,
            texportformat.cName,
            texportformat.cDateiname,
            texportformateinstellungen.cWert AS cLieferlandIso,
            tsprache.cNameDeutsch AS cSprache,
            tkundengruppe.cName AS cKundengruppe,
            twaehrung.cName AS cWaehrung
        FROM
            texportformat
        LEFT JOIN
            texportformateinstellungen ON texportformat.kExportformat = texportformateinstellungen.kExportformat AND texportformateinstellungen.cName = 'exportformate_lieferland'
        LEFT JOIN
            tsprache ON texportformat.kSprache = tsprache.kSprache
        LEFT JOIN
            tkundengruppe ON texportformat.kKundengruppe = tkundengruppe.kKundengruppe
        LEFT JOIN
            twaehrung ON texportformat.kWaehrung = twaehrung.kWaehrung
        WHERE kPlugin = {$oPlugin->kPlugin}", 2);


$smarty->assign("oExportformate", $oExportformate);
$smarty->assign("oSprache_arr", $oSprache_arr);
$smarty->assign("oKundengruppen_arr", $oKundengruppen_arr);
$smarty->assign("oWaehrung_arr", $oWaehrung_arr);
$smarty->assign("cVersandlandIso_arr", $cVersandlandIso_arr);

$smarty->assign("cHinweis", $cHinweis);
$smarty->assign("cFehler", $cFehler);
$smarty->assign("URL_SHOP", URL_SHOP);
$smarty->assign("PFAD_ROOT", PFAD_ROOT);
$smarty->assign("URL_ADMINMENU", URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);
$smarty->assign("stepPlugin", $stepPlugin);
print($smarty->fetch($oPlugin->cAdminmenuPfad . "templates/sprachen.tpl"));
