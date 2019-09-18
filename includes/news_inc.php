<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $bActiveOnly
 * @return stdClass
 */
function baueFilterSQL($bActiveOnly = false)
{
    $oSQL              = new stdClass();
    $oSQL->cSortSQL    = '';
    $oSQL->cDatumSQL   = '';
    $oSQL->cNewsKatSQL = '';
    // Sortierung Filter
    if ($_SESSION['NewsNaviFilter']->nSort > 0) {
        switch ($_SESSION['NewsNaviFilter']->nSort) {
            case 1: // Datum absteigend
                $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
                break;
            case 2: // Datum aufsteigend
                $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon';
                break;
            case 3: // Name a ... z
                $oSQL->cSortSQL = ' ORDER BY tnews.cBetreff';
                break;
            case 4: // Name z ... a
                $oSQL->cSortSQL = ' ORDER BY tnews.cBetreff DESC';
                break;
            case 5: // Anzahl Kommentare absteigend
                $oSQL->cSortSQL = ' ORDER BY nNewsKommentarAnzahl DESC';
                break;
            case 6: // Anzahl Kommentare aufsteigend
                $oSQL->cSortSQL = ' ORDER BY nNewsKommentarAnzahl';
                break;
        }
    } elseif ($_SESSION['NewsNaviFilter']->nSort == -1) {
        // Standard
        $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
    }
    // Datum Filter
    $oSQL->cDatumSQL = '';
    if ($_SESSION['NewsNaviFilter']->cDatum != -1 && strlen($_SESSION['NewsNaviFilter']->cDatum) > 0) {
        $_date = explode('-', $_SESSION['NewsNaviFilter']->cDatum);
        if (count($_date) > 1) {
            list($nMonat, $nJahr) = $_date;
            $oSQL->cDatumSQL      = " AND MONTH(tnews.dGueltigVon) = '" . (int)$nMonat . "' 
                                      AND YEAR(tnews.dGueltigVon) = '" . (int)$nJahr . "'";
        } else { //invalid date given/xss -> reset to -1
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        }
    }
    // NewsKat Filter
    $oSQL->cNewsKatSQL = ' JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews';
    if ($_SESSION['NewsNaviFilter']->nNewsKat > 0) {
        $oSQL->cNewsKatSQL = " JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews
                               AND tnewskategorienews.kNewsKategorie = " . (int)$_SESSION['NewsNaviFilter']->nNewsKat;
    }

    if ($bActiveOnly) {
        $oSQL->cNewsKatSQL .= ' JOIN tnewskategorie 
                                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                                    AND tnewskategorie.nAktiv = 1';
    }

    return $oSQL;
}

/**
 * Prüft ob eine Kunde bereits einen Kommentar zu einer News geschrieben hat.
 * Falls Ja => return false
 * Falls Nein => return true
 *
 * @param string $cKommentar
 * @param string $cName
 * @param string $cEmail
 * @param int    $kNews
 * @param array  $Einstellungen
 * @return array
 */
function pruefeKundenKommentar($cKommentar, $cName = '', $cEmail = '', $kNews, $Einstellungen)
{
    $nPlausiValue_arr = [];
    $conf             = Shop::getSettings([CONF_NEWS]);
    // Kommentar prüfen
    if (strlen($cKommentar) === 0) {
        $nPlausiValue_arr['cKommentar'] = 1;
    }
    if (strlen($cKommentar) > 1000) {
        $nPlausiValue_arr['cKommentar'] = 2;
    }
    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && $kNews > 0) {
        // Kunde ist eingeloggt
        $oNewsKommentar = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tnewskommentar
                WHERE kNews = " . (int)$kNews . "
                    AND kKunde = " . (int)$_SESSION['Kunde']->kKunde, 1
        );

        if ($oNewsKommentar->nAnzahl > (int)$Einstellungen['news']['news_kommentare_anzahlprobesucher'] &&
            (int)$Einstellungen['news']['news_kommentare_anzahlprobesucher'] !== 0) {
            $nPlausiValue_arr['nAnzahl'] = 1;
        }

        $cEmail = $_SESSION['Kunde']->cMail;
    } else {
        // Kunde ist nicht eingeloggt - Name prüfen
        if (strlen($cName) === 0) {
            $nPlausiValue_arr['cName'] = 1;
        }
        // Email prüfen
        if (!valid_email($cEmail)) {
            $nPlausiValue_arr['cEmail'] = 1;
        }
        if (isset($conf['news']['news_sicherheitscode']) &&
            $conf['news']['news_sicherheitscode'] !== 'N' &&
            !validateCaptcha($_POST)
        ) {
            $nPlausiValue_arr['captcha'] = 2;
        }
    }
    if ((!isset($nPlausiValue_arr['cName']) || !$nPlausiValue_arr['cName']) && pruefeEmailblacklist($cEmail)) {
        $nPlausiValue_arr['cEmail'] = 2;
    }

    return $nPlausiValue_arr;
}

