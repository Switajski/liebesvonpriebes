<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kSlider
 * @return mixed
 */
function holeExtension($kSlider)
{
    return Shop::DB()->select('textensionpoint', 'cClass', 'Slider', 'kInitial', (int)$kSlider);
}
