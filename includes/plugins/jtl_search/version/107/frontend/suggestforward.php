<?php
/**
 *-------------------------------------------------------------------------------
 *	JTL-Shop 3
 *	File: suggestforward.php, php file
 *
 *	JTL-Shop 3
 *
 * Do not use, modify or sell this code without permission / licence.
 *
 * @author JTL-Software <daniel.boehmer@jtl-software.de>
 * @copyright 2010, JTL-Software
 * @link http://jtl-software.de/jtlshop.php
 * @version 1.0
 *-------------------------------------------------------------------------------
 */

require_once("../../../../../globalinclude.php");

//session starten
@session_destroy();
$session = new Session();

$kPlugin = gibkPluginAuscPluginID('jtl_search');
$cQuery = trim($_POST['query']);

if (intval($kPlugin) > 0 && strlen($cQuery) > 0) {
    $oPlugin = new Plugin($kPlugin);
    
    if (!$oPlugin->getConf("cProjectId")) {
        $oObj_arr = $GLOBALS['DB']->executeQuery("SELECT * FROM tjtlsearchserverdata", 2);
        foreach ($oObj_arr as $oObj) {
            if (isset($oObj->cKey) && strlen($oObj->cKey) > 0) {
                switch ($oObj->cKey) {
                    case "cProjectId":
                        $oPlugin->setConf("cProjectId", $oObj->cValue);
                        break;
    
                    case "cAuthHash":
                        $oPlugin->setConf("cAuthHash", $oObj->cValue);
                        break;
                            
                    case "cServerUrl":
                        $oPlugin->setConf("cServerUrl", $oObj->cValue);
                        break;
                }
            }
        }
    }
    
    if (strlen($oPlugin->getConf("cProjectId")) > 0 && strlen($oPlugin->getConf("cAuthHash")) > 0 && strlen($oPlugin->getConf("cServerUrl")) > 0) {
        require_once("{$oPlugin->cFrontendPfad}../includes/defines_inc.php");
        require_once("{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php");
                
        echo JtlSearch::doSuggestForward($cQuery, $_SESSION['cISOSprache'], $oPlugin->getConf("cProjectId"), $oPlugin->getConf("cAuthHash"), urldecode($oPlugin->getConf("cServerUrl")));
    }
}
