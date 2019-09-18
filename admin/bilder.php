<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_BILDER]);
$shopSettings  = Shopsetting::getInstance();
$cHinweis      = '';
$cFehler       = '';
if (isset($_POST['speichern'])) {
    $cHinweis .= saveAdminSectionSettings(CONF_BILDER, $_POST);
    MediaImage::clearCache('product');
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
    $shopSettings->reset();
}

$oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_BILDER, '*', 'nSort');
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
    $oSetValue = Shop::DB()->select('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_BILDER, $oConfig_arr[$i]->cWertName]);
    $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert) ? $oSetValue->cWert : null;
}
$Einstellungen = Shop::getSettings([CONF_BILDER]);

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oConfig', $Einstellungen['bilder'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('bilder.tpl');
