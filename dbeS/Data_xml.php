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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
        }
        $zipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        if (!mkdir($zipPath) && !is_dir($zipPath)) {
            syncException('Error : Verzeichnis ' . $zipPath . ' kann nicht erstellt werden!');
        }
        $zipPath .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $zipPath)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $zipPath . $zip['filename'] . ' size: ' .
                        filesize($zipPath . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
                }
                $d   = file_get_contents($zipPath . $zip['filename']);
                $xml = XML_unserialize($d);
                if ($zip['filename'] === 'ack_verfuegbarkeitsbenachrichtigungen.xml') {
                    bearbeiteVerfuegbarkeitsbenachrichtigungenAck($xml);
                } elseif ($zip['filename'] === 'ack_uploadqueue.xml') {
                    bearbeiteUploadQueueAck($xml);
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Data_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Data_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteVerfuegbarkeitsbenachrichtigungenAck($xml)
{
    if (isset($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
        if (!is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']) &&
            (int)$xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] > 0
        ) {
            $xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] =
                [$xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung']];
        }
        if (is_array($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'])) {
            foreach ($xml['ack_verfuegbarkeitsbenachrichtigungen']['kVerfuegbarkeitsbenachrichtigung'] as $kVerfuegbarkeitsbenachrichtigung) {
                $kVerfuegbarkeitsbenachrichtigung = (int)$kVerfuegbarkeitsbenachrichtigung;
                if ($kVerfuegbarkeitsbenachrichtigung > 0) {
                    Shop::DB()->update(
                        'tverfuegbarkeitsbenachrichtigung',
                        'kVerfuegbarkeitsbenachrichtigung',
                        $kVerfuegbarkeitsbenachrichtigung,
                        (object)['cAbgeholt' => 'Y']
                    );
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Verfuegbarkeitsbenachrichtigung erfolgreich abgeholt: ' .
                            $kVerfuegbarkeitsbenachrichtigung, JTLLOG_LEVEL_DEBUG, false, 'Data_xml');
                    }
                }
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteUploadQueueAck($xml)
{
    if (is_array($xml['ack_uploadqueue']['kuploadqueue'])) {
        foreach ($xml['ack_uploadqueue']['kuploadqueue'] as $kUploadqueue) {
            $kUploadqueue = (int)$kUploadqueue;
            if ($kUploadqueue > 0) {
                Shop::DB()->delete('tuploadqueue', 'kUploadqueue', $kUploadqueue);
            }
        }
    } elseif ((int)$xml['ack_uploadqueue']['kuploadqueue'] > 0) {
        Shop::DB()->delete('tuploadqueue', 'kUploadqueue', (int)$xml['ack_uploadqueue']['kuploadqueue']);
    }
}
