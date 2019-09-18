<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cDynKey
 * @return stdClass
 */
function holeRegParameter($cDynKey)
{
    $shopURL                                = Shop::getURL();
    $oParams                                = new stdClass();
    $oParams->mode                          = 'anbieter';
    $oParams->portalmerchant                = 'JTLSHOP';
    $oParams->skriptname                    = 'Ihr%20Warenkorb';
    $oParams->linkType                      = 'transaction';
    $oParams->test                          = 'False';
    $oParams->readOnly                      = 'False';
    $oParams->cb_regversion                 = '1.1';
    $oParams->EnableDynamicCurrencyHandover = 'True';
    $oParams->prn_link                      = 'True';
    $oParams->activateTMI                   = 'True';
    $oParams->SellerIDMaster                = '21699122';
    $oParams->dynkey                        = $cDynKey;
    $oParams->ConfigurationURL              = $shopURL . '/' . PFAD_ADMIN . 'clickandbuy.php?succ=1';
    $oParams->domainurl                     = $shopURL . '/';
    $oParams->domainname                    = $shopURL . '/';
    $oParams->skripturl                     = $shopURL . '/';

    $cMember_arr = array_keys(get_object_vars($oParams));
    $cURL        = '';
    if (is_array($cMember_arr) && count($cMember_arr) > 0) {
        $cURL .= 'https://eu.clickandbuy.com/cgi-bin/register.pl';
        foreach ($cMember_arr as $i => $cMember) {
            if ($i > 0) {
                $cURL .= '&' . $cMember . '=' . $oParams->$cMember;
            } else {
                $cURL .= '?' . $cMember . '=' . $oParams->$cMember;
            }
        }
    }
    // RFC 2396 TODO
    $oParams->fgkey = md5('JTT3Nni5FR83r' . $cURL);
    $oParams->cURL  = $cURL . '&fgkey=' . $oParams->fgkey;

    return $oParams;
}
