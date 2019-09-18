<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

try {
    if (isset($args_arr['oHersteller']->kHersteller) && $args_arr['oHersteller']->kHersteller > 0) {
        $oCategoryObj                = new stdClass();
        $oCategoryObj->kId           = intval($args_arr['oHersteller']->kHersteller);
        $oCategoryObj->eDocumentType = 'manufacturer';
        $oCategoryObj->bDelete       = 0;
        $oCategoryObj->dLastModified = 'now()';

        if (Shop::DB()->query('UPDATE tjtlsearchdeltaexport SET bDelete = 0, dLastModified = now() WHERE kId = ' . $oCategoryObj->kId . ' AND eDocumentType = "' . $oCategoryObj->eDocumentType . '";', 3) == 0) {
            Shop::DB()->insert('tjtlsearchdeltaexport', $oCategoryObj);
        }
    }
} catch (Exception $oEx) {
    error_log("\nError: \n" . print_r($oEx, true) . " \n", 3, PFAD_ROOT . 'jtllogs/jtlsearch_error.txt');
}
