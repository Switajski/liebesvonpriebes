<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

global $smarty;

if ($smarty === null) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    $smarty = Shop::Smarty();
}

$return  = 3;
$archive = null;
if (auth()) {
    $articleIDs = [];
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $return = 0;
            $conf   = Shop::getSettings([CONF_GLOBAL]);
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' .
                        filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'artdel.xml') {
                    $articleIDs = array_merge($articleIDs, bearbeiteDeletes($xml, $conf));
                } else {
                    foreach (bearbeiteInsert($xml, $conf) as $articleID) {
                        $articleIDs[] = $articleID;
                    }
                }
                if ($i === 0) {
                    // Suchcachetimer setzen.
                    Shop::DB()->query(
                        "UPDATE tsuchcache
                            SET dGueltigBis = DATE_ADD(now(), INTERVAL " . SUCHCACHE_LEBENSDAUER . " MINUTE)
                            WHERE dGueltigBis IS NULL", 3
                    );
                }
                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
            clearProductCaches($articleIDs);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
    }
}
if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
}

/**
 * @param array $xml
 * @param array $conf
 * @return array - list of article IDs
 */
function bearbeiteDeletes($xml, $conf)
{
    $res = [];
    if (!is_array($xml['del_artikel'])) {
        return $res;
    }
    if (!is_array($xml['del_artikel']['kArtikel'])) {
        $xml['del_artikel']['kArtikel'] = [$xml['del_artikel']['kArtikel']];
    }
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';

    foreach ($xml['del_artikel']['kArtikel'] as $kArtikel) {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0) {
            $kVaterArtikel = ArtikelHelper::getParent($kArtikel);
            $nIstVater     = $kVaterArtikel > 0 ? 0 : 1;
            checkArtikelBildLoeschung($kArtikel);

            Shop::DB()->query(
                "DELETE teigenschaftkombiwert
                    FROM teigenschaftkombiwert
                    JOIN tartikel 
                    ON tartikel.kArtikel = {$kArtikel}
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi", 4
            );
            $res[] = loescheArtikel($kArtikel, $nIstVater, false, $conf);
            // Lösche Artikel aus tartikelkategorierabatt
            Shop::DB()->delete('tartikelkategorierabatt', 'kArtikel', $kArtikel);
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            if ($kVaterArtikel > 0) {
                Artikel::beachteVarikombiMerkmalLagerbestand($kVaterArtikel);
            }

            executeHook(HOOK_ARTIKEL_XML_BEARBEITEDELETES, ['kArtikel' => $kArtikel]);
        }
    }

    return $res;
}

/**
 * @param array $xml
 * @param array $conf
 * @return array - list of article IDs to flush
 */
