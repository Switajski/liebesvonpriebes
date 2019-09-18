<?php
/**
 *-------------------------------------------------------------------------------
 *	JTL-Shop 3
 *	File: extendurl.php, php file
 *
 *	JTL-Shop 3
 *
 * Do not use, modify or sell this code without permission / licence.
 *
 * @author JTL-Software <daniel.boehmer@jtl-software.de>
 * @copyright 2010, JTL-Software
 * @link http://jtl-software.de/jtlshop.php
 * @version 1.0
 *-------------------------------------------------------------------------------
*/

require_once("{$oPlugin->cFrontendPfad}../includes/defines_inc.php");
require_once("{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php");

// Filter and url extending
$cFilter_arr = JtlSearch::getFilter($_GET);
JtlSearch::extendSessionCurrencyURL(JtlSearch::buildFilterShopURL($cFilter_arr));    // Extend currency url
JtlSearch::extendSessionLanguageURL(JtlSearch::buildFilterShopURL($cFilter_arr));    // Extend language url
;
