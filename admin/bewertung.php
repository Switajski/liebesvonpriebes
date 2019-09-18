<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_VOTESYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_BEWERTUNG]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'bewertung_uebersicht';
$cacheTags     = [];

setzeSprache();

if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
// Bewertung editieren
if (verifyGPCDataInteger('bewertung_editieren') === 1 && validateToken()) {
    if (editiereBewertung($_POST)) {
        $cHinweis .= 'Ihre Bewertung wurde erfolgreich editiert. ';

        if (verifyGPCDataInteger('nFZ') === 1) {
            header('Location: freischalten.php');
            exit();
        }
    } else {
        $step = 'bewertung_editieren';
        $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben. ';
    }
} elseif (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
    Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE]);
    $cHinweis .= saveAdminSectionSettings(CONF_BEWERTUNG, $_POST);
} elseif (isset($_POST['bewertung_nicht_aktiv']) && (int)$_POST['bewertung_nicht_aktiv'] === 1) {
    // Bewertungen aktivieren
    if (isset($_POST['aktivieren'])  && validateToken()) {
        if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            $kArtikel_arr = $_POST['kArtikel'];
            foreach ($_POST['kBewertung'] as $i => $kBewertung) {
                $upd = new stdClass();
                $upd->nAktiv = 1;
                Shop::DB()->update('tbewertung', 'kBewertung', (int)$kBewertung, $upd);
                // Durchschnitt neu berechnen
                aktualisiereDurchschnitt($kArtikel_arr[$i], $Einstellungen['bewertung']['bewertung_freischalten']);
                // Berechnet BewertungGuthabenBonus
                checkeBewertungGuthabenBonus($kBewertung, $Einstellungen);
                $cacheTags[] = $kArtikel_arr[$i];
            }
            // Clear Cache
            array_walk($cacheTags, function(&$i) { $i = CACHING_GROUP_ARTICLE . '_' . $i; });
            Shop::Cache()->flushTags($cacheTags);
            $cHinweis .= count($_POST['kBewertung']) . " Bewertung(en) wurde(n) erfolgreich aktiviert.";
        }
    } elseif (isset($_POST['loeschen']) && validateToken()) { // Bewertungen loeschen
        if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            foreach ($_POST['kBewertung'] as $kBewertung) {
                Shop::DB()->delete('tbewertung', 'kBewertung', (int)$kBewertung);
            }
            $cHinweis .= count($_POST['kBewertung']) . " Bewertung(en) wurde(n) erfolgreich gel&ouml;scht.";
        }
    }
} elseif (isset($_POST['bewertung_aktiv']) && (int)$_POST['bewertung_aktiv'] === 1) {
    if (isset($_POST['cArtNr'])) {
        // Bewertungen holen
        $oBewertungAktiv_arr = Shop::DB()->executeQueryPrepared(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                FROM tbewertung
                LEFT JOIN tartikel 
                    ON tbewertung.kArtikel = tartikel.kArtikel
                WHERE tbewertung.kSprache = :lang
                    AND (tartikel.cArtNr LIKE :cartnr
                        OR tartikel.cName LIKE :cartnr)
                ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC",
            [
                'lang' => (int)$_SESSION['kSprache'],
                'cartnr' => '%' .  $_POST['cArtNr'] . '%'
            ],
            2
        );
        $smarty->assign('cArtNr', StringHandler::filterXSS($_POST['cArtNr']));
    }
    // Bewertungen loeschen
    if (isset($_POST['loeschen']) && is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0 && validateToken()) {
        $kArtikel_arr = $_POST['kArtikel'];
        foreach ($_POST['kBewertung'] as $i => $kBewertung) {
            // Loesche Guthaben aus tbewertungguthabenbonus und aktualisiere tkunde
            BewertungsGuthabenBonusLoeschen($kBewertung);

            Shop::DB()->delete('tbewertung', 'kBewertung', (int)$kBewertung);
            // Durchschnitt neu berechnen
            aktualisiereDurchschnitt($kArtikel_arr[$i], $Einstellungen['bewertung']['bewertung_freischalten']);
            $cacheTags[] = $kArtikel_arr[$i];
        }
        array_walk($cacheTags, function(&$i) { $i = CACHING_GROUP_ARTICLE . '_' . $i; });
        Shop::Cache()->flushTags($cacheTags);

        $cHinweis .= count($_POST['kBewertung']) . ' Bewertung(en) wurde(n) erfolgreich gel&ouml;scht.';
    }
}

if ((isset($_GET['a']) && $_GET['a'] === 'editieren') || $step === 'bewertung_editieren') {
    $step = 'bewertung_editieren';
    $smarty->assign('oBewertung', holeBewertung(verifyGPCDataInteger('kBewertung')));
    if (verifyGPCDataInteger('nFZ') === 1) {
        $smarty->assign('nFZ', 1);
    }
} elseif ($step === 'bewertung_uebersicht') {
    if (isset($_GET['a']) && $_GET['a'] === 'delreply' && validateToken()) {
        removeReply(verifyGPCDataInteger('kBewertung'));
        $cHinweis = 'Antwort zu einer Bewertung wurde entfernt.';
    }

    // Config holen
    $oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_BEWERTUNG, '*', 'nSort');
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$oConfig_arr[$i]->kEinstellungenConf,
                '*', 'nSort'
            );
        } elseif ($oConfig_arr[$i]->cInputTyp === 'listbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
                'tkundengruppe',
                [],
                [],
                'kKundengruppe, cName',
                'cStandard DESC'
            );
        }

        if ($oConfig_arr[$i]->cInputTyp === 'listbox') {
            $oSetValue = Shop::DB()->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_BEWERTUNG, $oConfig_arr[$i]->cWertName],
                'cWert'
            );
            $oConfig_arr[$i]->gesetzterWert = $oSetValue;
        } else {
            $oSetValue = Shop::DB()->select(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_BEWERTUNG, $oConfig_arr[$i]->cWertName]
            );
            $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert) ? $oSetValue->cWert : null;
        }
    }

    // Bewertungen Anzahl holen
    $nBewertungen = (int)Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                AND nAktiv = 0", 1
    )->nAnzahl;
    // Aktive Bewertungen Anzahl holen
    $nBewertungenAktiv = (int)Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                AND nAktiv = 1", 1
    )->nAnzahl;

    // Paginationen
    $oPagiInaktiv = (new Pagination('inactive'))
        ->setItemCount($nBewertungen)
        ->assemble();
    $oPageAktiv   = (new Pagination('active'))
        ->setItemCount($nBewertungenAktiv)
        ->assemble();

    // Bewertungen holen
    $oBewertung_arr = Shop::DB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . "
                AND tbewertung.nAktiv = 0
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC
            LIMIT " . $oPagiInaktiv->getLimitSQL(), 2
    );
    // Aktive Bewertungen
    $oBewertungLetzten50_arr = Shop::DB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . "
                AND tbewertung.nAktiv = 1
            ORDER BY tbewertung.dDatum DESC
            LIMIT " . $oPageAktiv->getLimitSQL(), 2
    );

    $smarty->assign('oPagiInaktiv', $oPagiInaktiv)
        ->assign('oPagiAktiv', $oPageAktiv)
        ->assign('oBewertung_arr', $oBewertung_arr)
        ->assign('oBewertungLetzten50_arr', $oBewertungLetzten50_arr)
        ->assign('oBewertungAktiv_arr', (isset($oBewertungAktiv_arr) ? $oBewertungAktiv_arr : null))
        ->assign('oConfig_arr', $oConfig_arr)
        ->assign('Sprachen', gibAlleSprachen());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('bewertung.tpl');
