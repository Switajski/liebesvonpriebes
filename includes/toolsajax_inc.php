<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . 'toolsajax.common.php';
/** @global JTLSmarty $smarty */
/** @global xajax $xajax*/
if (!isset($xajax) || !$xajax) {
    $xajax = $GLOBALS['xajax'];
}
// zuweisen des Xajax Javascripts zu der Smarty-Variable $xajax_javascript
$smarty->assign('xajax_javascript', $xajax->getJavascript(Shop::getURL() . '/' . PFAD_XAJAX));
