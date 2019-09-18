<?php
global $smarty, $oPlugin;
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion.'/includes/defines_inc.php');
require(JTLSEARCH_PFAD_INCLUDES.'global_inc.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_INCLUDES.'defines_inc.php');
$oDebugger->doDebug(__FILE__.': Anfang der status.php.');

//Variablen für die Ausgabe setzen
$cHinweis               = '';
$cFehler                = '';
$cStepPlugin            = 'Verwaltung';
$cStatusModulAssoc_arr  = array();

//require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG.'status_inc.php');
if (count($oServerinfo) == 3) {
    if (is_dir(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES)) {
        if ($oDir = opendir(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES)) {
            while (($cDir = readdir($oDir)) !== false) {
                if (is_dir(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.$cDir) && file_exists(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.$cDir.'/class.JTLSEARCH_Verwaltung_'.$cDir.'.php')) {
                    require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.$cDir.'/class.JTLSEARCH_Verwaltung_'.$cDir.'.php');
                    $cClass = 'JTLSEARCH_Verwaltung_'.$cDir;
                    if (class_exists($cClass) && is_subclass_of($cClass, 'JTLSEARCH_Verwaltung_Base')) {
                        $oStatus = new $cClass($oDB, $oDebugger);

                        $xContent = $oStatus->getContent(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.$cDir.'/');
                        if ($xContent !== null) {
                            $tmpSmarty = clone $smarty;
                            $cTmpStatusModulAssoc_arr['cName'] = $oStatus->getName();
                            $cTmpStatusModulAssoc_arr['cCssURL'] = $oStatus->getCssURL(JTLSEARCH_URL_ADMINMENU_VERWALTUNG.'modules/'.$cDir.'/');
                            foreach ($xContent['xContentVarAssoc'] as $cKey => $xValue) {
                                $tmpSmarty->assign($cKey, $xValue);
                            }
                            $cTmpStatusModulAssoc_arr['cContent'] = $tmpSmarty->fetch($xContent['cTemplate']);
                            if (isset($cStatusModulAssoc_arr[$oStatus->getSort()])) {
                                array_push($cStatusModulAssoc_arr, $cTmpStatusModulAssoc_arr);
                            } else {
                                $cStatusModulAssoc_arr[$oStatus->getSort()] = $cTmpStatusModulAssoc_arr;
                            }
                            unset($tmpSmarty);
                        } else {
                            $oDebugger->doDebug(__FILE__.': '.$cClass.'-Klasse gibt kein gültigen Content.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                        }
                    } else {
                        $oDebugger->doDebug(__FILE__.': '.$cClass.'-Klasse existiert nicht.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                    }
                } else {
                    $oDebugger->doDebug(__FILE__.': '.JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.$cDir.'/class.JTLSEARCH_Verwaltung_'.$cDir.'.php nicht vorhanden oder '.JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.$cDir.' ist kein Ordner.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                }
            }
        } else {
            $oDebugger->doDebug(__FILE__.': Konnte den Ordner '.JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.' nicht öffnen.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        }
    } else {
        $oDebugger->doDebug(__FILE__.': '.JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES.' ist kein Ordner oder dieser Ordner existiert nicht.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    }

    ksort($cStatusModulAssoc_arr);

    $smarty->assign("cHinweis", $cHinweis);
    $smarty->assign("cFehler", $cFehler);
    $smarty->assign("URL_SHOP", URL_SHOP);
    $smarty->assign("PFAD_ROOT", PFAD_ROOT);
    $smarty->assign("URL_ADMINMENU", URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);
    $smarty->assign("stepPlugin", $stepPlugin);
    //$smarty->assign("cServereinstellungenURL", $cServereinstellungenURL);
    $smarty->assign("cBaseCssURL", JTLSEARCH_URL_ADMINMENU_VERWALTUNG_TEMPLATES_CSS_BASE);
    $smarty->assign("cStatusModulAssoc_arr", $cStatusModulAssoc_arr);
    $smarty->register_function("count", "count");
    print($smarty->fetch(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_TEMPLATES."verwaltung.tpl"));
    $oDebugger->doDebug(__FILE__.': Ende der status.php', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
} else {
    $oDebugger->doDebug(__FILE__.': Keine Servereinstellungen vorhanden. Bitte registrieren.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    $oDebugger->doDebug(__FILE__.': Serverinfos: '.  var_export($oServerinfo, true), JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
    require_once(JTLSEARCH_PFAD_ADMINMENU.'testperiod.php');
}
