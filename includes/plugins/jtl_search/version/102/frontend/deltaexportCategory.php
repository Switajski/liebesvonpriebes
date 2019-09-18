<?php
try {
    if (isset($args_arr['oKategorie']->kKategorie) && $args_arr['oKategorie']->kKategorie > 0) {
        $oCategoryObj = new stdClass();
        $oCategoryObj->kId = intval($args_arr['oKategorie']->kKategorie);
        $oCategoryObj->eDocumentType = 'category';
        $oCategoryObj->bDelete = 0;
        $oCategoryObj->dLastModified = 'now()';

        if ($GLOBALS['DB']->executeQuery('UPDATE tjtlsearchdeltaexport SET bDelete = 0, dLastModified = now() WHERE kId = '.$oCategoryObj->kId.' AND eDocumentType = "'.$oCategoryObj->eDocumentType.'";', 3) == 0) {
            $GLOBALS['DB']->insertRow('tjtlsearchdeltaexport', $oCategoryObj);
        }
    }
} catch (Exception $oEx) {
    error_log("\nError: \n".print_r($oEx, true)." \n", 3, PFAD_ROOT.'jtllogs/jtlsearch_error.txt');
}
