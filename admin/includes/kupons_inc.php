<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param array $kKupon_arr
 * @return bool
 */
function loescheKupons($kKupon_arr)
{
    if (is_array($kKupon_arr) && count($kKupon_arr) > 0) {
        $kKupon_arr = array_map('intval', $kKupon_arr);
        $nRows      = Shop::DB()->query(
            "DELETE
                FROM tkupon
                WHERE kKupon IN(" . implode(',', $kKupon_arr) . ")", 3
        );
        Shop::DB()->query(
            "DELETE
                FROM tkuponsprache
                WHERE kKupon IN(" . implode(',', $kKupon_arr) . ")", 3
        );

        return ($nRows >= count($kKupon_arr));
    }

    return false;
}

/**
 * @param int $kKupon
 * @return array - key = lang-iso ; value = localized coupon name
 */
function getCouponNames($kKupon)
{
    $names = [];
    if (!$kKupon) {
        return $names;
    }
    $coupons = Shop::DB()->selectAll('tkuponsprache', 'kKupon', (int)$kKupon);
    foreach ($coupons as $coupon) {
        $names[$coupon->cISOSprache] = $coupon->cName;
    }

    return $names;
}

/**
 * @param string $selHerst
 * @return array
 */
function getManufacturers($selHerst = '')
{
    $selected       = StringHandler::parseSSK($selHerst);
    $hersteller_arr = Shop::DB()->query("SELECT kHersteller, cName FROM thersteller", 2);

    foreach ($hersteller_arr as $i => $hersteller) {
        $oHersteller                  = new Hersteller($hersteller->kHersteller);
        $hersteller_arr[$i]->cName    = $oHersteller->cName;
        $hersteller_arr[$i]->selected = in_array($hersteller_arr[$i]->kHersteller, $selected) ? 1 : 0;
        unset($oHersteller);
    }

    return $hersteller_arr;
}

/**
 * @param string $selKats
 * @param int    $kKategorie
 * @param int    $tiefe
 * @return array
 */
function getCategories($selKats = '', $kKategorie = 0, $tiefe = 0)
{
    $selected = StringHandler::parseSSK($selKats);
    $arr      = [];
    $kats     = Shop::DB()->selectAll('tkategorie', 'kOberKategorie', (int)$kKategorie, 'kKategorie, cName');
    $kCount   = count($kats);
    for ($o = 0; $o < $kCount; $o++) {
        for ($i = 0; $i < $tiefe; $i++) {
            $kats[$o]->cName = '--' . $kats[$o]->cName;
        }
        $kats[$o]->selected = 0;
        if (in_array($kats[$o]->kKategorie, $selected)) {
            $kats[$o]->selected = 1;
        }
        $arr[] = $kats[$o];
        $arr   = array_merge($arr, getCategories($selKats, $kats[$o]->kKategorie, $tiefe + 1));
    }

    return $arr;
}

/**
 * Parse Datumsstring und formatiere ihn im DB-kompatiblen Standardformat
 * 
 * @param string $string
 * @return string
 */
function normalizeDate($string)
{
    if ($string === null || $string === '') {
        return '0000-00-00 00:00:00';
    }

    $date = date_create($string);

    if ($date === false) {
        return $string;
    }

    return $date->format('Y-m-d H:i') . ':00';
}

/**
 * @param string $cKuponTyp
 * @param array $columns
 * @param string $cWhereSQL
 * @param string $cOrderSQL
 * @param string $cLimitSQL
 * @return array|int|object
 */
