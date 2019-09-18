<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::setPageType(PAGE_UNBEKANNT);
$Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$cAction       = strtolower($_GET['a']);
$kCustom       = (int)$_GET['k'];
$bNoData       = false;

switch ($cAction) {
    case 'download_vorschau':
        if (class_exists('Download')) {
            $oDownload = new Download($kCustom);
            if ($oDownload->getDownload() > 0) {
                $smarty->assign('oDownload', $oDownload);
            } else {
                $bNoData = true;
            }
        }
        break;
    default:
        $bNoData = true;
        break;
}

$smarty->assign('bNoData', $bNoData)
       ->assign('cAction', $cAction)
       ->assign('Einstellungen', $Einstellungen);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('checkout/download_popup.tpl');
