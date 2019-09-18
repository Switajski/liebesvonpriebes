<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return int
 */
function bestellungKomplett()
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.CheckBox.php';
    $kKundengruppe = Kundengruppe::getCurrent();
    // CheckBox Plausi
    $oCheckBox               = new CheckBox();
    $_SESSION['cPlausi_arr'] = $oCheckBox->validateCheckBox(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $_POST, true);
    $_SESSION['cPost_arr']   = $_POST;

    return (isset($_SESSION['Kunde']) &&
        isset($_SESSION['Lieferadresse']) &&
        isset($_SESSION['Versandart']) &&
        isset($_SESSION['Zahlungsart']) &&
        $_SESSION['Kunde'] &&
        $_SESSION['Lieferadresse'] &&
        $_SESSION['Versandart'] &&
        $_SESSION['Zahlungsart'] &&
        (int)$_SESSION['Versandart']->kVersandart > 0 &&
        (int)$_SESSION['Zahlungsart']->kZahlungsart > 0 &&
        verifyGPCDataInteger('abschluss') === 1 &&
        count($_SESSION['cPlausi_arr']) === 0
    ) ? 1 : 0;
}

/**
 * @return int
 */
function gibFehlendeEingabe()
{
    if (!isset($_SESSION['Kunde']) || !$_SESSION['Kunde']) {
        return 1;
    }
    if (!isset($_SESSION['Lieferadresse']) || !$_SESSION['Lieferadresse']) {
        return 2;
    }
    if (!isset($_SESSION['Versandart']) ||
        !$_SESSION['Versandart'] ||
        (int)$_SESSION['Versandart']->kVersandart === 0
    ) {
        return 3;
    }
    if (!isset($_SESSION['Zahlungsart']) ||
        !$_SESSION['Zahlungsart'] ||
        (int)$_SESSION['Zahlungsart']->kZahlungsart === 0
    ) {
        return 4;
    }
    if (count($_SESSION['cPlausi_arr']) > 0) {
        return 6;
    }

    return -1;
}

/**
 * @param int    $nBezahlt
 * @param string $cBestellNr
 */
