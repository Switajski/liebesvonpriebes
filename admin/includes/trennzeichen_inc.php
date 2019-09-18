<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPostAssoc_arr
 * @return bool
 */
function speicherTrennzeichen($cPostAssoc_arr)
{
    $nEinheit_arr = [JTLSEPARATER_WEIGHT, JTLSEPARATER_AMOUNT];

    foreach ($nEinheit_arr as $nEinheit) {
        if (isset($cPostAssoc_arr['nDezimal_' . $nEinheit], $cPostAssoc_arr['cDezZeichen_' . $nEinheit], $cPostAssoc_arr['cTausenderZeichen_' . $nEinheit])) {
            $oTrennzeichen = new Trennzeichen();
            $oTrennzeichen->setSprache($_SESSION['kSprache'])
                          ->setEinheit($nEinheit)
                          ->setDezimalstellen($cPostAssoc_arr['nDezimal_' . $nEinheit])
                          ->setDezimalZeichen($cPostAssoc_arr['cDezZeichen_' . $nEinheit])
                          ->setTausenderZeichen($cPostAssoc_arr['cTausenderZeichen_' . $nEinheit]);

            // Update
            if (isset($cPostAssoc_arr['kTrennzeichen_' . $nEinheit])) {
                $oTrennzeichen->setTrennzeichen($cPostAssoc_arr['kTrennzeichen_' . $nEinheit])
                              ->update();
            } else { // Speichern
                if (!$oTrennzeichen->save()) {
                    return false;
                }
            }
        }
    }

    Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);

    return true;
}
