<?php
/**
 * HOOK_INDEX_NAVI_HEAD_POSTGET
 *
 * called on save button click in frontend template configurator
 * saves the config to db and compiles css from less
 *
 * @package     tpl
 * @createdAt   24.09.15
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */

/** @global Plugin $oPlugin */
if (isset($_POST['tpl_config_save_export'])) {
    $tplConfig = Shop::getConfig([CONF_TEMPLATE]);
    if (isset($tplConfig['template']['demo']['demo_mode']) && $tplConfig['template']['demo']['demo_mode'] === 'Y') {
        require_once $oPlugin->cFrontendPfad . 'inc/class.JTLTplHelper.php';
        $adminHelper = new JTLTplHelper($oPlugin);
        $result      = $adminHelper->handlePost($_POST);
        header('Content-Type: application/json');
        die(json_encode($result));
    }
}
