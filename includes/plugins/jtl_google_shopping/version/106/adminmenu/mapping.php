<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

$stepPlugin = 'mapping';
$cHinweis   = '';
$cFehler    = '';
if (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == $stepPlugin) {
    if (isset($_POST['btn_delete'])) {
        $kMapping = key($_POST['btn_delete']);
        $oRes     = Shop::DB()->query("DELETE FROM xplugin_" . $oPlugin->cPluginID . "_mapping WHERE kMapping = " . $kMapping, 3);
        $cHinweis = 'Mapping mit der ID: ' . $kMapping . ' wurde erfolgreich gel&ouml;scht.';
    } elseif (isset($_POST['btn_save_new'])) {
        if (isset($_POST['cType']) && strlen($_POST['cType']) > 0) {
            if (isset($_POST['cVon']) && strlen($_POST['cVon']) > 0 && isset($_POST['cZu' . $_POST['cType']]) && strlen($_POST['cZu' . $_POST['cType']]) > 0) {
                $cSQL = "INSERT INTO xplugin_" . $oPlugin->cPluginID . "_mapping
                    (cVon, cZu, cType) VALUES
                    ('" . Shop::DB()->realEscape(strtolower($_POST['cVon'])) . "',
                    '" . Shop::DB()->realEscape(strtolower($_POST['cZu' . $_POST['cType']])) . "',
                    '" . Shop::DB()->realEscape($_POST['cType']) . "')";
                Shop::DB()->query($cSQL, 10);
            } else {
                $cFehler = 'Leere Felder k&ouml;nnen nicht gespeichert werden.';
            }
        } else {
            $cFehler = 'Es muss ein Typ ausgew&auml;hlt werden.<br />';
        }
        if (empty($cFehler)) {
            $cHinweis = 'Daten wurden erfolgreich hinzugef&uuml;gt';
        }
    }
}

$oRes = Shop::DB()->query("SELECT * FROM xplugin_" . $oPlugin->cPluginID . "_mapping ORDER BY cType, cZu", 9);

Shop::Smarty()->assign('mapping_arr', $oRes)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('URL_SHOP', Shop::getURL())
    ->assign('PFAD_ROOT', PFAD_ROOT)
    ->assign('URL_ADMINMENU', Shop::getURL() . '/' . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_ADMINMENU)
    ->assign('stepPlugin', $stepPlugin)
    ->display($oPlugin->cAdminmenuPfad . 'templates/mapping.tpl');