function bestellungInDB($nBezahlt = 0, $cBestellNr = '')
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    /** @var array('Kunde' => Kunde) $_SESSION */

    //für saubere DB Einträge
    unhtmlSession();
    //erstelle neue Bestellung
    $Bestellung = new Bestellung();
    //setze InetBestellNummer
    $Bestellung->cBestellNr = empty($cBestellNr) ? baueBestellnummer() : $cBestellNr;
    //füge Kunden ein, falls er nicht schon existiert ( loginkunde)
    if (!$_SESSION['Kunde']->kKunde) {
        // Kundenattribute sichern
        $cKundenattribut_arr = $_SESSION['Kunde']->cKundenattribut_arr;

        $_SESSION['Kunde']->kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        $_SESSION['Kunde']->kSprache      = Shop::$kSprache;
        $_SESSION['Kunde']->cAbgeholt     = 'N';
        $_SESSION['Kunde']->cAktiv        = 'Y';
        $_SESSION['Kunde']->cSperre       = 'N';
        $_SESSION['Kunde']->dErstellt     = 'now()';
        $cPasswortKlartext                = '';
        $_SESSION['Kunde']->nRegistriert  = 0;
        if ($_SESSION['Kunde']->cPasswort) {
            $_SESSION['Kunde']->nRegistriert = 1;
            $cPasswortKlartext               = $_SESSION['Kunde']->cPasswort;
            $_SESSION['Kunde']->cPasswort    = md5($_SESSION['Kunde']->cPasswort);
        }
        $_SESSION['Warenkorb']->kKunde = $_SESSION['Kunde']->insertInDB();
        $_SESSION['Kunde']->kKunde     = $_SESSION['Warenkorb']->kKunde;
        //Land: Deutschland -> DE
        $_SESSION['Kunde']->cLand = $_SESSION['Kunde']->pruefeLandISO($_SESSION['Kunde']->cLand);
        // Kundenattribute in DB setzen
        if (is_array($cKundenattribut_arr)) {
            $nKundenattributKey_arr = array_keys($cKundenattribut_arr);

            if (is_array($nKundenattributKey_arr) && count($nKundenattributKey_arr) > 0) {
                foreach ($nKundenattributKey_arr as $kKundenfeld) {
                    $oKundenattribut              = new stdClass();
                    $oKundenattribut->kKunde      = $_SESSION['Warenkorb']->kKunde;
                    $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                    $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                    $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                    Shop::DB()->insert('tkundenattribut', $oKundenattribut);
                }
            }
        }

        if (isset($_SESSION['Kunde']->cPasswort) && $_SESSION['Kunde']->cPasswort) {
            $_SESSION['Kunde']->cPasswortKlartext = $cPasswortKlartext;

            $obj         = new stdClass();
            $obj->tkunde = $_SESSION['Kunde'];

            executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_NEUKUNDENREGISTRIERUNG);

            sendeMail(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj);
        }
    } else {
        $_SESSION['Warenkorb']->kKunde = $_SESSION['Kunde']->kKunde;
        Shop::DB()->update('tkunde', 'kKunde', (int)$_SESSION['Kunde']->kKunde, (object)['cAbgeholt' => 'N']);
    }
    //Lieferadresse
    $_SESSION['Warenkorb']->kLieferadresse = 0; //=rechnungsadresse
    if (isset($_SESSION['Bestellung']->kLieferadresse) &&
        $_SESSION['Bestellung']->kLieferadresse == -1 &&
        !$_SESSION['Lieferadresse']->kLieferadresse
    ) {
        //neue Lieferadresse
        $_SESSION['Lieferadresse']->kKunde     = $_SESSION['Warenkorb']->kKunde;
        $_SESSION['Warenkorb']->kLieferadresse = $_SESSION['Lieferadresse']->insertInDB();
    } elseif (isset($_SESSION['Bestellung']->kLieferadresse) && $_SESSION['Bestellung']->kLieferadresse > 0) {
        $_SESSION['Warenkorb']->kLieferadresse = $_SESSION['Bestellung']->kLieferadresse;
    }
    $conf = Shop::getSettings([CONF_GLOBAL, CONF_TRUSTEDSHOPS]);
    //füge Warenkorb ein
    executeHook(HOOK_BESTELLABSCHLUSS_INC_WARENKORBINDB, ['oWarenkorb' => &$_SESSION['Warenkorb']]);
    $_SESSION['Warenkorb']->kWarenkorb = $_SESSION['Warenkorb']->insertInDB();
    //füge alle Warenkorbpositionen ein
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        $nArtikelAnzeigefilter = (int)$conf['global']['artikel_artikelanzeigefilter'];
        /** @var WarenkorbPos $Position */
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $Position) {
            if ($Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                $Position->fLagerbestandVorAbschluss = isset($Position->Artikel->fLagerbestand)
                    ? (double)$Position->Artikel->fLagerbestand
                    : 0;
            }
            $Position->cName         = StringHandler::unhtmlentities(is_array($Position->cName)
                ? $Position->cName[$_SESSION['cISOSprache']]
                : $Position->cName);
            $Position->cLieferstatus = isset($Position->cLieferstatus[$_SESSION['cISOSprache']])
                ? StringHandler::unhtmlentities($Position->cLieferstatus[$_SESSION['cISOSprache']])
                : '';
            $Position->kWarenkorb    = $_SESSION['Warenkorb']->kWarenkorb;
            $Position->fMwSt         = gibUst($Position->kSteuerklasse);
            $Position->kWarenkorbPos = $Position->insertInDB();
            if (is_array($Position->WarenkorbPosEigenschaftArr) && count($Position->WarenkorbPosEigenschaftArr) > 0) {
                // Bei einem Varkombikind dürfen nur FREIFELD oder PFLICHT-FREIFELD gespeichert werden,
                // da sonst eventuelle Aufpreise in der Wawi doppelt berechnet werden
                if (isset($Position->Artikel->kVaterArtikel) && $Position->Artikel->kVaterArtikel > 0) {
                    foreach ($Position->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->kWarenkorbPos        = $Position->kWarenkorbPos;
                            $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$_SESSION['cISOSprache']];
                            $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']];
                            $WKPosEigenschaft->cFreifeldWert        = $WKPosEigenschaft->cEigenschaftWertName;
                            $WKPosEigenschaft->insertInDB();
                        }
                    }
                } else {
                    foreach ($Position->WarenkorbPosEigenschaftArr as $o => $WKPosEigenschaft) {
                        $WKPosEigenschaft->kWarenkorbPos        = $Position->kWarenkorbPos;
                        $WKPosEigenschaft->cEigenschaftName     = $WKPosEigenschaft->cEigenschaftName[$_SESSION['cISOSprache']];
                        $WKPosEigenschaft->cEigenschaftWertName = $WKPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']];
                        if ($WKPosEigenschaft->cTyp === 'FREIFELD' || $WKPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                            $WKPosEigenschaft->cFreifeldWert = $WKPosEigenschaft->cEigenschaftWertName;
                        }
                        $WKPosEigenschaft->insertInDB();
                    }
                }
            }
            //bestseller tabelle füllen
            if ($Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && is_object($Position->Artikel)) {
                //Lagerbestand verringern
                aktualisiereLagerbestand(
                    $Position->Artikel,
                    $Position->nAnzahl,
                    $Position->WarenkorbPosEigenschaftArr,
                    $nArtikelAnzeigefilter
                );
                aktualisiereBestseller($Position->kArtikel, $Position->nAnzahl);
                //xsellkauf füllen
                foreach ($_SESSION['Warenkorb']->PositionenArr as $pos) {
                    if ($pos->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && $pos->kArtikel != $Position->kArtikel) {
                        aktualisiereXselling($Position->kArtikel, $pos->kArtikel);
                    }
                }
                $oWarenkorbpositionen_arr[] = $Position;
                // Clear Cache
                Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $Position->kArtikel]);
            } elseif ($Position->nPosTyp == C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                aktualisiereLagerbestand(
                    $Position->Artikel,
                    $Position->nAnzahl,
                    $Position->WarenkorbPosEigenschaftArr,
                    $nArtikelAnzeigefilter
                );
                $oWarenkorbpositionen_arr[] = $Position;
                // Clear Cache
                Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $Position->kArtikel]);
            }

            $Bestellung->Positionen[] = $Position;
        }
        // Falls die Einstellung global_wunschliste_artikel_loeschen_nach_kauf auf Y (Ja) steht und
        // Artikel vom aktuellen Wunschzettel gekauft wurden, sollen diese vom Wunschzettel geloescht werden
        if (isset($_SESSION['Wunschliste']->kWunschliste) && $_SESSION['Wunschliste']->kWunschliste > 0) {
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Wunschliste.php';
            Wunschliste::pruefeArtikelnachBestellungLoeschen($_SESSION['Wunschliste']->kWunschliste, $oWarenkorbpositionen_arr);
        }
    }
    // trechnungsadresse füllen
    $oRechnungsadresse = new Rechnungsadresse();

    $oRechnungsadresse->kKunde        = $_SESSION['Kunde']->kKunde;
    $oRechnungsadresse->cAnrede       = $_SESSION['Kunde']->cAnrede;
    $oRechnungsadresse->cTitel        = $_SESSION['Kunde']->cTitel;
    $oRechnungsadresse->cVorname      = $_SESSION['Kunde']->cVorname;
    $oRechnungsadresse->cNachname     = $_SESSION['Kunde']->cNachname;
    $oRechnungsadresse->cFirma        = $_SESSION['Kunde']->cFirma;
    $oRechnungsadresse->cZusatz       = $_SESSION['Kunde']->cZusatz;
    $oRechnungsadresse->cStrasse      = $_SESSION['Kunde']->cStrasse;
    $oRechnungsadresse->cHausnummer   = $_SESSION['Kunde']->cHausnummer;
    $oRechnungsadresse->cAdressZusatz = $_SESSION['Kunde']->cAdressZusatz;
    $oRechnungsadresse->cPLZ          = $_SESSION['Kunde']->cPLZ;
    $oRechnungsadresse->cOrt          = $_SESSION['Kunde']->cOrt;
    $oRechnungsadresse->cBundesland   = $_SESSION['Kunde']->cBundesland;
    $oRechnungsadresse->cLand         = $_SESSION['Kunde']->cLand;
    $oRechnungsadresse->cTel          = $_SESSION['Kunde']->cTel;
    $oRechnungsadresse->cMobil        = $_SESSION['Kunde']->cMobil;
    $oRechnungsadresse->cFax          = $_SESSION['Kunde']->cFax;
    $oRechnungsadresse->cUSTID        = $_SESSION['Kunde']->cUSTID;
    $oRechnungsadresse->cWWW          = $_SESSION['Kunde']->cWWW;
    $oRechnungsadresse->cMail         = $_SESSION['Kunde']->cMail;

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE);

    $kRechnungsadresse = $oRechnungsadresse->insertInDB();

    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = substr(strip_tags($_POST['kommentar']), 0, 1000);
    } elseif (!isset($_SESSION['kommentar'])) {
        $_SESSION['kommentar'] = '';
    }

    $Bestellung->kKunde            = $_SESSION['Warenkorb']->kKunde;
    $Bestellung->kWarenkorb        = $_SESSION['Warenkorb']->kWarenkorb;
    $Bestellung->kLieferadresse    = $_SESSION['Warenkorb']->kLieferadresse;
    $Bestellung->kRechnungsadresse = $kRechnungsadresse;
    $Bestellung->kZahlungsart      = $_SESSION['Zahlungsart']->kZahlungsart;
    $Bestellung->kVersandart       = $_SESSION['Versandart']->kVersandart;
    $Bestellung->kSprache          = Shop::getLanguage();
    $Bestellung->kWaehrung         = $_SESSION['Waehrung']->kWaehrung;
    $Bestellung->fGesamtsumme      = $_SESSION['Warenkorb']->gibGesamtsummeWaren(1);
    $Bestellung->cVersandartName   = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $Bestellung->cZahlungsartName  = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $Bestellung->cSession          = session_id();
    $Bestellung->cKommentar        = $_SESSION['kommentar'];
    $Bestellung->cAbgeholt         = 'N';
    $Bestellung->cStatus           = BESTELLUNG_STATUS_OFFEN;
    $Bestellung->dErstellt         = 'now()';
    $Bestellung->berechneEstimatedDelivery();
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) {
        $Bestellung->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
        Shop::DB()->query(
            "UPDATE tkunde
                SET fGuthaben = fGuthaben - " . (float)$_SESSION['Bestellung']->fGuthabenGenutzt . "
                WHERE kKunde = " . (int)$Bestellung->kKunde, 4
        );
        $_SESSION['Kunde']->fGuthaben -= $_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    // Gesamtsumme entspricht 0
    if ($Bestellung->fGesamtsumme == 0) {
        $Bestellung->cStatus          = BESTELLUNG_STATUS_BEZAHLT;
        $Bestellung->dBezahltDatum    = 'now()';
        $Bestellung->cZahlungsartName = Shop::Lang()->get('paymentNotNecessary', 'checkout');
    }
    $Bestellung->cIP = isset($_SESSION['IP']->cIP) ? $_SESSION['IP']->cIP : gibIP(true);
    //#8544
    $Bestellung->fWaehrungsFaktor = $_SESSION['Waehrung']->fFaktor;

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, ['oBestellung' => &$Bestellung]);

    $kBestellung = $Bestellung->insertInDB();

    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Bestellung gespeichert: ' . print_r($Bestellung, true), JTLLOG_LEVEL_DEBUG, false, 'kBestellung', $kBestellung);
    }
    // TrustedShops buchen
    if (isset($_SESSION['TrustedShops']->cKaeuferschutzProdukt) &&
        $_SESSION['Zahlungsart']->nWaehrendBestellung == 0 &&
        $conf['trustedshops']['trustedshops_nutzen'] === 'Y' &&
        strlen($_SESSION['TrustedShops']->cKaeuferschutzProdukt) > 0
    ) {
        $oTrustedShops                    = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
        $oTrustedShops->tsProductId       = $_SESSION['TrustedShops']->cKaeuferschutzProdukt;
        $oTrustedShops->amount            = $_SESSION['Waehrung']->fFaktor * $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);
        $oTrustedShops->currency          = $_SESSION['Waehrung']->cISO;
        $oTrustedShops->paymentType       = $_SESSION['Zahlungsart']->cTSCode;
        $oTrustedShops->buyerEmail        = $_SESSION['Kunde']->cMail;
        $oTrustedShops->shopCustomerID    = $_SESSION['Kunde']->kKunde;
        $oTrustedShops->shopOrderID       = $Bestellung->cBestellNr;
        $oTrustedShops->orderDate         = date('Y-m-d') . 'T' . date('H:i:s');
        $oTrustedShops->shopSystemVersion = 'JTL-Shop ' . JTL_VERSION;

        if (strlen($oTrustedShops->tsProductId) > 0 &&
            strlen($oTrustedShops->amount) > 0 &&
            strlen($oTrustedShops->currency) > 0 &&
            strlen($oTrustedShops->paymentType) > 0 &&
            strlen($oTrustedShops->buyerEmail) > 0 &&
            strlen($oTrustedShops->shopCustomerID) > 0 &&
            strlen($oTrustedShops->shopOrderID) > 0
        ) {
            $oTrustedShops->sendeBuchung();
        }
    }
    //BestellID füllen
    $bestellid              = new stdClass();
    $bestellid->cId         = gibUID(40, $Bestellung->kBestellung . md5(time()));
    $bestellid->kBestellung = $Bestellung->kBestellung;
    $bestellid->dDatum      = 'now()';
    Shop::DB()->insert('tbestellid', $bestellid);
    //bestellstatus füllen
    $bestellstatus              = new stdClass();
    $bestellstatus->kBestellung = $Bestellung->kBestellung;
    $bestellstatus->dDatum      = 'now()';
    $bestellstatus->cUID        = gibUID(40, (time() . $Bestellung->kBestellung) . substr(time(), -8));
    Shop::DB()->insert('tbestellstatus', $bestellstatus);
    //füge ZahlungsInfo ein, falls es die Versandart erfordert
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo) && $_SESSION['Zahlungsart']->ZahlungsInfo) {
        saveZahlungsInfo($Bestellung->kKunde, $Bestellung->kBestellung);
    }

    $_SESSION['BestellNr']   = $Bestellung->cBestellNr;
    $_SESSION['kBestellung'] = $Bestellung->kBestellung;
    //evtl. Kupon  Verwendungen hochzählen
    KuponVerwendungen($Bestellung);
    // Kampagne
    if (isset($_SESSION['Kampagnenbesucher'])) {
        // Verkauf
        setzeKampagnenVorgang(KAMPAGNE_DEF_VERKAUF, $Bestellung->kBestellung, 1.0);
        // Verkaufssumme
        setzeKampagnenVorgang(KAMPAGNE_DEF_VERKAUFSSUMME, $Bestellung->kBestellung, $Bestellung->fGesamtsumme);
    }

    executeHook(HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE, [
        'oBestellung'   => &$Bestellung,
        'bestellID'     => &$bestellid,
        'bestellstatus' => &$bestellstatus,
    ]);
}