/**
 * @param array $nPlausiValue_arr
 * @return string
 */
function gibNewskommentarFehler($nPlausiValue_arr)
{
    $cFehler = '';
    if (isset($nPlausiValue_arr['cKommentar'])) {
        // Kommentarfeld ist leer
        if ($nPlausiValue_arr['cKommentar'] == 1) {
            $cFehler .= Shop::Lang()->get('newscommentMissingtext', 'errorMessages') . '<br />';
        } elseif ($nPlausiValue_arr['cKommentar'] == 2) {
            // Kommentar ist länger als 1000 Zeichen
            $cFehler .= Shop::Lang()->get('newscommentLongtext', 'errorMessages') . '<br />';
        }
    }
    // Kunde hat bereits einen Newskommentar zu der aktuellen News geschrieben
    if (isset($nPlausiValue_arr['nAnzahl']) && $nPlausiValue_arr['nAnzahl'] == 1) {
        $cFehler .= Shop::Lang()->get('newscommentAlreadywritten', 'errorMessages') . '<br />';
    }
    // Kunde ist nicht eingeloggt und das Feld Name oder Email ist leer
    if ((isset($nPlausiValue_arr['cName']) && $nPlausiValue_arr['cName'] == 1) ||
        (isset($nPlausiValue_arr['cEmail']) && $nPlausiValue_arr['cEmail'] == 1)
    ) {
        $cFehler .= Shop::Lang()->get('newscommentMissingnameemail', 'errorMessages') . '<br />';
    }
    // Emailadresse ist auf der Blacklist
    if (isset($nPlausiValue_arr['cEmail']) && $nPlausiValue_arr['cEmail'] == 2) {
        $cFehler .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
    }

    return $cFehler;
}

/**
 * @param string $cDatumSQL
 * @param bool   $bActiveOnly
 * @return mixed
 */
function holeNewsKategorien($cDatumSQL, $bActiveOnly = false)
{
    $kSprache     = (int)$_SESSION['kSprache'];
    $cSQL         = '';
    $activeFilter = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';
    if (strlen($cDatumSQL) > 0) {
        $cSQL = "   JOIN tnewskategorienews ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    JOIN tnews ON tnews.kNews = tnewskategorienews.kNews
                    " . $cDatumSQL;
    }

    return Shop::DB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, 
            tnewskategorie.cPreviewImage, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y  %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            " . $cSQL . "
            LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . $kSprache . "
                AND tnewskategorie.kSprache = " . $kSprache . "
            WHERE tnewskategorie.kSprache = " . $kSprache
            . $activeFilter . "
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort", 2
    );
}

/**
 * @param array $oDatum_arr
 * @return array
 */
function baueDatum($oDatum_arr)
{
    $oDatumTMP_arr = [];
    if (is_array($oDatum_arr) && count($oDatum_arr) > 0) {
        foreach ($oDatum_arr as $oDatum) {
            $oTMP            = new stdClass();
            $oTMP->cWert     = $oDatum->nMonat . '-' . $oDatum->nJahr;
            $oTMP->cName     = mappeDatumName((string)$oDatum->nMonat, (int)$oDatum->nJahr, $_SESSION['cISOSprache']);
            $oDatumTMP_arr[] = $oTMP;
        }
    }

    return $oDatumTMP_arr;
}

