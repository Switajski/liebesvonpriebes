<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * HOOK_CHECKBOX_CLASS_GETCHECKBOXFRONTEND.
 */
if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_set_checkboxes'] !== 'N' && isset($_SESSION['paypalexpress']) && isset($args_arr['bSprache'])) {
    $sql = "SELECT kCheckBox
            FROM tcheckbox
            WHERE cAnzeigeOrt LIKE '%;" . intval(CHECKBOX_ORT_REGISTRIERUNG) . ";%'
               AND cKundengruppe LIKE  '%;" . $_SESSION['Kundengruppe']->kKundengruppe . ";%'
                AND nAktiv = 1
            ORDER BY nSort";

    $oCheckBoxTMP_arr = Shop::DB()->query($sql, 2);
    if (count($oCheckBoxTMP_arr) > 0) {
        foreach ($oCheckBoxTMP_arr as $oCheckBoxTMP) {
            $exists = false;
            foreach ($args_arr['oCheckBox_arr'] as $oCheckBox) {
                if ((int) $oCheckBox->kCheckBox === (int) $oCheckBoxTMP->kCheckBox) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $args_arr['oCheckBox_arr'][] = new CheckBox($oCheckBoxTMP->kCheckBox, $args_arr['bSprache']);
            }
        }
    }
}
