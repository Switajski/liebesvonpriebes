<?php
/**
 * JtlSearch Class
 *
 * @access public
 * @author Daniel Boehmer
 * @copyright 2011 JTL-Software GmbH
 */

require_once dirname(__FILE__) . '/class.Security.php';
require_once dirname(__FILE__) . '/class.Communication.php';

/**
 * Class JtlSearch
 */
class JtlSearch
{
    /**
     * @var string
     */
    private static $cProjectId = '';

    /**
     * @var string
     */
    private static $cAuthHash = '';

    /**
     * @var string
     */
    private static $cSearchURL = '';

    /**
     * @var string
     */
    private static $cAction = '';

    /**
     * @param $kKundengruppe
     * @param $cLanguageIso
     * @param $cCurrencyIso
     * @param $cValue
     * @param $cProjectId
     * @param $cAuthHash
     * @param $cServerUrl
     * @param $oResponse
     * @return array
     */
    public static function doSuggest($kKundengruppe, $cLanguageIso, $cCurrencyIso, $cValue, $cProjectId, $cAuthHash, $cServerUrl, &$oResponse)
    {
        if (intval($kKundengruppe) == 0 || strlen($cCurrencyIso) == 0 || strlen($cCurrencyIso) == 0 || strlen($cValue) == 0 || strlen($cProjectId) == 0 || strlen($cAuthHash) == 0 || strlen($cServerUrl) == 0) {
            return array();
        }

        self::$cProjectId = $cProjectId;
        self::$cAuthHash  = $cAuthHash;
        self::$cSearchURL = $cServerUrl;
        self::$cAction    = 'dosuggest';

        // Security Class
        $oSecurity = new Security(self::$cProjectId, self::$cAuthHash);
        $oSecurity->setParam_arr(array(self::$cAction, $cValue, $kKundengruppe, $cLanguageIso, $cCurrencyIso));

        try {
            $cResponse = Communication::postData(
                self::$cSearchURL . 'searchdaemon/index.php', array(
                'pid'   => self::$cProjectId,
                'p'     => $oSecurity->createKey(),
                'a'     => self::$cAction,
                'q'     => $cValue,
                'kdgrp' => $kKundengruppe,
                'lang'  => $cLanguageIso,
                'curr'  => $cCurrencyIso), true
            );
            $oResponse = json_decode($cResponse);
            $oData_arr = array();
            foreach ($oResponse->oSuggest->oGroup_arr as $oGroup) {
                $oData_arr[$oGroup->cType] = $oGroup;
            }

            return $oData_arr;
        } catch (Exception $exc) {
            Jtllog::writeLog("doSuggest mit Exception: File: {$exc->getFile()}\nLine: {$exc->getLine()}\nMessage: {$exc->getMessage()}");

            return null;
        }
    }

