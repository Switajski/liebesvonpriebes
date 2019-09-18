<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kZahlungsart
 * @return array
 */
function getNames($kZahlungsart)
{
    $namen = [];
    if (!$kZahlungsart) {
        return $namen;
    }
    $zanamen = Shop::DB()->selectAll('tzahlungsartsprache', 'kZahlungsart', (int)$kZahlungsart);
    $zCount  = count($zanamen);
    for ($i = 0; $i < $zCount; $i++) {
        $namen[$zanamen[$i]->cISOSprache] = $zanamen[$i]->cName;
    }

    return $namen;
}

/**
 * @param int $kZahlungsart
 * @return array
 */
function getshippingTimeNames($kZahlungsart)
{
    $namen = [];
    if (!$kZahlungsart) {
        return $namen;
    }
    $zanamen = Shop::DB()->selectAll('tzahlungsartsprache', 'kZahlungsart', (int)$kZahlungsart);
    $zCount  = count($zanamen);
    for ($i = 0; $i < $zCount; $i++) {
        $namen[$zanamen[$i]->cISOSprache] = $zanamen[$i]->cGebuehrname;
    }

    return $namen;
}

/**
 * @param int $kZahlungsart
 * @return array
 */
function getHinweisTexte($kZahlungsart)
{
    $cHinweisTexte_arr = [];
    if (!$kZahlungsart) {
        return $cHinweisTexte_arr;
    }
    $oZahlungsartSprache_arr = Shop::DB()->selectAll('tzahlungsartsprache', 'kZahlungsart', (int)$kZahlungsart);
    if (is_array($oZahlungsartSprache_arr) && count($oZahlungsartSprache_arr) > 0) {
        foreach ($oZahlungsartSprache_arr as $oZahlungsartSprache) {
            $cHinweisTexte_arr[$oZahlungsartSprache->cISOSprache] = $oZahlungsartSprache->cHinweisText;
        }
    }

    return $cHinweisTexte_arr;
}

/**
 * @param int $kZahlungsart
 * @return array
 */
function getHinweisTexteShop($kZahlungsart)
{
    $cHinweisTexte_arr = [];
    if (!$kZahlungsart) {
        return $cHinweisTexte_arr;
    }
    $oZahlungsartSprache_arr = Shop::DB()->selectAll('tzahlungsartsprache', 'kZahlungsart', (int)$kZahlungsart);
    if (is_array($oZahlungsartSprache_arr) && count($oZahlungsartSprache_arr) > 0) {
        foreach ($oZahlungsartSprache_arr as $oZahlungsartSprache) {
            $cHinweisTexte_arr[$oZahlungsartSprache->cISOSprache] = $oZahlungsartSprache->cHinweisTextShop;
        }
    }

    return $cHinweisTexte_arr;
}

/**
 * @param Zahlungsart $zahlungsart
 * @return array
 */
function getGesetzteKundengruppen($zahlungsart)
{
    $ret = [];
    if (!isset($zahlungsart->cKundengruppen) || !$zahlungsart->cKundengruppen) {
        $ret[0] = true;

        return $ret;
    }
    $kdgrp = explode(';', $zahlungsart->cKundengruppen);
    foreach ($kdgrp as $kKundengruppe) {
        $ret[$kKundengruppe] = true;
    }

    return $ret;
}

/**
 * @param string $cSearch
 * @return array $allShippingsByName
 */
function getPaymentMethodsByName($cSearch)
{
    // Einstellungen kommagetrennt?
    $cSearch_arr             = explode(',', $cSearch);
    $allPaymentMethodsByName = [];
    foreach ($cSearch_arr as $cSearchPos) {
        // Leerzeichen löschen
        trim($cSearchPos);
        // Nur Eingaben mit mehr als 2 Zeichen
        if (strlen($cSearchPos) > 2) {
            $paymentMethodsByName_arr = Shop::DB()->query(
                "SELECT za.kZahlungsart, za.cName
                    FROM tzahlungsart AS za
                    LEFT JOIN tzahlungsartsprache AS zs ON zs.kZahlungsart = za.kZahlungsart
                        AND zs.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%'
                    WHERE za.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%' 
                    OR zs.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%'", 2
            );
            // Berücksichtige keine fehlerhaften Eingaben
            if (!empty($paymentMethodsByName_arr)) {
                if (count($paymentMethodsByName_arr) > 1) {
                    foreach ($paymentMethodsByName_arr as $paymentMethodByName) {
                        $allPaymentMethodsByName[$paymentMethodByName->kZahlungsart] = $paymentMethodByName;
                    }
                } else {
                    $allPaymentMethodsByName[$paymentMethodsByName_arr[0]->kZahlungsart] = $paymentMethodsByName_arr[0];
                }
            }
        }
    }

    return $allPaymentMethodsByName;
}
