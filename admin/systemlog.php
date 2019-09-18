<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';

$oAccount->permission('SYSTEMLOG_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis          = '';
$cFehler           = '';
$cSuche            = '';
$nLevel            = 0;
$step              = 'systemlog_uebersicht';

if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
if (strlen(verifyGPDataString('cSucheEncode')) > 0) {
    $cSuche = urldecode(verifyGPDataString('cSucheEncode'));
}
if (strlen(verifyGPDataString('cSuche')) > 0) {
    $cSuche = verifyGPDataString('cSuche');
}
if (strlen(verifyGPCDataInteger('nLevel')) > 0) {
    $nLevel = verifyGPCDataInteger('nLevel');
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1 && validateToken()) {
    Shop::DB()->delete('teinstellungen', ['kEinstellungenSektion', 'cName'], [1, 'systemlog_flag']);
    $ins                        = new stdClass();
    $ins->kEinstellungenSektion = 1;
    $ins->cName                 = 'systemlog_flag';
    $ins->cWert                 = (isset($_POST['nFlag']) && count($_POST['nFlag']) > 0)
        ? Jtllog::setBitFlag(array_map('cleanSystemFlag', $_POST['nFlag']))
        : 0;
    Shop::DB()->insert('teinstellungen', $ins);
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);

    $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
} elseif (isset($_POST['a']) && $_POST['a'] === 'del' && validateToken()) {
    Jtllog::deleteAll();
    $cHinweis = 'Ihr Systemlog wurde erfolgreich gel&ouml;scht.';
}

if ($step === 'systemlog_uebersicht') {
    $nLogCount = Jtllog::getLogCount($cSuche, $nLevel);
    // Pagination
    $oPagination = (new Pagination())
        ->setItemCount($nLogCount)
        ->assemble();
    // Log
    $oLog_arr = Jtllog::getLog($cSuche, $nLevel, $oPagination->getFirstPageItem(), $oPagination->getPageItemCount());
    /** @var Jtllog $oLog */
    foreach ($oLog_arr as &$oLog) {
        $cLog = $oLog->getcLog();
        $cLog = preg_replace('/\[(.*)\] => (.*)/', '<span class="hl_key">$1</span>: <span class="hl_value">$2</span>', $cLog);
        $cLog = str_replace(['(', ')'], ['<span class="hl_brace">(</span>', '<span class="hl_brace">)</span>'], $cLog);

        $oLog->setcLog($cLog, false);
    }

    $nSystemlogFlag                 = getSytemlogFlag(false);
    $nFlag_arr[JTLLOG_LEVEL_ERROR]  = Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $nSystemlogFlag);
    $nFlag_arr[JTLLOG_LEVEL_NOTICE] = Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $nSystemlogFlag);
    $nFlag_arr[JTLLOG_LEVEL_DEBUG]  = Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $nSystemlogFlag);

    $smarty->assign('oLog_arr', $oLog_arr)
           ->assign('oPagination', $oPagination)
           ->assign('nFlag_arr', $nFlag_arr)
           ->assign('JTLLOG_LEVEL_ERROR', JTLLOG_LEVEL_ERROR)
           ->assign('JTLLOG_LEVEL_NOTICE', JTLLOG_LEVEL_NOTICE)
           ->assign('JTLLOG_LEVEL_DEBUG', JTLLOG_LEVEL_DEBUG);
}
/**
 * @param $nFlag
 * @return int
 */
function cleanSystemFlag($nFlag)
{
    return (int)$nFlag;
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('cSucheEncode', (isset($cSucheEncode) ? urlencode($cSucheEncode) : null))
       ->assign('cSuche', $cSuche)
       ->assign('nLevel', $nLevel)
       ->assign('step', $step)
       ->display('systemlog.tpl');
