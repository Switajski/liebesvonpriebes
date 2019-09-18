<?php
/**
 *-------------------------------------------------------------------------------
 *	JTL-Shop 3
 *	File: suggest.php, php file
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
$session = new Session();

//erstelle $smarty
require_once(PFAD_ROOT . PFAD_INCLUDES . "smartyInclude.php");

$kPlugin = gibkPluginAuscPluginID('jtl_search');
$cSearch = utf8_decode(trim($_POST['k']));

if (intval($kPlugin) > 0 && strlen($cSearch) >= 3) {
    $oPlugin = new Plugin($kPlugin);
    $cTemplatePath = $oPlugin->cFrontendPfad . PFAD_PLUGIN_TEMPLATE;
    $cTemplateResult = $cTemplatePath . 'results.tpl';
    
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
    
        $oResponse = null;
        $cSearch_arr = JtlSearch::doSuggest($_SESSION['Kundengruppe']->kKundengruppe, $_SESSION['cISOSprache'], $_SESSION['Waehrung']->cISO, $cSearch, $oPlugin->getConf("cProjectId"), $oPlugin->getConf("cAuthHash"), urldecode($oPlugin->getConf("cServerUrl")), $oResponse);
        
        foreach ($cSearch_arr as &$cTmp) {
            foreach ($cTmp->oItem_arr as &$oItem) {
                $oItem->cName = utf8_decode($oItem->cName);
            }
        }

        $oResponse->oSuggest->cSuggest = utf8_decode($oResponse->oSuggest->cSuggest);

        if (is_array($cSearch_arr) && count($cSearch_arr) > 0) {
            $GLOBALS['smarty']->assign('cSearch', $cSearch);
            $GLOBALS['smarty']->assign('cSearch_arr', $cSearch_arr);
            $GLOBALS['smarty']->assign('cTemplatePath', $cTemplatePath);
            $GLOBALS['smarty']->assign('oSearchResponse', $oResponse);
            
            echo $GLOBALS['smarty']->fetch($cTemplateResult);
        }
    }
}
