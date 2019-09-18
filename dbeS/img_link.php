<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

ob_start();
require_once __DIR__ . '/syncinclude.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'img_link_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'img_link_xml');
        }
        if ($archive->extract(PCLZIP_OPT_PATH, PFAD_SYNC_TMP)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . PFAD_SYNC_TMP . $zip['filename'] .
                        ' size: ' . filesize(PFAD_SYNC_TMP . $zip['filename']),
                        JTLLOG_LEVEL_DEBUG, false, 'img_link_xml');
                }
                $xml = simplexml_load_file(PFAD_SYNC_TMP . $zip['filename']);
                if ($zip['filename'] === 'bildartikellink.xml') {
                    bildartikellink_xml($xml);
                } elseif ($zip['filename'] === 'del_bildartikellink.xml') {
                    del_bildartikellink_xml($xml);
                }
                removeTemporaryFiles(PFAD_SYNC_TMP . $zip['filename']);
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'img_link_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'img_link_xml');
    }
}

echo $return;

/**
 * @param SimpleXMLElement $xml
 */
function bildartikellink_xml(SimpleXMLElement $xml)
{
    $items           = get_array($xml);
    $articleIDs      = [];
    $cacheArticleIDs = [];
    foreach ($items as $item) {
        //delete link first. Important because jtl-wawi does not send del_bildartikellink when image is updated.
        Shop::DB()->delete('tartikelpict', ['kArtikel', 'nNr'], [(int)$item->kArtikel, (int)$item->nNr]);
        $articleIDs[] = (int)$item->kArtikel;
        DBUpdateInsert('tartikelpict', [$item], 'kArtikelPict');
    }
    foreach (array_unique($articleIDs) as $_aid) {
        $cacheArticleIDs[] = CACHING_GROUP_ARTICLE . '_' . $_aid;
        MediaImage::clearCache(Image::TYPE_PRODUCT, $_aid);
    }
    Shop::Cache()->flushTags($cacheArticleIDs);
}

/**
 * @param SimpleXMLElement $xml
 */
function del_bildartikellink_xml(SimpleXMLElement $xml)
{
    $items           = get_del_array($xml);
    $articleIDs      = [];
    $cacheArticleIDs = [];
    foreach ($items as $item) {
        del_img_item($item);
        $articleIDs[] = $item->kArtikel;
    }
    foreach (array_unique($articleIDs) as $_aid) {
        $cacheArticleIDs[] = CACHING_GROUP_ARTICLE . '_' . $_aid;
        MediaImage::clearCache(Image::TYPE_PRODUCT, $_aid);
    }
    Shop::Cache()->flushTags($cacheArticleIDs);
}

/**
 * @param stdClass $item
 */
function del_img_item($item) {
    $image = Shop::DB()->select('tartikelpict', 'kArtikel', $item->kArtikel, 'nNr', $item->nNr);
    if (is_object($image)) {
        // is last reference
        $res = Shop::DB()->query("SELECT COUNT(*) AS cnt FROM tartikelpict WHERE kBild = " . (int)$image->kBild, 1);
        if ($res->cnt == 1) {
            Shop::DB()->delete('tbild', 'kBild', (int)$image->kBild);
            $storage = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $image->cPfad;
            if (file_exists($storage)) {
                @unlink($storage);
            }
            Jtllog::writeLog('Removed last image link: ' . (int)$image->kBild, JTLLOG_LEVEL_NOTICE, false, 'img_link_xml');
        }
        Shop::DB()->delete('tartikelpict', ['kArtikel', 'nNr'], [(int)$item->kArtikel, (int)$item->nNr]);
    }
}

/**
 * @param SimpleXMLElement $xml
 * @return array
 */
function get_del_array(SimpleXMLElement $xml)
{
    $items = [];
    foreach ($xml->children() as $child) {
        $item    = (object)[
            'nNr'      => (int)$child->nNr,
            'kArtikel' => (int)$child->kArtikel
        ];
        $items[] = $item;
    }

    return $items;
}

/**
 * @param SimpleXMLElement $xml
 * @return array
 */
function get_array(SimpleXMLElement $xml)
{
    $items = [];
    /** @var SimpleXMLElement $child */
    foreach ($xml->children() as $child) {
        $item    = (object)[
            'cPfad'        => '',
            'kBild'        => (int)$child->attributes()->kBild,
            'nNr'          => (int)$child->attributes()->nNr,
            'kArtikel'     => (int)$child->attributes()->kArtikel,
            'kArtikelPict' => (int)$child->attributes()->kArtikelPict
        ];
        $imageId = (int)$child->attributes()->kBild;
        $image   = Shop::DB()->select('tbild', 'kBild', $imageId);
        if (is_object($image)) {
            $item->cPfad = $image->cPfad;
            $items[]     = $item;
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Missing reference in tbild (Key: ' . $imageId . ')', JTLLOG_LEVEL_DEBUG, false, 'img_link_xml');
        }
    }

    return $items;
}
