<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $nUnit
 * @return string
 */
function fmtUnit($nUnit)
{
    return sprintf("%0.2f", $nUnit / 100);
}
