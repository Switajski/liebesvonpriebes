<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$oAccount->permission('MODULE_CAC_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_KUNDENWERBENKUNDEN]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'kwk_uebersicht';

setzeSprache();

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
// KwK
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_KUNDENWERBENKUNDEN, $_POST);
}
// KwK
if (verifyGPCDataInteger('KwK') === 1 && validateToken()) {
    // Einladung vom Neukunden loeschen
    if (verifyGPCDataInteger('nichtreggt_loeschen') === 1) {
        $kKundenWerbenKunden_arr = $_POST['kKundenWerbenKunden'];
        if (is_array($kKundenWerbenKunden_arr) && count($kKundenWerbenKunden_arr) > 0) {
            foreach ($kKundenWerbenKunden_arr as $kKundenWerbenKunden) {
                Shop::DB()->delete('tkundenwerbenkunden', 'kKundenWerbenKunden', (int)$kKundenWerbenKunden);
            }
            $cHinweis .= 'Ihre markierten Neukunden wurden erfolgreich gel&ouml;scht.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Neukunden<br />';
        }
    }
}

//
if ($step === 'kwk_uebersicht') {
    // Einstellungen
    $oConfig_arr = Shop::DB()->selectAll(
        'teinstellungenconf',
        'kEinstellungenSektion',
        CONF_KUNDENWERBENKUNDEN,
        '*',
        'nSort'
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$oConfig_arr[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        } elseif ($oConfig_arr[$i]->cInputTyp === 'selectkdngrp') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                "SELECT kKundengruppe, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC", 2
            );
        }

        if ($oConfig_arr[$i]->cInputTyp === 'selectkdngrp') {
            $oSetValue = Shop::DB()->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_KUNDENWERBENKUNDEN,
                 $oConfig_arr[$i]->cWertName]
            );
            $oConfig_arr[$i]->gesetzterWert = $oSetValue;
        } else {
            $oSetValue = Shop::DB()->select(
                'teinstellungen',
                'kEinstellungenSektion',
                CONF_KUNDENWERBENKUNDEN,
                'cName',
                $oConfig_arr[$i]->cWertName
            );
            $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert)
                ? $oSetValue->cWert
                : null;
        }
    }

    // Anzahl
    $oAnzahlReg = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tkundenwerbenkunden
            WHERE nRegistriert = 0", 1
    );
    $oAnzahlNichtReg = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tkundenwerbenkunden
            WHERE nRegistriert = 1", 1
    );
    $oAnzahlPraemie = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tkundenwerbenkundenbonus", 1
    );

    // Paginationen
    $oPagiNichtReg = (new Pagination('nichtreg'))
        ->setItemCount($oAnzahlReg->nAnzahl)
        ->assemble();
    $oPagiReg = (new Pagination('reg'))
        ->setItemCount($oAnzahlNichtReg->nAnzahl)
        ->assemble();
    $oPagiPraemie = (new Pagination('praemie'))
        ->setItemCount($oAnzahlPraemie->nAnzahl)
        ->assemble();

    // tkundenwerbenkunden Nicht registrierte Kunden
    $oKwKNichtReg_arr = Shop::DB()->query(
        "SELECT tkundenwerbenkunden.*, tkunde.kKunde AS kKundeBestand, tkunde.cMail, 
            DATE_FORMAT(tkundenwerbenkunden.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            tkunde.cVorname AS cBestandVorname, tkunde.cNachname AS cBestandNachname
            FROM tkundenwerbenkunden
            JOIN tkunde 
                ON tkunde.kKunde = tkundenwerbenkunden.kKunde
            WHERE tkundenwerbenkunden.nRegistriert = 0
            ORDER BY tkundenwerbenkunden.dErstellt DESC 
            LIMIT " . $oPagiNichtReg->getLimitSQL(), 2
    );
    if (is_array($oKwKNichtReg_arr) && count($oKwKNichtReg_arr) > 0) {
        foreach ($oKwKNichtReg_arr as $i => $oKwKNichtReg) {
            $oKunde = new Kunde($oKwKNichtReg->kKundeBestand);

            $oKwKNichtReg_arr[$i]->cBestandNachname = $oKunde->cNachname;
        }
    }
    // tkundenwerbenkunden registrierte Kunden
    $oKwKReg_arr = Shop::DB()->query(
        "SELECT tkundenwerbenkunden.*, 
            DATE_FORMAT(tkundenwerbenkunden.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dBestandErstellt_de
            FROM tkundenwerbenkunden
            JOIN tkunde 
                ON tkunde.cMail = tkundenwerbenkunden.cEmail
            WHERE tkundenwerbenkunden.nRegistriert = 1
            ORDER BY tkundenwerbenkunden.dErstellt DESC 
            LIMIT " . $oPagiReg->getLimitSQL(), 2
    );
    if (is_array($oKwKReg_arr) && count($oKwKReg_arr) > 0) {
        foreach ($oKwKReg_arr as $i => $oKwKReg) {
            $oBestandsKunde = new Kunde($oKwKReg->kKunde);

            $oKwKReg_arr[$i]->cBestandVorname  = $oBestandsKunde->cVorname;
            $oKwKReg_arr[$i]->cBestandNachname = $oBestandsKunde->cNachname;
            $oKwKReg_arr[$i]->cMail            = $oBestandsKunde->cMail;
        }
    }
    // letzten 100 Bestandskunden die Guthaben erhalten haben
    $oKwKBestandBonus_arr = Shop::DB()->query(
        "SELECT tkundenwerbenkundenbonus.*, tkunde.kKunde AS kKundeBestand, tkunde.cMail, 
            DATE_FORMAT(tkundenwerbenkundenbonus.dErhalten, '%d.%m.%Y %H:%i') AS dErhalten_de,
            tkunde.cVorname AS cBestandVorname, tkunde.cNachname AS cBestandNachname
            FROM tkundenwerbenkundenbonus
            JOIN tkunde 
                ON tkunde.kKunde = tkundenwerbenkundenbonus.kKunde
            ORDER BY dErhalten DESC 
            LIMIT " . $oPagiPraemie->getLimitSQL(), 2
    );

    if (is_array($oKwKBestandBonus_arr) && count($oKwKBestandBonus_arr) > 0) {
        foreach ($oKwKBestandBonus_arr as $i => $oKwKBestandBonus) {
            $oKunde = new Kunde($oKwKBestandBonus->kKundeBestand);

            $oKwKBestandBonus_arr[$i]->cBestandNachname = $oKunde->cNachname;
        }
    }

    $smarty->assign('oConfig_arr', $oConfig_arr)
        ->assign('oKwKNichtReg_arr', $oKwKNichtReg_arr)
        ->assign('oKwKReg_arr', $oKwKReg_arr)
        ->assign('oKwKBestandBonus_arr', $oKwKBestandBonus_arr)
        ->assign('oPagiNichtReg', $oPagiNichtReg)
        ->assign('oPagiReg', $oPagiReg)
        ->assign('oPagiPraemie', $oPagiPraemie);
}
$smarty->assign('Sprachen', gibAlleSprachen())
       ->assign('kSprache', $_SESSION['kSprache'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kundenwerbenkunden.tpl');
