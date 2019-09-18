<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_SITEMAP]);
$cHinweis      = '';
$cFehler       = '';

setzeSprache();

if (isset($_POST['speichern']) && validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_SITEMAP, $_POST);
    if (isset($_POST['nVon']) && is_array($_POST['nVon']) && count($_POST['nVon']) > 0 &&
        is_array($_POST['nBis']) && count($_POST['nBis']) > 0) {
        // Tabelle leeren
        Shop::DB()->query("TRUNCATE TABLE tpreisspannenfilter", 3);
        for ($i = 0; $i < 10; $i++) {
            // Neue Werte in die DB einfuegen
            if ((int)$_POST['nVon'][$i] >= 0 && (int)$_POST['nBis'][$i] > 0) {
                $oPreisspannenfilter       = new stdClass();
                $oPreisspannenfilter->nVon = (int)$_POST['nVon'][$i];
                $oPreisspannenfilter->nBis = (int)$_POST['nBis'][$i];

                Shop::DB()->insert('tpreisspannenfilter', $oPreisspannenfilter);
            }
        }
    }
}

$oConfig_arr = Shop::DB()->selectAll(
    'teinstellungenconf',
    'kEinstellungenSektion',
    CONF_SITEMAP,
    '*',
    'nSort'
);
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
        CONF_SITEMAP,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert)
        ? $oSetValue->cWert
        : null;
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopsitemap.tpl');
