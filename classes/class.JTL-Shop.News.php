<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class News
 */
class News extends MainModel
{
    /**
     * @var int
     */
    public $kNews;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $cUrl;

    /**
     * @var string
     */
    public $cUrlExt;

    /**
     * @var string
     */
    public $cKundengruppe;

    /**
     * @var string
     */
    public $cBetreff;

    /**
     * @var string
     */
    public $cText;

    /**
     * @var string
     */
    public $cVorschauText;

    /**
     * @var string
     */
    public $cMetaTitle;

    /**
     * @var string
     */
    public $cMetaDescription;

    /**
     * @var string
     */
    public $cMetaKeywords;

    /**
     * @var string
     */
    public $nAktiv;

    /**
     * @var string
     */
    public $nNewsKommentarAnzahl;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dGueltigVon;

    /**
     * @var string
     */
    public $dGueltigVonJS;

    /**
     * @return int
     */
    public function getNews()
    {
        return $this->kNews;
    }

    /**
     * @param int $kNews
     * @return $this
     */
    public function setNews($kNews)
    {
        $this->kNews = (int)$kNews;

        return $this;
    }

    /**
     * @return int
     */
    public function getSprache()
    {
        return $this->kSprache;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache($kSprache)
    {
        $this->kSprache = (int)$kSprache;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeo()
    {
        return $this->cSeo;
    }

    /**
     * @param string $cSeo
     * @return $this
     */
    public function setSeo($cSeo)
    {
        $this->cSeo = $cSeo;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->cUrl;
    }

    /**
     * @param string $cUrl
     * @return $this
     */
    public function setUrl($cUrl)
    {
        $this->cUrl = $cUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlExt()
    {
        return $this->cUrlExt;
    }

    /**
     * @param string $cUrlExt
     * @return $this
     */
    public function setUrlExt($cUrlExt)
    {
        $this->cUrlExt = $cUrlExt;

        return $this;
    }

    /**
     * @return string
     */
    public function getKundengruppe()
    {
        return $this->cKundengruppe;
    }

    /**
     * @param string $cKundengruppe
     * @return $this
     */
    public function setKundengruppe($cKundengruppe)
    {
        $this->cKundengruppe = $cKundengruppe;

        return $this;
    }

    /**
     * @return string
     */
    public function getBetreff()
    {
        return $this->cBetreff;
    }

    /**
     * @param string $cBetreff
     * @return $this
     */
    public function setBetreff($cBetreff)
    {
        $this->cBetreff = $cBetreff;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->cText;
    }

    /**
     * @param string $cText
     * @return $this
     */
    public function setText($cText)
    {
        $this->cText = $cText;

        return $this;
    }

    /**
     * @return string
     */
    public function getVorschauText()
    {
        return $this->cVorschauText;
    }

    /**
     * @param string $cVorschauText
     * @return $this
     */
    public function setVorschauText($cVorschauText)
    {
        $this->cVorschauText = $cVorschauText;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->cMetaTitle;
    }

    /**
     * @param string $cMetaTitle
     * @return $this
     */
    public function setMetaTitle($cMetaTitle)
    {
        $this->cMetaTitle = $cMetaTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->cMetaDescription;
    }

    /**
     * @param string $cMetaDescription
     * @return $this
     */
    public function setMetaDescription($cMetaDescription)
    {
        $this->cMetaDescription = $cMetaDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->cMetaKeywords;
    }

    /**
     * @param string $cMetaKeywords
     * @return $this
     */
    public function setMetaKeywords($cMetaKeywords)
    {
        $this->cMetaKeywords = $cMetaKeywords;

        return $this;
    }

    /**
     * @return int
     */
    public function getAktiv()
    {
        return $this->nAktiv;
    }

    /**
     * @param int $nAktiv
     * @return $this
     */
    public function setAktiv($nAktiv)
    {
        $this->nAktiv = (int)$nAktiv;

        return $this;
    }

    /**
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = ($dErstellt === 'now()')
            ? date('Y-m-d H:i:s')
            : $dErstellt;

        return $this;
    }

    /**
     * @return string
     */
    public function getGueltigVon()
    {
        return $this->dGueltigVon;
    }

    /**
     * @param string $dGueltigVon
     * @return $this
     */
    public function setGueltigVon($dGueltigVon)
    {
        $this->dGueltigVon = ($dGueltigVon === 'now()')
            ? date('Y-m-d H:i:s')
            : $dGueltigVon;

        return $this;
    }

    /**
     * @return string
     */
    public function getGueltigVonJS()
    {
        return $this->dGueltigVonJS;
    }

    /**
     * @param string $dGueltigVonJS
     * @return $this
     */
    public function setGueltigVonJS($dGueltigVonJS)
    {
        $this->dGueltigVonJS = ($dGueltigVonJS === 'now()')
            ? date('Y-m-d H:i:s')
            : $dGueltigVonJS;

        return $this;
    }

    /**
     * @param int         $kKey
     * @param null|object $oObj
     * @param null        $xOption
     */
    public function load($kKey, $oObj = null, $xOption = null)
    {
        $kKey = (int)$kKey;
        if ($kKey > 0) {
            $kSprache = null;
            if (isset($_SESSION['kSprache'])) {
                $kSprache = (int)$_SESSION['kSprache'];
            } else {
                $oSprache = gibStandardsprache(true);
                $kSprache = (int)$oSprache->kSprache;
            }

            $oObj = Shop::DB()->query(
                "SELECT tseo.cSeo, tnews.*, DATE_FORMAT(tnews.dGueltigVon, '%Y,%m,%d') AS dGueltigVonJS, 
                    COUNT(DISTINCT(tnewskommentar.kNewsKommentar)) AS nNewsKommentarAnzahl
                    FROM tnews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = {$kSprache}
                    LEFT JOIN tnewskommentar 
                        ON tnewskommentar.kNews = tnews.kNews
                        AND tnewskommentar.nAktiv = 1
                    WHERE tnews.kNews = {$kKey}
                    GROUP BY tnews.kNews
                    LIMIT 1", 1
            );
            $oObj->cUrl = baueURL($oObj, URLART_NEWS);

            $this->loadObject($oObj);
        }
    }

    /**
     * @param bool        $bActive
     * @param null|string $cOrder
     * @param null|int    $nCount
     * @param null|int    $nOffset
     * @param null|int    $kExcludeCategory
     * @return array
     */
    public static function loadAll($bActive = true, $cOrder = null, $nCount = null, $nOffset = null, $kExcludeCategory = null)
    {
        $cSqlActive = '';
        if ($bActive) {
            $cSqlActive = ' AND tnews.nAktiv = 1';
        }
        $cSqlExcludeCategory = '';
        if ($kExcludeCategory !== null) {
            $kExcludeCategory    = (int)$kExcludeCategory;
            $cSqlExcludeCategory = "JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews
                                        AND tnewskategorienews.kNewsKategorie != {$kExcludeCategory}";
        }
        $cSqlOrder = ' ORDER BY tnews.dGueltigVon DESC';
        if ($cOrder !== null) {
            $cSqlOrder = " ORDER BY {$cOrder}";
        }

        $cSqlLimit = '';
        if ($nCount !== null && $nOffset !== null) {
            $cSqlLimit = " LIMIT {$nOffset}, $nCount";
        } elseif ($nCount !== null) {
            $cSqlLimit = " LIMIT {$nCount}";
        }
        $kKundengruppe = null;
        if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        } else {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        if (isset($_SESSION['kSprache'])) {
            $kSprache = (int)$_SESSION['kSprache'];
        } else {
            $oSprache = gibStandardsprache(true);
            $kSprache = (int)$oSprache->kSprache;
        }
        $oObj_arr = Shop::DB()->query(
            "SELECT tseo.cSeo, tnews.*, DATE_FORMAT(tnews.dGueltigVon, '%Y,%m,%d') AS dGueltigVonJS, 
                COUNT(DISTINCT(tnewskommentar.kNewsKommentar)) AS nNewsKommentarAnzahl
                FROM tnews
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = {$kSprache}
                LEFT JOIN tnewskommentar ON tnewskommentar.kNews = tnews.kNews
                    AND tnewskommentar.nAktiv = 1
                {$cSqlExcludeCategory}
                WHERE tnews.dGueltigVon <= now()
                    AND (tnews.cKundengruppe LIKE '%;-1;%' 
                        OR FIND_IN_SET('{$kKundengruppe}', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    AND tnews.kSprache = {$kSprache}
                {$cSqlActive}
                GROUP BY tnews.kNews
                {$cSqlOrder}
                {$cSqlLimit}", 2
        );

        if (is_array($oObj_arr) && count($oObj_arr) > 0) {
            $oNews_arr = [];
            foreach ($oObj_arr as $oObj) {
                $oObj->cUrl    = baueURL($oObj, URLART_NEWS);
                $oObj->cUrlExt = Shop::getURL() . "/{$oObj->cUrl}";
                $oNews_arr[]   = new self(null, $oObj);
            }

            return $oNews_arr;
        }

        return [];
    }
}
