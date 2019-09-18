<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $bActive
 * @return mixed
 */
function getWidgets($bActive = true)
{
    $oWidget_arr = Shop::DB()->selectAll('tadminwidgets', 'bActive', (int)$bActive, '*', 'eContainer ASC, nPos ASC');
    if ($bActive && is_array($oWidget_arr) && count($oWidget_arr) > 0) {
        foreach ($oWidget_arr as $i => $oWidget) {
            $oWidget_arr[$i]->cContent = '';
            $cClass                    = 'Widget' . $oWidget->cClass;
            $cClassFile                = 'class.' . $cClass . '.php';
            $cClassPath                = PFAD_ROOT . PFAD_ADMIN . 'includes/widgets/' . $cClassFile;
            $oWidget->cNiceTitle       = str_replace(['--', ' '], '-', $oWidget->cTitle);
            $oWidget->cNiceTitle       = strtolower(str_replace(
                ['ä', 'Ä', 'ü', 'Ü', 'ö', 'Ö', 'ß', utf8_decode('ü'), utf8_decode('Ü'), utf8_decode('ä'), utf8_decode('Ä'), utf8_decode('ö'), utf8_decode('Ö'), '(', ')', '/', '\\'],
                '',
                $oWidget->cNiceTitle)
            );
            // Plugin?
            $oPlugin = null;
            if (isset($oWidget->kPlugin) && $oWidget->kPlugin > 0) {
                $oPlugin    = new Plugin($oWidget->kPlugin);
                $cClass     = 'Widget' . $oPlugin->oPluginAdminWidgetAssoc_arr[$oWidget->kWidget]->cClass;
                $cClassPath = $oPlugin->oPluginAdminWidgetAssoc_arr[$oWidget->kWidget]->cClassAbs;
            }
            if (file_exists($cClassPath)) {
                require_once $cClassPath;
                if (class_exists($cClass)) {
                    /** @var WidgetBase $oClassObj */
                    $oClassObj                 = new $cClass(null, null, $oPlugin);
                    $oWidget_arr[$i]->cContent = $oClassObj->getContent();
                }
            }
        }
    }

    return $oWidget_arr;
}

/**
 * @param int    $kWidget
 * @param string $eContainer
 * @param int    $nPos
 */
function setWidgetPosition($kWidget, $eContainer, $nPos)
{
    $upd             = new stdClass();
    $upd->eContainer = $eContainer;
    $upd->nPos       = (int)$nPos;
    Shop::DB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 */
function closeWidget($kWidget)
{
    $upd          = new stdClass();
    $upd->bActive = 0;
    Shop::DB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 */
function addWidget($kWidget)
{
    $upd          = new stdClass();
    $upd->bActive = 1;
    Shop::DB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 * @param int $bExpand
 */
function expandWidget($kWidget, $bExpand)
{
    $upd            = new stdClass();
    $upd->bExpanded = (int)$bExpand;
    Shop::DB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 * @return string
 */
function getWidgetContent($kWidget)
{
    $cContent = '';
    $oWidget  = Shop::DB()->select('tadminwidgets', 'kWidget', (int)$kWidget);

    if (!is_object($oWidget)) {
        return '';
    }

    $cClass     = 'Widget' . $oWidget->cClass;
    $cClassFile = 'class.' . $cClass . '.php';
    $cClassPath = 'includes/widgets/' . $cClassFile;

    if (file_exists($cClassPath)) {
        require_once $cClassPath;
        if (class_exists($cClass)) {
            /** @var WidgetBase $oClassObj */
            $oClassObj = new $cClass();
            $cContent  = $oClassObj->getContent();
        }
    }

    return $cContent;
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @return mixed|string
 * @deprecated since 4.06
 */
function getRemoteData($cURL, $nTimeout = 15)
{
    $cData = '';
    if (function_exists('curl_init')) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $cURL);
        curl_setopt($curl, CURLOPT_TIMEOUT, $nTimeout);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_REFERER, Shop::getURL());

        $cData = curl_exec($curl);
        curl_close($curl);
    } elseif (ini_get('allow_url_fopen')) {
        @ini_set('default_socket_timeout', $nTimeout);
        $fileHandle = @fopen($cURL, 'r');
        if ($fileHandle) {
            @stream_set_timeout($fileHandle, $nTimeout);
            $cData = fgets($fileHandle);
            fclose($fileHandle);
        }
    }

    return $cData;
}

/**
 * @param string $cURL
 * @param string $cDataName
 * @param string $cTpl
 * @param string $cWrapperID
 * @param string $cPost
 * @param null $cCallback
 * @param bool $bDecodeUTF8
 * @return IOResponse
 * @throws SmartyException
 */
function getRemoteDataIO($cURL, $cDataName, $cTpl, $cWrapperID, $cPost = null, $cCallback = null, $bDecodeUTF8 = false)
{
    $response = new IOResponse();
    $cData    = http_get_contents($cURL, 15, $cPost);
    $oData    = json_decode($cData);
    $oData    = $bDecodeUTF8 ? utf8_convert_recursive($oData) : $oData;
    Shop::Smarty()->assign($cDataName, $oData);
    $cWrapper = Shop::Smarty()->fetch('tpl_inc/' . $cTpl);
    $response->assign($cWrapperID, 'innerHTML', $cWrapper);

    if ($cCallback !== null) {
        $response->script("if(typeof {$cCallback} === 'function') {$cCallback}({$cData});");
    }

    return $response;
}

function getShopInfoIO($cTpl, $cWrapperID)
{
    $response = new IOResponse();

    $oSubscription = Shop()->RS()->getSubscription();
    $oLatestVersion = Shop()->RS()->getLatestVersion();
    $bUpdateAvailable = Shop()->RS()->hasNewerVersion();

    $strLatestVersion = $oLatestVersion
        ? sprintf('%.2f', $oLatestVersion->version / 100)
        : null;

    Shop::Smarty()->assign('oSubscription', $oSubscription);
    Shop::Smarty()->assign('oVersion', $oLatestVersion);
    Shop::Smarty()->assign('strLatestVersion', $strLatestVersion);
    Shop::Smarty()->assign('bUpdateAvailable', $bUpdateAvailable);

    $cWrapper = Shop::Smarty()->fetch('tpl_inc/' . $cTpl);
    $response->assign($cWrapperID, 'innerHTML', $cWrapper);

    return $response;
}

function getAvailableWidgetsIO()
{
    $response             = new IOResponse();
    $oAvailableWidget_arr = getWidgets(false);
    Shop::Smarty()->assign('oAvailableWidget_arr', $oAvailableWidget_arr);
    $cWrapper = Shop::Smarty()->fetch('tpl_inc/widget_selector.tpl');
    $cWrapper = utf8_encode($cWrapper);
    $response->assign('settings', 'innerHTML', $cWrapper);

    return $response;
}