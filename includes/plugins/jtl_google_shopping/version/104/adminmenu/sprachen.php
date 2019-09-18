<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

global $oPlugin;

$stepPlugin = 'sprachen';
$cHinweis   = '';
$cFehler    = '';

$oSprache_arr        = Shop::DB()->query("SELECT kSprache,cNameDeutsch FROM tsprache", 2);
$oKundengruppen_arr  = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
$oWaehrung_arr       = Shop::DB()->query("SELECT kWaehrung, cName FROM twaehrung", 2);
$oVersandLandIso     = Shop::DB()->query("SELECT cLaender FROM tversandart", 2);
$cVersandlandIso_arr = array();
foreach ($oVersandLandIso as $oVersandLandIso) {
    $cVersandlandIso_arr = array_merge($cVersandlandIso_arr, explode(' ', $oVersandLandIso->cLaender));
}
$cVersandlandIso_arr = array_unique($cVersandlandIso_arr);
sort($cVersandlandIso_arr);

if (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == $stepPlugin) {
    $oNewFeed                = new stdClass();
    $oNewFeed->cName         = trim($_POST['cName']);
    $oNewFeed->cDateiname    = trim($_POST['cDateiname']);
    $oNewFeed->kSprache      = intval($_POST['kSprache']);
    $oNewFeed->kKundengruppe = intval($_POST['kKundengruppe']);
    $oNewFeed->kWaehrung     = intval($_POST['kWaehrung']);
    $oNewFeed->kPlugin       = $oPlugin->kPlugin;
    $oNewFeed->cKopfzeile    = ' ';
    $oNewFeed->cContent      = 'PluginContentFile_googleShopping.php';
    $oNewFeed->cFusszeile    = ' ';
    if (strlen($oNewFeed->cName) > 0 && strlen($oNewFeed->cDateiname)) {
        $iRes = Shop::DB()->insert('texportformat', $oNewFeed);
        if ($iRes > 0) {
            $oConf_arr = Shop::DB()->query("
                SELECT cWertName
                    FROM teinstellungenconf
                    WHERE kEinstellungenSektion = " . intval(CONF_EXPORTFORMATE), 2
            );
            foreach ($oConf_arr as $oConf) {
                $oExportformatEinstellungen                = new stdClass();
                $oExportformatEinstellungen->kExportformat = $iRes;
                $oExportformatEinstellungen->cName         = $oConf->cWertName;
                if ($oConf->cWertName === 'exportformate_lieferland') {
                    $oExportformatEinstellungen->cWert = $_POST['cLieferlandIso'];
                } else {
                    $oExportformatEinstellungen->cWert = 'N';
                }
                Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
            }
        }
    }
}

$oExportformate = Shop::DB()->query(
    "SELECT
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
            texportformateinstellungen ON texportformat.kExportformat = texportformateinstellungen.kExportformat
                AND texportformateinstellungen.cName = 'exportformate_lieferland'
        LEFT JOIN
            tsprache ON texportformat.kSprache = tsprache.kSprache
        LEFT JOIN
            tkundengruppe ON texportformat.kKundengruppe = tkundengruppe.kKundengruppe
        LEFT JOIN
            twaehrung ON texportformat.kWaehrung = twaehrung.kWaehrung
        WHERE kPlugin = {$oPlugin->kPlugin}", 2
);

Shop::Smarty()->assign('oExportformate', $oExportformate)
    ->assign('oSprache_arr', $oSprache_arr)
    ->assign('oKundengruppen_arr', $oKundengruppen_arr)
    ->assign('oWaehrung_arr', $oWaehrung_arr)
    ->assign('cVersandlandIso_arr', $cVersandlandIso_arr)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('URL_SHOP', Shop::getURL())
    ->assign('PFAD_ROOT', PFAD_ROOT)
    ->assign('URL_ADMINMENU', Shop::getURL() . '/' . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_ADMINMENU)
    ->assign('stepPlugin', $stepPlugin)
    ->display($oPlugin->cAdminmenuPfad . 'templates/sprachen.tpl');
