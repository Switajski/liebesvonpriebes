<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Affiliate trennen
 *
 * @param string $seo
 * @return string
 */
function extFremdeParameter($seo)
{
    $oSeo_arr = preg_split('/[' . EXT_PARAMS_SEPERATORS_REGEX . ']+/', $seo);
    if (is_array($oSeo_arr) && count($oSeo_arr) > 1) {
        $seo = $oSeo_arr[0];
        $cnt = count($oSeo_arr);
        for ($i = 1; $i < $cnt; $i++) {
            $keyValue = explode('=', $oSeo_arr[$i]);
            if (count($keyValue) > 1) {
                list($cName, $cWert)                = $keyValue;
                $_SESSION['FremdParameter'][$cName] = $cWert;
            }
        }
    }

    return $seo;
}
