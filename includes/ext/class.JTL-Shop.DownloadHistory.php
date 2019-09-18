<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_DOWNLOADS)) {
    /**
     * Class DownloadHistory
     */
    class DownloadHistory
    {
        /**
         * @var int
         */
        protected $kDownloadHistory;

        /**
         * @var int
         */
        protected $kDownload;

        /**
         * @var int
         */
        protected $kKunde;

        /**
         * @var int
         */
        protected $kBestellung;

        /**
         * @var string
         */
        protected $dErstellt;

        /**
         * @param int $kDownloadHistory
         */
        public function __construct($kDownloadHistory = 0)
        {
            if ((int)$kDownloadHistory > 0) {
                $this->loadFromDB((int)$kDownloadHistory);
            }
        }

        /**
         * @param int $kDownloadHistory
         */
        private function loadFromDB($kDownloadHistory)
        {
            $oDownloadHistory = Shop::DB()->select('tdownloadhistory', 'kDownloadHistory', (int)$kDownloadHistory);
            if (isset($oDownloadHistory->kDownloadHistory) && (int)$oDownloadHistory->kDownloadHistory > 0) {
                $cMember_arr = array_keys(get_object_vars($oDownloadHistory));
                if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                    foreach ($cMember_arr as $cMember) {
                        $this->$cMember = $oDownloadHistory->$cMember;
                    }
                }
            }
        }

        /**
         * @param int $kDownload
         * @return array
         */
        public static function getHistorys($kDownload)
        {
            $kDownload            = (int)$kDownload;
            $oDownloadHistory_arr = [];
            if ($kDownload > 0) {
                $oHistory_arr = Shop::DB()->selectAll(
                    'tdownloadhistory',
                    'kDownload',
                    $kDownload,
                    'kDownloadHistory',
                    'dErstellt DESC'
                );
                if (count($oHistory_arr) > 0) {
                    foreach ($oHistory_arr as $oHistory) {
                        $oDownloadHistory_arr[] = new self($oHistory->kDownloadHistory);
                    }
                }
            }

            return $oDownloadHistory_arr;
        }

        /**
         * @param int $kKunde
         * @param int $kBestellung
         * @return array
         */
        public static function getOrderHistory($kKunde, $kBestellung = 0)
        {
            $kBestellung  = (int)$kBestellung;
            $kKunde       = (int)$kKunde;
            $oHistory_arr = [];
            if ($kBestellung > 0 || $kKunde > 0) {
                $cSQLWhere = "kBestellung = " . $kBestellung;
                if ($kBestellung > 0) {
                    $cSQLWhere .= " AND kKunde = " . $kKunde;
                }

                $oHistoryTMP_arr = Shop::DB()->query(
                    "SELECT kDownload, kDownloadHistory
                         FROM tdownloadhistory
                         WHERE " . $cSQLWhere . "
                         ORDER BY dErstellt DESC", 2
                );
                if (is_array($oHistoryTMP_arr) && count($oHistoryTMP_arr) > 0) {
                    foreach ($oHistoryTMP_arr as $oHistoryTMP) {
                        if (!isset($oHistory_arr[$oHistoryTMP->kDownload]) ||
                            !is_array($oHistory_arr[$oHistoryTMP->kDownload])
                        ) {
                            $oHistory_arr[$oHistoryTMP->kDownload] = [];
                        }
                        $oHistory_arr[$oHistoryTMP->kDownload][] = new self($oHistoryTMP->kDownloadHistory);
                    }
                }
            }

            return $oHistory_arr;
        }

        /**
         * @param bool $bPrimary
         * @return bool
         */
        public function save($bPrimary = false)
        {
            $oObj = $this->kopiereMembers();
            unset($oObj->kDownloadHistory);

            $kDownloadHistory = Shop::DB()->insert('tdownloadhistory', $oObj);
            if ($kDownloadHistory > 0) {
                return $bPrimary ? $kDownloadHistory : true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function update()
        {
            $_upd              = new stdClass();
            $_upd->kDownload   = $this->kDownload;
            $_upd->kKunde      = $this->kKunde;
            $_upd->kBestellung = $this->kBestellung;
            $_upd->dErstellt   = $this->dErstellt;

            return Shop::DB()->update('tdownloadhistory', 'kDownloadHistory', (int)$this->kDownloadHistory, $_upd);
        }

        /**
         * @param int $kDownloadHistory
         * @return $this
         */
        public function setDownloadHistory($kDownloadHistory)
        {
            $this->kDownloadHistory = (int)$kDownloadHistory;

            return $this;
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
         * @param int $kKunde
         * @return $this
         */
        public function setKunde($kKunde)
        {
            $this->kKunde = (int)$kKunde;

            return $this;
        }

        /**
         * @param int $kBestellung
         * @return $this
         */
        public function setBestellung($kBestellung)
        {
            $this->kBestellung = (int)$kBestellung;

            return $this;
        }

        /**
         * @param string $dErstellt
         * @return $this
         */
        public function setErstellt($dErstellt)
        {
            $this->dErstellt = $dErstellt;

            return $this;
        }

        /**
         * @return int
         */
        public function getDownloadHistory()
        {
            return (int)$this->kDownloadHistory;
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
        public function getKunde()
        {
            return (int)$this->kKunde;
        }

        /**
         * @return int
         */
        public function getBestellung()
        {
            return (int)$this->kBestellung;
        }

        /**
         * @return string
         */
        public function getErstellt()
        {
            return $this->dErstellt;
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
