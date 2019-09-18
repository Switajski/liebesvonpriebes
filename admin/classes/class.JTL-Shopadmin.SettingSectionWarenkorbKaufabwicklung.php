<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SettingSectionWarenkorbKaufabwicklung
 */
class SettingSectionWarenkorbKaufabwicklung extends SettingSection
{
    public function __construct()
    {
        $this->hasSectionMarkup = true;
    }

    /**
     * @return string
     */
    public function getSectionMarkup()
    {
        return Shop::Smarty()->fetch('tpl_inc/settingsection_warenkorb.tpl');
    }
}
