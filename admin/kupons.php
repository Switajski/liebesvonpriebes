<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$cHinweis         = '';
$cFehler          = '';
$action           = '';
$tab              = 'standard';
$oSprache_arr     = gibAlleSprachen();
$oKupon           = null;
$importDeleteDone = false;

// CSV Import ausgeloest?
$res = handleCsvImportAction('kupon', function ($obj, $importType = 2) {
    global $importDeleteDone;

    if ($importType === 0 && $importDeleteDone === false) {
        Shop::DB()->query('TRUNCATE TABLE tkupon', 3);
        Shop::DB()->query('TRUNCATE TABLE tkuponsprache', 3);
        $importDeleteDone = true;
    }

    $couponNames = [];

    foreach (get_object_vars($obj) as $key => $val) {
        if (strpos($key, 'cName_') === 0) {
            $couponNames[substr($key, 6)] = $val;
            unset($obj->$key);
        }
    }

    if (isset($obj->cCode)
        && Shop::DB()->select('tkupon', 'cCode', $obj->cCode) !== null
    ) {
        return false;
    }

    unset($obj->dLastUse);
    $kKupon = Shop::DB()->insert('tkupon', $obj);

    if ($kKupon === 0) {
        return false;
    }

    foreach ($couponNames as $key => $val) {
        $res = Shop::DB()->insert(
            'tkuponsprache',
            (object)['kKupon' => (int)$kKupon, 'cISOSprache' => $key, 'cName' => $val]
        );

        if ($res === 0) {
            return false;
        }
    }

    return true;
});

if ($res > 0) {
    $cFehler  = 'Konnte CSV-Datei nicht vollst&auml;ndig importieren. ';
    $cFehler .= ($res === 1 ? '1 Zeile ist' : $res . ' Zeilen sind') . ' nicht importierbar.';
} elseif ($res === 0) {
    $cHinweis = 'CSV-Datei wurde erfolgreich importiert.';
}

// Aktion ausgeloest?
if (validateToken()) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'speichern') {
            // Kupon speichern
            $action = 'speichern';
        } elseif ($_POST['action'] === 'loeschen') {
            // Kupons loeschen
            $action = 'loeschen';
        }
    } elseif (isset($_GET['kKupon']) && verifyGPCDataInteger('kKupon') >= 0) {
        // Kupon bearbeiten
        $action = 'bearbeiten';
    }
}

