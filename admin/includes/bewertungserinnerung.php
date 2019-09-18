<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

/**
 *
 */
function baueBewertungsErinnerung()
{
    $conf = holeBewertungserinnerungSettings();
    if (is_array($conf) && count($conf) > 0) {
        $kKundengruppen_arr = $conf['bewertungserinnerung_kundengruppen'];
        if ($conf['bewertungserinnerung_nutzen'] === 'Y' && $conf['bewertung_anzeigen'] === 'Y') {
            $nVersandTage = (int)$conf['bewertungserinnerung_versandtage'];
            if ($nVersandTage > 0) {
                // Baue SQL mit allen erlaubten Kundengruppen
                $cSQL = '';
                if (is_array($kKundengruppen_arr) && count($kKundengruppen_arr) > 0) {
                    foreach ($kKundengruppen_arr as $i => $kKundengruppen) {
                        if (is_numeric($kKundengruppen)) {
                            if ($i > 0) {
                                $cSQL .= " OR tkunde.kKundengruppe=" . $kKundengruppen;
                            } else {
                                $cSQL .= " tkunde.kKundengruppe=" . $kKundengruppen;
                            }
                        }
                    }
                } else {
                    // Hole standard Kundengruppe
                    $oKundengruppe = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
                    if ($oKundengruppe->kKundengruppe > 0) {
                        $cSQL = " tkunde.kKundengruppe = " . $oKundengruppe->kKundengruppe;
                    }
                }
                if (strlen($cSQL) > 0) {
                    $nMaxTage = $nVersandTage * 2;
                    if ($nVersandTage == 1) {
                        $nMaxTage = 4;
                    }
                    $cQuery            = "SELECT kBestellung
                            FROM tbestellung
                            JOIN tkunde 
                                ON tkunde.kKunde = tbestellung.kKunde
                            WHERE dVersandDatum != '0000-00-00'
                                AND dVersandDatum IS NOT NULL
                                AND DATE_ADD(dVersandDatum, INTERVAL " . $nVersandTage . " DAY) <= now()
                                AND DATE_ADD(dVersandDatum, INTERVAL " . $nMaxTage . " DAY) > now()
                                AND cStatus = 4
                                AND (" . $cSQL . ")
                                AND (
                                        dBewertungErinnerung IS NULL 
                                        OR dBewertungErinnerung = '0000-00-00 00:00:00'
                                    )";
                    $oBestellungen_arr = Shop::DB()->query($cQuery, 2);
                    if (is_array($oBestellungen_arr) && count($oBestellungen_arr) > 0) {
                        foreach ($oBestellungen_arr as $oBestellungen) {
                            $oBestellung = new Bestellung($oBestellungen->kBestellung);
                            $oBestellung->fuelleBestellung(0);
                            $oKunde            = new Kunde($oBestellung->kKunde);
                            $obj               = new stdClass();
                            $obj->tkunde       = $oKunde;
                            $obj->tbestellung  = $oBestellung;
                            $openReviewPos_arr = [];

                            foreach ($oBestellung->Positionen as $Pos) {
                                if ($Pos->kArtikel > 0) {
                                    $res = Shop::DB()->query(
                                        "SELECT kBewertung
                                            FROM tbewertung
                                            WHERE kArtikel = " . (int)$Pos->kArtikel . "
                                                AND kKunde = " . (int)$oBestellung->kKunde,
                                        1
                                    );
                                    if ($res === false) {
                                        $openReviewPos_arr[] = $Pos;
                                    }
                                }
                            }

                            if (count($openReviewPos_arr) === 0) {
                                continue;
                            }

                            $oBestellung->Positionen = $openReviewPos_arr;

                            Shop::DB()->query(
                                "UPDATE tbestellung
                                    SET dBewertungErinnerung = now()
                                    WHERE kBestellung = " . (int)$oBestellungen->kBestellung, 3
                            );

                            if (Jtllog::doLog(JTLLOG_LEVEL_NOTICE)) {
                                Jtllog::writeLog(
                                    'Kunde und Bestellung aus baueBewertungsErinnerung (Mail versendet): <pre>' .
                                    print_r($obj, true) .
                                    '</pre>',
                                    JTLLOG_LEVEL_NOTICE,
                                    true,
                                    'Bewertungserinnerung',
                                    $oBestellungen->kBestellung
                                );
                            }

                            sendeMail(MAILTEMPLATE_BEWERTUNGERINNERUNG, $obj);
                        }
                    } else {
                        Jtllog::writeLog(
                            "Es wurden keine Bestellungen fuer Bewertungserinnerungen gefunden. SQL:
                            <code>{$cQuery}</code>", JTLLOG_LEVEL_NOTICE, true, 'Bewertungserinnerung'
                        );
                    }
                }
            } else {
                Jtllog::writeLog('Einstellung bewertungserinnerung_versandtage ist 0 oder nicht gesetzt.',
                    JTLLOG_LEVEL_ERROR,
                    true
                );
            }
        } else {
            Jtllog::writeLog('Bewertungserinnerung ist deaktiviert.', JTLLOG_LEVEL_DEBUG, false);
        }
    }
}
