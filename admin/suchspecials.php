<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

$oAccount->permission('SETTINGS_SPECIALPRODUCTS_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_KUNDENFELD]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'suchspecials';

setzeSprache();

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}

// Einstellungen
if (verifyGPCDataInteger('einstellungen') === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_SUCHSPECIAL, $_POST);
} elseif (isset($_POST['suchspecials']) && (int)$_POST['suchspecials'] === 1 && validateToken()) {
    // Suchspecials aus der DB holen und in smarty assignen
    $oSuchSpecials_arr       = Shop::DB()->selectAll(
        'tseo',
        ['cKey', 'kSprache'],
        ['suchspecial',
         (int)$_SESSION['kSprache']],
        '*',
        'kKey'
    );
    $oSuchSpecialsTMP_arr    = [];
    $nSuchSpecialsLoesch_arr = [];
    $cBestSellerSeo          = strip_tags(Shop::DB()->escape($_POST['bestseller']));
    $cSonderangeboteSeo      = Shop::DB()->escape($_POST['sonderangebote']);
    $cNeuImSortimentSeo      = strip_tags(Shop::DB()->escape($_POST['neu_im_sortiment']));
    $cTopAngeboteSeo         = strip_tags(Shop::DB()->escape($_POST['top_angebote']));
    $cInKuerzeVerfuegbarSeo  = strip_tags(Shop::DB()->escape($_POST['in_kuerze_verfuegbar']));
    $cTopBewertetSeo         = strip_tags(Shop::DB()->escape($_POST['top_bewertet']));

    // Pruefe BestSeller
    if (strlen($cBestSellerSeo) > 0 && !pruefeSuchspecialSeo(
            $oSuchSpecials_arr,
            $cBestSellerSeo,
            SEARCHSPECIALS_BESTSELLER)
    ) {
        $cBestSellerSeo = checkSeo(getSeo($cBestSellerSeo));

        if ($cBestSellerSeo !== $_POST['bestseller']) {
            $cHinweis .= 'Das BestSeller Seo "' . strip_tags(Shop::DB()->escape($_POST['bestseller'])) .
                '" war bereits vorhanden und wurde in "' . $cBestSellerSeo . '" umbenannt.<br />';
        }

        unset($oBestSeller);
        $oBestSeller       = new stdClass();
        $oBestSeller->kKey = SEARCHSPECIALS_BESTSELLER;
        $oBestSeller->cSeo = $cBestSellerSeo;

        $oSuchSpecialsTMP_arr[] = $oBestSeller;
    } elseif (strlen($cBestSellerSeo) === 0) {
        // cSeo loeschen
        $nSuchSpecialsLoesch_arr[] = SEARCHSPECIALS_BESTSELLER;
    }
    // Pruefe Sonderangebote
    if (strlen($cSonderangeboteSeo) > 0 && !pruefeSuchspecialSeo(
            $oSuchSpecials_arr,
            $cSonderangeboteSeo,
            SEARCHSPECIALS_SPECIALOFFERS
        )
    ) {
        $cSonderangeboteSeo = checkSeo(getSeo($cSonderangeboteSeo));

        if ($cSonderangeboteSeo !== $_POST['sonderangebote']) {
            $cHinweis .= 'Das Sonderangebot Seo "' . strip_tags(Shop::DB()->escape($_POST['sonderangebote'])) .
                '" war bereits vorhanden und wurde auf "' . $cSonderangeboteSeo . '" umbenannt.<br />';
        }

        unset($oSonderangebot);
        $oSonderangebot       = new stdClass();
        $oSonderangebot->kKey = SEARCHSPECIALS_SPECIALOFFERS;
        $oSonderangebot->cSeo = $cSonderangeboteSeo;

        $oSuchSpecialsTMP_arr[] = $oSonderangebot;
    } elseif (strlen($cSonderangeboteSeo) === 0) {
        // cSeo loeschen
        $nSuchSpecialsLoesch_arr[] = SEARCHSPECIALS_SPECIALOFFERS;
    }
    // Pruefe Neu im Sortiment
    if (strlen($cNeuImSortimentSeo) > 0 && !pruefeSuchspecialSeo(
            $oSuchSpecials_arr,
            $cNeuImSortimentSeo,
            SEARCHSPECIALS_NEWPRODUCTS)
    ) {
        $cNeuImSortimentSeo = checkSeo(getSeo($cNeuImSortimentSeo));

        if ($cNeuImSortimentSeo !== $_POST['neu_im_sortiment']) {
            $cHinweis .= 'Das Neu im Sortiment Seo "' . strip_tags(Shop::DB()->escape($_POST['neu_im_sortiment'])) .
                '" war bereits vorhanden und wurde auf "' . $cNeuImSortimentSeo . '" umbenannt.<br />';
        }

        unset($oNeuImSortiment);
        $oNeuImSortiment       = new stdClass();
        $oNeuImSortiment->kKey = SEARCHSPECIALS_NEWPRODUCTS;
        $oNeuImSortiment->cSeo = $cNeuImSortimentSeo;

        $oSuchSpecialsTMP_arr[] = $oNeuImSortiment;
    } elseif (strlen($cNeuImSortimentSeo) === 0) {
        // cSeo leoschen
        $nSuchSpecialsLoesch_arr[] = SEARCHSPECIALS_NEWPRODUCTS;
    }
    // Pruefe Top Angebote
    if (strlen($cTopAngeboteSeo) > 0 && !pruefeSuchspecialSeo(
            $oSuchSpecials_arr,
            $cTopAngeboteSeo,
            SEARCHSPECIALS_TOPOFFERS)
    ) {
        $cTopAngeboteSeo = checkSeo(getSeo($cTopAngeboteSeo));

        if ($cTopAngeboteSeo !== $_POST['top_angebote']) {
            $cHinweis .= 'Das Top Angebote Seo "' . strip_tags(Shop::DB()->escape($_POST['top_angebote'])) .
                '" war bereits vorhanden und wurde auf "' . $cTopAngeboteSeo . '" umbenannt.<br />';
        }

        unset($oTopAngebote);
        $oTopAngebote       = new stdClass();
        $oTopAngebote->kKey = SEARCHSPECIALS_TOPOFFERS;
        $oTopAngebote->cSeo = $cTopAngeboteSeo;

        $oSuchSpecialsTMP_arr[] = $oTopAngebote;
    } elseif (strlen($cTopAngeboteSeo) === 0) {
        // cSeo loeschen
        $nSuchSpecialsLoesch_arr[] = SEARCHSPECIALS_TOPOFFERS;
    }
    // Pruefe In kuerze Verfuegbar
    if (strlen($cInKuerzeVerfuegbarSeo) > 0 && !pruefeSuchspecialSeo(
            $oSuchSpecials_arr,
            $cInKuerzeVerfuegbarSeo,
            SEARCHSPECIALS_UPCOMINGPRODUCTS)
    ) {
        $cInKuerzeVerfuegbarSeo = checkSeo(getSeo($cInKuerzeVerfuegbarSeo));
        if ($cInKuerzeVerfuegbarSeo !== $_POST['in_kuerze_verfuegbar']) {
            $cHinweis .= 'Das In k&uuml;rze Verf&uuml;gbar Seo "' .
                strip_tags(Shop::DB()->escape($_POST['in_kuerze_verfuegbar'])) .
                '" war bereits vorhanden und wurde auf "' . $cInKuerzeVerfuegbarSeo . '" umbenannt.<br />';
        }
        $oInKuerzeVerfuegbar       = new stdClass();
        $oInKuerzeVerfuegbar->kKey = SEARCHSPECIALS_UPCOMINGPRODUCTS;
        $oInKuerzeVerfuegbar->cSeo = $cInKuerzeVerfuegbarSeo;

        $oSuchSpecialsTMP_arr[] = $oInKuerzeVerfuegbar;
    } elseif (strlen($cInKuerzeVerfuegbarSeo) === 0) {
        // cSeo loeschen
        $nSuchSpecialsLoesch_arr[] = SEARCHSPECIALS_UPCOMINGPRODUCTS;
    }
    // Pruefe Top bewertet
    if (strlen($cTopBewertetSeo) > 0 && !pruefeSuchspecialSeo(
            $oSuchSpecials_arr,
            $cTopBewertetSeo,
            SEARCHSPECIALS_TOPREVIEWS)
    ) {
        $cTopBewertetSeo = checkSeo(getSeo($cTopBewertetSeo));

        if ($cTopBewertetSeo !== $_POST['top_bewertet']) {
            $cHinweis .= 'Das In k&uuml;rze Verf&uuml;gbar Seo "' .
                strip_tags(Shop::DB()->escape($_POST['top_bewertet'])) .
                '" war bereits vorhanden und wurde auf "' . $cTopBewertetSeo . '" umbenannt.<br />';
        }
        $oTopBewertet       = new stdClass();
        $oTopBewertet->kKey = SEARCHSPECIALS_TOPREVIEWS;
        $oTopBewertet->cSeo = $cTopBewertetSeo;

        $oSuchSpecialsTMP_arr[] = $oTopBewertet;
    } elseif (strlen($cTopBewertetSeo) === 0) {
        // cSeo loeschen
        $nSuchSpecialsLoesch_arr[] = SEARCHSPECIALS_TOPREVIEWS;
    }
    // tseo speichern
    if (count($oSuchSpecialsTMP_arr) > 0) {
        $cSQL = '';
        foreach ($oSuchSpecialsTMP_arr as $i => $oSuchSpecialsTMP) {
            if ($i > 0) {
                $cSQL .= ', ' . (int)$oSuchSpecialsTMP->kKey;
            } else {
                $cSQL .= (int)$oSuchSpecialsTMP->kKey;
            }
        }
        // Loeschen
        Shop::DB()->query(
            "DELETE FROM tseo
                WHERE cKey = 'suchspecial'
                    AND kSprache = " . (int)$_SESSION['kSprache'] . "
                    AND kKey IN (" . $cSQL . ")", 3
        );

        // Neu Setzen
        foreach ($oSuchSpecialsTMP_arr as $oSuchSpecialsTMP) {
            $oSeo = new stdClass();
            $oSeo->cSeo     = $oSuchSpecialsTMP->cSeo;
            $oSeo->cKey     = 'suchspecial';
            $oSeo->kKey     = $oSuchSpecialsTMP->kKey;
            $oSeo->kSprache = $_SESSION['kSprache'];

            Shop::DB()->insert('tseo', $oSeo);
        }
    }
    // nicht gesetzte seos loeschen
    if (count($nSuchSpecialsLoesch_arr) > 0) {
        $cSQL = '';
        foreach ($nSuchSpecialsLoesch_arr as $i => $nSuchSpecialsLoesch) {
            if ($i > 0) {
                $cSQL .= ', ' . (int)$nSuchSpecialsLoesch;
            } else {
                $cSQL .= (int)$nSuchSpecialsLoesch;
            }
        }

        // Loeschen
        Shop::DB()->query(
            "DELETE FROM tseo
                WHERE cKey = 'suchspecial'
                    AND kSprache = " . (int)$_SESSION['kSprache'] . "
                    AND kKey IN (" . $cSQL . ")", 3
        );
    }

    $cHinweis .= 'Ihre Seos wurden erfolgreich gespeichert bzw. aktualisiert.<br />';
}

