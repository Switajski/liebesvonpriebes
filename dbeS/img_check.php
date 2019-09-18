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
        Jtllog::writeLog('Image Check: Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Image Check: Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
        }

        $newTmpDir = PFAD_SYNC_TMP . uniqid('check_') . '/';
        mkdir($newTmpDir, 0777, true);

        if ($extracedList = $archive->extract(PCLZIP_OPT_PATH, $newTmpDir)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('Image Check: bearbeite: ' . $newTmpDir . $zip['filename'] .
                        ' size: ' . filesize($newTmpDir . $zip['filename']),
                        JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
                }
                if ($zip['filename'] === 'bildercheck.xml') {
                    $xml = simplexml_load_file($newTmpDir . $zip['filename']);
                    bildercheck_xml($xml);
                }
                removeTemporaryFiles($newTmpDir . $zip['filename']);
            }
            removeTemporaryFiles($newTmpDir);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Image Check Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'img_check_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Image Check Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'img_check_xml');
    }
}
echo $return;

/**
 * @param SimpleXMLElement $xml
 */
function bildercheck_xml(SimpleXMLElement $xml)
{
    $found  = [];
    $sqls   = [];
    $object = get_object($xml);
    foreach ($object->items as $item) {
        $hash   = Shop::DB()->escape($item->hash);
        $sqls[] = "(kBild={$item->id} && cPfad='{$hash}')";
    }
    $sqlOr  = implode(' || ', $sqls);
    $sql    = "SELECT kBild AS id, cPfad AS hash FROM tbild WHERE {$sqlOr}";
    $images = Shop::DB()->query($sql, 2);
    if ($images !== false) {
        foreach ($images as $image) {
            $storage = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $image->hash;
            if (file_exists($storage)) {
                $found[] = $image->id;
            } else {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog("Dropping orphan {$image->id} -> {$image->hash}: no such file",
                        JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
                }
                Shop::DB()->delete('tbild', 'kBild', $image->id);
                Shop::DB()->delete('tartikelpict', 'kBild', $image->id);
            }
        }
    }
    if ($object->cloud) {
        foreach ($object->items as $item) {
            if (in_array($item->id, $found)) {
                continue;
            }
            if (cloud_download($item->hash)) {
                $oBild = (object)[
                    'kBild' => $item->id,
                    'cPfad' => $item->hash
                ];
                DBUpdateInsert('tbild', [$oBild], 'kBild');
                $found[] = $item->id;
            }
        }
    }

    if (!empty($found) && Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        $checkids = array_map(function ($item) {
            return $item->id;
        }, $object->items);

        $checklist = implode(';', $checkids);
        Jtllog::writeLog('Checking: ' . $checklist, JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
    }

    $missing = array_filter($object->items, function ($item) use ($found) {
        return !in_array($item->id, $found);
    });

    $ids = array_map(function ($item) {
        return $item->id;
    }, $missing);

    $idlist = implode(';', $ids);
    push_response("0;\n<bildcheck><notfound>{$idlist}</notfound></bildcheck>");
}

/**
 * @param string $content
 */
function push_response($content)
{
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Image check response: ' . htmlentities($content), JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
    }

    ob_clean();
    echo $content;
    exit;
}

/**
 * @param SimpleXMLElement $xml
 * @return object
 */
function get_object(SimpleXMLElement $xml)
{
    $cloudURL = (string)$xml->attributes()->cloudURL;
    $check    = (object)[
        'url'   => $cloudURL,
        'cloud' => strlen($cloudURL) > 0,
        'items' => []
    ];
    /** @var SimpleXMLElement $child */
    foreach ($xml->children() as $child) {
        $check->items[] = (object)[
            'id'   => (int)$child->attributes()->kBild,
            'hash' => (string)$child->attributes()->cHash
        ];
    }

    return $check;
}

/**
 * @param string $hash
 * @return bool
 */
function cloud_download($hash)
{
    $service   = ImageCloud::getInstance();
    $url       = $service->get($hash);
    $imageData = download($url);

    if ($imageData !== null) {
        $tmpFile = tempnam(sys_get_temp_dir(), 'jtl');
        $filename = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $hash;

        file_put_contents($tmpFile, $imageData, FILE_BINARY);

        return rename($tmpFile, $filename);
    }
    
    return false;
}

/**
 * @param string $url
 * @return mixed|null
 */
function download($url)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, DEFAULT_CURL_OPT_VERIFYHOST);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, DEFAULT_CURL_OPT_VERIFYPEER);
    curl_setopt($ch, CURLOPT_USERAGENT, 'JTL-Shop/' . JTL_VERSION);

    $data = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $code === 200 ? $data : null;
}
