<?php
/**
 * Groups configuration for default Minify implementation
 *
 * @package Minify
 */

define('JTL_INCLUDE_ONLY_DB', true);
require_once '../../../includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';

$isAdmin   = (isset($_GET['g']) && ($_GET['g'] === 'admin_js' || $_GET['g'] === 'admin_css'));
$oTemplate = ($isAdmin) ? AdminTemplate::getInstance() : Template::getInstance();

return $oTemplate->getMinifyArray(true);