// Suchspecials aus der DB holen und in smarty assignen
$oSuchSpecials_arrTMP = Shop::DB()->selectAll(
    'tseo',
    ['cKey', 'kSprache'],
    ['suchspecial', (int)$_SESSION['kSprache']],
    '*',
    'kKey'
);
$oSuchSpecials_arr    = [];
if (is_array($oSuchSpecials_arrTMP) && count($oSuchSpecials_arrTMP) > 0) {
    foreach ($oSuchSpecials_arrTMP as $oSuchSpecials) {
        $oSuchSpecials_arr[$oSuchSpecials->kKey] = $oSuchSpecials->cSeo;
    }
}

// Config holen
$oConfig_arr = Shop::DB()->selectAll(
    'teinstellungenconf',
    'kEinstellungenSektion',
    CONF_SUCHSPECIAL,
    '*',
    'nSort'
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    $oConfig_arr[$i]->ConfWerte     = Shop::DB()->selectAll(
        'teinstellungenconfwerte',
        'kEinstellungenConf',
        (int)$oConfig_arr[$i]->kEinstellungenConf,
        '*',
        'nSort'
    );
    $oSetValue                      = Shop::DB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        (int)$oConfig_arr[$i]->kEinstellungenSektion,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert)
        ? $oSetValue->cWert
        : null;
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oSuchSpecials_arr', $oSuchSpecials_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('suchspecials.tpl');

/**
 * Prueft ob ein bestimmtes Suchspecial Seo schon vorhanden ist
 *
 * @param array  $oSuchSpecials_arr
 * @param string $cSeo
 * @param int    $kKey
 * @return bool
 */
function pruefeSuchspecialSeo($oSuchSpecials_arr, $cSeo, $kKey)
{
    if (is_array($oSuchSpecials_arr) && count($oSuchSpecials_arr) > 0 && strlen($cSeo) && $kKey > 0) {
        foreach ($oSuchSpecials_arr as $oSuchSpecials) {
            if ($oSuchSpecials->kKey == $kKey && $oSuchSpecials->cSeo === $cSeo) {
                return true;
            }
        }
    }

    return false;
}
