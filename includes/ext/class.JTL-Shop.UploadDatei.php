<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UPLOADS)) {
    /**
     * Class UploadDatei
     */
    class UploadDatei
    {
        /**
         * @var int
         */
        public $kUpload;

        /**
         * @var int
         */
        public $kCustomID;

        /**
         * @var int
         */
        public $nTyp;

        /**
         * @var string
         */
        public $cName;

        /**
         * @var string
         */
        public $cPfad;

        /**
         * @var string
         */
        public $dErstellt;

        /**
         * @param int $kUpload
         */
        public function __construct($kUpload = 0)
        {
            if ((int)$kUpload > 0) {
                $this->loadFromDB($kUpload);
            }
        }

        /**
         * @param int $kUpload
         * @return bool
         */
        public function loadFromDB($kUpload)
        {
            $oUpload = Shop::DB()->select('tuploaddatei', 'kUpload', (int)$kUpload);
            if (isset($oUpload->kUpload) && (int)$oUpload->kUpload > 0) {
                self::copyMembers($oUpload, $this);

                return true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function save()
        {
            return Shop::DB()->insert('tuploaddatei', self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function update()
        {
            return Shop::DB()->update('tuploaddatei', 'kUpload', (int)$this->kUpload, self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function delete()
        {
            return Shop::DB()->delete('tuploaddatei', 'kUpload', (int)$this->kUpload);
        }

        /**
         * @param int $customerID
         * @return bool
         */
        public function validateOwner($customerID)
        {
            return Shop::DB()->queryPrepared(
                'SELECT tbestellung.kKunde
                    FROM tuploaddatei 
                    JOIN tbestellung
                    ON tbestellung.kBestellung = tuploaddatei.kCustomID
                    WHERE tuploaddatei.kCustomID = :ulid AND tbestellung.kKunde = :cid',
                    ['ulid' => (int)$this->kCustomID, 'cid' => (int)$customerID],
                    1
                ) !== false;
        }

        /**
         * @param int $kCustomID
         * @param int $nTyp
         * @return mixed
         */
        public static function fetchAll($kCustomID, $nTyp)
        {
            $oUploadDatei_arr = Shop::DB()->selectAll(
                'tuploaddatei',
                ['kCustomID', 'nTyp'],
                [(int)$kCustomID, (int)$nTyp]
            );

            if (is_array($oUploadDatei_arr)) {
                foreach ($oUploadDatei_arr as &$oUpload) {
                    $oUpload->cGroesse   = Upload::formatGroesse($oUpload->nBytes);
                    $oUpload->bVorhanden = is_file(PFAD_UPLOADS . $oUpload->cPfad);
                    $oUpload->bVorschau  = Upload::vorschauTyp($oUpload->cName);
                    $oUpload->cBildpfad  = sprintf(
                        '%s/%s?action=preview&secret=%s&sid=%s',
                        Shop::getURL(),
                        PFAD_UPLOAD_CALLBACK,
                        rawurlencode(verschluesselXTEA($oUpload->kUpload)),
                        session_id()
                    );
                }
            }

            return $oUploadDatei_arr;
        }

        /**
         * @param object        $objFrom
         * @param null|stdClass $objTo
         * @return null|stdClass
         */
        private static function copyMembers($objFrom, &$objTo = null)
        {
            if (!is_object($objTo)) {
                $objTo = new stdClass();
            }
            $cMember_arr = array_keys(get_object_vars($objFrom));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $objTo->$cMember = $objFrom->$cMember;
                }
            }

            return $objTo;
        }

        /**
         * @param string $filename
         * @param string $mimetype
         * @param bool   $bEncode
         * @param string $downloadName
         */
        public static function send_file_to_browser($filename, $mimetype, $bEncode = false, $downloadName)
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
                header('Content-Disposition: inline; filename="' . $downloadName . '"');
            } else {
                header('Content-Type: ' . $mimetype . '; name="' . basename($filename) . '"');
                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
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
