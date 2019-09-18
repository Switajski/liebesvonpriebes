<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_RMA)) {
    /**
     * RMAGrund Class
     *
     * @access public
     * @author
     * @copyright
     */
    class RMAGrund
    {
        /**
         * @access protected
         * @var int
         */
        protected $kRMAGrund;

        /**
         * @access protected
         * @var int
         */
        protected $kSprache;

        /**
         * @access protected
         * @var int
         */
        protected $nSort;

        /**
         * @access protected
         * @var string
         */
        protected $cGrund;

        /**
         * @access protected
         * @var string
         */
        protected $cKommentar;

        /**
         * @access protected
         * @var int
         */
        protected $nAktiv;

        /**
         * Constructor
         *
         * @param int $kRMAGrund primary key
         * @access public
         */
        public function __construct($kRMAGrund = 0)
        {
            if ((int)$kRMAGrund > 0) {
                $this->loadFromDB($kRMAGrund);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kRMAGrund primary key
         * @access private
         */
        private function loadFromDB($kRMAGrund = 0)
        {
            $oObj = Shop::DB()->query(
                "SELECT *
                  FROM trmagrund
                  WHERE kRMAGrund = " . (int)$kRMAGrund, 1
            );

            if ($oObj->kRMAGrund > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
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

            unset($oObj->kRMAGrund);

            $kPrim = Shop::DB()->insert('trmagrund', $oObj);

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
                "UPDATE trmagrund
                   SET kRMAGrund = " . $this->kRMAGrund . ",
                       kSprache = " . $this->kSprache . ",
                       nSort = " . $this->nSort . ",
                       cGrund = '" . $this->cGrund . "',
                       cKommentar = '" . $this->cKommentar . "',
                       nAktiv = " . $this->nAktiv . "
                   WHERE kRMAGrund = " . $this->kRMAGrund, 3
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
            return Shop::DB()->delete('trmagrund', 'kRMAGrund', $this->getRMAGrund());
        }

        /**
         * Sets the kRMAGrund
         *
         * @access public
         * @param int $kRMAGrund
         * @return $this
         */
        public function setRMAGrund($kRMAGrund)
        {
            $this->kRMAGrund = (int)$kRMAGrund;

            return $this;
        }

        /**
         * Sets the kSprache
         *
         * @access public
         * @param int $kSprache
         * @return $this
         */
        public function setSprache($kSprache)
        {
            $this->kSprache = (int)$kSprache;

            return $this;
        }

        /**
         * Sets the nSort
         *
         * @access public
         * @param int $nSort
         * @return $this
         */
        public function setSort($nSort)
        {
            $this->nSort = (int)$nSort;

            return $this;
        }

        /**
         * Sets the cGrund
         *
         * @access public
         * @param string $cGrund
         * @return $this
         */
        public function setGrund($cGrund)
        {
            $this->cGrund = Shop::DB()->escape($cGrund);

            return $this;
        }

        /**
         * Sets the cKommentar
         *
         * @access public
         * @param string $cKommentar
         * @return $this
         */
        public function setKommentar($cKommentar)
        {
            $this->cKommentar = Shop::DB()->escape($cKommentar);

            return $this;
        }

        /**
         * Sets the nAktiv
         *
         * @access public
         * @param int $nAktiv
         * @return $this
         */
        public function setAktiv($nAktiv)
        {
            $this->nAktiv = (int)$nAktiv;

            return $this;
        }

        /**
         * Gets the kRMAGrund
         *
         * @access public
         * @return int
         */
        public function getRMAGrund()
        {
            return (int)$this->kRMAGrund;
        }

        /**
         * Gets the kSprache
         *
         * @access public
         * @return int
         */
        public function getSprache()
        {
            return (int)$this->kSprache;
        }

        /**
         * Gets the nSort
         *
         * @access public
         * @return int
         */
        public function getSort()
        {
            return (int)$this->nSort;
        }

        /**
         * Gets the cGrund
         *
         * @access public
         * @return string
         */
        public function getGrund()
        {
            return $this->cGrund;
        }

        /**
         * Gets the cKommentar
         *
         * @access public
         * @return string
         */
        public function getKommentar()
        {
            return $this->cKommentar;
        }

        /**
         * Gets the nAktiv
         *
         * @access public
         * @return int
         */
        public function getAktiv()
        {
            return $this->nAktiv;
        }

        /**
         * @param bool $bPrimary
         * @return array|bool|int
         */
        public function saveReason($bPrimary = false)
        {
            $cPlausi_arr = $this->checkReason();

            if (count($cPlausi_arr) === 0) {
                $kRMAGrund = $this->save(true);

                if ($kRMAGrund > 0) {
                    return $bPrimary ? $kRMAGrund : true;
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @return array|bool
         */
        public function updateReason()
        {
            $cPlausi_arr = $this->checkReason();

            if (count($cPlausi_arr) === 0) {
                $this->update();

                return true;
            }

            return $cPlausi_arr;
        }

        /**
         * @return array
         */
        private function checkReason()
        {
            $cPlausi_arr = [];
            // Sprache
            if ($this->kSprache == 0) {
                $cPlausi_arr['kSprache'] = 1;
            }
            // Grund
            if (strlen($this->cGrund) === 0) {
                $cPlausi_arr['cGrund'] = 1;
            }
            // Kommentar
            if (strlen($this->cKommentar) === 0) {
                $cPlausi_arr['cKommentar'] = 1;
            }

            return $cPlausi_arr;
        }

        /**
         * @param int  $kSprache
         * @param bool $bAktiv
         * @return array
         */
        public static function getAll($kSprache, $bAktiv = true)
        {
            $oRMAGrund_arr = [];
            $kSprache      = (int)$kSprache;

            if ($kSprache > 0) {
                $cSQL = '';
                if ($bAktiv) {
                    $cSQL = " AND nAktiv = 1";
                }
                $oObj_arr = Shop::DB()->query(
                    "SELECT kRMAGrund
                        FROM trmagrund
                        WHERE kSprache = " . $kSprache . "
                        " . $cSQL . "
                        ORDER BY nSort", 2
                );

                if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                    foreach ($oObj_arr as $oObj) {
                        if (isset($oObj->kRMAGrund) && $oObj->kRMAGrund > 0) {
                            $oRMAGrund_arr[] = new self($oObj->kRMAGrund);
                        }
                    }
                }
            }

            return $oRMAGrund_arr;
        }
    }
}