/**
 * @param int  $kKunde
 * @param int  $kBestellung
 * @param bool $bZahlungAgain
 *
 * @return bool
 */
function saveZahlungsInfo($kKunde, $kBestellung, $bZahlungAgain = false)
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */

    if (!$kKunde || !$kBestellung) {
        return false;
    }
    if (!class_exists('ZahlungsInfo')) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsInfo.php';
    }
    $_SESSION['ZahlungsInfo']               = new ZahlungsInfo();
    $_SESSION['ZahlungsInfo']->kBestellung  = $kBestellung;
    $_SESSION['ZahlungsInfo']->kKunde       = $kKunde;
    $_SESSION['ZahlungsInfo']->cKartenTyp   = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp)
        : null;
    $_SESSION['ZahlungsInfo']->cGueltigkeit = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit)
        : null;
    $_SESSION['ZahlungsInfo']->cBankName    = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBankName)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBankName)
        : null;
    $_SESSION['ZahlungsInfo']->cKartenNr    = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr)
        : null;
    $_SESSION['ZahlungsInfo']->cCVV         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cCVV)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cCVV)
        : null;
    $_SESSION['ZahlungsInfo']->cKontoNr     = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr)
        : null;
    $_SESSION['ZahlungsInfo']->cBLZ         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ)
        : null;
    $_SESSION['ZahlungsInfo']->cIBAN        = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
        : null;
    $_SESSION['ZahlungsInfo']->cBIC         = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cBIC)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cBIC)
        : null;
    $_SESSION['ZahlungsInfo']->cInhaber     = isset($_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber)
        ? StringHandler::unhtmlentities($_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber)
        : null;

    if (!$bZahlungAgain) {
        $_SESSION['Warenkorb']->kZahlungsInfo = $_SESSION['ZahlungsInfo']->insertInDB();
        $_SESSION['Warenkorb']->updateInDB();
    } else {
        $_SESSION['ZahlungsInfo']->insertInDB();
    }
    // Kontodaten speichern
    if (isset($_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr) ||
        isset($_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN)
    ) {
        Shop::DB()->delete('tkundenkontodaten', 'kKunde', (int)$kKunde);
        speicherKundenKontodaten($_SESSION['Zahlungsart']->ZahlungsInfo);
    }

    return true;
}

/**
 * @param object $oZahlungsinfo
 */
function speicherKundenKontodaten($oZahlungsinfo)
{
    $oKundenKontodaten            = new stdClass();
    $oKundenKontodaten->kKunde    = $_SESSION['Warenkorb']->kKunde;
    $oKundenKontodaten->cBLZ      = verschluesselXTEA($oZahlungsinfo->cBLZ);
    $oKundenKontodaten->nKonto    = verschluesselXTEA($oZahlungsinfo->cKontoNr);
    $oKundenKontodaten->cInhaber  = verschluesselXTEA($oZahlungsinfo->cInhaber);
    $oKundenKontodaten->cBankName = verschluesselXTEA($oZahlungsinfo->cBankName);
    $oKundenKontodaten->cIBAN     = verschluesselXTEA($oZahlungsinfo->cIBAN);
    $oKundenKontodaten->cBIC      = verschluesselXTEA($oZahlungsinfo->cBIC);

    Shop::DB()->insert('tkundenkontodaten', $oKundenKontodaten);
}

/**
 *
 */
