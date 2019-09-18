<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
/**
 * @global JTLSmarty $smarty
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'template_inc.php';

$oAccount->permission('DISPLAY_TEMPLATE_VIEW', true, true);

$cHinweis       = '';
$cFehler        = '';
$lessVars_arr   = [];
$lessVarsSkin   = [];
$lessColors_arr = [];
$lessColorsSkin = [];
$oTemplate      = Template::getInstance();
$templateHelper = TemplateHelper::getInstance(true);
$templateHelper->disableCaching();
$admin          = (isset($_GET['admin']) && $_GET['admin'] === 'true');
/** @global JTLSmarty $smarty */
if (isset($_POST['key'], $_POST['upload'])) {
    $file   = PFAD_ROOT . PFAD_TEMPLATES . $_POST['upload'];
    $upload = explode('/', $_POST['upload']);
    $oTemplate->setConfig($upload[0], 'theme', 'favicon', '');
    $response = new stdClass();
    if (file_exists($file) && is_file($file)) {
        $delete           = unlink($file);
        $response->status = ($delete === true) ? 'OK' : 'FAILED';
    } else {
        $response->status = 'FAILED';
    }
    die(json_encode($response));
}
if (isset($_GET['check'])) {
    if ($_GET['check'] === 'true') {
        $cHinweis = 'Template und Einstellungen wurden erfolgreich ge&auml;ndert.';
    } elseif ($_GET['check'] === 'false') {
        $cFehler = 'Template bzw. Einstellungen konnten nicht ge&auml;ndert werden.';
    }
}
if (isset($_GET['faviconError'])) {
    $cFehler .= 'Favicon konnten nicht gespeichert werden - bitte Schreibrechte &uumlberpr&uuml;fen.';
}
if (isset($_POST['type']) && $_POST['type'] === 'layout' && validateToken()) {
    $oCSS           = new SimpleCSS();
    $cOrdner        = basename($_POST['ordner']);
    $cCustomCSSFile = $oCSS->getCustomCSSFile($cOrdner);
    $bReset         = (isset($_POST['reset']) && $_POST['reset'] == 1);
    if ($bReset) {
        $bOk = false;
        if (file_exists($cCustomCSSFile)) {
            $bOk = is_writable($cCustomCSSFile);
        }
        if ($bOk) {
            $cHinweis = 'Layout wurde erfolgreich zur&uuml;ckgesetzt.';
        } else {
            $cFehler = 'Layout konnte nicht zur&uuml;ckgesetzt werden.';
        }
    } else {
        $cSelector_arr  = $_POST['selector'];
        $cAttribute_arr = $_POST['attribute'];
        $cValue_arr     = $_POST['value'];
        $oCSS           = new SimpleCSS();
        $selectorCount  = count($cSelector_arr);
        for ($i = 0; $i < $selectorCount; $i++) {
            $oCSS->addCSS($cSelector_arr[$i], $cAttribute_arr[$i], $cValue_arr[$i]);
        }
        $cCSS   = $oCSS->renderCSS();
        $nCheck = file_put_contents($cCustomCSSFile, $cCSS);
        if ($nCheck === false) {
            $cFehler = 'Style-Datei konnte nicht geschrieben werden. &Uuml;berpr&uuml;fen Sie die Dateirechte von ' . $cCustomCSSFile . '.';
        } else {
            $cHinweis = 'Layout wurde erfolgreich angepasst.';
        }
    }
}
if (isset($_POST['type']) && $_POST['type'] === 'settings' && validateToken()) {
    $cOrdner      = Shop::DB()->escape($_POST['ordner']);
    $parentFolder = null;
    $tplXML       = $oTemplate->leseXML($cOrdner);
    if (!empty($tplXML->Parent)) {
        $parentFolder = (string)$tplXML->Parent;
        $parentTplXML = $oTemplate->leseXML($parentFolder);
    }
    $tplConfXML   = $oTemplate->leseEinstellungenXML($cOrdner, $parentFolder);
    $sectionCount = count($_POST['cSektion']);
    $faviconError = '';
    for ($i = 0; $i < $sectionCount; $i++) {
        $cSektion = Shop::DB()->escape($_POST['cSektion'][$i]);
        $cName    = Shop::DB()->escape($_POST['cName'][$i]);
        $cWert    = Shop::DB()->escape($_POST['cWert'][$i]);
        //for uploads, the value of an input field is the $_FILES index of the uploaded file
        if (strpos($cWert, 'upload-') === 0) {
            //all upload fields have to start with "upload-" - so check for that
            if (!empty($_FILES[$cWert]['name']) && $_FILES[$cWert]['error'] === UPLOAD_ERR_OK) {
                //we have an upload field and the file is set in $_FILES array
                $file  = $_FILES[$cWert];
                $cWert = basename($_FILES[$cWert]['name']);
                $break = false;
                foreach ($tplConfXML as $_section) {
                    if (isset($_section->oSettings_arr)) {
                        foreach ($_section->oSettings_arr as $_setting) {
                            if (isset($_setting->cKey, $_setting->rawAttributes['target'])
                                && $_setting->cKey === $cName
                            ) {
                                //target folder
                                $base = PFAD_ROOT . PFAD_TEMPLATES . $cOrdner . '/';
                                //optional target file name + extension
                                if (isset($_setting->rawAttributes['targetFileName'])) {
                                    $cWert = $_setting->rawAttributes['targetFileName'];
                                }
                                $targetFile = $base . $cWert;
                                if (strpos($targetFile, $base) !== 0 || !move_uploaded_file($file['tmp_name'], $targetFile)) {
                                    $faviconError = '&faviconError=true';
                                }
                                $break = true;
                                break;
                            }
                        }
                    }
                    if ($break === true) {
                        break;
                    }
                }
            } else {
                //no file uploaded, ignore
                continue;
            }
        }
        $oTemplate->setConfig($cOrdner, $cSektion, $cName, $cWert);
    }
    $bCheck = __switchTemplate($_POST['ordner'], $_POST['eTyp']);
    if ($bCheck) {
        $cHinweis = 'Template und Einstellungen wurden erfolgreich ge&auml;ndert.';
    } else {
        $cFehler = 'Template bzw. Einstellungen konnten nicht ge&auml;ndert werden.';
    }
    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
    //re-init smarty with new template - problematic because of re-including functions.php
    header('Location: ' . Shop::getURL() . '/' .
        PFAD_ADMIN . 'shoptemplate.php?check=' .
        ($bCheck ? 'true' : 'false') . $faviconError, true, 301);
}
if (isset($_GET['settings']) && strlen($_GET['settings']) > 0 && validateToken()) {
    $cOrdner      = Shop::DB()->escape($_GET['settings']);
    $oTpl         = $templateHelper->getData($cOrdner, $admin);
    $tplXML       = $templateHelper->getXML($cOrdner, false);
    $preview      = [];
    $parentFolder = null;
    if (!empty($tplXML->Parent)) {
        $parentFolder = (string)$tplXML->Parent;
        $parentTplXML = $templateHelper->getXML($parentFolder, false);
    }
    $tplConfXML       = $oTemplate->leseEinstellungenXML($cOrdner, $parentFolder);
    $tplLessXML       = $oTemplate->leseLessXML($cOrdner);
    $currentSkin      = $oTemplate->getSkin();
    $frontendTemplate = PFAD_ROOT . PFAD_TEMPLATES . $oTemplate->getFrontendTemplate();
    $lessStack        = null;
    $shopURL          = Shop::getURL() . '/';
    if ($admin === true) {
        $oTpl->eTyp = 'admin';
        $bCheck     = __switchTemplate($cOrdner, $oTpl->eTyp);
        if ($bCheck) {
            $cHinweis = 'Template und Einstellungen wurden erfolgreich ge&auml;ndert.';
        } else {
            $cFehler = 'Template bzw. Einstellungen konnten nicht ge&auml;ndert werden.';
        }
        Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
        //re-init smarty with new template - problematic because of re-including functions.php
        header('Location: ' . $shopURL . PFAD_ADMIN . 'shoptemplate.php', true, 301);
    } else {
        // iterate over each "Section"
        foreach ($tplConfXML as $_conf) {
            // iterate over each "Setting" in this "Section"
            foreach ($_conf->oSettings_arr as $_setting) {
                if ($_setting->cType === 'upload' &&
                    isset($_setting->rawAttributes['target'], $_setting->rawAttributes['targetFileName']) &&
                    !file_exists(PFAD_ROOT . PFAD_TEMPLATES . $cOrdner . '/'
                        . $_setting->rawAttributes['targetFileName'])
                ) {

                    $_setting->cValue = null;
                }
            }
            if (isset($_conf->cKey, $_conf->oSettings_arr) && $_conf->cKey === 'theme' && count($_conf->oSettings_arr) > 0) {
                foreach ($_conf->oSettings_arr as $_themeConf) {
                    if (isset($_themeConf->cKey, $_themeConf->oOptions_arr) &&
                        $_themeConf->cKey === 'theme_default' &&
                        count($_themeConf->oOptions_arr) > 0
                    ) {
                        foreach ($_themeConf->oOptions_arr as $_theme) {
                            $previewImage = isset($_theme->cOrdner)
                                ? PFAD_ROOT . PFAD_TEMPLATES . $_theme->cOrdner . '/themes/' . $_theme->cValue . '/preview.png'
                                : PFAD_ROOT . PFAD_TEMPLATES . $cOrdner . '/themes/' . $_theme->cValue . '/preview.png';
                            if (file_exists($previewImage)) {
                                $preview[$_theme->cValue] = isset($_theme->cOrdner)
                                    ? $shopURL . PFAD_TEMPLATES . $_theme->cOrdner . '/themes/' . $_theme->cValue . '/preview.png'
                                    : $shopURL . PFAD_TEMPLATES . $cOrdner . '/themes/' . $_theme->cValue . '/preview.png';
                            }
                        }
                        break;
                    }
                }
            }
        }
        foreach ($tplLessXML as $_less) {
            if (isset($_less->cName)) {
                $themesLess = $_less;
                $less       = new LessParser();
                foreach ($themesLess->oFiles_arr as $filePaths) {
                    if ($themesLess->cName === $currentSkin) {
                        $less->read($frontendTemplate . '/' . $filePaths->cPath);
                        $lessVarsSkin   = $less->getStack();
                        $lessColorsSkin = $less->getColors();
                    }
                    $less->read($frontendTemplate . '/' . $filePaths->cPath);
                    $lessVars   = $less->getStack();
                    $lessColors = $less->getColors();
                }
                $lessVars_arr[$themesLess->cName]   = $lessVars;
                $lessColors_arr[$themesLess->cName] = $lessColors;
            }
        }
    }

    $smarty->assign('oTemplate', $oTpl)
           ->assign('themePreviews', (count($preview) > 0) ? $preview : null)
           ->assign('themePreviewsJSON', json_encode($preview))
           ->assign('themesLessVars', $lessVars_arr)
           ->assign('themesLessVarsJSON', json_encode($lessVars_arr))
           ->assign('themesLessVarsSkin', $lessVarsSkin)
           ->assign('themesLessVarsSkinJSON', json_encode($lessVarsSkin))
           ->assign('themesLessColorsSkin', $lessColorsSkin)
           ->assign('themesLessColorsJSON', json_encode($lessColors_arr))
           ->assign('oEinstellungenXML', $tplConfXML);
} elseif (isset($_GET['switch']) && strlen($_GET['switch']) > 0) {
    $bCheck = __switchTemplate($_GET['switch'], ($admin === true ? 'admin' : 'standard'));
    if ($bCheck) {
        $cHinweis = 'Template wurde erfolgreich ge&auml;ndert.';
    } else {
        $cFehler = 'Template konnte nicht ge&auml;ndert werden.';
    }

    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
}
$smarty->assign('admin', ($admin === true) ? 1 : 0)
       ->assign('oTemplate_arr', $templateHelper->getFrontendTemplates())
       ->assign('oAdminTemplate_arr', $templateHelper->getAdminTemplates())
       ->assign('oStoredTemplate_arr', $templateHelper->getStoredTemplates())
       ->assign('cFehler', $cFehler)
       ->assign('cHinweis', $cHinweis)
       ->display('shoptemplate.tpl');
