<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

/**
 * @return JTLSmarty
 */
function getSmarty()
{
    $smarty = new JTLSmarty(true, false, false, 'cron');
    $smarty->setCaching(0)
           ->setDebugging(0)
           ->setTemplateDir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES)
           ->setCompileDir(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR)
           ->setConfigDir($smarty->getTemplateDir($smarty->context) . 'lang/')
           ->registerResource('db', new SmartyResourceNiceDB('export'));

    return $smarty;
}

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteExportformate($oJobQueue)
{
    $oJobQueue->nInArbeit        = 1;
    $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
    $oJobQueue->updateJobInDB();
    $oExportformat        = $oJobQueue->holeJobArt();
    if (empty($oExportformat)) {
        Jtllog::cronLog('Invalid export format for job queue ID ' . $oJobQueue->kJobQueue);
        return;
    }
    // Special Export?
    if ((int)$oExportformat->nSpecial === SPECIAL_EXPORTFORMAT_YATEGO) {
        // Kampagne
        if (isset($oExportformat->kKampagne) && $oExportformat->kKampagne > 0) {
            $oKampagne = Shop::DB()->select('tkampagne', ['kKampagne', 'nAktiv'], [(int)$oExportformat->kKampagne, 1]);
            if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                $oExportformat->tkampagne_cParameter = $oKampagne->cParameter;
                $oExportformat->tkampagne_cWert      = $oKampagne->cWert;
            }
        }
        gibYategoExport($oExportformat, $oJobQueue, getEinstellungenExport($oExportformat->kExportformat));
    } else {
        $ef = new Exportformat($oExportformat->kExportformat);
        $ef->setTempFileName('tmp_' . $oExportformat->cDateiname)->startExport($oJobQueue, false, false, true);
    }
}


/**
 * @param object $oJobQueue
 * @return bool
 */
function updateExportformatQueueBearbeitet($oJobQueue)
{
    if ($oJobQueue->kJobQueue > 0) {
        Shop::DB()->delete('texportformatqueuebearbeitet', 'kJobQueue', (int)$oJobQueue->kJobQueue);

        $oExportformatQueueBearbeitet                   = new stdClass();
        $oExportformatQueueBearbeitet->kJobQueue        = $oJobQueue->kJobQueue;
        $oExportformatQueueBearbeitet->kExportformat    = $oJobQueue->kKey;
        $oExportformatQueueBearbeitet->nLimitN          = $oJobQueue->nLimitN;
        $oExportformatQueueBearbeitet->nLimitM          = $oJobQueue->nLimitM;
        $oExportformatQueueBearbeitet->nInArbeit        = $oJobQueue->nInArbeit;
        $oExportformatQueueBearbeitet->dStartZeit       = $oJobQueue->dStartZeit;
        $oExportformatQueueBearbeitet->dZuletztGelaufen = $oJobQueue->dZuletztGelaufen;

        Shop::DB()->insert('texportformatqueuebearbeitet', $oExportformatQueueBearbeitet);

        return true;
    }

    return false;
}

/**
 * @param string $n
 * @return mixed
 */
function getNum($n)
{
    return str_replace('.', ',', $n);
}

/**
 * @param string $img
 * @return string
 */
function getURL($img)
{
    return $img ? Shop::getURL() . '/' . $img : '';
}

/**
 * @param string $file
 * @param string $data
 */
function writeFile($file, $data)
{
    $handle = fopen($file, 'a');
    fwrite($handle, $data);
    fclose($handle);
}

/**
 * @param array $cGlobalAssoc_arr
 * @param int   $nLimitN
 * @return string
 */