function unhtmlSession()
{
    $knd = new Kunde();

    if ($_SESSION['Kunde']->kKunde > 0) {
        $knd->kKunde = $_SESSION['Kunde']->kKunde;
    }
    $knd->kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
    if ($_SESSION['Kunde']->kKundengruppe > 0) {
        $knd->kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
    }
    $knd->kSprache = Shop::$kSprache;
    if ($_SESSION['Kunde']->kSprache > 0) {
        $knd->kSprache = $_SESSION['Kunde']->kSprache;
    }
    if ($_SESSION['Kunde']->cKundenNr) {
        $knd->cKundenNr = $_SESSION['Kunde']->cKundenNr;
    }
    if ($_SESSION['Kunde']->cPasswort) {
        $knd->cPasswort = $_SESSION['Kunde']->cPasswort;
    }
    if ($_SESSION['Kunde']->fGuthaben) {
        $knd->fGuthaben = $_SESSION['Kunde']->fGuthaben;
    }
    if ($_SESSION['Kunde']->fRabatt) {
        $knd->fRabatt = $_SESSION['Kunde']->fRabatt;
    }
    if ($_SESSION['Kunde']->dErstellt) {
        $knd->dErstellt = $_SESSION['Kunde']->dErstellt;
    }
    if ($_SESSION['Kunde']->cAktiv) {
        $knd->cAktiv = $_SESSION['Kunde']->cAktiv;
    }
    if ($_SESSION['Kunde']->cAbgeholt) {
        $knd->cAbgeholt = $_SESSION['Kunde']->cAbgeholt;
    }
    if (isset($_SESSION['Kunde']->nRegistriert)) {
        $knd->nRegistriert = $_SESSION['Kunde']->nRegistriert;
    }
    $knd->cAnrede       = StringHandler::unhtmlentities($_SESSION['Kunde']->cAnrede);
    $knd->cVorname      = StringHandler::unhtmlentities($_SESSION['Kunde']->cVorname);
    $knd->cNachname     = StringHandler::unhtmlentities($_SESSION['Kunde']->cNachname);
    $knd->cStrasse      = StringHandler::unhtmlentities($_SESSION['Kunde']->cStrasse);
    $knd->cHausnummer   = StringHandler::unhtmlentities($_SESSION['Kunde']->cHausnummer);
    $knd->cPLZ          = StringHandler::unhtmlentities($_SESSION['Kunde']->cPLZ);
    $knd->cOrt          = StringHandler::unhtmlentities($_SESSION['Kunde']->cOrt);
    $knd->cLand         = StringHandler::unhtmlentities($_SESSION['Kunde']->cLand);
    $knd->cMail         = StringHandler::unhtmlentities($_SESSION['Kunde']->cMail);
    $knd->cTel          = StringHandler::unhtmlentities($_SESSION['Kunde']->cTel);
    $knd->cFax          = StringHandler::unhtmlentities($_SESSION['Kunde']->cFax);
    $knd->cFirma        = StringHandler::unhtmlentities($_SESSION['Kunde']->cFirma);
    $knd->cZusatz       = StringHandler::unhtmlentities($_SESSION['Kunde']->cZusatz);
    $knd->cTitel        = StringHandler::unhtmlentities($_SESSION['Kunde']->cTitel);
    $knd->cAdressZusatz = StringHandler::unhtmlentities($_SESSION['Kunde']->cAdressZusatz);
    $knd->cMobil        = StringHandler::unhtmlentities($_SESSION['Kunde']->cMobil);
    $knd->cWWW          = StringHandler::unhtmlentities($_SESSION['Kunde']->cWWW);
    $knd->cUSTID        = StringHandler::unhtmlentities($_SESSION['Kunde']->cUSTID);
    $knd->dGeburtstag   = StringHandler::unhtmlentities($_SESSION['Kunde']->dGeburtstag);
    $knd->cBundesland   = StringHandler::unhtmlentities($_SESSION['Kunde']->cBundesland);

    $knd->cKundenattribut_arr = $_SESSION['Kunde']->cKundenattribut_arr;

    $_SESSION['Kunde'] = $knd;

    $lieferadresse = new Lieferadresse();
    if ($_SESSION['Lieferadresse']->kKunde > 0) {
        $lieferadresse->kKunde = $_SESSION['Lieferadresse']->kKunde;
    }
    if ($_SESSION['Lieferadresse']->kLieferadresse > 0) {
        $lieferadresse->kLieferadresse = $_SESSION['Lieferadresse']->kLieferadresse;
    }
    $lieferadresse->cVorname      = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cVorname);
    $lieferadresse->cNachname     = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cNachname);
    $lieferadresse->cFirma        = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cFirma);
    $lieferadresse->cZusatz       = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cZusatz);
    $lieferadresse->cStrasse      = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cStrasse);
    $lieferadresse->cHausnummer   = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cHausnummer);
    $lieferadresse->cPLZ          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cPLZ);
    $lieferadresse->cOrt          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cOrt);
    $lieferadresse->cLand         = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cLand);
    $lieferadresse->cAnrede       = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cAnrede);
    $lieferadresse->cMail         = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cMail);
    $lieferadresse->cBundesland   = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cBundesland);
    $lieferadresse->cTel          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cTel);
    $lieferadresse->cFax          = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cFax);
    $lieferadresse->cTitel        = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cTitel);
    $lieferadresse->cAdressZusatz = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cAdressZusatz);
    $lieferadresse->cMobil        = StringHandler::unhtmlentities($_SESSION['Lieferadresse']->cMobil);

    $lieferadresse->angezeigtesLand = ISO2land($lieferadresse->cLand);

    $_SESSION['Lieferadresse'] = $lieferadresse;
}

/**
 * @param int       $kArtikel
 * @param int|float $Anzahl
 */
function aktualisiereBestseller($kArtikel, $Anzahl)
{
    $kArtikel = (int)$kArtikel;
    if (!$kArtikel || !$Anzahl) {
        return;
    }
    $best_obj = Shop::DB()->select('tbestseller', 'kArtikel', $kArtikel);
    if (isset($best_obj->kArtikel) && $best_obj->kArtikel > 0) {
        Shop::DB()->query("UPDATE tbestseller SET fAnzahl = fAnzahl + " . $Anzahl . " WHERE kArtikel = " . $kArtikel, 4);
    } else {
        $Bestseller           = new stdClass();
        $Bestseller->kArtikel = $kArtikel;
        $Bestseller->fAnzahl  = $Anzahl;
        Shop::DB()->insert('tbestseller', $Bestseller);
    }
    // Ist der Artikel eine Variationskombination?
    if (ArtikelHelper::isVariCombiChild($kArtikel)) {
        // Hole den kArtikel vom Vater
        $kArtikel = ArtikelHelper::getParent($kArtikel);
        // Trage auch den Vater in die Bestseller ein
        if (!$kArtikel || !$Anzahl) {
            return;
        }
        $best_obj = Shop::DB()->select('tbestseller', 'kArtikel', $kArtikel);
        if (isset($best_obj->kArtikel) && $best_obj->kArtikel > 0) {
            Shop::DB()->query("UPDATE tbestseller SET fAnzahl = fAnzahl + " . $Anzahl . " WHERE kArtikel = " . $kArtikel, 4);
        } else {
            $Bestseller           = new stdClass();
            $Bestseller->kArtikel = $kArtikel;
            $Bestseller->fAnzahl  = $Anzahl;
            Shop::DB()->insert('tbestseller', $Bestseller);
        }
    }
}

/**
 * @param int $kArtikel
 * @param int $kZielArtikel
 */
function aktualisiereXselling($kArtikel, $kZielArtikel)
{
    $kArtikel     = (int)$kArtikel;
    $kZielArtikel = (int)$kZielArtikel;
    if (!$kArtikel || !$kZielArtikel) {
        return;
    }
    $obj = Shop::DB()->select('txsellkauf', 'kArtikel', $kArtikel, 'kXSellArtikel', $kZielArtikel);
    if (isset($obj->nAnzahl) && $obj->nAnzahl > 0) {
        Shop::DB()->query(
            "UPDATE txsellkauf
              SET nAnzahl = nAnzahl + 1 
              WHERE kArtikel = " . $kArtikel . " 
                AND kXSellArtikel = " . $kZielArtikel, 4
        );
    } else {
        $xs                = new stdClass();
        $xs->kArtikel      = $kArtikel;
        $xs->kXSellArtikel = $kZielArtikel;
        $xs->nAnzahl       = 1;
        Shop::DB()->insert('txsellkauf', $xs);
    }
}