/**
 * @param string $cMonat
 * @param string $nJahr
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
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaTitle($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    $cMetaTitle = baueNewsMetaStart($oNewsNaviFilter);
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        $nCount = 3;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaTitle .= ' - ' . $oNewsUebersicht_arr[$i]->cBetreff;
            } else {
                $cMetaTitle .= $oNewsUebersicht_arr[$i]->cBetreff;
            }
        }
    }

    return $cMetaTitle;
}

/**
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaDescription($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    $cMetaDescription = baueNewsMetaStart($oNewsNaviFilter);
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        shuffle($oNewsUebersicht_arr);
        $nCount = 12;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaDescription .= ' - ' . $oNewsUebersicht_arr[$i]->cBetreff;
            } else {
                $cMetaDescription .= $oNewsUebersicht_arr[$i]->cBetreff;
            }
        }
    }

    return $cMetaDescription;
}

/**
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 */
function baueNewsMetaKeywords($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    $cMetaKeywords = '';
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        $nCount = 6;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaKeywords .= ', ' . $oNewsUebersicht_arr[$i]->cMetaKeywords;
            } else {
                $cMetaKeywords .= $oNewsUebersicht_arr[$i]->cMetaKeywords;
            }
        }
    }

    return $cMetaKeywords;
}

/**
 * @param object $oNewsNaviFilter
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaStart($oNewsNaviFilter)
{
    $cMetaStart = Shop::Lang()->get('overview', 'news');
    // Datumfilter gesetzt
    if ($oNewsNaviFilter->cDatum != -1) {
        $cMetaStart .= ' ' . $oNewsNaviFilter->cDatum;
    }
    // Kategoriefilter gesetzt
    if ($oNewsNaviFilter->nNewsKat != -1) {
        $oNewsKat = Shop::DB()->select(
            'tnewskategorie',
            'kNewsKategorie',
            (int)$oNewsNaviFilter->nNewsKat,
            'kSprache',
            (int)$_SESSION['kSprache']
        );
        if (isset($oNewsKat->kNewsKategorie) && $oNewsKat->kNewsKategorie > 0) {
            $cMetaStart .= ' ' . $oNewsKat->cName;
        }
    }

    return $cMetaStart . ': ';
}

/**
 * @param JTLSmarty   $smarty
 * @param string|null $AktuelleSeite
 * @param string      $cCanonicalURL
 */
function baueNewsKruemel($smarty, $AktuelleSeite, &$cCanonicalURL)
{
    $oLink = Shop::DB()->select('tlink', 'nLinkart', LINKTYP_NEWS);
    if (isset($oLink->kLink) && $oLink->kLink > 0) {
        //hole Link
        $linkHelper    = LinkHelper::getInstance();
        $Link          = $linkHelper->getPageLink($oLink->kLink);
        $Link->Sprache = $linkHelper->getPageLinkLanguage($oLink->kLink);
        //url
        global $sprachURL, $requestURL;
        $requestURL = baueURL($Link, URLART_SEITE);
        $sprachURL  = isset($Link->languageURLs)
            ? $Link->languageURLs
            : baueSprachURLS($Link, URLART_SEITE);
        // Canonical
        if (strpos($requestURL, '.php') === false) {
            $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
        }
        if (empty($AktuelleSeite)) {
            $AktuelleSeite = null;
        }
        $smarty->assign('Navigation', createNavigation($AktuelleSeite, 0, 0, $Link->Sprache->cName, $requestURL));
    } else {
        // Canonical
        $cCanonicalURL = Shop::getURL() . '/news.php';
        $smarty->assign('Navigation',
            createNavigation(
                $AktuelleSeite,
                0,
                0,
                Shop::Lang()->get('news', 'breadcrumb'),
                'news.php'
            )
        );
    }
}

/**
 * @param int  $kNews
 * @param bool $bActiveOnly
 * @return mixed
 */
function getNewsArchive($kNews, $bActiveOnly = false)
{
    $activeFilter = $bActiveOnly ? ' AND tnews.nAktiv = 1 ' : '';

    return Shop::DB()->query("
        SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
            tnews.cVorschauText, tnews.cPreviewImage, tnews.cMetaTitle, tnews.cMetaDescription, 
            tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tnews.dGueltigVon, tseo.cSeo,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS Datum, 
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnews.kNews = " . (int)$kNews . " 
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND tnews.kSprache = " . (int)$_SESSION['kSprache']
                . $activeFilter, 1
    );
}

/**
 * @param int  $kNewsKategorie
 * @param bool $bActiveOnly
 * @return mixed
 */
function getCurrentNewsCategory($kNewsKategorie, $bActiveOnly = false)
{
    $activeFilter = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';

    return Shop::DB()->query(
        "SELECT tnewskategorie.cName, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription, tseo.cSeo
            FROM tnewskategorie
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = " . (int)$kNewsKategorie . "
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnewskategorie.kNewsKategorie = " . (int)$kNewsKategorie
                . $activeFilter, 1
    );
}

