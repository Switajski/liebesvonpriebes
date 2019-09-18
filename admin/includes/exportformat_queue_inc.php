<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array|bool
 */
function holeExportformatCron()
{
    $oExportformatCron_arr = Shop::DB()->query(
        "SELECT texportformat.*, tcron.kCron, tcron.nAlleXStd, tcron.dStart, 
            DATE_FORMAT(tcron.dStart, '%d.%m.%Y %H:%i') AS dStart_de, tcron.dLetzterStart, 
            DATE_FORMAT(tcron.dLetzterStart, '%d.%m.%Y %H:%i') AS dLetzterStart_de,
            DATE_FORMAT(DATE_ADD(tcron.dLetzterStart, INTERVAL tcron.nAlleXStd HOUR), '%d.%m.%Y %H:%i') 
            AS dNaechsterStart_de
            FROM texportformat
            JOIN tcron ON tcron.cJobArt = 'exportformat'
                AND tcron.kKey = texportformat.kExportformat
            ORDER BY tcron.dStart DESC", 2
    );

    if (is_array($oExportformatCron_arr) && count($oExportformatCron_arr) > 0) {
        foreach ($oExportformatCron_arr as $i => $oExportformatCron) {
            $oExportformatCron_arr[$i]->cAlleXStdToDays = rechneUmAlleXStunden($oExportformatCron->nAlleXStd);
            $oExportformatCron_arr[$i]->Sprache         = Shop::DB()->select(
                'tsprache',
                'kSprache',
                (int)$oExportformatCron->kSprache
            );
            $oExportformatCron_arr[$i]->Waehrung        = Shop::DB()->select(
                'twaehrung',
                'kWaehrung',
                (int)$oExportformatCron->kWaehrung
            );
            $oExportformatCron_arr[$i]->Kundengruppe    = Shop::DB()->select(
                'tkundengruppe',
                'kKundengruppe',
                (int)$oExportformatCron->kKundengruppe
            );
            $oExportformatCron_arr[$i]->oJobQueue       = Shop::DB()->query(
                "SELECT *, DATE_FORMAT(dZuletztGelaufen, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_de 
                    FROM tjobqueue 
                    WHERE kCron = " . (int)$oExportformatCron->kCron, 1
            );
            $oExportformatCron_arr[$i]->nAnzahlArtikel       = holeMaxExportArtikelAnzahl($oExportformatCron);
            $oExportformatCron_arr[$i]->nAnzahlArtikelYatego = Shop::DB()->query(
                "SELECT count(*) AS nAnzahl 
                    FROM tartikel 
                    JOIN tartikelattribut 
                        ON tartikelattribut.kArtikel = tartikel.kArtikel 
                    WHERE tartikelattribut.cName = 'yategokat'", 1
            );
        }

        return $oExportformatCron_arr;
    }

    return false;
}

/**
 * @param int $kCron
 * @return int|object
 */
function holeCron($kCron)
{
    $kCron = (int)$kCron;
    if ($kCron > 0) {
        $oCron = Shop::DB()->query(
            "SELECT *, DATE_FORMAT(tcron.dStart, '%d.%m.%Y %H:%i') AS dStart_de
                FROM tcron
                WHERE kCron = " . $kCron, 1
        );

        if (!empty($oCron->kCron) && $oCron->kCron > 0) {
            return $oCron;
        }
    }

    return 0;
}

/**
 * @param int $nAlleXStd
 * @return bool|string
 */
function rechneUmAlleXStunden($nAlleXStd)
{
    if ($nAlleXStd > 0) {
        // nAlleXStd umrechnen
        if ($nAlleXStd > 24) {
            $nAlleXStd = round($nAlleXStd / 24);
            if ($nAlleXStd >= 365) {
                $nAlleXStd /= 365;
                if ($nAlleXStd == 1) {
                    $nAlleXStd .= ' Jahr';
                } else {
                    $nAlleXStd .= ' Jahre';
                }
            } else {
                if ($nAlleXStd == 1) {
                    $nAlleXStd .= ' Tag';
                } else {
                    $nAlleXStd .= ' Tage';
                }
            }
        } else {
            if ($nAlleXStd > 1) {
                $nAlleXStd .= ' Stunden';
            } else {
                $nAlleXStd .= ' Stunde';
            }
        }

        return $nAlleXStd;
    }

    return false;
}

/**
 * @return bool
 */
