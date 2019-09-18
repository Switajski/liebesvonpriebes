<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Bestellung
 */
class Bestellung
{
    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kRechnungsadresse;

    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kZahlungsart;

    /**
     * @var int
     */
    public $kVersandart;

    /**
     * @var int
     */
    public $kWaehrung;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var float
     */
    public $fGuthaben = 0.0;

    /**
     * @var int
     */
    public $fGesamtsumme;

    /**
     * @var string
     */
    public $cSession;

    /**
     * @var string
     */
    public $cBestellNr;

    /**
     * @var string
     */
    public $cVersandInfo;

    /**
     * @var string
     */
    public $cTracking;

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var string
     */
    public $cVersandartName;

    /**
     * @var string
     */
    public $cZahlungsartName;

    /**
     * @var string - 'Y'/'N'
     */
    public $cAbgeholt;

    /**
     * @var string 'Y'/'N'
     */
    public $cStatus;

    /**
     * @var string - datetime [yyyy.mm.dd hh:ii:ss]
     */
    public $dVersandDatum = '0000-00-00';

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dBezahltDatum = '0000-00-00';

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var object {
     *      localized: string,
     *      longestMin: int,
     *      longestMax: int,
     * }
     */
    public $oEstimatedDelivery;

    /**
     * @var WarenkorbPos[]
     */
    public $Positionen;

    /**
     * @var PaymentMethod
     */
    public $Zahlungsart;

    /**
     * @var Lieferadresse
     */
    public $Lieferadresse;

    /**
     * @var Rechnungsadresse
     */
    public $oRechnungsadresse;

    /**
     * @var Versandart
     */
    public $oVersandart;

    /**
     * @var null|string
     */
    public $dBewertungErinnerung;

    /**
     * @var string
     */
    public $cLogistiker = '';

    /**
     * @var string
     */
    public $cTrackingURL = '';

    /**
     * @var string
     */
    public $cIP = '';

    /**
     * @var Kunde
     */
    public $oKunde;

    /**
     * @var string
     */
    public $BestellstatusURL;

    /**
     * @var string
     */
    public $dVersanddatum_de;

    /**
     * @var string
     */
    public $dBezahldatum_de;

    /**
     * @var string
     */
    public $dErstelldatum_de;

    /**
     * @var string
     */
    public $dVersanddatum_en;

    /**
     * @var string
     */
    public $dBezahldatum_en;

    /**
     * @var string
     */
    public $dErstelldatum_en;

    /**
     * @var
     */
    public $cBestellwertLocalized;

    /**
     * @var
     */
    public $Waehrung;

    /**
     * @var
     */
    public $Steuerpositionen;

    /**
     * @var
     */
    public $Status;

    /**
     * @var array
     */
    public $oLieferschein_arr;

    /**
     * @var ZahlungsInfo
     */
    public $Zahlungsinfo;

    /**
     * @var int
     */
    public $GuthabenNutzen;

    /**
     * @var string
     */
    public $GutscheinLocalized;

    /**
     * @var float
     */
    public $fWarensumme;

    /**
     * @var float
     */
    public $fVersand = 0.0;

    /**
     * @var float
     */
    public $fWarensummeNetto = 0.0;

    /**
     * @var float
     */
    public $fVersandNetto = 0.0;

    /**
     * @var array
     */
    public $oUpload_arr;

    /**
     * @var array
     */
    public $oDownload_arr;

    /**
     * @var float
     */
    public $fGesamtsummeNetto;

    /**
     * @var float
     */
    public $fWarensummeKundenwaehrung;

    /**
     * @var float
     */
    public $fVersandKundenwaehrung;

    /**
     * @var float
     */
    public $fSteuern;

    /**
     * @var float
     */
    public $fGesamtsummeKundenwaehrung;

    /**
     * @var array
     */
    public $WarensummeLocalized = [];

    /**
     * @var float
     */
    public $fWaehrungsFaktor = 1.0;

    /**
     * @var string
     */
    public $cPUIZahlungsdaten;

    /**
     * Konstruktor
     *
     * @param int  $kBestellung Falls angegeben, wird der Bestellung mit angegebenem kBestellung aus der DB geholt
     * @param bool $bFill
     */
    public function __construct($kBestellung = 0, $bFill = false)
    {
        $kBestellung = (int)$kBestellung;
        if ($kBestellung > 0) {
            $this->loadFromDB($kBestellung);
            if ($bFill) {
                $this->fuelleBestellung();
            }
        }
    }

