<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_CUSTOMERFIELDS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.PlausiKundenfeld.php';
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_KUNDENFELD]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'uebersicht';

setzeSprache();

// Tabs
$smarty->assign('cTab', (isset($cStep) ? $cStep : null));
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}

// Einstellungen
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_KUNDENFELD, $_POST);
} elseif (isset($_POST['kundenfelder']) && (int)$_POST['kundenfelder'] === 1 && validateToken()) { // Kundenfelder
    if (isset($_POST['loeschen'])) {
        $kKundenfeld_arr = $_POST['kKundenfeld'];

        if (is_array($kKundenfeld_arr) && count($kKundenfeld_arr) > 0) {
            foreach ($kKundenfeld_arr as $kKundenfeld) {
                Shop::DB()->delete('tkundenfeld', 'kKundenfeld', (int)$kKundenfeld);
                Shop::DB()->delete('tkundenfeldwert', 'kKundenfeld', (int)$kKundenfeld);
                Shop::DB()->delete('tkundenattribut', 'kKundenfeld', (int)$kKundenfeld);
            }
            $cHinweis .= "Die ausgew&auml;hlten Kundenfelder wurden erfolgreich gel&ouml;scht.<br />";
        } else {
            $cFehler .= "Fehler: Bitte w&auml;hlen Sie mindestens ein Kundenfeld aus.<br />";
        }
    } elseif (isset($_POST['aktualisieren']) && validateToken()) { // Aktualisieren
        // Kundenfelder auslesen und in Smarty assignen
        $oKundenfeld_arr = Shop::DB()->selectAll('tkundenfeld', 'kSprache', (int)$_SESSION['kSprache'], '*', 'nSort DESC');

        if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
            foreach ($oKundenfeld_arr as $oKundenfeld) {
                $upd = new stdClass();
                $upd->nSort = (int)$_POST['nSort_' . $oKundenfeld->kKundenfeld];
                Shop::DB()->update('tkundenfeld', 'kKundenfeld', $oKundenfeld->kKundenfeld, $upd);
            }
            $cHinweis .= 'Ihre Kundenfelder wurden erfolgreich aktualisiert.';
        }
    } else { // Speichern
        $cName           = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $cWawi           = str_replace(['"',"'"], '',$_POST['cWawi']);
        $cTyp            = $_POST['cTyp'];
        $nSort           = (int)$_POST['nSort'];
        $nPflicht        = $_POST['nPflicht'];
        $nEdit           = $_POST['nEdit'];
        $cWert_arr       = isset($_POST['cWert']) ? $_POST['cWert'] : null;
        $oKundenfeld_arr = [];

        // Plausi
        $oPlausi = new PlausiKundenfeld();
        $oPlausi->setPostVar($_POST);
        $oPlausi->doPlausi($cTyp, verifyGPCDataInteger('kKundenfeld') > 0);

        if (count($oPlausi->getPlausiVar()) === 0) {
            // Update?
            if (isset($_POST['kKundenfeld']) && (int)$_POST['kKundenfeld'] > 0) {
                Shop::DB()->delete('tkundenfeld', 'kKundenfeld', (int)$_POST['kKundenfeld']);
                Shop::DB()->delete('tkundenfeldwert', 'kKundenfeld', (int)$_POST['kKundenfeld']);
                Shop::DB()->delete('tkundenattribut', 'kKundenfeld', (int)$_POST['kKundenfeld']);
            }

            $oKundenfeld              = new stdClass();
            $oKundenfeld->kSprache    = (int)$_SESSION['kSprache'];
            $oKundenfeld->cName       = $cName;
            $oKundenfeld->cWawi       = $cWawi;
            $oKundenfeld->cTyp        = $cTyp;
            $oKundenfeld->nSort       = $nSort;
            $oKundenfeld->nPflicht    = (int)$nPflicht;
            $oKundenfeld->nEditierbar = (int)$nEdit;
            if (isset($_POST['kKundenfeld']) && (int)$_POST['kKundenfeld'] > 0) {
                $oKundenfeld->kKundenfeld = (int)$_POST['kKundenfeld'];
            }

            $kKundenfeld = Shop::DB()->insert('tkundenfeld', $oKundenfeld);
            if (isset($oKundenfeld->kKundenfeld)) {
                $kKundenfeld = $oKundenfeld->kKundenfeld;
            }

            if ($cTyp === 'auswahl' && is_array($cWert_arr) && count($cWert_arr) > 0) {
                foreach ($cWert_arr as $cWert) {
                    unset($oKundenfeldWert);
                    $oKundenfeldWert              = new stdClass();
                    $oKundenfeldWert->kKundenfeld = $kKundenfeld;
                    $oKundenfeldWert->cWert       = $cWert;

                    Shop::DB()->insert('tkundenfeldwert', $oKundenfeldWert);
                }
            }
            $cHinweis .= 'Ihr Kundenfeld wurde erfolgreich gespeichert.<br />';
        } else {
            $cFehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
            $smarty->assign('xPlausiVar_arr', $oPlausi->getPlausiVar())
                   ->assign('xPostVar_arr', $oPlausi->getPostVar())
                   ->assign('kKundenfeld', verifyGPCDataInteger('kKundenfeld'));
        }
    }
} elseif (verifyGPDataString('a') === 'edit') { // Editieren
    $kKundenfeld = verifyGPCDataInteger('kKundenfeld');

    if ($kKundenfeld > 0) {
        $oKundenfeld = Shop::DB()->select('tkundenfeld', 'kKundenfeld', $kKundenfeld);
        if (isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0) {
            $oKundenfeldWert_arr = Shop::DB()->selectAll('tkundenfeldwert', 'kKundenfeld', (int)$kKundenfeld);

            $oKundenfeld->oKundenfeldWert_arr = $oKundenfeldWert_arr;
            $smarty->assign('oKundenfeld', $oKundenfeld);
        }
    }
}

$oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_KUNDENFELD, '*', 'nSort');
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll
        ('teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }

    $oSetValue = Shop::DB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_KUNDENFELD,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert) ? $oSetValue->cWert : null);
}
// Kundenfelder auslesen und in Smarty assignen
$oKundenfeld_arr = Shop::DB()->selectAll('tkundenfeld', 'kSprache', (int)$_SESSION['kSprache'], '*', 'nSort DESC');
if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
    // tkundenfeldwert nachschauen ob dort Werte fuer tkundenfeld enthalten sind
    foreach ($oKundenfeld_arr as $i => $oKundenfeld) {
        if ($oKundenfeld->cTyp === 'auswahl') {
            $oKundenfeldWert_arr = Shop::DB()->selectAll('tkundenfeldwert', 'kKundenfeld', (int)$oKundenfeld->kKundenfeld);
            $oKundenfeld_arr[$i]->oKundenfeldWert_arr = $oKundenfeldWert_arr;
        }
    }
}

$smarty->assign('oKundenfeld_arr', $oKundenfeld_arr)
       ->assign('oConfig_arr', $oConfig_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kundenfeld.tpl');