function getRawCoupons($cKuponTyp = 'standard', $cWhereSQL = '', $cOrderSQL = '', $cLimitSQL = '')
{
    return Shop::DB()->query(
        "SELECT k.*, max(kk.dErstellt) AS dLastUse
            FROM tkupon AS k
            LEFT JOIN tkuponkunde AS kk ON kk.kKupon = k.kKupon
            WHERE cKuponTyp = '" . Shop::DB()->escape($cKuponTyp) . "' " .
            ($cWhereSQL !== '' ? " AND " . $cWhereSQL : "") .
            "GROUP BY k.kKupon" .
            ($cOrderSQL !== '' ? " ORDER BY " . $cOrderSQL : "") .
            ($cLimitSQL !== '' ? " LIMIT " . $cLimitSQL : ""),
        2
    );
}

/**
 * Get instances of existing coupons, each with some enhanced information that can be displayed
 * 
 * @param string $cKuponTyp
 * @param string $cWhereSQL - an SQL WHERE clause (col1 = val1 AND vol2 LIKE ...)
 * @param string $cOrderSQL - an SQL ORDER BY clause (cName DESC)
 * @param string $cLimitSQL - an SQL LIMIT clause  (10,20)
 * @return array
 */
function getCoupons($cKuponTyp = 'standard', $cWhereSQL = '', $cOrderSQL = '', $cLimitSQL = '')
{
    $oKuponDB_arr = getRawCoupons($cKuponTyp, $cWhereSQL, $cOrderSQL, $cLimitSQL);
    $oKupon_arr   = [];

    if (is_array($oKuponDB_arr)) {
        foreach ($oKuponDB_arr as $oKuponDB) {
            $oKupon_arr[] = getCoupon((int)$oKuponDB->kKupon);
        }
    }

    return $oKupon_arr;
}

/**
 * @param string $cKuponTyp
 * @param string $cWhereSQL
 * @return array
 */
function getExportableCoupons($cKuponTyp = 'standard', $cWhereSQL = '')
{
    $coupons = getRawCoupons($cKuponTyp, $cWhereSQL);

    foreach ($coupons as $rawCoupon) {
        foreach (getCouponNames($rawCoupon->kKupon) as $iso => $name) {
            $rawCoupon->{'cName_' . $iso} = $name;
        }
    }

    return $coupons;
}

/**
 * Get an instance of an existing coupon with some enhanced information that can be displayed
 *
 * @param int $kKupon
 * @return Kupon $oKupon
 */
function getCoupon($kKupon)
{
    $oKupon = new Kupon($kKupon);
    augmentCoupon($oKupon);

    return $oKupon;
}

/**
 * Enhance an existing Kupon instance with some extra information that can be displayed
 *
 * @param Kupon $oKupon
 */
