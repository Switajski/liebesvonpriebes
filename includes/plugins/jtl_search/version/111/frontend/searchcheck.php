<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if (!$oPlugin->getConf('cProjectId')) {
    $oObj_arr = Shop::DB()->query("SELECT * FROM tjtlsearchserverdata", 2);
    foreach ($oObj_arr as $oObj) {
        if (isset($oObj->cKey) && strlen($oObj->cKey) > 0) {
            switch ($oObj->cKey) {
                case 'cProjectId':
                    $oPlugin->setConf('cProjectId', $oObj->cValue);
                    break;

                case 'cAuthHash':
                    $oPlugin->setConf('cAuthHash', $oObj->cValue);
                    break;

                case 'cServerUrl':
                    $oPlugin->setConf('cServerUrl', $oObj->cValue);
                    break;
            }
        }
    }
}

if (strlen($oPlugin->getConf('cProjectId')) > 0 && strlen($oPlugin->getConf('cAuthHash')) > 0 && strlen($oPlugin->getConf('cServerUrl')) > 0) {
    require_once "{$oPlugin->cFrontendPfad}../includes/defines_inc.php";
    require_once "{$oPlugin->cFrontendPfad}../classes/class.JtlSearch.php";

    if (isset($_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch) && isset($_SESSION['ExtendedJTLSearch']->nCreateTime) &&
        ($_SESSION['ExtendedJTLSearch']->nCreateTime + 300) > time() && $_SESSION['cISOSprache'] == $oPlugin->getConf('cCurrentISO')
    ) {
        $args_arr['bExtendedJTLSearch'] = $_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch;
    } else {
        $return = JtlSearch::doCheck(
            $_SESSION['Kundengruppe']->kKundengruppe,
            $_SESSION['cISOSprache'],
            $_SESSION['Waehrung']->cISO,
            $oPlugin->getConf('cProjectId'),
            $oPlugin->getConf('cAuthHash'),
            urldecode($oPlugin->getConf('cServerUrl'))
        );

        $args_arr['bExtendedJTLSearch'] = false;
        if (is_object($return)) {
            $oPlugin->setConf('cCurrentISO', $_SESSION['cISOSprache']);
            // Server change
            $oPlugin->setConf('cServerUrl', $return->_serverurl);
            Shop::DB()->update('tjtlsearchserverdata', 'cKey', 'cServerUrl', (object)['cValue' => $return->_serverurl]);

            if ($return->_code == 3) {
                $args_arr['bExtendedJTLSearch'] = true;
            }
        } else {
            $args_arr['bExtendedJTLSearch'] = $return;
        }

        // Save state into session
        if (!isset($_SESSION['ExtendedJTLSearch'])) {
            $_SESSION['ExtendedJTLSearch']                     = new stdClass();
            $_SESSION['ExtendedJTLSearch']->cQueryTracking_arr = [];
        }

        $_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch = $args_arr['bExtendedJTLSearch'];
        $_SESSION['ExtendedJTLSearch']->nCreateTime        = time();
    }
}
