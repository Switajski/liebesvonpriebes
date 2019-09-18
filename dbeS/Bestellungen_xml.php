<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.KundenwerbenKunden.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

$archive = null;
$return  = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
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
                        filesize($zipPath . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
                }
                $d   = file_get_contents($zipPath . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'ack_bestellung.xml') {
                    bearbeiteAck($xml);
                } elseif ($zip['filename'] === 'del_bestellung.xml') {
                    bearbeiteDel($xml);
                } elseif ($zip['filename'] === 'delonly_bestellung.xml') {
                    bearbeiteDelOnly($xml);
                } elseif ($zip['filename'] === 'storno_bestellung.xml') {
                    bearbeiteStorno($xml);
                } elseif ($zip['filename'] === 'reaktiviere_bestellung.xml') {
                    bearbeiteRestorno($xml);
                } elseif ($zip['filename'] === 'ack_zahlungseingang.xml') {
                    bearbeiteAckZahlung($xml);
                } elseif ($zip['filename'] === 'set_bestellung.xml') {
                    bearbeiteSet($xml);
                } elseif ($zip['filename'] === 'upd_bestellung.xml') {
                    bearbeiteUpdate($xml);
                }

                removeTemporaryFiles($zipPath . $zip['filename']);
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Bestellungen_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Bestellungen_xml');
    }
}

if ($return === 2) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteAck($xml)
{
    if (!is_array($xml['ack_bestellungen']['kBestellung']) && (int)$xml['ack_bestellungen']['kBestellung'] > 0) {
        $xml['ack_bestellungen']['kBestellung'] = [$xml['ack_bestellungen']['kBestellung']];
    }
    if (is_array($xml['ack_bestellungen']['kBestellung'])) {
        foreach ($xml['ack_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung = (int)$kBestellung;
            if ($kBestellung > 0) {
                Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, (object)['cAbgeholt' => 'Y']);
                Shop::DB()->update(
                    'tbestellung',
                    ['kBestellung', 'cStatus'],
                    [$kBestellung, BESTELLUNG_STATUS_OFFEN],
                    (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]
                );
                Shop::DB()->update('tzahlungsinfo', 'kBestellung', $kBestellung, (object)['cAbgeholt' => 'Y']);
            }
        }
    }
}

/**
 * @param int $kBestellung
 * @return bool|PaymentMethod
 */
function gibZahlungsmodul($kBestellung)
{
    $oBestellung = Shop::DB()->query(
        "SELECT tbestellung.kBestellung, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart 
                ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = '" . (int)$kBestellung . "'
            LIMIT 1", 1
    );

    if ($oBestellung) {
        return PaymentMethod::create($oBestellung->cModulId);
    }

    return false;
}

/**
 * @param array $xml
 */
