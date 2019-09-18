<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

$oAccount->permission('FILECHECK_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis     = '';
$cFehler      = '';
$oDatei_arr   = [];
$nStat_arr    = [];
$nReturnValue = getAllFiles($oDatei_arr, $nStat_arr);

if ($nReturnValue !== 1) {
    switch ($nReturnValue) {
        case 2:
            $cFehler = 'Fehler: Die Datei mit der aktuellen Dateiliste existiert nicht.';
            break;
        case 3:
            $cFehler = 'Fehler: Die Datei mit der aktuellen Dateiliste ist leer.';
            break;
        default:
            $cFehler = '';
            break;
    }
}
$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('oDatei_arr', $oDatei_arr)
       ->assign('nStat_arr', $nStat_arr)
       ->assign('JTL_VERSION', JTL_VERSION)
       ->display('filecheck.tpl');