// Aktion behandeln
if ($action === 'bearbeiten') {
    // Kupon bearbeiten
    $kKupon = isset($_GET['kKupon']) ? (int)$_GET['kKupon'] : (int)$_POST['kKuponBearbeiten'];
    if ($kKupon > 0) {
        $oKupon = getCoupon($kKupon);
    } else {
        $oKupon = createNewCoupon($_REQUEST['cKuponTyp']);
    }
} elseif ($action === 'speichern') {
    // Kupon speichern
    $oKupon      = createCouponFromInput();
    $cFehler_arr = validateCoupon($oKupon);
    if (count($cFehler_arr) > 0) {
        // Es gab Fehler bei der Validierung => weiter bearbeiten
        $cFehler = 'Bitte &uuml;berpr&uuml;fen Sie folgende Eingaben:<ul>';

        foreach ($cFehler_arr as $fehler) {
            $cFehler .= '<li>' . $fehler . '</li>';
        }

        $cFehler .= '</ul>';
        $action   = 'bearbeiten';
        augmentCoupon($oKupon);
    } else {
        // Validierung erfolgreich => Kupon speichern
        if (saveCoupon($oKupon, $oSprache_arr) > 0) {
            // erfolgreich gespeichert => evtl. Emails versenden
            if (isset($_POST['informieren']) && $_POST['informieren'] === 'Y' &&
                ($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon') &&
                $oKupon->cAktiv === 'Y') {
                informCouponCustomers($oKupon);
            }
            $cHinweis = 'Der Kupon wurde erfolgreich gespeichert.';
        } else {
            $cFehler = 'Der Kupon konnte nicht gespeichert werden.';
        }
    }
} elseif ($action === 'loeschen') {
    // Kupons loeschen
    if (isset($_POST['kKupon_arr']) && is_array($_POST['kKupon_arr']) && count($_POST['kKupon_arr']) > 0) {
        $kKupon_arr = array_map('intval', $_POST['kKupon_arr']);
        if (loescheKupons($kKupon_arr)) {
            $cHinweis = 'Ihre markierten Kupons wurden erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Fehler: Ein oder mehrere Kupons konnten nicht gel&ouml;scht werden.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Kupon.';
    }
}

// Seite ausgeben
if ($action === 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::DB()->query("SELECT kSteuerklasse, cName FROM tsteuerklasse", 2);
    $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
    $oHersteller_arr   = getManufacturers($oKupon->cHersteller);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $kKunde_arr        = array_filter(
        StringHandler::parseSSK($oKupon->cKunden),
        function ($kKunde) {
            return (int)$kKunde > 0;
        }
    );
    if ($oKupon->kKupon > 0) {
        $oKuponName_arr = getCouponNames((int)$oKupon->kKupon);
    } else {
        $oKuponName_arr = [];
        foreach ($oSprache_arr as $oSprache) {
            $postVarName                     = 'cName_' . $oSprache->cISO;
            $oKuponName_arr[$oSprache->cISO] = (isset($_POST[$postVarName]) && $_POST[$postVarName] !== '')
                ? $_POST[$postVarName]
                : $oKupon->cName;
        }
    }

    $smarty->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
           ->assign('oKundengruppe_arr', $oKundengruppe_arr)
           ->assign('oHersteller_arr', $oHersteller_arr)
           ->assign('oKategorie_arr', $oKategorie_arr)
           ->assign('kKunde_arr', $kKunde_arr)
           ->assign('oSprache_arr', $oSprache_arr)
           ->assign('oKuponName_arr', $oKuponName_arr)
           ->assign('oKupon', $oKupon);
} else {
    // Seite: Uebersicht
    if (hasGPCDataInteger('tab')) {
        $tab = verifyGPDataString('tab');
    } elseif (hasGPCDataInteger('cKuponTyp')) {
        $tab = verifyGPDataString('cKuponTyp');
    }

    deactivateOutdatedCoupons();
    deactivateExhaustedCoupons();

    $oFilterStandard = new Filter('standard');
    $oFilterStandard->addTextfield('Name', 'cName');
    $oFilterStandard->addTextfield('Code', 'cCode');
    $oAktivSelect = $oFilterStandard->addSelectfield('Status', 'cAktiv');
    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', 4);
    $oAktivSelect->addSelectOption('inaktiv', 'N', 4);
    $oFilterStandard->assemble();

    $oFilterVersand = new Filter('versand');
    $oFilterVersand->addTextfield('Name', 'cName');
    $oFilterVersand->addTextfield('Code', 'cCode');
    $oAktivSelect = $oFilterVersand->addSelectfield('Status', 'cAktiv');
    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', 4);
    $oAktivSelect->addSelectOption('inaktiv', 'N', 4);
    $oFilterVersand->assemble();

    $oFilterNeukunden = new Filter('neukunden');
    $oFilterNeukunden->addTextfield('Name', 'cName');
    $oAktivSelect = $oFilterNeukunden->addSelectfield('Status', 'cAktiv');
    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', 4);
    $oAktivSelect->addSelectOption('inaktiv', 'N', 4);
    $oFilterNeukunden->assemble();

    $cSortByOption_arr = [
        ['cName', 'Name'],
        ['cCode', 'Code'],
        ['nVerwendungenBisher', 'Verwendungen'],
        ['dLastUse', 'Zuletzt verwendet']
    ];

    $nKuponStandardCount  = getCouponCount('standard', $oFilterStandard->getWhereSQL());
    $nKuponVersandCount   = getCouponCount('versandkupon', $oFilterVersand->getWhereSQL());
    $nKuponNeukundenCount = getCouponCount('neukundenkupon', $oFilterNeukunden->getWhereSQL());
    $nKuponStandardTotal  = getCouponCount('standard');
    $nKuponVersandTotal   = getCouponCount('versandkupon');
    $nKuponNeukundenTotal = getCouponCount('neukundenkupon');

    handleCsvExportAction(
        'standard', 'standard.csv',
        function () use ($oFilterStandard) {
            return getExportableCoupons('standard', $oFilterStandard->getWhereSQL());
        },
        [], ['kKupon']
    );
    handleCsvExportAction(
        'versandkupon', 'versandkupon.csv',
        function () use ($oFilterVersand) {
            return getExportableCoupons('versandkupon', $oFilterVersand->getWhereSQL());
        },
        [], ['kKupon']
    );
    handleCsvExportAction(
        'neukundenkupon', 'neukundenkupon.csv',
        function () use ($oFilterNeukunden) {
            return getExportableCoupons('neukundenkupon', $oFilterNeukunden->getWhereSQL());
        },
        [], ['kKupon']
    );

    $oPaginationStandard  = (new Pagination('standard'))
        ->setSortByOptions($cSortByOption_arr)
        ->setItemCount($nKuponStandardCount)
        ->assemble();
    $oPaginationVersand   = (new Pagination('versand'))
        ->setSortByOptions($cSortByOption_arr)
        ->setItemCount($nKuponVersandCount)
        ->assemble();
    $oPaginationNeukunden = (new Pagination('neukunden'))
        ->setSortByOptions($cSortByOption_arr)
        ->setItemCount($nKuponNeukundenCount)
        ->assemble();

    $oKuponStandard_arr  = getCoupons(
        'standard', $oFilterStandard->getWhereSQL(), $oPaginationStandard->getOrderSQL(),
        $oPaginationStandard->getLimitSQL()
    );
    $oKuponVersand_arr   = getCoupons(
        'versandkupon', $oFilterVersand->getWhereSQL(), $oPaginationVersand->getOrderSQL(),
        $oPaginationVersand->getLimitSQL()
    );
    $oKuponNeukunden_arr = getCoupons(
        'neukundenkupon', $oFilterNeukunden->getWhereSQL(), $oPaginationNeukunden->getOrderSQL(),
        $oPaginationNeukunden->getLimitSQL()
    );

    $smarty->assign('tab', $tab)
        ->assign('oFilterStandard', $oFilterStandard)
        ->assign('oFilterVersand', $oFilterVersand)
        ->assign('oFilterNeukunden', $oFilterNeukunden)
        ->assign('oPaginationStandard', $oPaginationStandard)
        ->assign('oPaginationVersandkupon', $oPaginationVersand)
        ->assign('oPaginationNeukundenkupon', $oPaginationNeukunden)
        ->assign('oKuponStandard_arr', $oKuponStandard_arr)
        ->assign('oKuponVersandkupon_arr', $oKuponVersand_arr)
        ->assign('oKuponNeukundenkupon_arr', $oKuponNeukunden_arr)
        ->assign('nKuponStandardCount', $nKuponStandardTotal)
        ->assign('nKuponVersandCount', $nKuponVersandTotal)
        ->assign('nKuponNeukundenCount', $nKuponNeukundenTotal);
}

$smarty->assign('action', $action)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->display('kupons.tpl');
