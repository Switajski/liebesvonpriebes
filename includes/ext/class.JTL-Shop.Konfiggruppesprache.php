<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfiggruppesprache
     */
    class Konfiggruppesprache implements JsonSerializable
    {
        /**
         * @access protected
         * @var int
         */
        protected $kKonfiggruppe;

        /**
         * @access protected
         * @var int
         */
        protected $kSprache;

        /**
         * @access protected
         * @var string
         */
        protected $cName;

        /**
         * @access protected
         * @var string
         */
        protected $cBeschreibung;

        /**
         * Constructor
         *
         * @param int $kKonfiggruppe
         * @param int $kSprache
         * @access public
         */
        public function __construct($kKonfiggruppe = 0, $kSprache = 0)
        {
            if ((int)$kKonfiggruppe > 0 && (int)$kSprache > 0) {
                $this->loadFromDB($kKonfiggruppe, $kSprache);
            }
        }

        /**
         * Specify data which should be serialized to JSON
         */
        public function jsonSerialize()
        {
            return utf8_convert_recursive([
                'cName'         => $this->cName,
                'cBeschreibung' => $this->cBeschreibung
            ]);
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfiggruppe primarykey
         * @param int $kSprache primarykey
         * @access private
         */
        private function loadFromDB($kKonfiggruppe = 0, $kSprache = 0)
        {
            $oObj = Shop::DB()->select(
                'tkonfiggruppesprache',
                'kKonfiggruppe',
                (int)$kKonfiggruppe,
                'kSprache',
                (int)$kSprache
            );
            if (isset($oObj->kKonfiggruppe, $oObj->kSprache) &&
                $oObj->kKonfiggruppe > 0 &&
                $oObj->kSprache > 0
            ) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
                $this->kSprache      = (int)$this->kSprache;
                $this->kKonfiggruppe = (int)$this->kKonfiggruppe;
            }
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
            $oObj        = new stdClass();
            $cMember_arr = array_keys(get_object_vars($this));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $oObj->$cMember = $this->$cMember;
                }
            }
            unset($oObj->kKonfiggruppe, $oObj->kSprache);

            $kPrim = Shop::DB()->insert('tkonfiggruppesprache', $oObj);

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
            $_upd                = new stdClass();
            $_upd->kSprache      = $this->getSprache();
            $_upd->cName         = $this->getName();
            $_upd->cBeschreibung = $this->getBeschreibung();

            return Shop::DB()->update(
                'tkonfiggruppesprache',
                ['kKonfiggruppe', 'kSprache'],
                [$this->getKonfiggruppe(), $this->getSprache()],
                $_upd
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
            return Shop::DB()->delete(
                'tkonfiggruppesprache',
                ['kKonfiggruppe', 'kSprache'],
                [(int)$this->kKonfiggruppe, (int)$this->kSprache]
            );
        }

        /**
         * Sets the kKonfiggruppe
         *
         * @access public
         * @param int $kKonfiggruppe
         * @return $this
         */
        public function setKonfiggruppe($kKonfiggruppe)
        {
            $this->kKonfiggruppe = (int)$kKonfiggruppe;

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
         * Sets the cName
         *
         * @access public
         * @param string $cName
         * @return $this
         */
        public function setName($cName)
        {
            $this->cName = Shop::DB()->escape($cName);

            return $this;
        }

        /**
         * Sets the cBeschreibung
         *
         * @access public
         * @param string $cBeschreibung
         * @return $this
         */
        public function setBeschreibung($cBeschreibung)
        {
            $this->cBeschreibung = Shop::DB()->escape($cBeschreibung);

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
            return (int)$this->kKonfiggruppe;
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
         * Gets the cName
         *
         * @access public
         * @return string
         */
        public function getName()
        {
            return $this->cName;
        }

        /**
         * Gets the cBeschreibung
         *
         * @access public
         * @return string
         */
        public function getBeschreibung()
        {
            return $this->cBeschreibung;
        }

        /**
         * Gets the cBeschreibung
         *
         * @access public
         * @return bool
         */
        public function hatBeschreibung()
        {
            return strlen($this->cBeschreibung) > 0;
        }
    }
}