function augmentCoupon($oKupon)
{
    $oKupon->cLocalizedValue = $oKupon->cWertTyp === 'festpreis'
        ? gibPreisStringLocalized($oKupon->fWert)
        : '';
    $oKupon->cLocalizedMbw   = isset($oKupon->fMindestbestellwert)
        ? gibPreisStringLocalized($oKupon->fMindestbestellwert)
        : '';
    $oKupon->bOpenEnd        = $oKupon->dGueltigBis === '0000-00-00 00:00:00';

    if (date_create($oKupon->dGueltigAb) === false) {
        $oKupon->cGueltigAbShort = 'ung&uuml;ltig';
        $oKupon->cGueltigAbLong  = 'ung&uuml;ltig';
    } else {
        $oKupon->cGueltigAbShort = date_create($oKupon->dGueltigAb)->format('d.m.Y');
        $oKupon->cGueltigAbLong  = date_create($oKupon->dGueltigAb)->format('d.m.Y H:i');
    }

    if ($oKupon->bOpenEnd) {
        $oKupon->cGueltigBisShort = 'open-end';
        $oKupon->cGueltigBisLong  = 'open-end';
    } elseif (date_create($oKupon->dGueltigBis) === false) {
        $oKupon->cGueltigBisShort = 'ung&uuml;ltig';
        $oKupon->cGueltigBisLong  = 'ung&uuml;ltig';
    } elseif ($oKupon->dGueltigBis === '') {
        $oKupon->cGueltigBisShort = '';
        $oKupon->cGueltigBisLong  = '';
    } else {
        $oKupon->cGueltigBisShort = date_create($oKupon->dGueltigBis)->format('d.m.Y');
        $oKupon->cGueltigBisLong  = date_create($oKupon->dGueltigBis)->format('d.m.Y H:i');
    }

    if ((int)$oKupon->kKundengruppe === -1) {
        $oKupon->cKundengruppe = '';
    } else {
        $oKundengruppe         = Shop::DB()->query("
            SELECT cName 
                FROM tkundengruppe 
                WHERE kKundengruppe = " . $oKupon->kKundengruppe, 1
        );
        $oKupon->cKundengruppe = $oKundengruppe->cName;
    }

    $cArtNr_arr      = StringHandler::parseSSK($oKupon->cArtikel);
    $cHersteller_arr = StringHandler::parseSSK($oKupon->cHersteller);
    $cKategorie_arr  = StringHandler::parseSSK($oKupon->cKategorien);
    $cKunde_arr      = StringHandler::parseSSK($oKupon->cKunden);

    $oKupon->cArtikelInfo    = ($oKupon->cArtikel === '')
        ? ''
        : (string)count($cArtNr_arr);
    $oKupon->cHerstellerInfo = (empty($oKupon->cHersteller) || $oKupon->cHersteller === '-1')
        ? ''
        : (string)count($cHersteller_arr);
    $oKupon->cKategorieInfo  = (empty($oKupon->cKategorien) || $oKupon->cKategorien=== '-1')
        ? ''
        : (string)count($cKategorie_arr);
    $oKupon->cKundenInfo  = (empty($oKupon->cKunden) || $oKupon->cKunden=== '-1')
        ? ''
        : (string)count($cKunde_arr);

    $oMaxErstelltDB   = Shop::DB()->query("
        SELECT max(dErstellt) as dLastUse
            FROM " . ($oKupon->cKuponTyp === 'neukundenkupon'
                ? "tkuponneukunde"
                : "tkuponkunde") . "
            WHERE kKupon = " . (int)$oKupon->kKupon,
        1);
    $oKupon->dLastUse = date_create(is_string($oMaxErstelltDB->dLastUse)
        ? $oMaxErstelltDB->dLastUse
        : '0000-00-00 00:00:00'
    );
}

/**
 * Create a fresh Kupon instance with default values to be edited
 * 
 * @param $cKuponTyp - 'standard', 'versandkupon', 'neukundenkupon'
 * @return Kupon
 */
function createNewCoupon($cKuponTyp)
{
    $oKupon                        = new Kupon();
    $oKupon->cKuponTyp             = $cKuponTyp;
    $oKupon->cName                 = '';
    $oKupon->fWert                 = 0.0;
    $oKupon->cWertTyp              = 'festpreis';
    $oKupon->cZusatzgebuehren      = 'N';
    $oKupon->nGanzenWKRabattieren  = 1;
    $oKupon->kSteuerklasse         = 1;
    $oKupon->fMindestbestellwert   = 0.0;
    $oKupon->cCode                 = '';
    $oKupon->cLieferlaender        = '';
    $oKupon->nVerwendungen         = 0;
    $oKupon->nVerwendungenProKunde = 0;
    $oKupon->cArtikel              = '';
    $oKupon->kKundengruppe         = -1;
    $oKupon->dGueltigAb            = date_create()->format('Y-m-d H:i');
    $oKupon->dGueltigBis           = '';
    $oKupon->cAktiv                = 'Y';
    $oKupon->cHersteller           = '-1';
    $oKupon->cKategorien           = '-1';
    $oKupon->cKunden               = '-1';
    $oKupon->kKupon                = 0;

    augmentCoupon($oKupon);

    return $oKupon;
}

/**
 * Read coupon settings from the edit page form and create a Kupon instance of it
 * 
 * @return Kupon
 */
