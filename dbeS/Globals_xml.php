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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Globals_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Globals_xml');
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
                        filesize($zipPath . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Globals_xml');
                }
                $d   = file_get_contents($zipPath . $zip['filename']);
                $xml = XML_unserialize($d);
                if ($zip['filename'] === 'del_globals.xml') {
                    bearbeiteDeletes($xml);
                } elseif ($zip['filename'] === 'globals.xml') {
                    bearbeiteUpdates($xml);
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Globals_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Globals_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
echo $return;

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    // Warengruppe
    if (is_array($xml['del_globals_wg']['kWarengruppe'])) {
        foreach ($xml['del_globals_wg']['kWarengruppe'] as $kWarengruppe) {
            if ((int)$kWarengruppe > 0) {
                loescheWarengruppe($kWarengruppe);
            }
        }
    } elseif ((int)$xml['del_globals_wg']['kWarengruppe'] > 0) {
        loescheWarengruppe($xml['del_globals_wg']['kWarengruppe']);
    }
}

/**
 * @param array $xml
 */
function bearbeiteUpdates($xml)
{
    if (isset($xml['globals']['tfirma'], $xml['globals']['tfirma attr']['kFirma']) &&
        is_array($xml['globals']['tfirma']) && $xml['globals']['tfirma attr']['kFirma'] > 0) {
        mappe($Firma, $xml['globals']['tfirma'], $GLOBALS['mFirma']);
        DBDelInsert('tfirma', [$Firma], 1);
    }
    if (isset($xml['globals'])) {
        //Sprache inserten
        $oSprache_arr = mapArray($xml['globals'], 'tsprache', $GLOBALS['mSprache']);
        $langCount    = count($oSprache_arr);
        for ($i = 0; $i < $langCount; $i++) {
            $oSprache_arr[$i]->cStandard = $oSprache_arr[$i]->cWawiStandard;
            unset($oSprache_arr[$i]->cWawiStandard);
        }
        Shop::Cache()->flushTags([CACHING_GROUP_LANGUAGE]);
        if (count($oSprache_arr) > 0) {
            DBDelInsert('tsprache', $oSprache_arr, 1);
        }

        XML2DB($xml['globals'], 'tlieferstatus', $GLOBALS['mLieferstatus']);
        XML2DB($xml['globals'], 'txsellgruppe', $GLOBALS['mXsellgruppe']);
        XML2DB($xml['globals'], 'teinheit', $GLOBALS['mEinheit']);
        XML2DB($xml['globals'], 'twaehrung', $GLOBALS['mWaehrung']);
        XML2DB($xml['globals'], 'tsteuerklasse', $GLOBALS['mSteuerklasse']);
        XML2DB($xml['globals'], 'tsteuersatz', $GLOBALS['mSteuersatz']);
        XML2DB($xml['globals'], 'tversandklasse', $GLOBALS['mVersandklasse']);

        if (isset($xml['globals']['tsteuerzone']) && is_array($xml['globals']['tsteuerzone'])) {
            $steuerzonen_arr = mapArray($xml['globals'], 'tsteuerzone', $GLOBALS['mSteuerzone']);
            DBDelInsert('tsteuerzone', $steuerzonen_arr, 1);
            Shop::DB()->query("DELETE FROM tsteuerzoneland", 4);
            $taxCount = count($steuerzonen_arr);
            for ($i = 0; $i < $taxCount; $i++) {
                if (count($steuerzonen_arr) < 2) {
                    XML2DB($xml['globals']['tsteuerzone'], 'tsteuerzoneland', $GLOBALS['mSteuerzoneland'], 0);
                } else {
                    XML2DB($xml['globals']['tsteuerzone'][$i], 'tsteuerzoneland', $GLOBALS['mSteuerzoneland'], 0);
                }
            }
        }
        if (isset($xml['globals']['tkundengruppe']) && is_array($xml['globals']['tkundengruppe'])) {
            $kundengruppen_arr = mapArray($xml['globals'], 'tkundengruppe', $GLOBALS['mKundengruppe']);
            DBDelInsert('tkundengruppe', $kundengruppen_arr, 1);
            Shop::DB()->query("TRUNCATE TABLE tkundengruppensprache", 4);
            Shop::DB()->query("TRUNCATE TABLE tkundengruppenattribut", 4);
            $cgCount = count($kundengruppen_arr);
            for ($i = 0; $i < $cgCount; $i++) {
                if (count($kundengruppen_arr) < 2) {
                    XML2DB($xml['globals']['tkundengruppe'], 'tkundengruppensprache', $GLOBALS['mKundengruppensprache'], 0);
                    XML2DB($xml['globals']['tkundengruppe'], 'tkundengruppenattribut', $GLOBALS['mKundengruppenattribut'], 0);
                } else {
                    XML2DB($xml['globals']['tkundengruppe'][$i], 'tkundengruppensprache', $GLOBALS['mKundengruppensprache'], 0);
                    XML2DB($xml['globals']['tkundengruppe'][$i], 'tkundengruppenattribut', $GLOBALS['mKundengruppenattribut'], 0);
                }
            }
            Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
        }
        // Warenlager
        if (isset($xml['globals']['twarenlager']) && is_array($xml['globals']['twarenlager'])) {
            $oWarenlager_arr = mapArray($xml['globals'], 'twarenlager', $GLOBALS['mWarenlager']);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('oWarenlager_arr: ' . print_r($oWarenlager_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Globals_xml');
            }
            //Lagersichtbarkeit für Shop zwischenspeichern
            $lagersichtbarkeit_arr = Shop::DB()->query("SELECT kWarenlager, nAktiv FROM twarenlager WHERE nAktiv = 1", 2);
            //Alle Einträge in twarenlager löschen - Wawi 1.0.1 sendet immer alle Warenlager.
            Shop::DB()->query("DELETE FROM twarenlager WHERE 1", 4);
            
            DBUpdateInsert('twarenlager', $oWarenlager_arr, 'kWarenlager');
            //Lagersichtbarkeit übertragen
            if (!empty($lagersichtbarkeit_arr)) {
                foreach ($lagersichtbarkeit_arr as $lager) {
                    Shop::DB()->update('twarenlager', 'kWarenlager', $lager->kWarenlager, $lager);
                }
            }
        }
        // Masseinheit
        if (isset($xml['globals']['tmasseinheit']) && is_array($xml['globals']['tmasseinheit'])) {
            $oMasseinheit_arr = mapArray($xml['globals'], 'tmasseinheit', $GLOBALS['mMasseinheit']);
            foreach ($oMasseinheit_arr as &$_me) {
                //hack?
                unset($_me->kBezugsMassEinheit);
            }
            DBDelInsert('tmasseinheit', $oMasseinheit_arr, 1);
            Shop::DB()->query("TRUNCATE TABLE tmasseinheitsprache", 4);
            $meCount = count($oMasseinheit_arr);
            for ($i = 0; $i < $meCount; $i++) {
                if (count($oMasseinheit_arr) < 2) {
                    XML2DB($xml['globals']['tmasseinheit'], 'tmasseinheitsprache', $GLOBALS['mMasseinheitsprache'], 0);
                } else {
                    XML2DB($xml['globals']['tmasseinheit'][$i], 'tmasseinheitsprache', $GLOBALS['mMasseinheitsprache'], 0);
                }
            }
        }
    }
    // Warengruppe
    if (isset($xml['globals_wg']['tWarengruppe']) && is_array($xml['globals_wg']['tWarengruppe'])) {
        $oWarengruppe_arr = mapArray($xml['globals_wg'], 'tWarengruppe', $GLOBALS['mWarengruppe']);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('oWarengruppe_arr: ' . print_r($oWarengruppe_arr, true), JTLLOG_LEVEL_DEBUG, false, 'Globals_xml');
        }
        DBUpdateInsert('twarengruppe', $oWarengruppe_arr, 'kWarengruppe');
    }
}

/**
 * @param int $kWarengruppe
 */
function loescheWarengruppe($kWarengruppe)
{
    $kWarengruppe = (int)$kWarengruppe;
    Shop::DB()->delete('twarengruppe', 'kWarengruppe', $kWarengruppe);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Warengruppe geloescht: ' . $kWarengruppe, JTLLOG_LEVEL_DEBUG, false, 'Globals_xml');
    }
}
