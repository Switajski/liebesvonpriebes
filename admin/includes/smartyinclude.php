<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$smarty                    = JTLSmarty::getInstance(false, true);
$templateDir               = $smarty->getTemplateDir($smarty->context);
$template                  = AdminTemplate::getInstance();
$Einstellungen             = Shop::getSettings([CONF_GLOBAL]);
$Einstellungen['template'] = $template->getConfig();
$currentTheme              = isset($Einstellungen['template']['theme_default']) ?: '';
$shopURL                   = Shop::getURL();
$currentTemplateDir        = str_replace(PFAD_ROOT . PFAD_ADMIN, '', $templateDir);
$resourcePaths             = $template->getResources(isset($Einstellungen['template']['general']['use_minify']) &&
    $Einstellungen['template']['general']['use_minify'] === 'Y');
$oAccount                  = new AdminAccount();
// Account
if (!isset($oAccount) || get_class_methods($oAccount) === null) {
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
    $oAccount = new AdminAccount();
}
// Einstellungen
$configSections = Shop::DB()->query("SELECT * FROM teinstellungensektion ORDER BY cName", 2);
$sectionCount   = count($configSections);
for ($i = 0; $i < $sectionCount; $i++) {
    $anz_einstellunen = Shop::DB()->query("
        SELECT count(*) AS anz
            FROM teinstellungenconf
            WHERE kEinstellungenSektion = " . (int)$configSections[$i]->kEinstellungenSektion . "
            AND cConf = 'Y'", 1
    );
    $configSections[$i]->anz       = $anz_einstellunen->anz;
    $configSections[$i]->cLinkname = $configSections[$i]->cName;
    $configSections[$i]->cURL      = 'einstellungen.php?kSektion=' . $configSections[$i]->kEinstellungenSektion;
}
$oLinkOberGruppe_arr = Shop::DB()->selectAll('tadminmenugruppe', 'kAdminmenueOberGruppe', 0, '*', 'nSort');