/**
 * @param Artikel   $Artikel
 * @param int|float $nAnzahl
 * @param array     $WarenkorbPosEigenschaftArr
 * @param int       $nArtikelAnzeigefilter
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereLagerbestand($Artikel, $nAnzahl, $WarenkorbPosEigenschaftArr, $nArtikelAnzeigefilter = 1)
{
    $artikelBestand = (float)$Artikel->fLagerbestand;

    if (isset($Artikel->cLagerBeachten) && $nAnzahl > 0 && $Artikel->cLagerBeachten === 'Y') {
        if ($Artikel->cLagerVariation === 'Y' &&
            is_array($WarenkorbPosEigenschaftArr) &&
            count($WarenkorbPosEigenschaftArr) > 0
        ) {
            foreach ($WarenkorbPosEigenschaftArr as $eWert) {
                $EigenschaftWert = new EigenschaftWert($eWert->kEigenschaftWert);
                if ($EigenschaftWert->fPackeinheit == 0) {
                    $EigenschaftWert->fPackeinheit = 1;
                }
                Shop::DB()->query(
                    "UPDATE teigenschaftwert
                        SET fLagerbestand = fLagerbestand - " . ($nAnzahl * $EigenschaftWert->fPackeinheit) . "
                        WHERE kEigenschaftWert = " . (int)$eWert->kEigenschaftWert, 4
                );
            }
        } elseif ($Artikel->fPackeinheit > 0) {
            // Stückliste
            if ($Artikel->kStueckliste > 0) {
                $artikelBestand = aktualisiereStuecklistenLagerbestand($Artikel, $nAnzahl);
            } else {
                Shop::DB()->query(
                    "UPDATE tartikel
                        SET fLagerbestand = IF (fLagerbestand >= " . ($nAnzahl * $Artikel->fPackeinheit) . ", 
                        (fLagerbestand - " . ($nAnzahl * $Artikel->fPackeinheit) . "), fLagerbestand)
                        WHERE kArtikel = " . (int)$Artikel->kArtikel, 4
                );
                $tmpArtikel = Shop::DB()->select('tartikel', 'kArtikel', (int)$Artikel->kArtikel, null, null, null, null, false, 'fLagerbestand');
                if ($tmpArtikel !== null) {
                    $artikelBestand = (float)$tmpArtikel->fLagerbestand;
                }
                // Stücklisten Komponente
                if (ArtikelHelper::isStuecklisteKomponente($Artikel->kArtikel)) {
                    aktualisiereKomponenteLagerbestand($Artikel->kArtikel, $artikelBestand, isset($Artikel->cLagerKleinerNull) && $Artikel->cLagerKleinerNull === 'Y' ? true : false);
                }
            }
            // Aktualisiere Merkmale in tartikelmerkmal vom Vaterartikel
            if ($Artikel->kVaterArtikel > 0) {
                Artikel::beachteVarikombiMerkmalLagerbestand($Artikel->kVaterArtikel, $nArtikelAnzeigefilter);
            }
        }
    }

    return $artikelBestand;
}

/**
 * @param Artikel $oStueckListeArtikel
 * @param int|float $nAnzahl
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereStuecklistenLagerbestand($oStueckListeArtikel, $nAnzahl)
{
    $nAnzahl             = (float)$nAnzahl;
    $kStueckListe        = (int)$oStueckListeArtikel->kStueckliste;
    $bestandAlt          = (float)$oStueckListeArtikel->fLagerbestand;
    $bestandNeu          = $bestandAlt;
    $bestandUeberverkauf = $bestandAlt;

    if ($nAnzahl > 0) {
        // Gibt es lagerrelevante Komponenten in der Stückliste?
        $oKomponente_arr = Shop::DB()->query(
            "SELECT tstueckliste.kArtikel, tstueckliste.fAnzahl
                FROM tstueckliste
                JOIN tartikel
                  ON tartikel.kArtikel = tstueckliste.kArtikel
                WHERE tstueckliste.kStueckliste = {$kStueckListe}
                    AND tartikel.cLagerBeachten = 'Y'", 2
        );

        if (is_array($oKomponente_arr) && count($oKomponente_arr) > 0) {
            // wenn ja, dann wird für diese auch der Bestand aktualisiert
            $options = Artikel::getDefaultOptions();

            $options->nKeineSichtbarkeitBeachten = 1;

            foreach ($oKomponente_arr as $oKomponente) {
                $tmpArtikel = new Artikel();
                $tmpArtikel->fuelleArtikel($oKomponente->kArtikel, $options);

                $komponenteBestand = floor(aktualisiereLagerbestand($tmpArtikel, $nAnzahl * $oKomponente->fAnzahl, null) / $oKomponente->fAnzahl);

                if ($komponenteBestand < $bestandNeu && $tmpArtikel->cLagerKleinerNull !== 'Y') {
                    // Neuer Bestand ist der Kleinste Komponententbestand aller Artikel ohne Überverkauf
                    $bestandNeu = $komponenteBestand;
                } elseif ($komponenteBestand < $bestandUeberverkauf) {
                    // Für Komponenten mit Überverkauf wird der kleinste Bestand ermittelt.
                    $bestandUeberverkauf = $komponenteBestand;
                }
            }
        }

        // Ist der alte gleich dem neuen Bestand?
        if ($bestandAlt === $bestandNeu) {
            // Es sind keine lagerrelevanten Komponenten vorhanden, die den Bestand der Stückliste herabsetzen.
            if ($bestandUeberverkauf === $bestandNeu) {
                // Es gibt auch keine Komponenten mit Überverkäufen, die den Bestand verringern, deshalb wird
                // der Bestand des Stücklistenartikels anhand des Verkaufs verringert
                $bestandNeu = $bestandNeu - $nAnzahl * $oStueckListeArtikel->fPackeinheit;
            } else {
                // Da keine lagerrelevanten Komponenten vorhanden sind, wird der kleinste Bestand der
                // Komponentent mit Überverkauf verwendet.
                $bestandNeu = $bestandUeberverkauf;
            }

            Shop::DB()->update('tartikel', 'kArtikel', (int)$oStueckListeArtikel->kArtikel, (object)[
                'fLagerbestand' => $bestandNeu,
            ]);
        }
        // Kein Lagerbestands-Update für die Stückliste notwendig! Dies erfolgte bereits über die Komponentenabfrage und
        // die dortige Lagerbestandsaktualisierung!
    }

    return $bestandNeu;
}

/**
 * @param int $kKomponenteArtikel
 * @param int|float $fLagerbestand
 * @param bool $bLagerKleinerNull
 * @return void
 */
function aktualisiereKomponenteLagerbestand($kKomponenteArtikel, $fLagerbestand, $bLagerKleinerNull)
{
    $kKomponenteArtikel = (int)$kKomponenteArtikel;
    $fLagerbestand      = (float)$fLagerbestand;

    $oStueckliste_arr = Shop::DB()->query(
        "SELECT tstueckliste.kStueckliste, tstueckliste.fAnzahl,
                tartikel.kArtikel, tartikel.fLagerbestand, tartikel.cLagerKleinerNull
            FROM tstueckliste
            JOIN tartikel
                ON tartikel.kStueckliste = tstueckliste.kStueckliste
            WHERE tstueckliste.kArtikel = {$kKomponenteArtikel}
                AND tartikel.cLagerBeachten = 'Y'", 2
    );

    if (is_array($oStueckliste_arr) && count($oStueckliste_arr) > 0) {
        foreach ($oStueckliste_arr as $oStueckliste) {
            // Ist der aktuelle Bestand der Stückliste größer als dies mit dem Bestand der Komponente möglich wäre?
            $maxAnzahl = floor($fLagerbestand / $oStueckliste->fAnzahl);
            if ($maxAnzahl < (float)$oStueckliste->fLagerbestand && (!$bLagerKleinerNull || $oStueckliste->cLagerKleinerNull === 'Y')) {
                // wenn ja, dann den Bestand der Stückliste entsprechend verringern, aber nur wenn die Komponente nicht
                // überberkaufbar ist oder die gesamte Stückliste Überverkäufe zulässt
                Shop::DB()->update('tartikel', 'kArtikel', (int)$oStueckliste->kArtikel, (object)[
                    'fLagerbestand' => $maxAnzahl,
                ]);
            }
        }
    }
}

