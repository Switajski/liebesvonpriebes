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
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Qucisync_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
            }
            $return = 0;
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' .
                        filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'quicksync.xml') {
                    bearbeiteInsert($xml);
                }

                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'QuickSync_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'QuickSync_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    if (is_array($xml['quicksync']['tartikel'])) {
        $oArtikel_arr = mapArray($xml['quicksync'], 'tartikel', $GLOBALS['mArtikelQuickSync']);
        $nCount       = count($oArtikel_arr);
        //PREISE
        if ($nCount < 2) {
            updateXMLinDB($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

            if (isset($xml['quicksync']['tartikel']['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
                handleNewPriceFormat($xml['quicksync']['tartikel']);
            } else {
                handleOldPriceFormat(mapArray($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise']));
            }

            // Preise für Preisverlauf
            $oPreis_arr = mapArray($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise']);
            foreach ($oPreis_arr as $oPreis) {
                setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
            }
        } else {
            for ($i = 0; $i < $nCount; ++$i) {
                updateXMLinDB($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

                if (version_compare($_POST['vers'], '099976', '>=')) {
                    handleNewPriceFormat(mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise']));
                }

                if (isset($xml['quicksync']['tartikel'][$i]['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
                    handleNewPriceFormat($xml['quicksync']['tartikel'][$i]);
                } else {
                    handleOldPriceFormat(mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise']));
                }

                // Preise für Preisverlauf
                $oPreis_arr = mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise']);
                foreach ($oPreis_arr as $oPreis) {
                    setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
                }
            }
        }
        $clearTags = [];
        foreach ($oArtikel_arr as $oArtikel) {
            //any new orders since last wawi-sync? see https://gitlab.jtl-software.de/jtlshop/jtl-shop/issues/304
            if (isset($oArtikel->fLagerbestand) && $oArtikel->fLagerbestand > 0) {
                $delta = Shop::DB()->query(
                    "SELECT SUM(pos.nAnzahl) AS totalquantity
                        FROM tbestellung b
                        JOIN twarenkorbpos pos
                        ON pos.kWarenkorb = b.kWarenkorb
                        WHERE b.cAbgeholt = 'N'
                            AND pos.kArtikel = " . (int)$oArtikel->kArtikel, 1
                );
                if ($delta->totalquantity > 0) {
                    //subtract delta from stocklevel
                    $oArtikel->fLagerbestand -= $delta->totalquantity;
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog("Artikel-Quicksync: Lagerbestand von kArtikel {$oArtikel->kArtikel} wurde wegen nicht-abgeholter Bestellungen "
                        . "um {$delta->totalquantity} auf {$oArtikel->fLagerbestand} reduziert.", JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                    }
                }
            }
            
            if ($oArtikel->fLagerbestand < 0) {
                $oArtikel->fLagerbestand = 0;
            }

            $upd                        = new stdClass();
            $upd->fLagerbestand         = $oArtikel->fLagerbestand;
            $upd->fStandardpreisNetto   = $oArtikel->fStandardpreisNetto;
            $upd->dLetzteAktualisierung = 'now()';
            Shop::DB()->update('tartikel', 'kArtikel', (int)$oArtikel->kArtikel, $upd);
            executeHook(HOOK_QUICKSYNC_XML_BEARBEITEINSERT, ['oArtikel' => $oArtikel]);
            // clear object cache for this article and its parent if there is any
            $parentArticle = Shop::DB()->select(
                'tartikel',
                'kArtikel', $oArtikel->kArtikel,
                null, null,
                null, null,
                false,
                'kVaterArtikel'
            );
            if (!empty($parentArticle->kVaterArtikel)) {
                $clearTags[] = (int)$parentArticle->kVaterArtikel;
            }
            $clearTags[] = (int)$oArtikel->kArtikel;
            versendeVerfuegbarkeitsbenachrichtigung($oArtikel);
        }
        $clearTags = array_unique($clearTags);
        array_walk($clearTags, function(&$i) { $i = CACHING_GROUP_ARTICLE . '_' . $i; });
        Shop::Cache()->flushTags($clearTags);
    }
}
