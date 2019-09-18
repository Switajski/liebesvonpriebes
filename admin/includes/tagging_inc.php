<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kTag
 * @param int $kSprache
 * @return int
 */
function holeTagDetailAnzahl($kTag, $kSprache)
{
    if ((int)$kTag > 0 && (int)$kSprache > 0) {
        $oTagArtikel = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM ttagartikel
                JOIN ttag 
                    ON ttag.kTag = ttagartikel.kTag
                    AND ttag.kSprache = " . (int)$kSprache . "
                WHERE ttagartikel.kTag = " . (int)$kTag, 1
        );

        return isset($oTagArtikel->nAnzahl)
            ? (int)$oTagArtikel->nAnzahl
            : 0;
    }

    return 0;
}

/**
 * @param int    $kTag
 * @param int    $kSprache
 * @param string $cLimit
 * @return bool
 */
function holeTagDetail($kTag, $kSprache, $cLimit)
{
    if (!$kSprache) {
        $kSprache = $_SESSION['kSprache'];
    }
    $kSprache = (int)$kSprache;
    $kTag     = (int)$kTag;
    if ($kTag > 0 && $kSprache > 0) {
        $oTagArtikel_arr = Shop::DB()->query(
            "SELECT ttagartikel.kTag, ttag.cName, tartikel.cName AS acName, 
                tartikel.kArtikel AS kArtikel, tseo.cSeo
                FROM ttagartikel
                JOIN ttag 
                    ON ttag.kTag = ttagartikel.kTag
                    AND ttag.kSprache = " . $kSprache . "
                JOIN tartikel 
                        ON tartikel.kArtikel = ttagartikel.kArtikel
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = " . $kSprache . "
                WHERE ttagartikel.kTag = " . $kTag . "
                    AND ttag.kSprache = " . $kSprache . "
                GROUP BY tartikel.kArtikel
                ORDER BY tartikel.cName" . $cLimit, 2
        );
        // URL für die Artikel bauen
        if (is_array($oTagArtikel_arr) && count($oTagArtikel_arr) > 0) {
            $shopURL = Shop::getURL();
            foreach ($oTagArtikel_arr as $i => $oTagArtikel) {
                $oTagArtikel_arr[$i]->cURL = $shopURL . '/' . baueURL($oTagArtikel, URLART_ARTIKEL);
            }
        }

        return $oTagArtikel_arr;
    }

    return false;
}

/**
 * @param array $kArtikel_arr
 * @param int   $kTag
 * @return bool
 */
function loescheTagsVomArtikel($kArtikel_arr, $kTag)
{
    $kTag = (int)$kTag;
    if ($kTag > 0 && is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
        foreach ($kArtikel_arr as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            Shop::DB()->delete('ttagartikel', ['kArtikel', 'kTag'], [$kArtikel, $kTag]);
            $oTagArtikel_arr = Shop::DB()->selectAll('ttagartikel', 'kTag', $kTag);
            // Es gibt keine Artikel mehr zu dem Tag => Tag aus ttag / tseo löschen
            if (count($oTagArtikel_arr) === 0) {
                Shop::DB()->query(
                    "DELETE ttag, tseo
                        FROM ttag
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kTag'
                            AND tseo.kKey = ttag.kTag
                        WHERE ttag.kTag = " . $kTag, 4
                );
            }
            Shop::Cache()->flushTags(['CACHING_GROUP_ARTICLE_' . $kArtikel]);
        }

        return true;
    }

    return false;
}

/**
 * @param array $tagIDs
 * @return int
 */
function flushAffectedArticleCache(array $tagIDs)
{
    //get tagged article IDs to invalidate their cache
    $_affectedArticles = Shop::DB()->query("
        SELECT DISTINCT kArtikel
            FROM ttagartikel
            WHERE kTag IN (" . implode(', ', $tagIDs) . ")", 2
    );
    if (count($_affectedArticles) > 0) {
        $articleCacheIDs = [];
        foreach ($_affectedArticles as $_article) {
            $articleCacheIDs[] = CACHING_GROUP_ARTICLE . '_' . $_article->kArtikel;
        }

        return Shop::Cache()->flushTags($articleCacheIDs);
    }

    return 0;
}
