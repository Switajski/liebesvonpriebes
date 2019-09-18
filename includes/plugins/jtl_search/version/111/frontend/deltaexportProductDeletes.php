<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

try {
    if (isset($args_arr['kArtikel']) && $args_arr['kArtikel'] > 0) {
        $oObj                = new stdClass();
        $oObj->kId           = $args_arr['kArtikel'];
        $oObj->eDocumentType = 'product';
        $oObj->bDelete       = 1;
        $oObj->dLastModified = 'now()';

        Shop::DB()->query('
            REPLACE INTO tjtlsearchdeltaexport 
                VALUES (' . $oObj->kId . ', "' . $oObj->eDocumentType . '", ' . $oObj->bDelete . ', ' . $oObj->dLastModified . ')', 3
        );
    }
} catch (Exception $oEx) {
    error_log("Error: \n" . print_r($oEx, true), 3, PFAD_ROOT . 'jtllogs/dbes.txt');
}
