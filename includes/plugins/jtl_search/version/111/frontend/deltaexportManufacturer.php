<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

try {
    if (isset($args_arr['oHersteller']->kHersteller) && $args_arr['oHersteller']->kHersteller > 0) {
        $oObj                = new stdClass();
        $oObj->kId           = intval($args_arr['oHersteller']->kHersteller);
        $oObj->eDocumentType = 'manufacturer';
        $oObj->bDelete       = 0;
        $oObj->dLastModified = 'now()';

        Shop::DB()->query('
            REPLACE INTO tjtlsearchdeltaexport 
                VALUES (' . $oObj->kId . ', "' . $oObj->eDocumentType . '", ' . $oObj->bDelete . ', ' . $oObj->dLastModified . ')', 3
        );
    }
} catch (Exception $oEx) {
    error_log("\nError: \n" . print_r($oEx, true) . " \n", 3, PFAD_ROOT . 'jtllogs/jtlsearch_error.txt');
}
