<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_RMA)) {
    require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.RMAArtikel.php';
    require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.RMAGrund.php';
    require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.RMAStatus.php';

    /**
     * Class RMA
     *
     * @access public
     * @author
     * @copyright
     */
    class RMA
    {
        /**
         * @access public
         * @var int
         */
        public $kRMA;

        /**
         * @access public
         * @var int
         */
        public $kKunde;

        /**
         * @access public
         * @var int
         */
        public $kRMAStatus;

        /**
         * @access public
         * @var string
         */
        public $cRMANumber;

        /**
         * @access public
         * @var string
         */
        public $dErstellt;

        /**
         * @access public
         * @var array
         */
        public $oRMAArtikel_arr;

        /**
         * @param int  $kRMA
         * @param bool $bCustomer
         * @param bool $bRMAArtikel
         * @param int  $kSprache
         */
        public function __construct($kRMA = 0, $bCustomer = false, $bRMAArtikel = true, $kSprache = 0)
        {
            if ((int)$kRMA > 0) {
                $this->loadFromDB($kRMA, $bCustomer, $bRMAArtikel, $kSprache);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param $kRMA
         * @param $bCustomer
         * @param $bRMAArtikel
         * @param $kSprache
         */
        private function loadFromDB($kRMA, $bCustomer, $bRMAArtikel, $kSprache)
        {
            $oObj = Shop::DB()->select('trma', 'kRMA', (int)$kRMA);
            if (isset($oObj->kRMA) && $oObj->kRMA > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }

                $this->oRMAStatus = new RMAStatus($this->kRMAStatus, $kSprache);

                if ($bCustomer) {
                    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';
                    $this->oKunde = new Kunde($this->kKunde);
                }

                if ($bRMAArtikel) {
                    $this->oRMAArtikel_arr = [];
                    $oObj_arr              = Shop::DB()->query("
                        SELECT kRMA, kArtikel
                            FROM trmaartikel
                            WHERE kRMA = " . (int)$kRMA, 2
                    );

                    if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                        foreach ($oObj_arr as $oObj) {
                            $this->oRMAArtikel_arr[] = new RMAArtikel($oObj->kRMA, $oObj->kArtikel, true, false, $_SESSION['kSprache'], $_SESSION['Kundengruppe']->kKundengruppe);
                        }
                    }
                }
            }
        }

        /**
         * Store the class in the database
         *
         * @param bool $bPrim - Controls the return of the method
         * @return bool|int
         * @access public
         */
        public function save($bPrim = true)
        {
            $oObj        = new stdClass();
            $cMember_arr = array_keys(get_object_vars($this));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $oObj->$cMember = $this->$cMember;
                }
            }

            unset(
                $oObj->kRMA,
                $oObj->oRMAArtikel_arr,
                $oObj->oKunde,
                $oObj->oRMAStatus
            );

            $kPrim = Shop::DB()->insert('trma', $oObj);

            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }

            return false;
        }

        /**
         * Update the class in the database
         *
         * @return int
         * @access public
         */
        public function update()
        {
            return Shop::DB()->query(
                "UPDATE trma
                   SET kRMA = " . $this->kRMA . ",
                       kKunde = " . $this->kKunde . ",
                       kRMAStatus = " . $this->kRMAStatus . ",
                       cRMANumber = '" . $this->cRMANumber . "',
                       dErstellt = '" . $this->dErstellt . "'
                   WHERE kRMA = " . $this->kRMA, 3
            );
        }

        /**
         * Delete the class in the database
         *
         * @return int
         * @access public
         */
        public function delete()
        {
            return Shop::DB()->delete('trma', 'kRMA', $this->getRMA());
        }

        /**
         * Sets the kRMA
         *
         * @access public
         * @param int $kRMA
         * @return $this
         */
        public function setRMA($kRMA)
        {
            $this->kRMA = (int)$kRMA;

            return $this;
        }

        /**
         * Sets the kKunde
         *
         * @access public
         * @param int $kKunde
         * @return $this
         */
        public function setKunde($kKunde)
        {
            $this->kKunde = (int)$kKunde;

            return $this;
        }

        /**
         * Sets the kRMAStatus
         *
         * @access public
         * @param int $kRMAStatus
         * @return $this
         */
        public function setRMAStatus($kRMAStatus)
        {
            $this->kRMAStatus = (int)$kRMAStatus;

            return $this;
        }

        /**
         * Sets the cRMANumber
         *
         * @access public
         * @param string $cRMANumber
         * @return $this
         */
        public function setRMANumber($cRMANumber)
        {
            $this->cRMANumber = Shop::DB()->escape($cRMANumber);

            return $this;
        }

        /**
         * Sets the dErstellt
         *
         * @access public
         * @param string $dErstellt
         * @return $this
         */
        public function setErstellt($dErstellt)
        {
            if ($dErstellt === 'now()') {
                $this->dErstellt = date('Y-m-d H:i:s');
            } else {
                $this->dErstellt = Shop::DB()->escape($dErstellt);
            }

            return $this;
        }

        /**
         * Gets the kRMA
         *
         * @access public
         * @return int
         */
        public function getRMA()
        {
            return (int)$this->kRMA;
        }

        /**
         * Gets the kKunde
         *
         * @access public
         * @return int
         */
        public function getKunde()
        {
            return (int)$this->kKunde;
        }

        /**
         * Gets the kRMAStatus
         *
         * @access public
         * @return int
         */
        public function getRMAStatus()
        {
            return (int)$this->kRMAStatus;
        }

        /**
         * Gets the cRMANumber
         *
         * @access public
         * @return string
         */
        public function getRMANumber()
        {
            return $this->cRMANumber;
        }

        /**
         * Gets the dErstellt
         *
         * @access public
         * @return string
         */
        public function getErstellt()
        {
            return $this->dErstellt;
        }

        /**
         * @param int $kLink
         * @param int $kPlugin
         * @return stdClass
         */
        public static function findCMSLinkInSession($kLink, $kPlugin = 0)
        {
            $kLink   = (int)$kLink;
            $kPlugin = (int)$kPlugin;
            if (($kLink > 0 || $kPlugin > 0) && isset($_SESSION['Linkgruppen']) && is_object($_SESSION['Linkgruppen'])) {
                $cMember_arr = array_keys(get_object_vars($_SESSION['Linkgruppen']));

                if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                    foreach ($cMember_arr as $cMember) {
                        if (isset($_SESSION['Linkgruppen']->$cMember->Links) && is_array($_SESSION['Linkgruppen']->$cMember->Links) && count($_SESSION['Linkgruppen']->$cMember->Links) > 0) {
                            foreach ($_SESSION['Linkgruppen']->$cMember->Links as $oLink) {
                                if ($kLink > 0) {
                                    if (isset($oLink->kLink) && $oLink->kLink == $kLink) {
                                        return $oLink;
                                    }
                                } elseif ($kPlugin > 0) {
                                    if (isset($oLink->kPlugin) && $oLink->kPlugin == $kPlugin) {
                                        return $oLink;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return new stdClass();
        }

        /**
         * @return array
         */
        public static function getCustomerOrders()
        {
            $kKunde          = (int)$_SESSION['Kunde']->kKunde;
            $oBestellung_arr = [];

            if ($kKunde > 0) {
                $cSQL = '';
                if (isset($_SESSION['RMA_TimePeriod']->nYear) && $_SESSION['RMA_TimePeriod']->nYear > 0) {
                    $cSQL = " AND dErstellt >= '" . $_SESSION['RMA_TimePeriod']->cDateFrom . "' AND dErstellt <= '" . $_SESSION['RMA_TimePeriod']->cDateTo . "'";
                }

                $oBestellungTMP_arr = Shop::DB()->query(
                    "SELECT kBestellung, date_format(dErstellt,'%d.%m.%Y') AS dBestelldatum
                        FROM tbestellung
                        WHERE kKunde = " . $kKunde . "
                            AND cStatus = '4'
                            " . $cSQL . "
                        ORDER BY kBestellung DESC", 2
                );

                if (is_array($oBestellungTMP_arr) && count($oBestellungTMP_arr) > 0) {
                    foreach ($oBestellungTMP_arr as $i => $oBestellungTMP) {
                        if (isset($oBestellungTMP->kBestellung) && $oBestellungTMP->kBestellung > 0) {
                            $oBestellung_arr[$i]                = self::getOrderProducts($oBestellungTMP->kBestellung);
                            $oBestellung_arr[$i]->dBestelldatum = $oBestellungTMP->dBestelldatum;
                        }
                    }
                }
            }

            return $oBestellung_arr;
        }

        /**
         * @param $kBestellung
         * @return Bestellung
         */
        public static function getOrderProducts($kBestellung)
        {
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Trennzeichen.php';

            $kBestellung = (int)$kBestellung;
            $oBestellung = new Bestellung();

            if ($kBestellung > 0) {
                $oBestellung = new Bestellung($kBestellung, true);

                if (isset($oBestellung->kBestellung, $oBestellung->Positionen) &&
                    $oBestellung->kBestellung > 0 &&
                    is_array($oBestellung->Positionen) &&
                    count($oBestellung->Positionen) > 0
                ) {
                    $kArtikel_arr = RMAArtikel::getProductsByOrder($kBestellung);
                    foreach ($oBestellung->Positionen as $i => $oPosition) {
                        $oBestellung->Positionen[$i]->cAnzahl = Trennzeichen::getUnit(JTLSEPARATER_AMOUNT, $_SESSION['kSprache'], $oPosition->nAnzahl);
                        $oBestellung->Positionen[$i]->bRMA    = false;

                        if ($oPosition->nPosTyp != C_WARENKORBPOS_TYP_ARTIKEL || !isset($oPosition->Artikel->kArtikel) || !$oPosition->Artikel->kArtikel) {
                            unset($oBestellung->Positionen[$i]);
                        } elseif (count($kArtikel_arr) > 0) {
                            // Pruefe ob Artikel bereits vollstaendig zurueckgeschickt wurde
                            if (in_array($oPosition->kArtikel, $kArtikel_arr)) {
                                $fRMAArtikelQuantity = RMAArtikel::getRMAQuantity($kBestellung, $oPosition->kArtikel);
                                if ($fRMAArtikelQuantity && $fRMAArtikelQuantity > 0) {
                                    $fAnzahlBestellung = Bestellung::getProductAmount($kBestellung, $oPosition->kArtikel);

                                    if ($fRMAArtikelQuantity >= $fAnzahlBestellung) {
                                        $oBestellung->Positionen[$i]->bRMA = true;
                                    }

                                    $oBestellung->Positionen[$i]->cRMAArtikelQuantity = Trennzeichen::getUnit(JTLSEPARATER_AMOUNT, $_SESSION['kSprache'], $fRMAArtikelQuantity);
                                }
                            }
                        }
                    }

                    $oBestellung->Positionen = array_merge($oBestellung->Positionen);
                }
            }

            return $oBestellung;
        }

        /**
         * @param int   $kKunde
         * @param int   $kBestellung
         * @param array $kArtikel_arr
         * @param array $cRMAPostAssoc_arr
         * @param int   $kSprache
         * @return bool|stdClass
         */
        public static function insert($kKunde, $kBestellung, $kArtikel_arr, $cRMAPostAssoc_arr, $kSprache)
        {
            $kKunde      = (int)$kKunde;
            $kBestellung = (int)$kBestellung;
            $kSprache    = (int)$kSprache;
            $cRMA        = self::genRMANumber($kKunde, $kBestellung);

            if ($kKunde > 0 && $kBestellung > 0 && is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
                $oRMAStatus = RMAStatus::getFromFunction('start', $kSprache);
                $kRMAStatus = 0;
                if ($oRMAStatus && $oRMAStatus->getRMAStatus() > 0) {
                    $kRMAStatus = $oRMAStatus->getRMAStatus();
                }

                $oRMA = new self();
                $oRMA->setKunde($kKunde);
                $oRMA->setRMAStatus($kRMAStatus);
                $oRMA->setRMANumber($cRMA);
                $oRMA->setErstellt("now()");

                $kRMA = $oRMA->save();

                if ($kRMA > 0) {
                    foreach ($kArtikel_arr as $kArtikel) {
                        $kRMAGrund   = 0;
                        $oRMAArtikel = new RMAArtikel();
                        $oRMAArtikel->setRMA($kRMA);
                        $oRMAArtikel->setBestellung($kBestellung);
                        $oRMAArtikel->setArtikel($kArtikel);
                        if (isset($cRMAPostAssoc_arr['cGrund'][$kArtikel])) {
                            $oRMAGrund = new RMAGrund((int)$cRMAPostAssoc_arr['cGrund'][$kArtikel]);
                            $oRMAArtikel->setGrund($oRMAGrund->getGrund());
                            $kRMAGrund = (int)$cRMAPostAssoc_arr['cGrund'][$kArtikel];
                        } else {
                            $oRMAArtikel->setGrund('');
                        }
                        if (isset($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund])) {
                            $oRMAArtikel->setAnzahl($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund]);
                        } else {
                            $oRMAArtikel->setAnzahl(1);
                        }
                        if (isset($cRMAPostAssoc_arr['cKommentar'][$kArtikel][$kRMAGrund])) {
                            $oRMAArtikel->setKommentar($cRMAPostAssoc_arr['cKommentar'][$kArtikel][$kRMAGrund]);
                        } else {
                            $oRMAArtikel->setKommentar('');
                        }

                        $oRMAArtikel->save();
                    }

                    $oReturn       = new stdClass();
                    $oReturn->kRMA = $kRMA;
                    $oReturn->cRMA = $cRMA;

                    return $oReturn;
                }
            }

            return false;
        }

        /**
         * @param $kKunde
         * @param $kBestellung
         * @param $kArtikel_arr
         * @param $cRMAPostAssoc_arr
         * @return bool
         */
        public static function isRMAAlreadySend($kKunde, $kBestellung, $kArtikel_arr, &$cRMAPostAssoc_arr)
        {
            $kKunde      = (int)$kKunde;
            $kBestellung = (int)$kBestellung;

            if (!$kKunde || !$kBestellung || !is_array($kArtikel_arr) || count($kArtikel_arr) === 0 || !is_array($cRMAPostAssoc_arr) || count($cRMAPostAssoc_arr) === 0) {
                return true;
            }

            return RMAArtikel::isOrderExisting($kBestellung, $kArtikel_arr, $cRMAPostAssoc_arr);
        }

        /**
         * @param int $kKunde
         * @param int $kBestellung
         * @return int|string
         */
        public static function genRMANumber($kKunde = 0, $kBestellung = 0)
        {
            $oEinstellung_arr = Shop::getSettings([CONF_RMA]);
            $cRMANumber       = '';

            if (isset($oEinstellung_arr['rma']['rma_number_gen']) && strlen($oEinstellung_arr['rma']['rma_number_gen']) > 0) {
                $oNummer = new Nummern(JTL_GENNUMBER_RMANUMBER);
                $nNummer = $oNummer->getNummer();
                switch ($oEinstellung_arr['rma']['rma_number_gen']) {
                    // Fortlaufende Nummer
                    case 'F':
                        $cRMANumber = $nNummer;
                        $oNummer->setNummer($oNummer->getNummer() + 1);
                        $oNummer->update();
                        break;

                    // Kundennummer - Datum - Fortlaufende Nummer
                    case 'K':
                        if (isset($_SESSION['Kunde']->cKundenNr) && strlen($_SESSION['Kunde']->cKundenNr) > 0) {
                            $cRMANumber = $_SESSION['Kunde']->cKundenNr . $nNummer;
                        } else {
                            $cRMANumber = self::genStdRMANumber();
                        }
                        break;

                    // Bestellnummer - Fortlaufende Nummer
                    case 'B':
                        if ((int)$kBestellung > 0) {
                            $cBestellNr = Bestellung::getOrderNumber($kBestellung);
                            if ($cBestellNr) {
                                $cRMANumber = $cBestellNr . $nNummer;
                            } else {
                                $cRMANumber = self::genStdRMANumber();
                            }
                        }
                        break;
                }
            } else {
                $cRMANumber = self::genStdRMANumber();
            }

            return $cRMANumber;
        }

        /**
         * @return string
         */
        private static function genStdRMANumber()
        {
            $oRMA = Shop::DB()->query(
                "SELECT kRMA
                    FROM trma
                    ORDER BY kRMA DESC
                    LIMIT 1", 1
            );

            $kRMA = 1;
            if (isset($oRMA->kRMA) && $oRMA->kRMA > 0) {
                $kRMA = $oRMA->kRMA + 1;
            }

            return 'RMA-' . $kRMA . time();
        }

        /**
         * @param array $cPostVar_arr
         * @return array
         */
        public static function checkPostVars($cPostVar_arr)
        {
            $cRMAPostAssoc_arr = [];
            if (is_array($cPostVar_arr) && count($cPostVar_arr) > 0 && isset($cPostVar_arr['kArtikel_arr'])) {
                foreach ($cPostVar_arr['kArtikel_arr'] as $kArtikel) {
                    // Grund
                    if (isset($cPostVar_arr['cGrund_' . $kArtikel])) {
                        $kRMAGrund                              = (int)$cPostVar_arr['cGrund_' . $kArtikel];
                        $cRMAPostAssoc_arr['cGrund'][$kArtikel] = $cPostVar_arr['cGrund_' . $kArtikel];
                        // Anzahl
                        if (isset($cPostVar_arr['fAnzahl_' . $kArtikel . '_' . $kRMAGrund])) {
                            $cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund] = $cPostVar_arr['fAnzahl_' . $kArtikel . '_' . $kRMAGrund];
                        }
                        // Kommentar
                        if (isset($cPostVar_arr['cKommentar_' . $kArtikel . '_' . $kRMAGrund])) {
                            $cRMAPostAssoc_arr['cKommentar'][$kArtikel][$kRMAGrund] = $cPostVar_arr['cKommentar_' . $kArtikel . '_' . $kRMAGrund];
                        }
                    }
                }
            }

            return $cRMAPostAssoc_arr;
        }

        /**
         * @return bool
         */
        public static function getFirstOrder()
        {
            $cSQL = '';
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                $cSQL = " WHERE kKunde = " . $_SESSION['Kunde']->kKunde;
            }
            $oObj = Shop::DB()->query(
                "SELECT kBestellung, dErstellt
                    FROM tbestellung
                    " . $cSQL . "
                    ORDER BY kBestellung ASC
                    LIMIT 1", 1
            );

            if (isset($oObj->kBestellung) && $oObj->kBestellung > 0) {
                return $oObj->dErstellt;
            }

            return false;
        }

        /**
         * @return array
         */
        public static function getFirstOrderTillNow()
        {
            $cFirstOrder = self::getFirstOrder();
            $nYear_arr   = [];

            if ($cFirstOrder !== false) {
                $nFirstOrderYear = (int)substr($cFirstOrder, 0, strpos($cFirstOrder, '-'));

                if ($nFirstOrderYear > 0) {
                    $nYear_arr[] = $nFirstOrderYear;
                    while ($nFirstOrderYear < (int)date('Y')) {
                        $nFirstOrderYear++;
                        $nYear_arr[] = $nFirstOrderYear;
                    }
                    if (count($nYear_arr) > 1) {
                        $nYear_arr = array_reverse($nYear_arr);
                    }
                }
            }

            return $nYear_arr;
        }

        /**
         * @param int $nYear
         */
        public static function setTimePeriod($nYear = 0)
        {
            if (!isset($_SESSION['RMA_TimePeriod'])) {
                $_SESSION['RMA_TimePeriod'] = new stdClass();
                $nYear                      = 1;
            }
            $nYear = (int)$nYear;
            // Special cases
            if ($nYear === 1 || $nYear === 2) {
                $nYearNow  = (int)date('Y');
                $nMonthNow = (int)date('m');
                $nDayNow   = (int)date('d');
                // Last 2 months
                if ($nYear === 1) {
                    $nMonthNow -= 2;
                } elseif ($nYear === 2) {
                    // Last 6 months
                    $nMonthNow -= 6;
                }
                if ($nMonthNow <= 0) {
                    $nMonthNow = 12;
                    $nYearNow--;
                }
                $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $nMonthNow, $nDayNow, $nYearNow));
                if ($nAnzahlTageProMonat < $nDayNow) {
                    $nDayNow = $nAnzahlTageProMonat;
                }
                $_SESSION['RMA_TimePeriod']->cDateFrom = date('Y-m-d', mktime(0, 0, 0, $nMonthNow, $nDayNow, $nYearNow)) . ' 00:00:00';
                $_SESSION['RMA_TimePeriod']->cDateTo   = date('Y-m-d H:i:s');
                $_SESSION['RMA_TimePeriod']->nYear     = $nYear;
            } elseif ($nYear > 3) {
                $_SESSION['RMA_TimePeriod']->cDateFrom = $nYear . '-1-1 00:00:00';
                $_SESSION['RMA_TimePeriod']->cDateTo   = $nYear . '-12-31 23:59:59';
                $_SESSION['RMA_TimePeriod']->nYear     = $nYear;
            }
        }

        /**
         * @param int $kKunde
         * @return array
         */
        public static function getAllCustomerRMA($kKunde)
        {
            $kKunde   = (int)$kKunde;
            $oRMA_arr = [];
            if ($kKunde > 0) {
                $oObj_arr = Shop::DB()->query(
                    "SELECT kRMA
                        FROM trma
                        WHERE kKunde = " . $kKunde . "
                        ORDER BY dErstellt DESC", 2
                );

                if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                    foreach ($oObj_arr as $oObj) {
                        if (isset($oObj->kRMA) && $oObj->kRMA > 0) {
                            $oRMA_arr[] = new self($oObj->kRMA);
                        }
                    }
                }
            }

            return $oRMA_arr;
        }

        /**
         * @param bool $bCustomer
         * @param bool $bRMAArtikel
         * @param int  $kSprache
         * @return array
         */
        public static function getAllRMA($bCustomer = false, $bRMAArtikel = true, $kSprache = 0)
        {
            $oRMA_arr = [];
            $oObj_arr = Shop::DB()->query(
                "SELECT kRMA
                    FROM trma
                    ORDER BY dErstellt DESC", 2
            );

            if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                foreach ($oObj_arr as $oObj) {
                    if (isset($oObj->kRMA) && $oObj->kRMA > 0) {
                        $oRMA_arr[] = new self($oObj->kRMA, $bCustomer, $bRMAArtikel, $kSprache);
                    }
                }
            }

            return $oRMA_arr;
        }

        /**
         * @param int $kRMAStatus
         * @return string
         */
        public static function mapStatusCode($kRMAStatus)
        {
            $oRMAStatus = new RMAStatus($kRMAStatus);
            if ($oRMAStatus->getRMAStatus() > 0) {
                return $oRMAStatus->getStatus();
            }

            return '';
        }

        /**
         * @param int   $kBestellung
         * @param array $kArtikel_arr
         * @param array $cRMAPostAssoc_arr
         * @return array
         */
        public static function checkPlausi($kBestellung, $kArtikel_arr, $cRMAPostAssoc_arr)
        {
            $cPlausi_arr = [];

            if (is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
                $kBestellung = (int)$kBestellung;

                if ($kBestellung > 0) {
                    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
                    $oPositionAssoc_arr = Bestellung::getOrderPositions($kBestellung);

                    foreach ($kArtikel_arr as $kArtikel) {
                        $kArtikel            = (int)$kArtikel;
                        $fRMAArtikelQuantity = RMAArtikel::getRMAQuantity($kBestellung, $kArtikel);
                        if ($oPositionAssoc_arr && isset($oPositionAssoc_arr[$kArtikel])) {
                            if (!$fRMAArtikelQuantity || $fRMAArtikelQuantity <= 0) {
                                $fRMAArtikelQuantity = 0.0;
                            }

                            // Check Grund
                            $kRMAGrund = 0;
                            if (!isset($cRMAPostAssoc_arr['cGrund'][$kArtikel]) ||
                                strlen($cRMAPostAssoc_arr['cGrund'][$kArtikel]) === 0 ||
                                $cRMAPostAssoc_arr['cGrund'][$kArtikel] == '-1'
                            ) {
                                $cPlausi_arr[$kArtikel]['cGrund'] = 1;
                            } else {
                                $kRMAGrund = (int)$cRMAPostAssoc_arr['cGrund'][$kArtikel];
                            }

                            // Check Anzahl
                            if ($kRMAGrund > 0) {
                                if (!isset($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund]) ||
                                    strlen($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund]) === 0
                                ) {
                                    $cPlausi_arr[$kArtikel]['fAnzahl'][$kRMAGrund] = 1;
                                } elseif ($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund] < 0) {
                                    $cPlausi_arr[$kArtikel]['fAnzahl'][$kRMAGrund] = 2;
                                } elseif (($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund] + $fRMAArtikelQuantity) > $oPositionAssoc_arr[$kArtikel]->nAnzahl) {
                                    $cPlausi_arr[$kArtikel]['fAnzahl'][$kRMAGrund] = 3;
                                }

                                if (isset($cPlausi_arr[$kArtikel]['fAnzahl'])) {
                                    $cPlausi_arr[0]['fAnzahl'] = 1;
                                }
                            }
                        } else {
                            $cPlausi_arr[$kArtikel]['oPositionAssoc_arr'] = 1;
                            break;
                        }
                    }
                } else {
                    $cPlausi_arr[0]['kBestellung'] = 1;
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @param array $kArtikel_arr
         * @return array
         */
        public static function getArtikelAssocArray($kArtikel_arr)
        {
            $kArtikelAssoc_arr = [];

            if (is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
                foreach ($kArtikel_arr as $kArtikel) {
                    $kArtikelAssoc_arr[$kArtikel] = 1;
                }
            }

            return $kArtikelAssoc_arr;
        }

        /**
         * @param int   $kRMA
         * @param int   $kKunde
         * @param int   $kSprache
         * @param array $cEinstellung_arr
         * @return bool
         */
        public static function sendSuccessEmail($kRMA, $kKunde, $kSprache = 0, $cEinstellung_arr = [])
        {
            $kRMA     = (int) $kRMA;
            $kKunde   = (int) $kKunde;
            $kSprache = (int) $kSprache;
            if ($kRMA > 0 && $kKunde > 0) {
                if (!$kSprache) {
                    if (isset($_SESSION['kSprache'])) {
                        $kSprache = $_SESSION['kSprache'];
                    } else {
                        $oSprache = gibStandardsprache(true);
                        $kSprache = $oSprache->kSprache;
                    }
                }

                $oRMA = new self($kRMA, true, true, $kSprache);

                if (isset($oRMA) && $oRMA->getRMA() > 0) {
                    if (!is_array($cEinstellung_arr) || count($cEinstellung_arr) === 0) {
                        $cEinstellung_arr = Shop::getSettings([CONF_RMA]);
                    }
                    if (!isset($oObj)) {
                        $oObj = new stdClass();
                    }
                    $oObj->tkunde              = $_SESSION['Kunde'];
                    $oObj->cRMAEinstellung_arr = $cEinstellung_arr['rma'];
                    $oObj->oRMA                = $oRMA;

                    sendeMail(MAILTEMPLATE_RMA_ABGESENDET, $oObj);

                    return true;
                }
            }

            return false;
        }
    }
}
