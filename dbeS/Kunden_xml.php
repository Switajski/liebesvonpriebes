<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);

    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
        }
        $zipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        if (!mkdir($zipPath) && !is_dir($zipPath)) {
            syncException('Error : Verzeichnis ' . $zipPath . ' kann nicht erstellt werden!');
        }
        $zipPath .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $zipPath)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $zipPath . $zip['filename'] . ' size: ' .
                        filesize($zipPath . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
                }
                $d   = file_get_contents($zipPath . $zip['filename']);
                $xml = XML_unserialize($d);
                if ($zip['filename'] === 'del_kunden.xml') {
                    bearbeiteDeletes($xml);
                } elseif ($zip['filename'] === 'ack_kunden.xml') {
                    bearbeiteAck($xml);
                } elseif ($zip['filename'] === 'gutscheine.xml') {
                    bearbeiteGutscheine($xml);
                } elseif ($zip['filename'] === 'aktiviere_kunden.xml') {
                    aktiviereKunden($xml);
                } elseif ($zip['filename'] === 'passwort_kunden.xml') {
                    generiereNeuePasswoerter($xml);
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kunden_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kunden_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function aktiviereKunden($xml)
{
    $kunden = mapArray($xml['aktiviere_kunden'], 'tkunde', []);
    foreach ($kunden as $kunde) {
        if ($kunde->kKunde > 0 && $kunde->kKundenGruppe > 0) {
            $kunde_db = new Kunde($kunde->kKunde);

            if ($kunde_db->kKunde > 0 && $kunde_db->kKundengruppe != $kunde->kKundenGruppe) {
                Shop::DB()->update(
                    'tkunde',
                    'kKunde',
                    (int)$kunde->kKunde,
                    (object)['kKundengruppe' => (int)$kunde->kKundenGruppe]
                );
                //mail
                $kunde_db->kKundengruppe = (int)$kunde->kKundenGruppe;
                $obj                     = new stdClass();
                $obj->tkunde             = $kunde_db;
                if ($kunde_db->cMail) {
                    sendeMail(MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj);
                }
            }
            Shop::DB()->update('tkunde', 'kKunde', (int)$kunde->kKunde, (object)['cAktiv' => 'Y']);
        }
    }
}

/**
 * @param array $xml
 */
function generiereNeuePasswoerter($xml)
{
    $oKundeXML_arr = mapArray($xml['passwort_kunden'], 'tkunde', []);
    foreach ($oKundeXML_arr as $oKundeXML) {
        if (isset($oKundeXML->kKunde) && $oKundeXML->kKunde > 0) {
            $oKunde = new Kunde((int)$oKundeXML->kKunde);
            if ($oKunde->nRegistriert == 1 && $oKunde->cMail) {
                $oKunde->prepareResetPassword();
            } else {
                syncException('Kunde hat entweder keine Emailadresse oder es ist ein unregistrierter Kunde', 8);
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (isset($xml['del_kunden']['kKunde'])) {
        if (is_array($xml['del_kunden']['kKunde'])) {
            foreach ($xml['del_kunden']['kKunde'] as $kKunde) {
                $kKunde = (int)$kKunde;
                if ($kKunde > 0) {
                    Shop::DB()->delete('tkunde', 'kKunde', $kKunde);
                    Shop::DB()->delete('tlieferadresse', 'kKunde', $kKunde);
                    Shop::DB()->delete('tkundenattribut', 'kKunde', $kKunde);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Kunde geloescht: ' . $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
                    }
                }
            }
        } elseif ((int)$xml['del_kunden']['kKunde'] > 0) {
            $kKunde = (int)$xml['del_kunden']['kKunde'];
            Shop::DB()->delete('tkunde', 'kKunde', $kKunde);
            Shop::DB()->delete('tlieferadresse', 'kKunde', $kKunde);
            Shop::DB()->delete('tkundenattribut', 'kKunde', $kKunde);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Kunde geloescht: ' . $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteAck($xml)
{
    if (isset($xml['ack_kunden']['kKunde'])) {
        if (!is_array($xml['ack_kunden']['kKunde']) && (int)$xml['ack_kunden']['kKunde'] > 0) {
            $xml['ack_kunden']['kKunde'] = [$xml['ack_kunden']['kKunde']];
        }
        if (is_array($xml['ack_kunden']['kKunde'])) {
            foreach ($xml['ack_kunden']['kKunde'] as $kKunde) {
                $kKunde = (int)$kKunde;
                if ($kKunde > 0) {
                    Shop::DB()->update('tkunde', 'kKunde', $kKunde, (object)['cAbgeholt' => 'Y']);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Kunde erfolgreich abgeholt: ' .
                            $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
                    }
                }
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteGutscheine($xml)
{
    if (isset($xml['gutscheine']['gutschein']) && is_array($xml['gutscheine']['gutschein'])) {
        $gutscheine_arr = mapArray($xml['gutscheine'], 'gutschein', $GLOBALS['mGutschein']);
        foreach ($gutscheine_arr as $gutschein) {
            if ($gutschein->kGutschein > 0 && $gutschein->kKunde > 0) {
                $gutschein_exists = Shop::DB()->select('tgutschein', 'kGutschein', (int)$gutschein->kGutschein);
                if (!isset($gutschein_exists->kGutschein) || !$gutschein_exists->kGutschein) {
                    $kGutschein = Shop::DB()->insert('tgutschein', $gutschein);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Gutschein fuer kKunde ' . (int)$gutschein->kKunde . ' wurde eingeloest. ' .
                            print_r($gutschein, true), JTLLOG_LEVEL_DEBUG, 'kGutschein', $kGutschein);
                    }
                    //kundenkto erhöhen
                    Shop::DB()->query("
                        UPDATE tkunde 
                          SET fGuthaben = fGuthaben+" . (float)$gutschein->fWert . " 
                          WHERE kKunde = " . (int)$gutschein->kKunde, 4
                    );
                    Shop::DB()->query("
                        UPDATE tkunde 
                          SET fGuthaben = 0 
                          WHERE kKunde = " . (int)$gutschein->kKunde . " 
                          AND fGuthaben < 0", 3
                    );
                    //mail
                    $kunde           = new Kunde((int)$gutschein->kKunde);
                    $obj             = new stdClass();
                    $obj->tkunde     = $kunde;
                    $obj->tgutschein = $gutschein;
                    if ($kunde->cMail) {
                        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                            Jtllog::writeLog('Gutschein Email wurde an ' . $kunde->cMail .
                                ' versendet.', JTLLOG_LEVEL_DEBUG, 'kGutschein', $kGutschein);
                        }
                        sendeMail(MAILTEMPLATE_GUTSCHEIN, $obj);
                    }
                }
            }
        }
    }
}
