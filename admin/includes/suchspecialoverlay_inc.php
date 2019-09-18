<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return mixed
 */
function gibAlleSuchspecialOverlays()
{
    return Shop::DB()->query("
        SELECT tsuchspecialoverlay.*, tsuchspecialoverlaysprache.kSprache, 
            tsuchspecialoverlaysprache.cBildPfad, tsuchspecialoverlaysprache.nAktiv,
            tsuchspecialoverlaysprache.nPrio, tsuchspecialoverlaysprache.nMargin,
            tsuchspecialoverlaysprache.nTransparenz,
            tsuchspecialoverlaysprache.nGroesse, tsuchspecialoverlaysprache.nPosition
            FROM tsuchspecialoverlay
            LEFT JOIN tsuchspecialoverlaysprache 
                ON tsuchspecialoverlaysprache.kSuchspecialOverlay = tsuchspecialoverlay.kSuchspecialOverlay
                AND tsuchspecialoverlaysprache.kSprache = " . (int)$_SESSION['kSprache'] . "
            ORDER BY tsuchspecialoverlay.cSuchspecial", 2
    );
}

/**
 * @param int $kSuchspecialOverlay
 * @return mixed
 */
function gibSuchspecialOverlay($kSuchspecialOverlay)
{
    return Shop::DB()->query("
        SELECT tsuchspecialoverlay.*, tsuchspecialoverlaysprache.kSprache, tsuchspecialoverlaysprache.cBildPfad, 
            tsuchspecialoverlaysprache.nAktiv, tsuchspecialoverlaysprache.nPrio, tsuchspecialoverlaysprache.nMargin, 
            tsuchspecialoverlaysprache.nTransparenz, tsuchspecialoverlaysprache.nGroesse, 
            tsuchspecialoverlaysprache.nPosition
            FROM tsuchspecialoverlay
            LEFT JOIN tsuchspecialoverlaysprache 
                ON tsuchspecialoverlaysprache.kSuchspecialOverlay = tsuchspecialoverlay.kSuchspecialOverlay
                AND tsuchspecialoverlaysprache.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tsuchspecialoverlay.kSuchspecialOverlay = " . (int)$kSuchspecialOverlay, 1
    );
}

/**
 * @param int   $kSuchspecialOverlay
 * @param array $cPost_arr
 * @param array $cFiles_arr
 * @return bool
 */
function speicherEinstellung($kSuchspecialOverlay, $cPost_arr, $cFiles_arr)
{
    $oSuchspecialoverlaySprache                      = new stdClass();
    $oSuchspecialoverlaySprache->kSuchspecialOverlay = (int)$kSuchspecialOverlay;
    $oSuchspecialoverlaySprache->kSprache            = $_SESSION['kSprache'];
    $oSuchspecialoverlaySprache->nAktiv              = (int)$cPost_arr['nAktiv'];
    $oSuchspecialoverlaySprache->nTransparenz        = (int)$cPost_arr['nTransparenz'];
    $oSuchspecialoverlaySprache->nGroesse            = (int)$cPost_arr['nGroesse'];
    $oSuchspecialoverlaySprache->nPosition           = isset($cPost_arr['nPosition'])
        ? (int)$cPost_arr['nPosition']
        : 0;

    if (!isset($cPost_arr['nPrio']) || $cPost_arr['nPrio'] == '-1') {
        return false;
    }

    $oSuchspecialoverlaySprache->nPrio     = (int)$cPost_arr['nPrio'];
    $oSuchspecialoverlaySprache->cBildPfad = '';

    if (strlen($cFiles_arr['cSuchspecialOverlayBild']['name']) > 0) {
        $oSuchspecialoverlaySprache->cBildPfad = 'kSuchspecialOverlay_' . $_SESSION['kSprache'] . '_' .
            (int)$kSuchspecialOverlay . mappeFileTyp($cFiles_arr['cSuchspecialOverlayBild']['type']);
    } else {
        $oSuchspecialoverlaySpracheTMP = Shop::DB()->select(
            'tsuchspecialoverlaysprache',
            'kSuchspecialOverlay',
            (int)$kSuchspecialOverlay,
            'kSprache',
            (int)$_SESSION['kSprache']
        );
        if (isset($oSuchspecialoverlaySpracheTMP->cBildPfad) && strlen($oSuchspecialoverlaySpracheTMP->cBildPfad)) {
            $oSuchspecialoverlaySprache->cBildPfad = $oSuchspecialoverlaySpracheTMP->cBildPfad;
        }
    }

    if ($oSuchspecialoverlaySprache->kSuchspecialOverlay > 0) {
        if (strlen($cFiles_arr['cSuchspecialOverlayBild']['name']) > 0) {
            loescheBild($oSuchspecialoverlaySprache);
            speicherBild($cFiles_arr, $oSuchspecialoverlaySprache);
        }
        Shop::DB()->delete(
            'tsuchspecialoverlaysprache',
            ['kSuchspecialOverlay', 'kSprache'],
            [(int)$kSuchspecialOverlay, (int)$_SESSION['kSprache']]
        );
        Shop::DB()->insert('tsuchspecialoverlaysprache', $oSuchspecialoverlaySprache);

        return true;
    }

    return false;
}

