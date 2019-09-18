<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_RMA)) {
    /**
     * Class RMAStatus
     *
     * @access public
     * @author
     * @copyright
     */
    class RMAStatus
    {
        /**
         * @access protected
         * @var int
         */
        protected $kRMAStatus;

        /**
         * @access protected
         * @var int
         */
        protected $kSprache;

        /**
         * @access protected
         * @var string
         */
        protected $cStatus;

        /**
         * @access protected
         * @var string
         */
        protected $eFunktion;

        /**
         * @access protected
         * @var int
         */
        protected $nAktiv;

        /**
         * Constructor
         *
         * @param int $kRMAStatus
         * @access public
         */
        public function __construct($kRMAStatus = 0)
        {
            if ((int)$kRMAStatus > 0) {
                $this->loadFromDB($kRMAStatus);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kRMAStatus
         * @access private
         */
        private function loadFromDB($kRMAStatus)
        {
            $oObj = Shop::DB()->select('trmastatus', 'kRMAStatus', (int)$kRMAStatus);
            if (isset($oObj->kRMAStatus) && $oObj->kRMAStatus > 0) {
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
            unset($oObj->kRMAStatus);

            $kPrim = Shop::DB()->insert('trmastatus', $oObj);

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
            $_upd            = new stdClass();
            $_upd->kSprache  = $this->getSprache();
            $_upd->cStatus   = $this->getStatus();
            $_upd->eFunktion = $this->getFunktion();
            $_upd->nAktiv    = $this->getAktiv();

            return Shop::DB()->update('trmastatus', 'kRMAStatus', $this->getRMAStatus(), $_upd);
        }

        /**
         * Delete the class in the database
         *
         * @return int
         * @access public
         */
        public function delete()
        {
            return Shop::DB()->delete('trmastatus', 'kRMAStatus', $this->getRMAStatus());
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
         * Sets the cStatus
         *
         * @access public
         * @param string $cStatus
         * @return $this
         */
        public function setStatus($cStatus)
        {
            $this->cStatus = Shop::DB()->escape($cStatus);

            return $this;
        }

        /**
         * Sets the eFunktion
         *
         * @access public
         * @param string $eFunktion
         * @return $this
         */
        public function setFunktion($eFunktion)
        {
            $this->eFunktion = Shop::DB()->escape($eFunktion);

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
         * Gets the cStatus
         *
         * @access public
         * @return string
         */
        public function getStatus()
        {
            return $this->cStatus;
        }

        /**
         * Gets the eFunktion
         *
         * @access public
         * @return string
         */
        public function getFunktion()
        {
            return $this->eFunktion;
        }

        /**
         * Gets the nAktiv
         *
         * @access public
         * @return int
         */
        public function getAktiv()
        {
            return (int)$this->nAktiv;
        }

        /**
         * @param bool $bPrimary
         * @return array|bool|int
         */
        public function saveStatus($bPrimary = false)
        {
            $cPlausi_arr = $this->checkStatus();

            if (count($cPlausi_arr) === 0) {
                $kRMAStatus = $this->save(true);

                if ($kRMAStatus > 0) {
                    return $bPrimary ? $kRMAStatus : true;
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @return array|bool
         */
        public function updateStatus()
        {
            $cPlausi_arr = $this->checkStatus();

            if (count($cPlausi_arr) === 0) {
                $this->update();

                return true;
            }

            return $cPlausi_arr;
        }

        /**
         * @return array
         */
        private function checkStatus()
        {
            $cPlausi_arr = [];
            // Sprache
            if ($this->kSprache == 0) {
                $cPlausi_arr['kSprache'] = 1;
            }
            // Status
            if (strlen($this->cStatus) === 0) {
                $cPlausi_arr['cStatus'] = 1;
            }
            // Funktion
            if (strlen($this->eFunktion) === 0) {
                $cPlausi_arr['eFunktion'] = 1;
            } elseif (!$this->checkDoubleFunction()) {
                $cPlausi_arr['eFunktion'] = 2;
            }

            return $cPlausi_arr;
        }

        /**
         * @return bool
         */
        private function checkDoubleFunction()
        {
            if ($this->kSprache > 0 && strlen($this->eFunktion) > 0) {
                $oObj = Shop::DB()->query(
                    "SELECT kRMAStatus
                        FROM trmastatus
                        WHERE kSprache = " . $this->kSprache . "
                            AND eFunktion = '" . $this->eFunktion . "'", 1
                );

                if (!isset($oObj->kRMAStatus) || (int)$oObj->kRMAStatus === 0) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param int  $kSprache
         * @param bool $bAssoc
         * @param bool $bAktiv
         * @return array
         */
        public static function getAll($kSprache, $bAssoc = true, $bAktiv = true)
        {
            $oRMAStatus_arr = [];
            $kSprache       = (int)$kSprache;
            if ($kSprache > 0) {
                $cSQL = '';
                if ($bAktiv) {
                    $cSQL = " AND nAktiv = 1";
                }
                $oObj_arr = Shop::DB()->query(
                    "SELECT kRMAStatus
                        FROM trmastatus
                        WHERE kSprache = " . $kSprache . $cSQL, 2
                );
                if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                    foreach ($oObj_arr as $oObj) {
                        if (isset($oObj->kRMAStatus) && $oObj->kRMAStatus > 0) {
                            if ($bAssoc) {
                                $oRMAStatus_arr[$oObj->kRMAStatus] = new self($oObj->kRMAStatus);
                            } else {
                                $oRMAStatus_arr[] = new self($oObj->kRMAStatus);
                            }
                        }
                    }
                }
            }

            return $oRMAStatus_arr;
        }

        /**
         * @param string $cFunktion
         * @param int    $kSprache
         * @param bool   $bAktiv
         * @return bool|RMAStatus
         */
        public static function getFromFunction($cFunktion, $kSprache = 0, $bAktiv = true)
        {
            if (strlen($cFunktion) > 0) {
                $cSQL = '';
                if ($bAktiv) {
                    $cSQL = " AND nAktiv = 1";
                }
                $oObj = Shop::DB()->query(
                    "SELECT kRMAStatus
                        FROM trmastatus
                        WHERE kSprache = " . (int)$kSprache . "
                            AND eFunktion = '" . StringHandler::filterXSS($cFunktion) . "'" . $cSQL, 1
                );

                if (isset($oObj->kRMAStatus) && $oObj->kRMAStatus > 0) {
                    return new self($oObj->kRMAStatus);
                }
            }

            return false;
        }

        /**
         * @param array $cPlausi_arr
         * @return array
         */
        public static function mapPlausiError($cPlausi_arr)
        {
            $cError_arr = [];
            if (is_array($cPlausi_arr) &&
                count($cPlausi_arr) > 0 &&
                isset($cPlausi_arr['eFunktion']) &&
                $cPlausi_arr['eFunktion'] == 2
            ) {
                $cError_arr[] = "Die gewählte Funktion ist bereits vorhanden. Diese darf nicht doppelt belegt werden.";
            }

            return $cError_arr;
        }
    }
}
