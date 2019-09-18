<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfigitemsprache
     */
    class Konfigitemsprache
    {
        /**
         * @access protected
         * @var int
         */
        protected $kKonfigitem;

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
         * @param int $kKonfigitem
         * @param int $kSprache
         * @access public
         */
        public function __construct($kKonfigitem = 0, $kSprache = 0)
        {
            if ((int)$kKonfigitem > 0 && (int)$kSprache > 0) {
                $this->loadFromDB($kKonfigitem, $kSprache);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfigitem
         * @param int $kSprache
         * @access private
         */
        private function loadFromDB($kKonfigitem = 0, $kSprache = 0)
        {
            $oObj = Shop::DB()->select('tkonfigitemsprache', 'kKonfigitem', (int)$kKonfigitem, 'kSprache', (int)$kSprache);
            if (isset($oObj) && empty($oObj->cName)) {
                $kSprache         = gibStandardsprache();
                $StandardLanguage = Shop::DB()->select(
                    'tkonfigitemsprache',
                    'kKonfigitem', (int)$kKonfigitem,
                    'kSprache', (int)$kSprache->kSprache,
                    null, null,
                    false,
                    'cName'
                );
                $oObj->cName      = $StandardLanguage->cName;
            }
            if (isset($oObj) && empty($oObj->cBeschreibung)) {
                $kSprache            = gibStandardsprache();
                $StandardLanguage    = Shop::DB()->select(
                    'tkonfigitemsprache',
                    'kKonfigitem', (int)$kKonfigitem,
                    'kSprache', (int)$kSprache->kSprache,
                    null, null,
                    false,
                    'cBeschreibung'
                );
                $oObj->cBeschreibung = $StandardLanguage->cBeschreibung;
            }

            if (isset($oObj->kKonfigitem, $oObj->kSprache) && $oObj->kKonfigitem > 0 && $oObj->kSprache > 0) {
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
            unset($oObj->kKonfigitem, $oObj->kSprache);

            $kPrim = Shop::DB()->insert('tkonfigitemsprache', $oObj);

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
            $_upd->cName         = $this->getName();
            $_upd->cBeschreibung = $this->getBeschreibung();

            return Shop::DB()->update(
                'tkonfigitemsprache',
                ['kKonfigitem', 'kSprache'],
                [$this->getKonfigitem(), $this->getSprache()],
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
                'tkonfigitemsprache',
                ['kKonfigitem', 'kSprache'],
                [(int)$this->kKonfigitem, (int)$this->kSprache]
            );
        }

        /**
         * Sets the kKonfigitem
         *
         * @access public
         * @param int $kKonfigitem
         * @return $this
         */
        public function setKonfigitem($kKonfigitem)
        {
            $this->kKonfigitem = (int)$kKonfigitem;

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
    }
}
