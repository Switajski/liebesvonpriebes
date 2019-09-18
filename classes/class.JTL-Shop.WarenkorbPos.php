<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPos
 */
class WarenkorbPos
{
    /**
     * @var int
     */
    public $kWarenkorbPos;

    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kSteuerklasse;

    /**
     * @var int
     */
    public $kVersandklasse = 0;

    /**
     * @var int
     */
    public $nAnzahl;

    /**
     * @var int
     */
    public $nPosTyp;

    /**
     * @var float
     */
    public $fPreisEinzelNetto;

    /**
     * @var float
     */
    public $fPreis;

    /**
     * @var float
     */
    public $fMwSt;

    /**
     * @var float
     */
    public $fGesamtgewicht;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cEinheit = '';

    /**
     * @var string
     */
    public $cGesamtpreisLocalized;

    /**
     * @var string
     */
    public $cHinweis = '';

    /**
     * @var string
     */
    public $cUnique = '';

    /**
     * @var string
     */
    public $cResponsibility = '';

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var string
     */
    public $cKonfigpreisLocalized;

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * @var array
     */
    public $WarenkorbPosEigenschaftArr = [];

    /**
     * @var object[]
     */
    public $variationPicturesArr = [];

    /**
     * @var int
     */
    public $nZeitLetzteAenderung = 0;

    /**
     * @var float
     */
    public $fLagerbestandVorAbschluss = 0.0;

    /**
     * @var int
     */
    public $kBestellpos = 0;

    /**
     * @var string
     */
    public $cLieferstatus = '';

    /**
     * @var string
     */
    public $cArtNr = '';

    /**
     * @var int
     */
    public $nAnzahlEinzel;

    /**
     * @var string[]
     */
    public $cEinzelpreisLocalized;

    /**
     * @var string
     */
    public $cKonfigeinzelpreisLocalized;

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
     * Konstruktor
     *
     * @param int $kWarenkorbPos Falls angegeben, wird der WarenkorbPos mit angegebenem kWarenkorbPos aus der DB geholt
     */
    public function __construct($kWarenkorbPos = 0)
    {
        if ((int)$kWarenkorbPos > 0) {
            $this->loadFromDB($kWarenkorbPos);
        }
    }

