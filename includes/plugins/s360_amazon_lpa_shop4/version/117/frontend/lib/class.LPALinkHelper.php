<?php

/**
 * Class LPALinkHelper
 *
 * Helper for everything regarding links.
 *
 */
require_once(__DIR__ . '/lpa_defines.php');

class LPALinkHelper {

    private static $id2file = array(
        S360_LPA_FRONTEND_LINK_CHECKOUT => 'lpa_checkout.php',
        S360_LPA_FRONTEND_LINK_COMPLETE => 'lpa_checkout_complete.php',
        S360_LPA_FRONTEND_LINK_CREATE => 'lpa_create.php',
        S360_LPA_FRONTEND_LINK_LOGIN => 'lpa_login.php',
        S360_LPA_FRONTEND_LINK_MERGE => 'lpa_merge.php',
        S360_LPA_FRONTEND_LINK_PROCESS_FAILURE => 'lpa_process_failure.php',
        S360_LPA_FRONTEND_LINK_PROCESS_SUCCESS => 'lpa_process_success.php'
    );

    public static function getFrontendLinkUrl($id, $seoPartOnly = false) {
        $file = self::$id2file[$id];
        if(empty($file)) {
            return null;
        }

        $link = Shop::DB()->select('tpluginlinkdatei', 'cDatei', $file);
        if(empty($link)) {
            return null;
        }

        $seo = Shop::DB()->select('tseo', 'kSprache', Shop::getLanguage(false), 'cKey', 'kLink', 'kKey', $link->kLink);
        if(empty($seo)) {
            return null;
        }

        if($seoPartOnly) {
            return $seo->cSeo;
        }
        return Shop::getURL(true) . '/' . $seo->cSeo;
    }
}