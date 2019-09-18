<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Search for backend settings
 *
 * @param string $query - search string
 * @param bool $data - true to return raw data, false to return html
 * @return IOError|object
 */
function adminSearch($query, $data = false)
{
    define('JTL_CHARSET', 'utf-8');

    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';

    Shop::DB()->executeQuery('SET NAMES ' . str_replace('-', '', JTL_CHARSET), 3);

    $settings       = bearbeiteEinstellungsSuche($query);
    $shippings      = getShippingByName($query);
    $paymentMethods = getPaymentMethodsByName($query);

    $groupedSettings = [];
    $currentGroup    = null;

    foreach ($settings->oEinstellung_arr as $setting) {
        if ($setting->cConf === 'N') {
            $currentGroup                   = $setting;
            $currentGroup->oEinstellung_arr = [];
            $groupedSettings[]              = $currentGroup;
        } elseif ($currentGroup !== null) {
            $currentGroup->oEinstellung_arr[] = $setting;
        }
    }

    if ($data === true) {
        if (count($groupedSettings) === 0) {
            $result = new IOError('No search results');
        } else {
            $result = (object)['data' => utf8_convert_recursive($groupedSettings)];
        }
    } else {
        Shop::Smarty()->assign('settings', !empty($settings->oEinstellung_arr) ? $groupedSettings : null)
               ->assign('shippings', count($shippings) > 0 ? $shippings : null)
               ->assign('paymentMethods', count($paymentMethods) > 0 ? $paymentMethods : null);
        $template = Shop::Smarty()->fetch('suche.tpl');
        $result   = (object)['data' => (object)['tpl' => utf8_encode($template)]];
    }

    $result->type = 'search';

    return $result;
}
