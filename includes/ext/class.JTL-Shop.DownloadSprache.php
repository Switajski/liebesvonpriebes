<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_DOWNLOADS)) {
    /**
     * Class DownloadSprache
     */
    class DownloadSprache
    {
        /**
         * @var int
         */
        protected $kDownload;

        /**
         * @var int
         */
        protected $kSprache;

        /**
         * @var string
         */
        protected $cName;

        /**
         * @var string
         */
        protected $cBeschreibung;

        /**
         * @param int $kDownload
         * @param int $kSprache
         */
        public function __construct($kDownload = 0, $kSprache = 0)
        {
            if ((int)$kDownload > 0 && (int)$kSprache > 0) {
                $this->loadFromDB($kDownload, $kSprache);
            }
        }

        /**
         * @param int $kDownload
         * @param int $kSprache
         */
        private function loadFromDB($kDownload, $kSprache)
        {
            $oDownloadSprache = Shop::DB()->select(
                'tdownloadsprache',
                'kDownload',
                (int)$kDownload,
                'kSprache',
                (int)$kSprache
            );
            if (isset($oDownloadSprache->kDownload) && (int)$oDownloadSprache->kDownload > 0) {
                $cMember_arr = array_keys(get_object_vars($oDownloadSprache));
                if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                    foreach ($cMember_arr as $cMember) {
                        $this->$cMember = $oDownloadSprache->$cMember;
                    }
                }
                $this->kSprache  = (int)$this->kSprache;
                $this->kDownload = (int)$this->kDownload;
            }
        }

        /**
         * @param bool $bPrimary
         * @return bool
         */
        public function save($bPrimary = false)
        {
            $oObj      = $this->kopiereMembers();
            $kDownload = Shop::DB()->insert('tdownloadsprache', $oObj);
            if ($kDownload > 0) {
                return $bPrimary ? $kDownload : true;
            }

            return false;
        }

        /**
         * @return mixed
         */
        public function update()
        {
            $_upd                = new stdClass();
            $_upd->cName         = $this->getName();
            $_upd->cBeschreibung = $this->getBeschreibung();

            return Shop::DB()->update(
                'tdownloadsprache',
                ['kDownload', 'kSprache'],
                [$this->getDownload(), $this->getSprache()],
                $_upd
            );
        }

        /**
         * @param int $kDownload
         * @return $this
         */
        public function setDownload($kDownload)
        {
            $this->kDownload = (int)$kDownload;

            return $this;
        }

        /**
         * @param int $kSprache
         * @return $this
         */
        public function setSprache($kSprache)
        {
            $this->kSprache = (int)$kSprache;

            return $this;
        }

        /**
         * @param string $cName
         * @return $this
         */
        public function setName($cName)
        {
            $this->cName = $cName;

            return $this;
        }

        /**
         * @param string $cBeschreibung
         * @return $this
         */
        public function setBeschreibung($cBeschreibung)
        {
            $this->cBeschreibung = $cBeschreibung;

            return $this;
        }

        /**
         * @return int
         */
        public function getDownload()
        {
            return (int)$this->kDownload;
        }

        /**
         * @return int
         */
        public function getSprache()
        {
            return (int)$this->kSprache;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->cName;
        }

        /**
         * @return string
         */
        public function getBeschreibung()
        {
            return $this->cBeschreibung;
        }

        /**
         * @return stdClass
         */
        private function kopiereMembers()
        {
            $obj         = new stdClass();
            $cMember_arr = array_keys(get_object_vars($this));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $obj->$cMember = $this->$cMember;
                }
            }

            return $obj;
        }
    }
}
