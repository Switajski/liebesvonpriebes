<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cOrdner
 * @param string $eTyp
 * @return mixed
 */
function __switchTemplate($cOrdner, $eTyp = 'standard')
{
    $cOrdner   = Shop::DB()->escape($cOrdner);
    $oTemplate = Template::getInstance();
    $bCheck    = $oTemplate->setTemplate($cOrdner, $eTyp);
    if ($bCheck) {
        unset($_SESSION['cTemplate'], $_SESSION['template']);
    }

    return $bCheck;
}
