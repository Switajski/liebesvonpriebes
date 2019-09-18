<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once '../../../../../globalinclude.php';

Session::getInstance();

$oPlugin = Plugin::getPluginById('jtl_search');
$cQuery  = trim($_POST['query']);

if ($oPlugin !== null && $oPlugin->kPlugin > 0 && strlen($cQuery) > 0) {
    if (!$oPlugin->getConf('cProjectId')) {
        $oObj_arr = Shop::DB()->query('SELECT * FROM tjtlsearchserverdata', 2);
        foreach ($oObj_arr as $oObj) {
            if (isset($oObj->cKey) && strlen($oObj->cKey) > 0) {
                switch ($oObj->cKey) {
                    case 'cProjectId':
                        $oPlugin->setConf('cProjectId', $oObj->cValue);
                        break;

                    case 'cAuthHash':
                        $oPlugin->setConf('cAuthHash', $oObj->cValue);
                        break;

                    case 'cServerUrl':
                        $oPlugin->setConf('cServerUrl', $oObj->cValue);
                        break;
                }
            }
        }
    }

    if (strlen($oPlugin->getConf('cProjectId')) > 0 && strlen($oPlugin->getConf('cAuthHash')) > 0 && strlen($oPlugin->getConf('cServerUrl')) > 0) {
        require_once "{$oPlugin->cFrontendPfad}../includes/defines_inc.php";
        require_once "{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php";

        echo JtlSearch::doSuggestForward($cQuery, $_SESSION['cISOSprache'], $oPlugin->getConf('cProjectId'), $oPlugin->getConf('cAuthHash'), urldecode($oPlugin->getConf('cServerUrl')));
    }
}
