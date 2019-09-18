<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PERMISSIONCHECK_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$oFsCheck = new Systemcheck_Platform_Filesystem(PFAD_ROOT); // to get all folders which need to be writable

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('cDirAssoc_arr', $oFsCheck->getFoldersChecked())
       ->assign('oStat', $oFsCheck->getFolderStats())
       ->display('permissioncheck.tpl');
