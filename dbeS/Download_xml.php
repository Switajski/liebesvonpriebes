<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
            }
            $return = 0;
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' .
                        filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);
                if ($zip['filename'] === 'del_download.xml') {
                    bearbeiteDeletes($xml);
                } else {
                    bearbeiteInsert($xml);
                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Download_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Download_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
}

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (is_array($xml['del_downloads']['kDownload'])) {
        foreach ($xml['del_downloads']['kDownload'] as $kDownload) {
            if ((int)$kDownload > 0) {
                loescheDownload($kDownload);
            }
        }
    } elseif ((int)$xml['del_downloads']['kDownload'] > 0) {
        loescheDownload($xml['del_downloads']['kDownload']);
    }
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    if (isset($xml['tDownloads']['tDownload attr']) && is_array($xml['tDownloads']['tDownload attr'])) {
        // 1 Download
        $oDownload_arr = mapArray($xml['tDownloads'], 'tDownload', $GLOBALS['mDownload']);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Single download, oDownload_arr: ' . print_r($oDownload_arr, true), JTLLOG_LEVEL_DEBUG);
        }
        if ($oDownload_arr[0]->kDownload > 0) {
            $oDownloadSprache_arr = mapArray($xml['tDownloads']['tDownload'], 'tDownloadSprache', $GLOBALS['mDownloadSprache']);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('oDownloadSprache_arr: ' . print_r($oDownloadSprache_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
            }
            if (is_array($oDownloadSprache_arr) && count($oDownloadSprache_arr) > 0) {
                DBUpdateInsert('tdownload', $oDownload_arr, 'kDownload');
                $lCount = count($oDownloadSprache_arr);
                for ($i = 0; $i < $lCount; ++$i) {
                    $oDownloadSprache_arr[$i]->kDownload = $oDownload_arr[0]->kDownload;
                    DBUpdateInsert('tdownloadsprache', [$oDownloadSprache_arr[$i]], 'kDownload', 'kSprache');
                }
            }
        }
    } else {
        // N-Downloads
        $oDownload_arr = mapArray($xml['tDownloads'], 'tDownload', $GLOBALS['mDownload']);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Multiple downloads, oDownload_arr: ' . print_r($oDownload_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
        }

        foreach ($oDownload_arr as $i => $oDownload) {
            if ($oDownload->kDownload > 0) {
                $oDownloadSprache_arr = mapArray($xml['tDownloads']['tDownload'][$i], 'tDownloadSprache', $GLOBALS['mDownloadSprache']);
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('oDownloadSprache_arr: ' . print_r($oDownloadSprache_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
                }
                if (is_array($oDownloadSprache_arr) && count($oDownloadSprache_arr) > 0) {
                    DBUpdateInsert('tdownload', [$oDownload], 'kDownload');
                    $cdsaCount = count($oDownloadSprache_arr);
                    for ($j = 0; $j < $cdsaCount; ++$j) {
                        $oDownloadSprache_arr[$j]->kDownload = $oDownload->kDownload;
                        DBUpdateInsert('tdownloadsprache', [$oDownloadSprache_arr[$j]], 'kDownload', 'kSprache');
                    }
                }
            }
        }
    }
}

/**
 * @param int $kDownload
 */
function loescheDownload($kDownload)
{
    $kDownload = (int)$kDownload;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche Download: ' . $kDownload, JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
    }
    if ($kDownload > 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.Download.php';
        if (class_exists('Download')) {
            $oDownload = new Download($kDownload);
            $nRows     = $oDownload->delete();
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Rows: ' . $nRows . ' geloescht', JTLLOG_LEVEL_DEBUG, false, 'Download_xml');
            }
        }
    }
}
