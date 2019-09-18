<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface PluginLizenz
 */
interface PluginLizenz
{
    /**
     * @param string $cLicence
     * @return mixed
     */
    public function checkLicence($cLicence);
}
