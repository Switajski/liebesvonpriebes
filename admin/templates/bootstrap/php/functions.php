<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
/** @global JTLSmarty $smarty */
$smarty->registerPlugin('function', 'getCurrencyConversionSmarty', 'getCurrencyConversionSmarty')
       ->registerPlugin('function', 'getCurrencyConversionTooltipButton', 'getCurrencyConversionTooltipButton')
       ->registerPlugin('function', 'getCurrentPage', 'getCurrentPage')
       ->registerPlugin('function', 'SmartyConvertDate', 'SmartyConvertDate')
       ->registerPlugin('function', 'getHelpDesc', 'getHelpDesc')
       ->registerPlugin('function', 'getExtensionCategory', 'getExtensionCategory')
       ->registerPlugin('function', 'formatVersion', 'formatVersion')
       ->registerPlugin('function', 'gravatarImage', 'gravatarImage')
       ->registerPlugin('function', 'getRevisions', 'getRevisions')
       ->registerPlugin('modifier', 'permission', 'permission');

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return mixed
 */
function getRevisions($params, &$smarty)
{
    $secondary = isset($params['secondary'])
        ? $params['secondary']
        : false;
    $data      = isset($params['data'])
        ? $params['data']
        : null;
    $revision  = new Revision();
    return $smarty->assign('revisions', $revision->getRevisions($params['type'], $params['key']))
           ->assign('secondary', $secondary)
           ->assign('data', $data)
           ->assign('show', $params['show'])
           ->fetch('tpl_inc/revisions.tpl');
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getCurrencyConversionSmarty($params, &$smarty)
{
    $bForceSteuer = !(isset($params['bSteuer']) && $params['bSteuer'] == false);
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
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getCurrencyConversionTooltipButton($params, &$smarty)
{
    $placement = isset($params['placement'])
        ? $params['placement']
        : 'left';

    if (isset($params['inputId'])) {
        $inputId = $params['inputId'];
        $button = '<button type="button" class="btn btn-tooltip btn-info" id="' . $inputId . 'Tooltip" data-html="true"';
        $button .= ' data-toggle="tooltip" data-placement="' . $placement . '">';
        $button .= '<i class="fa fa-eur"></i></button>';

        return $button;
    }

    return '';
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 */
function getCurrentPage($params, &$smarty)
{
    $path = $_SERVER['SCRIPT_NAME'];
    $page = basename($path, '.php');

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $page);
    }
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getHelpDesc($params, &$smarty)
{
    $placement = 'left';
    if (isset($params['placement'])) {
        $placement = $params['placement'];
    }

    $button = '<button type="button" class="btn-tooltip btn btn-info btn-heading" data-html="true" data-toggle="tooltip" data-placement="' . $placement . '" title="';

    if (isset($params['cDesc'])) {
        $button .= str_replace('"', '\'', $params['cDesc']);
        if (isset($params['cID'])) {
            $button .= '<hr><strong>Einstellungsnr.:</strong> ' . $params['cID'];
        }
        $button .= '"><i class="fa fa-question"></i></button>';
    } else {
        if (isset($params['cID'])) {
            $button .= '<p><strong>Einstellungsnr.:</strong> ' . $params['cID'] . '</p>';
        }
        $button .= '"><i class="fa fa-question"></i></button>';
    }

    return $button;
}

/**
 * @param mixed $cRecht
 * @return bool
 */
function permission($cRecht)
{
    $bOkay = false;
    global $smarty;

    if (isset($_SESSION['AdminAccount'])) {
        if ($_SESSION['AdminAccount']->oGroup->kAdminlogingruppe == 1) {
            $bOkay = true;
        } else {
            $orExpressions = explode('|', $cRecht);
            foreach ($orExpressions as $flag) {
                $bOkay = in_array($flag, $_SESSION['AdminAccount']->oGroup->oPermission_arr);
                if ($bOkay) {
                    break;
                }
            }
        }
    }

    if (!$bOkay) {
        $smarty->debugging = false;
    }

    return $bOkay;
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
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
 * Map marketplace categoryId to localized category name
 * 
 * @param array $params
 * @param JTLSmarty $smarty
 */
function getExtensionCategory($params, &$smarty)
{
    if (!isset($params['cat'])) {
        return;
    }

    $catNames = [
        4  => 'Templates/Themes',
        5  => 'Sprachpakete',
        6  => 'Druckvorlagen',
        7  => 'Tools',
        8  => 'Marketing',
        9  => 'Zahlungsarten',
        10 => 'Import/Export',
        11 => 'SEO',
        12 => 'Auswertungen'
    ];

    $key = isset($catNames[$params['cat']]) ? $catNames[$params['cat']] : null;
    $smarty->assign('catName', $key);
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return string|null
 */
function formatVersion($params, &$smarty)
{
    if (!isset($params['value'])) {
        return null;
    }

    return substr_replace((int)$params['value'], '.', 1, 0);
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param array $params
 *
 * array['email'] - The email address
 * array['s']     - Size in pixels, defaults to 80px [ 1 - 2048 ]
 * array['d']     - Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * array['r']     - Maximum rating (inclusive) [ g | pg | r | x ]
 *
 * @params JTLSmarty $smarty
 * @source https://gravatar.com/site/implement/images/php/
 * @return string
 */
function gravatarImage($params, &$smarty)
{
    $email = isset($params['email']) ? $params['email'] : null;
    if ($email === null) {
        $email = JTLSUPPORT_EMAIL;
    } else {
        unset($params['email']);
    }

    $params = array_merge(['email' => null, 's' => 80, 'd' => 'mm', 'r' => 'g'], $params);

    $url  = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= '?' . http_build_query($params, '', '&');

    executeHook(HOOK_BACKEND_FUNCTIONS_GRAVATAR, [
        'url' => &$url,
        'AdminAccount' => &$_SESSION['AdminAccount']
    ]);

    return $url;
}
