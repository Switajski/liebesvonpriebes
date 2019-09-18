<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return mixed
 */
function holeAlleBanner()
{
    $oBanner = new ImageMap();

    return $oBanner->fetchAll();
}

/**
 * @param int  $kImageMap
 * @param bool $fill
 * @return mixed
 */
function holeBanner($kImageMap, $fill = true)
{
    $oBanner = new ImageMap();

    return $oBanner->fetch($kImageMap, true, $fill);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function holeExtension($kImageMap)
{
    return Shop::DB()->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', (int)$kImageMap);
}

/**
 * @param int $kImageMap
 * @return mixed
 */
function entferneBanner($kImageMap)
{
    $kImageMap = (int)$kImageMap;
    $oBanner   = new ImageMap();
    Shop::DB()->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $kImageMap]);

    return $oBanner->delete($kImageMap);
}

/**
 * @return array
 */
function holeBannerDateien()
{
    $cBannerFile_arr = [];
    if (($nHandle = opendir(PFAD_ROOT . PFAD_BILDER_BANNER)) !== false) {
        while (false !== ($cFile = readdir($nHandle))) {
            if ($cFile !== '.' && $cFile !== '..' && $cFile[0] !== '.') {
                $cBannerFile_arr[] = $cFile;
            }
        }
        closedir($nHandle);
    }

    return $cBannerFile_arr;
}

/**
 * @param $cData
 * @return IOResponse
 */
function saveBannerAreasIO($cData)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ImageMap.php';

    $oBanner  = new ImageMap();
    $response = new IOResponse();
    $oData    = json_decode($cData);

    foreach ($oData->oArea_arr as $oArea) {
        $oArea->cTitel        = utf8_decode($oArea->cTitel);
        $oArea->cUrl          = utf8_decode($oArea->cUrl);
        $oArea->cBeschreibung = utf8_decode($oArea->cBeschreibung);
        $oArea->cStyle        = utf8_decode($oArea->cStyle);
        $oArea->kArtikel      = (int)$oArea->kArtikel;
    }

    $oBanner->saveAreas($oData);

    return $response;
}