function bearbeiteDel($xml)
{
    if (is_array($xml['del_bestellungen']['kBestellung'])) {
        foreach ($xml['del_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung = (int)$kBestellung;
            if ($kBestellung > 0) {
                $oModule = gibZahlungsmodul($kBestellung);
                if ($oModule) {
                    $oModule->cancelOrder($kBestellung, true);
                }
                deleteOrder($kBestellung);
                //uploads (bestellungen)
                Shop::DB()->delete('tuploadschema', ['kCustomID', 'nTyp'], [$kBestellung, 2]);
                Shop::DB()->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$kBestellung, 2]);
                //uploads (artikel der bestellung)
                //todo...
                //wenn unreg kunde, dann kunden auch löschen
                $b = Shop::DB()->query("SELECT kKunde FROM tbestellung WHERE kBestellung = " . $kBestellung, 1);
                if (isset($b->kKunde) && $b->kKunde > 0) {
                    checkGuestAccount($b->kKunde);
                }
            }
        }
    } else {
        $kBestellung = (int)$xml['del_bestellungen']['kBestellung'];
        if ($kBestellung > 0) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->cancelOrder($kBestellung, true);
            }
            deleteOrder($kBestellung);
            //wenn unreg kunde, dann kunden auch löschen
            $b = Shop::DB()->query("SELECT kKunde FROM tbestellung WHERE kBestellung = " . $kBestellung, 1);
            if (isset($b->kKunde) && $b->kKunde > 0) {
                checkGuestAccount($b->kKunde);
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDelOnly($xml)
{
    if (is_array($xml['del_bestellungen']['kBestellung'])) {
        foreach ($xml['del_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung = (int)$kBestellung;
            if ($kBestellung > 0) {
                $oModule = gibZahlungsmodul($kBestellung);
                if ($oModule) {
                    $oModule->cancelOrder($kBestellung, true);
                }
                deleteOrder($kBestellung);
            }
        }
    } else {
        $kBestellung = (int)$xml['del_bestellungen']['kBestellung'];
        if ($kBestellung > 0) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->cancelOrder($kBestellung, true);
            }
            deleteOrder($kBestellung);
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteStorno($xml)
{
    if (is_array($xml['storno_bestellungen']['kBestellung'])) {
        foreach ($xml['storno_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung   = (int)$kBestellung;
            $bestellungTmp = null;
            $kunde         = null;
            $oModule       = gibZahlungsmodul($kBestellung);
            $bestellungTmp = new Bestellung($kBestellung);
            $kunde         = new Kunde($bestellungTmp->kKunde);
            $bestellungTmp->fuelleBestellung();
            if ($oModule) {
                $oModule->cancelOrder($kBestellung);
            } else {
                if (!empty($kunde->cMail) && ($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO)) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $oMail);
                }
                Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, (object)['cStatus' => BESTELLUNG_STATUS_STORNO]);
            }
            checkGuestAccount($kunde->kKunde);
            executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
                'oBestellung' => &$bestellungTmp,
                'oKunde'      => &$kunde,
                'oModule'     => $oModule
            ]);
        }
    } else {
        $kBestellung = (int)$xml['storno_bestellungen']['kBestellung'];
        if ($kBestellung > 0) {
            $bestellungTmp = null;
            $kunde         = null;
            $oModule       = gibZahlungsmodul($kBestellung);
            $bestellungTmp = new Bestellung($kBestellung);
            $kunde         = new Kunde($bestellungTmp->kKunde);
            $bestellungTmp->fuelleBestellung();
            if ($oModule) {
                $oModule->cancelOrder($kBestellung);
            } else {
                if (!empty($kunde->cMail) && ($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO)) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $oMail);
                }
                Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, (object)['cStatus' => BESTELLUNG_STATUS_STORNO]);
            }
            checkGuestAccount($kunde->kKunde);
            executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, [
                'oBestellung' => &$bestellungTmp,
                'oKunde'      => &$kunde,
                'oModule'     => $oModule
            ]);
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteRestorno($xml)
{
    if (is_array($xml['reaktiviere_bestellungen']['kBestellung'])) {
        foreach ($xml['reaktiviere_bestellungen']['kBestellung'] as $kBestellung) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->reactivateOrder($kBestellung);
            } else {
                $bestellungTmp = new Bestellung($kBestellung);
                $kunde         = new Kunde($bestellungTmp->kKunde);
                $bestellungTmp->fuelleBestellung();

                if (($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($kunde->cMail) > 0) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_RESTORNO, $oMail);
                }
                Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]);
                checkGuestAccount($kunde->kKunde);
            }
        }
    } else {
        $kBestellung = (int)$xml['reaktiviere_bestellungen']['kBestellung'];
        if ($kBestellung > 0) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->reactivateOrder($kBestellung);
            } else {
                $bestellungTmp = new Bestellung($kBestellung);
                $kunde         = new Kunde($bestellungTmp->kKunde);
                $bestellungTmp->fuelleBestellung();

                if (($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($kunde->cMail) > 0) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_RESTORNO, $oMail);
                }

                Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, (object)['cStatus' => BESTELLUNG_STATUS_IN_BEARBEITUNG]);
                checkGuestAccount($kunde->kKunde);
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteAckZahlung($xml)
{
    if (!is_array($xml['ack_zahlungseingang']['kZahlungseingang']) &&
        (int)$xml['ack_zahlungseingang']['kZahlungseingang'] > 0
    ) {
        $xml['ack_zahlungseingang']['kZahlungseingang'] = [$xml['ack_zahlungseingang']['kZahlungseingang']];
    }
    if (is_array($xml['ack_zahlungseingang']['kZahlungseingang'])) {
        foreach ($xml['ack_zahlungseingang']['kZahlungseingang'] as $kZahlungseingang) {
            if ((int)$kZahlungseingang > 0) {
                Shop::DB()->update(
                    'tzahlungseingang',
                    'kZahlungseingang',
                    (int)$kZahlungseingang,
                    (object)['cAbgeholt' => 'Y']
                );
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteUpdate($xml)
{
    $kunde            = null;
    $oBestellung      = new stdClass();
    $Bestellungen_arr = mapArray($xml, 'tbestellung', $GLOBALS['mBestellung']);
    if (is_array($Bestellungen_arr) && count($Bestellungen_arr) === 1) {
        $oBestellung = $Bestellungen_arr[0];
    }
    //kommt überhaupt eine kbestellung?
    if (!$oBestellung->kBestellung) {
        unhandledError('Error Bestellung Update. Keine kBestellung in tbestellung! XML:' . print_r($xml, true));
    }
    //hole bestellung
    $oBestellungAlt = Shop::DB()->select('tbestellung', 'kBestellung', (int)$oBestellung->kBestellung);
    //mappe rechnungsadresse
    $oRechnungsadresse = new Rechnungsadresse($oBestellungAlt->kRechnungsadresse);
    mappe($oRechnungsadresse, $xml['tbestellung']['trechnungsadresse'], $GLOBALS['mRechnungsadresse']);
    if (!empty($oRechnungsadresse->cAnrede)) {
        $oRechnungsadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oRechnungsadresse->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($oRechnungsadresse);
    //rechnungsadresse gefüllt?
    if (!$oRechnungsadresse->cNachname && !$oRechnungsadresse->cFirma && !$oRechnungsadresse->cStrasse) {
        unhandledError('Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' . print_r($xml, true));
    }
    //existiert eine alte bestellung mit dieser kBestellung?
    if (!$oBestellungAlt->kBestellung || trim($oBestellung->cBestellNr) !== trim($oBestellungAlt->cBestellNr)) {
        unhandledError('Fehler: Zur Bestellung ' . $oBestellung->cBestellNr . ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!');
    }
    // Zahlungsart vorhanden?
    $oZahlungsart = new stdClass();
    if (isset($xml['tbestellung']['cZahlungsartName']) && strlen($xml['tbestellung']['cZahlungsartName']) > 0) {
        $oSprache = gibStandardsprache(true);
        if ($oSprache->kSprache != $oBestellung->kSprache) {
            $oZahlungsart = Shop::DB()->executeQueryPrepared(
                "SELECT kZahlungsart, cName
                    FROM tzahlungsart
                    WHERE cName LIKE :name",
                ['name' => '%' . $xml['tbestellung']['cZahlungsartName'] . '%'],
                1
            );
        } else {
            $oZahlungsart = Shop::DB()->executeQueryPrepared(
                "SELECT tzahlungsart.kZahlungsart, IFNULL(tzahlungsartsprache.cName, tzahlungsart.cName) AS cName
                    FROM tzahlungsart
                    LEFT JOIN tzahlungsartsprache 
                        ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                        AND tzahlungsartsprache.cISOSprache = :iso
                    WHERE tzahlungsart.cName LIKE :name",
                [
                    'iso'  => gibSprachKeyISO('', (int)$oBestellung->kSprache),
                    'name' => '%' . $xml['tbestellung']['cZahlungsartName'] . '%'
                ],
                1
            );
        }
    }
    $cZAUpdateSQL = '';
    if (isset($oZahlungsart->kZahlungsart) && $oZahlungsart->kZahlungsart > 0) {
        $cZAUpdateSQL = " , kZahlungsart = " . (int)$oZahlungsart->kZahlungsart . ", cZahlungsartName = '" . $oZahlungsart->cName . "' ";
    }
    //#8544
    $correctionFactor = 1.0;
    if (isset($oBestellung->kWaehrung)) {
        $currentCurrency = Shop::DB()->select('twaehrung', 'kWaehrung', $oBestellung->kWaehrung);
        $defaultCurrency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        if (isset($currentCurrency->kWaehrung, $defaultCurrency->kWaehrung)) {
            $correctionFactor           = (float)$currentCurrency->fFaktor;
            $oBestellung->fGesamtsumme /= $correctionFactor;
            $oBestellung->fGuthaben    /= $correctionFactor;
        }
    }
    // Die Wawi schickt in fGesamtsumme die Rechnungssumme (Summe aller Positionen), der Shop erwartet hier aber tatsächlich
    // eine Gesamtsumme oder auch den Zahlungsbetrag (Rechnungssumme abzgl. evtl. Guthaben)
    $oBestellung->fGesamtsumme -= $oBestellung->fGuthaben;

    //aktualisiere bestellung
    Shop::DB()->query(
        "UPDATE tbestellung SET
            fGuthaben = '" . Shop::DB()->escape($oBestellung->fGuthaben) . "',
            fGesamtsumme = '" . Shop::DB()->escape($oBestellung->fGesamtsumme) . "',
            cKommentar = '" . Shop::DB()->escape($oBestellung->cKommentar) . "'
            {$cZAUpdateSQL}
            WHERE kBestellung = " . (int)$oBestellungAlt->kBestellung, 4
    );
    //aktualisliere lieferadresse
    $oLieferadresse = new Lieferadresse($oBestellungAlt->kLieferadresse);
    mappe($oLieferadresse, $xml['tbestellung']['tlieferadresse'], $GLOBALS['mLieferadresse']);
    if (isset($oLieferadresse->cAnrede)) {
        $oLieferadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oLieferadresse->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($oLieferadresse);
    //lieferadresse ungleich rechungsadresse?
    if ($oLieferadresse->cVorname !== $oRechnungsadresse->cVorname ||
        $oLieferadresse->cNachname !== $oRechnungsadresse->cNachname ||
        $oLieferadresse->cStrasse !== $oRechnungsadresse->cStrasse ||
        $oLieferadresse->cHausnummer !== $oRechnungsadresse->cHausnummer ||
        $oLieferadresse->cPLZ !== $oRechnungsadresse->cPLZ ||
        $oLieferadresse->cOrt !== $oRechnungsadresse->cOrt ||
        $oLieferadresse->cLand !== $oRechnungsadresse->cLand
    ) {
        if ($oLieferadresse->kLieferadresse > 0) {
            //lieferadresse aktualisieren
            $oLieferadresse->updateInDB();
        } else {
            //lieferadresse erstellen
            $oLieferadresse->kKunde         = $oBestellungAlt->kKunde;
            $oLieferadresse->kLieferadresse = $oLieferadresse->insertInDB();

            Shop::DB()->query(
                "UPDATE tbestellung
                    SET kLieferadresse = " . (int)$oLieferadresse->kLieferadresse . "
                    WHERE kBestellung = " . (int)$oBestellungAlt->kBestellung, 4
            );
        }
    } elseif ($oBestellungAlt->kLieferadresse > 0) { //falls lieferadresse vorhanden zurücksetzen
        Shop::DB()->update(
            'tbestellung',
            'kBestellung',
            (int)$oBestellungAlt->kBestellung,
            (object)['kLieferadresse' => 0]
        );
    }

    $oRechnungsadresse->updateInDB();
    //loesche alte positionen
    $WarenkorbposAlt_arr = Shop::DB()->selectAll('twarenkorbpos', 'kWarenkorb', (int)$oBestellungAlt->kWarenkorb);
    $WarenkorbposAlt_map = [];
    //loesche poseigenschaften
    foreach ($WarenkorbposAlt_arr as $key => $WarenkorbposAlt) {
        Shop::DB()->delete('twarenkorbposeigenschaft', 'kWarenkorbPos', (int)$WarenkorbposAlt->kWarenkorbPos);
        if ($WarenkorbposAlt->kArtikel > 0) {
            $WarenkorbposAlt_map[$WarenkorbposAlt->kArtikel] = $key;
        }
    }
    //loesche positionen
    Shop::DB()->delete('twarenkorbpos', 'kWarenkorb', (int)$oBestellungAlt->kWarenkorb);
    //erstelle neue posis
    $Warenkorbpos_arr = mapArray($xml['tbestellung'], 'twarenkorbpos', $GLOBALS['mWarenkorbpos']);
    $positionCount    = count($Warenkorbpos_arr);
    for ($i = 0; $i < $positionCount; $i++) {
        //füge wkpos ein
        $oWarenkorbposAlt = array_key_exists($Warenkorbpos_arr[$i]->kArtikel, $WarenkorbposAlt_map)
            ? $WarenkorbposAlt_arr[$WarenkorbposAlt_map[$Warenkorbpos_arr[$i]->kArtikel]]
            : null;
        unset($Warenkorbpos_arr[$i]->kWarenkorbPos);
        $Warenkorbpos_arr[$i]->kWarenkorb         = $oBestellungAlt->kWarenkorb;
        $Warenkorbpos_arr[$i]->fPreis            /= $correctionFactor;
        $Warenkorbpos_arr[$i]->fPreisEinzelNetto /= $correctionFactor;
        // persistiere nLongestMin/MaxDelivery wenn nicht von Wawi übetragen
        if (!isset($Warenkorbpos_arr[$i]->nLongestMinDelivery)) {
            $Warenkorbpos_arr[$i]->nLongestMinDelivery = isset($oWarenkorbposAlt) ? $oWarenkorbposAlt->nLongestMinDelivery : 0;
        }
        if (!isset($Warenkorbpos_arr[$i]->nLongestMaxDelivery)) {
            $Warenkorbpos_arr[$i]->nLongestMaxDelivery = isset($oWarenkorbposAlt) ? $oWarenkorbposAlt->nLongestMaxDelivery : 0;
        }
        $Warenkorbpos_arr[$i]->kWarenkorbPos = Shop::DB()->insert('twarenkorbpos', $Warenkorbpos_arr[$i]);

        if (count($Warenkorbpos_arr) < 2) { // nur eine pos
            $Warenkorbposeigenschaft_arr = mapArray($xml['tbestellung']['twarenkorbpos'], 'twarenkorbposeigenschaft', $GLOBALS['mWarenkorbposeigenschaft']);
        } else { //mehrere posis
            $Warenkorbposeigenschaft_arr = mapArray($xml['tbestellung']['twarenkorbpos'][$i], 'twarenkorbposeigenschaft', $GLOBALS['mWarenkorbposeigenschaft']);
        }
        //füge warenkorbposeigenschaften ein
        foreach ($Warenkorbposeigenschaft_arr as $Warenkorbposeigenschaft) {
            unset($Warenkorbposeigenschaft->kWarenkorbPosEigenschaft);
            $Warenkorbposeigenschaft->kWarenkorbPos = $Warenkorbpos_arr[$i]->kWarenkorbPos;
            Shop::DB()->insert('twarenkorbposeigenschaft', $Warenkorbposeigenschaft);
        }
    }

    if (isset($xml['tbestellung']['tbestellattribut'])) {
        bearbeiteBestellattribute($oBestellung->kBestellung, is_assoc($xml['tbestellung']['tbestellattribut']) ? [$xml['tbestellung']['tbestellattribut']] : $xml['tbestellung']['tbestellattribut']);
    }

    //sende Versandmail
    $oModule = gibZahlungsmodul($oBestellungAlt->kBestellung);
    //neues flag 'cSendeEMail' ab JTL-Wawi 099781 damit die email nur versandt wird wenns auch wirklich für den kunden interessant ist
    //ab JTL-Wawi 099781 wird das Flag immer gesendet und ist entweder "Y" oder "N"
    //bei JTL-Wawi Version <= 099780 ist dieses Flag nicht gesetzt, Mail soll hier immer versendet werden.
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Emailvorlage.php';
    $emailvorlage = Emailvorlage::load(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
    $kunde        = new Kunde((int)$oBestellungAlt->kKunde);

    if ($emailvorlage !== null && $emailvorlage->getAktiv() === 'Y' && ($oBestellung->cSendeEMail === 'Y' || !isset($oBestellung->cSendeEMail))) {
        if ($oModule) {
            $oModule->sendMail($oBestellungAlt->kBestellung, MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        } else {
            $bestellungTmp = new Bestellung((int)$oBestellungAlt->kBestellung);
            $bestellungTmp->fuelleBestellung();

            $oMail              = new stdClass();
            $oMail->tkunde      = $kunde;
            $oMail->tbestellung = $bestellungTmp;
            sendeMail(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $oMail);
        }
    }
    checkGuestAccount($kunde->kKunde);
    executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE, [
        'oBestellung'    => &$oBestellung,
        'oBestellungAlt' => &$oBestellungAlt,
        'oKunde'         => &$kunde
    ]);
}

/**
 * @param array $xml
 */
function bearbeiteSet($xml)
{
    $Bestellungen_arr = mapArray($xml['tbestellungen'], 'tbestellung', $GLOBALS['mBestellung']);
    foreach ($Bestellungen_arr as $oBestellungWawi) {
        $oBestellungShop = Shop::DB()->select('tbestellung', 'kBestellung', (int)$oBestellungWawi->kBestellung);
        if (isset($oBestellungShop->kBestellung) && $oBestellungShop->kBestellung > 0) {
            $cTrackingURL = '';
            if (strlen($oBestellungWawi->cIdentCode) > 0) {
                $cTrackingURL = $oBestellungWawi->cLogistikURL;
                if ($oBestellungShop->kLieferadresse > 0) {
                    $Lieferadresse = Shop::DB()->query(
                        "SELECT cPLZ
                            FROM tlieferadresse 
                            WHERE kLieferadresse = " . (int)$oBestellungShop->kLieferadresse, 1
                    );
                    if ($Lieferadresse->cPLZ) {
                        $cTrackingURL = str_replace('#PLZ#', $Lieferadresse->cPLZ, $cTrackingURL);
                    }
                } else {
                    $kunde        = new Kunde($oBestellungShop->kKunde);
                    $cTrackingURL = str_replace('#PLZ#', $kunde->cPLZ, $cTrackingURL);
                }

                $cTrackingURL = str_replace('#IdentCode#', $oBestellungWawi->cIdentCode, $cTrackingURL);
            }
            
            if ($oBestellungShop->cStatus === BESTELLUNG_STATUS_STORNO) {
                $status = BESTELLUNG_STATUS_STORNO;    // fixes jtlshop/jtl-shop#42
            } else {
                $status = BESTELLUNG_STATUS_IN_BEARBEITUNG;
                if (isset($oBestellungWawi->cBezahlt) && $oBestellungWawi->cBezahlt === 'Y') {
                    $status = BESTELLUNG_STATUS_BEZAHLT;
                }

                if (isset($oBestellungWawi->dVersandt) && strlen($oBestellungWawi->dVersandt) > 0) {
                    $status = BESTELLUNG_STATUS_VERSANDT;
                }
                $oBestellungUpdated = new Bestellung((int)$oBestellungShop->kBestellung);
                $oBestellungUpdated->fuelleBestellung();

                if ((is_array($oBestellungUpdated->oLieferschein_arr) && count($oBestellungUpdated->oLieferschein_arr) > 0) &&
                    (isset($oBestellungWawi->nKomplettAusgeliefert) && (int)$oBestellungWawi->nKomplettAusgeliefert === 0)) {
                    $status = BESTELLUNG_STATUS_TEILVERSANDT;
                }
            }
            

            executeHook(HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, ['status' => &$status, 'oBestellung' => &$oBestellungShop]);
            $cZahlungsartName = Shop::DB()->escape($oBestellungWawi->cZahlungsartName);
            $dBezahltDatum    = Shop::DB()->escape($oBestellungWawi->dBezahltDatum);
            $dVersandDatum    = Shop::DB()->escape($oBestellungWawi->dVersandt);
            if ($dVersandDatum === null || $dVersandDatum === '') {
                $dVersandDatum = '0000-00-00';
            }
            $upd                = new stdClass();
            $upd->dVersandDatum = $dVersandDatum;
            $upd->cTracking     = Shop::DB()->escape($oBestellungWawi->cIdentCode);
            $upd->cLogistiker   = Shop::DB()->escape($oBestellungWawi->cLogistik);
            $upd->cTrackingURL  = Shop::DB()->escape($cTrackingURL);
            $upd->cStatus       = $status;
            $upd->cVersandInfo  = Shop::DB()->escape($oBestellungWawi->cVersandInfo);
            if (strlen($cZahlungsartName) > 0) {
                $upd->cZahlungsartName = $cZahlungsartName;
            }
            $upd->dBezahltDatum = empty($dBezahltDatum) 
                ? '0000-00-00' 
                : $dBezahltDatum;
            Shop::DB()->update('tbestellung', 'kBestellung', (int)$oBestellungWawi->kBestellung, $upd);
            $oBestellungUpdated = new Bestellung($oBestellungShop->kBestellung, true);

            $kunde = null;
            if (((!$oBestellungShop->dVersandDatum || $oBestellungShop->dVersandDatum === '0000-00-00') && $oBestellungWawi->dVersandt) ||
                ((!$oBestellungShop->dBezahltDatum || $oBestellungShop->dBezahltDatum === '0000-00-00') && $oBestellungWawi->dBezahltDatum)
            ) {
                $b     = Shop::DB()->query("SELECT kKunde FROM tbestellung WHERE kBestellung = " . (int)$oBestellungWawi->kBestellung, 1);
                $kunde = new Kunde((int)$b->kKunde);
            }

            $bLieferschein = false;
            foreach ($oBestellungUpdated->oLieferschein_arr as $oLieferschein) {
                /** @var Lieferschein $oLieferschein */
                if ($oLieferschein->getEmailVerschickt() === false) {
                    $bLieferschein = true;
                    break;
                }
            }

            if (($status == BESTELLUNG_STATUS_VERSANDT && $oBestellungShop->cStatus != BESTELLUNG_STATUS_VERSANDT) ||
                ($status == BESTELLUNG_STATUS_TEILVERSANDT && $bLieferschein === true)) {
                $cMailType = ($status == BESTELLUNG_STATUS_VERSANDT) ? MAILTEMPLATE_BESTELLUNG_VERSANDT : MAILTEMPLATE_BESTELLUNG_TEILVERSANDT;
                $oModule   = gibZahlungsmodul($oBestellungWawi->kBestellung);
                if (!isset($oBestellungUpdated->oVersandart->cSendConfirmationMail) || $oBestellungUpdated->oVersandart->cSendConfirmationMail !== 'N') {
                    if ($oModule) {
                        $oModule->sendMail((int)$oBestellungWawi->kBestellung, $cMailType);
                    } else {
                        if ($kunde === null) {
                            $kunde = new Kunde((int)$oBestellungShop->kKunde);
                        }

                        $oMail              = new stdClass();
                        $oMail->tkunde      = $kunde;
                        $oMail->tbestellung = $oBestellungUpdated;
                        sendeMail($cMailType, $oMail);
                    }
                }
                /** @var Lieferschein $oLieferschein */
                foreach ($oBestellungUpdated->oLieferschein_arr as $oLieferschein) {
                    $oLieferschein->setEmailVerschickt(true)->update();
                }
                // Guthaben an Bestandskunden verbuchen, Email rausschicken:
                if ($kunde === null) {
                    $kunde = new Kunde($oBestellungShop->kKunde);
                }

                $oKwK = new KundenwerbenKunden();
                $oKwK->verbucheBestandskundenBoni($kunde->cMail);
            }

            checkGuestAccount((int)$oBestellungShop->kKunde);

            if ((!$oBestellungShop->dBezahltDatum || $oBestellungShop->dBezahltDatum === '0000-00-00') &&
                $oBestellungWawi->dBezahltDatum &&
                $kunde->kKunde > 0
            ) {
                //sende Zahlungseingangmail
                $oModule = gibZahlungsmodul($oBestellungWawi->kBestellung);
                if ($oModule) {
                    $oModule->sendMail((int)$oBestellungWawi->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);
                } else {
                    if ($kunde === null) {
                        $kunde = new Kunde((int)$oBestellungShop->kKunde);
                    }
                    $oBestellungUpdated = new Bestellung((int)$oBestellungShop->kBestellung);
                    $oBestellungUpdated->fuelleBestellung();
                    if (($oBestellungUpdated->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG) && strlen($kunde->cMail) > 0) {
                        $oMail              = new stdClass();
                        $oMail->tkunde      = $kunde;
                        $oMail->tbestellung = $oBestellungUpdated;
                        sendeMail(MAILTEMPLATE_BESTELLUNG_BEZAHLT, $oMail);
                    }
                }
            }
            executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESET, [
                'oBestellung'     => &$oBestellungShop,
                'oKunde'          => &$kunde,
                'oBestellungWawi' => &$oBestellungWawi
            ]);
        }
    }
}

/**
 * @param $kBestellung
 */
function deleteOrder($kBestellung)
{
    $kWarenkorb = Shop::DB()->select('tbestellung', 'kBestellung', $kBestellung, null, null, null, null, false, 'kWarenkorb');
    Shop::DB()->delete('tbestellung', 'kBestellung', $kBestellung);
    Shop::DB()->delete('tbestellid', 'kBestellung', $kBestellung);
    Shop::DB()->delete('tbestellstatus', 'kBestellung', $kBestellung);
    Shop::DB()->delete('tkuponbestellung', 'kBestellung', $kBestellung);
    Shop::DB()->delete('tuploaddatei', ['kCustomID', 'nTyp'], [$kBestellung, UPLOAD_TYP_BESTELLUNG]);
    Shop::DB()->delete('tuploadqueue', 'kBestellung', $kBestellung);
    if ((int)$kWarenkorb->kWarenkorb > 0) {
        Shop::DB()->delete('twarenkorb', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb);
        $kWarenkorbPos_arr = Shop::DB()->selectAll('twarenkorbpos', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb, 'kWarenkorbPos');
        Shop::DB()->delete('twarenkorbpos', 'kWarenkorb', (int)$kWarenkorb->kWarenkorb);
        if (is_array($kWarenkorbPos_arr)) {
            foreach ($kWarenkorbPos_arr as $kWarenkorbPos) {
                Shop::DB()->delete('twarenkorbposeigenschaft', 'kWarenkorbPos', (int)$kWarenkorbPos->kWarenkorbPos);
            }
        }
    }
}

/**
 * @param int $kKunde
 */
function checkGuestAccount($kKunde)
{
    //Bei komplett versendeten Gastbestellungen, Kundendaten aus dem Shop loeschen
    $kunde = new Kunde((int)$kKunde);
    if (isset($kunde->cPasswort) && strlen($kunde->cPasswort) < 10) {
        // Da Gastkonten auch durch Kundenkontolöschung entstehen können, kann es auch mehrere Bestellungen geben
        $oBestellung = Shop::DB()->query(
            "SELECT COUNT(kBestellung) AS countBestellung
                FROM tbestellung
                WHERE cStatus NOT IN (" . BESTELLUNG_STATUS_VERSANDT . ", " . BESTELLUNG_STATUS_STORNO . ")
                    AND kKunde = " . (int)$kunde->kKunde, 1
        );
        if (isset($oBestellung->countBestellung) && $oBestellung->countBestellung == 0) {
            Shop::DB()->delete('tlieferadresse', 'kKunde', (int)$kunde->kKunde);
            Shop::DB()->delete('trechnungsadresse', 'kKunde', (int)$kunde->kKunde);
            Shop::DB()->delete('tkundenattribut', 'kKunde', (int)$kunde->kKunde);
            Shop::DB()->delete('tkunde', 'kKunde', (int)$kunde->kKunde);
        }
    }
}

/**
 * @param int $kBestellung
 * @param stdClass[] $oBestellatribute_arr
 */
function bearbeiteBestellattribute($kBestellung, $oBestellattribut_arr)
{
    $keys_updated = [];
    if (isset($oBestellattribut_arr) && count($oBestellattribut_arr)) {
        foreach ($oBestellattribut_arr as $bestellattribut) {
            $oBestellattribut      = (object)$bestellattribut;
            $oBestellattribute_old = Shop::DB()->select('tbestellattribut', ['kBestellung', 'cName'], [$kBestellung, $oBestellattribut->key]);
            if (isset($oBestellattribute_old->kBestellattribut)) {
                Shop::DB()->update('tbestellattribut', 'kBestellattribut', $oBestellattribute_old->kBestellattribut, (object)[
                    'cValue' => $oBestellattribut->value,
                ]);
                $keys_updated[] = $oBestellattribute_old->kBestellattribut;
            } else {
                $keys_updated[] = Shop::DB()->insert('tbestellattribut', (object)[
                    'kBestellung' => $kBestellung,
                    'cName'       => $oBestellattribut->key,
                    'cValue'      => $oBestellattribut->value,
                ]);
            }
        }
    }

    if (count($keys_updated) > 0) {
        Shop::DB()->query(
            "DELETE FROM tbestellattribut
                WHERE kBestellung = {$kBestellung}
                    AND kBestellattribut NOT IN (" . implode(', ', $keys_updated) . ")", 10
        );
    } else {
        Shop::DB()->delete('tbestellattribut', 'kBestellung', $kBestellung);
    }
}
