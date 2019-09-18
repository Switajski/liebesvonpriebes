<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_XAJAX . 'xajax_core/xajax.inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Hersteller.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kategorie.php';

global $oAccount;
$url = null;
if (isset($_SERVER['REQUEST_URI'])) {
    $protocol = 'http://';
    if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] === '1')) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (isset($_SERVER['HTTP_HTTPS']) && (strtolower($_SERVER['HTTP_HTTPS']) === 'on' || $_SERVER['HTTP_HTTPS'] === '1'))
    ) {
        $protocol = 'https://';
    }
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

$xajax = new xajax($url);

/**
 * @deprecated since 4.06
 * @return xajaxResponse
 */
function reloadAdminLoginCaptcha()
{
    $oCaptcha    = generiereCaptchaCode(3);
    $objResponse = new xajaxResponse();
    $objResponse->assign('captcha_text', 'value', '');
    $objResponse->assign('captcha_md5', 'value', $oCaptcha->codemd5);
    $objResponse->assign('captcha', 'src', $oCaptcha->codeURL);

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cTargetID
 * @return xajaxResponse
 */
function getCurrencyConversionAjax($fPreisNetto, $fPreisBrutto, $cTargetID)
{
    $objResponse = new xajaxResponse();
    $cString     = getCurrencyConversion($fPreisNetto, $fPreisBrutto);
    $objResponse->assign($cTargetID, 'innerHTML', $cString);

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cTooltipID
 * @return xajaxResponse
 */
function setCurrencyConversionAjaxTooltip($fPreisNetto, $fPreisBrutto, $cTooltipID)
{
    $objResponse = new xajaxResponse();
    $cString     = getCurrencyConversion($fPreisNetto, $fPreisBrutto);
    $objResponse->assign($cTooltipID, 'dataset.originalTitle', $cString);

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param int    $kWidget
 * @param string $cContainer
 * @param int    $nPos
 * @return xajaxResponse
 */
function setWidgetPositionAjax($kWidget, $cContainer, $nPos)
{
    $objResponse = new xajaxResponse();
    setWidgetPosition($kWidget, $cContainer, $nPos);

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param int $kWidget
 * @return xajaxResponse
 */
function closeWidgetAjax($kWidget)
{
    $objResponse = new xajaxResponse();
    closeWidget((int)$kWidget);

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param int $kWidget
 * @return xajaxResponse
 */
function addWidgetAjax($kWidget)
{
    $objResponse = new xajaxResponse();
    addWidget((int)$kWidget);

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param int  $kWidget
 * @param bool $bExpand
 * @return xajaxResponse
 */
function expandWidgetAjax($kWidget, $bExpand)
{
    global $oAccount;
    $objResponse = new xajaxResponse();
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        expandWidget((int)$kWidget, $bExpand);
    }

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @return xajaxResponse
 */
function getAvailableWidgetsAjax()
{
    $objResponse = new xajaxResponse();
    global $oAccount;
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        $oAvailableWidget_arr = getWidgets(false);
        Shop::Smarty()->assign('oAvailableWidget_arr', $oAvailableWidget_arr);
        $cWrapper = Shop::Smarty()->fetch('tpl_inc/widget_selector.tpl');
        $cWrapper = utf8_encode($cWrapper);

        $objResponse->assign('settings', 'innerHTML', $cWrapper);
        $objResponse->script('registerWidgetSettings();');
    }

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cURL
 * @param string $cDataName
 * @param string $cTpl
 * @param string $cWrapperID
 * @param string $cPost
 * @param object $oCallback
 * @param bool $bDecodeUTF8
 * @return xajaxResponse
 */
function getRemoteDataAjax($cURL, $cDataName, $cTpl, $cWrapperID, $cPost = null, $oCallback = null, $bDecodeUTF8 = false)
{
    global $oAccount;
    $objResponse = new xajaxResponse();
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        $cData = http_get_contents($cURL, 15, $cPost);
        $oData = json_decode($cData);
        $oData = $bDecodeUTF8 ? utf8_convert_recursive($oData) : $oData;

        Shop::Smarty()->assign($cDataName, $oData);
        $cWrapper = Shop::Smarty()->fetch('tpl_inc/' . $cTpl);
        $objResponse->assign($cWrapperID, 'innerHTML', $cWrapper);

        if ($oCallback !== null) {
            $objResponse->script("if(typeof {$oCallback} === 'function') {$oCallback}({$cData});");
        }
    }

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cURL
 * @param string $cDataName
 * @param string $cTpl
 * @param string $cWrapperID
 */
function getRemoteDataApiAjax($cURL, $cDataName, $cTpl, $cWrapperID)
{
}

/**
 * @deprecated since 4.06
 * @return xajaxResponse
 */
function getRandomPassword()
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('cPass', 'value', gibUID(8));

    return $objResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cSearch
 * @param array $aParam
 * @return array
 */
function getArticleList($cSearch, $aParam)
{
    global $oAccount;
    $oResponse = new xajaxResponse();
    if ($oAccount->permission('REDIRECT_VIEW')) {
        $cSearch      = Shop::DB()->escape($cSearch);
        $cSearch      = utf8_decode($cSearch);
        $limit        = isset($aParam['cLimit']) ? (int)$aParam['cLimit'] : 50;
        $oArticle_arr = [];
        if (strlen($cSearch) >= 2 && $oAccount->logged()) {
            $oArticle_arr = Shop::DB()->executeQueryPrepared("
                SELECT kArtikel AS kPrimary, cArtNr AS cBase, kArtikel, cName
                    FROM tartikel
                    WHERE kArtikel LIKE :search
                        OR cArtNr LIKE :search
                        OR cISBN LIKE :search
                        OR cName LIKE :searchrl
                    LIMIT :lim",
                ['search' => $cSearch . '%', 'searchrl' => '%' . $cSearch . '%', 'lim' => $limit],
                2
            );
            foreach ($oArticle_arr as &$oArticle) {
                $oArticle->cName                              = utf8_encode($oArticle->cName);
                $oArticle->cBase                              = utf8_encode($oArticle->cBase); // optional (maybe for austria)
                $Artikel                                      = new Artikel();
                $oArtikelOptionen                             = new stdClass();
                $oArtikelOptionen->nKeinLagerbestandBeachten  = 1;
                $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
                $Artikel->fuelleArtikel($oArticle->kPrimary, $oArtikelOptionen, 0, 0, true);
                $oArticle->cUrl = $Artikel->cURL;
            }
        }
        if (isset($aParam['return']) && $aParam['return'] === 'object') {
            $oResponse = $oArticle_arr;
        } else {
            $oResponse->script('this.search_arr = ' . json_encode($oArticle_arr) . ';');
        }
    }

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cArray
 * @return xajaxResponse
 */
function getArticleListFromString($cArray)
{
    global $oAccount;

    $cArray         = Shop::DB()->escape($cArray);
    $cArray         = utf8_decode($cArray);
    $cArticleID_arr = explode(';', $cArray);
    $oArticle_arr   = [];
    $oResponse      = new xajaxResponse();
    if (count($cArticleID_arr) && $oAccount->logged()) {
        $cSQL = '';
        foreach ($cArticleID_arr as $cArticleID) {
            if (strlen($cSQL) > 0) {
                $cSQL .= " OR ";
            }
            $cSQL .= " cArtNr = '" . Shop::DB()->escape($cArticleID) . "' ";
        }

        $oArticle_arr = Shop::DB()->query("
            SELECT kArtikel AS kPrimary, cArtNr AS cBase, kArtikel, cName
                FROM tartikel
                WHERE " . $cSQL . "
                LIMIT 50", 2
        );
        foreach ($oArticle_arr as &$oArticle) {
            $oArticle->cName = utf8_encode($oArticle->cName);
        }
        unset($oArticle);
    }
    $oResponse->script('this.selected_arr = ' . json_encode($oArticle_arr) . ';');

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cSearch
 * @param array $aParam
 * @return array
 */
function getManufacturerList($cSearch, $aParam)
{
    global $oAccount;
    $cSearch           = Shop::DB()->escape($cSearch);
    $cSearch           = utf8_decode($cSearch);
    $limit             = isset($aParam['cLimit'])
        ? (int)$aParam['cLimit']
        : 50;
    $oManufacturer_arr = [];
    $shopURL           = Shop::getURL();
    if (strlen($cSearch) >= 2 && $oAccount->logged()) {
        $oManufacturer_arr = Shop::DB()->executeQueryPrepared("
            SELECT kHersteller AS kPrimary, kHersteller AS cBase, cName
                FROM thersteller
                WHERE cName LIKE :search
                LIMIT :lim",
            ['search' => $cSearch . '%', 'lim' => $limit],
            2
        );
        foreach ($oManufacturer_arr as &$oManufacturer) {
            $oManufacturer->cName = utf8_encode($oManufacturer->cName);
            $oHersteller          = new Hersteller($oManufacturer->kPrimary);
            $oManufacturer->cUrl  = substr($oHersteller->cURL, strlen($shopURL) + 1);
        }
        unset($oManufacturer);
    }
    if (isset($aParam['return']) && $aParam['return'] === 'object') {
        $oResponse = $oManufacturer_arr;
    } else {
        $oResponse = new xajaxResponse();
        $oResponse->script('this.search_arr = ' . json_encode($oManufacturer_arr) . ';');
    }

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cArray
 * @return xajaxResponse
 */
function getManufacturerListFromString($cArray)
{
    global $oAccount;

    $cArray            = Shop::DB()->escape($cArray);
    $cArray            = utf8_decode($cArray);
    $cManufacturer_arr = explode(';', $cArray);
    $oResponse         = new xajaxResponse();
    $oManufacturer_arr = [];
    if (count($cManufacturer_arr) && $oAccount->logged()) {
        $cSQL = '';
        foreach ($cManufacturer_arr as $cManufacturerID) {
            if (strlen($cSQL) > 0) {
                $cSQL .= " OR ";
            }
            $cSQL .= " kHersteller = " . (int)$cManufacturerID . " ";
        }

        $oManufacturer_arr = Shop::DB()->query(
            "SELECT kHersteller AS kPrimary, kHersteller AS cBase, cName
                FROM thersteller
                WHERE " . $cSQL . "
                LIMIT 50", 2
        );
        foreach ($oManufacturer_arr as &$oManufacturer) {
            $oManufacturer->cName = utf8_encode($oManufacturer->cName);
        }
        unset($oManufacturer);
    }
    $oResponse->script('this.selected_arr = ' . json_encode($oManufacturer_arr) . ';');

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cSearch
 * @param array $aParam
 * @return array
 */
function getCategoryList($cSearch, $aParam)
{
    global $oAccount;
    $cSearch       = Shop::DB()->escape($cSearch);
    $cSearch       = utf8_decode($cSearch);
    $limit         = isset($aParam['cLimit'])
        ? (int)$aParam['cLimit']
        : 50;
    $oCategory_arr = [];
    if (strlen($cSearch) >= 2 && $oAccount->logged()) {
        $oCategory_arr = Shop::DB()->executeQueryPrepared("
            SELECT kKategorie AS kPrimary, kKategorie AS cBase, cName
                FROM tkategorie
                WHERE cName LIKE :search
                LIMIT :lim",
            ['search' => $cSearch . '%', 'lim' => $limit],
            2
        );
        foreach ($oCategory_arr as &$oCategory) {
            $oCategory->cName = utf8_encode($oCategory->cName);
            $oKategorie       = new Kategorie($oCategory->kPrimary);
            $oCategory->cUrl  = $oKategorie->cSeo;
        }
        unset($oCategory);
    }
    if (isset($aParam['return']) && $aParam['return'] === 'object') {
        $oResponse = $oCategory_arr;
    } else {
        $oResponse = new xajaxResponse();
        $oResponse = $oResponse->script('this.search_arr = ' . json_encode($oCategory_arr) . ';');
    }

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cArray
 * @return xajaxResponse
 */
function getCategoryListFromString($cArray)
{
    global $oAccount;

    $cArray         = Shop::DB()->escape($cArray);
    $cArray         = utf8_decode($cArray);
    $cArticleID_arr = explode(';', $cArray);
    $oArticle_arr   = [];
    $oResponse      = new xajaxResponse();
    if (count($cArticleID_arr) && $oAccount->logged()) {
        $cSQL = '';
        foreach ($cArticleID_arr as $cArticleID) {
            if (strlen($cSQL) > 0) {
                $cSQL .= " OR ";
            }
            $cSQL .= " kKategorie = " . (int)$cArticleID . " ";
        }

        $oArticle_arr = Shop::DB()->query("
            SELECT kKategorie AS kPrimary, kKategorie AS cBase, cName
                FROM tkategorie
                WHERE " . $cSQL . "
                LIMIT 50", 2
        );
        foreach ($oArticle_arr as &$oArticle) {
            $oArticle->cName = utf8_encode($oArticle->cName);
        }
        unset($oArticle);
    }
    $oResponse->script('this.selected_arr = ' . json_encode($oArticle_arr) . ';');

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cSearch
 * @param string $cWrapperID
 * @return xajaxResponse
 */
function getTagList($cSearch, $cWrapperID)
{
    global $oAccount;

    $cSearch      = Shop::DB()->escape($cSearch);
    $cSearch      = utf8_decode($cSearch);
    $oArticle_arr = [];
    $oResponse    = new xajaxResponse();
    if (strlen($cSearch) >= 2 && $oAccount->logged()) {
        $oArticle_arr = Shop::DB()->executeQueryPrepared("
            SELECT kTag AS kPrimary, kTag AS cBase, cName
                FROM ttag
                WHERE cName LIKE :search
                LIMIT 50",
            ['search' => $cSearch . '%'],
            2
        );
        foreach ($oArticle_arr as &$oArticle) {
            $oArticle->cName = utf8_encode($oArticle->cName);
        }
        unset($oArticle);
    }
    $oResponse->script('this.search_arr = ' . json_encode($oArticle_arr) . ';');

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cSearch
 * @param string $cWrapperID
 * @return xajaxResponse
 */
function getAttributeList($cSearch, $cWrapperID)
{
    global $oAccount;

    $cSearch      = Shop::DB()->escape($cSearch);
    $cSearch      = utf8_decode($cSearch);
    $oArticle_arr = [];
    $oResponse    = new xajaxResponse();
    if (strlen($cSearch) >= 2 && $oAccount->logged()) {
        $oArticle_arr = Shop::DB()->executeQueryPrepared("
            SELECT tmerkmalwert.kMerkmalwert AS kPrimary, tmerkmalwert.kMerkmalwert AS cBase,
                tmerkmalwertsprache.cWert AS cName
                FROM tmerkmal
                LEFT JOIN tmerkmalwert
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                LEFT JOIN tmerkmalwertsprache
                    ON tmerkmalwert.kMerkmalwert = tmerkmalwertsprache.kMerkmalwert
                WHERE length(tmerkmalwertsprache.cWert) > 0
                    AND tmerkmalwertsprache.cWert LIKE :search
                LIMIT 50",
            ['search' => $cSearch . '%'],
            2
        );
        foreach ($oArticle_arr as &$oArticle) {
            $oArticle->cName = utf8_encode($oArticle->cName);
        }
        unset($oArticle);
    }
    $oResponse->script('this.search_arr = ' . json_encode($oArticle_arr) . ';');

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cSearch
 * @param array $aParam
 * @return xajaxResponse
 */
function getLinkList($cSearch, $aParam)
{
    global $oAccount;
    $cSearch      = Shop::DB()->escape($cSearch);
    $cSearch      = utf8_decode($cSearch);
    $oArticle_arr = [];
    $oResponse    = new xajaxResponse();
    if (strlen($cSearch) >= 2 && $oAccount->logged()) {
        $oArticle_arr = Shop::DB()->executeQueryPrepared("
            SELECT kLink AS kPrimary, kLink AS cBase, cName
                FROM tlink
                WHERE cName LIKE :search
                LIMIT 50",
            ['search' => $cSearch . '%'],
            2
        );
        foreach ($oArticle_arr as &$oArticle) {
            $oArticle->cName = utf8_encode($oArticle->cName);
        }
    }
    $oResponse->script('this.search_arr = ' . json_encode($oArticle_arr) . ';');

    return $oResponse;
}


/**
 * Auswahlassistent
 *
 * @deprecated since 4.06
 * @param array $kMM_arr
 * @param int   $kSprache
 * @return xajaxResponse
 */
function getMerkmalWerteAA($kMM_arr, $kSprache)
{
    global $oAccount;
    $oResponse = new xajaxResponse();
    $kMM_arr = array_map('intval', $kMM_arr);
    if ($kSprache > 0 &&
        is_array($kMM_arr) &&
        count($kMM_arr) > 0 &&
        $oAccount->permission('EXTENSION_SELECTIONWIZARD_VIEW')
    ) {
        $oMerkmalWert_arr = Shop::DB()->query(
            "SELECT tmerkmalwert.*, tmerkmalwertsprache.cWert, tmerkmal.cName
                FROM tmerkmalwert
                JOIN tmerkmal
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                JOIN tmerkmalwertsprache
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND tmerkmalwertsprache.kSprache = " . (int)$kSprache . "
                WHERE tmerkmalwert.kMerkmal IN(" . implode(',', $kMM_arr) . ")
                ORDER BY tmerkmalwert.nSort", 2
        );
        if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
            $cMMWOption = '';
            foreach ($oMerkmalWert_arr as $oMerkmalWert) {
                $cMMWOption .= '<option value="' . $oMerkmalWert->kMerkmalWert . '">';
                $cMMWOption .= utf8_encode($oMerkmalWert->cName) . ': ' .
                    utf8_encode($oMerkmalWert->cWert) . '</option>';
            }
            $oResponse->assign('MerkmalWert', 'innerHTML', $cMMWOption);
        }
    }

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cData
 * @return xajaxResponse
 */
function saveBannerAreas($cData)
{
    global $oAccount;
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ImageMap.php';

    $oBanner   = new ImageMap();
    $oResponse = new xajaxResponse();
    $oData     = json_decode($cData);
    foreach ($oData->oArea_arr as &$oArea) {
        $oArea->cTitel        = utf8_decode($oArea->cTitel);
        $oArea->cUrl          = utf8_decode($oArea->cUrl);
        $oArea->cBeschreibung = utf8_decode($oArea->cBeschreibung);
        $oArea->cStyle        = utf8_decode($oArea->cStyle);
        $oArea->kArtikel      = (int)$oArea->kArtikel;
    }
    unset($oArea);
    if ($oAccount->logged()) {
        $oBanner->saveAreas($oData);
    }

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @param string $cTemplate
 * @return xajaxResponse
 */
function getContentTemplate($cTemplate)
{
    global $oAccount;

    $oResponse = new xajaxResponse();
    $cTplPath  = "tpl_inc/links/{$cTemplate}.tpl";
    if ($oAccount->logged() && file_exists(Shop::Smarty()->getTemplateDir(Shop::Smarty()->context) . $cTplPath)) {
        $cWrapper = Shop::Smarty()->fetch($cTplPath);
        $oResponse->assign('content_template_data', 'innerHTML', $cWrapper);
        $oResponse->script('link_dynamic_init()');
    } else {
        $oResponse->assign('content_template_data', 'innerHTML', '');
    }

    return $oResponse;
}

/**
 * @deprecated since 4.06
 * @return xajaxResponse
 */
function truncateJtllog()
{
    global $oAccount;
    $oResponse = new xajaxResponse();
    if ($oAccount->permission('DASHBOARD_VIEW')) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
        Jtllog::truncateLog();
    }

    return $oResponse;
}

if ($oAccount->getIsAuthenticated()) {
    executeHook(HOOK_TOOLSAJAX_SERVER_ADMIN, ['xajax' => &$xajax]);

    $xajax->registerFunction('reloadAdminLoginCaptcha');
    $xajax->registerFunction('getCurrencyConversionAjax');
    $xajax->registerFunction('setCurrencyConversionAjaxTooltip');
    $xajax->registerFunction('setWidgetPositionAjax');
    $xajax->registerFunction('closeWidgetAjax');
    $xajax->registerFunction('addWidgetAjax');
    $xajax->registerFunction('expandWidgetAjax');
    $xajax->registerFunction('getAvailableWidgetsAjax');
    $xajax->registerFunction('getRemoteDataAjax');
    $xajax->registerFunction('getRemoteDataApiAjax');
    $xajax->registerFunction('getRandomPassword');
    $xajax->registerFunction('getArticleList');
    $xajax->registerFunction('getArticleListFromString');
    $xajax->registerFunction('getManufacturerList');
    $xajax->registerFunction('getManufacturerListFromString');
    $xajax->registerFunction('getCategoryList');
    $xajax->registerFunction('getCategoryListFromString');
    $xajax->registerFunction('getTagList');
    $xajax->registerFunction('getAttributeList');
    $xajax->registerFunction('getLinkList');
    $xajax->registerFunction('getMerkmalWerteAA');
    $xajax->registerFunction('setRMAStatusAjax');
    $xajax->registerFunction('saveBannerAreas');
    $xajax->registerFunction('getContentTemplate');
    $xajax->registerFunction('truncateJtllog');

    $xajax->processRequest();
    header('Content-Type:text/html;charset=' . JTL_CHARSET . ';');
}