/**
 * @param int       $kArtikelKomponente
 * @param int|float $nAnzahl
 * @param null|int  $kStueckliste
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereAndereStuecklisten($kArtikelKomponente, $nAnzahl, $kStueckliste = null)
{
    $kArtikelKomponente = (int)$kArtikelKomponente;

    if ($kArtikelKomponente > 0) {
        $tmpArtikel = new Artikel();
        $tmpArtikel->fuelleArtikel($kArtikelKomponente, Artikel::getDefaultOptions());
        aktualisiereKomponenteLagerbestand($kArtikelKomponente, $tmpArtikel->fLagerbestand, $tmpArtikel->cLagerKleinerNull);
    }
}

/**
 * @param int       $kStueckliste
 * @param float     $fPackeinheitSt
 * @param float     $fLagerbestandSt
 * @param int|float $nAnzahl
 * @deprecated since 4.06 - dont use anymore
 */
function AktualisiereStueckliste($kStueckliste, $fPackeinheitSt, $fLagerbestandSt, $nAnzahl)
{
    $kStueckliste  = (int)$kStueckliste;
    $fLagerbestand = (float)$fLagerbestandSt;
    Shop::DB()->update('tartikel', 'kStueckliste', $kStueckliste, (object)['fLagerbestand' => $fLagerbestand]);
}

/**
 * @param Artikel        $oArtikel
 * @param null|int|float $nAnzahl
 * @param bool           $bStueckliste
 * @deprecated since 4.06 - use aktualisiereStuecklistenLagerbestand instead
 */
function AktualisiereLagerStuecklisten($oArtikel, $nAnzahl = null, $bStueckliste = false)
{
    if (is_object($oArtikel) && isset($oArtikel->kArtikel) && $oArtikel->kArtikel > 0) {
        if ($bStueckliste) {
            aktualisiereStuecklistenLagerbestand($oArtikel, $nAnzahl);
        } else {
            aktualisiereKomponenteLagerbestand($oArtikel->kArtikel, $oArtikel->fLagerbestand, $oArtikel->cLagerKleinerNull);
        }
    }
}

/**
 * @param $oBestellung
 */
function KuponVerwendungen($oBestellung)
{
    $kKupon           = 0;
    $cKuponTyp        = '';
    $fKuponwertBrutto = 0;
    if (isset($_SESSION['VersandKupon']->kKupon) && $_SESSION['VersandKupon']->kKupon > 0) {
        $kKupon           = $_SESSION['VersandKupon']->kKupon;
        $cKuponTyp        = 'versand';
        $fKuponwertBrutto = $_SESSION['Versandart']->fPreis;
    }
    if (isset($_SESSION['NeukundenKupon']->kKupon) && $_SESSION['NeukundenKupon']->kKupon > 0) {
        $kKupon    = $_SESSION['NeukundenKupon']->kKupon;
        $cKuponTyp = 'neukunden';
    }
    if (isset($_SESSION['Kupon']->kKupon) && $_SESSION['Kupon']->kKupon > 0) {
        $kKupon = $_SESSION['Kupon']->kKupon;
        if (isset($_SESSION['Kupon']->cWertTyp) &&
            ($_SESSION['Kupon']->cWertTyp === 'prozent' || $_SESSION['Kupon']->cWertTyp === 'festpreis')
        ) {
            $cKuponTyp = $_SESSION['Kupon']->cWertTyp;
        }
    }
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $Position) {
            if (!isset($_SESSION['VersandKupon']) && ($Position->nPosTyp == 3 || $Position->nPosTyp == 7)) {
                $fKuponwertBrutto = berechneBrutto($Position->fPreisEinzelNetto, gibUst($Position->kSteuerklasse)) * (-1);
            }
        }
    }
    $kKupon = (int)$kKupon;
    if ($kKupon > 0) {
        Shop::DB()->query("UPDATE tkupon SET nVerwendungenBisher = nVerwendungenBisher + 1 WHERE kKupon = " . $kKupon, 4);
        $KuponKunde                = new stdClass();
        $KuponKunde->kKupon        = $kKupon;
        $KuponKunde->kKunde        = $_SESSION['Warenkorb']->kKunde;
        $KuponKunde->cMail         = Shop::DB()->escape(StringHandler::filterXSS($_SESSION['Kunde']->cMail));
        $KuponKunde->dErstellt     = 'now()';
        $KuponKunde->nVerwendungen = 1;
        $KuponKundeBisher          = Shop::DB()->query(
            "SELECT SUM(nVerwendungen) AS nVerwendungen
                FROM tkuponkunde 
                WHERE cMail = '{$KuponKunde->cMail}'", 1
        );
        if (isset($KuponKundeBisher->nVerwendungen) && $KuponKundeBisher->nVerwendungen > 0) {
            $KuponKunde->nVerwendungen += $KuponKundeBisher->nVerwendungen;
        }
        Shop::DB()->delete('tkuponkunde', ['kKunde', 'kKupon'], [(int)$KuponKunde->kKunde, $kKupon]);
        Shop::DB()->insert('tkuponkunde', $KuponKunde);

        if (isset($_SESSION['NeukundenKupon']->kKupon) && $_SESSION['NeukundenKupon']->kKupon > 0) {
            Shop::DB()->delete('tkuponneukunde', ['kKupon', 'cEmail'], [$kKupon, $_SESSION['Kunde']->cMail]);
        }

        $oKuponBestellung                     = new KuponBestellung();
        $oKuponBestellung->kKupon             = $kKupon;
        $oKuponBestellung->kBestellung        = $oBestellung->kBestellung;
        $oKuponBestellung->kKunde             = $_SESSION['Warenkorb']->kKunde;
        $oKuponBestellung->cBestellNr         = $oBestellung->cBestellNr;
        $oKuponBestellung->fGesamtsummeBrutto = $oBestellung->fGesamtsumme;
        $oKuponBestellung->fKuponwertBrutto   = $fKuponwertBrutto;
        $oKuponBestellung->cKuponTyp          = $cKuponTyp;
        $oKuponBestellung->dErstellt          = 'now()';
        $oKuponBestellung->save();
    }
}

/**
 * @return string
 */
function baueBestellnummer()
{
    $conf           = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    $oNummer        = new Nummern(JTL_GENNUMBER_ORDERNUMBER);
    $nBestellnummer = 1;
    $nIncrement     = isset($conf['kaufabwicklung']['bestellabschluss_bestellnummer_anfangsnummer'])
        ? (int)$conf['kaufabwicklung']['bestellabschluss_bestellnummer_anfangsnummer']
        : 1;
    if ($oNummer) {
        $nBestellnummer = $oNummer->getNummer() + $nIncrement;
        $oNummer->setNummer($oNummer->getNummer() + 1);
        $oNummer->update();
    }

    /*
    *   %Y = -aktuelles Jahr
    *   %m = -aktueller Monat
    *   %d = -aktueller Tag
    *   %W = -aktuelle KW
    */
    $cPraefix = str_replace(
        ['%Y', '%m', '%d', '%W'],
        [date('Y'), date('m'), date('d'), date('W')],
        $conf['kaufabwicklung']['bestellabschluss_bestellnummer_praefix']
    );
    $cSuffix  = str_replace(
        ['%Y', '%m', '%d', '%W'],
        [date('Y'), date('m'), date('d'), date('W')],
        $conf['kaufabwicklung']['bestellabschluss_bestellnummer_suffix']
    );

    return $cPraefix . $nBestellnummer . $cSuffix;
}

/**
 * @param Bestellung $oBestellung
 */
function speicherUploads($oBestellung)
{
    if (!empty($oBestellung->kBestellung) && class_exists('Upload')) {
        // Uploads speichern
        Upload::speicherUploadDateien($_SESSION['Warenkorb'], $oBestellung->kBestellung);
    }
}

/**
 * @param Bestellung $bestellung
 */
