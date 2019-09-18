<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('STATS_CAMPAIGN_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kampagne_inc.php';

$cHinweis          = '';
$cFehler           = '';
$kKampagne         = 0;
$kKampagneDef      = 0;
$cStamp            = '';
$step              = 'kampagne_uebersicht';

// Zeitraum
// 1 = Monat
// 2 = Woche
// 3 = Tag
if (!isset($_SESSION['Kampagne'])) {
    $_SESSION['Kampagne'] = new stdClass();
}
if (!isset($_SESSION['Kampagne']->nAnsicht)) {
    $_SESSION['Kampagne']->nAnsicht = 1;
}
if (!isset($_SESSION['Kampagne']->cStamp)) {
    $_SESSION['Kampagne']->cStamp = date('Y-m-d H:i:s');
}
if (!isset($_SESSION['Kampagne']->nSort)) {
    $_SESSION['Kampagne']->nSort = 0;
}
if (!isset($_SESSION['Kampagne']->cSort)) {
    $_SESSION['Kampagne']->cSort = 'DESC';
}

$cDatumNow_arr = gibDatumTeile(date('Y-m-d H:i:s'));
// Tab
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
if (verifyGPCDataInteger('neu') === 1 && validateToken()) {
    $step = 'kampagne_erstellen';
} elseif (verifyGPCDataInteger('editieren') === 1 && verifyGPCDataInteger('kKampagne') > 0 && validateToken()) {
    // Editieren
    $step      = 'kampagne_erstellen';
    $kKampagne = verifyGPCDataInteger('kKampagne');
} elseif (verifyGPCDataInteger('detail') === 1 && verifyGPCDataInteger('kKampagne') > 0 && validateToken()) {
    // Detail
    $step      = 'kampagne_detail';
    $kKampagne = verifyGPCDataInteger('kKampagne');
    // Zeitraum / Ansicht
    setzeDetailZeitraum($cDatumNow_arr);
} elseif (verifyGPCDataInteger('defdetail') === 1 &&
    verifyGPCDataInteger('kKampagne') > 0 &&
    verifyGPCDataInteger('kKampagneDef') > 0 &&
    validateToken()
) { // Def Detail
    $step         = 'kampagne_defdetail';
    $kKampagne    = verifyGPCDataInteger('kKampagne');
    $kKampagneDef = verifyGPCDataInteger('kKampagneDef');
    $cStamp       = verifyGPDataString('cStamp');
} elseif (verifyGPCDataInteger('erstellen_speichern') === 1 && validateToken()) {
    // Speichern / Editieren
    $oKampagne             = new Kampagne();
    $oKampagne->cName      = $_POST['cName'];
    $oKampagne->cParameter = $_POST['cParameter'];
    $oKampagne->cWert      = isset($_POST['cWert']) ? $_POST['cWert'] : null;
    $oKampagne->nDynamisch = isset($_POST['nDynamisch']) ? $_POST['nDynamisch'] : 0;
    $oKampagne->nAktiv     = $_POST['nAktiv'];
    $oKampagne->dErstellt  = 'now()';

    // Editieren
    if (verifyGPCDataInteger('kKampagne') > 0) {
        $oKampagne->kKampagne = verifyGPCDataInteger('kKampagne');
    }

    $nReturnValue = speicherKampagne($oKampagne);

    if ($nReturnValue == 1) {
        $cHinweis = 'Ihre Kampagne wurde erfolgreich gespeichert.';
    } else {
        $cFehler = mappeFehlerCodeSpeichern($nReturnValue);
        $smarty->assign('oKampagne', $oKampagne);
        $step = 'kampagne_erstellen';
    }
} elseif (verifyGPCDataInteger('delete') === 1 && validateToken()) {
    // Loeschen
    if (isset($_POST['kKampagne']) && is_array($_POST['kKampagne']) && count($_POST['kKampagne']) > 0) {
        $nReturnValue = loescheGewaehlteKampagnen($_POST['kKampagne']);

        if ($nReturnValue == 1) {
            $cHinweis = 'Ihre ausgew&auml;hlten Kampagnen wurden erfolgreich gel&ouml;scht.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens eine Kampagne.';
    }
} elseif (verifyGPCDataInteger('nAnsicht') > 0) { // Ansicht
    $_SESSION['Kampagne']->nAnsicht = verifyGPCDataInteger('nAnsicht');
} elseif (verifyGPCDataInteger('nStamp') == -1 || verifyGPCDataInteger('nStamp') === 1) { // Zeitraum
    // Vergangenheit
    if (verifyGPCDataInteger('nStamp') == -1) {
        $_SESSION['Kampagne']->cStamp = gibStamp($_SESSION['Kampagne']->cStamp, -1, $_SESSION['Kampagne']->nAnsicht);
    } elseif (verifyGPCDataInteger('nStamp') === 1) {
        // Zukunft
        $_SESSION['Kampagne']->cStamp = gibStamp($_SESSION['Kampagne']->cStamp, 1, $_SESSION['Kampagne']->nAnsicht);
    }
} elseif (verifyGPCDataInteger('nSort') > 0) { // Sortierung
    // ASC / DESC
    if ($_SESSION['Kampagne']->nSort == verifyGPCDataInteger('nSort')) {
        if ($_SESSION['Kampagne']->cSort === 'ASC') {
            $_SESSION['Kampagne']->cSort = 'DESC';
        } else {
            $_SESSION['Kampagne']->cSort = 'ASC';
        }
    }

    $_SESSION['Kampagne']->nSort = verifyGPCDataInteger('nSort');
}
if ($step === 'kampagne_uebersicht') {
    $oKampagne_arr    = holeAlleKampagnen(true, false);
    $oKampagneDef_arr = holeAlleKampagnenDefinitionen();

    $nGroessterKey = 0;
    if (is_array($oKampagne_arr) && count($oKampagne_arr) > 0) {
        $cMemeber_arr  = array_keys($oKampagne_arr);
        $nGroessterKey = $cMemeber_arr[count($cMemeber_arr) - 1];
    }

    $smarty->assign('nGroessterKey', $nGroessterKey)
           ->assign('oKampagne_arr', $oKampagne_arr)
           ->assign('oKampagneDef_arr', $oKampagneDef_arr)
           ->assign('oKampagneStat_arr', holeKampagneGesamtStats($oKampagne_arr, $oKampagneDef_arr));
} elseif ($step === 'kampagne_erstellen') { // Erstellen / Editieren
    if ($kKampagne > 0) {
        $smarty->assign('oKampagne', holeKampagne($kKampagne));
    }
} elseif ($step === 'kampagne_detail') { // Detailseite
    if ($kKampagne > 0) {
        $oKampagne_arr    = holeAlleKampagnen(true, false);
        $oKampagneDef_arr = holeAlleKampagnenDefinitionen();
        if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
            $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
        }
        $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDef_arr = $oKampagneDef_arr;
        $_SESSION['nDiagrammTyp']                                     = 5;

        $Stats = holeKampagneDetailStats($kKampagne, $oKampagneDef_arr);
        // Highchart
        $Charts = [];
        for ($i = 1; $i <= 10; $i++) {
            $Charts[$i] = PrepareLineChartKamp($Stats, $i);
        }

        $smarty->assign('TypeNames', GetTypes())
               ->assign('Charts', $Charts)
               ->assign('oKampagne', holeKampagne($kKampagne))
               ->assign('oKampagneStat_arr', $Stats)
               ->assign('oKampagne_arr', $oKampagne_arr)
               ->assign('oKampagneDef_arr', $oKampagneDef_arr)
               ->assign('nRand', time());
    }
} elseif ($step === 'kampagne_defdetail') { // DefDetailseite
    if (strlen($cStamp) === 0) {
        $cStamp = checkGesamtStatZeitParam();
    }

    if ($kKampagne > 0 && $kKampagneDef > 0 && strlen($cStamp) > 0) {
        $oKampagneDef = holeKampagneDef($kKampagneDef);
        $cMember_arr  = [];
        $cStampText   = '';
        $cSQLSELECT   = '';
        $cSQLWHERE    = '';
        baueDefDetailSELECTWHERE($cSQLSELECT, $cSQLWHERE, $cStamp);

        $oStats_arr = Shop::DB()->query(
            "SELECT kKampagne, kKampagneDef, kKey " . $cSQLSELECT . "
                FROM tkampagnevorgang
                " . $cSQLWHERE . "
                    AND kKampagne = " . (int)$kKampagne . "
                    AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef, 2
        );

        $oPagiDefDetail = (new Pagination('defdetail'))
            ->setItemCount(count($oStats_arr))
            ->assemble();
        $oKampagneStat_arr = holeKampagneDefDetailStats($kKampagne, $oKampagneDef, $cStamp, $cStampText, $cMember_arr,
            ' LIMIT ' . $oPagiDefDetail->getLimitSQL());

        $smarty->assign('oPagiDefDetail', $oPagiDefDetail)
            ->assign('oKampagne', holeKampagne($kKampagne))
            ->assign('oKampagneStat_arr', $oKampagneStat_arr)
            ->assign('oKampagneDef', $oKampagneDef)
            ->assign('cMember_arr', $cMember_arr)
            ->assign('cStampText', $cStampText)
            ->assign('cStamp', $cStamp)
            ->assign('nGesamtAnzahlDefDetail', count($oStats_arr));
    }
}

$cDatum_arr = gibDatumTeile($_SESSION['Kampagne']->cStamp);
switch ((int)$_SESSION['Kampagne']->nAnsicht) {
    case 1:    // Monat
        $cZeitraum = '01.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' - ' .
            date('t', mktime(0, 0, 0, (int)$cDatum_arr['cMonat'], 1, (int)$cDatum_arr['cJahr'])) .
            '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'];
        $smarty->assign('cZeitraum', $cZeitraum)
               ->assign('cZeitraumParam', base64_encode($cZeitraum));
        break;
    case 2:    // Woche
        $cDate_arr = ermittleDatumWoche($cDatum_arr['cJahr'] . '-' . $cDatum_arr['cMonat'] . '-' . $cDatum_arr['cTag']);
        $cZeitraum = date('d.m.Y', $cDate_arr[0]) . ' - ' . date('d.m.Y', $cDate_arr[1]);
        $smarty->assign('cZeitraum', $cZeitraum)
               ->assign('cZeitraumParam', base64_encode($cZeitraum));
        break;
    case 3:    // Tag
        $cZeitraum = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'];
        $smarty->assign('cZeitraum', $cZeitraum)
               ->assign('cZeitraumParam', base64_encode($cZeitraum));
        break;
}

if ((int)$cDatumNow_arr['cTag'] === (int)$cDatum_arr['cTag'] &&
    (int)$cDatumNow_arr['cMonat'] === (int)$cDatum_arr['cMonat'] &&
    (int)$cDatumNow_arr['cJahr'] === (int)$cDatum_arr['cJahr']
) {
    $smarty->assign('nGreaterNow', 1);
}
$smarty->assign('PFAD_ADMIN', PFAD_ADMIN)
       ->assign('PFAD_TEMPLATES', PFAD_TEMPLATES)
       ->assign('PFAD_GFX', PFAD_GFX)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kampagne.tpl');
