<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cBetreff
 * @param string $cText
 * @param array  $kKundengruppe_arr
 * @param array  $kNewsKategorie_arr
 * @return array
 */
function pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr)
{
    $cPlausiValue_arr = [];
    // Betreff prüfen
    if (strlen($cBetreff) === 0) {
        $cPlausiValue_arr['cBetreff'] = 1;
    }
    // Text prüfen
    if (strlen($cText) === 0) {
        $cPlausiValue_arr['cText'] = 1;
    }
    // Kundengruppe prüfen
    if (!is_array($kKundengruppe_arr) || count($kKundengruppe_arr) === 0) {
        $cPlausiValue_arr['kKundengruppe_arr'] = 1;
    }
    // Newskategorie prüfen
    if (!is_array($kNewsKategorie_arr) || count($kNewsKategorie_arr) === 0) {
        $cPlausiValue_arr['kNewsKategorie_arr'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * @param string $cName
 * @param int    $nNewskategorieEditSpeichern
 * @return array
 */
function pruefeNewsKategorie($cName, $nNewskategorieEditSpeichern = 0)
{
    $cPlausiValue_arr = [];
    // Name prüfen
    if (strlen($cName) === 0) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Prüfen ob Name schon vergeben
    if ($nNewskategorieEditSpeichern == 0) {
        $oNewsKategorieTMP = Shop::DB()->select('tnewskategorie', 'cName', $cName);
        if (isset($oNewsKategorieTMP->kNewsKategorie) && $oNewsKategorieTMP->kNewsKategorie > 0) {
            $cPlausiValue_arr['cName'] = 2;
        }
    }

    return $cPlausiValue_arr;
}

/**
 * @deprecated since 4.06
 *
 * @param string $string
 * @return string
 */
function convertDate($string)
{
    list($dDatum, $dZeit) = explode(' ', $string);
    if (substr_count(':', $dZeit) === 2 ) {
        list($nStunde, $nMinute) = explode(':', $dZeit);
    } else {
        list($nStunde, $nMinute, $nSekunde) = explode(':', $dZeit);
    }
    list($nTag, $nMonat, $nJahr) = explode('.', $dDatum);

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $nStunde . ':' . $nMinute . ':00';
}

/**
 * @param int $kNews
 * @return int|string
 */
function gibLetzteBildNummer($kNews)
{
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_NEWSBILDER;

    $cBild_arr = [];
    if (is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $cBild_arr[] = $Datei;
            }
        }
    }
    $nMax       = 0;
    $imageCount = count($cBild_arr);
    if ($imageCount > 0) {
        for ($i = 0; $i < $imageCount; $i++) {
            $cNummer = substr($cBild_arr[$i], 4, (strlen($cBild_arr[$i]) - strpos($cBild_arr[$i], '.')) - 3);

            if ($cNummer > $nMax) {
                $nMax = $cNummer;
            }
        }
    }

    return $nMax;
}

/**
 * @param string $a
 * @param string $b
 * @return int
 */
function cmp($a, $b)
{
    return strcmp($a, $b);
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function cmp_obj($a, $b)
{
    return strcmp($a->cName, $b->cName);
}

/**
 * @param string $cMonat
 * @param int    $nJahr
 * @param string $cISOSprache
 * @return string
 */
function mappeDatumName($cMonat, $nJahr, $cISOSprache)
{
    $cName = '';

    if ($cISOSprache === 'ger') {
        switch ($cMonat) {
            case '01':
                $cName .= Shop::Lang()->get('january', 'news') . ', ' . $nJahr;
                break;
            case '02':
                $cName .= Shop::Lang()->get('february', 'news') . ', ' . $nJahr;
                break;
            case '03':
                $cName .= Shop::Lang()->get('march', 'news') . ', ' . $nJahr;
                break;
            case '04':
                $cName .= Shop::Lang()->get('april', 'news') . ', ' . $nJahr;
                break;
            case '05':
                $cName .= Shop::Lang()->get('may', 'news') . ', ' . $nJahr;
                break;
            case '06':
                $cName .= Shop::Lang()->get('june', 'news') . ', ' . $nJahr;
                break;
            case '07':
                $cName .= Shop::Lang()->get('july', 'news') . ', ' . $nJahr;
                break;
            case '08':
                $cName .= Shop::Lang()->get('august', 'news') . ', ' . $nJahr;
                break;
            case '09':
                $cName .= Shop::Lang()->get('september', 'news') . ', ' . $nJahr;
                break;
            case '10':
                $cName .= Shop::Lang()->get('october', 'news') . ', ' . $nJahr;
                break;
            case '11':
                $cName .= Shop::Lang()->get('november', 'news') . ', ' . $nJahr;
                break;
            case '12':
                $cName .= Shop::Lang()->get('december', 'news') . ', ' . $nJahr;
                break;
        }
    } else {
        $cName .= date('F', mktime(0, 0, 0, (int)$cMonat, 1, $nJahr)) . ', ' . $nJahr;
    }

    return $cName;
}

/**
 * @deprecated since 4.06
 *
 * @param string $cDateTimeStr
 * @return stdClass
 */
function gibJahrMonatVonDateTime($cDateTimeStr)
{
    list($dDatum, $dUhrzeit)     = explode(' ', $cDateTimeStr);
    list($dJahr, $dMonat, $dTag) = explode('-', $dDatum);
    $oDatum                      = new stdClass();
    $oDatum->Jahr                = (int)$dJahr;
    $oDatum->Monat               = (int)$dMonat;
    $oDatum->Tag                 = (int)$dTag;

    return $oDatum;
}

/**
 * @param int   $kNewsKommentar
 * @param array $cPost_arr
 * @return bool
 */
function speicherNewsKommentar($kNewsKommentar, $cPost_arr)
{
    if ($kNewsKommentar > 0) {
        $upd             = new stdClass();
        $upd->cName      = $cPost_arr['cName'];
        $upd->cKommentar = $cPost_arr['cKommentar'];

        return Shop::DB()->update('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar, $upd) >= 0;
    }

    return false;
}

/**
 * Gibt eine neue Breite und Höhe als Array zurück
 *
 * @param string $cDatei
 * @param int    $nMaxBreite
 * @param int    $nMaxHoehe
 * @return array
 */
function calcRatio($cDatei, $nMaxBreite, $nMaxHoehe)
{
    $path = str_replace(Shop::getURL(), PFAD_ROOT, $cDatei);
    if (file_exists($path)) {
        $cDatei = $path;
    }
    list($ImageBreite, $ImageHoehe) = getimagesize($cDatei);
    if ($ImageBreite === null || $ImageBreite === 0) {
        $ImageBreite = 1;
    }
    if ($ImageHoehe === null || $ImageHoehe === 0) {
        $ImageHoehe = 1;
    }
    $f = min($nMaxBreite / $ImageBreite, $nMaxHoehe / $ImageHoehe, 1);

    return [round($f * $nMaxBreite), round($f * $nMaxHoehe)];
}

/**
 * @param  int    $kSprache
 * @param  string $cLimitSQL
 * @return mixed
 */
function holeNewskategorie($kSprache = null, $cLimitSQL = '')
{
    if (!isset($kSprache)) {
        $kSprache = $_SESSION['kSprache'];
    }
    $kSprache = (int)$kSprache;

    return Shop::DB()->query(
        "SELECT" . (!empty($cLimitSQL) ? " SQL_CALC_FOUND_ROWS" : '') . 
            " *, DATE_FORMAT(dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            WHERE kSprache = " . $kSprache . "
            ORDER BY nSort DESC" . (!empty($cLimitSQL) ? " " . $cLimitSQL : ''), 2
    );
}

/**
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return array
 */
function holeNewsBilder($kNews, $cUploadVerzeichnis)
{
    $oDatei_arr = [];
    $kNews      = (int)$kNews;
    if ($kNews > 0) {
        if (is_dir($cUploadVerzeichnis . $kNews)) {
            $DirHandle = opendir($cUploadVerzeichnis . $kNews);
            $shopURL   = Shop::getURL() . '/';
            while (false !== ($Datei = readdir($DirHandle))) {
                if ($Datei !== '.' && $Datei !== '..') {
                    $oDatei         = new stdClass();
                    $oDatei->cName  = substr($Datei, 0, strpos($Datei, '.'));
                    $oDatei->cURL   = '<img src="' . $shopURL . PFAD_NEWSBILDER . $kNews . '/' . $Datei . '" />';
                    $oDatei->cDatei = $Datei;

                    $oDatei_arr[] = $oDatei;
                }
            }

            usort($oDatei_arr, 'cmp_obj');
        }
    }

    return $oDatei_arr;
}

/**
 * @param int    $kNewsKategorie
 * @param string $cUploadVerzeichnis
 * @return array
 */
function holeNewsKategorieBilder($kNewsKategorie, $cUploadVerzeichnis)
{
    $oDatei_arr = [];
    $kNewsKategorie      = (int)$kNewsKategorie;
    if ($kNewsKategorie > 0) {
        if (is_dir($cUploadVerzeichnis . $kNewsKategorie)) {
            $DirHandle = opendir($cUploadVerzeichnis . $kNewsKategorie);
            $shopURL   = Shop::getURL() . '/';
            while (false !== ($Datei = readdir($DirHandle))) {
                if ($Datei !== '.' && $Datei !== '..') {
                    $oDatei         = new stdClass();
                    $oDatei->cName  = substr($Datei, 0, strpos($Datei, '.'));
                    $oDatei->cURL   = '<img src="' . $shopURL . PFAD_NEWSKATEGORIEBILDER . $kNewsKategorie . '/' . $Datei . '" />';
                    $oDatei->cDatei = $Datei;

                    $oDatei_arr[] = $oDatei;
                }
            }

            usort($oDatei_arr, 'cmp_obj');
        }
    }

    return $oDatei_arr;
}

/**
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return bool
 */
function loescheNewsBilderDir($kNews, $cUploadVerzeichnis)
{
    if (is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                unlink($cUploadVerzeichnis . $kNews . '/' . $Datei);
            }
        }
        rmdir($cUploadVerzeichnis . $kNews);

        return true;
    }

    return false;
}

/**
 * @param array $kNewsKategorie_arr
 * @return bool
 */
function loescheNewsKategorie($kNewsKategorie_arr)
{
    if (is_array($kNewsKategorie_arr) && count($kNewsKategorie_arr) > 0) {
        foreach ($kNewsKategorie_arr as $kNewsKategorie) {
            $kNewsKategorie = (int)$kNewsKategorie;
            Shop::DB()->delete('tnewskategorie', 'kNewsKategorie', $kNewsKategorie);
            // tseo löschen
            Shop::DB()->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $kNewsKategorie]);
            // tnewskategorienews löschen
            Shop::DB()->delete('tnewskategorienews', 'kNewsKategorie', $kNewsKategorie);
        }

        return true;
    }

    return false;
}