    /**
     * Setzt Bestellung mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kBestellung - Primary Key
     * @return $this
     */
    public function loadFromDB($kBestellung)
    {
        $obj = Shop::DB()->select('tbestellung', 'kBestellung', (int)$kBestellung);
        if (isset($obj->kBestellung) && $obj->kBestellung > 0) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }
        }

        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            $this->setEstimatedDelivery($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            $this->setEstimatedDelivery();
        }

        return $this;
    }

    /**
     * @param int $htmlWaehrung
     * @param int $nZahlungExtern
     * @param bool $bArtikel
     * @param bool $disableFactor - @see #8544, hack to avoid applying currency factor twice
     * @return $this
     */
    public function fuelleBestellung($htmlWaehrung = 1, $nZahlungExtern = 0, $bArtikel = true, $disableFactor = false)
    {
        if ($this->kWarenkorb > 0 || $nZahlungExtern > 0) {
            $warenwert = null;
            $date      = null;
            if ($this->kWarenkorb > 0) {
                $this->Positionen = Shop::DB()->selectAll(
                    'twarenkorbpos',
                    'kWarenkorb',
                    (int)$this->kWarenkorb,
                    '*',
                    'kWarenkorbPos'
                );
                if ($this->kLieferadresse !== null && $this->kLieferadresse > 0) {
                    $this->Lieferadresse = new Lieferadresse($this->kLieferadresse);
                }
                // Rechnungsadresse holen
                if ($this->kRechnungsadresse !== null && $this->kRechnungsadresse > 0) {
                    $oRechnungsadresse = new Rechnungsadresse($this->kRechnungsadresse);
                    if ($oRechnungsadresse->kRechnungsadresse > 0) {
                        $this->oRechnungsadresse = $oRechnungsadresse;
                    }
                }
                // Versandart holen
                if ($this->kVersandart !== null && $this->kVersandart > 0) {
                    $oVersandart = new Versandart($this->kVersandart);

                    if ($oVersandart->kVersandart !== null && $oVersandart->kVersandart > 0) {
                        $this->oVersandart = $oVersandart;
                    }
                }
                // Kunde holen
                if ($this->kKunde !== null && $this->kKunde > 0) {
                    $oKunde = new Kunde($this->kKunde);

                    if ($oKunde->kKunde !== null && $oKunde->kKunde > 0) {
                        unset($oKunde->cPasswort, $oKunde->fRabatt, $oKunde->fGuthaben, $oKunde->cUSTID);
                        $this->oKunde = $oKunde;
                    }
                }

                $bestellstatus          = Shop::DB()->select('tbestellstatus', 'kBestellung', (int)$this->kBestellung);
                $this->BestellstatusURL = Shop::getURL() . '/status.php?uid=' . $bestellstatus->cUID;
                $warenwert              = Shop::DB()->query(
                    "SELECT sum(((fPreis*fMwSt)/100+fPreis)*nAnzahl) AS wert
                        FROM twarenkorbpos
                        WHERE kWarenkorb = " . (int)$this->kWarenkorb, 1
                );
                $date = Shop::DB()->query(
                    "SELECT date_format(dVersandDatum,'%d.%m.%Y') AS dVersanddatum_de,
                        date_format(dBezahltDatum,'%d.%m.%Y') AS dBezahldatum_de,
                        date_format(dErstellt,'%d.%m.%Y %H:%i:%s') AS dErstelldatum_de,
                        date_format(dVersandDatum,'%D %M %Y') AS dVersanddatum_en,
                        date_format(dBezahltDatum,'%D %M %Y') AS dBezahldatum_en,
                        date_format(dErstellt,'%D %M %Y') AS dErstelldatum_en
                        FROM tbestellung WHERE kBestellung = " . (int)$this->kBestellung, 1
                );
            }
            if ($date !== null && is_object($date)) {
                $this->dVersanddatum_de = $date->dVersanddatum_de;
                $this->dBezahldatum_de  = $date->dBezahldatum_de;
                $this->dErstelldatum_de = $date->dErstelldatum_de;
                $this->dVersanddatum_en = $date->dVersanddatum_en;
                $this->dBezahldatum_en  = $date->dBezahldatum_en;
                $this->dErstelldatum_en = $date->dErstelldatum_en;
            }
            // Hole Netto- oder Bruttoeinstellung der Kundengruppe
            $nNettoPreis = 0;
            if ($this->kBestellung > 0) {
                $oKundengruppeBestellung = Shop::DB()->query(
                    "SELECT tkundengruppe.nNettoPreise
                        FROM tkundengruppe
                        JOIN tbestellung 
                            ON tbestellung.kBestellung = " . (int)$this->kBestellung . "
                        JOIN tkunde 
                            ON tkunde.kKunde = tbestellung.kKunde
                        WHERE tkunde.kKundengruppe = tkundengruppe.kKundengruppe", 1
                );
                if (isset($oKundengruppeBestellung->nNettoPreise) && $oKundengruppeBestellung->nNettoPreise > 0) {
                    $nNettoPreis = 1;
                }
            }
            $this->cBestellwertLocalized = gibPreisStringLocalized((isset($warenwert->wert) ? $warenwert->wert : 0), $htmlWaehrung);
            $this->Status                = lang_bestellstatus($this->cStatus);
            if ($this->kWaehrung > 0) {
                $this->Waehrung = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$this->kWaehrung);
                if ($this->fWaehrungsFaktor !== null && $this->fWaehrungsFaktor != 1 && isset($this->Waehrung->fFaktor)) {
                    $this->Waehrung->fFaktor = $this->fWaehrungsFaktor;
                }
                if ($disableFactor === true) {
                    $this->Waehrung->fFaktor = 1;
                }
            }
            $this->Steuerpositionen      = gibAlteSteuerpositionen($this->Positionen, $nNettoPreis, $htmlWaehrung, $this->Waehrung);
            if ($this->kZahlungsart > 0) {
                require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
                $this->Zahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', (int)$this->kZahlungsart);
                if ($this->Zahlungsart !== null) {
                    $oPaymentMethod = new PaymentMethod($this->Zahlungsart->cModulId, 1);
                    $oZahlungsart   = $oPaymentMethod::create($this->Zahlungsart->cModulId);
                    if ($oZahlungsart !== null) {
                        $this->Zahlungsart->bPayAgain = $oZahlungsart->canPayAgain();
                    }
                }
            }
            if ($this->kBestellung > 0) {
                $this->Zahlungsinfo = new ZahlungsInfo(0, $this->kBestellung);
            }
            if ((float)$this->fGuthaben) {
                $this->GuthabenNutzen = 1;
            }
            $this->GutscheinLocalized = gibPreisStringLocalized($this->fGuthaben, $htmlWaehrung);
            $summe                    = 0;
            $this->fWarensumme        = 0;
            $this->fVersand           = 0;
            $this->fWarensummeNetto   = 0;
            $this->fVersandNetto      = 0;
            $positionCount            = count($this->Positionen);
            $defaultOptions           = Artikel::getDefaultOptions();
            for ($i = 0; $i < $positionCount; $i++) {
                if ($this->Positionen[$i]->nAnzahl == (int)$this->Positionen[$i]->nAnzahl) {
                    $this->Positionen[$i]->nAnzahl = (int)$this->Positionen[$i]->nAnzahl;
                }
                if ($this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_VERSANDPOS ||
                    $this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_VERSANDZUSCHLAG ||
                    $this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR ||
                    $this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG ||
                    $this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_VERPACKUNG
                ) {
                    $this->fVersandNetto += $this->Positionen[$i]->fPreis;
                    $this->fVersand += $this->Positionen[$i]->fPreis + ($this->Positionen[$i]->fPreis * $this->Positionen[$i]->fMwSt) / 100;
                } else {
                    $this->fWarensummeNetto += $this->Positionen[$i]->fPreis * $this->Positionen[$i]->nAnzahl;
                    $this->fWarensumme += ($this->Positionen[$i]->fPreis + ($this->Positionen[$i]->fPreis * $this->Positionen[$i]->fMwSt) / 100) * $this->Positionen[$i]->nAnzahl;
                }

                if ($this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                    if ($bArtikel) {
                        $this->Positionen[$i]->Artikel = new Artikel();
                        $this->Positionen[$i]->Artikel->fuelleArtikel($this->Positionen[$i]->kArtikel, $defaultOptions);
                    }

                    $kSprache = (isset($_SESSION['kSprache']) ? $_SESSION['kSprache'] : null);
                    if (!$kSprache) {
                        $oSprache             = Shop::DB()->query("SELECT kSprache FROM tsprache WHERE cStandard = 'Y'", 1);
                        $kSprache             = $oSprache->kSprache;
                        $_SESSION['kSprache'] = $kSprache;
                    }
                    // Downloads
                    if (class_exists('Download')) {
                        $this->oDownload_arr = Download::getDownloads(['kBestellung' => $this->kBestellung], $kSprache);
                    }
                    // Uploads
                    if (class_exists('Upload')) {
                        $this->oUpload_arr = Upload::gibBestellungUploads($this->kBestellung);
                    }
                    if ($this->Positionen[$i]->kWarenkorbPos > 0) {
                        $this->Positionen[$i]->WarenkorbPosEigenschaftArr = Shop::DB()->selectAll(
                            'twarenkorbposeigenschaft',
                            'kWarenkorbPos',
                            (int)$this->Positionen[$i]->kWarenkorbPos
                        );
                        $fpositionCount = count($this->Positionen[$i]->WarenkorbPosEigenschaftArr);
                        for ($o = 0; $o < $fpositionCount; $o++) {
                            if ($this->Positionen[$i]->WarenkorbPosEigenschaftArr[$o]->fAufpreis) {
                                $this->Positionen[$i]->WarenkorbPosEigenschaftArr[$o]->cAufpreisLocalized[0] = gibPreisStringLocalized(
                                    berechneBrutto(
                                        $this->Positionen[$i]->WarenkorbPosEigenschaftArr[$o]->fAufpreis,
                                        $this->Positionen[$i]->fMwSt
                                    ),
                                    $this->Waehrung,
                                    $htmlWaehrung
                                );
                                $this->Positionen[$i]->WarenkorbPosEigenschaftArr[$o]->cAufpreisLocalized[1] = gibPreisStringLocalized(
                                    $this->Positionen[$i]->WarenkorbPosEigenschaftArr[$o]->fAufpreis,
                                    $this->Waehrung,
                                    $htmlWaehrung
                                );
                            }
                        }
                    }

                    WarenkorbPos::setEstimatedDelivery($this->Positionen[$i], $this->Positionen[$i]->nLongestMinDelivery, $this->Positionen[$i]->nLongestMaxDelivery);
                }
                if (!isset($this->Positionen[$i]->kSteuerklasse)) {
                    $taxClass = Shop::DB()->select('tsteuersatz', 'fSteuersatz', $this->Positionen[$i]->fMwSt);
                    if ($taxClass !== null) {
                        $this->Positionen[$i]->kSteuerklasse = $taxClass->kSteuerklasse;
                    }
                }
                $summe += $this->Positionen[$i]->fPreis * $this->Positionen[$i]->nAnzahl;
                if ($this->kWarenkorb > 0) {
                    $this->Positionen[$i]->cGesamtpreisLocalized[0] = gibPreisStringLocalized(
                        berechneBrutto(
                            $this->Positionen[$i]->fPreis * $this->Positionen[$i]->nAnzahl,
                            $this->Positionen[$i]->fMwSt
                        ),
                        $this->Waehrung, $htmlWaehrung
                    );
                    $this->Positionen[$i]->cGesamtpreisLocalized[1] = gibPreisStringLocalized(
                        $this->Positionen[$i]->fPreis * $this->Positionen[$i]->nAnzahl,
                        $this->Waehrung, $htmlWaehrung
                    );
                    $this->Positionen[$i]->cEinzelpreisLocalized[0] = gibPreisStringLocalized(
                        berechneBrutto($this->Positionen[$i]->fPreis, $this->Positionen[$i]->fMwSt),
                        $this->Waehrung, $htmlWaehrung
                    );
                    $this->Positionen[$i]->cEinzelpreisLocalized[1] = gibPreisStringLocalized(
                        $this->Positionen[$i]->fPreis,
                        $this->Waehrung,
                        $htmlWaehrung
                    );

                    // Konfigurationsartikel: mapto: 9a87wdgad
                    if ((int)$this->Positionen[$i]->kKonfigitem > 0 &&
                        is_string($this->Positionen[$i]->cUnique) &&
                        strlen($this->Positionen[$i]->cUnique) === 10
                    ) {
                        $fPreisNetto  = 0;
                        $fPreisBrutto = 0;
                        $nVaterPos    = null;

                        foreach ($this->Positionen as $nPos => $oPosition) {
                            if ($this->Positionen[$i]->cUnique === $oPosition->cUnique) {
                                $fPreisNetto += $oPosition->fPreis * $oPosition->nAnzahl;
                                $ust = isset($oPosition->kSteuerklasse)
                                    ? gibUst($oPosition->kSteuerklasse)
                                    : gibUst(null);
                                $fPreisBrutto += berechneBrutto($oPosition->fPreis * $oPosition->nAnzahl, $ust);
                                if ((int)$oPosition->kKonfigitem === 0 &&
                                    is_string($oPosition->cUnique) &&
                                    strlen($oPosition->cUnique) === 10
                                ) {
                                    $nVaterPos = $nPos;
                                }
                            }
                        }

                        if ($nVaterPos !== null) {
                            $oVaterPos = $this->Positionen[$nVaterPos];
                            if (is_object($oVaterPos)) {
                                $this->Positionen[$i]->nAnzahlEinzel       = $this->Positionen[$i]->nAnzahl / $oVaterPos->nAnzahl;
                                $oVaterPos->cKonfigpreisLocalized[0]       = gibPreisStringLocalized($fPreisBrutto, $this->Waehrung);
                                $oVaterPos->cKonfigpreisLocalized[1]       = gibPreisStringLocalized($fPreisNetto, $this->Waehrung);
                                $oVaterPos->cKonfigeinzelpreisLocalized[0] = gibPreisStringLocalized($fPreisBrutto / $oVaterPos->nAnzahl, $this->Waehrung);
                                $oVaterPos->cKonfigeinzelpreisLocalized[1] = gibPreisStringLocalized($fPreisNetto / $oVaterPos->nAnzahl, $this->Waehrung);
                            }
                        }
                    }
                }

                $this->Positionen[$i]->kLieferschein_arr   = [];
                $this->Positionen[$i]->nAusgeliefert       = 0;
                $this->Positionen[$i]->nAusgeliefertGesamt = 0;
                $this->Positionen[$i]->bAusgeliefert       = false;
                $this->Positionen[$i]->nOffenGesamt        = $this->Positionen[$i]->nAnzahl;
            }

            $this->WarensummeLocalized[0]     = gibPreisStringLocalized($this->fGesamtsumme, $this->Waehrung, $htmlWaehrung);
            $this->WarensummeLocalized[1]     = gibPreisStringLocalized($summe + $this->fGuthaben, $this->Waehrung, $htmlWaehrung);
            $this->fGesamtsummeNetto          = $summe + $this->fGuthaben;
            $this->fWarensummeKundenwaehrung  = ($this->fWarensumme + $this->fGuthaben) * $this->fWaehrungsFaktor;
            $this->fVersandKundenwaehrung     = $this->fVersand * $this->fWaehrungsFaktor;
            $this->fSteuern                   = $this->fGesamtsumme - $this->fGesamtsummeNetto;
            $this->fGesamtsummeKundenwaehrung = optionaleRundung($this->fWarensummeKundenwaehrung + $this->fVersandKundenwaehrung);

            $oData       = new stdClass();
            $oData->cPLZ = isset($this->oRechnungsadresse->cPLZ)
                ? $this->oRechnungsadresse->cPLZ
                : $this->Lieferadresse->cPLZ;
            $this->oLieferschein_arr = [];
            $kLieferschein_arr       = Shop::DB()->selectAll('tlieferschein', 'kInetBestellung', (int)$this->kBestellung, 'kLieferschein');
            foreach ($kLieferschein_arr as $oLieferschein) {
                $oLieferschein                = new Lieferschein($oLieferschein->kLieferschein, $oData);
                $oLieferschein->oPosition_arr = [];
                /** @var Lieferscheinpos $oLieferscheinPos */
                foreach ($oLieferschein->oLieferscheinPos_arr as &$oLieferscheinPos) {
                    foreach ($this->Positionen as &$oPosition) {
                        if (in_array($oPosition->nPosTyp, [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_GRATISGESCHENK])) {
                            if ($oLieferscheinPos->getBestellPos() == $oPosition->kBestellpos) {
                                $oPosition->kLieferschein_arr[] = $oLieferschein->getLieferschein();
                                $oPosition->nAusgeliefert       = $oLieferscheinPos->getAnzahl();
                                $oPosition->nAusgeliefertGesamt += $oPosition->nAusgeliefert;
                                $oPosition->nOffenGesamt -= $oPosition->nAusgeliefert;
                                $oLieferschein->oPosition_arr[] = &$oPosition;
                                if (!isset($oLieferscheinPos->oPosition) || !is_object($oLieferscheinPos->oPosition)) {
                                    $oLieferscheinPos->oPosition = &$oPosition;
                                }
                                if ($oPosition->nOffenGesamt == 0) {
                                    $oPosition->bAusgeliefert = true;
                                }
                            }
                        }
                    }
                    // Charge, MDH & Seriennummern
                    if (isset($oLieferscheinPos->oPosition) && is_object($oLieferscheinPos->oPosition)) {
                        /** @var Lieferscheinposinfo $oLieferscheinPosInfo */
                        foreach ($oLieferscheinPos->oLieferscheinPosInfo_arr as $oLieferscheinPosInfo) {
                            $mhd    = $oLieferscheinPosInfo->getMHD();
                            $serial = $oLieferscheinPosInfo->getSeriennummer();
                            $charge = $oLieferscheinPosInfo->getChargeNr();
                            if (strlen($charge) > 0) {
                                $oLieferscheinPos->oPosition->cChargeNr = $charge;
                            }
                            if ($mhd !== '0000-00-00 00:00:00' && strlen($mhd) > 0) {
                                $oLieferscheinPos->oPosition->dMHD    = $mhd;
                                $oLieferscheinPos->oPosition->dMHD_de = date_format(date_create($mhd), 'd.m.Y');
                            }
                            if (strlen($serial) > 0) {
                                $oLieferscheinPos->oPosition->cSeriennummer = $serial;
                            }
                        }
                    }
                }
                $this->oLieferschein_arr[] = $oLieferschein;
            }
            // Wenn Konfig-Vater, alle Kinder ueberpruefen
            foreach ($this->oLieferschein_arr as &$oLieferschein) {
                foreach ($oLieferschein->oPosition_arr as &$oPosition) {
                    if ($oPosition->kKonfigitem == 0 && strlen($oPosition->cUnique) > 0) {
                        $bAlleAusgeliefert = true;
                        foreach ($this->Positionen as $oKind) {
                            if ($oKind->cUnique == $oPosition->cUnique &&
                                $oKind->kKonfigitem > 0 &&
                                !$oKind->bAusgeliefert
                            ) {
                                $bAlleAusgeliefert = false;
                            }
                        }
                        $oPosition->bAusgeliefert = $bAlleAusgeliefert;
                    }
                }
            }
            // Fallback for Non-Beta
            if ($this->cStatus == BESTELLUNG_STATUS_VERSANDT) {
                $positionCountB = count($this->Positionen);
                for ($i = 0; $i < $positionCountB; $i++) {
                    $this->Positionen[$i]->nAusgeliefertGesamt = $this->Positionen[$i]->nAnzahl;
                    $this->Positionen[$i]->bAusgeliefert       = true;
                    $this->Positionen[$i]->nOffenGesamt        = 0;
                }
            }

            if (empty($this->oEstimatedDelivery->localized)) {
                $this->berechneEstimatedDelivery();
            }

            executeHook(HOOK_BESTELLUNG_CLASS_FUELLEBESTELLUNG, [
                'oBestellung' => $this
            ]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function machGoogleAnalyticsReady()
    {
        $positionCount = count($this->Positionen);
        for ($i = 0; $i < $positionCount; $i++) {
            if ($this->Positionen[$i]->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && $this->Positionen[$i]->kArtikel > 0) {
                $artikel                = new Artikel();
                $artikel->kArtikel      = $this->Positionen[$i]->kArtikel;
                $AufgeklappteKategorien = new KategorieListe();
                $kategorie              = new Kategorie($artikel->gibKategorie());
                $AufgeklappteKategorien->getOpenCategories($kategorie);
                $this->Positionen[$i]->Category = '';
                $elemCount                      = count($AufgeklappteKategorien->elemente) - 1;
                for ($o = $elemCount; $o >= 0; $o--) {
                    $this->Positionen[$i]->Category = $AufgeklappteKategorien->elemente[$o]->cName;
                    if ($o > 0) {
                        $this->Positionen[$i]->Category .= ' / ';
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return mixed
     */
    public function insertInDB()
    {
        $obj                       = new stdClass();
        $obj->kWarenkorb           = $this->kWarenkorb;
        $obj->kKunde               = $this->kKunde;
        $obj->kLieferadresse       = $this->kLieferadresse;
        $obj->kRechnungsadresse    = $this->kRechnungsadresse;
        $obj->kZahlungsart         = $this->kZahlungsart;
        $obj->kVersandart          = $this->kVersandart;
        $obj->kSprache             = $this->kSprache;
        $obj->kWaehrung            = $this->kWaehrung;
        $obj->fGuthaben            = $this->fGuthaben;
        $obj->fGesamtsumme         = $this->fGesamtsumme;
        $obj->cSession             = $this->cSession;
        $obj->cVersandartName      = $this->cVersandartName;
        $obj->cZahlungsartName     = $this->cZahlungsartName;
        $obj->cBestellNr           = $this->cBestellNr;
        $obj->cVersandInfo         = $this->cVersandInfo;
        $obj->nLongestMinDelivery  = $this->oEstimatedDelivery->longestMin;
        $obj->nLongestMaxDelivery  = $this->oEstimatedDelivery->longestMax;
        $obj->dVersandDatum        = $this->dVersandDatum;
        $obj->dBezahltDatum        = $this->dBezahltDatum;
        $obj->dBewertungErinnerung = ($this->dBewertungErinnerung !== null)
            ? $this->dBewertungErinnerung
            : '0000-00-00 00:00:00';
        $obj->cTracking            = $this->cTracking;
        $obj->cKommentar           = $this->cKommentar;
        $obj->cLogistiker          = $this->cLogistiker;
        $obj->cTrackingURL         = $this->cTrackingURL;
        $obj->cIP                  = $this->cIP;
        $obj->cAbgeholt            = $this->cAbgeholt;
        $obj->cStatus              = $this->cStatus;
        $obj->dErstellt            = $this->dErstellt;
        $obj->fWaehrungsFaktor     = $this->fWaehrungsFaktor;
        $obj->cPUIZahlungsdaten    = $this->cPUIZahlungsdaten;

        $this->kBestellung = Shop::DB()->insert('tbestellung', $obj);

        return $this->kBestellung;
    }

    /**
     * Update data with same primary key in db
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj                       = new stdClass();
        $obj->kBestellung          = $this->kBestellung;
        $obj->kWarenkorb           = $this->kWarenkorb;
        $obj->kKunde               = $this->kKunde;
        $obj->kLieferadresse       = $this->kLieferadresse;
        $obj->kRechnungsadresse    = $this->kRechnungsadresse;
        $obj->kZahlungsart         = $this->kZahlungsart;
        $obj->kVersandart          = $this->kVersandart;
        $obj->kSprache             = $this->kSprache;
        $obj->kWaehrung            = $this->kWaehrung;
        $obj->fGuthaben            = $this->fGuthaben;
        $obj->fGesamtsumme         = $this->fGesamtsumme;
        $obj->cSession             = $this->cSession;
        $obj->cVersandartName      = $this->cVersandartName;
        $obj->cZahlungsartName     = $this->cZahlungsartName;
        $obj->cBestellNr           = $this->cBestellNr;
        $obj->cVersandInfo         = $this->cVersandInfo;
        $obj->nLongestMinDelivery  = $this->oEstimatedDelivery->longestMin;
        $obj->nLongestMaxDelivery  = $this->oEstimatedDelivery->longestMax;
        $obj->dVersandDatum        = $this->dVersandDatum;
        $obj->dBezahltDatum        = $this->dBezahltDatum;
        $obj->dBewertungErinnerung = $this->dBewertungErinnerung;
        $obj->cTracking            = $this->cTracking;
        $obj->cKommentar           = $this->cKommentar;
        $obj->cLogistiker          = $this->cLogistiker;
        $obj->cTrackingURL         = $this->cTrackingURL;
        $obj->cIP                  = $this->cIP;
        $obj->cAbgeholt            = $this->cAbgeholt;
        $obj->cStatus              = $this->cStatus;
        $obj->dErstellt            = $this->dErstellt;
        $obj->cPUIZahlungsdaten    = $this->cPUIZahlungsdaten;

        return Shop::DB()->update('tbestellung', 'kBestellung', $obj->kBestellung, $obj);
    }

    /**
     * @param int    $kBestellung
     * @param bool   $bAssoc
     * @param string $nPosTyp
     * @return array
     */
    public static function getOrderPositions($kBestellung, $bAssoc = true, $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL)
    {
        $oPosition_arr = [];
        $kBestellung   = (int)$kBestellung;
        if ($kBestellung > 0) {
            $oObj_arr = Shop::DB()->query(
                "SELECT twarenkorbpos.kWarenkorbPos, twarenkorbpos.kArtikel
                      FROM tbestellung
                      JOIN twarenkorbpos
                        ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                          AND nPosTyp = " . (int)$nPosTyp . "
                      WHERE tbestellung.kBestellung = " . $kBestellung, 2
            );

            if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                foreach ($oObj_arr as $oObj) {
                    if (isset($oObj->kWarenkorbPos) && $oObj->kWarenkorbPos > 0) {
                        if ($bAssoc) {
                            $oPosition_arr[$oObj->kArtikel] = new WarenkorbPos($oObj->kWarenkorbPos);
                        } else {
                            $oPosition_arr[] = new WarenkorbPos($oObj->kWarenkorbPos);
                        }
                    }
                }
            }
        }

        return $oPosition_arr;
    }

    /**
     * @param int $kBestellung
     * @return int|bool
     */
    public static function getOrderNumber($kBestellung)
    {
        $kBestellung = (int)$kBestellung;
        if ($kBestellung > 0) {
            $oObj = Shop::DB()->select(
                'tbestellung',
                'kBestellung',
                $kBestellung,
                null,
                null,
                null,
                null,
                false,
                'cBestellNr'
            );
            if (isset($oObj->cBestellNr) && strlen($oObj->cBestellNr) > 0) {
                return $oObj->cBestellNr;
            }
        }

        return false;
    }

    /**
     * @param int $kBestellung
     * @param int $kArtikel
     * @return int
     */
    public static function getProductAmount($kBestellung, $kArtikel)
    {
        $kBestellung = (int)$kBestellung;
        $kArtikel    = (int)$kArtikel;
        if ($kBestellung > 0 && $kArtikel > 0) {
            $oObj = Shop::DB()->query(
                "SELECT twarenkorbpos.nAnzahl
                    FROM tbestellung
                    JOIN twarenkorbpos
                        ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                    WHERE tbestellung.kBestellung = " . $kBestellung . "
                        AND twarenkorbpos.kArtikel = " . $kArtikel, 1
            );
            if (isset($oObj->nAnzahl) && $oObj->nAnzahl > 0) {
                return $oObj->nAnzahl;
            }
        }

        return 0;
    }

    /**
     * @param int|null $nMinDelivery
     * @param int|null $nMaxDelivery
     */
    public function setEstimatedDelivery($nMinDelivery = null, $nMaxDelivery = null)
    {
        $this->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($nMinDelivery !== null && $nMaxDelivery !== null) {
            $this->oEstimatedDelivery->longestMin = (int)$nMinDelivery;
            $this->oEstimatedDelivery->longestMax = (int)$nMaxDelivery;

            $this->oEstimatedDelivery->localized = (!empty($this->oEstimatedDelivery->longestMin) &&
                !empty($this->oEstimatedDelivery->longestMax))
                ? getDeliverytimeEstimationText(
                    $this->oEstimatedDelivery->longestMin,
                    $this->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $this->cEstimatedDelivery = &$this->oEstimatedDelivery->localized;
    }

    /**
     * @return Bestellung
     */
    public function berechneEstimatedDelivery()
    {
        if (is_array($this->Positionen) && count($this->Positionen) > 0) {
            $longestMinDeliveryDays = 0;
            $longestMaxDeliveryDays = 0;
            //Lookup language iso
            $lang = Shop::DB()->select('tsprache', 'kSprache', (int)$this->kSprache);
            foreach ($this->Positionen as $i => $oPosition) {
                if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL &&
                    isset($oPosition->Artikel) &&
                    get_class($oPosition->Artikel) === 'Artikel'
                ) {
                    $oPosition->Artikel->getDeliveryTime(
                        isset($this->Lieferadresse->cLand) ? $this->Lieferadresse->cLand : null,
                        $oPosition->nAnzahl,
                        $oPosition->fLagerbestandVorAbschluss,
                        isset($lang->cISOSprache) ? $lang->cISOSprache : null,
                        $this->kVersandart
                    );
                    WarenkorbPos::setEstimatedDelivery($oPosition, $oPosition->Artikel->nMinDeliveryDays, $oPosition->Artikel->nMaxDeliveryDays);
                    if (isset($oPosition->Artikel->nMinDeliveryDays) && $oPosition->Artikel->nMinDeliveryDays > $longestMinDeliveryDays) {
                        $longestMinDeliveryDays = $oPosition->Artikel->nMinDeliveryDays;
                    }
                    if (isset($oPosition->Artikel->nMaxDeliveryDays) && $oPosition->Artikel->nMaxDeliveryDays > $longestMaxDeliveryDays) {
                        $longestMaxDeliveryDays = $oPosition->Artikel->nMaxDeliveryDays;
                    }
                }
            }

            $this->setEstimatedDelivery($longestMinDeliveryDays, $longestMaxDeliveryDays);
        } else {
            $this->setEstimatedDelivery();
        }

        return $this;
    }

    /**
     * @deprecated since 4.6
     * @return string
     */
    public function getEstimatedDeliveryTime()
    {
        if (empty($this->oEstimatedDelivery->localized)) {
            $this->berechneEstimatedDelivery();
        }

        return $this->oEstimatedDelivery->localized;
    }
}