    /**
     * Setzt in dieser Position einen Eigenschaftswert der angegebenen Eigenschaft.
     * Existiert ein EigenschaftsWert für die Eigenschaft, so wir er überschrieben, ansonsten neu angelegt
     *
     * @param int    $kEigenschaft
     * @param int    $kEigenschaftWert
     * @param string $freifeld
     * @return bool
     */
    public function setzeVariationsWert($kEigenschaft, $kEigenschaftWert, $freifeld = '')
    {
        $kEigenschaftWert                                = (int)$kEigenschaftWert;
        $kEigenschaft                                    = (int)$kEigenschaft;
        $EigenschaftWert                                 = new EigenschaftWert($kEigenschaftWert);
        $Eigenschaft                                     = new Eigenschaft($kEigenschaft);
        $NeueWarenkorbPosEigenschaft                     = new WarenkorbPosEigenschaft();
        $NeueWarenkorbPosEigenschaft->kEigenschaft       = $kEigenschaft;
        $NeueWarenkorbPosEigenschaft->kEigenschaftWert   = $kEigenschaftWert;
        $NeueWarenkorbPosEigenschaft->fGewichtsdifferenz = $EigenschaftWert->fGewichtDiff;
        $NeueWarenkorbPosEigenschaft->fAufpreis          = $EigenschaftWert->fAufpreisNetto;
        $Aufpreis_obj                                    = Shop::DB()->select(
            'teigenschaftwertaufpreis',
            'kEigenschaftWert',  (int)$NeueWarenkorbPosEigenschaft->kEigenschaftWert,
            'kKundengruppe',  (int)$_SESSION['Kundengruppe']->kKundengruppe
        );
        if (isset($Aufpreis_obj) && $Aufpreis_obj->fAufpreisNetto) {
            $NeueWarenkorbPosEigenschaft->fAufpreis = $Aufpreis_obj->fAufpreisNetto;
        }
        if ($this->Artikel->Preise->rabatt > 0) {
            $NeueWarenkorbPosEigenschaft->fAufpreis *= (1 - $this->Artikel->Preise->rabatt / 100);
        }
        $NeueWarenkorbPosEigenschaft->cTyp               = $Eigenschaft->cTyp;
        $NeueWarenkorbPosEigenschaft->cAufpreisLocalized = gibPreisStringLocalized($NeueWarenkorbPosEigenschaft->fAufpreis);
        //posname lokalisiert ablegen
        $NeueWarenkorbPosEigenschaft->cEigenschaftName     = [];
        $NeueWarenkorbPosEigenschaft->cEigenschaftWertName = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $NeueWarenkorbPosEigenschaft->cEigenschaftName[$Sprache->cISO]     = $Eigenschaft->cName;
            $NeueWarenkorbPosEigenschaft->cEigenschaftWertName[$Sprache->cISO] = $EigenschaftWert->cName;

            if ($Sprache->cStandard !== 'Y') {
                $eigenschaft_spr = Shop::DB()->select(
                    'teigenschaftsprache',
                    'kEigenschaft', (int)$NeueWarenkorbPosEigenschaft->kEigenschaft,
                    'kSprache', (int)$Sprache->kSprache
                );
                if (isset($eigenschaft_spr->cName) && $eigenschaft_spr->cName) {
                    $NeueWarenkorbPosEigenschaft->cEigenschaftName[$Sprache->cISO] = $eigenschaft_spr->cName;
                }
                $eigenschaftwert_spr = Shop::DB()->select(
                    'teigenschaftwertsprache',
                    'kEigenschaftWert', (int)$NeueWarenkorbPosEigenschaft->kEigenschaftWert,
                    'kSprache', (int)$Sprache->kSprache
                );
                if (isset($eigenschaftwert_spr->cName) && $eigenschaftwert_spr->cName) {
                    $NeueWarenkorbPosEigenschaft->cEigenschaftWertName[$Sprache->cISO] = $eigenschaftwert_spr->cName;
                }
            }

            if ($freifeld || strlen(trim($freifeld)) > 0) {
                $NeueWarenkorbPosEigenschaft->cEigenschaftWertName[$Sprache->cISO] = Shop::DB()->escape($freifeld);
                $NeueWarenkorbPosEigenschaft->cFreifeldWert                        = Shop::DB()->escape($freifeld);
            }
        }
        $this->WarenkorbPosEigenschaftArr[] = $NeueWarenkorbPosEigenschaft;
        $this->fGesamtgewicht               = $this->gibGesamtgewicht();