/**
 * @param int $kNewsKategorie
 * @param int $kSprache
 * @return stdClass
 */
function editiereNewskategorie($kNewsKategorie, $kSprache)
{
    $oNewsKategorie = new stdClass();
    $kNewsKategorie = (int)$kNewsKategorie;
    $kSprache       = (int)$kSprache;
    if ($kNewsKategorie > 0 && $kSprache > 0) {
        $oNewsKategorie = Shop::DB()->query(
            "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
                tnewskategorie.cPreviewImage, tseo.cSeo,
                DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
                FROM tnewskategorie
                LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = " . $kSprache . "
                WHERE kNewsKategorie = " . $kNewsKategorie, 1
        );
    }

    return $oNewsKategorie;
}

/**
 * @param string $cText
 * @param int    $kNews
 * @return mixed
 */
function parseText($cText, $kNews)
{
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_NEWSBILDER;
    $cBild_arr          = [];
    if (is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $cBild_arr[] = $Datei;
            }
        }

        closedir($DirHandle);
    }
    usort($cBild_arr, 'cmp');

    $shopURL = Shop::getURL() . '/';
    $count   = count($cBild_arr);
    for ($i = 1; $i <= $count; $i++) {
        $cText = str_replace("$#Bild" . $i . "#$", '<img alt="" src="' . 
            $shopURL . PFAD_NEWSBILDER . $kNews . '/' . $cBild_arr[$i - 1] . 
            '" />', $cText);
    }
    if (strpos(end($cBild_arr), 'preview') !== false) {
        $cText = str_replace("$#preview#$", '<img alt="" src="' . 
            $shopURL . PFAD_NEWSBILDER . $kNews . '/' . $cBild_arr[count($cBild_arr) - 1] . 
            '" />', $cText);
    }

    return $cText;
}

