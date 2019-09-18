<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JTLSmarty.php';
$smarty = JTLSmarty::getInstance(false, false);
executeHook(HOOK_SMARTY_INC);
