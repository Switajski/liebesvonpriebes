<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int   $kKundengruppe
 * @param int   $kSprache
 * @param array $cPost_arr
 * @param int   $kText
 * @return bool
 */
function speicherAGBWRB($kKundengruppe, $kSprache, $cPost_arr, $kText = 0)
{
    $kText         = (int)$kText;
    $kKundengruppe = (int)$kKundengruppe;
    $kSprache      = (int)$kSprache;
    if ($kKundengruppe > 0 && $kSprache > 0) {
        $oAGBWRB = new stdClass();
        if ($kText > 0) {
            Shop::DB()->delete('ttext', 'kText', $kText);
            $oAGBWRB->kText = $kText;
        }
        // Soll Standard sein?
        if (isset($cPost_arr['nStandard']) && (int)$cPost_arr['nStandard'] > 0) {
            // Standard umsetzen
            Shop::DB()->query("UPDATE ttext SET nStandard = 0", 3);
        }
        $oAGBWRB->kSprache            = $kSprache;
        $oAGBWRB->kKundengruppe       = $kKundengruppe;
        $oAGBWRB->cAGBContentText     = $cPost_arr['cAGBContentText'];
        $oAGBWRB->cAGBContentHtml     = $cPost_arr['cAGBContentHtml'];
        $oAGBWRB->cWRBContentText     = $cPost_arr['cWRBContentText'];
        $oAGBWRB->cWRBContentHtml     = $cPost_arr['cWRBContentHtml'];
        $oAGBWRB->cWRBFormContentText = $cPost_arr['cWRBFormContentText'];
        $oAGBWRB->cWRBFormContentHtml = $cPost_arr['cWRBFormContentHtml'];
        $oAGBWRB->nStandard           = isset($cPost_arr['nStandard']) ? $cPost_arr['nStandard'] : 0;

        Shop::DB()->insert('ttext', $oAGBWRB);

        return true;
    }

    return false;
}
