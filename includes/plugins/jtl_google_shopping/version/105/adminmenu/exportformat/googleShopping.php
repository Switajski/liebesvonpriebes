<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

$nLimitN       = 0;
$nLimitM       = 0;
$cTmpDateiname = substr($exportformat->cDateiname, 0, strrpos($exportformat->cDateiname, '.')) . '.xml';

if (isset($queue->nLimit_n)) {
    $nLimitN = $queue->nLimit_n;
} elseif (isset($oJobQueue->nLimitN)) {
    $nLimitN = $oJobQueue->nLimitN;
}
if (isset($queue->nLimit_m)) {
    $nLimitM = $queue->nLimit_m;
} elseif (isset($oJobQueue->nLimitM)) {
    $cTmpDateiname        = 'cron_' . $cTmpDateiname;
    $nLimitM              = $oJobQueue->nLimitM;
    $oJobQueue->nInArbeit = 1;
    $oJobQueue->updateJobInDB();
}

//falls datei existiert, loeschen
if ($nLimitN == 0 && file_exists(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname)) {
    unlink(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname);
}

$f = fopen(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname, 'a');

require_once 'classes/class.XML_GoogleShopping.inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

foreach ($oPlugin->oPluginEinstellung_arr as $oEinstellung) {
    $cEinstellungen_arr[$oEinstellung->cName] = $oEinstellung->cWert;
}
foreach ($ExportEinstellungen as $key => $value) {
    $cEinstellungen_arr[$key] = $value;
}
$cEinstellungen_arr['cPluginID'] = $oPlugin->cPluginID;

//NEU
$cPreZipFile = (int)(($nLimitN + $nLimitM) / $cEinstellungen_arr['maxItem']);
if (($nLimitN + $nLimitM) % $cEinstellungen_arr['maxItem'] != 0) {
    $cPreZipFile++;
}
if ($cPreZipFile > 1) {
    $exportformat->cDateiname = $cPreZipFile . '_' . $exportformat->cDateiname;
}
// Ende NEU

$oXML = new XML_GoogleShopping($exportformat, $f, $cEinstellungen_arr);

//kArtikel der zu Exportierenden Artikel aus der DB holen
$cSQL_arr = baueArtikelExportSQL($exportformat);
$res      = Shop::DB()->query(
    "SELECT tartikel.kArtikel
        FROM tartikel
        LEFT JOIN tartikelattribut
            ON tartikelattribut.kArtikel = tartikel.kArtikel
            AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
        " . $cSQL_arr['Join'] . "
        LEFT JOIN tartikelsichtbarkeit
            ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
            AND tartikelsichtbarkeit.kKundengruppe = " . $exportformat->kKundengruppe . "
        WHERE tartikelattribut.kArtikelAttribut IS NULL" . $cSQL_arr['Where'] . "
            AND tartikelsichtbarkeit.kArtikel IS NULL
        ORDER BY kArtikel
        LIMIT " . $nLimitN . ", " . $nLimitM, 9
);

//Anzahl ALLER zu exportierenden Artikel
$count = Shop::DB()->query(
    "SELECT count(tartikel.kArtikel) AS anzahl
        FROM tartikel
        LEFT JOIN tartikelattribut
            ON tartikelattribut.kArtikel = tartikel.kArtikel
            AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
        " . $cSQL_arr['Join'] . "
        LEFT JOIN tartikelsichtbarkeit
            ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
            AND tartikelsichtbarkeit.kKundengruppe = " . $exportformat->kKundengruppe . "
        WHERE tartikelattribut.kArtikelAttribut IS NULL" . $cSQL_arr['Where'] . "
            AND tartikelsichtbarkeit.kArtikel IS NULL", 1
);

//Laden der Artikel
$oXML->setExportArticleIds($res);
unset($res);

//XML-Head schreiben
if ($nLimitN == 0 || $nLimitN % $cEinstellungen_arr['maxItem'] == 0) {
    $oXML->writeHead();
}

//Artikel in XML schreiben
$oXML->writeContent();