function holeAlleExportformate()
{
    $oExportformat_arr = Shop::DB()->selectAll('texportformat', [], [], '*', 'cName, kSprache, kKundengruppe, kWaehrung');
    if (is_array($oExportformat_arr) && count($oExportformat_arr) > 0) {
        foreach ($oExportformat_arr as $i => $oExportformat) {
            $oExportformat_arr[$i]->Sprache      = Shop::DB()->select('tsprache', 'kSprache', (int)$oExportformat->kSprache);
            $oExportformat_arr[$i]->Waehrung     = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$oExportformat->kWaehrung);
            $oExportformat_arr[$i]->Kundengruppe = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$oExportformat->kKundengruppe);
        }

        return $oExportformat_arr;
    }

    return false;
}

/**
 * @param int    $kExportformat
 * @param string $dStart
 * @param int    $nAlleXStunden
 * @param int    $kCron
 * @return int
 */
function erstelleExportformatCron($kExportformat, $dStart, $nAlleXStunden, $kCron = 0)
{
    $kExportformat = (int)$kExportformat;
    $nAlleXStunden = (int)$nAlleXStunden;
    $kCron         = (int)$kCron;
    if ($kExportformat > 0 && $nAlleXStunden >= 1 && dStartPruefen($dStart)) {
        if ($kCron > 0) {
            // Editieren
            Shop::DB()->query(
                "DELETE tcron, tjobqueue
                    FROM tcron
                    LEFT JOIN tjobqueue ON tjobqueue.kCron = tcron.kCron
                    WHERE tcron.kCron = " . $kCron, 4
            );
            $oCron = new Cron(
                $kCron,
                $kExportformat,
                $nAlleXStunden,
                $dStart . '_' . $kExportformat,
                'exportformat',
                'texportformat',
                'kExportformat',
                baueENGDate($dStart),
                baueENGDate($dStart, 1)
            );
            $oCron->speicherInDB();

            return 1;
        }
        // Pruefe ob Exportformat nicht bereits vorhanden
        $oCron = Shop::DB()->select('tcron', 'cKey', 'kExportformat', 'kKey', $kExportformat);
        if (isset($oCron->kCron) && $oCron->kCron > 0) {
            return -1;
        }
        $oCron = new Cron(
            0,
            $kExportformat,
            $nAlleXStunden,
            $dStart . '_' . $kExportformat,
            'exportformat',
            'texportformat',
            'kExportformat',
            baueENGDate($dStart),
            baueENGDate($dStart, 1)
        );
        $oCron->speicherInDB();

        return 1;
    }

    return 0;
}

/**
 * @param string $dStart
 * @return bool
 */
function dStartPruefen($dStart)
{
    if (preg_match('/^([0-3]{1}[0-9]{1}[.]{1}[0-1]{1}[0-9]{1}[.]{1}[0-9]{4}[ ]{1}[0-2]{1}[0-9]{1}[:]{1}[0-6]{1}[0-9]{1})/', $dStart)) {
        return true;
    }

    return false;
}

/**
 * @param string $dStart
 * @param int    $bZeit
 * @return string
 */
function baueENGDate($dStart, $bZeit = 0)
{
    list($dDatum, $dZeit)        = explode(' ', $dStart);
    list($nTag, $nMonat, $nJahr) = explode('.', $dDatum);

    if ($bZeit) {
        return $dZeit;
    }

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $dZeit;
}

/**
 * @param array $kCron_arr
 * @return bool
 */
function loescheExportformatCron($kCron_arr)
{
    if (is_array($kCron_arr) && count($kCron_arr) > 0) {
        foreach ($kCron_arr as $kCron) {
            $kCron = (int)$kCron;
            Shop::DB()->delete('tjobqueue', 'kCron', $kCron);
            Shop::DB()->delete('tcron', 'kCron', $kCron);
        }

        return true;
    }

    return false;
}

/**
 * @param int $nStunden
 * @return array|bool
 */
