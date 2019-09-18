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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Lieferschein_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Lieferschein_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'Lieferschein_xml');
            }
            $return = 0;
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' .
                        filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Lieferschein_xml');
                }
                $cData = file_get_contents($entzippfad . $zip['filename']);
                $oXml  = simplexml_load_string($cData);
                switch ($zip['filename']) {
                    case 'lief.xml':
                        bearbeiteInsert($oXml);
                        break;

                    case 'del_lief.xml':
                        bearbeiteDelete($oXml);
                        break;

                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Lieferschein_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Lieferschein_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Lieferschein_xml');
}

/**
 * @param object $oXml
 */
function bearbeiteInsert($oXml)
{
    foreach ($oXml->tlieferschein as $oXmlLieferschein) {
        $oLieferschein            = JTLMapArr($oXmlLieferschein, $GLOBALS['mLieferschein']);
        $oLieferschein->dErstellt = date_format(date_create($oLieferschein->dErstellt), 'U');
        DBUpdateInsert('tlieferschein', [$oLieferschein], 'kLieferschein');

        foreach ($oXmlLieferschein->tlieferscheinpos as $oXmlLieferscheinpos) {
            $oLieferscheinpos                = JTLMapArr($oXmlLieferscheinpos, $GLOBALS['mLieferscheinpos']);
            $oLieferscheinpos->kLieferschein = $oLieferschein->kLieferschein;
            DBUpdateInsert('tlieferscheinpos', [$oLieferscheinpos], 'kLieferscheinPos');

            foreach ($oXmlLieferscheinpos->tlieferscheinposInfo as $oXmlLieferscheinposinfo) {
                $oLieferscheinposinfo                   = JTLMapArr($oXmlLieferscheinposinfo, $GLOBALS['mLieferscheinposinfo']);
                $oLieferscheinposinfo->kLieferscheinPos = $oLieferscheinpos->kLieferscheinPos;
                DBUpdateInsert('tlieferscheinposinfo', [$oLieferscheinposinfo], 'kLieferscheinPosInfo');
            }
        }

        foreach ($oXmlLieferschein->tversand as $oXmlVersand) {
            $oVersand                = JTLMapArr($oXmlVersand, $GLOBALS['mVersand']);
            $oVersand->kLieferschein = $oLieferschein->kLieferschein;
            $oVersand->dErstellt     = date_format(date_create($oVersand->dErstellt), 'U');
            DBUpdateInsert('tversand', [$oVersand], 'kVersand');
        }
    }
}

/**
 * @param object $oXml
 */
function bearbeiteDelete($oXml)
{
    $kLieferschein_arr = $oXml->kLieferschein;
    if (!is_array($kLieferschein_arr)) {
        $kLieferschein_arr = (array)$kLieferschein_arr;
    }
    foreach ($kLieferschein_arr as $kLieferschein) {
        $kLieferschein = (int)$kLieferschein;
        Shop::DB()->delete('tversand', 'kLieferschein', $kLieferschein);
        Shop::DB()->delete('tlieferschein', 'kLieferschein', $kLieferschein);

        $oLieferscheinPos_arr = Shop::DB()->selectAll('tlieferscheinpos', 'kLieferschein', $kLieferschein, 'kLieferscheinPos');
        if (is_array($oLieferscheinPos_arr)) {
            foreach ($oLieferscheinPos_arr as $oLieferscheinPos) {
                Shop::DB()->delete('tlieferscheinpos', 'kLieferscheinPos', (int)$oLieferscheinPos->kLieferscheinPos);
                Shop::DB()->delete('tlieferscheinposinfo', 'kLieferscheinPos', (int)$oLieferscheinPos->kLieferscheinPos);
            }
        }
    }
}
