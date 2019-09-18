<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfigitem
     */
    class Konfigitem implements JsonSerializable
    {
        /**
         * @var int
         */
        protected $kKonfigitem;

        /**
         * @var int
         */
        protected $kArtikel;

        /**
         * @var int
         */
        protected $nPosTyp;

        /**
         * @var int
         */
        protected $kKonfiggruppe;

        /**
         * @var
         */
        protected $bSelektiert;

        /**
         * @var
         */
        protected $bEmpfohlen;

        /**
         * @var
         */
        protected $bPreis;

        /**
         * @var
         */
        protected $bName;
        /**
         * @var
         */
        protected $bRabatt;
        /**
         * @var
         */
        protected $bZuschlag;
        /**
         * @var
         */
        protected $bIgnoreMultiplier;
        /**
         * @var float
         */
        protected $fMin;

        /**
         * @var float
         */
        protected $fMax;

        /**
         * @var float
         */
        protected $fInitial;

        /**
         * @var Konfigitemsprache
         */
        protected $oSprache;

        /**
         * @var Konfigitempreis
         */
        protected $oPreis;

        /**
         * @var Artikel
         */
        protected $oArtikel;

        /**
         * @var int
         */
        protected $kSprache;

        /**
         * @var int
         */
        protected $kKundengruppe;

        /**
         * @var int
         */
        protected $nSort = 0;

        /**
         * @var int|null
         */
        public $fAnzahl;

        /**
         * @var int|null
         */
        public $fAnzahlWK;

        /**
         * @var bool|null
         */
        public $bAktiv;

        /**
         * Constructor
         *
         * @param int $kKonfigitem - primary key
         * @param int $kSprache
         * @param int $kKundengruppe
         * @access public
         */
        public function __construct($kKonfigitem = 0, $kSprache = 0, $kKundengruppe = 0)
        {
            if ((int)$kKonfigitem > 0) {
                $this->loadFromDB($kKonfigitem, $kSprache, $kKundengruppe);
            }
        }

        /**
         * Specify data which should be serialized to JSON
         *
         * @return array
         */
        public function jsonSerialize()
        {
            $cKurzBeschreibung = $this->getKurzBeschreibung();
            $virtual = [
                'bAktiv' => $this->{"bAktiv"}
            ];
            $override = [
                'cName'             => $this->getName(),
                'kArtikel'          => $this->getArtikelKey(),
                'cBeschreibung'     => !empty($cKurzBeschreibung)
                    ? $this->getKurzBeschreibung()
                    : $this->getBeschreibung(),

                'bAnzahl'           => $this->getMin() != $this->getMax(),
                'fInitial'          => (float)$this->getInitial(),
                'fMin'              => (float)$this->getMin(),
                'fMax'              => (float)$this->getMax(),
                'cBildPfad'         => $this->getBildPfad(),
                'fPreis'            => [
                    (float)$this->getPreis(),
                    (float)$this->getPreis(true)
                ],
                'fPreisLocalized' => [
                    gibPreisStringLocalized($this->getPreis()),
                    gibPreisStringLocalized($this->getPreis(true))
                ]
            ];
            $result = array_merge($override, $virtual);

            return utf8_convert_recursive($result);
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfigitem
         * @param int $kSprache
         * @param int $kKundengruppe
         * @return $this
         */
        private function loadFromDB($kKonfigitem = 0, $kSprache = 0, $kKundengruppe = 0)
        {
            $oObj = Shop::DB()->select('tkonfigitem', 'kKonfigitem', (int)$kKonfigitem);

            if (isset($oObj->kKonfigitem) && $oObj->kKonfigitem > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }

                if (!$kSprache) {
                    $kSprache = isset($_SESSION['kSprache'])
                        ? $_SESSION['kSprache']
                        : getDefaultLanguageID();
                }
                if (!$kKundengruppe) {
                    $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
                }
                $this->kKonfiggruppe     = (int)$this->kKonfiggruppe;
                $this->kKonfigitem       = (int)$this->kKonfigitem;
                $this->kArtikel          = (int)$this->kArtikel;
                $this->nPosTyp           = (int)$this->nPosTyp;
                $this->nSort             = (int)$this->nSort;
                $this->bSelektiert       = (int)$this->bSelektiert;
                $this->bEmpfohlen        = (int)$this->bEmpfohlen;
                $this->bName             = (int)$this->bName;
                $this->bPreis            = (int)$this->bPreis;
                $this->bRabatt           = (int)$this->bRabatt;
                $this->bZuschlag         = (int)$this->bZuschlag;
                $this->bIgnoreMultiplier = (int)$this->bIgnoreMultiplier;
                $this->kSprache          = (int)$kSprache;
                $this->kKundengruppe     = (int)$kKundengruppe;
                $this->oSprache          = new Konfigitemsprache($this->kKonfigitem, $kSprache);
                $this->oPreis            = new Konfigitempreis($this->kKonfigitem, $kKundengruppe);
                $this->oArtikel          = null;
                if ($this->kArtikel > 0) {
                    $oArtikelOptionen                             = new stdClass();
                    $oArtikelOptionen->nAttribute                 = 1;
                    $oArtikelOptionen->nArtikelAttribute          = 1;
                    $oArtikelOptionen->nVariationKombi            = 1;
                    $oArtikelOptionen->nVariationKombiKinder      = 1;
                    $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
                    $oArtikelOptionen->nVariationen               = 0;

                    $this->oArtikel = new Artikel();
                    $this->oArtikel->fuelleArtikel($this->kArtikel, $oArtikelOptionen, $kKundengruppe, $kSprache);
                }
            }

            return $this;
        }

        /**
         * @return bool
         */
        public function isValid()
        {
            if ($this->kArtikel > 0) {
                if (!$this->oArtikel->kArtikel) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Store in database
         *
         * @param bool $bPrim - Controls the return of the method
         * @return bool|int
         * @access public
         */
        public function save($bPrim = true)
        {
            $oObj                    = new stdClass();
            $oObj->kKonfiggruppe     = $this->kKonfiggruppe;
            $oObj->kArtikel          = $this->kArtikel;
            $oObj->nPosTyp           = $this->nPosTyp;
            $oObj->bSelektiert       = $this->bSelektiert;
            $oObj->bEmpfohlen        = $this->bEmpfohlen;
            $oObj->bName             = $this->bName;
            $oObj->bPreis            = $this->bPreis;
            $oObj->bRabatt           = $this->bRabatt;
            $oObj->bZuschlag         = $this->bZuschlag;
            $oObj->bIgnoreMultiplier = $this->bIgnoreMultiplier;
            $oObj->fMin              = $this->fMin;
            $oObj->fMax              = $this->fMax;
            $oObj->fInitial          = $this->fInitial;
            $oObj->nSort             = $this->nSort;

            $kPrim = Shop::DB()->insert('tkonfigitem', $oObj);
            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }

            return false;
        }

        /**
         * @return mixed
         */
        public function update()
        {
            $_upd                    = new stdClass();
            $_upd->kKonfiggruppe     = $this->kKonfiggruppe;
            $_upd->kArtikel          = $this->kArtikel;
            $_upd->nPosTyp           = $this->nPosTyp;
            $_upd->bSelektiert       = $this->bSelektiert;
            $_upd->bEmpfohlen        = $this->bEmpfohlen;
            $_upd->bPreis            = $this->bPreis;
            $_upd->bName             = $this->bName;
            $_upd->bRabatt           = $this->bRabatt;
            $_upd->bZuschlag         = $this->bZuschlag;
            $_upd->bIgnoreMultiplier = $this->bIgnoreMultiplier;
            $_upd->fMin              = $this->fMin;
            $_upd->fMax              = $this->fMax;
            $_upd->fInitial          = $this->fInitial;
            $_upd->nSort             = $this->nSort;

            return Shop::DB()->update('tkonfigitem', 'kKonfigitem', (int)$this->kKonfigitem, $_upd);
        }

        /**
         * @return mixed
         */
        public function delete()
        {
            return Shop::DB()->delete('tkonfigitem', 'kKonfigitem', (int)$this->kKonfigitem);
        }

        /**
         * @param int $kKonfiggruppe
         * @return array|bool
         */
        public static function fetchAll($kKonfiggruppe)
        {
            $oItem_arr = Shop::DB()->query("
                SELECT kKonfigitem 
                    FROM tkonfigitem 
                    WHERE kKonfiggruppe = " . (int)$kKonfiggruppe . " 
                    ORDER BY nSort ASC", 2
            );
            if (!is_array($oItem_arr)) {
                return false;
            }
            $oItemEx_arr = [];
            foreach ($oItem_arr as &$oItem) {
                $kKonfigitem = $oItem->kKonfigitem;
                $oItem       = new self($kKonfigitem);
                if ($oItem->isValid()) {
                    $oItemEx_arr[] = $oItem;
                }
            }

            return $oItemEx_arr;
        }

        /**
         * @param int $kKonfigitem
         * @return $this
         */
        public function setKonfigitem($kKonfigitem)
        {
            $this->kKonfigitem = (int)$kKonfigitem;

            return $this;
        }

        /**
         * @param int $kArtikel
         * @return $this
         */
        public function setArtikelKey($kArtikel)
        {
            $this->kArtikel = (int)$kArtikel;

            return $this;
        }

        /**
         * @param Artikel $oArtikel
         * @return $this
         */
        public function setArtikel($oArtikel)
        {
            $this->oArtikel = $oArtikel;

            return $this;
        }

        /**
         * @param int $nPosTyp
         * @return $this
         */
        public function setPosTyp($nPosTyp)
        {
            $this->nPosTyp = (int)$nPosTyp;

            return $this;
        }

        /**
         * Gets the kKonfigitem
         *
         * @access public
         * @return int
         */
        public function getKonfigitem()
        {
            return (int)$this->kKonfigitem;
        }

        /**
         * @return int
         */
        public function getKonfiggruppe()
        {
            return (int)$this->kKonfiggruppe;
        }

        /**
         * Gets the oArtikel
         *
         * @access public
         * @return int
         */
        public function getArtikelKey()
        {
            return (int)$this->kArtikel;
        }

        /**
         * Gets the oArtikel
         *
         * @access public
         * @return object
         */
        public function getArtikel()
        {
            return $this->oArtikel;
        }

        /**
         * Gets the nPosTyp
         *
         * @access public
         * @return int
         */
        public function getPosTyp()
        {
            return $this->nPosTyp;
        }

        /**
         * @return int
         */
        public function getSelektiert()
        {
            return $this->bSelektiert;
        }

        /**
         * @return int
         */
        public function getEmpfohlen()
        {
            return $this->bEmpfohlen;
        }

        /**
         * Gets the oSprache
         *
         * @access public
         * @return Konfigitemsprache
         */
        public function getSprache()
        {
            return $this->oSprache;
        }

        /**
         * @return string
         */
        public function getName()
        {
            if ($this->oArtikel && $this->bName) {
                return $this->oArtikel->cName;
            }

            if ($this->oSprache) {
                return $this->oSprache->getName();
            }

            return '';
        }

        /**
         * @return string
         */
        public function getBeschreibung()
        {
            if ($this->oArtikel && $this->bName) {
                return $this->oArtikel->cBeschreibung;
            }

            if ($this->oSprache) {
                return $this->oSprache->getBeschreibung();
            }

            return '';
        }

        /**
         * @return string
         */
        public function getKurzBeschreibung()
        {
            if ($this->oArtikel && $this->bName) {
                return $this->oArtikel->cKurzBeschreibung;
            }

            if ($this->oSprache) {
                return $this->oSprache->getBeschreibung();
            }

            return '';
        }

        /**
         * @return string|null
         */
        public function getBildPfad()
        {
            if ($this->oArtikel) {
                if ($this->oArtikel->Bilder[0]->cPfadKlein !== BILD_KEIN_ARTIKELBILD_VORHANDEN) {
                    return $this->oArtikel->Bilder[0];
                }
            }

            return null;
        }

        /**
         * @return bool
         */
        public function getUseOwnName()
        {
            return !$this->bName;
        }

        /**
         * @param bool $bForceNetto
         * @param bool $bConvertCurrency
         * @return float|int
         */
        public function getPreis($bForceNetto = false, $bConvertCurrency = false)
        {
            $fVKPreis    = 0.0;
            $isConverted = false;
            if ($this->oArtikel && $this->bPreis) {
                //get price from associated article
                $fVKPreis = isset($this->oArtikel->Preise->fVKNetto) ? $this->oArtikel->Preise->fVKNetto : 0;
                // Zuschlag / Rabatt berechnen
                $fSpecial = $this->oPreis->getPreis($bConvertCurrency);
                if ($fSpecial != 0) {
                    // Betrag
                    if ($this->oPreis->getTyp() == 0) {
                        $fVKPreis += $fSpecial;
                    } elseif ($this->oPreis->getTyp() == 1) { // Prozent
                        $fVKPreis *= (100 + $fSpecial) / 100;
                    }
                }
            } elseif ($this->oPreis) {
                $fVKPreis    = $this->oPreis->getPreis($bConvertCurrency);
                $isConverted = true;
            }
            if ($bConvertCurrency && !$isConverted) {
                if (isset($_SESSION['Waehrung'])) {
                    $waehrung = $_SESSION['Waehrung'];
                } else {
                    $waehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
                }
                $fVKPreis *= (float)$waehrung->fFaktor;
            }
            if (!$_SESSION['Kundengruppe']->nNettoPreise && !$bForceNetto) {
                $fVKPreis = berechneBrutto($fVKPreis, gibUst($this->getSteuerklasse()), 4);
            }

            return $fVKPreis;
        }

        /**
         * @param bool $bForceNetto
         * @param bool $bConvertCurrency
         * @param int $totalAmount
         * @return float|int
         */
        public function getFullPrice($bForceNetto = false, $bConvertCurrency = false, $totalAmount = 1)
        {
            $fVKPreis    = 0.0;
            $isConverted = false;
            if ($this->oArtikel && $this->bPreis) {
                //get price from associated article
                $fVKPreis = isset($this->oArtikel->Preise->fVKNetto) ? $this->oArtikel->Preise->fVKNetto : 0;
                // Zuschlag / Rabatt berechnen
                $fSpecial = $this->oPreis->getPreis($bConvertCurrency);
                if ($fSpecial != 0) {
                    // Betrag
                    if ($this->oPreis->getTyp() == 0) {
                        $fVKPreis += $fSpecial;
                    } elseif ($this->oPreis->getTyp() == 1) { // Prozent
                        $fVKPreis *= (100 + $fSpecial) / 100;
                    }
                }
            } elseif ($this->oPreis) {
                $fVKPreis    = $this->oPreis->getPreis($bConvertCurrency);
                $isConverted = true;
            }
            if ($bConvertCurrency && !$isConverted) {
                if (isset($_SESSION['Waehrung'])) {
                    $waehrung = $_SESSION['Waehrung'];
                } else {
                    $waehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
                }
                $fVKPreis *= (float)$waehrung->fFaktor;
            }
            if (!$_SESSION['Kundengruppe']->nNettoPreise && !$bForceNetto) {
                $fVKPreis = berechneBrutto($fVKPreis, gibUst($this->getSteuerklasse()), 4);
            }

            return $fVKPreis * $this->fAnzahl * $totalAmount;
        }

        /**
         * @return bool
         */
        public function hasPreis()
        {
            return $this->getPreis(true) != 0;
        }

        /**
         * @return bool
         */
        public function hasRabatt()
        {
            return $this->getRabatt() > 0;
        }

        /**
         * @return float
         */
        public function getRabatt()
        {
            $fRabatt = 0.0;
            if ($this->oArtikel && $this->bPreis) {
                $fTmp = $this->oPreis->getPreis();
                if ($fTmp < 0) {
                    $fRabatt = $fTmp * -1;
                    if ($this->oPreis->getTyp() == 0) {
                        if (!$_SESSION['Kundengruppe']->nNettoPreise) {
                            $fRabatt = berechneBrutto($fRabatt, gibUst($this->getSteuerklasse()));
                        }
                    }
                }
            }

            return $fRabatt;
        }

        /**
         * @return bool
         */
        public function hasZuschlag()
        {
            return $this->getZuschlag() > 0;
        }

        /**
         * @return float
         */
        public function getZuschlag()
        {
            $fZuschlag = 0.0;
            if ($this->oArtikel && $this->bPreis) {
                $fTmp = $this->oPreis->getPreis();
                if ($fTmp > 0) {
                    $fZuschlag = $fTmp;
                    if ($this->oPreis->getTyp() == 0) {
                        if (!$_SESSION['Kundengruppe']->nNettoPreise) {
                            $fZuschlag = berechneBrutto($fZuschlag, gibUst($this->getSteuerklasse()));
                        }
                    }
                }
            }

            return $fZuschlag;
        }

        /**
         * @param bool $bHTML
         * @return string
         */
        public function getRabattLocalized($bHTML = true)
        {
            if ($this->oPreis->getTyp() == 0) {
                return gibPreisStringLocalized($this->getRabatt(), 0, $bHTML);
            }

            return $this->getRabatt() . '%';
        }

        /**
         * @param bool $bHTML
         * @return string
         */
        public function getZuschlagLocalized($bHTML = true)
        {
            if ($this->oPreis->getTyp() == 0) {
                return gibPreisStringLocalized($this->getZuschlag(), 0, $bHTML);
            }

            return $this->getZuschlag() . '%';
        }

        /**
         * @return int
         */
        public function getSteuerklasse()
        {
            $kSteuerklasse = 0;
            if ($this->oArtikel && $this->bPreis) {
                $kSteuerklasse = $this->oArtikel->kSteuerklasse;
            } elseif ($this->oPreis) {
                $kSteuerklasse = $this->oPreis->getSteuerklasse();
            }

            return $kSteuerklasse;
        }

        /**
         * @param bool $bHTML
         * @param bool $bSigned
         * @param bool $bForceNetto
         * @return string
         */
        public function getPreisLocalized($bHTML = true, $bSigned = true, $bForceNetto = false)
        {
            $cLocalized = gibPreisStringLocalized($this->getPreis($bForceNetto), 0, $bHTML);
            if ($bSigned && $this->getPreis() > 0) {
                $cLocalized = '+' . $cLocalized;
            }

            return $cLocalized;
        }

        /**
         * @param bool $bHTML
         * @param bool $bForceNetto
         * @param int $totalAmount
         * @return string
         */
        public function getFullPriceLocalized($bHTML = true, $bForceNetto = false, $totalAmount = 1)
        {
            $cLocalized = gibPreisStringLocalized($this->getFullPrice($bForceNetto, false, $totalAmount), 0, $bHTML);

            return $cLocalized;
        }

        /**
         * @return float
         */
        public function getMin()
        {
            return $this->fMin;
        }

        /**
         * @return float
         */
        public function getMax()
        {
            return $this->fMax;
        }

        /**
         * @return float|int
         */
        public function getInitial()
        {
            if ($this->fInitial < 0) {
                $this->fInitial = 0;
            }
            if ($this->fInitial < $this->getMin()) {
                $this->fInitial = $this->getMin();
            }
            if ($this->fInitial > $this->getMax()) {
                $this->fInitial = $this->getMax();
            }

            return $this->fInitial;
        }

        /**
         * @return mixed
         */
        public function showRabatt()
        {
            return $this->bRabatt;
        }

        /**
         * @return mixed
         */
        public function showZuschlag()
        {
            return $this->bZuschlag;
        }

        /**
         * @return mixed
         */
        public function ignoreMultiplier()
        {
            return $this->bIgnoreMultiplier;
        }

        /**
         * @return int
         */
        public function getSprachKey()
        {
            return $this->kSprache;
        }

        /**
         * @return int
         */
        public function getKundengruppe()
        {
            return $this->kKundengruppe;
        }
    }
}