function bearbeiteInsert($xml, array $conf)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
    $res = [];

    $Artikel           = new stdClass();
    $Artikel->kArtikel = 0;

    if (is_array($xml['tartikel attr'])) {
        $Artikel->kArtikel = (int)$xml['tartikel attr']['kArtikel'];
    }
    if (!$Artikel->kArtikel) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('kArtikel fehlt! XML:' . print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'Artikel_xml');
        }

        return $res;
    }
    if (is_array($xml['tartikel'])) {
        $artikel_arr = mapArray($xml, 'tartikel', $GLOBALS['mArtikel']);
        // Alten SEO-Pfad merken. Eintrag in tredirect, wenn sich der Pfad geändert hat.
        $oSeoOld       = Shop::DB()->select(
            'tartikel',
            'kArtikel', (int)$Artikel->kArtikel,
            null, null,
            null, null,
            false,
            'cSeo'
        );
        $oSeoAssoc_arr = getSeoFromDB($Artikel->kArtikel, 'kArtikel', null, 'kSprache');
        $isParent      = isset($artikel_arr[0]->nIstVater) ? 1 : 0;

        if (isset($xml['tartikel']['tkategorieartikel'])
            && $conf['global']['kategorien_anzeigefilter'] == EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE
            && Shop::Cache()->isCacheGroupActive(CACHING_GROUP_CATEGORY)
        ) {
            $currentArticleCategories = [];
            $newArticleCategories     = [];
            $flush                    = false;
            // get list of all categories the article is currently associated with
            $currentArticleCategoriesObject = Shop::DB()->selectAll(
                'tkategorieartikel',
                'kArtikel',
                (int)$Artikel->kArtikel,
                'kKategorie'
            );
            foreach ($currentArticleCategoriesObject as $obj) {
                $currentArticleCategories[] = (int)$obj->kKategorie;
            }
            // get list of all categories the article will be associated with after this update
            $newArticleCategoriesObject = mapArray($xml['tartikel'], 'tkategorieartikel', $GLOBALS['mKategorieArtikel']);
            foreach ($newArticleCategoriesObject as $newArticleCategory) {
                $newArticleCategories[] = (int)$newArticleCategory->kKategorie;
            }
            foreach ($newArticleCategories as $newArticleCategory) {
                if (!in_array($newArticleCategory, $currentArticleCategories, true)) {
                    // the article was previously not associated with this category
                    $articleCount = Shop::DB()->query(
                        "SELECT count(tkategorieartikel.kArtikel) AS count
                            FROM tkategorieartikel
                            LEFT JOIN tartikel
                                ON tartikel.kArtikel = tkategorieartikel.kArtikel
                            WHERE tkategorieartikel.kKategorie = {$newArticleCategory} " . gibLagerfilter(), 1
                    );
                    if (isset($articleCount->count) && (int)$articleCount->count === 0) {
                        // the category was previously empty - flush cache
                        $flush = true;
                        break;
                    }
                }
            }

            if ($flush === false) {
                foreach ($currentArticleCategories as $category) {
                    // check if the article is removed from an existing category
                    if (!in_array($category, $newArticleCategories, true)) {
                        // check if the article was the only one in at least one of these categories
                        $articleCount = Shop::DB()->query(
                            "SELECT count(tkategorieartikel.kArtikel) AS count
                                FROM tkategorieartikel
                                LEFT JOIN tartikel
                                    ON tartikel.kArtikel = tkategorieartikel.kArtikel
                                WHERE tkategorieartikel.kKategorie = {$category} " . gibLagerfilter(), 1
                        );
                        if (!isset($articleCount->count) || (int)$articleCount->count === 1) {
                            // the category only had this article in it - flush cache
                            $flush = true;
                            break;
                        }
                    }
                }
            }
            if ($flush === false
                && $conf['global']['artikel_artikelanzeigefilter'] != EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
            ) {
                $check         = false;
                $currentStatus = Shop::DB()->select(
                    'tartikel',
                    'kArtikel', $Artikel->kArtikel,
                    null, null,
                    null, null,
                    false,
                    'cLagerBeachten, cLagerKleinerNull, fLagerbestand'
                );
                if (isset($currentStatus->cLagerBeachten)) {
                    if ($currentStatus->fLagerbestand <= 0 && $xml['tartikel']['fLagerbestand'] > 0) {
                        // article was not in stock before but is now - check if flush is necessary
                        $check = true;
                    } elseif ($currentStatus->fLagerbestand > 0 && $xml['tartikel']['fLagerbestand'] <= 0) {
                        // article was in stock before but is not anymore - check if flush is necessary
                        $check = true;
                    } elseif ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
                        && $currentStatus->cLagerKleinerNull !== $xml['tartikel']['cLagerKleinerNull']
                    ) {
                        // overselling status changed - check if flush is necessary
                        $check = true;
                    } elseif ($currentStatus->cLagerBeachten !== $xml['tartikel']['cLagerBeachten']
                        && $xml['tartikel']['fLagerbestand'] <= 0
                    ) {
                        $check = true;
                    }
                    if ($check === true) {
                        // get count of visible articles in the article's futre categories
                        $articleCount = Shop::DB()->query(
                            "SELECT tkategorieartikel.kKategorie, count(tkategorieartikel.kArtikel) AS count
                                FROM tkategorieartikel
                                LEFT JOIN tartikel
                                    ON tartikel.kArtikel = tkategorieartikel.kArtikel
                                WHERE tkategorieartikel.kKategorie IN (" . implode(',', $newArticleCategories) . ") " .
                                gibLagerfilter() . " 
                                GROUP BY tkategorieartikel.kKategorie", 2
                        );
                        if (is_array($newArticleCategories) && !empty($newArticleCategories)) {
                            foreach ($newArticleCategories as $nac) {
                                if (is_array($articleCount) && !empty($articleCount)) {
                                    foreach ($articleCount as $ac) {
                                        if ($ac->kKategorie == $nac) {
                                            if (($currentStatus->cLagerBeachten !== 'Y' && $ac->count == 1) ||
                                                ($currentStatus->cLagerBeachten === 'Y' && $ac->count == 0)
                                            ) {
                                                // there was just one product that is now sold out
                                                // or there were just sold out products and now it's not sold out anymore
                                                $flush = true;
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    $flush = true;
                                    break;
                                }
                            }
                        } else {
                            $flush = true;
                        }
                    }
                }
            }

            if ($flush === true) {
                flushCategoryTreeCache();
            }
        }
        $downloadKeys = getDownloadKeys($Artikel->kArtikel);
        loescheArtikel($Artikel->kArtikel, $isParent, true, $conf);
        if ($artikel_arr[0]->kArtikel > 0) {
            if (!$artikel_arr[0]->cSeo) {
                //get seo-path from productname, but replace slashes
                $artikel_arr[0]->cSeo = getFlatSeoPath($artikel_arr[0]->cName);
            }
            $artikel_arr[0]->cSeo = getSeo($artikel_arr[0]->cSeo);
            $artikel_arr[0]->cSeo = checkSeo($artikel_arr[0]->cSeo);
            //persistente werte
            $artikel_arr[0]->dLetzteAktualisierung = 'now()';
            //mysql strict fixes
            if (isset($artikel_arr[0]->dMHD) && $artikel_arr[0]->dMHD === '') {
                $artikel_arr[0]->dMHD = '0000-00-00';
            }
            if (isset($artikel_arr[0]->dErstellt) && $artikel_arr[0]->dErstellt === '') {
                $artikel_arr[0]->dErstellt = 'now()';
            }
            if (isset($artikel_arr[0]->dZulaufDatum) && $artikel_arr[0]->dZulaufDatum === '') {
                $artikel_arr[0]->dZulaufDatum = '0000-00-00';
            } elseif (!isset($artikel_arr[0]->dZulaufDatum)) {
                $artikel_arr[0]->dZulaufDatum = '0000-00-00';
            }
            if (isset($artikel_arr[0]->dErscheinungsdatum) && $artikel_arr[0]->dErscheinungsdatum === '') {
                $artikel_arr[0]->dErscheinungsdatum = '0000-00-00';
            }
            if (isset($artikel_arr[0]->fLieferantenlagerbestand) && $artikel_arr[0]->fLieferantenlagerbestand === '') {
                $artikel_arr[0]->fLieferantenlagerbestand = 0;
            } elseif (!isset($artikel_arr[0]->fLieferantenlagerbestand)) {
                $artikel_arr[0]->fLieferantenlagerbestand = 0;
            }
            if (isset($artikel_arr[0]->fZulauf) && $artikel_arr[0]->fZulauf === '') {
                $artikel_arr[0]->fZulauf = 0;
            } elseif (!isset($artikel_arr[0]->fZulauf)) {
                $artikel_arr[0]->fZulauf = 0;
            }
            if (isset($artikel_arr[0]->fLieferzeit) && $artikel_arr[0]->fLieferzeit === '') {
                $artikel_arr[0]->fLieferzeit = 0;
            } elseif (!isset($artikel_arr[0]->fLieferzeit)) {
                $artikel_arr[0]->fLieferzeit = 0;
            }
            //temp. fix for syncing with wawi 1.0
            if (isset($artikel_arr[0]->kVPEEinheit) && is_array($artikel_arr[0]->kVPEEinheit)) {
                $artikel_arr[0]->kVPEEinheit = $artikel_arr[0]->kVPEEinheit[0];
            }
            
            //any new orders since last wawi-sync? see https://gitlab.jtl-software.de/jtlshop/jtl-shop/issues/304
            if (isset($artikel_arr[0]->fLagerbestand) && $artikel_arr[0]->fLagerbestand > 0) {
                $delta = Shop::DB()->query(
                    "SELECT SUM(pos.nAnzahl) AS totalquantity
                        FROM tbestellung b
                        JOIN twarenkorbpos pos
                            ON pos.kWarenkorb = b.kWarenkorb
                        WHERE b.cAbgeholt = 'N'
                            AND pos.kArtikel = " . (int)$artikel_arr[0]->kArtikel, 1
                );
                if ($delta->totalquantity > 0) {
                    //subtract delta from stocklevel
                    $artikel_arr[0]->fLagerbestand -= $delta->totalquantity;
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog(
                            "Artikel-Sync: Lagerbestand von kArtikel {$artikel_arr[0]->kArtikel} wurde " .
                            "wegen nicht-abgeholter Bestellungen " .
                            "um {$delta->totalquantity} auf {$artikel_arr[0]->fLagerbestand} reduziert.",
                            JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml'
                        );
                    }
                }
            }
            
            DBUpdateInsert('tartikel', $artikel_arr, 'kArtikel');
            executeHook(HOOK_ARTIKEL_XML_BEARBEITEINSERT, ['oArtikel' => $artikel_arr[0]]);
            // Insert into tredirect weil sich das SEO vom Artikel geändert hat
            if (isset($oSeoOld->cSeo)) {
                checkDbeSXmlRedirect($oSeoOld->cSeo, $artikel_arr[0]->cSeo);
            }
            //insert in tseo
            Shop::DB()->query(
                "INSERT INTO tseo
                    SELECT tartikel.cSeo, 'kArtikel', tartikel.kArtikel, tsprache.kSprache
                    FROM tartikel, tsprache
                    WHERE tartikel.kArtikel = " . (int)$artikel_arr[0]->kArtikel . " 
                        AND tsprache.cStandard = 'Y' 
                        AND tartikel.cSeo != ''", 4
            );
        }
        //Artikelsprache
        $artikelsprache_arr = mapArray($xml['tartikel'], 'tartikelsprache', $GLOBALS['mArtikelSprache']);
        if (is_array($artikelsprache_arr)) {
            $oShopSpracheAssoc_arr = gibAlleSprachen(1);
            $langCount             = count($artikelsprache_arr);
            for ($i = 0; $i < $langCount; ++$i) {
                // Sprachen die nicht im Shop vorhanden sind überspringen
                if (!Sprache::isShopLanguage($artikelsprache_arr[$i]->kSprache, $oShopSpracheAssoc_arr)) {
                    continue;
                }
                if (!$artikelsprache_arr[$i]->cSeo) {
                    $artikelsprache_arr[$i]->cSeo = getFlatSeoPath($artikelsprache_arr[$i]->cName);
                }
                if (!$artikelsprache_arr[$i]->cSeo) {
                    $artikelsprache_arr[$i]->cSeo = $artikel_arr[0]->cSeo;
                }
                if (!$artikelsprache_arr[$i]->cSeo) {
                    $artikelsprache_arr[$i]->cSeo = $artikel_arr[0]->cName;
                }
                $artikelsprache_arr[$i]->cSeo = getSeo($artikelsprache_arr[$i]->cSeo);
                $artikelsprache_arr[$i]->cSeo = checkSeo($artikelsprache_arr[$i]->cSeo);

                DBUpdateInsert('tartikelsprache', [$artikelsprache_arr[$i]], 'kArtikel', 'kSprache');
                Shop::DB()->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kArtikel', (int)$artikelsprache_arr[$i]->kArtikel, (int)$artikelsprache_arr[$i]->kSprache]
                );

                $oSeo           = new stdClass();
                $oSeo->cSeo     = $artikelsprache_arr[$i]->cSeo;
                $oSeo->cKey     = 'kArtikel';
                $oSeo->kKey     = $artikelsprache_arr[$i]->kArtikel;
                $oSeo->kSprache = $artikelsprache_arr[$i]->kSprache;
                Shop::DB()->insert('tseo', $oSeo);
                // Insert into tredirect weil sich das SEO vom Artikel geändert hat
                if (isset($oSeoAssoc_arr[$artikelsprache_arr[$i]->kSprache])) {
                    checkDbeSXmlRedirect($oSeoAssoc_arr[$artikelsprache_arr[$i]->kSprache]->cSeo, $artikelsprache_arr[$i]->cSeo);
                }
            }
        }
        //Attribute
        if (isset($xml['tartikel']['tattribut']) && is_array($xml['tartikel']['tattribut'])) {
            $Attribut_arr = mapArray($xml['tartikel'], 'tattribut', $GLOBALS['mAttribut']);
            $aArrCount    = count($Attribut_arr);
            for ($i = 0; $i < $aArrCount; ++$i) {
                if (count($Attribut_arr) < 2) {
                    loescheAttribute($xml['tartikel']['tattribut attr']['kAttribut']);
                    updateXMLinDB(
                        $xml['tartikel']['tattribut'],
                        'tattributsprache',
                        $GLOBALS['mAttributSprache'],
                        'kAttribut',
                        'kSprache'
                    );
                } else {
                    loescheAttribute($xml['tartikel']['tattribut'][$i . ' attr']['kAttribut']);
                    updateXMLinDB(
                        $xml['tartikel']['tattribut'][$i],
                        'tattributsprache',
                        $GLOBALS['mAttributSprache'],
                        'kAttribut',
                        'kSprache'
                    );
                }
            }
            DBUpdateInsert('tattribut', $Attribut_arr, 'kAttribut');
        }
        //Medienmodul
        if (isset($xml['tartikel']['tmediendatei']) && is_array($xml['tartikel']['tmediendatei'])) {
            $oMediendatei_arr = mapArray($xml['tartikel'], 'tmediendatei', $GLOBALS['mMediendatei']);
            $mediaCount       = count($oMediendatei_arr);
            for ($i = 0; $i < $mediaCount; ++$i) {
                if ($mediaCount < 2) {
                    loescheMediendateien($xml['tartikel']['tmediendatei attr']['kMedienDatei']);
                    updateXMLinDB(
                        $xml['tartikel']['tmediendatei'],
                        'tmediendateisprache',
                        $GLOBALS['mMediendateisprache'],
                        'kMedienDatei',
                        'kSprache'
                    );
                    updateXMLinDB(
                        $xml['tartikel']['tmediendatei'],
                        'tmediendateiattribut',
                        $GLOBALS['mMediendateiattribut'],
                        'kMedienDateiAttribut'
                    );
                } else {
                    loescheMediendateien($xml['tartikel']['tmediendatei'][$i . ' attr']['kMedienDatei']);
                    updateXMLinDB(
                        $xml['tartikel']['tmediendatei'][$i],
                        'tmediendateisprache',
                        $GLOBALS['mMediendateisprache'],
                        'kMedienDatei',
                        'kSprache'
                    );
                    updateXMLinDB(
                        $xml['tartikel']['tmediendatei'][$i],
                        'tmediendateiattribut',
                        $GLOBALS['mMediendateiattribut'],
                        'kMedienDateiAttribut'
                    );
                }
            }
            DBUpdateInsert('tmediendatei', $oMediendatei_arr, 'kMedienDatei');
        }
        //Downloadmodul
        if (isset($xml['tartikel']['tArtikelDownload']) && is_array($xml['tartikel']['tArtikelDownload'])) {
            $oDownload_arr = [];
            loescheDownload($Artikel->kArtikel);
            if (isset($xml['tartikel']['tArtikelDownload']['kDownload'])
                && is_array($xml['tartikel']['tArtikelDownload']['kDownload'])
            ) {
                $kDownload_arr = $xml['tartikel']['tArtikelDownload']['kDownload'];
                foreach ($kDownload_arr as $kDownload) {
                    $oArtikelDownload            = new stdClass();
                    $oArtikelDownload->kDownload = (int)$kDownload;
                    $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
                    $oDownload_arr[]             = $oArtikelDownload;

                    if (($idx = array_search($oArtikelDownload->kDownload, $downloadKeys, true)) !== false) {
                        unset($downloadKeys[$idx]);
                    }
                }
            } else {
                $oArtikelDownload            = new stdClass();
                $oArtikelDownload->kDownload = (int)$xml['tartikel']['tArtikelDownload']['kDownload'];
                $oArtikelDownload->kArtikel  = $Artikel->kArtikel;
                $oDownload_arr[]             = $oArtikelDownload;

                if (($idx = array_search($oArtikelDownload->kDownload, $downloadKeys, true)) !== false) {
                    unset($downloadKeys[$idx]);
                }
            }

            DBUpdateInsert('tartikeldownload', $oDownload_arr, 'kArtikel', 'kDownload');
        }
        // Nicht übertragene Downloads löschen
        if (is_array($downloadKeys) && count($downloadKeys)) {
            foreach ($downloadKeys as $kDownload) {
                loescheDownload($Artikel->kArtikel, $kDownload);
            }
        }
        // Stückliste
        if (isset($xml['tartikel']['tstueckliste']) && is_array($xml['tartikel']['tstueckliste'])) {
            $oStueckliste_arr = mapArray($xml['tartikel'], 'tstueckliste', $GLOBALS['mStueckliste']);
            $cacheIDs         = [];
            if (count($oStueckliste_arr) > 0) {
                loescheStueckliste($oStueckliste_arr[0]->kStueckliste);
            }
            DBUpdateInsert('tstueckliste', $oStueckliste_arr, 'kStueckliste', 'kArtikel');
            foreach ($oStueckliste_arr as $_sl) {
                if (isset($_sl->kArtikel)) {
                    $cacheIDs[] = CACHING_GROUP_ARTICLE . '_' . (int)$_sl->kArtikel;
                }
            }
            if (count($cacheIDs) > 0) {
                Shop::Cache()->flushTags($cacheIDs);
            }
        }
        // Uploads
        if (isset($xml['tartikel']['tartikelupload']) && is_array($xml['tartikel']['tartikelupload'])) {
            $oArtikelUpload_arr = mapArray($xml['tartikel'], 'tartikelupload', $GLOBALS['mArtikelUpload']);
            foreach ($oArtikelUpload_arr as &$oArtikelUpload) {
                $oArtikelUpload->nTyp          = 3;
                $oArtikelUpload->kUploadSchema = $oArtikelUpload->kArtikelUpload;
                $oArtikelUpload->kCustomID     = $oArtikelUpload->kArtikel;
                unset($oArtikelUpload->kArtikelUpload, $oArtikelUpload->kArtikel);
            }
            unset($oArtikelUpload);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'oArtikelUpload_arr: ' . print_r($oArtikelUpload_arr, true),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'Artikel_xml'
                );
            }
            DBUpdateInsert('tuploadschema', $oArtikelUpload_arr, 'kUploadSchema', 'kCustomID');

            // Upload-Sprachen
            if (count($oArtikelUpload_arr) < 2) {
                $oArtikelUploadSprache_arr = mapArray(
                    $xml['tartikel']['tartikelupload'],
                    'tartikeluploadsprache',
                    $GLOBALS['mArtikelUploadSprache']
                );
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog(
                        'oArtikelUploadSprache_arr: ' . print_r($oArtikelUploadSprache_arr, true),
                        JTLLOG_LEVEL_DEBUG,
                        false,
                        'Artikel_xml'
                    );
                }
                DBUpdateInsert('tuploadschemasprache', $oArtikelUploadSprache_arr, 'kArtikelUpload', 'kSprache');
            } else {
                $ulCount = count($oArtikelUpload_arr);
                for ($i = 0; $i < $ulCount; ++$i) {
                    $oArtikelUploadSprache_arr = mapArray(
                        $xml['tartikel']['tartikelupload'][$i],
                        'tartikeluploadsprache',
                        $GLOBALS['mArtikelUploadSprache']
                    );
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog(
                            'oArtikelUploadSprache_arr: ' . print_r($oArtikelUploadSprache_arr, true),
                            JTLLOG_LEVEL_DEBUG,
                            false,
                            'Artikel_xml'
                        );
                    }
                    DBUpdateInsert('tuploadschemasprache', $oArtikelUploadSprache_arr, 'kArtikelUpload', 'kSprache');
                }
            }
        }
        // abnahmeintervalle
        Shop::DB()->delete('tartikelabnahme', 'kArtikel', $artikel_arr[0]->kArtikel);
        if (isset($xml['tartikel']['tartikelabnahme']) && is_array($xml['tartikel']['tartikelabnahme'])) {
            $oArtikelAbnahmeIntervalle_arr = mapArray($xml['tartikel'], 'tartikelabnahme', $GLOBALS['mArtikelAbnahme']);
            DBUpdateInsert('tartikelabnahme', $oArtikelAbnahmeIntervalle_arr, 'kArtikel', 'kKundengruppe');
        }
        // Konfig
        loescheKonfig($Artikel->kArtikel);
        if (isset($xml['tartikel']['tartikelkonfiggruppe']) && is_array($xml['tartikel']['tartikelkonfiggruppe'])) {
            $oArtikelKonfig_arr = mapArray($xml['tartikel'], 'tartikelkonfiggruppe', $GLOBALS['mArtikelkonfiggruppe']);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'oArtikelKonfig_arr: ' . print_r($oArtikelKonfig_arr, true),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'Artikel_xml'
                );
            }
            DBUpdateInsert('tartikelkonfiggruppe', $oArtikelKonfig_arr, 'kArtikel', 'kKonfiggruppe');
        }
        // Sonderpreise
        if (isset($xml['tartikel']['tartikelsonderpreis'])) {
            updateXMLinDB(
                $xml['tartikel']['tartikelsonderpreis'],
                'tsonderpreise',
                $GLOBALS['mSonderpreise'],
                'kArtikelSonderpreis',
                'kKundengruppe'
            );
        }

        updateXMLinDB($xml['tartikel'], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

        if (isset($xml['tartikel']['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
            handleNewPriceFormat($xml['tartikel']);
        } else {
            handleOldPriceFormat(mapArray($xml['tartikel'], 'tpreise', $GLOBALS['mPreise']));
        }

        updateXMLinDB($xml['tartikel'], 'tartikelsonderpreis', $GLOBALS['mArtikelSonderpreis'], 'kArtikelSonderpreis');
        updateXMLinDB($xml['tartikel'], 'tkategorieartikel', $GLOBALS['mKategorieArtikel'], 'kKategorieArtikel');
        updateXMLinDB($xml['tartikel'], 'tartikelattribut', $GLOBALS['mArtikelAttribut'], 'kArtikelAttribut');
        updateXMLinDB($xml['tartikel'], 'tartikelsichtbarkeit', $GLOBALS['mArtikelSichtbarkeit'], 'kKundengruppe', 'kArtikel');
        updateXMLinDB($xml['tartikel'], 'txsell', $GLOBALS['mXSell'], 'kXSell');
        updateXMLinDB($xml['tartikel'], 'tartikelmerkmal', $GLOBALS['mArtikelSichtbarkeit'], 'kMermalWert');
        if ($artikel_arr[0]->nIstVater == 1) {
            //Lagerbestand-Update: Lagerbestand des Vaterartikels berechnet sich aus der Summe der Kindartikel-Lagerbestände
            Shop::DB()->query(
                "UPDATE tartikel SET fLagerbestand =
                    (SELECT * FROM
                        (SELECT SUM(fLagerbestand) 
                            FROM tartikel 
                            WHERE kVaterartikel = " . (int)$artikel_arr[0]->kArtikel . "
                         ) AS x
                     )
                    WHERE kArtikel = " . (int)$artikel_arr[0]->kArtikel, 3
            );
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            Artikel::beachteVarikombiMerkmalLagerbestand(
                $artikel_arr[0]->kArtikel,
                $conf['global']['artikel_artikelanzeigefilter']
            );
        } elseif (isset($artikel_arr[0]->kVaterArtikel) && $artikel_arr[0]->kVaterArtikel > 0) {
            //Lagerbestand-Update: Lagerbestand des Vaterartikels berechnet sich aus der Summe der Kindartikel-Lagerbestände
            Shop::DB()->query("
                UPDATE tartikel SET fLagerbestand =
                    (SELECT * FROM
                        (SELECT SUM(fLagerbestand) 
                            FROM tartikel 
                            WHERE kVaterartikel = " . (int)$artikel_arr[0]->kVaterArtikel . "
                        ) AS x
                    )
                    WHERE kArtikel = " . (int)$artikel_arr[0]->kVaterArtikel, 3);
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            Artikel::beachteVarikombiMerkmalLagerbestand(
                $artikel_arr[0]->kVaterArtikel,
                $conf['global']['artikel_artikelanzeigefilter']
            );
        }
        // SQL DEL
        if (isset($xml['tartikel']['SQLDEL']) && strlen($xml['tartikel']['SQLDEL']) > 10) { // teigenschaftkombiwert sqls absetzen
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('SQLDEL: ' . $xml['tartikel']['SQLDEL'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $cSQL_arr = explode("\n", $xml['tartikel']['SQLDEL']);
            foreach ($cSQL_arr as $cSQL) {
                if (strlen($cSQL) > 10) {
                    Shop::DB()->query($cSQL, 4);
                }
            }
        }
        // SQL
        if (isset($xml['tartikel']['SQL']) && strlen($xml['tartikel']['SQL']) > 10) { //teigenschaftkombiwert sqls absetzen
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('SQL: ' . $xml['tartikel']['SQL'], JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
            }
            $cSQL_arr = explode("\n", $xml['tartikel']['SQL']);
            foreach ($cSQL_arr as $cSQL) {
                if (strlen($cSQL) > 10) {
                    // Pre Wawi 0.99862 fix
                    if (isset($artikel_arr[0]->kVaterArtikel)
                        && $artikel_arr[0]->kVaterArtikel > 0
                        && !isset($xml['tartikel']['SQLDEL'])
                        && strpos($cSQL, 'teigenschaftkombiwert') !== false
                    ) {
                        $cDel     = substr($cSQL, strpos($cSQL, 'values ') + strlen('values '));
                        $cDel_arr = str_replace(['(', ')'], '', explode('),(', $cDel));
                        $kKey_arr = [];
                        foreach ($cDel_arr as $cDel) {
                            $kKey_arr[] = (int)substr($cDel, 0, strpos($cDel, ','));
                        }
                        Shop::DB()->query(
                            "DELETE
                                FROM teigenschaftkombiwert 
                                WHERE kEigenschaftKombi IN (" . implode(',', $kKey_arr) . ")", 4
                        );
                    }
                    Shop::DB()->query($cSQL, 4);
                }
            }
        }
        // Artikel Warenlager
        Shop::DB()->delete('tartikelwarenlager', 'kArtikel', (int)$xml['tartikel attr']['kArtikel']);
        if (isset($xml['tartikel']['tartikelwarenlager']) && is_array($xml['tartikel']['tartikelwarenlager'])) {
            $oArtikelWarenlager_arr = mapArray($xml['tartikel'], 'tartikelwarenlager', $GLOBALS['mArtikelWarenlager']);

            foreach ($oArtikelWarenlager_arr as $oArtikelWarenlager) {
                if (isset($oArtikelWarenlager->dZulaufDatum) && $oArtikelWarenlager->dZulaufDatum === '') {
                    $oArtikelWarenlager->dZulaufDatum = '0000-00-00 00:00:00';
                }
                Shop::DB()->insert('tartikelwarenlager', $oArtikelWarenlager);
            }
        }
        $bTesteSonderpreis = false;
        if (isset($xml['tartikel']['tartikelsonderpreis']) && is_array($xml['tartikel']['tartikelsonderpreis'])) {
            $ArtikelSonderpreis_arr = mapArray($xml['tartikel'], 'tartikelsonderpreis', $GLOBALS['mArtikelSonderpreis']);
            if ($ArtikelSonderpreis_arr[0]->cAktiv === 'Y') {
                $specialPriceStart = explode('-', $ArtikelSonderpreis_arr[0]->dStart);
                if (count($specialPriceStart) > 2) {
                    list($start_jahr, $start_monat, $start_tag) = $specialPriceStart;
                } else {
                    $start_jahr  = null;
                    $start_monat = null;
                    $start_tag   = null;
                }
                $specialPriceEnd = explode('-', $ArtikelSonderpreis_arr[0]->dEnde);
                if (count($specialPriceEnd) > 2) {
                    list($ende_jahr, $ende_monat, $ende_tag) = $specialPriceEnd;
                } else {
                    $ende_jahr  = null;
                    $ende_monat = null;
                    $ende_tag   = null;
                }
                $nEndStamp   = mktime(null);
                $nStartStamp = mktime(0, 0, 0, $start_monat, $start_tag, $start_jahr);
                $nNowStamp   = time();

                if ($ende_jahr > 0) {
                    $nEndStamp = mktime(0, 0, 0, $ende_monat, $ende_tag + 1, $ende_jahr);
                }
                $bTesteSonderpreis = ($nNowStamp >= $nStartStamp
                    && ($nNowStamp < $nEndStamp || (int)$ArtikelSonderpreis_arr[0]->dEnde === 0)
                    && ($ArtikelSonderpreis_arr[0]->nIstAnzahl === 0 || ($ArtikelSonderpreis_arr[0]->nIstAnzahl === '1'
                            && (int)$ArtikelSonderpreis_arr[0]->nAnzahl < (int)$xml['tartikel']['fLagerbestand'])));
            }
            $spCount = count($ArtikelSonderpreis_arr);
            for ($i = 0; $i < $spCount; ++$i) {
                $Sonderpreise_arr = mapArray(
                    $xml['tartikel']['tartikelsonderpreis'],
                    'tsonderpreise',
                    $GLOBALS['mSonderpreise']
                );
                if ($bTesteSonderpreis === true) {
                    foreach ($Sonderpreise_arr as $Sonderpreise) {
                        setzePreisverlauf(
                            $ArtikelSonderpreis_arr[0]->kArtikel,
                            $Sonderpreise->kKundengruppe,
                            $Sonderpreise->fNettoPreis
                        );
                    }
                }
                updateXMLinDB(
                    $xml['tartikel']['tartikelsonderpreis'],
                    'tsonderpreise',
                    $GLOBALS['mSonderpreise'],
                    'kArtikelSonderpreis',
                    'kKundengruppe'
                );
            }
            DBUpdateInsert('tartikelsonderpreis', $ArtikelSonderpreis_arr, 'kArtikelSonderpreis');
        }
        // Preise für Preisverlauf
        // NettoPreis übertragen, falls kein Sonderpreis gesetzt wurde
        if (!($bTesteSonderpreis === true &&
            isset($xml['tartikel']['tartikelsonderpreis']) &&
            is_array($xml['tartikel']['tartikelsonderpreis']))
        ) {
            $oPreis_arr = mapArray($xml['tartikel'], 'tpreise', $GLOBALS['mPreise']);
            foreach ($oPreis_arr as $oPreis) {
                setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
            }
        }
        if (isset($xml['tartikel']['teigenschaft']) && is_array($xml['tartikel']['teigenschaft'])) {
            $Eigenschaft_arr = mapArray($xml['tartikel'], 'teigenschaft', $GLOBALS['mEigenschaft']);
            $eCount          = count($Eigenschaft_arr);
            for ($i = 0; $i < $eCount; ++$i) {
                if (count($Eigenschaft_arr) < 2) {
                    loescheEigenschaft($xml['tartikel']['teigenschaft attr']['kEigenschaft']);
                    updateXMLinDB(
                        $xml['tartikel']['teigenschaft'],
                        'teigenschaftsprache',
                        $GLOBALS['mEigenschaftSprache'],
                        'kEigenschaft',
                        'kSprache'
                    );
                    updateXMLinDB(
                        $xml['tartikel']['teigenschaft'],
                        'teigenschaftsichtbarkeit',
                        $GLOBALS['mEigenschaftsichtbarkeit'],
                        'kEigenschaft',
                        'kKundengruppe'
                    );
                    $EigenschaftWert_arr = mapArray(
                        $xml['tartikel']['teigenschaft'],
                        'teigenschaftwert',
                        $GLOBALS['mEigenschaftWert']
                    );
                    $ewCount             = count($EigenschaftWert_arr);
                    for ($o = 0; $o < $ewCount; ++$o) {
                        if ($ewCount < 2) {
                            loescheEigenschaftWert($xml['tartikel']['teigenschaft']['teigenschaftwert attr']['kEigenschaftWert']);
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertsprache',
                                $GLOBALS['mEigenschaftWertSprache'],
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertaufpreis',
                                $GLOBALS['mEigenschaftWertAufpreis'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertsichtbarkeit',
                                $GLOBALS['mEigenschaftWertSichtbarkeit'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'],
                                'teigenschaftwertabhaengigkeit',
                                $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        } else {
                            loescheEigenschaftWert($xml['tartikel']['teigenschaft']['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']);
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertsprache',
                                $GLOBALS['mEigenschaftWertSprache'],
                                'kEigenschaftWert',
                                'kSprache'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertaufpreis',
                                $GLOBALS['mEigenschaftWertAufpreis'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertsichtbarkeit',
                                $GLOBALS['mEigenschaftWertSichtbarkeit'],
                                'kEigenschaftWert',
                                'kKundengruppe'
                            );
                            updateXMLinDB(
                                $xml['tartikel']['teigenschaft']['teigenschaftwert'][$o],
                                'teigenschaftwertabhaengigkeit',
                                $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                                'kEigenschaftWert',
                                'kEigenschaftWertZiel'
                            );
                        }
                    }
                    DBUpdateInsert('teigenschaftwert', $EigenschaftWert_arr, 'kEigenschaftWert');
                } else {
                    //@todo: this if was added to be able to sync with wawi 1.0 - check.
                    if (isset($xml['tartikel']['teigenschaft'][$i . ' attr'])) {
                        loescheEigenschaft($xml['tartikel']['teigenschaft'][$i . ' attr']['kEigenschaft']);
                    }
                    //@todo: this if was added to be able to sync with wawi 1.0 - check.
                    if (isset($xml['tartikel']['teigenschaft'][$i])) {
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft'][$i],
                            'teigenschaftsprache',
                            $GLOBALS['mEigenschaftSprache'],
                            'kEigenschaft',
                            'kSprache'
                        );
                        updateXMLinDB(
                            $xml['tartikel']['teigenschaft'][$i],
                            'teigenschaftsichtbarkeit',
                            $GLOBALS['mEigenschaftsichtbarkeit'],
                            'kEigenschaft',
                            'kKundengruppe'
                        );
                        $EigenschaftWert_arr = mapArray(
                            $xml['tartikel']['teigenschaft'][$i],
                            'teigenschaftwert',
                            $GLOBALS['mEigenschaftWert']
                        );
                        $ewCount             = count($EigenschaftWert_arr);
                        for ($o = 0; $o < $ewCount; ++$o) {
                            if ($ewCount < 2) {
                                loescheEigenschaftWert($xml['tartikel']['teigenschaft'][$i]['teigenschaftwert attr']['kEigenschaftWert']);
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                    'teigenschaftwertsprache',
                                    $GLOBALS['mEigenschaftWertSprache'],
                                    'kEigenschaftWert',
                                    'kSprache'
                                );
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                    'teigenschaftwertaufpreis',
                                    $GLOBALS['mEigenschaftWertAufpreis'],
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                    'teigenschaftwertsichtbarkeit',
                                    $GLOBALS['mEigenschaftWertSichtbarkeit'],
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'],
                                    'teigenschaftwertabhaengigkeit',
                                    $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                                    'kEigenschaftWert',
                                    'kEigenschaftWertZiel'
                                );
                            } else {
                                loescheEigenschaftWert($xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o . ' attr']['kEigenschaftWert']);
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                    'teigenschaftwertsprache',
                                    $GLOBALS['mEigenschaftWertSprache'],
                                    'kEigenschaftWert',
                                    'kSprache'
                                );
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                    'teigenschaftwertaufpreis',
                                    $GLOBALS['mEigenschaftWertAufpreis'],
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                    'teigenschaftwertsichtbarkeit',
                                    $GLOBALS['mEigenschaftWertSichtbarkeit'],
                                    'kEigenschaftWert',
                                    'kKundengruppe'
                                );
                                updateXMLinDB(
                                    $xml['tartikel']['teigenschaft'][$i]['teigenschaftwert'][$o],
                                    'teigenschaftwertabhaengigkeit', $GLOBALS['mEigenschaftWertAbhaengigkeit'],
                                    'kEigenschaftWert',
                                    'kEigenschaftWertZiel'
                                );
                            }
                        }
                        DBUpdateInsert('teigenschaftwert', $EigenschaftWert_arr, 'kEigenschaftWert');
                    }
                }
            }
            DBUpdateInsert('teigenschaft', $Eigenschaft_arr, 'kEigenschaft');
        }
        // Alle Shop Kundengruppen holen
        $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe", 2);
        $res[]             = (int)$Artikel->kArtikel;
        fuelleArtikelKategorieRabatt($artikel_arr[0], $oKundengruppe_arr);
        if (!empty($artikel_arr[0]->kVaterartikel)) {
            $res[] = (int)$artikel_arr[0]->kVaterartikel;
        }
        //emailbenachrichtigung, wenn verfügbar
        versendeVerfuegbarkeitsbenachrichtigung($artikel_arr[0]);
    }

    return $res;
}

