<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface IExtensionPoint
 */
interface IExtensionPoint
{
    /**
     * @param int $kInitial
     * @return mixed
     */
    public function init($kInitial);
}
