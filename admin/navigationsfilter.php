<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SETTINGS_NAVIGATION_FILTER_VIEW', true, true);

$Einstellungen = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
$cHinweis      = '';
$cFehler       = '';

setzeSprache();

if (isset($_POST['speichern']) && validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NAVIGATIONSFILTER, $_POST);
    Shop::Cache()->flushTags([CACHING_GROUP_CATEGORY]);
    if (is_array($_POST['nVon'])
        && is_array($_POST['nBis'])
        && count($_POST['nVon']) > 0
        && count($_POST['nBis']) > 0
    ) {
        // Tabelle leeren
        Shop::DB()->query("TRUNCATE TABLE tpreisspannenfilter", 3);

        foreach ($_POST['nVon'] as $i => $nVon) {
            $nVon = (float)$nVon;
            $nBis = (float)$_POST['nBis'][$i];

            if ($nVon >= 0 && $nBis >= 0) {
                Shop::DB()->insert('tpreisspannenfilter', (object)['nVon' => $nVon, 'nBis' => $nBis]);
            }
        }
    }
}

$oConfig_arr = Shop::DB()->selectAll(
    'teinstellungenconf',
    'kEinstellungenSektion',
    CONF_NAVIGATIONSFILTER,
    '*',
    'nSort'
);

foreach ($oConfig_arr as $oConfig) {
    if ($oConfig->cInputTyp === 'selectbox') {
        $oConfig->ConfWerte = Shop::DB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::DB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_NAVIGATIONSFILTER,
        'cName',
        $oConfig->cWertName
    );
    $oConfig->gesetzterWert = isset($oSetValue->cWert) ? $oSetValue->cWert : null;
}

$oPreisspannenfilter_arr = Shop::DB()->query("SELECT * FROM tpreisspannenfilter", 2);

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oPreisspannenfilter_arr', $oPreisspannenfilter_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('navigationsfilter.tpl');
