<?php
/**
 * HOOK_SMARTY_OUTPUTFILTER
 * add scripts/css/configurator html to DOM
 *
 * @package     tpl
 * @createdAt   24.09.15
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */

global $smarty, $Einstellungen;
if (isset($Einstellungen['template']['demo']['demo_mode'], $Einstellungen['template']['theme']['theme_default'])
    && $Einstellungen['template']['demo']['demo_mode'] === 'Y'
    && Shop::isAdmin()
) {
    /** @global Plugin $oPlugin */
    require_once $oPlugin->cFrontendPfad . 'inc/class.JTLTplHelper.php';
    $adminHelper        = new JTLTplHelper($oPlugin);
    $tpl                = Template::getInstance();
    $currentTemplateDir = $smarty->getTemplateVars('currentTemplateDir');
    $lessPath           = $currentTemplateDir . 'themes/' .
        $Einstellungen['template']['theme']['theme_default'] . '/less/theme.less';

    if (JTLSmarty::$isChildTemplate === true) {
        $lessFile = PFAD_ROOT . $lessPath;
        if (!file_exists($lessFile)) {
            $parentTemplateDir = $smarty->getTemplateVars('parentTemplateDir');
            if ($parentTemplateDir !== null) {
                $lessPath           = $parentTemplateDir . 'themes/' .
                    $Einstellungen['template']['theme']['theme_default'] . '/less/theme.less';
                $lessFile           = PFAD_ROOT . $lessPath;
                $currentTemplateDir = $parentTemplateDir;
            }
        }
    }
    $lessURL = Shop::getURL() . '/' . $lessPath;
    $smarty->assign('less_vars', $adminHelper->getOptions($Einstellungen['template']['theme']['theme_default'], $currentTemplateDir))
           ->assign('shop_url', Shop::getURL())
           ->assign('is_admin', true)
           ->assign('template_dir', PFAD_ROOT . $currentTemplateDir)
           ->assign('theme', $Einstellungen['template']['theme']['theme_default'])
           ->assign('tpl_config_lang_vars', $oPlugin->oPluginSprachvariableAssoc_arr)
           ->assign('kk_fontjs_source', $oPlugin->cAdminmenuPfadURL . 'js/fonts.js');
    $html = $smarty->fetch($oPlugin->cFrontendPfad . 'template/tpl_configurator_frontend.tpl');
    pq('body')->append('<link type="text/css" rel="stylesheet" href="' .
        $oPlugin->cFrontendPfadURLSSL . 'css/tpl_configurator.css" />' . "\n" .
        '<link type="text/css" rel="stylesheet" href="' .
        $oPlugin->cFrontendPfadURLSSL . 'css/bootstrap-colorpicker.min.css" />' . "\n" .
        '<link rel="stylesheet/less" type="text/css" href="' . $lessURL . '" />' . "\n" .
        '<script type="text/javascript">
            var less = {
                env: "development",
                async: false,
                fileAsync: false,
                dumpLineNumbers: "comments",
                relativeUrls: true
            };
            jtl.load("' . $oPlugin->cFrontendPfadURLSSL . 'js/bootstrap-colorpicker.min.js", "' .
        $oPlugin->cFrontendPfadURLSSL . 'js/tpl_configurator.js", "' .
        $oPlugin->cFrontendPfadURLSSL . 'js/less-2.5.1.min.js");
        </script>'
    );
    pq('body')->append($html);
    //only makes sense when using async - which can't be used because of responsive images..
    //pq('body')->attr('class', 'loading');
}
