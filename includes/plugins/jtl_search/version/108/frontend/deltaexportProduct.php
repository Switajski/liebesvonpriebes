<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

try {
    if (isset($args_arr['oArtikel']->kArtikel) && $args_arr['oArtikel']->kArtikel > 0) {
        $oProductObj                = new stdClass();
        $oProductObj->kId           = intval($args_arr['oArtikel']->kArtikel);
        $oProductObj->eDocumentType = 'product';
        $oProductObj->bDelete       = 0;
        $oProductObj->dLastModified = 'now()';

        if (Shop::DB()->query('UPDATE tjtlsearchdeltaexport SET bDelete = 0, dLastModified = now() WHERE kId = ' . $oProductObj->kId . ' AND eDocumentType = "' . $oProductObj->eDocumentType . '";', 3) == 0) {
            Shop::DB()->insert('tjtlsearchdeltaexport', $oProductObj);
        }
    }
} catch (Exception $oEx) {
    error_log("\nError: \n" . print_r($oEx, true) . " \n", 3, PFAD_ROOT . 'jtllogs/jtlsearch_error.txt');
}
