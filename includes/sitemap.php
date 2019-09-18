<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
define('JTL_INCLUDE_ONLY_DB', 1);
require_once __DIR__ . '/globalinclude.php';
include PFAD_ROOT . PFAD_INCLUDES . 'spiderlist_inc.php';

$cDatei = isset($_GET['datei'])
    ? getRequestFile($_GET['datei'])
    : null;

if ($cDatei === null) {
    http_response_code(503);
    header('Retry-After: 86400');
    exit;
}

$cIP              = Shop::DB()->escape(getRealIp());
$nFloodProtection = (int)Shop::DB()->query("
    SELECT * 
        FROM `tsitemaptracker` 
        WHERE `cIP` = '{$cIP}' 
            AND DATE_ADD(`dErstellt`, INTERVAL 2 MINUTE) >= NOW() 
        ORDER BY `dErstellt` DESC", 3
);
if ($nFloodProtection === 0) {
    // Track request
    $oSitemapTracker               = new stdClass();
    $oSitemapTracker->cSitemap     = basename($cDatei);
    $oSitemapTracker->kBesucherBot = getRequestBot();
    $oSitemapTracker->cIP          = $cIP;
    $oSitemapTracker->cUserAgent   = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
    $oSitemapTracker->dErstellt    = 'now()';

    Shop::DB()->insert('tsitemaptracker', $oSitemapTracker);
}

// Redirect to real filepath
sendRequestFile($cDatei);

/**
 * @return int
 */
function getRequestBot()
{
    $cSpider_arr       = getSpiderArr();
    $cBotUserAgent_arr = array_keys($cSpider_arr);
    if (is_array($cBotUserAgent_arr) && count($cBotUserAgent_arr) > 0) {
        foreach ($cBotUserAgent_arr as $i => $cBotUserAgent) {
            if (stripos($_SERVER['HTTP_USER_AGENT'], $cBotUserAgent) !== false) {
                $oBesucherBot = Shop::DB()->select('tbesucherbot', 'cUserAgent', $cBotUserAgent);

                return isset($oBesucherBot->kBesucherBot) ? (int)$oBesucherBot->kBesucherBot : 0;
            }
        }
    }

    return 0;
}

/**
 * @param string $cDatei
 * @return null|string
 */
function getRequestFile($cDatei)
{
    $cDateiInfo_arr = pathinfo($cDatei);

    if (!isset($cDateiInfo_arr['extension']) || !in_array($cDateiInfo_arr['extension'], ['xml', 'txt', 'gz'], true)) {
        return null;
    }

    if ($cDatei !== $cDateiInfo_arr['basename']) {
        return null;
    }

    $cDatei = $cDateiInfo_arr['basename'];

    if (!file_exists(PFAD_ROOT . PFAD_EXPORT . $cDatei)) {
        return null;
    }

    return $cDatei;
}

/**
 * @param string $cFile
 */
function sendRequestFile($cFile)
{
    $cFile          = basename($cFile);
    $cAbsoluteFile  = PFAD_ROOT . PFAD_EXPORT . basename($cFile);
    $cFileExtension = pathinfo($cAbsoluteFile, PATHINFO_EXTENSION);

    switch (strtolower($cFileExtension)) {
        case 'xml':
            $cContentType = 'application/xml';
            break;
        case 'txt':
            $cContentType = 'text/plain';
            break;
        default:
            $cContentType = 'application/octet-stream';
            break;
    }

    if (file_exists($cAbsoluteFile)) {
        header('Content-Type: ' . $cContentType);
        header('Content-Length: ' . filesize($cAbsoluteFile));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($cAbsoluteFile)) . ' GMT');

        if ($cContentType === 'application/octet-stream') {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . $cFile);
            header('Content-Transfer-Encoding: binary');
        }

        ob_end_clean();
        flush();
        readfile($cAbsoluteFile);
        exit;
    }
}
