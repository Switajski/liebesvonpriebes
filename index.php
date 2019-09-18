<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require __DIR__ . '/includes/globalinclude.php';
require PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::run();
$cParameter_arr = Shop::getParameters();
$NaviFilter     = Shop::buildNaviFilter($cParameter_arr);
Shop::checkNaviFilter($NaviFilter);
$linkHelper     = LinkHelper::getInstance();
if (Shop::$kLink > 0) {
    $link = $linkHelper->getPageLink(Shop::$kLink);
}
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
//prg
if (isset($_SESSION['bWarenkorbHinzugefuegt'], $_SESSION['bWarenkorbAnzahl'], $_SESSION['hinweis'])) {
    $smarty->assign('bWarenkorbHinzugefuegt', $_SESSION['bWarenkorbHinzugefuegt'])
           ->assign('bWarenkorbAnzahl', $_SESSION['bWarenkorbAnzahl'])
           ->assign('hinweis', $_SESSION['hinweis']);
    unset($_SESSION['hinweis'], $_SESSION['bWarenkorbAnzahl'], $_SESSION['bWarenkorbHinzugefuegt']);
}
//wurde ein artikel in den Warenkorb gelegt?
checkeWarenkorbEingang();
if (!$cParameter_arr['kWunschliste'] &&
    verifyGPDataString('error') === '' &&
    strlen(verifyGPDataString('wlid')) > 0
) {
    header(
        'Location: ' . $linkHelper->getStaticRoute('wunschliste.php', true) .
        '?wlid=' . StringHandler::filterXSS(verifyGPDataString('wlid')) . '&error=1',
        true,
        303
    );
    exit();
}
//support for artikel_after_cart_add
if ($smarty->getTemplateVars('bWarenkorbHinzugefuegt')) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    if (isset($_POST['a']) && function_exists('gibArtikelXSelling')) {
        $smarty->assign('Xselling', gibArtikelXSelling($_POST['a']));
    }
}
if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen &&
    ($cParameter_arr['kArtikel'] > 0 || $cParameter_arr['kKategorie'] > 0)
) {
    //falls Artikel/Kategorien nicht gesehen werden duerfen -> login
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) . '?li=1', true, 303);
    exit;
}
if ($cParameter_arr['kKategorie'] > 0 &&
    !Kategorie::isVisible($cParameter_arr['kKategorie'], $_SESSION['Kundengruppe']->kKundengruppe)
) {
    $cParameter_arr['kKategorie'] = 0;
    $oLink                        = Shop::DB()->select('tlink', 'nLinkart', LINKTYP_404);
    $kLink                        = (int)$oLink->kLink;
    Shop::$kLink                  = $kLink;
}
Shop::getEntryPoint();
if (Shop::$is404 === true) {
    $cParameter_arr['is404'] = true;
    Shop::$fileName = null;
}
$smarty->assign('NaviFilter', $NaviFilter);
if (Shop::$fileName !== null) {
    require PFAD_ROOT . Shop::$fileName;
}
if ($cParameter_arr['is404'] === true) {
    if (!isset($seo)) {
        $seo = null;
    }
    executeHook(HOOK_INDEX_SEO_404, ['seo' => $seo]);
    if (!Shop::$kLink) {
        $hookInfos     = urlNotFoundRedirect([
            'key'   => 'kLink',
            'value' => $cParameter_arr['kLink']
        ]);
        $kLink         = $hookInfos['value'];
        $bFileNotFound = $hookInfos['isFileNotFound'];
        if (!$kLink) {
            $kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_404);
            Shop::$kLink = $kLink;
        }
    }
    require_once PFAD_ROOT . 'seite.php';
} elseif (Shop::$fileName === null && Shop::getPageType() !== null) {
    require_once PFAD_ROOT . 'seite.php';
}
