<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @deprecated since 4.06
 * TODO remove image prices in version 4.06+
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_PRICECHART_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis              = '';
$cFehler               = '';
$oPreisanzeigeConf_arr = [];
$oPreisanzeigeConf_arr = holePreisanzeigeEinstellungen();
// Update Preisanzeige
if (isset($_POST['update']) &&
    (int)$_POST['update'] === 1 &&
    is_array($oPreisanzeigeConf_arr) &&
    validateToken() &&
    count($oPreisanzeigeConf_arr) > 0
) {
    foreach ($oPreisanzeigeConf_arr as $oPreisanzeigeConf) {
        $upd = new stdClass();
        if (isset($oPreisanzeigeConf[0]->cName, $_POST[$oPreisanzeigeConf[0]->cName])) {
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[0]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[0]->cName], $upd);
        }
        if (isset($oPreisanzeigeConf[1]->cName, $_POST[$oPreisanzeigeConf[1]->cName])) {
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[1]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[1]->cName], $upd);
        }
        if (isset($oPreisanzeigeConf[2]->cName, $_POST[$oPreisanzeigeConf[2]->cName])) {
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[2]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[2]->cName], $upd);
        }
        if (isset($oPreisanzeigeConf[3]->cName, $_POST[$oPreisanzeigeConf[3]->cName])) {
            $upd = new stdClass();
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[3]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[3]->cName], $upd);
        }
    }

    unset($GLOBALS['Einstellungen']['preisverlauf']);
    $oPreisanzeigeConf_arr = holePreisanzeigeEinstellungen();
    $cHinweis .= 'Ihre Einstellungen wurde erfolgreich gespeichert.';

    Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION]);
}
// Hole Fonts
$cFont_arr = [];
$dir       = PFAD_ROOT . PFAD_FONTS;
if (is_dir($dir)) {
    $dir_handle = opendir($dir);
    while (false !== ($file = readdir($dir_handle))) {
        if ($file !== '..' && $file !== '.' && $file[0] !== '.') {
            $cFont_arr[] = $file;
        }
    }
    closedir($dir_handle);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('cFont_arr', $cFont_arr)
       ->assign('oPreisanzeigeConf_arr', $oPreisanzeigeConf_arr)
       ->assign('cSektion_arr', array_keys($oPreisanzeigeConf_arr))
       ->display('preisanzeige.tpl');
