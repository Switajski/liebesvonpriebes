<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once "{$oPlugin->cFrontendPfad}../includes/defines_inc.php";
require_once "{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php";

// Filter and url extending
$cFilter_arr = JtlSearch::getFilter($_GET);
JtlSearch::extendSessionCurrencyURL(JtlSearch::buildFilterShopURL($cFilter_arr));// Extend currency url
JtlSearch::extendSessionLanguageURL(JtlSearch::buildFilterShopURL($cFilter_arr));// Extend language url
