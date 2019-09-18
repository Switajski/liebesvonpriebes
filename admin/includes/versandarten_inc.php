<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisBrutto($fPreis, $fSteuersatz)
{
    return $fPreis > 0
        ? round((float)($fPreis * ((100 + $fSteuersatz) / 100)), 2)
        : 0.0;
}

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisNetto($fPreis, $fSteuersatz)
{
    return $fPreis > 0
        ? round($fPreis * ((100 / (100 + $fSteuersatz)) * 100) / 100, 2)
        : 0.0;
}

/**
 * @param array  $obj_arr
 * @param string $key
 * @return array
 */
function reorganizeObjectArray($obj_arr, $key)
{
    $res = [];
    if (is_array($obj_arr)) {
        foreach ($obj_arr as $obj) {
            $arr  = get_object_vars($obj);
            $keys = array_keys($arr);
            if (in_array($key, $keys)) {
                $res[$obj->$key]           = new stdClass();
                $res[$obj->$key]->checked  = 'checked';
                $res[$obj->$key]->selected = 'selected';
                foreach ($keys as $k) {
                    if ($key != $k) {
                        $res[$obj->$key]->$k = $obj->$k;
                    }
                }
            }
        }
    }

    return $res;
}

/**
 * @param array $arr
 * @return array
 */
function P($arr)
{
    $newArr = [];
    if (is_array($arr)) {
        foreach ($arr as $ele) {
            $newArr = bauePot($newArr, $ele);
        }
    }

    return $newArr;
}

/**
 * @param array  $arr
 * @param string $key
 * @return array
 */
function bauePot($arr, $key)
{
    $cnt = count($arr);
    for ($i = 0; $i < $cnt; ++$i) {
        $obj                 = new stdClass();
        $obj->kVersandklasse = $arr[$i]->kVersandklasse . '-' . $key->kVersandklasse;
        $obj->cName          = $arr[$i]->cName . ', ' . $key->cName;
        $arr[]               = $obj;
    }
    $arr[] = $key;

    return $arr;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassen($cVersandklassen)
{
    if (trim($cVersandklassen) === '-1') {
        return ['alle' => true];
    }
    $gesetzteVK = [];
    $uniqueIDs  = [];
    $cVKarr     = explode(' ', trim($cVersandklassen));
    // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
    foreach ($cVKarr as $idString) {
        // we want the single kVersandklasse IDs to reduce the possible amount of combinations
        foreach (explode('-', $idString) as $kVersandklasse) {
            $uniqueIDs[] = (int)$kVersandklasse;
        }
    }
    $PVersandklassen = P(Shop::DB()->query(
        "SELECT * 
            FROM tversandklasse
            WHERE kVersandklasse IN (" . implode(',', $uniqueIDs) . ")  
            ORDER BY kVersandklasse", 2));
    foreach ($PVersandklassen as $vk) {
        $gesetzteVK[$vk->kVersandklasse] = in_array($vk->kVersandklasse, $cVKarr, true);
    }

    return $gesetzteVK;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassenUebersicht($cVersandklassen)
{
    if (trim($cVersandklassen) === '-1') {
        return ['Alle'];
    }
    $gesetzteVK = [];
    $uniqueIDs  = [];
    $cVKarr     = explode(' ', trim($cVersandklassen));
    // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
    foreach ($cVKarr as $idString) {
        // we want the single kVersandklasse IDs to reduce the possible amount of combinations
        foreach (explode('-', $idString) as $kVersandklasse) {
            $uniqueIDs[] = (int)$kVersandklasse;
        }
    }
    $PVersandklassen = P(Shop::DB()->query(
        "SELECT * 
            FROM tversandklasse 
            WHERE kVersandklasse IN (" . implode(',', $uniqueIDs) . ")
            ORDER BY kVersandklasse", 2)
    );
    foreach ($PVersandklassen as $vk) {
        if (in_array($vk->kVersandklasse, $cVKarr, true)) {
            $gesetzteVK[] = $vk->cName;
        }
    }

    return $gesetzteVK;
}

/**
 * @param string $cKundengruppen
 * @return array
 */
function gibGesetzteKundengruppen($cKundengruppen)
{
    $bGesetzteKG_arr   = [];
    $cKG_arr           = explode(';', trim($cKundengruppen));
    $oKundengruppe_arr = Shop::DB()->query(
        "SELECT kKundengruppe
            FROM tkundengruppe
            ORDER BY kKundengruppe", 2
    );
    foreach ($oKundengruppe_arr as $oKundengruppe) {
        $bGesetzteKG_arr[$oKundengruppe->kKundengruppe] = in_array($oKundengruppe->kKundengruppe, $cKG_arr);
    }
    if ($cKundengruppen === '-1') {
        $bGesetzteKG_arr['alle'] = true;
    }

    return $bGesetzteKG_arr;
}

/**
 * @param int   $kVersandart
 * @param array $oSprache_arr
 * @return array
 */
function getShippingLanguage($kVersandart = 0, $oSprache_arr)
{
    $oVersandartSpracheAssoc_arr = [];
    $oVersandartSprache_arr      = Shop::DB()->selectAll('tversandartsprache', 'kVersandart', (int)$kVersandart);
    if (is_array($oSprache_arr)) {
        foreach ($oSprache_arr as $oSprache) {
            $oVersandartSpracheAssoc_arr[$oSprache->cISO] = new stdClass();
        }
    }
    foreach ($oVersandartSprache_arr as $oVersandartSprache) {
        if (isset($oVersandartSprache->kVersandart) && $oVersandartSprache->kVersandart > 0) {
            $oVersandartSpracheAssoc_arr[$oVersandartSprache->cISOSprache] = $oVersandartSprache;
        }
    }

    return $oVersandartSpracheAssoc_arr;
}

/**
 * @param int $kVersandzuschlag
 * @return array
 */
function getZuschlagNames($kVersandzuschlag)
{
    $names = [];
    if (!$kVersandzuschlag) {
        return $names;
    }
    $zuschlagnamen = Shop::DB()->selectAll('tversandzuschlagsprache', 'kVersandzuschlag', (int)$kVersandzuschlag);
    foreach ($zuschlagnamen as $name) {
        $names[$name->cISOSprache] = $name->cName;
    }

    return $names;
}

/**
 * @param string $cSearch
 * @return array $allShippingsByName
 */
function getShippingByName($cSearch)
{
    // Einstellungen Kommagetrennt?
    $cSearch_arr        = explode(',', $cSearch);
    $allShippingsByName = [];
    foreach ($cSearch_arr as $cSearchPos) {
        trim($cSearchPos);
        if (strlen($cSearchPos) > 2) {
            $shippingByName_arr = Shop::DB()->query(
                "SELECT va.kVersandart, va.cName
                    FROM tversandart AS va
                    LEFT JOIN tversandartsprache AS vs 
                        ON vs.kVersandart = va.kVersandart
                        AND vs.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%'
                    WHERE va.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%' 
                    OR vs.cName LIKE '%" . Shop::DB()->escape($cSearchPos) . "%'", 2
            );
            if (!empty($shippingByName_arr)) {
                if (count($shippingByName_arr) > 1) {
                    foreach ($shippingByName_arr as $shippingByName) {
                        $allShippingsByName[$shippingByName->kVersandart] = $shippingByName;
                    }
                } else {
                    $allShippingsByName[$shippingByName_arr[0]->kVersandart] = $shippingByName_arr[0];
                }
            }
        }
    }

    return $allShippingsByName;
}