/**
 * @param resource $dst_im
 * @param resource $src_im
 * @param int      $dst_x
 * @param int      $dst_y
 * @param int      $src_x
 * @param int      $src_y
 * @param int      $src_w
 * @param int      $src_h
 * @param int      $pct
 * @return bool
 */
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    if (!isset($pct)) {
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx($src_im);
    $h = imagesy($src_im);
    // Turn alpha blending off
    imagealphablending($src_im, false);

    $minalpha = 0;

    //loop through image pixels and modify alpha for each
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat($src_im, $x, $y);
            $alpha   = ($colorxy >> 24) & 0xFF;
            //calculate new alpha
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha(
                $src_im,
                ($colorxy >> 16) & 0xFF,
                ($colorxy >> 8) & 0xFF,
                $colorxy & 0xFF,
                $alpha
            );
            //set pixel with the new color + opacity
            if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                return false;
            }
        }
    }

    return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

/**
 * @param string $img
 * @param int    $width
 * @param int    $height
 * @return resource|null
 */
function imageload_alpha($img, $width, $height)
{
    $imgInfo = getimagesize($img);
    switch ($imgInfo[2]) {
        case 1:
            $im = imagecreatefromgif($img);
            break;
        case 2:
            $im = imagecreatefromjpeg($img);
            break;
        case 3:
            $im = imagecreatefrompng($img);
            break;
        default:
            return null;
    }

    $new = imagecreatetruecolor($width, $height);

    if ($imgInfo[2] == 1 || $imgInfo[2] == 3) {
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $width, $height, $transparent);
    }

    imagecopyresampled($new, $im, 0, 0, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

    return $new;
}

/**
 * @param string $cBild
 * @param int    $nBreite
 * @param int    $nHoehe
 * @param int    $nTransparenz
 * @return resource
 */
function ladeOverlay($cBild, $nBreite, $nHoehe, $nTransparenz)
{
    $img_src = imageload_alpha($cBild, $nBreite, $nHoehe);

    if ($nTransparenz > 0) {
        $new = imagecreatetruecolor($nBreite, $nHoehe);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $nBreite, $nHoehe, $transparent);
        imagealphablending($new, true);
        imagesavealpha($new, true);

        imagecopymerge_alpha($new, $img_src, 0, 0, 0, 0, $nBreite, $nHoehe, 100 - $nTransparenz);

        return $new;
    }

    return $img_src;
}

/**
 * @param resource $im
 * @param string   $cFormat
 * @param string   $cPfad
 * @param int      $nQuali
 * @return bool
 */
function speicherOverlay($im, $cFormat, $cPfad, $nQuali = 80)
{
    if (!$cFormat || !$im) {
        return false;
    }
    switch ($cFormat) {
        case '.jpg':
            if (!function_exists('imagejpeg')) {
                return false;
            }

            return imagejpeg($im, $cPfad, $nQuali);
            break;
        case '.png':
            if (!function_exists('imagepng')) {
                return false;
            }

            return imagepng($im, $cPfad);
            break;
        case '.gif':
            if (!function_exists('imagegif')) {
                return false;
            }

            return imagegif($im, $cPfad);
            break;
        case '.bmp':
            if (!function_exists('imagewbmp')) {
                return false;
            }

            return imagewbmp($im, $cPfad);
            break;
    }

    return false;
}

/**
 * @param string $cBild
 * @param string $cBreite
 * @param string $cHoehe
 * @param int    $nGroesse
 * @param int    $nTransparenz
 * @param string $cFormat
 * @param string $cPfad
 */
