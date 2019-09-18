<?php
require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
require JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';

$stepPlugin                                = 'settings';
$cHinweis                                  = '';
$cFehler                                   = '';
$oSettings_arr                             = new stdClass();
$oSettings_arr->jtlsearch_suggest_align    = (isset($oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'])) ? $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'] : 'left';
$oSettings_arr->jtlsearch_export_languages = array();
//load available languages
$oLanguage_arr    = Shop::DB()->query("SELECT cISO, cNameDeutsch, cShopStandard FROM tsprache ORDER BY cShopStandard DESC, cNameDeutsch", 2);
$cLanguageISO_arr = array();
if (is_array($oLanguage_arr)) {
    foreach ($oLanguage_arr as $oLanguage) {
        $cLanguageISO_arr[] = $oLanguage->cISO;
    }
}

//save new config
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
    Shop::DB()->query("UPDATE tplugineinstellungen SET cWert = '{$oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align']}' WHERE cName = 'jtlsearch_suggest_align' AND kPlugin = {$oPlugin->kPlugin}", 3);
    $oSettings_arr->jtlsearch_suggest_align = $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'];

    //save export languages
    if (is_array($_POST['jtlsearch_export_languages'])) {
        $bFirst = true;
        foreach ($_POST['jtlsearch_export_languages'] as $cLanguage) {
            if (in_array($cLanguage, $cLanguageISO_arr)) {
                if ($bFirst === true) {
                    $cLanguageQuery = "INSERT INTO tjtlsearchexportlanguage (`cISO`) VALUES ('{$cLanguage}')";
                    $bFirst         = false;
                } else {
                    $cLanguageQuery .= ", ('{$cLanguage}')";
                }
            }
        }
        if ($bFirst === false) {
            foreach ($oLanguage_arr as $oLanguage) {
                if ($oLanguage->cShopStandard === 'Y' && !strpos($cLanguageQuery, "('{$oLanguage->cISO}')")) {
                    $cLanguageQuery .= ", ('{$oLanguage->cISO}')";
                }
            }
            Shop::DB()->query('TRUNCATE TABLE tjtlsearchexportlanguage', 3);
            Shop::DB()->query($cLanguageQuery, 3);
        }
    }
}

//save languages to export
$oExportLanguage = Shop::DB()->query("SELECT cISO FROM tjtlsearchexportlanguage", 2);
if (is_array($oExportLanguage)) {
    foreach ($oExportLanguage as $oLanguage) {
        $oSettings_arr->jtlsearch_export_languages[] = $oLanguage->cISO;
    }
}

Shop::Smarty()->assign('oLanguage_arr', $oLanguage_arr)
    ->assign('oSettings_arr', $oSettings_arr)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('URL_SHOP', Shop::getURL())
    ->assign('PFAD_ROOT', PFAD_ROOT)
    ->assign('URL_ADMINMENU', Shop::getURL() . '/' . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_ADMINMENU)
    ->assign('stepPlugin', $stepPlugin)
    ->display(JTLSEARCH_PFAD_ADMINMENU_SETTINGS_TEMPLATES . 'settings.tpl');