/**
 * @param string $cBildname
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return bool
 */
function loescheNewsBild($cBildname, $kNews, $cUploadVerzeichnis)
{
    if ((int)$kNews > 0 && strlen($cBildname) > 0 &&
        is_dir($cUploadVerzeichnis) &&
        is_dir($cUploadVerzeichnis . $kNews)
    ) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..' && substr($Datei, 0, strpos($Datei, '.')) === $cBildname) {
                unlink($cUploadVerzeichnis . $kNews . '/' . $Datei);
                closedir($DirHandle);
                if ($cBildname === 'preview') {
                    $upd                = new stdClass();
                    $upd->cPreviewImage = '';
                    if (strpos($cUploadVerzeichnis, PFAD_NEWSKATEGORIEBILDER) === false){
                        Shop::DB()->update('tnews', 'kNews', $kNews, $upd);
                    } else {
                        Shop::DB()->update('tnewskategorie', 'kNewsKategorie', $kNews, $upd);
                    }
                }

                return true;
            }
        }
    }

    return false;
}

/**
 * @param string $cTab
 * @param string $cHinweis
 * @param array  $urlParams
 * @return bool
 */
function newsRedirect($cTab = '', $cHinweis = '', $urlParams = null)
{
    $tabPageMapping = [
        'inaktiv'    => 's1',
        'aktiv'      => 's2',
        'kategorien' => 's3',
    ];
    if (empty($cHinweis)) {
        unset($_SESSION['news.cHinweis']);
    } else {
        $_SESSION['news.cHinweis'] = $cHinweis;
    }

    if (!empty($cTab)) {
        if (!is_array($urlParams)) {
            $urlParams = [];
        }
        $urlParams['tab'] = $cTab;
        if (isset($tabPageMapping[$cTab]) && verifyGPCDataInteger($tabPageMapping[$cTab]) > 1 && 
            !array_key_exists($tabPageMapping[$cTab], $urlParams)) {
            $urlParams[$tabPageMapping[$cTab]] = verifyGPCDataInteger($tabPageMapping[$cTab]);
        }
    }

    header('Location: news.php' . (is_array($urlParams) 
            ? '?' . http_build_query($urlParams, '', '&') 
            : ''));
    exit;
}
