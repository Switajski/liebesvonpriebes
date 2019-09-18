<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis           = '';
$cFehler            = '';
$step               = 'plugin_uebersicht';
$customPluginTabs   = [];
$invalidateCache    = false;
$pluginTemplateFile = 'plugin.tpl';
if ($step === 'plugin_uebersicht') {
    $kPlugin = verifyGPCDataInteger('kPlugin');
    if ($kPlugin > 0) {
        // Ein Settinglink wurde submitted
        if (verifyGPCDataInteger('Setting') === 1) {
            if (!validateToken()) {
                $bError = true;
            } else {
                $bError                     = false;
                $oPluginEinstellungConf_arr = [];
                if (isset($_POST['kPluginAdminMenu'])) {
                    $oPluginEinstellungConf_arr = Shop::DB()->query(
                        "SELECT *
                            FROM tplugineinstellungenconf
                            WHERE kPluginAdminMenu != 0
                                AND kPlugin = " . $kPlugin . "
                                AND cConf != 'N'
                                AND kPluginAdminMenu = " . (int)$_POST['kPluginAdminMenu'], 2
                    );
                }
                if (count($oPluginEinstellungConf_arr) > 0) {
                    foreach ($oPluginEinstellungConf_arr as $oPluginEinstellungConf) {
                        Shop::DB()->delete(
                            'tplugineinstellungen',
                            ['kPlugin', 'cName'],
                            [$kPlugin, $oPluginEinstellungConf->cWertName]
                        );
                        $oPluginEinstellung          = new stdClass();
                        $oPluginEinstellung->kPlugin = $kPlugin;
                        $oPluginEinstellung->cName   = $oPluginEinstellungConf->cWertName;
                        if (isset($_POST[$oPluginEinstellungConf->cWertName])) {
                            if (is_array($_POST[$oPluginEinstellungConf->cWertName])) {
                                if ($oPluginEinstellungConf->cConf === 'M') {
                                    //selectbox with "multiple" attribute
                                    $oPluginEinstellung->cWert = serialize($_POST[$oPluginEinstellungConf->cWertName]);
                                } else {
                                    //radio buttons
                                    $oPluginEinstellung->cWert = $_POST[$oPluginEinstellungConf->cWertName][0];
                                }
                            } else {
                                //textarea/text
                                $oPluginEinstellung->cWert = $_POST[$oPluginEinstellungConf->cWertName];
                            }
                        } else {
                            //checkboxes that are not checked
                            $oPluginEinstellung->cWert = null;
                        }
                        $kKey = Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellung);

                        if (!$kKey) {
                            $bError = true;
                        }
                    }
                    $invalidateCache = true;
                }
            }
            if ($bError) {
                $cFehler = 'Fehler: Ihre Einstellungen konnten nicht gespeichert werden.';
            } else {
                $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert';
            }
        }
        if (verifyGPCDataInteger('kPluginAdminMenu') > 0) {
            $smarty->assign('defaultTabbertab', verifyGPCDataInteger('kPluginAdminMenu'));
        }
        if (strlen(verifyGPDataString('cPluginTab')) > 0) {
            $smarty->assign('defaultTabbertab', verifyGPDataString('cPluginTab'));
        }

        $oPlugin = new Plugin($kPlugin, $invalidateCache);
        if (!$invalidateCache) { //make sure dynamic options are reloaded
            foreach ($oPlugin->oPluginEinstellungConf_arr as $option) {
                if (!empty($option->cSourceFile)) {
                    $option->oPluginEinstellungenConfWerte_arr = $oPlugin->getDynamicOptions($option);
                }
            }
        }
        $smarty->assign('oPlugin', $oPlugin);
        $i = 0;
        $j = 0;
        // check, if we have a README.md in this current plugin and if so, we insert a "DocTab"
        $fAddAsDocTab = false;
        $szReadmeFile = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/README.md';
        if ('' !== $oPlugin->cTextReadmePath) {
            $szReadmeContent = utf8_decode(file_get_contents($szReadmeFile)); // slurp in the file-content
            // check, if we got a Markdown-parser
            $fMarkDown     = false;
            if (class_exists('Parsedown')) {
                $fMarkDown       = true;
                $oParseDown      = new Parsedown();
                $szReadmeContent = $oParseDown->text($szReadmeContent);
            }
            // set, what we found, into the smarty-object
            // (and let the template decide to show markdown or <pre>-formatted-text)
            $smarty->assign('fMarkDown'      , $fMarkDown)
                   ->assign('szReadmeContent', $szReadmeContent);

            // create a tab-object (for insert into the admin-menu later)
            $oUnnamedTab                      = new stdClass();
            // normally the `kPluginAdminMenu` from `tpluginadminmenu`, but we use it as a counter here
            $oUnnamedTab->kPluginAdminMenu    = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->kPlugin             = $oPlugin->kPlugin; // the current plugin-ID
            $oUnnamedTab->cName               = 'Dokumentation';
            $oUnnamedTab->cDateiname          = '';
            $oUnnamedTab->nSort               = count($oPlugin->oPluginAdminMenu_arr) + 1; // set as the last entry/tab
            $oUnnamedTab->nConf               = 1;
            $oPlugin->oPluginAdminMenu_arr[]  = $oUnnamedTab; // append to menu-array

            $fAddAsDocTab = true;
        }
        // check, if there is a LICENSE.md too
        $fAddAsLicenseTab  = false;
        if ('' !== $oPlugin->cTextLicensePath) {
            $szLicenseContent = utf8_decode(file_get_contents($oPlugin->cTextLicensePath)); // slurp in the file content
            // check, if we got a Markdown-parser
            $fMarkDown     = false;
            if (class_exists('Parsedown')) {
                $fMarkDown       = true;
                $oParseDown      = new Parsedown();
                $szLicenseContent = $oParseDown->text($szLicenseContent);
            }
            // set, what we found, into the smarty-object
            // (and let the template decide to show markdown or <pre>-formatted-text)
            $smarty->assign('fMarkDown'      , $fMarkDown)
                   ->assign('szLicenseContent', $szLicenseContent);

            // create a tab-object (for insert into the admin-menu later)
            $oUnnamedTab                      = new stdClass();
            // normally the `kPluginAdminMenu` from `tpluginadminmenu`, but we use it as a counter here
            $oUnnamedTab->kPluginAdminMenu    = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->kPlugin             = $oPlugin->kPlugin; // the current plugin-ID
            $oUnnamedTab->cName               = 'Lizenzvereinbarung';
            $oUnnamedTab->cDateiname          = '';
            $oUnnamedTab->nSort               = count($oPlugin->oPluginAdminMenu_arr) + 1; // set as the last entry/tab
            $oUnnamedTab->nConf               = 1;
            $oPlugin->oPluginAdminMenu_arr[]  = $oUnnamedTab; // append to menu-array

            $fAddAsLicenseTab = true;
        }
        // build the the tabs
        foreach ($oPlugin->oPluginAdminMenu_arr as $_adminMenu) {
            if ($_adminMenu->nConf === '0' && $_adminMenu->cDateiname !== '' &&
                file_exists($oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname)) {
                ob_start();
                require $oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname;

                $tab                   = new stdClass();
                $tab->file             = $oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname;
                $tab->idx              = $i;
                $tab->id               = str_replace('.php', '', $_adminMenu->cDateiname);
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = ob_get_contents();
                $customPluginTabs[]    = $tab;
                ob_end_clean();
                ++$i;
            } elseif ($_adminMenu->nConf === '1') {
                $smarty->assign('oPluginAdminMenu', $_adminMenu);
                $tab                   = new stdClass();
                $tab->file             = $oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname;
                $tab->idx              = $i;
                $tab->id               = 'settings-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_options.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
            } elseif (true === $fAddAsDocTab ) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html = $smarty->fetch('tpl_inc/plugin_documentation.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
                $fAddAsDocTab = false; // prevent another appending!
            } elseif (true === $fAddAsLicenseTab) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html = $smarty->fetch('tpl_inc/plugin_license.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
                $fAddAsLicenseTab = false; // prevent another appending!
            }
        }
    }
}

$smarty->assign('customPluginTabs', $customPluginTabs)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display($pluginTemplateFile);
