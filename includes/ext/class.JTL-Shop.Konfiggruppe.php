<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfiggruppe
     */
    class Konfiggruppe implements JsonSerializable
    {
        /**
         * @access protected
         * @var int
         */
        protected $kKonfiggruppe;

        /**
         * @access protected
         * @var string
         */
        protected $cBildPfad;

        /**
         * @access protected
         * @var int
         */
        protected $nMin;

        /**
         * @access protected
         * @var int
         */
        protected $nMax;

        /**
         * @access protected
         * @var int
         */
        protected $nTyp;

        /**
         * @access protected
         * @var int
         */
        protected $nSort;

        /**
         * @var string
         */
        public $cKommentar;

        /**
         * @var object
         */
        public $oSprache;

        /**
         * @var array
         */
        public $oItem_arr;

        /**
         * @var bool|null
         */
        public $bAktiv;

        /**
         * Constructor
         *
         * @param int $kKonfiggruppe
         * @param int $kSprache
         * @access public
         */
        public function __construct($kKonfiggruppe = 0, $kSprache = 0)
        {
            $this->kKonfiggruppe = (int)$kKonfiggruppe;
            if ($this->kKonfiggruppe > 0) {
                $this->loadFromDB($this->kKonfiggruppe, (int)$kSprache);
            }
        }

        /**
         * Specify data which should be serialized to JSON
         *
         * @return array
         */
        public function jsonSerialize()
        {
            $override = [
                'kKonfiggruppe' => (int)$this->kKonfiggruppe,
                'cBildPfad'     => $this->getBildPfad(),
                'nMin'          => (float)$this->nMin,
                'nMax'          => (float)$this->nMax,
                'nTyp'          => (int)$this->nTyp,
                'fInitial'      => (float)$this->getInitQuantity(),
                'bAnzahl'       => $this->getAnzeigeTyp() == KONFIG_ANZEIGE_TYP_RADIO || $this->getAnzeigeTyp() == KONFIG_ANZEIGE_TYP_DROPDOWN,
                'cName'         => $this->oSprache->getName(),
                'cBeschreibung' => $this->oSprache->getBeschreibung(),
                'oItem_arr'     => $this->oItem_arr
            ];
            $result = array_merge(get_object_vars($this), $override);

            return utf8_convert_recursive($result);
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfiggruppe
         * @param int $kSprache
         * @return $this
         */
        private function loadFromDB($kKonfiggruppe = 0, $kSprache = 0)
        {
            $oObj = Shop::DB()->select('tkonfiggruppe', 'kKonfiggruppe', (int)$kKonfiggruppe);
            if (isset($oObj->kKonfiggruppe) && $oObj->kKonfiggruppe > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
                if (!$kSprache) {
                    $kSprache = $_SESSION['kSprache'];
                }
                $this->oSprache  = new Konfiggruppesprache($this->kKonfiggruppe, $kSprache);
                $this->oItem_arr = Konfigitem::fetchAll($this->kKonfiggruppe);
            }

            return $this;
        }

        /**
         * Store the class in the database
         *
         * @param bool $bPrim Controls the return of the method
         * @return bool|int
         * @access public
         */
        public function save($bPrim = true)
        {
            $oObj             = new stdClass();
            $oObj->cBildPfad  = $this->cBildPfad;
            $oObj->nMin       = $this->nMin;
            $oObj->nMax       = $this->nMax;
            $oObj->nTyp       = $this->nTyp;
            $oObj->nSort      = $this->nSort;
            $oObj->cKommentar = $this->cKommentar;

            $kPrim = Shop::DB()->insert('tkonfiggruppe', $oObj);
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
            $_upd             = new stdClass();
            $_upd->cBildPfad  = $this->cBildPfad;
            $_upd->nMin       = $this->nMin;
            $_upd->nMax       = $this->nMax;
            $_upd->nTyp       = $this->nTyp;
            $_upd->nSort      = $this->nSort;
            $_upd->cKommentar = $this->cKommentar;

            return Shop::DB()->update('tkonfiggruppe', 'kKonfiggruppe', (int)$this->kKonfiggruppe, $_upd);
        }

        /**
         * Delete the class in the database
         *
         * @return int
         * @access public
         */
        public function delete()
        {
            return Shop::DB()->delete('tkonfiggruppe', 'kKonfiggruppe', (int)$this->kKonfiggruppe);
        }

        /**
         * Sets the kKonfiggruppe
         *
         * @access public
         * @param int
         * @return $this
         */
        public function setKonfiggruppe($kKonfiggruppe)
        {
            $this->kKonfiggruppe = (int)$kKonfiggruppe;

            return $this;
        }

        /**
         * Sets the cBildPfad
         *
         * @access public
         * @param string $cBildPfad
         * @return $this
         */
        public function setBildPfad($cBildPfad)
        {
            $this->cBildPfad = Shop::DB()->escape($cBildPfad);

            return $this;
        }

        /**
         * Sets the nTyp
         *
         * @access public
         * @param int $nTyp
         * @return $this
         */
        public function setAnzeigeTyp($nTyp)
        {
            $this->nTyp = (int)$nTyp;

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
         * Gets the kKonfiggruppe
         *
         * @access public
         * @return int
         */
        public function getKonfiggruppe()
        {
            return $this->kKonfiggruppe;
        }

        /**
         * Gets the cBildPfad
         *
         * @access public
         * @return string|null
         */
        public function getBildPfad()
        {
            if (strlen($this->cBildPfad)) {
                return PFAD_KONFIGURATOR_KLEIN . $this->cBildPfad;
            }

            return null;
        }

        /**
         * Gets the nMin
         *
         * @access public
         * @return int
         */
        public function getMin()
        {
            return $this->nMin;
        }

        /**
         * Gets the nMax
         *
         * @access public
         * @return int
         */
        public function getMax()
        {
            return $this->nMax;
        }

        /**
         * Gets the nAuswahlTyp
         *
         * @access public
         * @return int
         */
        public function getAuswahlTyp()
        {
            return 0;
        }

        /**
         * Gets the nTyp
         *
         * @access public
         * @return int
         */
        public function getAnzeigeTyp()
        {
            return $this->nTyp;
        }

        /**
         * Gets the nSort
         *
         * @access public
         * @return int
         */
        public function getSort()
        {
            return $this->nSort;
        }

        /**
         * @return mixed
         */
        public function getKommentar()
        {
            return $this->cKommentar;
        }

        /**
         * @return mixed
         */
        public function getSprache()
        {
            return $this->oSprache;
        }

        /**
         * @return int
         */
        public function getItemCount()
        {
            $oCount = Shop::DB()->query("
                SELECT COUNT(*) AS nCount 
                    FROM tkonfigitem 
                    WHERE kKonfiggruppe = " . (int)$this->kKonfiggruppe, 1
            );

            return isset($oCount->nCount)
                ? (int)$oCount->nCount
                : 0;
        }

        /**
         * @return bool
         */
        public function quantityEquals()
        {
            $bEquals = false;
            if (count($this->oItem_arr) > 0) {
                $oItem = $this->oItem_arr[0];
                if ($oItem->getMin() == $oItem->getMax()) {
                    $bEquals = true;
                    $nKey    = $oItem->getMin();
                    foreach ($this->oItem_arr as &$oItem) {
                        if (!($oItem->getMin() == $oItem->getMax() && $oItem->getMin() == $nKey)) {
                            $bEquals = false;
                        }
                    }
                }
            }

            return $bEquals;
        }

        /**
         * @return int
         */
        public function getInitQuantity()
        {
            $fQuantity = 1;
            foreach ($this->oItem_arr as &$oItem) {
                if ($oItem->getSelektiert()) {
                    $fQuantity = $oItem->getInitial();
                }
            }

            return $fQuantity;
        }
    }
}
