<?php
/**
 * Handles Plugin-Configuration settings tab.
 */
global $oPlugin;
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');

Shop::Smarty()->assign('pluginAdminUrl', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&');

$backupPath = $oPlugin->cFrontendPfad . 'backup/';

/*
 * Test if backup folder is readable and writable.
 */
$writeResult = is_writable($backupPath);
$readResult = is_readable($backupPath);

Shop::Smarty()->assign('lpa_backup_writable', $writeResult)
              ->assign('lpa_backup_readable', $readResult);

/**
 * Check if Shop 3 plugin tables are present and if so, if an import could
 * possibly collide with the current database of the shop 3 plugin.
 * 
 * This only tests for "account mapping" because that table would contain the first data!
 */
$importShop3State = "disabled";
$test = Shop::DB()->query("SHOW TABLES LIKE 'xplugin_s360_amazon_lpa_taccountmapping'", 2);
if(!empty($test)) {
    // shop 3 table exists and is not empty
    $test = Shop::DB()->query("SELECT * FROM ".S360_LPA_TABLE_ACCOUNTMAPPING, 2);
    if(!empty($test)) {
        // shop 4 table is not empty, either. we show the collision warning.
        $importShop3State = "warning";
    } else {
        // shop 4 table is empty
        $importShop3State = "enabled";
    }
}
Shop::Smarty()->assign('lpa_import_old_plugin', $importShop3State);

$exportFolders = scandir($backupPath);
if (is_array($exportFolders)) {
    $foldersFiltered = array();
    foreach ($exportFolders as $folder) {
        if ($folder === '.' || $folder === '..') {
            continue;
        }
        if (is_dir($backupPath . $folder)) {
            $foldersFiltered[] = $folder;
        }
    }
    Shop::Smarty()->assign('lpa_backup_folders', $foldersFiltered);
}

// Test if easytemplate360 is installed and active
$templateName = '';
if(Template::getInstance()->getParent() === null) {
    $templateName = Template::getInstance()->getDir();
} else {
    $templateName = Template::getInstance()->getParent();
}
if(stripos($templateName, 'easytemplate360') !== false) {
    Shop::Smarty()->assign('lpa_easytemplate_detected', 1);
} else {
    Shop::Smarty()->assign('lpa_easytemplate_detected', 0);
}

// Workaround for failed save attempts on first opening
Shop::Smarty()->assign('s360_jtl_token', getTokenInput());

Shop::Smarty()->display($oPlugin->cAdminmenuPfad . "template/extended_functions.tpl");
