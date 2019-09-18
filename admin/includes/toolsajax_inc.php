<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . 'toolsajax.server.php';
/**
 * @global JTLSmarty $smarty
 * @global xajax     $xajax
 */
$smarty->assign('xajax_javascript', $xajax->getJavascript(Shop::getURL() . '/' . PFAD_XAJAX));
