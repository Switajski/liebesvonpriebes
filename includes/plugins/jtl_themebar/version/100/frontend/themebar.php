<?php
/** @global Plugin $oPlugin */
/** @global JTLSmarty $smarty */
$oBrowser = getBrowser();

if ($oBrowser->bMobile) {
    return;
}

require_once __DIR__ . '/includes/themebar_inc.php';

$oTheme_arr      = getThemes();
$cDefaultTheme   = getDefaultTheme();
$oTemplate       = Template::getInstance();
$cRequestedTheme = isset($_COOKIE['style'])
    ? $_COOKIE['style']
    : null;

$cRequestedTheme_arr = array_filter(
    $oTheme_arr,
    function ($e) use (&$cRequestedTheme) {
        return $e->cValue === $cRequestedTheme;
    }
);

$cTheme = count($cRequestedTheme_arr) > 0
    ? $cRequestedTheme
    : $cDefaultTheme;

if (is_array($oTheme_arr) && count($oTheme_arr) > 0) {
    $smarty->assign('oThemebar_arr', $oTheme_arr);
    $tplData = $smarty->fetch($oPlugin->cFrontendPfad . 'templates/' . 'themebar.tpl');

    pq('div#content-wrapper')->before($tplData);

    $currentTheme = pq('head > link[rel="stylesheet"]:first');
    $currentTheme->attr('data-theme', $cDefaultTheme);

    foreach ($oTheme_arr as $oTheme) {
        if ($oTheme->cValue !== $cDefaultTheme) {
            $style = '<link type="text/css" href="asset/' .
                $oTheme->cValue . '.css?v=' . $oTemplate->getVersion() .
                '" rel="stylesheet" data-theme="' . $oTheme->cValue . '">';
            $currentTheme->after($style);
        }
    }

    pq('head > link[data-theme]')->attr('disabled', true);
    pq('head > link[data-theme="' . $cTheme . '"]')->removeAttr('disabled');
}
