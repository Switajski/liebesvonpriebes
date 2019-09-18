<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once '../../../../../globalinclude.php';

//session starten
Session::getInstance();

//erstelle $smarty
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$oPlugin = Plugin::getPluginById('jtl_search');
$cSearch = utf8_decode(trim($_POST['k']));

if ($oPlugin !== null && $oPlugin->kPlugin > 0 && strlen($cSearch) >= 3) {
    $cTemplatePath   = $oPlugin->cFrontendPfad . PFAD_PLUGIN_TEMPLATE;
    $cTemplateResult = $cTemplatePath . 'results.tpl';

    if (!$oPlugin->getConf('cProjectId')) {
        $oObj_arr = Shop::DB()->query("SELECT * FROM tjtlsearchserverdata", 2);
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

        $oResponse   = null;
        $cSearch_arr = JtlSearch::doSuggest(
            $_SESSION['Kundengruppe']->kKundengruppe,
            $_SESSION['cISOSprache'],
            $_SESSION['Waehrung']->cISO,
            $cSearch,
            $oPlugin->getConf('cProjectId'),
            $oPlugin->getConf('cAuthHash'),
            urldecode($oPlugin->getConf('cServerUrl')),
            $oResponse
        );

        foreach ($cSearch_arr as &$cTmp) {
            foreach ($cTmp->oItem_arr as &$oItem) {
                $oItem->cName = utf8_decode($oItem->cName);
            }
        }

        $oResponse->oSuggest->cSuggest = utf8_decode($oResponse->oSuggest->cSuggest);

        if (is_array($cSearch_arr) && count($cSearch_arr) > 0) {
            Shop::Smarty()->assign('cSearch', StringHandler::filterXSS($cSearch))
                ->assign('cSearch_arr', $cSearch_arr)
                ->assign('langVars', $oPlugin->oPluginSprachvariableAssoc_arr)
                ->assign('cTemplatePath', $cTemplatePath)
                ->assign('oSearchResponse', $oResponse)
                ->assign('bBranding', (isset($oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_branding']) && $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_branding'] === "0")?false:true)
                ->display($cTemplateResult);
        }
    }
}