    /**
     * @param        $cSessionID
     * @param        $kKundengruppe
     * @param        $cLanguageIso
     * @param        $cCurrencyIso
     * @param        $cValue
     * @param        $cProjectId
     * @param        $cAuthHash
     * @param        $cServerUrl
     * @param int    $nProductsPerSite
     * @param int    $nPage
     * @param bool   $bCheckStock
     * @param string $cFilter
     * @param string $cSort
     * @return mixed|null
     */
    public static function doSearch($cSessionID, $kKundengruppe, $cLanguageIso, $cCurrencyIso, $cValue, $cProjectId, $cAuthHash, $cServerUrl, $nProductsPerSite = 100, $nPage = 1, $bCheckStock = false, $cFilter = '', $cSort = '')
    {
        if (strlen($cSessionID) == 0 || intval($kKundengruppe) == 0 || strlen($cCurrencyIso) == 0 || strlen($cCurrencyIso) == 0 || strlen($cValue) == 0 || strlen($cProjectId) == 0 || strlen($cAuthHash) == 0 || strlen($cServerUrl) == 0) {
            return null;
        }

        self::$cProjectId = $cProjectId;
        self::$cAuthHash  = $cAuthHash;
        self::$cSearchURL = $cServerUrl;
        self::$cAction    = "dosearch";

        $nStart = ($nPage - 1) * $nProductsPerSite;

        $cValue = utf8_encode($cValue);

        // Security Class
        $oSecurity = new Security(self::$cProjectId, self::$cAuthHash);
        $oSecurity->setParam_arr(array(self::$cAction, $cValue, $nProductsPerSite, $nStart, $cFilter, $kKundengruppe, $cLanguageIso, $cCurrencyIso));

        try {
            $cResponse = Communication::postData(
                self::$cSearchURL . 'searchdaemon/index.php',
                array(
                    'pid'     => self::$cProjectId,
                    'p'       => $oSecurity->createKey(),
                    'a'       => self::$cAction,
                    'q'       => $cValue,
                    'rows'    => $nProductsPerSite,
                    'start'   => $nStart,
                    'filter'  => $cFilter,
                    'kdgrp'   => $kKundengruppe,
                    'lang'    => $cLanguageIso,
                    'curr'    => $cCurrencyIso,
                    'sessid'  => $cSessionID,
                    'sort'    => $cSort,
                    'instock' => $bCheckStock), true
            );
            $oResponse = json_decode($cResponse);

            // LandingPage hit?
            if (isset($oResponse->oLandingPage) && $oResponse->oLandingPage !== null) {
                header('Location: ' . $oResponse->oLandingPage->cPageURL);
                exit();
            }

            self::checkEncoding($oResponse);

            // Search
            if (count($oResponse->oSearch->oBanner_arr) > 0) {
                // Momentan wird nur eine ImageMap angezeigt
                $oBanner = $oResponse->oSearch->oBanner_arr[0];

                // Nach Shop3 Standard
                $oBannerShop3            = new stdClass;
                $oBannerShop3->cBildPfad = $oBanner->cImgUrl;
                $oBannerShop3->fWidth    = $oBanner->nImgWidth;
                $oBannerShop3->fHeight   = $oBanner->nImgHeight;
                $oBannerShop3->oArea_arr = array();

                foreach ($oBanner->oBannercoord_arr as &$oArea) {
                    $AreaShop3                = new stdClass;
                    $AreaShop3->cTitel        = utf8_decode($oArea->cTitle);
                    $AreaShop3->cUrl          = "";
                    $AreaShop3->cBeschreibung = utf8_decode($oArea->cDescription);
                    $AreaShop3->kImageMapArea = $oArea->kBannerCoord;
                    $AreaShop3->cStyle        = $oArea->cStyle;
                    $AreaShop3->oCoords       = new stdClass;
                    $AreaShop3->oCoords->x    = $oArea->oCoord->nX;
                    $AreaShop3->oCoords->y    = $oArea->oCoord->nY;
                    $AreaShop3->oCoords->w    = $oArea->oCoord->nW;
                    $AreaShop3->oCoords->h    = $oArea->oCoord->nH;

                    if (utf8_decode($oArea->cKey) === 'article' && intval($oArea->cValue) > 0) {
                        $AreaShop3->kArtikel = intval($oArea->cValue);

                        $oSprache      = gibStandardsprache(true);
                        $kSprache      = $oSprache->kSprache;
                        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
                        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                            $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
                        }

                        $oArtikelOptionen                    = new stdClass;
                        $oArtikelOptionen->nAttribute        = 1;
                        $oArtikelOptionen->nArtikelAttribute = 1;

                        $AreaShop3->oArtikel = new Artikel();
                        $AreaShop3->oArtikel->fuelleArtikel($AreaShop3->kArtikel, $oArtikelOptionen, $kKundengruppe, $kSprache);

                        if (strlen($AreaShop3->cTitel) === 0) {
                            $AreaShop3->cTitel = $AreaShop3->oArtikel->cName;
                        }
                        if (strlen($AreaShop3->cUrl) === 0) {
                            $AreaShop3->cUrl = $AreaShop3->oArtikel->cURL;
                        }
                        if (strlen($AreaShop3->cBeschreibung) === 0) {
                            $AreaShop3->cBeschreibung = $AreaShop3->oArtikel->cKurzBeschreibung;
                        }
                    }

                    $oBannerShop3->oArea_arr[] = $AreaShop3;
                }

                Shop::Smarty()->assign('oImageMap', $oBannerShop3);
            }

            return $oResponse;
        } catch (Exception $exc) {
            Jtllog::writeLog("doSearch mit Exception: File: {$exc->getFile()}\nLine: {$exc->getLine()}\nMessage: {$exc->getMessage()}");

            return null;
        }
    }

    /**
     * @param $kQuery
     * @param $kArtikel
     * @param $nHitType
     * @param $cProjectId
     * @param $cAuthHash
     * @param $cServerUrl
     * @return mixed|string
     */
    public static function doProductStats($kQuery, $kArtikel, $nHitType, $cProjectId, $cAuthHash, $cServerUrl)
    {
        self::$cProjectId = $cProjectId;
        self::$cAuthHash  = $cAuthHash;
        self::$cSearchURL = $cServerUrl;
        self::$cAction    = "doproductstats";

        // Security Class
        $oSecurity = new Security(self::$cProjectId, self::$cAuthHash);
        $oSecurity->setParam_arr(array(self::$cAction, $kQuery, $kArtikel, $nHitType));

        try {
            $cResponse = Communication::postData(
                self::$cSearchURL . 'searchdaemon/index.php',
                array(
                    'pid'     => self::$cProjectId,
                    'p'       => $oSecurity->createKey(),
                    'a'       => self::$cAction,
                    'query'   => $kQuery,
                    'product' => $kArtikel,
                    'hittype' => $nHitType),
                true
            );

            $oResponse = json_decode($cResponse);

            if ($oResponse !== null) {
                return $oResponse;
            }

            return $cResponse;
        } catch (Exception $exc) {
            Jtllog::writeLog("doProductStats mit Exception: File: {$exc->getFile()}\nLine: {$exc->getLine()}\nMessage: {$exc->getMessage()}");

            return null;
        }
    }

    /**
     * @param $cQuery
     * @param $cLanguageIso
     * @param $cProjectId
     * @param $cAuthHash
     * @param $cServerUrl
     * @return mixed|string
     */
    public static function doSuggestForward($cQuery, $cLanguageIso, $cProjectId, $cAuthHash, $cServerUrl)
    {
        self::$cProjectId = $cProjectId;
        self::$cAuthHash  = $cAuthHash;
        self::$cSearchURL = $cServerUrl;
        self::$cAction    = 'dosuggestforward';

        // Security Class
        $oSecurity = new Security(self::$cProjectId, self::$cAuthHash);
        $oSecurity->setParam_arr(array(self::$cAction, $cQuery, $cLanguageIso));

        try {
            return Communication::postData(
                self::$cSearchURL . 'searchdaemon/index.php',
                array(
                    'pid'     => self::$cProjectId,
                    'p'       => $oSecurity->createKey(),
                    'a'       => self::$cAction,
                    'query'   => $cQuery,
                    'langiso' => $cLanguageIso),
                true
            );
        } catch (Exception $exc) {
            Jtllog::writeLog("doSuggestForward mit Exception: File: {$exc->getFile()}\nLine: {$exc->getLine()}\nMessage: {$exc->getMessage()}");

            return null;
        }
    }

    /**
     * Convert UTF-8 into shop standard encoding
     *
     * @param $oResponse
     */
    private static function checkEncoding(&$oResponse)
    {
        if (isset($oResponse->oSearch->oFilterGroup_arr) && is_array($oResponse->oSearch->oFilterGroup_arr) && count($oResponse->oSearch->oFilterGroup_arr) > 0) {
            foreach ($oResponse->oSearch->oFilterGroup_arr as $oFilterGroup) {
                $oFilterGroup->cName = utf8_decode($oFilterGroup->cName);
                if (isset($oFilterGroup->oFilterItem_arr) && is_array($oFilterGroup->oFilterItem_arr) && count($oFilterGroup->oFilterItem_arr) > 0) {
                    foreach ($oFilterGroup->oFilterItem_arr as $oFilterItem) {
                        $oFilterItem->cValue = utf8_decode($oFilterItem->cValue);

                        if (isset($oFilterItem->cUnit)) {
                            $oFilterItem->cUnit = utf8_decode($oFilterItem->cUnit);
                        }

                        if ($oFilterGroup->cDataType === 'float' && $oFilterGroup->nType == 2 && strpos($oFilterGroup->cName, 'price') !== false) {
                            // Price Slider

                            $oFilterItem->cUnit = $_SESSION['Waehrung']->cNameHTML;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $kKundengruppe
     * @param $cLanguageIso
     * @param $cCurrencyIso
     * @param $cProjectId
     * @param $cAuthHash
     * @param $cServerUrl
     * @return bool|mixed
     * Return:
     * bool true            => ready for searching
     * bool false            => server not accessible or account closed
     * object oResponse        => server change -> write new data to db
     * oResponse->_code 2    => only change ... solr is not accessible
     * oResponse->_code 3    => change and ready for searching
     */
    public static function doCheck($kKundengruppe, $cLanguageIso, $cCurrencyIso, $cProjectId, $cAuthHash, $cServerUrl)
    {
        if (intval($kKundengruppe) === 0 || strlen($cCurrencyIso) === 0 || strlen($cCurrencyIso) === 0 || strlen($cProjectId) === 0 || strlen($cAuthHash) === 0 || strlen($cServerUrl) === 0) {
            return false;
        }

        self::$cProjectId = $cProjectId;
        self::$cAuthHash  = $cAuthHash;
        self::$cSearchURL = $cServerUrl;
        self::$cAction    = 'docheck';

        // Security Class
        $oSecurity = new Security(self::$cProjectId, self::$cAuthHash);
        $oSecurity->setParam_arr(array(self::$cAction, $kKundengruppe, $cLanguageIso, $cCurrencyIso));

        try {
            $cResponse = Communication::postData(
                self::$cSearchURL . 'searchdaemon/index.php',
                array(
                    'pid'   => self::$cProjectId,
                    'p'     => $oSecurity->createKey(),
                    'a'     => self::$cAction,
                    'kdgrp' => $kKundengruppe,
                    'lang'  => $cLanguageIso,
                    'curr'  => $cCurrencyIso), true
            );

            $oResponse = json_decode($cResponse);
            if (is_object($oResponse) && $oResponse->_code == 1) {
                return true;
            } else {
                if (is_object($oResponse) && ($oResponse->_code == 2 || $oResponse->_code == 3) && strlen($oResponse->_serverurl) > 0) {
                    // Server change
                    return $oResponse;
                } else {
                    return false;
                }
            }
        } catch (Exception $exc) {
            Jtllog::writeLog("doCheck mit Exception: File: {$exc->getFile()}\nLine: {$exc->getLine()}\nMessage: {$exc->getMessage()}");

            return false;
        }
    }

    /**
     * @param $cParamAssoc_arr
     * @return array
     */
    public static function getFilter($cParamAssoc_arr)
    {
        $cFilter_arr = array();
        for ($i = 0; $i < 20; $i++) {
            if (isset($cParamAssoc_arr["fq{$i}"])) {
                $cFilter_arr[] = $cParamAssoc_arr["fq{$i}"];
            }
        }

        return $cFilter_arr;
    }

    /**
     * Solr request
     *
     * @param $cFilter_arr
     * @return string
     */
    public static function buildFilterURL($cFilter_arr)
    {
        if (is_array($cFilter_arr) && count($cFilter_arr) > 0) {
            return implode('__', $cFilter_arr);
        }

        return '';
    }

    /**
     * @param $cFilter_arr
     * @return string
     */
    public static function buildFilterShopURL($cFilter_arr)
    {
        $cURL = '';
        if (is_array($cFilter_arr) && count($cFilter_arr) > 0) {
            foreach ($cFilter_arr as $i => $cFilter) {
                $cURL .= "&fq{$i}={$cFilter}";
            }
        }

        return $cURL;
    }

    /**
     * @param $oFilterGroup_arr
     * @return array
     */
    private static function buildStatedFilterList($oFilterGroup_arr)
    {
        $cStatedFilterAssoc_arr = array();
        if (is_array($oFilterGroup_arr) && count($oFilterGroup_arr) > 0) {
            foreach ($oFilterGroup_arr as $oFilterGroup) {
                foreach ($oFilterGroup->oFilterItem_arr as $oFilterItem) {
                    if ($oFilterItem->bSet) {
                        if (!isset($cStatedFilterAssoc_arr[$oFilterGroup->cName])) {
                            $cStatedFilterAssoc_arr[$oFilterGroup->cName] = array();
                        }

                        $cStatedFilterAssoc_arr[$oFilterGroup->cName][] = $oFilterItem->cValue;
                    }
                }
            }
        }

        return $cStatedFilterAssoc_arr;
    }

    /**
     * Build stated filter url
     *
     * @param        $cStatedFilterAssoc_arr
     * @param string $cGroupRel
     * @param string $cKeyRel
     * @return string
     */
    private static function buildStatedFilterURL($cStatedFilterAssoc_arr, $cGroupRel = '', $cKeyRel = '')
    {
        $cURL         = '';
        $nStatedCount = 0;
        if (is_array($cStatedFilterAssoc_arr) && count($cStatedFilterAssoc_arr) > 0) {
            foreach ($cStatedFilterAssoc_arr as $cGroup => $cStatedFilter_arr) {
                foreach ($cStatedFilter_arr as $cStatedFilter) {
                    // Release or set filter
                    if (($cGroup != $cGroupRel && $cStatedFilter != $cKeyRel) || ($cGroup == $cGroupRel && $cStatedFilter != $cKeyRel)) {
                        $cURL .= "&fq{$nStatedCount}={$cGroup}:" . urlencode($cStatedFilter);
                        $nStatedCount++;
                    }
                }
            }
        }

        return $cURL;
    }

    /**
     * @param $oFilterGroup_arr
     * @param $cShopURL
     * @return string
     */
    public static function extendFilterStandaloneURL(&$oFilterGroup_arr, $cShopURL)
    {
        if (is_array($oFilterGroup_arr) && count($oFilterGroup_arr) > 0) {
            $cStatedFilterAssoc_arr = self::buildStatedFilterList($oFilterGroup_arr);

            return $cShopURL . self::buildStatedFilterURL($cStatedFilterAssoc_arr);
        }

        return $cShopURL;
    }

    /**
     * Extended the filter entries for the cURL variable to release or set filters
     *
     * @param $oFilterGroup_arr
     * @param $nStatedFilterCount
     * @param $cShopURL
     */
    public static function extendFilterItemURL(&$oFilterGroup_arr, $nStatedFilterCount, $cShopURL)
    {
        if (is_array($oFilterGroup_arr) && count($oFilterGroup_arr) > 0) {
            $cStatedFilterAssoc_arr = self::buildStatedFilterList($oFilterGroup_arr);

            foreach ($oFilterGroup_arr as $oFilterGroup) {
                $oFilterGroup->cMapping = utf8_decode($oFilterGroup->cMapping);

                foreach ($oFilterGroup->oFilterItem_arr as $oFilterItem) {
                    if ($oFilterItem->bSet) {
                        $oFilterItem->cURL = $cShopURL . self::buildStatedFilterURL($cStatedFilterAssoc_arr, $oFilterGroup->cName, $oFilterItem->cValue);
                    } else {
                        $oFilterItem->cURL = $cShopURL . self::buildStatedFilterURL($cStatedFilterAssoc_arr) . "&fq{$nStatedFilterCount}={$oFilterGroup->cName}:" . urlencode($oFilterItem->cValue);
                    }
                }
            }
        }
    }

    /**
     * @param $cStatedFilter
     */
    public static function extendSessionCurrencyURL($cStatedFilter)
    {
        if (isset($_SESSION['Waehrungen']) && is_array($_SESSION['Waehrungen']) && count($_SESSION['Waehrungen']) > 0 && strlen($cStatedFilter) > 0) {
            foreach ($_SESSION['Waehrungen'] as $oWaehrung) {
                if (isset($oWaehrung->cURL)) {
                    $oWaehrung->cURL .= $cStatedFilter;
                }
            }
        }
    }

    /**
     * @param $cStatedFilter
     */
    public static function extendSessionLanguageURL($cStatedFilter)
    {
        if (isset($_SESSION['Sprachen']) && is_array($_SESSION['Sprachen']) && count($_SESSION['Sprachen']) > 0 && strlen($cStatedFilter) > 0) {
            foreach ($_SESSION['Sprachen'] as $oSprache) {
                if (isset($oSprache->cURL)) {
                    $oSprache->cURL .= $cStatedFilter;
                }
            }
        }
    }

    /**
     * @param $sorting
     * @param $currencyiso
     * @param $customergrp
     * @return string
     */
    public static function getSorting($sorting, $currencyiso, $customergrp)
    {
        $currencyiso = strtolower($currencyiso);
        $customergrp = (int)$customergrp;

        $sort = '';
        if ($sorting !== null) {
            switch ($sorting) {
                // Name ASC
                case 1:
                    $sort = "name ASC";
                    break;

                // Name ASC
                case 2:
                    $sort = "name DESC";
                    break;

                // Name ASC
                case 3:
                    $sort = "price_{$currencyiso}_{$customergrp} ASC";
                    break;

                // Name ASC
                case 4:
                    $sort = "price_{$currencyiso}_{$customergrp} DESC";
                    break;
            }
        }

        return $sort;
    }
}