/**
 * @param int $kNews
 * @return mixed
 */
function getNewsCategory($kNews)
{
    $cSQL                  = '';
    $oNewsKategorieKey_arr = Shop::DB()->selectAll('tnewskategorienews', 'kNews', (int)$kNews, 'kNewsKategorie');

    if (is_array($oNewsKategorieKey_arr) && count($oNewsKategorieKey_arr) > 0) {
        $cSQL = '';
        foreach ($oNewsKategorieKey_arr as $i => $oNewsKategorieKey) {
            if ($oNewsKategorieKey->kNewsKategorie > 0) {
                if ($i > 0) {
                    $cSQL .= ', ' . (int)$oNewsKategorieKey->kNewsKategorie;
                } else {
                    $cSQL .= (int)$oNewsKategorieKey->kNewsKategorie;
                }
            }
        }
    }

    return Shop::DB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
            tnewskategorie.cPreviewImage, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            LEFT JOIN tnewskategorienews 
                ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnewskategorie.kSprache = " . (int)$_SESSION['kSprache'] . "
                AND tnewskategorienews.kNewsKategorie IN (" . $cSQL . ")
                AND tnewskategorie.nAktiv = 1
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort DESC", 2
    );
}

/**
 * @param int    $kNews
 * @param string $cLimitSQL
 * @return mixed
 */
function getNewsComments($kNews, $cLimitSQL)
{
    return Shop::DB()->query(
        "SELECT *, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
            FROM tnewskommentar
            WHERE tnewskommentar.kNews = " . (int)$kNews . "
                AND tnewskommentar.nAktiv = 1
            ORDER BY tnewskommentar.dErstellt DESC
            LIMIT " . $cLimitSQL, 2
    );
}

/**
 * @param int $kNews
 * @return mixed
 */
function getCommentCount($kNews)
{
    return Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewskommentar
            WHERE kNews = " . (int)$kNews . "
            AND nAktiv = 1", 1
    );
}

/**
 * @param int $kNewsMonatsUebersicht
 * @return mixed
 */
function getMonthOverview($kNewsMonatsUebersicht)
{
    return Shop::DB()->query(
        "SELECT tnewsmonatsuebersicht.*, tseo.cSeo
            FROM tnewsmonatsuebersicht
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsMonatsUebersicht'
                AND tseo.kKey = " . (int)$kNewsMonatsUebersicht . "
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = " . (int)$kNewsMonatsUebersicht, 1
    );
}

/**
 * @param object $oSQL
 * @param string $cLimitSQL
 * @return mixed
 */
function getNewsOverview($oSQL, $cLimitSQL)
{
    return Shop::DB()->query(
        "SELECT tseo.cSeo, tnews.*, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dErstellt_de, 
            count(*) AS nAnzahl, count(DISTINCT(tnewskommentar.kNewsKommentar)) AS nNewsKommentarAnzahl
            FROM tnews
            LEFT JOIN tseo ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            LEFT JOIN tnewskommentar 
                ON tnewskommentar.kNews = tnews.kNews 
                AND tnewskommentar.nAktiv = 1
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
                " . $oSQL->cDatumSQL . "
            GROUP BY tnews.kNews
            " . $oSQL->cSortSQL . "
            LIMIT " . $cLimitSQL, 2
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getFullNewsOverview($oSQL)
{
    return Shop::DB()->query(
        "SELECT count(DISTINCT(tnews.kNews)) AS nAnzahl
            FROM tnews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                " . $oSQL->cDatumSQL . "
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'], 1
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getNewsDateArray($oSQL)
{
    return Shop::DB()->query(
      "SELECT month(tnews.dGueltigVon) AS nMonat, year(tnews.dGueltigVon) AS nJahr
            FROM tnews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
            GROUP BY nJahr, nMonat
            ORDER BY dGueltigVon DESC", 2
    );
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
                    $oDatei->cURL   = PFAD_NEWSBILDER . $kNews . '/' . $Datei;
                    $oDatei->cDatei = $Datei;

                    $oDatei_arr[] = $oDatei;
                }
            }

            usort($oDatei_arr, 'cmp_obj');
        }
    }

    return $oDatei_arr;
}
