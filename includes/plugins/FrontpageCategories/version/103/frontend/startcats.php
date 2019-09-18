<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/classes/class.StartCats.php';
/** @global Plugin $oPlugin */
/** @global JTLSmarty $smarty */
if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_aktiv'] === 'Y' &&
    Shop::getPageType() === PAGE_STARTSEITE
) {
    $oStartCats  = new StartCats();
    $categories  = $oStartCats->getCategories();
    $title       = (!empty($oPlugin->oPluginSprachvariableAssoc_arr['jtl_startcats_title']))
        ? $oPlugin->oPluginSprachvariableAssoc_arr['jtl_startcats_title']
        : '';
    $nCatsPerRow = (int)$oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_catsperrow'] > 0
        ? (int)$oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_catsperrow']
        : 3;

    if (count($categories) > 0) {
        $smarty->assign('startCategories', $categories)
               ->assign('title', $title)
               ->assign('type', $oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_type'])
               ->assign('itemcount', count($categories))
               ->assign('categoriesPerRow', $nCatsPerRow);
        $html       = $smarty->fetch($oPlugin->cFrontendPfad . 'template/categories.tpl');
        $pqFunction = $oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_function'];
        $pqSelector = (!empty($oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_selector']))
            ? $oPlugin->oPluginEinstellungAssoc_arr['jtl_startcats_selector']
            : '#content';
        pq($pqSelector)->$pqFunction($html);
    }
}
