<?php
try {
    if (isset($args_arr['kHersteller']) && $args_arr['kHersteller'] > 0) {
        $oObj = new stdClass();
        $oObj->kId = $args_arr['kHersteller'];
        $oObj->eDocumentType = 'manufacturer';
        $oObj->bDelete = 1;
        $oObj->dLastModified = 'now()';

        if ($GLOBALS['DB']->executeQuery('UPDATE tjtlsearchdeltaexport SET bDelete = 1, dLastModified = now() WHERE kId = '.$oObj->kId.' AND eDocumentType = "'.$oObj->eDocumentType.'";', 10) == 0) {
            $GLOBALS['DB']->insertRow('tjtlsearchdeltaexport', $oObj);
        }
    }
} catch (Exception $oEx) {
    error_log("Error: \n".print_r($oEx, true), 3, PFAD_ROOT.'jtllogs/dbes.txt');
}