function createCouponFromInput()
{
    $oKupon                        = new Kupon((int)$_POST['kKuponBearbeiten']);
    $oKupon->cKuponTyp             = $_POST['cKuponTyp'];
    $oKupon->cName                 = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $oKupon->fWert                 = !empty($_POST['fWert']) ? (float)str_replace(',', '.', $_POST['fWert']) : null;
    $oKupon->cWertTyp              = !empty($_POST['cWertTyp']) ? $_POST['cWertTyp'] : null;
    $oKupon->cZusatzgebuehren      = !empty($_POST['cZusatzgebuehren']) ? $_POST['cZusatzgebuehren'] : 'N';
    $oKupon->nGanzenWKRabattieren  = !empty($_POST['nGanzenWKRabattieren']) ? (int)$_POST['nGanzenWKRabattieren'] : 0;
    $oKupon->kSteuerklasse         = !empty($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : null;
    $oKupon->fMindestbestellwert   = (float)str_replace(',', '.', $_POST['fMindestbestellwert']);
    $oKupon->cCode                 = !empty($_POST['cCode']) ? $_POST['cCode'] : '';
    $oKupon->cLieferlaender        = !empty($_POST['cLieferlaender']) ? strtoupper($_POST['cLieferlaender']) : '';
    $oKupon->nVerwendungen         = !empty($_POST['nVerwendungen']) ? (int)$_POST['nVerwendungen'] : 0;
    $oKupon->nVerwendungenProKunde = !empty($_POST['nVerwendungenProKunde']) ? (int)$_POST['nVerwendungenProKunde'] : 0;
    $oKupon->cArtikel              = !empty($_POST['cArtikel']) ? ';' . trim($_POST['cArtikel'], ";\t\n\r") . ';' : '';
    $oKupon->cHersteller           = '-1';
    $oKupon->kKundengruppe         = (int)$_POST['kKundengruppe'];
    $oKupon->dGueltigAb            = normalizeDate(!empty($_POST['dGueltigAb']) ? $_POST['dGueltigAb'] : date_create()->format('Y-m-d H:i') . ':00');
    $oKupon->dGueltigBis           = normalizeDate(!empty($_POST['dGueltigBis']) ? $_POST['dGueltigBis'] : '');
    $oKupon->cAktiv                = isset($_POST['cAktiv']) && $_POST['cAktiv'] === 'Y' ? 'Y' : 'N';
    $oKupon->cKategorien           = '-1';
    if ($oKupon->cKuponTyp !== 'neukundenkupon') {
        $oKupon->cKunden = '-1';
    }
    if (isset($_POST['bOpenEnd']) && $_POST['bOpenEnd'] === 'Y') {
        $oKupon->dGueltigBis = '0000-00-00 00:00:00';
    } elseif (!empty($_POST['dDauerTage'])) {
        $oKupon->dGueltigBis     = '';
        $actualTimestamp         = date_create();
        $actualTimestampEndofDay = date_time_set($actualTimestamp, 23, 59, 59);
        $setDays                 = new DateInterval('P' . $_POST['dDauerTage'] . 'D');
        $oKupon->dGueltigBis     = date_add($actualTimestampEndofDay, $setDays)->format('Y-m-d H:i:s');
    }
    if (!empty($_POST['kHersteller']) &&
        is_array($_POST['kHersteller']) && count($_POST['kHersteller']) > 0 &&
        !in_array('-1', $_POST['kHersteller'])) {
        $oKupon->cHersteller = StringHandler::createSSK($_POST['kHersteller']);
    }
    if (!empty($_POST['kKategorien']) &&
        is_array($_POST['kKategorien']) && count($_POST['kKategorien']) > 0 &&
        !in_array('-1', $_POST['kKategorien'])) {
        $oKupon->cKategorien = StringHandler::createSSK($_POST['kKategorien']);
    }
    if (!empty($_POST['cKunden']) && $_POST['cKunden'] != "-1") {
        $oKupon->cKunden = trim($_POST['cKunden'], ";\t\n\r") . ';';
    }
    if (isset($_POST['couponCreation'])) {
        $massCreationCoupon                  = new stdClass();
        $massCreationCoupon->cActiv          = (!empty($_POST['couponCreation']))
            ? (int)$_POST['couponCreation']
            : 0;
        $massCreationCoupon->numberOfCoupons = ($massCreationCoupon->cActiv === 1 && !empty($_POST['numberOfCoupons']))
            ? (int)$_POST['numberOfCoupons']
            : 2;
        $massCreationCoupon->lowerCase       = ($massCreationCoupon->cActiv === 1 && !empty($_POST['lowerCase']));
        $massCreationCoupon->upperCase       = ($massCreationCoupon->cActiv === 1 && !empty($_POST['upperCase']));
        $massCreationCoupon->numbersHash     = ($massCreationCoupon->cActiv === 1 && !empty($_POST['numbersHash']));
        $massCreationCoupon->hashLength      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['hashLength']))
            ? $_POST['hashLength']
            : 4;
        $massCreationCoupon->prefixHash      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['prefixHash']))
            ? $_POST['prefixHash']
            : '';
        $massCreationCoupon->suffixHash      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['suffixHash']))
            ? $_POST['suffixHash']
            : '';

        $oKupon->massCreationCoupon          = $massCreationCoupon;
    }

    return $oKupon;
}