function makecsv($cGlobalAssoc_arr, $nLimitN = 0)
{
    global $queue;
    $out = '';
    if (isset($queue->nLimit_n)) {
        $nLimitN = $queue->nLimit_n;
    }
    $nLimitN = (int)$nLimitN;
    if (is_array($cGlobalAssoc_arr) && count($cGlobalAssoc_arr) > 0) {
        if ($nLimitN === 0) {
            $fieldnames = array_keys($cGlobalAssoc_arr[0]);
            $out        = ESC . implode(ESC . DELIMITER . ESC, $fieldnames) . ESC . CRLF;
        }
        foreach ($cGlobalAssoc_arr as $cGlobalAssoc) {
            $out .= ESC . implode(ESC . DELIMITER . ESC, $cGlobalAssoc) . ESC . CRLF;
        }
    }

    return $out;
}

/**
 * @param string $tpl_name
 * @param string $tpl_source
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_template($tpl_name, &$tpl_source, $smarty)
{
    $exportformat = Shop::DB()->select('texportformat', 'kExportformat', $tpl_name);

    if (empty($exportformat->kExportformat) || !$exportformat->kExportformat > 0) {
        return false;
    }
    $tpl_source = $exportformat->cContent;

    return true;
}

/**
 * @param string     $tpl_name
 * @param string|int $tpl_timestamp
 * @param JTLSmarty  $smarty
 * @return bool
 */
function db_get_timestamp($tpl_name, &$tpl_timestamp, $smarty)
{
    $tpl_timestamp = time();

    return true;
}

/**
 * @param string $tpl_name
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_secure($tpl_name, $smarty)
{
    return true;
}

/**
 * @param string $tpl_name
 * @param JTLSmarty $smarty
 */
function db_get_trusted($tpl_name, $smarty)
{
}

/**
 * @param array $catlist
 * @return array
 */
function getCats($catlist)
{
    $cats     = [];
    $shopcats = [];
    $res      = Shop::DB()->query("
        SELECT kKategorie, cName, kOberKategorie, nSort 
          FROM tkategorie", 10
    );
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $cats[array_shift($row)] = $row;
    }
    foreach ($catlist as $cat_id) {
        $this_cat = $cat_id;
        $catdir   = [];
        while ($this_cat > 0) {
            array_unshift($catdir, [$this_cat, $cats[$this_cat]['cName']]);
            $this_cat = $cats[$this_cat]['kOberKategorie'];
        }
        $shopcats[] = [
            'foreign_id_h' => $catdir[0][0],
            'foreign_id_m' => $catdir[1][0],
            'foreign_id_l' => $catdir[2][0],
            'title_h'      => $catdir[0][1],
            'title_m'      => $catdir[1][1],
            'title_l'      => $catdir[2][1],
            'sorting'      => $cats[$cat_id]['nSort']
        ];
    }

    return $shopcats;
}

/**
 * @param string $entry
 */
function writeLogTMP($entry)
{
    $logfile = fopen(PFAD_LOGFILES . 'exportformat.log', 'a');
    fwrite($logfile, "\n[" . date('m.d.y H:i:s') . ' ' . microtime() . '] ' . $_SERVER['SCRIPT_NAME'] . "\n" . $entry);
    fclose($logfile);
}

/**
 * @param object $exportformat
 * @param object $oJobQueue
 * @param array  $ExportEinstellungen
 * @return bool
 */
