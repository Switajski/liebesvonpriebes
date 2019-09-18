<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$smarty->register_function('requirePluginCustomlink', 'requirePluginCustomlink');
$smarty->register_function('getCurrencyConversionSmarty', 'getCurrencyConversionSmarty');
$smarty->register_function('SmartyConvertDate', 'SmartyConvertDate');
$smarty->register_function('getHelpDesc', 'getHelpDesc');
$smarty->register_modifier('permission', 'permission');
$smarty->register_function('dani_date_format', 'dani_date_format');

/**
 * @param $params
 * @param $smarty
 */
function requirePluginCustomlink($params, &$smarty)
{
    if (file_exists($params['cPfad'] . $params['cDateiname'])) {
        require_once $params['cPfad'] . $params['cDateiname'];
    }
}

/**
 * @param $params
 * @param $smarty
 * @return string
 */
function getCurrencyConversionSmarty($params, &$smarty)
{
    $bForceSteuer = true;
    if (isset($params['bSteuer']) && $params['bSteuer'] == false) {
        $bForceSteuer = false;
    }
    if (!isset($params['fPreisBrutto'])) {
        $params['fPreisBrutto'] = 0;
    }
    if (!isset($params['fPreisNetto'])) {
        $params['fPreisNetto'] = 0;
    }
    if (!isset($params['cClass'])) {
        $params['cClass'] = '';
    }

    return getCurrencyConversion($params['fPreisNetto'], $params['fPreisBrutto'], $params['cClass'], $bForceSteuer);
}

/**
 * @param $params
 * @param $smarty
 * @return string
 */
function getHelpDesc($params, &$smarty)
{
    return '<img src="templates/default/gfx/help.png" alt="' . $params['cDesc'] . '" title="' . $params['cDesc'] . '" style="vertical-align:middle; cursor:help;">';
}

/**
 * @param $cRecht
 * @return bool
 */
function permission($cRecht)
{
    $bOkay = false;
    global $smarty;

    if (isset($_SESSION['AdminAccount'])) {
        $bOkay = (in_array($cRecht, $_SESSION['AdminAccount']->oGroup->oPermission_arr) || $_SESSION['AdminAccount']->oGroup->kAdminlogingruppe == 1);
    }

    if (!$bOkay) {
        $smarty->debugging = false;
    }

    return $bOkay;
}

/**
 * @param $params
 * @param $smarty
 * @return string
 */
function SmartyConvertDate($params, &$smarty)
{
    if (isset($params['date']) && strlen($params['date']) > 0) {
        $oDateTime = new DateTime($params['date']);
        if (isset($params['format']) && strlen($params['format']) > 1) {
            $cDate = $oDateTime->format($params['format']);
        } else {
            $cDate = $oDateTime->format('d.m.Y H:i:s');
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $cDate);
        } else {
            return $cDate;
        }
    }

    return '';
}

/**
 * @param $params
 * @return string
 */
function dani_date_format($params)
{
    if (isset($params['date']) && strlen($params['date']) > 0) {
        if (isset($params['format']) && strlen($params['format']) > 0) {
            $oDateTime = new DateTime($params['date']);
        }

        return $oDateTime->format($params['format']);
    }
}