//wenn ALLE Artikel exportiert wurden diese in ein Zip-Archiv speichern
if ($nLimitN + $nLimitM >= $count->anzahl) {
    $oXML->writeFoot();
    fclose($f);
    if (isset($queue)) {
        Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt=now() WHERE kExportformat=" . $queue->kExportformat, 4);
    } else {
        $oJobQueue->nLimitN += ($count->anzahl - $nLimitN);
        $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
        updateExportformatQueueBearbeitet($oJobQueue);
        Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt=now() WHERE kExportformat=" . $oJobQueue->kKey, 4);
        $oJobQueue->deleteJobInDB();
    }

    $cPreFile = (int)(($nLimitN + $nLimitM) / $cEinstellungen_arr['maxItem']);
    if (($nLimitN + $nLimitM) % $cEinstellungen_arr['maxItem'] != 0) {
        $cPreFile++;
    }
    if (file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
        unlink(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
    }
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive;
        if ($zip->open(
                PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname,
                (is_file(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname) ? null : ZIPARCHIVE::CREATE)
            ) === true
        ) {
            $zip->addFile(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname, $cTmpDateiname);
            $zip->close();
        }
    } else {
        require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';

        $archive = new PclZip(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
        if (!$archive->create(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname, PCLZIP_OPT_REMOVE_ALL_PATH)) {
            echo 'Es ist ein Fehler beim Zippen der Datei aufgetreten!';
        }
    }

    unlink(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname);
    //Wenn MaxItem erreicht wurde dann XML-Foot schreiben und Datei einem Zip-Archiv hinzufuegen
} elseif (($nLimitN + $nLimitM) % $cEinstellungen_arr['maxItem'] == 0) {
    $oXML->writeFoot();
    fclose($f);

    if (file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
        unlink(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
    }
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive;
        if ($zip->open(
                PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname,
                (is_file(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname) ? null : ZIPARCHIVE::CREATE)
            ) === true
        ) {
            $zip->addFile(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname, $cTmpDateiname);
            $zip->close();
        }
    } else {
        //PCLZIP
        require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';

        $archive = new PclZip(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
        if (!$archive->create(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname, PCLZIP_OPT_REMOVE_ALL_PATH)) {
            echo 'Es ist ein Fehler beim Zippen der Datei aufgetreten!';
        }
    }
    unlink(PFAD_ROOT . PFAD_EXPORT . $cTmpDateiname);
    if (isset($queue)) {
        Shop::DB()->query("UPDATE texportqueue SET nLimit_n=nLimit_n+" . $nLimitM . " WHERE kExportqueue=" . $queue->kExportqueue, 4);
        $cURL = Shop::getURL() . '/' . PFAD_ADMIN . 'do_export.php?e=' . $queue->kExportqueue . '&back=admin&token=' . $_SESSION['jtl_token'];
        header('Location: ' . $cURL);
        exit;
    } else {
        $oJobQueue->nLimitN += $oJobQueue->nLimitM;
        $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
        $oJobQueue->updateJobInDB();
        updateExportformatQueueBearbeitet($oJobQueue);
        $oJobQueue->nInArbeit = 0;
        $oJobQueue->updateJobInDB();
    }
} else {
    if (isset($queue)) {
        Shop::DB()->query("UPDATE texportqueue SET nLimit_n=nLimit_n+" . $nLimitM . " WHERE kExportqueue=" . $queue->kExportqueue, 4);
        $cURL = Shop::getURL() . '/' . PFAD_ADMIN . 'do_export.php?e=' . $queue->kExportqueue . '&back=admin&token=' . $_SESSION['jtl_token'];
        header('Location: ' . $cURL);
        exit;
    } else {
        $oJobQueue->nLimitN += $oJobQueue->nLimitM;
        $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
        $oJobQueue->updateJobInDB();
        updateExportformatQueueBearbeitet($oJobQueue);
        $oJobQueue->nInArbeit = 0;
        $oJobQueue->updateJobInDB();
    }
}
