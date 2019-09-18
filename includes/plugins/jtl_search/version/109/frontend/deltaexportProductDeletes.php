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

        if (Shop::DB()->query('UPDATE tjtlsearchdeltaexport SET bDelete = 1, dLastModified = now() WHERE kId = ' . $oObj->kId . ' AND eDocumentType = "' . $oObj->eDocumentType . '"', 3) == 0) {
            Shop::DB()->insert('tjtlsearchdeltaexport', $oObj);
        }
    }
} catch (Exception $oEx) {
    error_log("Error: \n" . print_r($oEx, true), 3, PFAD_ROOT . 'jtllogs/dbes.txt');
}