/**
 * Get the number of existing coupons of type $cKuponTyp
 * 
 * @param string $cKuponTyp
 * @param string $cWhereSQL
 * @return int
 */
function getCouponCount($cKuponTyp = 'standard', $cWhereSQL = '')
{
    $oKuponDB = Shop::DB()->query("
        SELECT count(kKupon) AS count
            FROM tkupon
            WHERE cKuponTyp = '" . $cKuponTyp . "'" .
            ($cWhereSQL !== '' ? " AND " . $cWhereSQL : ""), 1
    );

    return (int)$oKuponDB->count;
}

/**
 * Validates the fields of a given Kupon instance
 * 
 * @param Kupon $oKupon
 * @return array - list of error messages
 */
function validateCoupon($oKupon)
{
    $cFehler_arr = [];

    if ($oKupon->cName === '') {
        $cFehler_arr[] = 'Es wurde kein Kuponname angegeben. Bitte geben Sie einen Namen an!';
    }
    if (($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'neukundenkupon') && $oKupon->fWert < 0) {
        $cFehler_arr[] = 'Bitte geben Sie einen nicht-negativen Kuponwert an!';
    }
    if ($oKupon->fMindestbestellwert < 0) {
        $cFehler_arr[] = 'Bitte geben Sie einen nicht-negativen Mindestbestellwert an!';
    }
    if ($oKupon->cKuponTyp === 'versandkupon' && $oKupon->cLieferlaender === '') {
        $cFehler_arr[] = 'Bitte geben Sie die L&auml;nderk&uuml;rzel (ISO-Codes) unter "Lieferl&auml;nder" an, ' .
            'f&uuml;r die dieser Versandkupon gelten soll!';
    }
    if (isset($oKupon->massCreationCoupon)) {
        $cCodeLength = (int)$oKupon->massCreationCoupon->hashLength
            + (int)strlen($oKupon->massCreationCoupon->prefixHash)
            + (int)strlen($oKupon->massCreationCoupon->suffixHash);
        if ($cCodeLength > 32) {
            $cFehler_arr[] = 'Der zu generiende Code ist l&auml;nger als 32 Zeichen. Bitte verringern Sie die Menge ' .
                'der Zeichen in Pr&auml;fix, Suffix oder geben eine kleinere Zahl bei der L&auml;nge des Zufallcodes an.';
        }
        if ($cCodeLength < 2) {
            $cFehler_arr[] = 'Der zu generiende Code ist k&uuml;rzer als 2 Zeichen. Bitte vergr&ouml;&szlig;ern Sie die Menge ' .
                'der Zeichen in Pr&auml;fix, Suffix oder geben eine gr&ouml;&szlig;ere Zahl bei der L&auml;nge des Zufallcodes an.';
        }
        if (!$oKupon->massCreationCoupon->lowerCase && !$oKupon->massCreationCoupon->upperCase && !$oKupon->massCreationCoupon->numbersHash) {
            $cFehler_arr[] = 'Bitte w&auml;hlen Sie f&uuml;r &quot;Zufallscode mit ...&quot; mindestens eine Option aus!';
        }
    } elseif (strlen($oKupon->cCode) > 32) {
        $cFehler_arr[] = 'Bitte geben Sie einen k&uuml;rzeren Code ein. Es sind maximal 32 Zeichen erlaubt.';
    }
    if ($oKupon->cCode !== '' && !isset($oKupon->massCreationCoupon) &&
        ($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon')
    ) {
        $queryRes = Shop::DB()->executeQueryPrepared(
            "SELECT kKupon
                FROM tkupon
                WHERE cCode = :cCode
                    AND kKupon != :kKupon",
            [ 'cCode' => $oKupon->cCode, 'kKupon' => (int)$oKupon->kKupon ],
            1
        );
        if (is_object($queryRes)) {
            $cFehler_arr[] = 'Der angegeben Kuponcode wird bereits von einem anderen Kupon verwendet. Bitte ' .
                'w&auml;hlen Sie einen anderen Code!';
        }
    }

    $cArtNr_arr = StringHandler::parseSSK($oKupon->cArtikel);
    foreach ($cArtNr_arr as $cArtNr) {
        $res = Shop::DB()->select('tartikel', 'cArtNr', $cArtNr);
        if ($res === null) {
            $cFehler_arr[] = 'Die Artikelnummer "' . $cArtNr . '" geh&ouml;rt zu keinem g&uuml;ltigen Artikel.';
        }
    }

    if ($oKupon->cKuponTyp === 'versandkupon') {
        $cLandISO_arr = StringHandler::parseSSK($oKupon->cLieferlaender);
        foreach ($cLandISO_arr as $cLandISO) {
            $res = Shop::DB()->select('tland', 'cISO', $cLandISO);
            if ($res === null) {
                $cFehler_arr[] = 'Der ISO-Code "' . $cLandISO . '" geh&ouml;rt zu keinem g&uuml;ltigen Land.';
            }
        }
    }

    $dGueltigAb  = date_create($oKupon->dGueltigAb);
    $dGueltigBis = date_create($oKupon->dGueltigBis);

    if ($dGueltigAb === false) {
        $cFehler_arr[] = 'Bitte geben sie den Beginn des G&uuml;ltigkeitszeitraumes ' .
            'im Format (<strong>tt.mm.yyyy ss:mm</strong>) an!';
    }
    if ($dGueltigBis === false) {
        $cFehler_arr[] = 'Bitte geben sie das Ende des G&uuml;ltigkeitszeitraumes ' .
            'im Format (<strong>tt.mm.yyyy ss:mm</strong>) an!';
    }

    $bOpenEnd = $oKupon->dGueltigBis === '0000-00-00 00:00:00';

    if ($dGueltigAb !== false && $dGueltigBis !== false && $dGueltigAb > $dGueltigBis && $bOpenEnd === false) {
        $cFehler_arr[] = 'Das Ende des G&uuml;ltigkeitszeitraumes muss nach dem Beginn des ' .
            'G&uuml;ltigkeitszeitraumes liegen!';
    }

    return $cFehler_arr;
}

/**
 * Save a new or already existing coupon in the DB
 * 
 * @param Kupon $oKupon
 * @param array $oSprache_arr
 * @return int - 0 on failure ; kKupon on success
 */
function saveCoupon($oKupon, $oSprache_arr)
{
    if ((int)$oKupon->kKupon > 0) {
        // vorhandener Kupon
        $res = $oKupon->update() === -1 ? 0 : $oKupon->kKupon;
    } else {
        // neuer Kupon
        $oKupon->nVerwendungenBisher = 0;
        $oKupon->dErstellt           = 'now()';
        if (isset($oKupon->massCreationCoupon)) {
            $massCreationCoupon = $oKupon->massCreationCoupon;
            $oKupon->kKupon     = [];
            unset($oKupon->massCreationCoupon, $_POST['informieren']);
            for ($i = 1; $i <= $massCreationCoupon->numberOfCoupons; $i++) {
                if ($oKupon->cKuponTyp !== 'neukundenkupon') {
                    $oKupon->cCode = $oKupon->generateCode(
                        $massCreationCoupon->hashLength, $massCreationCoupon->lowerCase,
                        $massCreationCoupon->upperCase, $massCreationCoupon->numbersHash,
                        $massCreationCoupon->prefixHash, $massCreationCoupon->suffixHash
                    );
                }
                unset($oKupon->translationList);
                $oKupon->kKupon[] = (int)$oKupon->save();
            }
        } else {
            if ($oKupon->cKuponTyp !== 'neukundenkupon' && $oKupon->cCode === '') {
                $oKupon->cCode = $oKupon->generateCode();
            }
            unset($oKupon->translationList);
            $oKupon->kKupon = (int)$oKupon->save();
        }
        $res = $oKupon->kKupon;
    }

    if ($res > 0) {
        // Kupon-Sprachen aktualisieren
        if (is_array($oKupon->kKupon)) {
            foreach ($oKupon->kKupon as $kKupon) {
                Shop::DB()->delete('tkuponsprache', 'kKupon', $kKupon);

                foreach ($oSprache_arr as $oSprache) {
                    $postVarName       = 'cName_' . $oSprache->cISO;
                    $cKuponSpracheName = isset($_POST[$postVarName]) && $_POST[$postVarName] !== ''
                        ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                        : $oKupon->cName;

                    $kuponSprache              = new stdClass();
                    $kuponSprache->kKupon      = $kKupon;
                    $kuponSprache->cISOSprache = $oSprache->cISO;
                    $kuponSprache->cName       = $cKuponSpracheName;
                    Shop::DB()->insert('tkuponsprache', $kuponSprache);
                }
            }
        } else {
            Shop::DB()->delete('tkuponsprache', 'kKupon', $oKupon->kKupon);

            foreach ($oSprache_arr as $oSprache) {
                $postVarName       = 'cName_' . $oSprache->cISO;
                $cKuponSpracheName = isset($_POST[$postVarName]) && $_POST[$postVarName] !== ''
                    ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                    : $oKupon->cName;

                $kuponSprache              = new stdClass();
                $kuponSprache->kKupon      = $oKupon->kKupon;
                $kuponSprache->cISOSprache = $oSprache->cISO;
                $kuponSprache->cName       = $cKuponSpracheName;
                Shop::DB()->insert('tkuponsprache', $kuponSprache);
            }
        }
    }

    return $res;
}

/**
 * Send notification emails to all customers admitted to this Kupon
 * 
 * @param Kupon $oKupon
 */
function informCouponCustomers($oKupon)
{
    // Augment Coupon
    augmentCoupon($oKupon);
    // Standard-Sprache
    $oStdSprache = Shop::DB()->select('tsprache', 'cShopStandard', 'Y');
    // Standard-Waehrung
    $oStdWaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
    // Artikel Default Optionen
    $defaultOptions = Artikel::getDefaultOptions();
    // lokalisierter Kuponwert und MBW
    $oKupon->cLocalizedWert = $oKupon->cWertTyp === 'festpreis'
        ? gibPreisStringLocalized($oKupon->fWert, $oStdWaehrung, 0)
        : $oKupon->fWert . ' %';
    $oKupon->cLocalizedMBW  = gibPreisStringLocalized($oKupon->fMindestbestellwert, $oStdWaehrung, 0);
    // kKunde-Array aller auserwaehlten Kunden
    $kKunde_arr   = StringHandler::parseSSK($oKupon->cKunden);
    $oKundeDB_arr = Shop::DB()->query(
        "SELECT kKunde
            FROM tkunde
            WHERE TRUE
                " . ((int)$oKupon->kKundengruppe === -1
                    ? "AND TRUE"
                    : "AND kKundengruppe = " . (int)$oKupon->kKundengruppe) . "
                " . ($oKupon->cKunden === '-1'
                    ? "AND TRUE"
                    : "AND kKunde IN (" . implode(',', $kKunde_arr) . ")"),
        2
    );
    // Artikel-Nummern
    $oArtikelDB_arr = [];
    $cArtNr_arr     = StringHandler::parseSSK($oKupon->cArtikel);

    if (count($cArtNr_arr) > 0) {
        $oArtikelDB_arr = Shop::DB()->query("
            SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (" . implode(',', $cArtNr_arr) . ")", 2
        );
    }
    foreach ($oKundeDB_arr as $oKundeDB) {
        $oKunde = new Kunde($oKundeDB->kKunde);
        // Sprache
        $oSprache = Shop::Lang()->getIsoFromLangID($oKunde->kSprache);
        if (!$oSprache) {
            $oSprache = $oStdSprache;
        }
        // Kuponsprache
        $oKuponsprache = Shop::DB()->select(
            'tkuponsprache',
            ['kKupon', 'cISOSprache'],
            [$oKupon->kKupon, $oSprache->cISO]
        );
        // Kategorien
        $oKategorie_arr = [];
        if ($oKupon->cKategorien !== '-1') {
            $kKategorie_arr = array_map('intval', StringHandler::parseSSK($oKupon->cKategorien));
            foreach ($kKategorie_arr as $kKategorie) {
                if ($kKategorie > 0) {
                    $oKategorie       = new Kategorie($kKategorie, $oKunde->kSprache, $oKunde->kKundengruppe);
                    $oKategorie->cURL = $oKategorie->cURLFull;
                    $oKategorie_arr[] = $oKategorie;
                }
            }
        }
        // Artikel
        $oArtikel_arr = [];
        foreach ($oArtikelDB_arr as $oArtikelDB) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel(
                $oArtikelDB->kArtikel,
                $defaultOptions,
                $oKunde->kKundengruppe,
                $oKunde->kSprache,
                true
            );
            $oArtikel_arr[] = $oArtikel;
        }
        // put all together
        $oKupon->Kategorien      = $oKategorie_arr;
        $oKupon->Artikel         = $oArtikel_arr;
        $oKupon->AngezeigterName = $oKuponsprache->cName;
        $obj                     = new stdClass();
        $obj->tkupon             = $oKupon;
        $obj->tkunde             = $oKunde;
        sendeMail(MAILTEMPLATE_KUPON, $obj);
    }
}

/**
 * Set all Coupons with an outdated dGueltigBis to cAktiv = 'N'
 */
function deactivateOutdatedCoupons()
{
    Shop::DB()->query("
        UPDATE tkupon
            SET cAktiv = 'N'
            WHERE dGueltigBis > 0
            AND dGueltigBis <= now()", 10
    );
}

/**
 * Set all Coupons that reached nVerwendungenBisher to nVerwendungen to cAktiv = 'N'
 */
function deactivateExhaustedCoupons()
{
    Shop::DB()->query("
        UPDATE tkupon
            SET cAktiv = 'N'
            WHERE nVerwendungen > 0
            AND nVerwendungenBisher >= nVerwendungen", 10
    );
}
