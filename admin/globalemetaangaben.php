<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_GLOBAL_META_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_METAANGABEN]);
$chinweis      = '';
$cfehler       = '';
setzeSprache();

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1 && validateToken()) {
    saveAdminSectionSettings(CONF_METAANGABEN, $_POST);

    $cTitle           = $_POST['Title'];
    $cMetaDesc        = $_POST['Meta_Description'];
    $cMetaKeys        = $_POST['Meta_Keywords'];
    $cMetaDescPraefix = $_POST['Meta_Description_Praefix'];
    Shop::DB()->delete(
        'tglobalemetaangaben',
        ['kSprache', 'kEinstellungenSektion'],
        [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
    );
    // Title
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Title';
    $oGlobaleMetaAngaben->cWertName             = $cTitle;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Description
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Description';
    $oGlobaleMetaAngaben->cWertName             = $cMetaDesc;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Keywords
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Keywords';
    $oGlobaleMetaAngaben->cWertName             = $cMetaKeys;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Description Präfix
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Description_Praefix';
    $oGlobaleMetaAngaben->cWertName             = $cMetaDescPraefix;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    Shop::Cache()->flushAll();
    $chinweis .= 'Ihre Einstellungen wurden &uuml;bernommen.<br />';
    unset($oConfig_arr);
}

$oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_METAANGABEN, '*', 'nSort');
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::DB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_METAANGABEN,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert) ? $oSetValue->cWert : null);
}

$oMetaangaben_arr = Shop::DB()->selectAll(
    'tglobalemetaangaben',
    ['kSprache', 'kEinstellungenSektion'],
    [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
);
$cTMP_arr         = [];
if (is_array($oMetaangaben_arr) && count($oMetaangaben_arr) > 0) {
    foreach ($oMetaangaben_arr as $oMetaangaben) {
        $cTMP_arr[$oMetaangaben->cName] = $oMetaangaben->cWertName;
    }
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oMetaangaben_arr', $cTMP_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $chinweis)
       ->assign('fehler', $cfehler)
       ->display('globalemetaangaben.tpl');
