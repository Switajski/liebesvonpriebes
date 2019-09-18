<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_DOWNLOADS)) {
    /**
     * Class Download
     */
    class Download
    {
        /**
         * @var int
         */
        public $kDownload;

        /**
         * @var string
         */
        public $cID;

        /**
         * @var string
         */
        public $cPfad;

        /**
         * @var string
         */
        public $cPfadVorschau;

        /**
         * @var int
         */
        public $nAnzahl;

        /**
         * @var int
         */
        public $nTage;

        /**
         * @var int
         */
        public $nSort;

        /**
         * @var string
         */
        public $dErstellt;

        /**
         * @var object
         */
        public $oDownloadSprache;

        /**
         * @var array
         */
        public $oDownloadHistory_arr;

        /**
         * @var int
         */
        public $kBestellung;

        /**
         * @var string
         */
        public $dGueltigBis;

        /**
         * @var array
         */
        public $oArtikelDownload_arr;

        /**
         * @param int  $kDownload
         * @param int  $kSprache
         * @param bool $bInfo
         * @param int  $kBestellung
         */
        public function __construct($kDownload = 0, $kSprache = 0, $bInfo = true, $kBestellung = 0)
        {
            if ((int)$kDownload > 0) {
                $this->loadFromDB((int)$kDownload, (int)$kSprache, (bool)$bInfo, (int)$kBestellung);
            }
        }

        /**
         * @param int  $kDownload
         * @param int  $kSprache
         * @param bool $bInfo
         * @param int  $kBestellung
         */
        private function loadFromDB($kDownload, $kSprache, $bInfo, $kBestellung)
        {
            $oDownload = Shop::DB()->select('tdownload', 'kDownload', (int)$kDownload);
            $kBestellung = (int)$kBestellung;
            if (isset($oDownload->kDownload) && (int)$oDownload->kDownload > 0) {
                $cMember_arr = array_keys(get_object_vars($oDownload));
                if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                    foreach ($cMember_arr as &$cMember) {
                        $this->$cMember = $oDownload->$cMember;
                    }
                }

                if ($bInfo) {
                    // Sprache
                    if (!$kSprache) {
                        $kSprache = $_SESSION['kSprache'];
                    }
                    $this->oDownloadSprache = new DownloadSprache($oDownload->kDownload, $kSprache);
                    // History
                    $this->oDownloadHistory_arr = DownloadHistory::getHistorys($oDownload->kDownload);
                }

                if ($kBestellung > 0) {
                    $this->kBestellung = $kBestellung;
                    $oBestellung       = Shop::DB()->select(
                        'tbestellung',
                        'kBestellung',
                        $kBestellung,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'kBestellung, dBezahltDatum'
                    );
                    if (isset($oBestellung->kBestellung) && $oBestellung->kBestellung > 0 &&
                        $oBestellung->dBezahltDatum !== '0000-00-00' && $this->getTage() > 0) {
                        $paymentDate = new DateTime($oBestellung->dBezahltDatum);
                        $modifyBy    = $this->getTage() + 1;
                        $paymentDate->modify('+' . $modifyBy . ' day');
                        $this->dGueltigBis = $paymentDate->format('d.m.Y');
                    }
                }

                // Artikel
                $this->oArtikelDownload_arr = Shop::DB()->query(
                    "SELECT tartikeldownload.*
                        FROM tartikeldownload
                        JOIN tdownload 
                            ON tdownload.kDownload = tartikeldownload.kDownload
                        WHERE tartikeldownload.kDownload = " . (int)$this->kDownload . "
                        ORDER BY tdownload.nSort", 2
                );
            }
        }

        /**
         * @param bool $bPrimary
         * @return bool
         */
        public function save($bPrimary = false)
        {
            $oObj = $this->kopiereMembers();
            unset(
                $oObj->kDownload,
                $oObj->oDownloadSprache,
                $oObj->oDownloadHistory_arr,
                $oObj->oArtikelDownload_arr,
                $oObj->cLimit,
                $oObj->dGueltigBis,
                $oObj->kBestellung
            );
            $kDownload = Shop::DB()->insert('tdownload', $oObj);
            if ($kDownload > 0) {
                return $bPrimary ? $kDownload : true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function update()
        {
            $_upd                = new stdClass();
            $_upd->cID           = $this->cID;
            $_upd->cPfad         = $this->cPfad;
            $_upd->cPfadVorschau = $this->cPfadVorschau;
            $_upd->nAnzahl       = $this->nAnzahl;
            $_upd->nTage         = $this->nTage;
            $_upd->dErstellt     = $this->dErstellt;

            return Shop::DB()->update('tdownload', 'kDownload', (int)$this->kDownload, $_upd);
        }

        /**
         * @return mixed
         */
        public function delete()
        {
            return Shop::DB()->query(
                "DELETE tdownload, tdownloadhistory, tdownloadsprache, tartikeldownload
                    FROM tdownload
                    JOIN tdownloadsprache 
                        ON tdownloadsprache.kDownload = tdownload.kDownload
                    LEFT JOIN tartikeldownload 
                        ON tartikeldownload.kDownload = tdownload.kDownload
                    LEFT JOIN tdownloadhistory 
                        ON tdownloadhistory.kDownload = tdownload.kDownload
                    WHERE tdownload.kDownload = " . (int)$this->kDownload, 3
            );
        }

        /**
         * @param array $kKey_arr
         * @param int   $kSprache
         * @return array
         */
        public static function getDownloads($kKey_arr = [], $kSprache)
        {
            $kArtikel      = isset($kKey_arr['kArtikel']) ? (int)$kKey_arr['kArtikel'] : 0;
            $kBestellung   = isset($kKey_arr['kBestellung']) ? (int)$kKey_arr['kBestellung'] : 0;
            $kKunde        = isset($kKey_arr['kKunde']) ? (int)$kKey_arr['kKunde'] : 0;
            $kSprache      = isset($kSprache) ? (int)$kSprache : 0;
            $oDownload_arr = [];
            if (($kArtikel > 0 || $kBestellung > 0 || $kKunde > 0) && $kSprache > 0) {
                $cSQLSelect = "tartikeldownload.kDownload";
                $cSQLWhere  = "kArtikel = " . $kArtikel;
                $cSQLJoin   = "LEFT JOIN tdownload ON tartikeldownload.kDownload = tdownload.kDownload";
                if ($kBestellung > 0) {
                    $cSQLSelect = "tbestellung.kBestellung, tbestellung.kKunde, tartikeldownload.kDownload";
                    $cSQLWhere  = "tartikeldownload.kArtikel = twarenkorbpos.kArtikel";
                    $cSQLJoin   = "JOIN tbestellung ON tbestellung.kBestellung = " . $kBestellung . "
                                   JOIN tdownload ON tdownload.kDownload = tartikeldownload.kDownload
                                   JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                                        AND twarenkorbpos.nPosTyp = " . C_WARENKORBPOS_TYP_ARTIKEL;
                } elseif ($kKunde > 0) {
                    $cSQLSelect = "MAX(tbestellung.kBestellung) AS kBestellung, tbestellung.kKunde, tartikeldownload.kDownload";
                    $cSQLWhere  = "tartikeldownload.kArtikel = twarenkorbpos.kArtikel";
                    $cSQLJoin   = "JOIN tbestellung ON tbestellung.kKunde = " . $kKunde . "
                                   JOIN tdownload ON tdownload.kDownload = tartikeldownload.kDownload
                                   JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                                        AND twarenkorbpos.nPosTyp = " . C_WARENKORBPOS_TYP_ARTIKEL;
                }
                $oDown_arr = Shop::DB()->query(
                    "SELECT " . $cSQLSelect . "
                        FROM tartikeldownload
                        " . $cSQLJoin . "
                        WHERE " . $cSQLWhere . "
                        GROUP BY tartikeldownload.kDownload
                        ORDER BY tdownload.nSort, tdownload.dErstellt DESC", 2
                );
                if (is_array($oDown_arr) && count($oDown_arr) > 0) {
                    foreach ($oDown_arr as $i => &$oDown) {
                        $oDownload_arr[$i] = new self(
                            $oDown->kDownload,
                            $kSprache,
                            true,
                            (isset($oDown->kBestellung) ? $oDown->kBestellung : 0)
                        );
                        if (($kBestellung > 0 || $kKunde > 0) && $oDownload_arr[$i]->getAnzahl() > 0) {
                            $oDownloadHistory_arr = DownloadHistory::getOrderHistory($oDown->kKunde, $oDown->kBestellung);
                            $kDownload            = $oDownload_arr[$i]->getDownload();
                            $count                = isset($oDownloadHistory_arr[$kDownload])
                                ? count($oDownloadHistory_arr[$kDownload])
                                : 0;
                            $oDownload_arr[$i]->cLimit      = $count . ' / ' . $oDownload_arr[$i]->getAnzahl();
                            $oDownload_arr[$i]->kBestellung = $oDown->kBestellung;
                        }
                    }
                }
            }

            return $oDownload_arr;
        }

        /**
         * @param $oWarenkorb
         * @return bool
         */
        public static function hasDownloads($oWarenkorb)
        {
            if (count($oWarenkorb->PositionenArr) > 0) {
                foreach ($oWarenkorb->PositionenArr as &$oPosition) {
                    if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                        if (isset($oPosition->Artikel->oDownload_arr) && count($oPosition->Artikel->oDownload_arr) > 0) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        /**
         * @param int $kDownload
         * @param int $kKunde
         * @param int $kBestellung
         * @return int
         */
        public static function getFile($kDownload, $kKunde, $kBestellung)
        {
            $kDownload   = (int)$kDownload;
            $kKunde      = (int)$kKunde;
            $kBestellung = (int)$kBestellung;
            if ($kDownload > 0 && $kKunde > 0 && $kBestellung > 0) {
                $oDownload = new self($kDownload, 0, false);
                $nReturn   = $oDownload::checkFile($oDownload->kDownload, $kKunde, $kBestellung);
                if ($nReturn === 1) {
                    $oDownloadHistory = new DownloadHistory();
                    $oDownloadHistory->setDownload($kDownload);
                    $oDownloadHistory->setKunde($kKunde);
                    $oDownloadHistory->setBestellung($kBestellung);
                    $oDownloadHistory->setErstellt('now()');
                    $oDownloadHistory->save();

                    self::send_file_to_browser(
                        PFAD_DOWNLOADS . $oDownload->getPfad(),
                        'application/octet-stream',
                        true
                    );

                    return 1;
                }

                return $nReturn;
            }

            return 7;
        }

        /**
         * Fehlercodes:
         * 1 = Alles O.K.
         * 2 = Bestellung nicht gefunden
         * 3 = Kunde stimmt nicht
         * 4 = Kein Artikel mit Downloads gefunden
         * 5 = Maximales Downloadlimit wurde erreicht
         * 6 = Maximales Datum wurde erreicht
         * 7 = Paramter fehlen
         *
         * @param int $kDownload
         * @param int $kKunde
         * @param int $kBestellung
         * @return int
         */
        public static function checkFile($kDownload, $kKunde, $kBestellung)
        {
            $kDownload   = (int)$kDownload;
            $kKunde      = (int)$kKunde;
            $kBestellung = (int)$kBestellung;
            if ($kDownload > 0 && $kKunde > 0 && $kBestellung > 0) {
                $oBestellung = new Bestellung($kBestellung);
                // Existiert die Bestellung und wurde Sie bezahlt?
                if ($oBestellung->kBestellung > 0 &&
                    (!empty($oBestellung->dBezahltDatum) && $oBestellung->dBezahltDatum !== '0000-00-00')
                ) {
                    // Stimmt der Kunde?
                    if ((int)$oBestellung->kKunde === $kKunde) {
                        $oBestellung->fuelleBestellung();
                        $oDownload = new self($kDownload, 0, false);
                        // Gibt es einen Artikel der zum Download passt?
                        if (is_array($oDownload->oArtikelDownload_arr) && count($oDownload->oArtikelDownload_arr) > 0) {
                            foreach ($oBestellung->Positionen as &$oPosition) {
                                foreach ($oDownload->oArtikelDownload_arr as &$oArtikelDownload) {
                                    if ($oPosition->kArtikel == $oArtikelDownload->kArtikel) {
                                        // Check Anzahl
                                        if ($oDownload->getAnzahl() > 0) {
                                            $oDownloadHistory_arr = DownloadHistory::getOrderHistory(
                                                $kKunde,
                                                $kBestellung
                                            );
                                            if (count($oDownloadHistory_arr[$oDownload->kDownload]) >= $oDownload->getAnzahl()) {
                                                return 5;
                                            }
                                        }
                                        // Check Datum
                                        $paymentDate = new DateTime($oBestellung->dBezahltDatum);
                                        $paymentDate->modify('+' . ($oDownload->getTage() + 1) . ' day');
                                        if ($oDownload->getTage() > 0 && $paymentDate < new DateTime()) {
                                            return 6;
                                        }

                                        return 1;
                                    }
                                }
                            }
                        } else {
                            return 4;
                        }
                    } else {
                        return 3;
                    }
                } else {
                    return 2;
                }
            }

            return 7;
        }

        /**
         * Fehlercodes:
         * 2 = Bestellung nicht gefunden
         * 3 = Kunde stimmt nicht
         * 4 = Kein Artikel mit Downloads gefunden
         * 5 = Maximales Downloadlimit wurde erreicht
         * 6 = Maximales Datum wurde erreicht
         * 7 = Paramter fehlen
         *
         * @param int $nErrorCode
         * @return string
         */
        public static function mapGetFileErrorCode($nErrorCode)
        {
            $cError = '';
            if ((int)$nErrorCode > 0) {
                switch ((int)$nErrorCode) {
                    case 2: // Bestellung nicht gefunden
                        $cError = Shop::Lang()->get('dlErrorOrderNotFound', 'global');
                        break;
                    case 3: // Kunde stimmt nicht
                        $cError = Shop::Lang()->get('dlErrorCustomerNotMatch', 'global');
                        break;
                    case 4: // Kein Artikel mit Downloads gefunden
                        $cError = Shop::Lang()->get('dlErrorDownloadNotFound', 'global');
                        break;
                    case 5: // Maximales Downloadlimit wurde erreicht
                        $cError = Shop::Lang()->get('dlErrorDownloadLimitReached', 'global');
                        break;
                    case 6: // Maximales Datum wurde erreicht
                        $cError = Shop::Lang()->get('dlErrorValidityReached', 'global');
                        break;
                    case 7: // Paramter fehlen
                        $cError = Shop::Lang()->get('dlErrorWrongParameter', 'global');
                        break;
                }
            }

            return $cError;
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
         * @param string $cID
         * @return $this
         */
        public function setID($cID)
        {
            $this->cID = $cID;

            return $this;
        }

        /**
         * @param string $cPfad
         * @return $this
         */
        public function setPfad($cPfad)
        {
            $this->cPfad = $cPfad;

            return $this;
        }

        /**
         * @param string $cPfadVorschau
         * @return $this
         */
        public function setPfadVorschau($cPfadVorschau)
        {
            $this->cPfadVorschau = $cPfadVorschau;

            return $this;
        }

        /**
         * @param int $nAnzahl
         * @return $this
         */
        public function setAnzahl($nAnzahl)
        {
            $this->nAnzahl = (int)$nAnzahl;

            return $this;
        }

        /**
         * @param int $nTage
         * @return $this
         */
        public function setTage($nTage)
        {
            $this->nTage = (int)$nTage;

            return $this;
        }

        /**
         * @param int $nSort
         * @return $this
         */
        public function setSort($nSort)
        {
            $this->nSort = (int)$nSort;

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
        public function getDownload()
        {
            return $this->kDownload;
        }

        /**
         * @return string
         */
        public function getID()
        {
            return $this->cID;
        }

        /**
         * @return string
         */
        public function getPfad()
        {
            return $this->cPfad;
        }

        /**
         * @return bool
         */
        public function hasPreview()
        {
            return (strlen($this->cPfadVorschau) > 0);
        }

        /**
         * @return string
         */
        public function getExtension()
        {
            if (strlen($this->cPfad) > 0) {
                $cPath_arr = pathinfo($this->cPfad);
                if (is_array($cPath_arr)) {
                    return strtoupper($cPath_arr['extension']);
                }
            }

            return '';
        }

        /**
         * @return string
         */
        public function getPreviewExtension()
        {
            if (strlen($this->cPfadVorschau) > 0) {
                $cPath_arr = pathinfo($this->cPfadVorschau);
                if (is_array($cPath_arr)) {
                    return strtoupper($cPath_arr['extension']);
                }
            }

            return '';
        }

        /**
         * @return string
         */
        public function getPreviewType()
        {
            switch (strtolower($this->getPreviewExtension())) {
                case 'mpeg':
                case 'mpg':
                case 'avi':
                case 'wmv':
                case 'mp4':
                    return 'video';

                case 'wav':
                case 'mp3':
                case 'wma':
                    return 'music';

                case 'gif':
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'jpe':
                case 'bmp':
                    return 'image';
                default:
                    break;
            }

            return 'misc';
        }

        /**
         * @return string
         */
        public function getPreview()
        {
            return Shop::getURL() . '/' . PFAD_DOWNLOADS_PREVIEW_REL . $this->cPfadVorschau;
        }

        /**
         * @return int
         */
        public function getAnzahl()
        {
            return $this->nAnzahl;
        }

        /**
         * @return int
         */
        public function getTage()
        {
            return $this->nTage;
        }

        /**
         * @return int
         */
        public function getSort()
        {
            return $this->nSort;
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
                foreach ($cMember_arr as &$cMember) {
                    $obj->$cMember = $this->$cMember;
                }
            }

            return $obj;
        }

        /**
         * @param string $filename
         * @param string $mimetype
         * @param bool $bEncode
         */
        private static function send_file_to_browser($filename, $mimetype, $bEncode = false)
        {
            if ($bEncode) {
                $file     = basename($filename);
                $filename = str_replace($file, '', $filename);
                $filename .= utf8_encode($file);
            }
            $browser_agent   = 'other';
            $HTTP_USER_AGENT = (!empty($_SERVER['HTTP_USER_AGENT']))
                ? $_SERVER['HTTP_USER_AGENT']
                : '';
            if (preg_match('/Opera\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'opera';
            } elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'ie';
            } elseif (preg_match('/OmniWeb\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'omniweb';
            } elseif (preg_match('/Netscape([0-9]{1})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'netscape';
            } elseif (preg_match('/Mozilla\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'mozilla';
            } elseif (preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'konqueror';
            }
            if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
                $mimetype = ($browser_agent === 'ie' || $browser_agent === 'opera')
                    ? 'application/octetstream'
                    : 'application/octet-stream';
            }

            @ob_end_clean();
            @ini_set('zlib.output_compression', 'Off');

            header('Pragma: public');
            header('Content-Transfer-Encoding: none');
            if ($browser_agent === 'ie') {
                header('Content-Type: ' . $mimetype);
                header('Content-Disposition: inline; filename="' . basename($filename) . '"');
            } else {
                header('Content-Type: ' . $mimetype . '; name="' . basename($filename) . '"');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            }

            $size = @filesize($filename);
            if ($size) {
                header("Content-length: $size");
            }

            readfile($filename);
            exit;
        }
    }
}
