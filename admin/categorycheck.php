<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();
/** @global JTLSmarty $smarty */

$status = Status::getInstance();
$orphanedCategories = $status->getOrphanedCategories(false);

$smarty->assign('passed', count($orphanedCategories) === 0)
       ->assign('cateogries', $orphanedCategories)
       ->display('categorycheck.tpl');