function erstelleOverlay($cBild, $cBreite, $cHoehe, $nGroesse, $nTransparenz, $cFormat, $cPfad)
{
    $Einstellungen = Shop::getSettings([CONF_BILDER]);
    $bSkalieren    = !($Einstellungen['bilder']['bilder_skalieren'] === 'N'); //@todo noch beachten

    $nBreite = $Einstellungen['bilder'][$cBreite];
    $nHoehe  = $Einstellungen['bilder'][$cHoehe];

    list($nOverlayBreite, $nOverlayHoehe) = getimagesize($cBild);

    $nOffX = $nOffY = 1;
    if ($nGroesse > 0) {
        $nMaxBreite = $nBreite * ($nGroesse / 100);
        $nMaxHoehe  = $nHoehe * ($nGroesse / 100);

        $nOffX = $nOverlayBreite / $nMaxBreite;
        $nOffY = $nOverlayHoehe / $nMaxHoehe;
    }

    if ($nOffY > $nOffX) {
        $nOverlayBreite = round($nOverlayBreite * (1 / $nOffY));
        $nOverlayHoehe  = round($nOverlayHoehe * (1 / $nOffY));
    } else {
        $nOverlayBreite = round($nOverlayBreite * (1 / $nOffX));
        $nOverlayHoehe  = round($nOverlayHoehe * (1 / $nOffX));
    }

    $im = ladeOverlay($cBild, $nOverlayBreite, $nOverlayHoehe, $nTransparenz);
    speicherOverlay($im, $cFormat, $cPfad);
}

/**
 * @param array  $cFiles_arr
 * @param object $oSuchspecialoverlaySprache
 * @return bool
 */
function speicherBild($cFiles_arr, $oSuchspecialoverlaySprache)
{
    if ($cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/jpeg' ||
        $cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/pjpeg' ||
        $cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/jpg' ||
        $cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/gif' ||
        $cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/png' ||
        $cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/bmp' ||
        $cFiles_arr['cSuchspecialOverlayBild']['type'] === 'image/x-png'
    ) {
        if (empty($cFiles_arr['cSuchspecialOverlayBild']['error'])) {
            $cFormat   = mappeFileTyp($cFiles_arr['cSuchspecialOverlayBild']['type']);
            $cName     = 'kSuchspecialOverlay_' . $oSuchspecialoverlaySprache->kSprache . '_' .
                $oSuchspecialoverlaySprache->kSuchspecialOverlay . $cFormat;
            $cOriginal = $cFiles_arr['cSuchspecialOverlayBild']['tmp_name'];

            erstelleOverlay(
                $cOriginal,
                'bilder_artikel_gross_breite',
                'bilder_artikel_gross_hoehe',
                $oSuchspecialoverlaySprache->nGroesse,
                $oSuchspecialoverlaySprache->nTransparenz,
                $cFormat,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_GROSS . $cName
            );
            erstelleOverlay(
                $cOriginal,
                'bilder_artikel_normal_breite',
                'bilder_artikel_normal_hoehe',
                $oSuchspecialoverlaySprache->nGroesse,
                $oSuchspecialoverlaySprache->nTransparenz,
                $cFormat,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_NORMAL . $cName
            );
            erstelleOverlay(
                $cOriginal,
                'bilder_artikel_klein_breite',
                'bilder_artikel_klein_hoehe',
                $oSuchspecialoverlaySprache->nGroesse,
                $oSuchspecialoverlaySprache->nTransparenz,
                $cFormat,
                PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY_KLEIN . $cName
            );

            return true;
        }
    }

    return false;
}

/**
 * @param object $oSuchspecialoverlaySprache
 */
function loescheBild($oSuchspecialoverlaySprache)
{
    if (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.jpg')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.jpg');
    } elseif (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.png')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.png');
    } elseif (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.gif')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.gif');
    } elseif (file_exists(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
        $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.bmp')) {
        @unlink(PFAD_ROOT . PFAD_SUCHSPECIALOVERLAY . 'kSuchspecialOverlay_' .
            $oSuchspecialoverlaySprache->kSprache . '_' . $oSuchspecialoverlaySprache->kSuchspecialOverlay . '.bmp');
    }
}

/**
 * @param string $cTyp
 * @return string
 */
function mappeFileTyp($cTyp)
{
    switch ($cTyp) {
        case 'image/jpeg':
            return '.jpg';
            break;
        case 'image/pjpeg':
            return '.jpg';
            break;
        case 'image/gif':
            return '.gif';
            break;
        case 'image/png':
            return '.png';
            break;
        case 'image/bmp':
            return '.bmp';
            break;
        // Adding MIME types that Internet Explorer returns
        case 'image/x-png':
            return '.png';
            break;
        case 'image/jpg':
            return '.jpg';
            break;
        //default jpg
        default:
            return '.jpg';
            break;
    }
}