function holeExportformatQueueBearbeitet($nStunden)
{
    if (!$nStunden) {
        $nStunden = 24;
    } else {
        $nStunden = (int)$nStunden;
    }
    $kSprache = isset($_SESSION['kSprache']) ? (int)$_SESSION['kSprache'] : null;
    if (!$kSprache) {
        $oSpracheTMP = Shop::DB()->select('tsprache', 'cShopStandard', 'Y');
        if (isset($oSpracheTMP->kSprache) && $oSpracheTMP->kSprache > 0) {
            $kSprache = (int)$oSpracheTMP->kSprache;
        } else {
            return false;
        }
    }

    $oExportformatQueueBearbeitet = Shop::DB()->query(
        "SELECT texportformat.cName, texportformat.cDateiname, texportformatqueuebearbeitet.*, 
            DATE_FORMAT(texportformatqueuebearbeitet.dZuletztGelaufen, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_DE, 
            tsprache.cNameDeutsch AS cNameSprache, tkundengruppe.cName AS cNameKundengruppe, 
            twaehrung.cName AS cNameWaehrung
            FROM texportformatqueuebearbeitet
            JOIN texportformat 
                ON texportformat.kExportformat = texportformatqueuebearbeitet.kExportformat
                AND texportformat.kSprache = " . $kSprache . "
            JOIN tsprache 
                    ON tsprache.kSprache = texportformat.kSprache
            JOIN tkundengruppe 
                    ON tkundengruppe.kKundengruppe = texportformat.kKundengruppe
            JOIN twaehrung 
                    ON twaehrung.kWaehrung = texportformat.kWaehrung
            WHERE DATE_SUB(now(), INTERVAL " . $nStunden . " HOUR) < texportformatqueuebearbeitet.dZuletztGelaufen
            ORDER BY texportformatqueuebearbeitet.dZuletztGelaufen DESC", 2
    );

    return $oExportformatQueueBearbeitet;
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionErstellen(JTLSmarty $smarty, array &$messages)
{
    $smarty->assign('oExportformat_arr', holeAlleExportformate());

    return 'erstellen';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionEditieren(JTLSmarty $smarty, array &$messages)
{
    $kCron = verifyGPCDataInteger('kCron');
    $oCron = $kCron > 0 ? holeCron($kCron) : 0;

    if (is_object($oCron) && $oCron->kCron > 0) {
        $step = 'erstellen';
        $smarty->assign('oCron', $oCron)
               ->assign('oExportformat_arr', holeAlleExportformate());
    } else {
        $messages['error'] .= 'Fehler: Bitte w&auml;hlen Sie eine g&uuml;ltige Warteschlange aus.';
        $step               = 'uebersicht';
    }

    return $step;
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionLoeschen(JTLSmarty $smarty, array &$messages)
{
    $kCron_arr = $_POST['kCron'];

    if (is_array($kCron_arr) && count($kCron_arr) > 0) {
        if (loescheExportformatCron($kCron_arr)) {
            $messages['notice'] .= 'Ihr ausgew&auml;hlten Warteschlangen wurde erfolgreich gel&ouml;scht.';
        } else {
            $messages['error'] .= 'Fehler: Es ist ein unbekannter Fehler aufgetreten.<br />';
        }
    } else {
        $messages['error'] .= 'Fehler: Bitte w&auml;hlen Sie eine g&uuml;ltige Warteschlange aus.';
    }

    return 'loeschen_result';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionTriggern(JTLSmarty $smarty, array &$messages)
{
    global $bCronManuell, $oCron_arr, $oJobQueue_arr;
    $bCronManuell = true;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'cron_inc.php';

    if (is_array($oCron_arr) && is_array($oJobQueue_arr)) {
        $cronCount = count($oCron_arr);
        $jobCount  = count($oJobQueue_arr);

        if ($cronCount === 0 && $jobCount === 0) {
            $messages['error'] .= 'Es wurde kein Cron-Job gestartet.<br />';
        } elseif ($cronCount === 1) {
            $messages['notice'] .= 'Es wurde ein Cron-Job gestartet.<br />';
        } elseif ($cronCount > 1) {
            $messages['notice'] .= 'Es wurden ' . $cronCount . ' Cron-Jobs gestartet.<br />';
        }

        if ($jobCount === 1) {
            $messages['notice'] .= 'Es wurde eine Job-Queue abgearbeitet.<br />';
        } elseif ($jobCount > 1) {
            $messages['notice'] .= 'Es wurden ' . $jobCount . ' Job-Queues abgearbeitet.<br />';
        }
    }

    return 'triggern';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionFertiggestellt(JTLSmarty $smarty, array &$messages)
{
    $nStunden = verifyGPCDataInteger('nStunden');
    if ($nStunden <= 0) {
        $nStunden = 24;
    }

    $_SESSION['exportformatQueue.nStunden'] = $nStunden;
    $smarty->assign('cTab', 'fertig');

    return 'fertiggestellt';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionErstellenEintragen(JTLSmarty $smarty, array &$messages)
{
    $kExportformat = (int)$_POST['kExportformat'];
    $dStart        = $_POST['dStart'];
    $nAlleXStunden = (!empty($_POST['nAlleXStundenCustom']))
        ? (int)$_POST['nAlleXStundenCustom']
        : (int)$_POST['nAlleXStunden'];
    $oValues       = new stdClass();

    $oValues->kExportformat = $kExportformat;
    $oValues->dStart        = StringHandler::filterXSS($_POST['dStart']);
    $oValues->nAlleXStunden = StringHandler::filterXSS($_POST['nAlleXStunden']);

    if ($kExportformat > 0) {
        if (dStartPruefen($dStart)) {
            if ($nAlleXStunden >= 1) {
                $kCron = (isset($_POST['kCron']) && (int)$_POST['kCron'] > 0)
                    ? (int)$_POST['kCron']
                    : null;
                // Speicher Cron mit Exportformat in Datenbank
                $nStatus = erstelleExportformatCron($kExportformat, $dStart, $nAlleXStunden, $kCron);

                if ($nStatus === 1) {
                    $messages['notice'] .= 'Ihre neue Exportwarteschlange wurde erfolgreich angelegt.';
                    $step                = 'erstellen_success';
                } elseif ($nStatus === -1) {
                    $messages['error'] .= 'Fehler: Das Exportformat ist bereits in der Warteschlange vorhanden.<br />';
                    $step               = 'erstellen';
                } else {
                    $messages['error'] .= 'Fehler: Es ist ein unbekannter Fehler aufgetreten.<br />';
                    $step               = 'erstellen';
                }
            } else { // Alle X Stunden ist entweder leer oder kleiner als 6
                $messages['error'] .= 'Fehler: Bitte geben Sie einen Wert gr&ouml;&szlig;er oder gleich 1 ein.<br />';
                $step               = 'erstellen';
                $smarty->assign('oFehler', $oValues);
            }
        } else { // Kein gueltiges Datum + Uhrzeit
            $messages['error'] .= 'Fehler: Bitte geben Sie ein g&uuml;ltiges Datum ein.<br />';
            $step               = 'erstellen';
            $smarty->assign('oFehler', $oValues);
        }
    } else { // Kein gueltiges Exportformat
        $messages['error'] .= 'Fehler: Bitte w&auml;hlen Sie ein g&uuml;ltiges Exportformat aus.<br />';
        $step               = 'erstellen';
        $smarty->assign('oFehler', $oValues);
    }

    return $step;
}

/**
 * @param string     $cTab
 * @param array|null $messages
 * @return void
 */
function exportformatQueueRedirect($cTab = '', array &$messages = null)
{
    if (isset($messages['notice']) && !empty($messages['notice'])) {
        $_SESSION['exportformatQueue.notice'] = $messages['notice'];
    } else {
        unset($_SESSION['exportformatQueue.notice']);
    }
    if (isset($messages['error']) && !empty($messages['error'])) {
        $_SESSION['exportformatQueue.error'] = $messages['error'];
    } else {
        unset($_SESSION['exportformatQueue.error']);
    }

    $urlParams = null;
    if (!empty($cTab)) {
        $urlParams['tab'] = StringHandler::filterXSS($cTab);
    }

    header('Location: exportformat_queue.php' . (is_array($urlParams) ? '?' . http_build_query($urlParams, '', '&') : ''));
    exit;
}

/**
 * @param string $step
 * @param JTLSmarty $smarty
 * @param array $messages
 * @return void
 */
function exportformatQueueFinalize($step, JTLSmarty $smarty, array &$messages)
{
    if (isset($_SESSION['exportformatQueue.notice'])) {
        $messages['notice'] = $_SESSION['exportformatQueue.notice'];
        unset($_SESSION['exportformatQueue.notice']);
    }
    if (isset($_SESSION['exportformatQueue.error'])) {
        $messages['error'] = $_SESSION['exportformatQueue.error'];
        unset($_SESSION['exportformatQueue.error']);
    }

    switch ($step) {
        case 'uebersicht':
            $nStunden = isset($_SESSION['exportformatQueue.nStunden'])
                ? $_SESSION['exportformatQueue.nStunden']
                : 24;
            $smarty->assign('oExportformatCron_arr', holeExportformatCron())
                   ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($nStunden))
                   ->assign('nStunden', $nStunden);
            break;
        case 'erstellen_success':
        case 'loeschen_result':
        case 'triggern':
            exportformatQueueRedirect('aktiv', $messages);
            break;
        case 'fertiggestellt':
            exportformatQueueRedirect('fertig', $messages);
            break;
        case 'erstellen':
            if (!empty($messages['error'])) {
                $nStunden = isset($_SESSION['exportformatQueue.nStunden'])
                    ? $_SESSION['exportformatQueue.nStunden']
                    : 24;
                $smarty->assign('oExportformatCron_arr', holeExportformatCron())
                       ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($nStunden))
                       ->assign('oExportformat_arr', holeAlleExportformate())
                       ->assign('nStunden', $nStunden);
            }
            break;
        default:
            break;
    }

    $smarty->assign('hinweis', $messages['notice'])
           ->assign('fehler', $messages['error'])
           ->assign('step', $step)
           ->assign('cTab', verifyGPDataString('tab'))
           ->display('exportformat_queue.tpl');
}