/**
 * @param int   $kArtikel
 * @param int   $nIstVater
 * @param bool  $bForce
 * @param array $conf
 * @return array
 */
function loescheArtikel($kArtikel, $nIstVater = 0, $bForce = false, $conf = null)
{
    $kArtikel = (int)$kArtikel;
    // get list of all categories the article was associated with
    $articleCategories = Shop::DB()->selectAll('tkategorieartikel', 'kArtikel', $kArtikel, 'kKategorie');
    if ($bForce === false &&
        isset($conf['global']['kategorien_anzeigefilter']) &&
        $conf['global']['kategorien_anzeigefilter'] === '2'
    ) {
        foreach ($articleCategories as $category) {
            // check if the article was the only one in at least one of these categories
            $categoryCount = Shop::DB()->query(
                "SELECT count(tkategorieartikel.kArtikel) AS count
                    FROM tkategorieartikel
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tkategorieartikel.kArtikel
                    WHERE tkategorieartikel.kKategorie = " . (int)$category->kKategorie . " " . gibLagerfilter(), 1
            );
            if (!isset($categoryCount->count) || (int)$categoryCount->count === 1) {
                // the category only had this article in it - flush cache
                flushCategoryTreeCache();
                break;
            }
        }
    }
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('kArtikel: ' . $kArtikel . ' - nIstVater: ' . $nIstVater, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml loescheArtikel');
    }
    if ($kArtikel > 0) {
        $manufacturerID = Shop::DB()->query("SELECT kHersteller FROM tartikel WHERE kArtikel = " . $kArtikel, 1);
        Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kArtikel', (int)$kArtikel]);
        Shop::DB()->delete('tartikel', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tpreise', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tkategorieartikel', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tartikelsprache', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tartikelattribut', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tartikelwarenlager', 'kArtikel', $kArtikel);
        loescheArtikelAttribute($kArtikel);
        loescheArtikelEigenschaftWert($kArtikel);
        loescheArtikelEigenschaft($kArtikel);
        loescheSonderpreise($kArtikel);
        Shop::DB()->delete('txsell', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tartikelmerkmal', 'kArtikel', $kArtikel);
        Shop::DB()->delete('tartikelsichtbarkeit', 'kArtikel', $kArtikel);
        loescheArtikelMediendateien($kArtikel);
        if ($bForce === false) {
            loescheArtikelDownload($kArtikel);
        } else {
            loescheDownload($kArtikel, null);
        }
        loescheArtikelUpload($kArtikel);
        loescheKonfig($kArtikel);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Artikel geloescht: ' . $kArtikel, JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
        }

        return [
            'kArtikel'    => $kArtikel,
            'categories'  => $articleCategories,
            'kHersteller' => isset($manufacturerID->kHersteller)
                ? (int)$manufacturerID->kHersteller
                : 0
        ];
    }

    return [];
}

/**
 * @param int $kEigenschaft
 */
function loescheEigenschaft($kEigenschaft)
{
    $kEigenschaft = (int)$kEigenschaft;
    if ($kEigenschaft > 0) {
        Shop::DB()->delete('teigenschaft', 'kEigenschaft', $kEigenschaft);
        Shop::DB()->delete('teigenschaftsprache', 'kEigenschaft', $kEigenschaft);
        Shop::DB()->delete('teigenschaftsichtbarkeit', 'kEigenschaft', $kEigenschaft);
        Shop::DB()->delete('teigenschaftwert', 'kEigenschaft', $kEigenschaft);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelEigenschaft($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $eigenschaft_arr = Shop::DB()->selectAll('teigenschaft', 'kArtikel', $kArtikel, 'kEigenschaft');

        if (is_array($eigenschaft_arr) && count($eigenschaft_arr)) {
            foreach ($eigenschaft_arr as $oEigenschaft) {
                loescheEigenschaft($oEigenschaft->kEigenschaft);
            }
        }
    }
}

/**
 * @param int $kEigenschaftWert
 */
function loescheEigenschaftWert($kEigenschaftWert)
{
    $kEigenschaftWert = (int)$kEigenschaftWert;
    if ($kEigenschaftWert > 0) {
        Shop::DB()->delete('teigenschaftwert', 'kEigenschaftWert', $kEigenschaftWert);
        Shop::DB()->delete('teigenschaftwertaufpreis', 'kEigenschaftWert', $kEigenschaftWert);
        Shop::DB()->delete('teigenschaftwertsichtbarkeit', 'kEigenschaftWert', $kEigenschaftWert);
        Shop::DB()->delete('teigenschaftwertsprache', 'kEigenschaftWert', $kEigenschaftWert);
        Shop::DB()->delete('teigenschaftwertabhaengigkeit', 'kEigenschaftWert', $kEigenschaftWert);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelEigenschaftWert($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $eigenschaftWert_arr = Shop::DB()->query(
            "SELECT teigenschaftwert.kEigenschaftWert
                FROM teigenschaftwert
                JOIN teigenschaft
                    ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
                WHERE teigenschaft.kArtikel = $kArtikel", 2
        );

        if (is_array($eigenschaftWert_arr) && count($eigenschaftWert_arr)) {
            foreach ($eigenschaftWert_arr as $oEigenschaftWert) {
                loescheEigenschaftWert($oEigenschaftWert->kEigenschaftWert);
            }
        }
    }
}

/**
 * @param int $kAttribut
 */
function loescheAttribute($kAttribut)
{
    $kAttribut = (int)$kAttribut;
    if ($kAttribut > 0) {
        Shop::DB()->delete('tattribut', 'kAttribut', $kAttribut);
        Shop::DB()->delete('tattributsprache', 'kAttribut', $kAttribut);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelAttribute($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $attribute_arr = Shop::DB()->selectAll('tattribut', 'kArtikel', $kArtikel, 'kAttribut');
        if (is_array($attribute_arr) && count($attribute_arr)) {
            foreach ($attribute_arr as $oAttribut) {
                loescheAttribute($oAttribut->kAttribut);
            }
        }
    }
}

/**
 * @param int $kMedienDatei
 */
function loescheMediendateien($kMedienDatei)
{
    $kMedienDatei = (int)$kMedienDatei;
    if ($kMedienDatei > 0) {
        Shop::DB()->delete('tmediendatei', 'kMedienDatei', $kMedienDatei);
        Shop::DB()->delete('tmediendateisprache', 'kMedienDatei', $kMedienDatei);
        Shop::DB()->delete('tmediendateiattribut', 'kMedienDatei', $kMedienDatei);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelMediendateien($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $mediendateien_arr = Shop::DB()->selectAll('tmediendatei', 'kArtikel', $kArtikel, 'kMedienDatei');
        if (is_array($mediendateien_arr) && count($mediendateien_arr)) {
            foreach ($mediendateien_arr as $oMediendatei) {
                loescheMediendateien($oMediendatei->kMedienDatei);
            }
        }
    }
}

/**
 * @param int $kUploadSchema
 */
function loescheUpload($kUploadSchema)
{
    $kUploadSchema = (int)$kUploadSchema;
    if ($kUploadSchema > 0) {
        Shop::DB()->delete('tuploadschema', 'kUploadSchema', $kUploadSchema);
        Shop::DB()->delete('tuploadschemasprache', 'kArtikelUpload', $kUploadSchema);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelUpload($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $uploadschema_arr = Shop::DB()->selectAll('tuploadschema', 'kCustomID', $kArtikel, 'kUploadSchema');
        if (is_array($uploadschema_arr) && count($uploadschema_arr)) {
            foreach ($uploadschema_arr as $oUploadschema) {
                loescheUpload($oUploadschema->kUploadSchema);
            }
        }
    }
}

/**
 * @param int $kArtikel
 * @param int $kDownload
 */
function loescheDownload($kArtikel, $kDownload = null)
{
    $kArtikel  = (int)$kArtikel;
    $kDownload = (int)$kDownload;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('loescheDownload: kArtikel:' . var_export($kArtikel, true) . '- kDownload:' .
            var_export($kDownload, true), JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
    }
    if ($kArtikel > 0) {
        if ($kDownload > 0) {
            Shop::DB()->delete('tartikeldownload', ['kArtikel', 'kDownload'], [$kArtikel, $kDownload]);
        } else {
            Shop::DB()->delete('tartikeldownload', 'kArtikel', $kArtikel);
        }
    }
    if ($kDownload > 0) {
        Shop::DB()->delete('tdownload', 'kDownload', $kDownload);
        Shop::DB()->delete('tdownloadsprache', 'kDownload', $kDownload);
    }
}

/**
 * @param int $kArtikel
 */
function loescheArtikelDownload($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $downloadKeys = getDownloadKeys($kArtikel);
        foreach ($downloadKeys as $kDownload) {
            loescheDownload($kArtikel, $kDownload);
        }
    }
}

/**
 * @param int $kArtikel
 */
function loescheKonfig($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        Shop::DB()->delete('tartikelkonfiggruppe', 'kArtikel', $kArtikel);
    }
}

/**
 * @param int $kStueckliste
 */
function loescheStueckliste($kStueckliste)
{
    $kStueckliste = (int)$kStueckliste;
    if ($kStueckliste > 0) {
        Shop::DB()->delete('tstueckliste', 'kStueckliste', $kStueckliste);
    }
}

/**
 * @param int $kArtikel
 * @return int
 */
function loescheSonderpreise($kArtikel)
{
    return Shop::DB()->queryPrepared(
        "DELETE asp, sp
            FROM tartikelsonderpreis asp
            LEFT JOIN tsonderpreise sp
            ON sp.kArtikelSonderpreis = asp.kArtikelSonderpreis
            WHERE asp.kArtikel = :articleID",
        [
            'articleID' => (int)$kArtikel,
        ],
        3
    );
}

/**
 * @param int $kArtikel
 */
function checkArtikelBildLoeschung($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $oArtikelPict_arr = Shop::DB()->selectAll(
            'tartikelpict',
            'kArtikel',
            $kArtikel,
            'kArtikelPict, kMainArtikelBild, cPfad'
        );
        // Besitzt der zu löschende Artikel Bilder?
        if (count($oArtikelPict_arr) > 0) {
            // Hat der Artikel Bilder die auf eine Verknüpfung verlinken wobei der Eigentümer Artikel des Bilder gelöscht wurde
            // und nun der zu löschende Artikel die letzte Refenz darauf ist?
            foreach ($oArtikelPict_arr as $oArtikelPict) {
                deleteArticleImage($oArtikelPict, $kArtikel);
            }
            // flush article images cache
            Shop::Cache()->flush('arr_article_images_' . $kArtikel);
        }
    }
}

/**
 * checks whether the article is a child product in any configurator
 * and returns the product IDs of parent products if yes
 *
 * @param int $kArtikel
 * @return array
 */
function getConfigParents($kArtikel)
{
    $kArtikel         = (int)$kArtikel;
    $parentProductIDs = [];
    $configItems      = Shop::DB()->selectAll('tkonfigitem', 'kArtikel', $kArtikel, 'kKonfiggruppe');
    if (!is_array($configItems) || count($configItems) === 0) {
        return $parentProductIDs;
    }
    $configGroupIDs = [];
    foreach ($configItems as $_configItem) {
        $configGroupIDs[] = (int)$_configItem->kKonfiggruppe;
    }
    $parents = Shop::DB()->query(
        'SELECT kArtikel 
            FROM tartikelkonfiggruppe 
            WHERE kKonfiggruppe IN (' . implode(',', $configGroupIDs) . ')', 2
    );
    if (!is_array($parents) || count($parents) === 0) {
        return $parentProductIDs;
    }
    foreach ($parents as $_parent) {
        $parentProductIDs[] = (int)$_parent->kArtikel;
    }

    return $parentProductIDs;
}

/**
 * @param  int $kArtikel
 * @return array
 */
function getDownloadKeys($kArtikel)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel > 0) {
        $download_arr = Shop::DB()->selectAll('tartikeldownload', 'kArtikel', $kArtikel, 'kDownload');
        array_walk($download_arr, function (&$item, $key) {
            $item = (int)$item->kDownload;
        });

        return $download_arr;
    }

    return [];
}

/**
 * clear all caches associated with a product ID
 * including manufacturers, categories, parent products
 *
 * @param array $articles
 */
function clearProductCaches($articles)
{
    if (count($articles) > 0) {
        $start     = microtime(true);
        $cacheTags = [];
        $deps      = [];

        foreach ($articles as $article) {
            if (isset($article['kArtikel'])) {
                // generated by bearbeiteDeletes()
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$article['kArtikel'];
                if ($article['kHersteller'] > 0) {
                    $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . (int)$article['kHersteller'];
                }
                foreach ($article['categories'] as $category) {
                    $cacheTags[] = CACHING_GROUP_CATEGORY . '_' . (int)$category->kKategorie;
                }
            } elseif (is_numeric($article)) {
                // generated by bearbeiteInsert()
                $parentIDs = getConfigParents($article);
                foreach ($parentIDs as $parentID) {
                    $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$parentID;
                }
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$article;
                $deps     [] = (int)$article;
            }
        }
        // additionally get dependencies for articles that were inserted
        if (count($deps) > 0) {
            // flush cache tags associated with the article's manufacturer ID
            $manufacturers = Shop::DB()->query(
                'SELECT DISTINCT kHersteller 
                    FROM tartikel 
                    WHERE kArtikel IN (' . implode(',', $deps) . ') 
                        AND kHersteller > 0',
                2
            );
            foreach ($manufacturers as $manufacturer) {
                $cacheTags[] = CACHING_GROUP_MANUFACTURER . '_' . (int)$manufacturer->kHersteller;
            }
            // flush cache tags associated with the article's category IDs
            $categories = Shop::DB()->query(
                "SELECT DISTINCT kKategorie
                    FROM tkategorieartikel
                    WHERE kArtikel IN (" . implode(',', $deps) . ')',
                2
            );
            foreach ($categories as $category) {
                $cacheTags[] = CACHING_GROUP_CATEGORY . '_' . (int)$category->kKategorie;
            }
            // flush parent article IDs
            $parentArticles = Shop::DB()->query(
                "SELECT DISTINCT kVaterArtikel AS id
                    FROM tartikel
                    WHERE kArtikel IN (" . implode(',', $deps) . ')
                    AND kVaterArtikel > 0',
                2
            );
            foreach ($parentArticles as $parentArticle) {
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$parentArticle->id;
            }
        }

        $cacheTags[] = 'jtl_mmf';
        $cacheTags   = array_unique($cacheTags);
        // flush article cache, category cache and cache for gibMerkmalFilterOptionen() and mega menu/category boxes
        $totalCount = Shop::Cache()->flushTags($cacheTags);
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            $end = microtime(true);
            Jtllog::writeLog(
                'Flushed a total of ' . $totalCount .
                ' keys for ' . count($cacheTags) .
                ' tags in ' . ($end - $start) . 's',
                JTLLOG_LEVEL_DEBUG,
                false,
                'Artikel_xml'
            );
        }
    }
}