function gibYategoExport($exportformat, $oJobQueue, $ExportEinstellungen)
{
    $smarty = getSmarty();

    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

    define('DELIMITER', ';');
    define('ESC', '"');
    define('CRLF', "\n");
    define('PATH', PFAD_ROOT . PFAD_EXPORT_YATEGO);
    define('DESCRIPTION_TAGS', '<a><b><i><u><p><br><hr><h1><h2><h3><h4><h5><h6><ul><ol><li><span><font><table><colgroup>');

    if (!pruefeYategoExportPfad()) {
        Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt = now() WHERE kExportformat = " . (int)$oJobQueue->kKey, 4);
        $oJobQueue->deleteJobInDB();
        unset($oJobQueue);

        return false;
    }
    $oJobQueue->nLimitN = (int)$oJobQueue->nLimitN;
    //falls dateien existieren, löschen
    if ($oJobQueue->nLimitN === 0 && file_exists(PATH . 'varianten.csv')) {
        unlink(PATH . 'varianten.csv');
    }
    if ($oJobQueue->nLimitN === 0 && file_exists(PATH . 'artikel.csv')) {
        unlink(PATH . 'artikel.csv');
    }
    if ($oJobQueue->nLimitN === 0 && file_exists(PATH . 'shopkategorien.csv')) {
        unlink(PATH . 'shopkategorien.csv');
    }
    if ($oJobQueue->nLimitN === 0 && file_exists(PATH . 'lager.csv')) {
        unlink(PATH . 'lager.csv');
    }
    // Global Array
    $oGlobal_arr          = [];
    $oGlobal_arr['lager'] = [];

    setzeSteuersaetze();
    $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
    $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
    $_SESSION['kSprache']                                 = $exportformat->kSprache;
    $_SESSION['kKundengruppe']                            = $exportformat->kKundengruppe;
    $_SESSION['Kundengruppe']->kKundengruppe              = $exportformat->kKundengruppe;

    $KategorieListe = [];
    $oArtikel_arr   = Shop::DB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            JOIN tartikelattribut 
                ON tartikelattribut.kArtikel = tartikel.kArtikel
            WHERE tartikelattribut.cName = 'yategokat'
                AND tartikel.kVaterArtikel = 0
            ORDER BY tartikel.kArtikel
            LIMIT " . (int)$oJobQueue->nLimitN . ", " . (int)$oJobQueue->nLimitM, 2
    );

    if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($oArtikel_arr as $i => $tartikel) {
            $Artikel = new Artikel();
            $Artikel->fuelleArtikel($tartikel->kArtikel, $defaultOptions, $exportformat->kKundengruppe, $exportformat->kSprache);

            verarbeiteYategoExport($Artikel, $exportformat, $ExportEinstellungen, $KategorieListe, $oGlobal_arr);
        }

        $KategorieListe                = array_keys($KategorieListe);
        $oGlobal_arr['shopkategorien'] = getCats($KategorieListe);

        if ($exportformat->cKodierung === 'UTF-8' || $exportformat->cKodierung === 'UTF-8noBOM') {
            $cHeader = $exportformat->cKodierung === 'UTF-8' ? "\xEF\xBB\xBF" : '';
            writeFile(PATH . 'varianten.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['varianten'], $oJobQueue->nLimitN) . CRLF .
                    makecsv($oGlobal_arr['variantenwerte'], $oJobQueue->nLimitN)));
            writeFile(PATH . 'artikel.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['artikel'], $oJobQueue->nLimitN)));
            writeFile(PATH . 'shopkategorien.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['shopkategorien'], $oJobQueue->nLimitN)));
            writeFile(PATH . 'lager.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['lager'], $oJobQueue->nLimitN)));
        } else {
            writeFile(PATH . 'varianten.csv', makecsv($oGlobal_arr['varianten'], $oJobQueue->nLimitN) . CRLF .
                makecsv($oGlobal_arr['variantenwerte'], $oJobQueue->nLimitN));
            writeFile(PATH . 'artikel.csv', makecsv($oGlobal_arr['artikel'], $oJobQueue->nLimitN));
            writeFile(PATH . 'shopkategorien.csv', makecsv($oGlobal_arr['shopkategorien'], $oJobQueue->nLimitN));
            writeFile(PATH . 'lager.csv', makecsv($oGlobal_arr['lager'], $oJobQueue->nLimitN));
        }

        $oJobQueue->nLimitN         += count($oArtikel_arr);
        $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
        $oJobQueue->nInArbeit        = 0;
        $oJobQueue->updateJobInDB();
        updateExportformatQueueBearbeitet($oJobQueue);
    } else {
        Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt = now() WHERE kExportformat = " . (int)$oJobQueue->kKey, 4);
        $oJobQueue->deleteJobInDB();
        unset($oJobQueue);
    }

    return true;
}
