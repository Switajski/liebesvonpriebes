<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $filesize
 * @return array
 */
function getItems($filesize = false)
{
    $smarty = JTLSmarty::getInstance(false, true);
    $smarty->configLoad("german.conf", 'bilderverwaltung');

    $item = (object) [
        'name'  => $smarty->config_vars["typeProduct"],
        'type'  => Image::TYPE_PRODUCT,
        'stats' => MediaImage::getStats(Image::TYPE_PRODUCT, $filesize)
    ];

    return [Image::TYPE_PRODUCT => $item];
}

/**
 * @param $type
 * @return IOError
 */
function loadStats($type)
{
    $items = getItems(true);

    if ($type === null || in_array($type, $items, true)) {
        return new IOError('Invalid argument request', 500);
    }

    return $items[$type]->stats;
}

/**
 * @param $index
 * @return object
 */
function cleanupStorage($index)
{
    $index      = (int)$index;
    $startIndex = $index;

    if ($index === null) {
        return new IOError('Invalid argument request', 500);
    }

    $directory = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE;
    $started   = time();
    $result    = (object)[
        'total'         => 0,
        'cleanupTime'   => 0,
        'nextIndex'     => 0,
        'deletedImages' => 0,
        'deletes'       => []
    ];

    if ($index === 0) {
        // at the first run, check how many files actually exist in the storage dir
        $storageIterator           = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
        $_SESSION['image_count']   = iterator_count($storageIterator);
        $_SESSION['deletedImages'] = 0;
        $_SESSION['checkedImages'] = 0;
    }

    $total            = $_SESSION['image_count'];
    $checkedInThisRun = 0;
    $deletedInThisRun = 0;
    $idx              = 0;

    foreach (new LimitIterator(new DirectoryIterator($directory), $index, IMAGE_CLEANUP_LIMIT) as $idx => $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        ++$checkedInThisRun;
        $imageIsUsed = Shop::DB()->select('tartikelpict', 'cPfad', $fileInfo->getFilename()) !== null;
        // files in the storage folder that have no associated entry in tartikelpict are considered orphaned
        if (!$imageIsUsed) {
            $result->deletes[] = $fileInfo->getFilename();
            unlink($fileInfo->getPathname());
            ++$_SESSION['deletedImages'];
            ++$deletedInThisRun;
        }
    }
    // increment total number of checked files by the amount checked in this run
    $_SESSION['checkedImages'] += $checkedInThisRun;
    $index                      = ($idx > 0) ? $idx + 1 - $deletedInThisRun : $total;
    // avoid endless recursion
    if ($index === $startIndex && $deletedInThisRun === 0) {
        $index = $total;
    }
    $result->total             = $total;
    $result->cleanupTime       = time() - $started;
    $result->nextIndex         = $index;
    $result->checkedFiles      = $checkedInThisRun;
    $result->checkedFilesTotal = $_SESSION['checkedImages'];
    $result->deletedImages     = $_SESSION['deletedImages'];
    if ($index >= $total) {
        // done.
        unset($_SESSION['image_count'], $_SESSION['deletedImages'], $_SESSION['checkedImages']);
    }

    return $result;
}

/**
 * @param $type
 * @param $isAjax
 * @return array
 */
function clearImageCache($type, $isAjax)
{
    if ($type !== null && preg_match('/[a-z]*/', $type)) {
        MediaImage::clearCache($type);
        unset($_SESSION['image_count'], $_SESSION['renderedImages']);
        if (isset($isAjax) && $isAjax === true) {
            return ['success' => 'Cache wurde erfolgreich zur&uuml;ckgesetzt'];
        }
        Shop::Smarty()->assign('success', 'Cache wurde erfolgreich zur&uuml;ckgesetzt');
    }
}

function generateImageCache($type, $index)
{
    $index = (int)$index;

    if ($type === null || $index === null) {
        return new IOError('Invalid argument request', 500);
    }

    $started = time();
    $result  = (object)[
        'total'          => 0,
        'renderTime'     => 0,
        'nextIndex'      => 0,
        'renderedImages' => 0,
        'images'         => []
    ];

    if ($index === 0) {
        $_SESSION['image_count']    = count(MediaImage::getImages($type, true));
        $_SESSION['renderedImages'] = 0;
    }

    $total  = $_SESSION['image_count'];
    $images = MediaImage::getImages($type, true, $index, IMAGE_PRELOAD_LIMIT);
    while (count($images) === 0 && $index < $total) {
        $index += 10;
        $images = MediaImage::getImages($type, true, $index, IMAGE_PRELOAD_LIMIT);
    }
    foreach ($images as $image) {
        $seconds = time() - $started;
        if ($seconds >= 10) {
            break;
        }
        $result->images[] = MediaImage::cacheImage($image);
        ++$index;
        ++$_SESSION['renderedImages'];
    }
    $result->total          = $total;
    $result->renderTime     = time() - $started;
    $result->nextIndex      = $index;
    $result->renderedImages = $_SESSION['renderedImages'];
    if ($_SESSION['renderedImages'] >= $total) {
        unset($_SESSION['image_count'], $_SESSION['renderedImages']);
    }

    return $result;
}

/**
 * @param $type
 * @param $limit
 * @return array
 */
function getCorruptedImages($type, $limit)
{
    static $offset = 0;
    $corruptedImages = [];
    $totalImages = count(MediaImage::getImages($type));

    do {
        $images = MediaImage::getImages($type, false, $offset, $limit);
        foreach ($images as $image) {
            $raw = $image->getRaw(true);
            $fallback = $image->getFallbackThumb(Image::SIZE_XS);
            if (!file_exists($raw) && !file_exists(PFAD_ROOT . $fallback)) {
                $corruptedImage  = (object) [
                    'article' => [],
                    'picture' => ''
                ];
                $articleDB           = Shop::DB()->select('tartikel', 'kArtikel', $image->getId());
                $articleDB->cURLFull = baueURL($articleDB, URLART_ARTIKEL, 0, false, true);
                $article             = (object) [
                    'articleNr'      => $articleDB->cArtNr,
                    'articleURLFull' => $articleDB->cURLFull
                ];
                $corruptedImage->article[] = $article;
                $corruptedImage->picture   = $image->getPath();
                if (array_key_exists($image->getPath(), $corruptedImages)) {
                    $corruptedImages[$corruptedImage->picture]->article[] = $article;
                } else {
                    $corruptedImages[$corruptedImage->picture] = $corruptedImage;
                }
            }
        }
        $offset += count($images);
    } while(count($corruptedImages) < $limit && $offset < $totalImages);

    return [Image::TYPE_PRODUCT => array_slice($corruptedImages, 0, $limit)];
}