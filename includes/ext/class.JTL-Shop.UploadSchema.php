<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UPLOADS)) {
    /**
     * Class UploadSchema
     */
    class UploadSchema
    {
        /**
         * @var int
         */
        public $kUploadSchema;

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
        public $cBeschreibung;

        /**
         * @var string
         */
        public $cDateiTyp;

        /**
         * @var int
         */
        public $nPflicht;

        /**
         * @param int $kUploadSchema
         */
        public function __construct($kUploadSchema = 0)
        {
            if ((int)$kUploadSchema > 0) {
                $this->loadFromDB($kUploadSchema);
            }
        }

        /**
         * @param $kUploadSchema
         */
        private function loadFromDB($kUploadSchema)
        {
            $oUpload = Shop::DB()->query(
                "SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                    tuploadschema.cDateiTyp, tuploadschema.nPflicht, tuploadschemasprache.cName, 
                    tuploadschemasprache.cBeschreibung
                    FROM tuploadschema
                    LEFT JOIN tuploadschemasprache
                        ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                        AND tuploadschemasprache.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE kUploadSchema =  " . (int)$kUploadSchema, 1
            );

            if (isset($oUpload->kUploadSchema) && (int)$oUpload->kUploadSchema > 0) {
                self::copyMembers($oUpload, $this);
            }
        }

        /**
         * @return int
         */
        public function save()
        {
            return Shop::DB()->insert('tuploadschema', self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function update()
        {
            return Shop::DB()->update(
                'tuploadschema',
                'kUploadSchema',
                (int)$this->kUploadSchema,
                self::copyMembers($this)
            );
        }

        /**
         * @return int
         */
        public function delete()
        {
            return Shop::DB()->delete('tuploadschema', 'kUploadSchema', (int)$this->kUploadSchema);
        }

        /**
         * @param int $kCustomID
         * @param int $nTyp
         * @return mixed
         */
        public static function fetchAll($kCustomID, $nTyp)
        {
            $cSql = '';
            if ($nTyp === UPLOAD_TYP_WARENKORBPOS) {
                $cSql = " AND kCustomID = '" . (int)$kCustomID . "'";
            }

            return Shop::DB()->query(
                "SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                    tuploadschema.cDateiTyp, tuploadschema.nPflicht, 
                    IFNULL(tuploadschemasprache.cName,tuploadschema.cName ) cName,
                    IFNULL(tuploadschemasprache.cBeschreibung, tuploadschema.cBeschreibung) cBeschreibung
                    FROM tuploadschema
                    LEFT JOIN tuploadschemasprache
                        ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                        AND tuploadschemasprache.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE nTyp = " . (int)$nTyp . $cSql, 2
            );
        }

        /**
         * @param object        $objFrom
         * @param stdClass|null $objTo
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
    }
}
