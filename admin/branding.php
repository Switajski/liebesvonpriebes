<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_BRANDING_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'branding_uebersicht';

if (verifyGPCDataInteger('branding') === 1) {
    $step = 'branding_detail';
    if (isset($_POST['speicher_einstellung']) && (int)$_POST['speicher_einstellung'] === 1 && validateToken()) {
        if (speicherEinstellung(verifyGPCDataInteger('kBranding'), $_POST, $_FILES)) {
            $cHinweis .= 'Ihre Einstellung wurde erfolgreich gespeichert.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte f&uuml;llen Sie alle Felder komplett aus.<br />';
        }
    }
    // Hole bestimmtes branding
    if (verifyGPCDataInteger('kBranding') > 0) {
        $smarty->assign('oBranding', gibBranding(verifyGPCDataInteger('kBranding')));
    }
} else {
    $smarty->assign('oBranding', gibBranding(1));
}

$smarty->assign('cRnd', time())
       ->assign('oBranding_arr', gibBrandings())
       ->assign('PFAD_BRANDINGBILDER', PFAD_BRANDINGBILDER)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('branding.tpl');

/**
 * @return mixed
 */
function gibBrandings()
{
    return Shop::DB()->selectAll('tbranding', [], [], '*', 'cBildKategorie');
}

/**
 * @param int $kBranding
 * @return mixed
 */
function gibBranding($kBranding)
{
    return Shop::DB()->query(
        "SELECT tbranding.*, tbranding.kBranding AS kBrandingTMP, tbrandingeinstellung.*
            FROM tbranding
            LEFT JOIN tbrandingeinstellung 
                ON tbrandingeinstellung.kBranding = tbranding.kBranding
            WHERE tbranding.kBranding = " . (int)$kBranding . "
            GROUP BY tbranding.kBranding", 1
    );
}

/**
 * @param int   $kBranding
 * @param array $cPost_arr
 * @param array $cFiles_arr
 * @return bool
 */
function speicherEinstellung($kBranding, $cPost_arr, $cFiles_arr)
{
    $kBranding                          = (int)$kBranding;
    $oBrandingEinstellung               = new stdClass();
    $oBrandingEinstellung->kBranding    = $kBranding;
    $oBrandingEinstellung->cPosition    = $cPost_arr['cPosition'];
    $oBrandingEinstellung->nAktiv       = $cPost_arr['nAktiv'];
    $oBrandingEinstellung->dTransparenz = $cPost_arr['dTransparenz'];
    $oBrandingEinstellung->dGroesse     = $cPost_arr['dGroesse'];

    if (strlen($cFiles_arr['cBrandingBild']['name']) > 0) {
        $oBrandingEinstellung->cBrandingBild = 'kBranding_' . $kBranding .
            mappeFileTyp($cFiles_arr['cBrandingBild']['type']);
    } else {
        $oBrandingEinstellungTMP             = Shop::DB()->select('tbrandingeinstellung', 'kBranding', $kBranding);
        $oBrandingEinstellung->cBrandingBild = (!empty($oBrandingEinstellungTMP->cBrandingBild))
            ? $oBrandingEinstellungTMP->cBrandingBild
            : '';
    }

    if ($oBrandingEinstellung->kBranding > 0 &&
        strlen($oBrandingEinstellung->cPosition) > 0 &&
        strlen($oBrandingEinstellung->cBrandingBild) > 0) {
        // Alte Einstellung loeschen
        Shop::DB()->delete('tbrandingeinstellung', 'kBranding', $kBranding);

        if (strlen($cFiles_arr['cBrandingBild']['name']) > 0) {
            loescheBrandingBild($oBrandingEinstellung->kBranding);
            speicherBrandingBild($cFiles_arr, $oBrandingEinstellung->kBranding);
        }

        Shop::DB()->insert('tbrandingeinstellung', $oBrandingEinstellung);
        MediaImage::clearCache('product');

        return true;
    }

    return false;
}

/**
 * @param array $cFiles_arr
 * @param int   $kBranding
 * @return bool
 * @todo: make size (2097152) configurable?
 */
function speicherBrandingBild($cFiles_arr, $kBranding)
{
    if ($cFiles_arr['cBrandingBild']['type'] === 'image/jpeg' ||
        $cFiles_arr['cBrandingBild']['type'] === 'image/pjpeg' ||
        $cFiles_arr['cBrandingBild']['type'] === 'image/gif' ||
        $cFiles_arr['cBrandingBild']['type'] === 'image/png' ||
        $cFiles_arr['cBrandingBild']['type'] === 'image/bmp') {
        if ($cFiles_arr['cBrandingBild']['size'] <= 2097152) {
            $cUploadDatei = PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' .
                $kBranding . mappeFileTyp($cFiles_arr['cBrandingBild']['type']);

            return move_uploaded_file($cFiles_arr['cBrandingBild']['tmp_name'], $cUploadDatei);
        }
    }

    return false;
}

/**
 * @param int $kBranding
 */
function loescheBrandingBild($kBranding)
{
    if (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.jpg')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.jpg');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.png')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.png');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.gif')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.gif');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.bmp')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $kBranding . '.bmp');
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
        case 'image/pjpeg':
            return '.jpg';
        case 'image/gif':
            return '.gif';
        case 'image/png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        default:
            return '.jpg';
    }
}