function setzeSmartyWeiterleitung($bestellung)
{
    global $Einstellungen;

    $successPaymentURL = '';
    // Uploads speichern
    speicherUploads($bestellung);

    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog(
            'setzeSmartyWeiterleitung wurde mit folgender Zahlungsart ausgefuehrt: ' .
            print_r($_SESSION['Zahlungsart'], true),
            JTLLOG_LEVEL_DEBUG,
            false,
            'cModulId',
            $_SESSION['Zahlungsart']->cModulId
        );
    }
    // Zahlungsart als Plugin
    $kPlugin = gibkPluginAuscModulId($_SESSION['Zahlungsart']->cModulId);
    if ($kPlugin > 0) {
        $oPlugin            = new Plugin($kPlugin);
        $GLOBALS['oPlugin'] = $oPlugin;
        if ($oPlugin->kPlugin > 0) {
            require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION .
                $oPlugin->nVersion . '/' . PFAD_PLUGIN_PAYMENTMETHOD .
                $oPlugin->oPluginZahlungsKlasseAssoc_arr[$_SESSION['Zahlungsart']->cModulId]->cClassPfad;
            $pluginClass = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$_SESSION['Zahlungsart']->cModulId]->cClassName;
            /** @var PaymentMethod $paymentMethod */
            $paymentMethod           = new $pluginClass($_SESSION['Zahlungsart']->cModulId);
            $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
            $paymentMethod->preparePaymentProcess($bestellung);
            Shop::Smarty()->assign('oPlugin', $oPlugin);
        }
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_paypal_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paypal/PayPal.class.php';
        $paymentMethod           = new PayPal($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_worldpay_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'worldpay/WorldPay.class.php';
        $paymentMethod           = new WorldPay($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_moneybookers_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'moneybookers/moneybookers.php';
        Shop::Smarty()->assign(
            'moneybookersform',
            gib_moneybookers_form(
                $bestellung,
                strtolower($Einstellungen['zahlungsarten']['zahlungsart_moneybookers_empfaengermail']),
                $successPaymentURL
            )
        );
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ipayment_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ipayment/iPayment.class.php';
        $paymentMethod           = new iPayment($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_sofortueberweisung_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'sofortueberweisung/SofortUeberweisung.class.php';
        $paymentMethod           = new SofortUeberweisung($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_stand_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_dd_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_cc_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_prepaid_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_gi_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_ut_ebank_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
        $paymentMethod           = new UT($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_safetypay') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/confirmation.php';
        Shop::Smarty()->assign('safetypay_form', show_confirmation($bestellung));
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_wirecard_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'wirecard/Wirecard.class.php';
        $paymentMethod           = new Wirecard($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_postfinance_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'postfinance/PostFinance.class.php';
        $paymentMethod           = new PostFinance($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_paymentpartner_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paymentpartner/PaymentPartner.class.php';
        $paymentMethod           = new PaymentPartner($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif (strpos($_SESSION['Zahlungsart']->cModulId, 'za_mbqc_') === 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'moneybookers_qc/MoneyBookersQC.class.php';
        $paymentMethod           = new MoneyBookersQC($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_eos_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
        $paymentMethod           = new EOS($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } // EOS Payment Solution
    elseif ($_SESSION['Zahlungsart']->cModulId === 'za_eos_dd_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
        $paymentMethod           = new EOS($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_eos_cc_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
        $paymentMethod           = new EOS($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_eos_direct_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
        $paymentMethod           = new EOS($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_eos_ewallet_jtl') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
        $paymentMethod           = new EOS($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif (strpos($_SESSION['Zahlungsart']->cModulId, 'za_billpay') === 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $paymentMethod           = PaymentMethod::create($_SESSION['Zahlungsart']->cModulId);
        $paymentMethod->cModulId = $_SESSION['Zahlungsart']->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
    } elseif ($_SESSION['Zahlungsart']->cModulId === 'za_kreditkarte_jtl' ||
        $_SESSION['Zahlungsart']->cModulId === 'za_lastschrift_jtl'
    ) {
        Shop::Smarty()->assign('abschlussseite', 1);
    }

    executeHook(HOOK_BESTELLABSCHLUSS_INC_SMARTYWEITERLEITUNG);
}

/**
 * @return Bestellung
 */
function fakeBestellung()
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */

    if (isset($_POST['kommentar'])) {
        $_SESSION['kommentar'] = substr(strip_tags(Shop::DB()->escape($_POST['kommentar'])), 0, 1000);
    }
    $bestellung                   = new Bestellung();
    $bestellung->kKunde           = $_SESSION['Warenkorb']->kKunde;
    $bestellung->kWarenkorb       = $_SESSION['Warenkorb']->kWarenkorb;
    $bestellung->kLieferadresse   = $_SESSION['Warenkorb']->kLieferadresse;
    $bestellung->kZahlungsart     = $_SESSION['Zahlungsart']->kZahlungsart;
    $bestellung->kVersandart      = $_SESSION['Versandart']->kVersandart;
    $bestellung->kSprache         = Shop::getLanguage();
    $bestellung->kWaehrung        = $_SESSION['Waehrung']->kWaehrung;
    $bestellung->fGesamtsumme     = $_SESSION['Warenkorb']->gibGesamtsummeWaren(1);
    $bestellung->fWarensumme      = $bestellung->fGesamtsumme;
    $bestellung->cVersandartName  = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']];
    $bestellung->cZahlungsartName = $_SESSION['Zahlungsart']->angezeigterName[$_SESSION['cISOSprache']];
    $bestellung->cSession         = session_id();
    $bestellung->cKommentar       = $_SESSION['kommentar'];
    $bestellung->cAbgeholt        = 'N';
    $bestellung->cStatus          = BESTELLUNG_STATUS_OFFEN;
    $bestellung->dErstellt        = 'now()';
    $bestellung->Zahlungsart      = $_SESSION['Zahlungsart'];
    $bestellung->Positionen       = [];
    $bestellung->Waehrung         = $_SESSION['Waehrung'];
    $bestellung->kWaehrung        = $_SESSION['Waehrung']->kWaehrung;
    $bestellung->fWaehrungsFaktor = $_SESSION['Waehrung']->fFaktor;
    if ($bestellung->oRechnungsadresse === null) {
        $bestellung->oRechnungsadresse = new stdClass();
    }
    $bestellung->oRechnungsadresse->cVorname    = $_SESSION['Kunde']->cVorname;
    $bestellung->oRechnungsadresse->cNachname   = $_SESSION['Kunde']->cNachname;
    $bestellung->oRechnungsadresse->cFirma      = $_SESSION['Kunde']->cFirma;
    $bestellung->oRechnungsadresse->kKunde      = $_SESSION['Kunde']->kKunde;
    $bestellung->oRechnungsadresse->cAnrede     = $_SESSION['Kunde']->cAnrede;
    $bestellung->oRechnungsadresse->cTitel      = $_SESSION['Kunde']->cTitel;
    $bestellung->oRechnungsadresse->cStrasse    = $_SESSION['Kunde']->cStrasse;
    $bestellung->oRechnungsadresse->cHausnummer = $_SESSION['Kunde']->cHausnummer;
    $bestellung->oRechnungsadresse->cPLZ        = $_SESSION['Kunde']->cPLZ;
    $bestellung->oRechnungsadresse->cOrt        = $_SESSION['Kunde']->cOrt;
    $bestellung->oRechnungsadresse->cLand       = $_SESSION['Kunde']->cLand;
    $bestellung->oRechnungsadresse->cTel        = $_SESSION['Kunde']->cTel;
    $bestellung->oRechnungsadresse->cMobil      = $_SESSION['Kunde']->cMobil;
    $bestellung->oRechnungsadresse->cFax        = $_SESSION['Kunde']->cFax;
    $bestellung->oRechnungsadresse->cUSTID      = $_SESSION['Kunde']->cUSTID;
    $bestellung->oRechnungsadresse->cWWW        = $_SESSION['Kunde']->cWWW;
    $bestellung->oRechnungsadresse->cMail       = $_SESSION['Kunde']->cMail;

    if (isset($_SESSION['Lieferadresse']) && strlen($_SESSION['Lieferadresse']->cVorname) > 0) {
        $bestellung->Lieferadresse = gibLieferadresseAusSession();
    }
    $bestellung->cBestellNr = date('dmYHis') . substr($bestellung->cSession, 0, 4);
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        $bestellung->Positionen = [];
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPositionen) {
            $bestellung->Positionen[$i] = new WarenkorbPos();
            $cMember_arr                = array_keys(get_object_vars($oPositionen));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $bestellung->Positionen[$i]->$cMember = $oPositionen->$cMember;
                }
            }

            $bestellung->Positionen[$i]->cName = $bestellung->Positionen[$i]->cName[$_SESSION['cISOSprache']];
            $bestellung->Positionen[$i]->fMwSt = gibUst($oPositionen->kSteuerklasse);
            $bestellung->Positionen[$i]->setzeGesamtpreisLocalized();
        }
    }
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) {
        $bestellung->fGuthaben = -$_SESSION['Bestellung']->fGuthabenGenutzt;
    }
    $conf = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    if ($conf['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'Y') {
        $bestellung->cIP = gibIP();
    }

    return $bestellung->fuelleBestellung(0, true);
}

/**
 * @return null|stdClass
 */
function gibLieferadresseAusSession()
{
    if (isset($_SESSION['Lieferadresse']) && strlen($_SESSION['Lieferadresse']->cVorname) > 0) {
        $oLieferadresse              = new stdClass();
        $oLieferadresse->cVorname    = $_SESSION['Lieferadresse']->cVorname;
        $oLieferadresse->cNachname   = $_SESSION['Lieferadresse']->cNachname;
        $oLieferadresse->cFirma      = isset($_SESSION['Lieferadresse']->cFirma)
            ? $_SESSION['Lieferadresse']->cFirma
            : null;
        $oLieferadresse->kKunde      = $_SESSION['Lieferadresse']->kKunde;
        $oLieferadresse->cAnrede     = $_SESSION['Lieferadresse']->cAnrede;
        $oLieferadresse->cTitel      = $_SESSION['Lieferadresse']->cTitel;
        $oLieferadresse->cStrasse    = $_SESSION['Lieferadresse']->cStrasse;
        $oLieferadresse->cHausnummer = $_SESSION['Lieferadresse']->cHausnummer;
        $oLieferadresse->cPLZ        = $_SESSION['Lieferadresse']->cPLZ;
        $oLieferadresse->cOrt        = $_SESSION['Lieferadresse']->cOrt;
        $oLieferadresse->cLand       = $_SESSION['Lieferadresse']->cLand;
        $oLieferadresse->cTel        = $_SESSION['Lieferadresse']->cTel;
        $oLieferadresse->cMobil      = isset($_SESSION['Lieferadresse']->cMobil)
            ? $_SESSION['Lieferadresse']->cMobil
            : null;
        $oLieferadresse->cFax        = isset($_SESSION['Lieferadresse']->cFax)
            ? $_SESSION['Lieferadresse']->cFax
            : null;
        $oLieferadresse->cUSTID      = isset($_SESSION['Lieferadresse']->cUSTID)
            ? $_SESSION['Lieferadresse']->cUSTID
            : null;
        $oLieferadresse->cWWW        = isset($_SESSION['Lieferadresse']->cWWW)
            ? $_SESSION['Lieferadresse']->cWWW
            : null;
        $oLieferadresse->cMail       = $_SESSION['Lieferadresse']->cMail;
        $oLieferadresse->cAnrede     = $_SESSION['Lieferadresse']->cAnrede;

        return $oLieferadresse;
    }

    return null;
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis.
 *
 * @return array
 */
function pruefeVerfuegbarkeit()
{
    $xResult_arr   = ['cArtikelName_arr' => []];
    $Einstellungen = Shop::getSettings([CONF_GLOBAL]);
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPosition) {
            if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                // Mit Lager arbeiten und Lagerbestand darf < 0 werden?
                if (isset($oPosition->Artikel->cLagerBeachten) && $oPosition->Artikel->cLagerBeachten === 'Y' &&
                    $oPosition->Artikel->cLagerKleinerNull === 'Y' &&
                    $Einstellungen['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                ) {
                    if ($oPosition->nAnzahl > $oPosition->Artikel->fLagerbestand) {
                        $xResult_arr['cArtikelName_arr'][] = $oPosition->Artikel->cName;
                    }
                }
            }
        }
    }

    if (count($xResult_arr['cArtikelName_arr']) > 0) {
        $cHinweis                = str_replace('%s', '', Shop::Lang()->get('orderExpandInventory', 'basket'));
        $xResult_arr['cHinweis'] = $cHinweis;
    }

    return $xResult_arr;
}

/**
 * @param string $cBestellNr
 * @param bool   $bSendeMail
 *
 * @return Bestellung
 */
function finalisiereBestellung($cBestellNr = '', $bSendeMail = true)
{
    $obj                      = new stdClass();
    $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();

    bestellungInDB(0, $cBestellNr);

    $bestellung = new Bestellung($_SESSION['kBestellung']);
    $bestellung->fuelleBestellung(0);
    $bestellung->machGoogleAnalyticsReady();

    if ($bestellung->oRechnungsadresse !== null) {
        $hash = Kuponneukunde::Hash(
            null,
            trim($bestellung->oRechnungsadresse->cNachname),
            trim($bestellung->oRechnungsadresse->cStrasse),
            null,
            trim($bestellung->oRechnungsadresse->cPLZ),
            trim($bestellung->oRechnungsadresse->cOrt),
            trim($bestellung->oRechnungsadresse->cLand)
        );
        Shop::DB()->update('tkuponneukunde', 'cDatenHash', $hash, (object)['cVerwendet' => 'Y']);
    }

    $_upd              = new stdClass();
    $_upd->kKunde      = (int)$_SESSION['Warenkorb']->kKunde;
    $_upd->kBestellung = (int)$bestellung->kBestellung;
    Shop::DB()->update('tbesucher', 'cIP', gibIP(), $_upd);
    //mail versenden
    $obj->tkunde      = $_SESSION['Kunde'];
    $obj->tbestellung = $bestellung;

    if (isset($bestellung->oEstimatedDelivery->longestMin, $bestellung->oEstimatedDelivery->longestMax)) {
        $obj->tbestellung->cEstimatedDeliveryEx = dateAddWeekday(
            $bestellung->dErstellt,
            $bestellung->oEstimatedDelivery->longestMin
        )->format('d.m.Y')
            . ' - ' .
            dateAddWeekday($bestellung->dErstellt, $bestellung->oEstimatedDelivery->longestMax)->format('d.m.Y');
    }

    // Work Around cLand
    $oKunde = new Kunde();
    $oKunde->kopiereSession();
    if ($bSendeMail === true) {
        sendeMail(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj);
    }
    $_SESSION['Kunde'] = $oKunde;
    $kKundengruppe     = Kundengruppe::getCurrent();
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.CheckBox.php';
    $oCheckBox = new CheckBox();
    // CheckBox Spezialfunktion ausführen
    $oCheckBox->triggerSpecialFunction(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        $kKundengruppe,
        true,
        $_POST,
        ['oBestellung' => $bestellung, 'oKunde' => $oKunde]
    );
    $oCheckBox->checkLogging(CHECKBOX_ORT_BESTELLABSCHLUSS, $kKundengruppe, $_POST, true);

    return $bestellung;
}

/**
 * EOS Server to Server.
 *
 * @param string $cSh
 */
function pruefeEOSServerCom($cSh)
{
    if (strlen($cSh) > 0 && strlen(verifyGPDataString('eos')) > 0) {
        $oZahlungbackground              = new stdClass();
        $oZahlungbackground->cSID        = $cSh;
        $oZahlungbackground->cKey        = 'eos';
        $oZahlungbackground->kKey        = verifyGPCDataInteger('eos');
        $oZahlungbackground->cCustomData = '';
        $oZahlungbackground->dErstellt   = 'now()';

        Shop::DB()->insert('tzahlungbackground', $oZahlungbackground);

        if (NO_MODE === 1) {
            Jtllog::writeLog(NO_PFAD, 'pruefeEOSServerCom Hash ' .
                $cSh . ' ergab ' . print_r($oZahlungbackground, true), 1);
        }
        die();
    }
}
