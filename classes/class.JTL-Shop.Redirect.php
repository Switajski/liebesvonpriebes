<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Redirect
 */
class Redirect
{
    /**
     * @var int
     */
    public $kRedirect;

    /**
     * @var string
     */
    public $cFromUrl;

    /**
     * @var string
     */
    public $cToUrl;

    /**
     * @var string
     */
    public $cAvailable;

    /**
     * @param int $kRedirect
     */
    public function __construct($kRedirect = 0)
    {
        if ($kRedirect > 0) {
            $this->loadFromDB($kRedirect);
        }
    }

    /**
     * @param int $kRedirect
     * @return $this
     */
    public function loadFromDB($kRedirect)
    {
        $obj = Shop::DB()->select('tredirect', 'kRedirect', (int)$kRedirect);
        if (is_object($obj) && $obj->kRedirect > 0) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * @param int $kRedirect
     * @return $this
     * @deprecated since 4.06 - use Redirect::deleteRedirect() instead
     */
    public function delete($kRedirect)
    {
        self::deleteRedirect($kRedirect);

        return $this;
    }

    /**
     * @return int
     * @deprecated since 4.06 - use Redirect::deleteUnassigned() instead
     */
    public function deleteAll()
    {
        return self::deleteUnassigned();
    }

    /**
     * @param string $cUrl
     * @return null|stdClass
     */
    public function find($cUrl)
    {
        return Shop::DB()->select('tredirect', 'cFromUrl', $this->normalize($cUrl));
    }

    /**
     * Get a redirect by target
     *
     * @param string $cToUrl target to search for
     * @return null|string - null if fails, string if successful
     */
    public function getRedirectByTarget($cToUrl)
    {
        return Shop::DB()->select('tredirect', 'cToUrl', $this->normalize($cToUrl));
    }

    /**
     * @param string $cSource
     * @param string $cDestination
     * @return bool
     */
    public function isDeadlock($cSource, $cDestination)
    {
        $xPath_arr    = parse_url(Shop::getURL());
        $cDestination = isset($xPath_arr['path']) ? $xPath_arr['path'] . '/' . $cDestination : $cDestination;
        $oObj         = Shop::DB()->select('tredirect', 'cFromUrl', $cDestination, 'cToUrl', $cSource);

        return isset($oObj->kRedirect) && (int)$oObj->kRedirect > 0;
    }

    /**
     * @param string $cSource
     * @param string $cDestination
     * @param bool   $bForce
     * @return bool
     */
    public function saveExt($cSource, $cDestination, $bForce = false)
    {
        if (strlen($cSource) > 0 && $cSource[0] !== '/') {
            $cSource = '/' . $cSource;
        }

        if ($bForce || (self::checkAvailability($cDestination) &&
                strlen($cSource) > 1 &&
                strlen($cDestination) > 1 &&
                $cSource !== $cDestination)
        ) {
            if ($this->isDeadlock($cSource, $cDestination)) {
                Shop::DB()->delete('tredirect', ['cToUrl', 'cFromUrl'], [$cSource, $cDestination]);
            }
            $oTarget = $this->getRedirectByTarget($cSource);
            if (!empty($oTarget)) {
                $this->saveExt($oTarget->cFromUrl, $cDestination);
                $oObj             = new stdClass();
                $oObj->cToUrl     = StringHandler::convertISO($cDestination);
                $oObj->cAvailable = 'y';
                Shop::DB()->update('tredirect', 'cToUrl', $cSource, $oObj);
            }

            $oRedirect = $this->find($cSource);
            if (empty($oRedirect)) {
                $oObj             = new stdClass();
                $oObj->cFromUrl   = StringHandler::convertISO($cSource);
                $oObj->cToUrl     = StringHandler::convertISO($cDestination);
                $oObj->cAvailable = 'y';

                $kRedirect = Shop::DB()->insert('tredirect', $oObj);
                if ((int)$kRedirect > 0) {
                    return true;
                }
            } elseif ($this->normalize($oRedirect->cFromUrl) === $this->normalize($cSource)
                && empty($oRedirect->cToUrl)
                && (int)Shop::DB()->update(
                    'tredirect', 'cFromUrl', $this->normalize($cSource),
                    (object)['cToUrl' => StringHandler::convertISO($cDestination)]
                ) > 0
            ) {
                // the redirect already exists but has an empty cToUrl => update it
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $cFile
     * @return array
     */
    public function doImport($cFile)
    {
        $cError_arr = [];
        if (file_exists($cFile)) {
            $handle = fopen($cFile, 'r');
            if ($handle) {
                $oSprache     = gibStandardsprache(true);
                $cMapping_arr = [];
                $i            = 0;
                while (($csv = fgetcsv($handle, 30000, ';')) !== false) {
                    if ($i > 0) {
                        if ($cMapping_arr !== null) {
                            $this->import($csv, $i, $cError_arr, $cMapping_arr, $oSprache);
                        } else {
                            $cError_arr[] = 'Die Kopfzeile entspricht nicht der Konvention!';
                            break;
                        }
                    } else {
                        $cMapping_arr = $this->readHeadRow($csv);
                    }
                    $i++;
                }

                fclose($handle);
            } else {
                $cError_arr[] = 'Datei konnte nicht gelesen werden';
            }
        } else {
            $cError_arr[] = 'Datei konnte nicht gefunden werden';
        }

        return $cError_arr;
    }

    /**
     * @param string  $csv
     * @param int     $nRow
     * @param array   $cError_arr
     * @param array   $cMapping_arr
     * @param object  $oSprache
     * @return $this
     */
    protected function import($csv, $nRow, &$cError_arr, $cMapping_arr, $oSprache)
    {
        $xParse_arr = parse_url($csv[$cMapping_arr['sourceurl']]);
        $cFromUrl   = $xParse_arr['path'];
        if (isset($xParse_arr['query'])) {
            $cFromUrl .= '?' . $xParse_arr['query'];
        }
        $options = ['cFromUrl' => $cFromUrl];
        $options['cArtNr'] = null;
        if (isset($csv[$cMapping_arr['articlenumber']])) {
            $options['cArtNr'] = $csv[$cMapping_arr['articlenumber']];
        }
        $options['cToUrl'] = null;
        if (isset($csv[$cMapping_arr['destinationurl']])) {
            $options['cToUrl'] = $csv[$cMapping_arr['destinationurl']];
        }
        $options['cIso'] = $oSprache->cISO;
        if (isset($csv[$cMapping_arr['languageiso']])) {
            $options['cIso'] = $csv[$cMapping_arr['languageiso']];
        }
        if ($options['cArtNr'] === null && $options['cToUrl'] === null) {
            $cError_arr[] = "Row {$nRow}: articlenumber und destinationurl sind nicht vorhanden oder fehlerhaft";
        } elseif ($options['cArtNr'] !== null && $options['cToUrl'] !== null) {
            $cError_arr[] = "Row {$nRow}: Nur articlenumber und destinationurl darf vorhanden sein";
        } elseif ($options['cToUrl'] !== null) {
            if (!$this->saveExt($options['cFromUrl'], $options['cToUrl'])) {
                $cError_arr[] = "Row {$nRow}: Konnte nicht gespeichert werden (Vielleicht bereits vorhanden?)";
            }
        } else {
            $cUrl = $this->getArtNrUrl($options['cArtNr'], $options['cIso']);
            if ($cUrl !== null) {
                if (!$this->saveExt($options['cFromUrl'], $cUrl)) {
                    $cError_arr[] = "Row {$nRow}: Konnte nicht gespeichert werden (Vielleicht bereits vorhanden?)";
                }
            } else {
                $cError_arr[] = "Row {$nRow}: Artikelnummer ({$options['cArtNr']}) konnte nicht im Shop gefunden werden";
            }
        }

        return $this;
    }

    /**
     * @param string $cArtNr
     * @param string $cIso
     * @return null|string
     */
    public function getArtNrUrl($cArtNr, $cIso)
    {
        if (strlen($cArtNr) > 0) {
            $oObj = Shop::DB()->executeQueryPrepared(
                "SELECT tartikel.kArtikel, tseo.cSeo
                    FROM tartikel
                    LEFT JOIN tsprache 
                        ON tsprache.cISO = :iso
                    LEFT JOIN tseo 
                        ON tseo.kKey = tartikel.kArtikel
                        AND tseo.cKey = 'kArtikel'
                        AND tseo.kSprache = tsprache.kSprache
                    WHERE tartikel.cArtNr = :artnr
                    LIMIT 1",
                ['iso' => strtolower($cIso), 'artnr' => $cArtNr],
                1
            );

            return baueURL($oObj, URLART_ARTIKEL);
        }

        return null;
    }

    /**
     * Parse head row from import file
     *
     * @param array $cRow_arr
     * @return array|null
     */
    public function readHeadRow($cRow_arr)
    {
        $cMapping_arr = ['sourceurl' => null];
        // Must not be present in the file
        $cOption_arr = ['articlenumber', 'destinationurl', 'languageiso'];
        if (is_array($cRow_arr) && count($cRow_arr) > 0) {
            $cMember_arr = array_keys($cMapping_arr);
            foreach ($cRow_arr as $i => $cRow) {
                $bExist = false;
                if (in_array($cRow, $cOption_arr, true)) {
                    $cMapping_arr[$cRow] = $i;
                    $bExist              = true;
                } else {
                    foreach ($cMember_arr as $cMember) {
                        if ($cMember === $cRow) {
                            $cMapping_arr[$cMember] = $i;
                            $bExist                 = true;
                            break;
                        }
                    }
                }

                if (!$bExist) {
                    return null;
                }
            }

            return $cMapping_arr;
        }

        return null;
    }

    /**
     * @param string $cUrl
     * @return bool|string
     */
    public function checkFallbackRedirect($cUrl)
    {
        $exploded = explode('/', trim($cUrl, '/'));
        if (count($exploded) > 0) {
            $lastPath = $exploded[count($exploded) - 1];
            $filename = strtok($lastPath, '?');
            $seoPath  = Shop::DB()->select('tseo', 'cSeo', $lastPath);
            if ($filename === 'jtl.php'
                || $filename === 'warenkorb.php'
                || $filename === 'kontakt.php'
                || $filename === 'news.php'
                || (isset($seoPath->cSeo) && strlen($seoPath->cSeo) > 0)
            ) {
                return $lastPath;
            }
        }

        return false;
    }

    /**
     * @param string $cUrl
     * @return bool|string
     */
    public function test($cUrl)
    {
        //Fallback e.g. if last URL-Path exists in tseo --> do not track 404 hit, instant redirect!
        if (($fallbackPath = $this->checkFallbackRedirect($cUrl)) !== false) {
            return $fallbackPath;
        }
        $cRedirectUrl = false;
        $cUrl         = $this->normalize($cUrl);
        if (is_string($cUrl) && strlen($cUrl) > 0 && $this->isValid($cUrl)) {
            $parsedUrl       = parse_url($cUrl);
            $cUrlQueryString = null;
            if (isset($parsedUrl['query'], $parsedUrl['path'])) {
                $cUrl            = $parsedUrl['path'];
                $cUrlQueryString = $parsedUrl['query'];
            }
            $foundRedirectWithQuery = false;
            if (!empty($cUrlQueryString)) {
                $oItem = $this->find($cUrl . '?' . $cUrlQueryString);
                if (is_object($oItem)) {
                    $cUrl                   = $cUrl . '?' . $cUrlQueryString;
                    $foundRedirectWithQuery = true;
                }
            } else {
                $oItem = $this->find($cUrl);
            }
            if (!is_object($oItem)) {
                $conf = Shop::getSettings([CONF_GLOBAL]);
                if (!isset($_GET['notrack'])  &&
                    (!isset($conf['global']['redirect_save_404']) || $conf['global']['redirect_save_404'] === 'Y')
                ) {
                    $oItem           = new self();
                    $oItem->cFromUrl = $cUrl . (!empty($cUrlQueryString) ? '?' . $cUrlQueryString : '');
                    $oItem->cToUrl   = '';
                    unset($oItem->kRedirect);
                    $oItem->kRedirect = Shop::DB()->insert('tredirect', $oItem);
                }
            } elseif (strlen($oItem->cToUrl) > 0) {
                $cRedirectUrl  = $oItem->cToUrl;
                $cRedirectUrl .= $cUrlQueryString !== null && !$foundRedirectWithQuery
                    ? '?' . $cUrlQueryString
                    : '';
            }
            $cReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if (strlen($cReferer) > 0) {
                $cReferer = $this->normalize($cReferer);
            }
            $cIP = getRealIp();
            // Eintrag für diese IP bereits vorhanden?
            $oEntry = Shop::DB()->query(
                "SELECT *
                    FROM tredirectreferer tr
                    LEFT JOIN tredirect t 
                        ON t.kRedirect = tr.kRedirect
                    WHERE tr.cIP = '{$cIP}'
                    AND t.cFromUrl = '{$cUrl}' LIMIT 1", 1
            );
            if ($oEntry === false || $oEntry === null || (is_object($oEntry) && (int)$oEntry->nCount === 0)) {
                $oReferer               = new stdClass();
                $oReferer->kRedirect    = isset($oItem->kRedirect) ? $oItem->kRedirect : 0;
                $oReferer->kBesucherBot = isset($_SESSION['oBesucher']->kBesucherBot)
                    ? (int)$_SESSION['oBesucher']->kBesucherBot
                    : 0;
                $oReferer->cRefererUrl  = is_string($cReferer) ? $cReferer : '';
                $oReferer->cIP          = $cIP;
                $oReferer->dDate        = time();
                Shop::DB()->insert('tredirectreferer', $oReferer);
                if (is_object($oItem)) {
                    if (!isset($oItem->nCount)) {
                        $oItem->nCount = 0;
                    }
                    ++$oItem->nCount;
                    Shop::DB()->update('tredirect', 'kRedirect', $oItem->kRedirect, $oItem);
                }
            }
        }

        return $cRedirectUrl;
    }

    /**
     * @param string $cUrl
     * @return bool
     */
    public function isValid($cUrl)
    {
        $cPath_arr       = pathinfo($cUrl);
        $cInvalidExt_arr = [
            'jpg',
            'gif',
            'bmp',
            'xml',
            'ico',
            'txt',
            'png'
        ];
        if (isset($cPath_arr['extension']) && strlen($cPath_arr['extension']) > 0) {
            $cExt = strtolower($cPath_arr['extension']);
            if (in_array($cExt, $cInvalidExt_arr, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $cUrl
     * @return bool
     * @deprecated since 4.05 - use Redirect::checkAvailability()
     */
    public function isAvailable($cUrl)
    {
        return self::checkAvailability($cUrl);
    }

    /**
     * @param string $cUrl
     * @return string
     */
    public function normalize($cUrl)
    {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Url.php';
        $oUrl = new UrlHelper();
        $oUrl->setUrl($cUrl);

        return '/' . trim($oUrl->normalize(), "\\/");
    }

    /**
     * @param int    $bUmgeleiteteUrls
     * @param string $cSuchbegriff
     * @return int
     */
    public function getCount($bUmgeleiteteUrls, $cSuchbegriff)
    {
        $bUmgeleiteteUrls = (int)$bUmgeleiteteUrls;
        $qry              = 'SELECT COUNT(*) AS nCount FROM tredirect ';
        $prep             = [];
        if ($bUmgeleiteteUrls === 1 || !empty($cSuchbegriff)) {
            $qry .= 'WHERE ';
        }
        if ($bUmgeleiteteUrls === 1) {
            $qry .= ' cToUrl != ""';
        }
        if (!empty($cSuchbegriff) && $bUmgeleiteteUrls === 1) {
            $qry .= ' AND ';
        }
        if (!empty($cSuchbegriff)) {
            $qry  .= "cFromUrl LIKE :search";
            $prep = ['search' => '%' . $cSuchbegriff . '%'];
        }
        $oCount = Shop::DB()->executeQueryPrepared($qry, $prep, 1);

        return isset($oCount->nCount)
            ? (int)$oCount->nCount
            : 0;
    }

    /**
     * @param int    $nStart
     * @param int    $nLimit
     * @param string $bUmgeleiteteUrls
     * @param string $cSortierFeld
     * @param string $cSortierung
     * @param string $cSuchbegriff
     * @param bool   $cMitVerweis
     * @return mixed
     * @deprecated since 4.05 - use Redirect::getRedirects()
     */
    public function getList($nStart, $nLimit, $bUmgeleiteteUrls, $cSortierFeld, $cSortierung, $cSuchbegriff, $cMitVerweis = true)
    {
        $cWhereSQL_arr = [];
        $cOrderSQL     = $cSortierFeld . ' ' . $cSortierung;
        $cLimitSQL     = (int)$nStart . ',' . (int)$nLimit;

        if ($cSuchbegriff !== '') {
            $cWhereSQL_arr[] = "cFromUrl LIKE '%" . $cSuchbegriff . "%'";
        }

        if ($bUmgeleiteteUrls === '1') {
            $cWhereSQL_arr[] = "cToUrl != ''";
            if ($cSuchbegriff !== '') {
                $cWhereSQL_arr[] = "cToUrl LIKE '%" . $cSuchbegriff . "%'";
            }
        } elseif ($bUmgeleiteteUrls === '2') {
            $cWhereSQL_arr[] = "cToUrl = ''";
        }

        $cWhereSQL = implode(' AND ', $cWhereSQL_arr);

        return self::getRedirects($cWhereSQL, $cOrderSQL, $cLimitSQL);
    }

    /**
     * @param int $kRedirect
     * @return array
     * @deprecated since 4.05 - use Redirect::getReferers()
     */
    public function getVerweise($kRedirect)
    {
        return self::getReferers($kRedirect);
    }

    /**
     * @param string $cWhereSQL
     * @param string $cOrderSQL
     * @param string $cLimitSQL
     * @return array
     */
    public static function getRedirects($cWhereSQL = '', $cOrderSQL = '', $cLimitSQL = '')
    {
        $oRedirect_arr = Shop::DB()->query("
            SELECT *
                FROM tredirect" .
                ($cWhereSQL !== '' ? " WHERE " . $cWhereSQL : "") .
                ($cOrderSQL !== '' ? " ORDER BY " . $cOrderSQL : "") .
                ($cLimitSQL !== '' ? " LIMIT " . $cLimitSQL : ""),
            2);

        if (is_array($oRedirect_arr) && count($oRedirect_arr) > 0) {
            foreach ($oRedirect_arr as &$oRedirect) {
                $oRedirect->oRedirectReferer_arr = self::getReferers($oRedirect->kRedirect);
            }
        }

        return $oRedirect_arr;
    }

    /**
     * @param string $cWhereSQL
     */
    public static function getRedirectCount($cWhereSQL = '')
    {
        return Shop::DB()->query(
            "SELECT COUNT(kRedirect) AS nCount
                FROM tredirect" .
                ($cWhereSQL !== '' ? " WHERE " . $cWhereSQL : ""),
            1
        )->nCount;
    }

    /**
     * @param int $kRedirect
     * @param int $nLimit
     * @return array
     */
    public static function getReferers($kRedirect, $nLimit = 100)
    {
        return Shop::DB()->query(
            "SELECT tredirectreferer.*, tbesucherbot.cName AS cBesucherBotName,
                    tbesucherbot.cUserAgent AS cBesucherBotAgent
                FROM tredirectreferer
                LEFT JOIN tbesucherbot
                    ON tredirectreferer.kBesucherBot = tbesucherbot.kBesucherBot
                    WHERE kRedirect = " . (int)$kRedirect . "
                ORDER BY dDate ASC
                LIMIT " . (int)$nLimit,
            2
        );
    }

    /**
     * @return int
     */
    public static function getTotalRedirectCount()
    {
        return (int)Shop::DB()->query("SELECT COUNT(kRedirect) AS nCount FROM tredirect", 1)->nCount;
    }

    /**
     * @param string $cUrl - one of
     *   * full URL (must be inside the same shop) e.g. http://www.shop.com/path/to/page
     *   * url path e.g. /path/to/page
     *   * path relative to the shop root url
     * @return bool
     */
    public static function checkAvailability($cUrl)
    {
        if (empty($cUrl)) {
            return false;
        }

        $parsedUrl     = parse_url($cUrl);
        $parsedShopUrl = parse_url(Shop::getURL() . '/');
        $fullUrlParts  = $parsedUrl;

        if (!isset($parsedUrl['host'])) {
            $fullUrlParts['scheme'] = $parsedShopUrl['scheme'];
            $fullUrlParts['host']   = $parsedShopUrl['host'];
        } elseif ($parsedUrl['host'] !== $parsedShopUrl['host']) {
            return false;
        }

        if (!isset($parsedUrl['path'])) {
            $fullUrlParts['path'] = $parsedShopUrl['path'];
        } elseif (strpos($parsedUrl['path'], $parsedShopUrl['path']) !== 0) {
            if (!isset($parsedUrl['host'])) {
                $fullUrlParts['path'] = $parsedShopUrl['path'] . ltrim($parsedUrl['path'], '/');
            } else {
                return false;
            }
        }

        if (isset($parsedUrl['query'])) {
            $fullUrlParts['query'] .= '&notrack';
        } else {
            $fullUrlParts['query'] = 'notrack';
        }

        $rebuiltUrl  = StringHandler::buildUrl($fullUrlParts);
        $cHeader_arr = get_headers($rebuiltUrl);

        if ($cHeader_arr !== false) {
            foreach ($cHeader_arr as $header) {
                if (preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $header)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $kRedirect
     */
    public static function deleteRedirect($kRedirect)
    {
        $kRedirect = (int)$kRedirect;
        Shop::DB()->delete('tredirect', 'kRedirect', $kRedirect);
        Shop::DB()->delete('tredirectreferer', 'kRedirect', $kRedirect);
    }

    /**
     * @return int
     */
    public static function deleteUnassigned()
    {
        return Shop::DB()->query(
            "DELETE tredirect, tredirectreferer
                FROM tredirect
                LEFT JOIN tredirectreferer 
                    ON tredirect.kRedirect = tredirectreferer.kRedirect
                WHERE tredirect.cToUrl = ''",
            3
        );
    }
}
