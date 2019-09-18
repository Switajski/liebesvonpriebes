<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
require_once __DIR__ . '/includes/plz_ort_import_inc.php';

$oAccount->permission('PLZ_ORT_IMPORT_VIEW', true, true);

$cAction  = 'index';
$messages = [
    'notice' => '',
    'error'  => '',
];

plzimportActionIndex($smarty, $messages);
plzimportFinalize($cAction, $smarty, $messages);
