<?php
require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
require JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
require_once JTLSEARCH_PFAD_ADMINMENU_TESTPERIOD_CLASSES . 'class.JTLSearch_Form_Activate.php';

$bStartedTestperiod = false;

if (isset($oServerSettings->cProjectId) && strlen($oServerSettings->cProjectId) > 0 &&
    isset($oServerSettings->cAuthHash) && strlen($oServerSettings->cAuthHash) > 0
) {
    $cLicenseKey = base64_encode("{$oServerSettings->cProjectId}:::{$oServerSettings->cAuthHash}");
    $stepPlugin  = 'Lizenz';

    $oForm = new JTLSearch_Form_Activate($oDebugger, 'JTLSearch_testperiod_form', 'post');
    $oForm->addElement('kPlugin', 'hidden', '', array('value' => $oPlugin->kPlugin));
    $oForm->addElement('cPluginTab', 'hidden', '', array('value' => 'Lizenz'));
    $oForm->addElement('stepPlugin', 'hidden', '', array('value' => $stepPlugin));

    $oForm->addElement('cCode', 'textarea', 'Lizenzschlüssel', array('style' => 'width: 500px; height: 120px;', 'value' => $cLicenseKey, 'id' => 'cCode', 'class' => 'form-control'));
    $oForm->addElement('btn_serverinfo', 'submit', '', array('value' => 'Lizenz speichern', 'class' => 'btn btn-primary button orange'));

    $oForm->addRule('cCode', 'Es muss ein Lizenzschlüssel angegeben werden.', 'required');
    $oForm->addRule('cCode', 'Der Lizenzschlüssel muss aus mindestens 3 Buchstaben bestehen.', 'minlength', 3);
    $oForm->addRule('cCode', 'Kein Gültiger Lizenzschlüssel.', 'base64decodeable');

    if (isset($_POST['kPlugin']) && isset($_POST['btn_serverinfo'])) {
        if ($oForm->isValid()) {
            if ($cLicenseKey != $_POST['cCode']) {
                require_once JTLSEARCH_PFAD_CLASSES . 'class.Communication.php';
                require_once JTLSEARCH_PFAD_CLASSES . 'class.Security.php';

                $cData_arr = explode(':::', base64_decode($_POST['cCode']));

                if (is_array($cData_arr) && count($cData_arr) === 2) {
                    // Security Objekt erstellen und Parameter zum senden der Daten setzen
                    $oSecurity = new Security($cData_arr[0], $cData_arr[1]);
                    $oSecurity->setParam_arr(array('getsearchserver'));

                    $xData_arr['a']   = 'getsearchserver';
                    $xData_arr['pid'] = $cData_arr[0];
                    $xData_arr['p']   = $oSecurity->createKey();
                    $cResult          = Communication::postData(JTLSEARCH_MANAGER_SERVER_URL, $xData_arr);
                    $oDebugger->doDebug(__FILE__ . ': $cResult : ' . $cResult, JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                    $oResult = json_decode($cResult);

                    if (isset($oResult->_serverurl) && strlen($oResult->_serverurl) > 0 && isset($oResult->_code) && $oResult->_code == 1) {
                        try {
                            Shop::DB()->exec('TRUNCATE TABLE tjtlsearchserverdata', 10);
                            $oObject         = new stdClass();
                            $oObject->cKey   = 'cProjectId';
                            $oObject->cValue = $cData_arr[0];
                            Shop::DB()->insert('tjtlsearchserverdata', $oObject);
                            $oObject         = new stdClass();
                            $oObject->cKey   = 'cAuthHash';
                            $oObject->cValue = $cData_arr[1];
                            Shop::DB()->insert('tjtlsearchserverdata', $oObject);
                            $oObject         = new stdClass();
                            $oObject->cKey   = 'cServerUrl';
                            $oObject->cValue = $oResult->_serverurl;
                            Shop::DB()->insert('tjtlsearchserverdata', $oObject);
                            unset($oObject);
                            $bStartedTestperiod = true;
                        } catch (Exception $oEx) {
                            $oDebugger->doDebug(__FILE__ . ': Es ist ein unerwarteter Fehler aufgetreten', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                            $oDebugger->doDebug(__FILE__ . ': Exception $oEx : ' . $oEx, JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                        }
                    } else {
                        if (strlen($cResult) > 0) {
                            $oForm->setError('Entweder haben Sie keinen korrekten Lizenzschlüssel eingetragen oder es gibt ein Problem mit Ihrem Lizenzschlüssel.');
                        } else {
                            $oForm->setError('Unerwartetes Ergebnis vom Server. Es gibt ein Problem mit Ihrem Lizenzschlüssel.');
                        }
                        $oDebugger->doDebug(__FILE__ . ': Es ist ein Fehler beim Export aufgetreten. Unerwartete Antwort vom Verwaltungsserver', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                        $oDebugger->doDebug(__FILE__ . ': $oResult : ' . print_r($oResult, true), JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                    }
                } else {
                    $oDebugger->doDebug(__FILE__ . ': Konnte Lizenzschlüssel nicht teilen (' . base64_decode($_POST['cCode']) . ')', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                    $oDebugger->doDebug(__FILE__ . ': $cData_arr : ' . print_r($cData_arr, true), JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
                    $oForm->setError('Konnte Lizenzschlüssel nicht prüfen.');
                }
            } else {
                $oDebugger->doDebug(__FILE__ . ': Es muss nichts geändert werden da die Keya die selben sind.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
            }
        }
    }
    Shop::Smarty()->assign('bStartedTestperiod', $bStartedTestperiod)
        ->assign('URL_SHOP', Shop::getURL())
        ->assign('PFAD_ROOT', PFAD_ROOT)
        ->assign('URL_ADMINMENU', Shop::getURL() . '/' . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_ADMINMENU)
        ->assign('cBaseCssURL', JTLSEARCH_URL_ADMINMENU_TESTPERIOD_TEMPLATES_CSS_BASE)
        ->assign('oForm', $oForm)
        ->display(JTLSEARCH_PFAD_ADMINMENU_TESTPERIOD_TEMPLATES . 'testperiod.tpl');
} else {
    $oDebugger->doDebug(__FILE__ . ': Keine Servereinstellungen vorhanden. Bitte registrieren.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    $oDebugger->doDebug(__FILE__ . ': Serverinfos: ' . var_export($oServerSettings, true), JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
    require JTLSEARCH_PFAD_ADMINMENU . 'testperiod.php';
}
