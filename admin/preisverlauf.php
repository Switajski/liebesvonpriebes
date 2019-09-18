<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_PRICECHART_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cfehler  = '';

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_PREISVERLAUF, $_POST);
}

$oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_PREISVERLAUF, '*', 'nSort');
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll('teinstellungenconfwerte', 'kEinstellungenConf', $oConfig_arr[$i]->kEinstellungenConf, '*', 'nSort');
    }

    $oSetValue = Shop::DB()->select('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISVERLAUF, $oConfig_arr[$i]->cWertName]);
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert) ? $oSetValue->cWert : null);
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cfehler)
       ->display('preisverlauf.tpl');

/**
 * @param string $cFarbCode
 * @return string
 */
function checkeFarbCode($cFarbCode)
{
    if (preg_match('/#[A-Fa-f0-9]{6}/', $cFarbCode) == 1) {
        return $cFarbCode;
    }
    $GLOBALS['cfehler'] = 'Bitte den Farbcode in folgender Schreibweise angeben: z.B. #FFFFFF';

    return '#000000';
}