if (is_array($oLinkOberGruppe_arr) && count($oLinkOberGruppe_arr) > 0) {
    // JTL Search Plugin aktiv?
    $oPluginSearch = Shop::DB()->query(
        "SELECT kPlugin, cName
            FROM tplugin
            WHERE cPluginID = 'jtl_search'", 1
    );
    foreach ($oLinkOberGruppe_arr as $i => $oLinkOberGruppe) {
        $oLinkOberGruppe_arr[$i]->oLinkGruppe_arr = [];
        $oLinkOberGruppe_arr[$i]->oLink_arr       = [];

        $oLinkGruppe_arr = Shop::DB()->selectAll(
            'tadminmenugruppe',
            'kAdminmenueOberGruppe',
            (int)$oLinkOberGruppe->kAdminmenueGruppe,
            '*',
            'cName, nSort'
        );
        if (is_array($oLinkGruppe_arr) && count($oLinkGruppe_arr) > 0) {
            foreach ($oLinkGruppe_arr as $j => $oLinkGruppe) {
                if (!isset($oLinkGruppe->oLink_arr)) {
                    $oLinkGruppe->oLink_arr = [];
                }
                $oLinkGruppe_arr[$j]->oLink_arr = Shop::DB()->selectAll(
                    'tadminmenu',
                    'kAdminmenueGruppe',
                    (int)$oLinkGruppe->kAdminmenueGruppe,
                    '*',
                    'cLinkname, nSort'
                );
                foreach ($configSections as $_k => $_configSection) {
                    if (isset($_configSection->kAdminmenueGruppe) &&
                        $_configSection->kAdminmenueGruppe == $oLinkGruppe->kAdminmenueGruppe
                    ) {
                        $oLinkGruppe->oLink_arr[] = $_configSection;
                        unset($configSections[$_k]);
                    }
                }
            }
            $oLinkOberGruppe->oLinkGruppe_arr = $oLinkGruppe_arr;
        }
        // Plugin Work Around
        if ($oLinkOberGruppe->kAdminmenueGruppe == LINKTYP_BACKEND_PLUGINS) {
            $oPlugin_arr = Shop::DB()->query(
                "SELECT DISTINCT tplugin.kPlugin, tplugin.cName, tplugin.cPluginID, tplugin.nPrio
                    FROM tplugin INNER JOIN tpluginadminmenu
                        ON tplugin.kPlugin = tpluginadminmenu.kPlugin
                    WHERE tplugin.nStatus = 2
                    ORDER BY tplugin.nPrio, tplugin.cName", 2
            );
            if (!is_array($oPlugin_arr)) {
                $oPlugin_arr = [];
            }
            foreach ($oPlugin_arr as $j => $oPlugin) {
                $oPlugin_arr[$j]->cLinkname = $oPlugin->cName;
                $oPlugin_arr[$j]->cURL      = $shopURL . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin;
                $oPlugin_arr[$j]->cRecht    = 'PLUGIN_ADMIN_VIEW';
            }
            $oLinkOberGruppe_arr[$i]->oLinkGruppe_arr   = [];
            $pluginManager                              = new stdClass();
            $pluginManager->cName                       = '&Uuml;bersicht';
            $pluginManager->break                       = false;
            $pluginManager->oLink_arr                   = Shop::DB()->selectAll(
                'tadminmenu',
                'kAdminmenueGruppe',
                (int)$oLinkOberGruppe->kAdminmenueGruppe,
                '*',
                'cLinkname'
            );
            $oLinkOberGruppe_arr[$i]->oLinkGruppe_arr[] = $pluginManager;
            $pluginCount                                = count($oPlugin_arr);
            $maxEntries                                 = ($pluginCount > 24) ? 10 : 6;
            $pluginListChunks                           = array_chunk($oPlugin_arr, $maxEntries);
            foreach ($pluginListChunks as $_chunk) {
                $pluginList                                 = new stdClass();
                $pluginList->cName                          = 'Plugins';
                $pluginList->oLink_arr                      = $_chunk;
                $oLinkOberGruppe_arr[$i]->oLinkGruppe_arr[] = $pluginList;
            }
            if ($pluginCount > 12) {
                //make the submenu full-width if more then 12 plugins are listed
                $oLinkOberGruppe_arr[$i]->class = 'yamm-fw';
            }
        } elseif ($oLinkOberGruppe->kAdminmenueGruppe == 17) {
            if (isset($oPluginSearch->kPlugin) && $oPluginSearch->kPlugin > 0) {
                $oPluginSearch->cLinkname = 'JTL Search';
                $oPluginSearch->cURL      = $shopURL . '/' . PFAD_ADMIN .
                    'plugin.php?kPlugin=' . $oPluginSearch->kPlugin;
                $oPluginSearch->cRecht    = 'PLUGIN_ADMIN_VIEW';

                $nI                                   = count($oLinkOberGruppe_arr[$i]->oLink_arr);
                $oLinkOberGruppe_arr[$i]->oLink_arr[] = $oPluginSearch;
                objectSort($oLinkOberGruppe_arr[$i]->oLink_arr, 'cLinkname');
            }
        } else {
            $oLinkOberGruppe_arr[$i]->oLink_arr = Shop::DB()->selectAll(
                'tadminmenu',
                'kAdminmenueGruppe',
                (int)$oLinkOberGruppe->kAdminmenueGruppe,
                '*',
                'cLinkname'
            );
        }
    }
}

if (isset($nUnsetPlugin) && $nUnsetPlugin > 0) {
    unset($linkgruppen[$nUnsetPlugin]);
    $linkgruppen = array_merge($linkgruppen);
}
if (is_array($currentTemplateDir)) {
    $currentTemplateDir = $currentTemplateDir[$smarty->context];
}
$smarty->assign('SID', (defined('SID') ? SID : null))
       ->assign('URL_SHOP', $shopURL)
       ->assign('jtl_token', getTokenInput())
       ->assign('shopURL', $shopURL)
       ->assign('PFAD_ADMIN', PFAD_ADMIN)
       ->assign('JTL_CHARSET', JTL_CHARSET)
       ->assign('session_name', session_name())
       ->assign('session_id', session_id())
       ->assign('currentTemplateDir', $currentTemplateDir)
       ->assign('currentTheme', $currentTheme)
       ->assign('lang', 'german')
       ->assign('admin_css', $resourcePaths['css'])
       ->assign('admin_js', $resourcePaths['js'])
       ->assign('account', $oAccount->account())
       ->assign('PFAD_CKEDITOR', $shopURL . '/' . PFAD_CKEDITOR)
       ->assign('PFAD_CODEMIRROR', $shopURL . '/' . PFAD_CODEMIRROR)
       ->assign('Einstellungen', $Einstellungen)
       ->assign('oLinkOberGruppe_arr', $oLinkOberGruppe_arr)
       ->assign('SektionenEinstellungen', $configSections)
       ->assign('kAdminmenuEinstellungen', KADMINMENU_EINSTELLUNGEN)
       ->assign('notifications', Notification::getInstance())
       ->assign('favorites', $oAccount->favorites());
