<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();

$phpInfo = '';
/** @global JTLSmarty $smarty */
if (isset($_GET['phpinfo'])) {
    if (in_array('phpinfo', explode(',', ini_get('disable_functions')), true)) {
        return;
    }
    ob_start();
    phpinfo();
    $content = ob_get_contents();
    ob_end_clean();

    $doc     = phpQuery::newDocumentHTML($content, JTL_CHARSET);
    $phpInfo = pq('body', $doc)->html();
}

$systemcheck = new Systemcheck_Environment();
$platform    = new Systemcheck_Platform_Hosting();

$smarty->assign('tests', $systemcheck->executeTestGroup('Shop4'))
       ->assign('platform', $platform)
       ->assign('passed', $systemcheck->getIsPassed())
       ->assign('phpinfo', $phpInfo)
       ->display('systemcheck.tpl');
