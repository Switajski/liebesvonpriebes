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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
            }
            $return = 0;
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' .
                        filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
                }
                $cData = file_get_contents($entzippfad . $zip['filename']);
                $oXml  = simplexml_load_string($cData);

                switch ($zip['filename']) {
                    case 'konfig.xml':
                        bearbeiteInsert($oXml);
                        break;

                    case 'del_konfig.xml':
                        bearbeiteDeletes($oXml);
                        break;

                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Konfig_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Konfig_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
}

/**
 * @param object $oXml
 */
function bearbeiteInsert($oXml)
{
    // Konfiggruppe
    foreach ($oXml->tkonfiggruppe as $oXmlKonfiggruppe) {
        $oKonfiggruppe = JTLMapArr($oXmlKonfiggruppe, $GLOBALS['mKonfigGruppe']);
        DBUpdateInsert('tkonfiggruppe', [$oKonfiggruppe], 'kKonfiggruppe');
        // Konfiggruppesprache
        foreach ($oXmlKonfiggruppe->tkonfiggruppesprache as $oXmlKonfiggruppesprache) {
            $oKonfiggruppesprache = JTLMapArr($oXmlKonfiggruppesprache, $GLOBALS['mKonfigSprache']);
            DBUpdateInsert('tkonfiggruppesprache', [$oKonfiggruppesprache], 'kKonfiggruppe', 'kSprache');
        }
        // Konfiggruppeitem
        loescheKonfigitem($oKonfiggruppe->kKonfiggruppe);

        foreach ($oXmlKonfiggruppe->tkonfigitem as $oXmlKonfigitem) {
            $oKonfigitem = JTLMapArr($oXmlKonfigitem, $GLOBALS['mKonfigItem']);
            DBUpdateInsert('tkonfigitem', [$oKonfigitem], 'kKonfigitem');
            // Konfiggruppeitemsprache
            foreach ($oXmlKonfigitem->tkonfigitemsprache as $oXmlKonfigitemsprache) {
                $oKonfigitemsprache = JTLMapArr($oXmlKonfigitemsprache, $GLOBALS['mKonfigSprache']);
                DBUpdateInsert('tkonfigitemsprache', [$oKonfigitemsprache], 'kKonfigitem', 'kSprache');
            }
            // Konfiggruppeitemsprache
            foreach ($oXmlKonfigitem->tkonfigitempreis as $oXmlKonfigitempreis) {
                $oKonfigitempreis = JTLMapArr($oXmlKonfigitempreis, $GLOBALS['mKonfigItemPreis']);
                DBUpdateInsert('tkonfigitempreis', [$oKonfigitempreis], 'kKonfigitem', 'kKundengruppe');
            }
        }
    }
}

/**
 * @param object $oXml
 */
function bearbeiteDeletes($oXml)
{
    // Konfiggruppe
    foreach ($oXml->kKonfiggruppe as $oXmlKonfiggruppe) {
        $kKonfiggruppe = (int)$oXmlKonfiggruppe;
        if ($kKonfiggruppe > 0) {
            loescheKonfiggruppe($kKonfiggruppe);
        }
    }
}

/**
 * @param int $kKonfiggruppe
 */
function loescheKonfiggruppe($kKonfiggruppe)
{
    $kKonfiggruppe = (int)$kKonfiggruppe;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche Konfiggruppe: ' . $kKonfiggruppe, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($kKonfiggruppe > 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.Konfiggruppe.php';
        if (class_exists('Konfiggruppe')) {
            // todo: alle items löschen
            $oKonfig = new Konfiggruppe($kKonfiggruppe);
            $nRows   = $oKonfig->delete();
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Rows: ' . $nRows . ' geloescht', JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
            }
        }
    }
}

/**
 * @param int $kKonfiggruppe
 */
function loescheKonfigitem($kKonfiggruppe)
{
    $kKonfiggruppe = (int)$kKonfiggruppe;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche kKonfigitem (gruppe): ' . $kKonfiggruppe, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($kKonfiggruppe > 0) {
        Shop::DB()->delete('tkonfigitem', 'kKonfiggruppe', $kKonfiggruppe);
    }
}

/**
 * @param int $kKonfigitem
 */
function loescheKonfigitempreis($kKonfigitem)
{
    $kKonfigitem = (int)$kKonfigitem;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche Konfigitempreis: ' . $kKonfigitem, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($kKonfigitem > 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.Konfigitempreis.php';
        if (class_exists('Konfigitempreis')) {
            $oKonfig = new Konfigitempreis($kKonfigitem);
            $nRows   = $oKonfig->delete();
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Rows: ' . $nRows . ' geloescht', JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
            }
        }
    }
}