        return true;
    }

    /**
     * gibt EigenschaftsWert zu einer Eigenschaft bei dieser Position
     *
     * @param int $kEigenschaft - Key der Eigenschaft
     * @return int - gesetzter Wert. Falls nicht gesetzt, wird 0 zurückgegeben
     */
    public function gibGesetztenEigenschaftsWert($kEigenschaft)
    {
        if (is_array($this->WarenkorbPosEigenschaftArr) && count($this->WarenkorbPosEigenschaftArr) > 0) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->kEigenschaft == $kEigenschaft) {
                    return $WKPosEigenschaft->kEigenschaftWert;
                }
            }
        }

        return 0;
    }

    /**
     * gibt Summe der Aufpreise der Variationen dieser Position zurück
     *
     * @return float Gesamtaufpreis
     */
    public function gibGesamtAufpreis()
    {
        $aufpreis = 0;
        if (is_array($this->WarenkorbPosEigenschaftArr) && count($this->WarenkorbPosEigenschaftArr) > 0) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->fAufpreis != 0) {
                    $aufpreis += $WKPosEigenschaft->fAufpreis;
                }
            }
        }

        return $aufpreis;
    }

    /**
     * gibt Gewicht dieser Position zurück. Variationen und PosAnzahl berücksichtigt
     *
     * @return float Gewicht
     */
    public function gibGesamtgewicht()
    {
        $gewicht = $this->Artikel->fGewicht * $this->nAnzahl;

        if (!$this->Artikel->kVaterArtikel && is_array($this->WarenkorbPosEigenschaftArr) && count($this->WarenkorbPosEigenschaftArr) > 0) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->fGewichtsdifferenz != 0) {
                    $gewicht += $WKPosEigenschaft->fGewichtsdifferenz * $this->nAnzahl;
                }
            }
        }

        return $gewicht;
    }

    /**
     * Calculate the total weight of a config item and his components.
     *
     * @return float|int
     */
    public function getTotalConfigWeight()
    {
        $weight = $this->Artikel->fGewicht * $this->nAnzahl;

        if ($this->kKonfigitem === 0 && !empty($this->cUnique)) {
            foreach (Session::getInstance()->Basket()->PositionenArr as $pos) {
                if ($pos->istKonfigKind() && $pos->cUnique === $this->cUnique) {
                    $weight += $pos->fGesamtgewicht;
                }
            }
        }

        return $weight;
    }

    /**
     * typo in function name - for compatibility reasons only
     * @deprecated since 4.05
     * @return $this
     */
    public function setzeGesamtpreisLoacalized()
    {
        return $this->setzeGesamtpreisLocalized();
    }

    /**
     * gibt Gesamtpreis inkl. aller Aufpreise * Positionsanzahl lokalisiert als String zurück
     *
     * @return $this
     */
    public function setzeGesamtpreisLocalized()
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        if (is_array($_SESSION['Waehrungen'])) {
            $ust = gibUst($this->kSteuerklasse);
            foreach ($_SESSION['Waehrungen'] as $Waehrung) {
                // Standardartikel
                $this->cGesamtpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized(berechneBrutto($this->fPreis * $this->nAnzahl, $ust), $Waehrung);
                $this->cGesamtpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($this->fPreis * $this->nAnzahl, $Waehrung);
                $this->cEinzelpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized(berechneBrutto($this->fPreis, $ust), $Waehrung);
                $this->cEinzelpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($this->fPreis, $Waehrung);

                if (!empty($this->Artikel->cVPEEinheit) && isset($this->Artikel->cVPE) && $this->Artikel->cVPE === 'Y' && $this->Artikel->fVPEWert > 0) {
                    $this->Artikel->baueVPE($this->fPreis);
                }

                if ($this->istKonfigVater()) {
                    $this->cKonfigpreisLocalized[0][$Waehrung->cName]       = gibPreisStringLocalized(berechneBrutto($this->fPreis * $this->nAnzahl, $ust), $Waehrung);
                    $this->cKonfigpreisLocalized[1][$Waehrung->cName]       = gibPreisStringLocalized($this->fPreis * $this->nAnzahl, $Waehrung);
                    $this->cKonfigeinzelpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized(berechneBrutto($this->fPreis, $ust), $Waehrung);
                    $this->cKonfigeinzelpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($this->fPreis, $Waehrung);
                }

                // Konfigurationsartikel
                if ($this->istKonfigKind()) {
                    $fPreisNetto  = 0;
                    $fPreisBrutto = 0;
                    $nVaterPos    = null;
                    if ($this->cUnique) {
                        /** @var WarenkorbPos $oPosition */
                        foreach ($_SESSION['Warenkorb']->PositionenArr as $nPos => $oPosition) {
                            if ($this->cUnique == $oPosition->cUnique) {
                                $fPreisNetto  += $oPosition->fPreis * $oPosition->nAnzahl;
                                $fPreisBrutto += berechneBrutto(
                                    $oPosition->fPreis * $oPosition->nAnzahl,
                                    gibUst($oPosition->kSteuerklasse),
                                    4
                                );

                                if ($oPosition->istKonfigVater()) {
                                    $nVaterPos = $nPos;
                                }
                            }
                        }
                    }

                    if ($nVaterPos !== null) {
                        $oVaterPos = $_SESSION['Warenkorb']->PositionenArr[$nVaterPos];
                        if (is_object($oVaterPos)) {
                            if (!$this->isIgnoreMultiplier()) {
                                $this->nAnzahlEinzel = $this->nAnzahl / $oVaterPos->nAnzahl;
                            } else {
                                $this->nAnzahlEinzel = $this->nAnzahl;
                            }
                            $oVaterPos->cKonfigpreisLocalized[0][$Waehrung->cName]       = gibPreisStringLocalized($fPreisBrutto, $Waehrung);
                            $oVaterPos->cKonfigpreisLocalized[1][$Waehrung->cName]       = gibPreisStringLocalized($fPreisNetto, $Waehrung);
                            $oVaterPos->cKonfigeinzelpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized($fPreisBrutto / $oVaterPos->nAnzahl, $Waehrung);
                            $oVaterPos->cKonfigeinzelpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($fPreisNetto / $oVaterPos->nAnzahl, $Waehrung);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Setzt WarenkorbPos mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kWarenkorbPos
     * @return $this
     */
    public function loadFromDB($kWarenkorbPos)
    {
        $obj     = Shop::DB()->select('twarenkorbpos', 'kWarenkorbPos', $kWarenkorbPos);
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            self::setEstimatedDelivery($this, $this->nLongestMinDelivery, $this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            self::setEstimatedDelivery($this);
        }

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter WarenkorbPos
     */
    public function insertInDB()
    {
        $obj                            = new stdClass();
        $obj->kWarenkorb                = $this->kWarenkorb;
        $obj->kArtikel                  = $this->kArtikel;
        $obj->kVersandklasse            = $this->kVersandklasse;
        $obj->cName                     = $this->cName;
        $obj->cLieferstatus             = $this->cLieferstatus;
        $obj->cArtNr                    = $this->cArtNr;
        $obj->cEinheit                  = ($this->cEinheit === null) ? '' : $this->cEinheit;
        $obj->fPreisEinzelNetto         = $this->fPreisEinzelNetto;
        $obj->fPreis                    = $this->fPreis;
        $obj->fMwSt                     = $this->fMwSt;
        $obj->nAnzahl                   = $this->nAnzahl;
        $obj->nPosTyp                   = $this->nPosTyp;
        $obj->cHinweis                  = $this->cHinweis;
        $obj->cUnique                   = $this->cUnique;
        $obj->cResponsibility           = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $obj->kKonfigitem               = $this->kKonfigitem;
        $obj->kBestellpos               = $this->kBestellpos;
        $obj->fLagerbestandVorAbschluss = $this->fLagerbestandVorAbschluss;

        if (isset($this->oEstimatedDelivery->longestMin)) {
            // Lieferzeiten nur speichern, wenn sie gesetzt sind, also z.B. nicht bei Versandkosten etc.
            $obj->nLongestMinDelivery = $this->oEstimatedDelivery->longestMin;
            $obj->nLongestMaxDelivery = $this->oEstimatedDelivery->longestMax;
        }

        $this->kWarenkorbPos = Shop::DB()->insert('twarenkorbpos', $obj);

        return $this->kWarenkorbPos;
    }

    /**
     * @return bool
     */
    public function istKonfigVater()
    {
        return (is_string($this->cUnique) && strlen($this->cUnique) === 10 && (int)$this->kKonfigitem === 0);
    }

    /**
     * @return bool
     */
    public function istKonfigKind()
    {
        return (is_string($this->cUnique) && strlen($this->cUnique) === 10 && (int)$this->kKonfigitem > 0);
    }

    /**
     * @return bool
     */
    public function istKonfig()
    {
        return ($this->istKonfigVater() || $this->istKonfigKind());
    }

    /**
     * @param WarenkorbPos $oWarenkorbPos
     * @param int|null     $nMinDelivery
     * @param int|null     $nMaxDelivery
     */
    public static function setEstimatedDelivery($oWarenkorbPos, $nMinDelivery = null, $nMaxDelivery = null)
    {
        $oWarenkorbPos->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($nMinDelivery !== null && $nMaxDelivery !== null) {
            $oWarenkorbPos->oEstimatedDelivery->longestMin = (int)$nMinDelivery;
            $oWarenkorbPos->oEstimatedDelivery->longestMax = (int)$nMaxDelivery;

            $oWarenkorbPos->oEstimatedDelivery->localized = (!empty($oWarenkorbPos->oEstimatedDelivery->longestMin) &&
                !empty($oWarenkorbPos->oEstimatedDelivery->longestMax))
                ? getDeliverytimeEstimationText(
                    $oWarenkorbPos->oEstimatedDelivery->longestMin,
                    $oWarenkorbPos->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $oWarenkorbPos->cEstimatedDelivery = &$oWarenkorbPos->oEstimatedDelivery->localized;
    }

    /**
     * Return value of config item property bIgnoreMultiplier
     *
     * @return boolean
     */
    public function isIgnoreMultiplier()
    {
        static $ignoreMultipliers = null;

        if ($ignoreMultipliers === null) {
            $konfigItem        = new Konfigitem($this->kKonfigitem);
            $ignoreMultipliers = [
                $this->kKonfigitem => $konfigItem->ignoreMultiplier(),
            ];
        } elseif (!array_key_exists($this->kKonfigitem, $ignoreMultipliers)) {
            $konfigItem                            = new Konfigitem($this->kKonfigitem);
            $ignoreMultipliers[$this->kKonfigitem] = $konfigItem->ignoreMultiplier();
        }

        return $ignoreMultipliers[$this->kKonfigitem];
    }
}
