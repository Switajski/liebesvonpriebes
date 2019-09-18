<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (class_exists('AuswahlAssistent')) {
    /**
     * Class AuswahlAssistentOrt
     */
    class AuswahlAssistentOrt
    {
        /**
         * @var int
         */
        public $kAuswahlAssistentOrt;

        /**
         * @var int
         */
        public $kAuswahlAssistentGruppe;

        /**
         * @var string
         */
        public $cKey;

        /**
         * @var int
         */
        public $kKey;

        /**
         * @var array
         */
        public $oOrt_arr;

        /**
         * @var string
         */
        public $cOrt;

        /**
         * @param int  $kAuswahlAssistentOrt
         * @param int  $kAuswahlAssistentGruppe
         * @param bool $bBackend
         */
        public function __construct($kAuswahlAssistentOrt = 0, $kAuswahlAssistentGruppe = 0, $bBackend = false)
        {
            if ($kAuswahlAssistentOrt > 0 || $kAuswahlAssistentGruppe > 0) {
                $this->loadFromDB($kAuswahlAssistentOrt, $kAuswahlAssistentGruppe, $bBackend);
            }
        }

        /**
         * @param int  $kAuswahlAssistentOrt
         * @param int  $kAuswahlAssistentGruppe
         * @param bool $bBackend
         */
        private function loadFromDB($kAuswahlAssistentOrt, $kAuswahlAssistentGruppe, $bBackend)
        {
            $kAuswahlAssistentGruppe = (int)$kAuswahlAssistentGruppe;
            $kAuswahlAssistentOrt    = (int)$kAuswahlAssistentOrt;
            if ($kAuswahlAssistentGruppe > 0) {
                $this->oOrt_arr = [];
                $oOrtTMP_arr    = Shop::DB()->selectAll(
                    'tauswahlassistentort',
                    'kAuswahlAssistentGruppe',
                    $kAuswahlAssistentGruppe
                );
                if (is_array($oOrtTMP_arr) && count($oOrtTMP_arr) > 0) {
                    foreach ($oOrtTMP_arr as $oOrtTMP) {
                        $this->oOrt_arr[] = new self($oOrtTMP->kAuswahlAssistentOrt, 0, $bBackend);
                    }
                }
            } elseif ($kAuswahlAssistentOrt > 0) {
                $oOrt = Shop::DB()->select(
                    'tauswahlassistentort',
                    'kAuswahlAssistentOrt',
                    $kAuswahlAssistentOrt
                );
                if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                    $cMember_arr = array_keys(get_object_vars($oOrt));
                    if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                        foreach ($cMember_arr as $cMember) {
                            $this->$cMember = $oOrt->$cMember;
                        }
                    }
                    $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                    $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
                    $this->kKey                    = (int)$this->kKey;
                    // cKey Mapping
                    switch ($this->cKey) {
                        case AUSWAHLASSISTENT_ORT_KATEGORIE:
                            if ($bBackend) {
                                unset($_SESSION['oKategorie_arr'], $_SESSION['oKategorie_arr_new']);
                            }
                            $oKategorie = new Kategorie(
                                $this->kKey,
                                AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe)
                            );

                            $this->cOrt = $oKategorie->cName . '(Kategorie)';
                            break;

                        case AUSWAHLASSISTENT_ORT_LINK:
                            $oSprache   = Shop::DB()->select(
                                'tsprache',
                                'kSprache',
                                AuswahlAssistentGruppe::getLanguage($this->kAuswahlAssistentGruppe)
                            );
                            $oLink      = Shop::DB()->select(
                                'tlinksprache',
                                'kLink',
                                $this->kKey,
                                'cISOSprache',
                                $oSprache->cISO,
                                null,
                                null,
                                false,
                                'cName'
                            );
                            $this->cOrt = isset($oLink->cName) ? ($oLink->cName . '(CMS)') : null;
                            break;

                        case AUSWAHLASSISTENT_ORT_STARTSEITE:
                            $this->cOrt = 'Startseite';
                            break;
                    }
                }
            }
        }

        /**
         * @param array $cParam_arr
         * @param int   $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function saveLocation($cParam_arr, $kAuswahlAssistentGruppe)
        {
            $kAuswahlAssistentGruppe = (int)$kAuswahlAssistentGruppe;
            if ($kAuswahlAssistentGruppe > 0 && is_array($cParam_arr) && count($cParam_arr) > 0) {
                // Kategorie
                if (isset($cParam_arr['cKategorie']) && strlen($cParam_arr['cKategorie']) > 0) {
                    $cKategorie_arr = explode(';', $cParam_arr['cKategorie']);
                    if (is_array($cKategorie_arr) && count($cKategorie_arr) > 0) {
                        foreach ($cKategorie_arr as $cKategorie) {
                            if ((int)$cKategorie > 0 && strlen($cKategorie) > 0) {
                                $oOrt                          = new stdClass();
                                $oOrt->kAuswahlAssistentGruppe = $kAuswahlAssistentGruppe;
                                $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_KATEGORIE;
                                $oOrt->kKey                    = $cKategorie;

                                Shop::DB()->insert('tauswahlassistentort', $oOrt);
                            }
                        }
                    }
                }
                // Spezialseite
                if (isset($cParam_arr['kLink_arr']) &&
                    is_array($cParam_arr['kLink_arr']) &&
                    count($cParam_arr['kLink_arr']) > 0
                ) {
                    foreach ($cParam_arr['kLink_arr'] as $kLink) {
                        if ((int)$kLink > 0) {
                            $oOrt                          = new stdClass();
                            $oOrt->kAuswahlAssistentGruppe = $kAuswahlAssistentGruppe;
                            $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_LINK;
                            $oOrt->kKey                    = $kLink;

                            Shop::DB()->insert('tauswahlassistentort', $oOrt);
                        }
                    }
                }
                // Startseite
                if (isset($cParam_arr['nStartseite']) && (int)$cParam_arr['nStartseite'] === 1) {
                    $oOrt                          = new stdClass();
                    $oOrt->kAuswahlAssistentGruppe = $kAuswahlAssistentGruppe;
                    $oOrt->cKey                    = AUSWAHLASSISTENT_ORT_STARTSEITE;
                    $oOrt->kKey                    = 1;

                    Shop::DB()->insert('tauswahlassistentort', $oOrt);
                }
            }

            return false;
        }

        /**
         * @param array $cParam_arr
         * @param int   $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function updateLocation($cParam_arr, $kAuswahlAssistentGruppe)
        {
            $kAuswahlAssistentGruppe = (int)$kAuswahlAssistentGruppe;
            if ($kAuswahlAssistentGruppe > 0 && is_array($cParam_arr) && count($cParam_arr) > 0) {
                $nRow = Shop::DB()->delete(
                    'tauswahlassistentort',
                    'kAuswahlAssistentGruppe',
                    $kAuswahlAssistentGruppe
                );

                if ($nRow > 0) {
                    if (self::saveLocation($cParam_arr, $kAuswahlAssistentGruppe)) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * @param array $cParam_arr
         * @param bool  $bUpdate
         * @return array
         */
        public static function checkLocation($cParam_arr, $bUpdate = false)
        {
            $cPlausi_arr = [];
            // Ort
            if ((!isset($cParam_arr['cKategorie']) || strlen($cParam_arr['cKategorie']) === 0) &&
                (!isset($cParam_arr['kLink_arr']) ||
                    !is_array($cParam_arr['kLink_arr']) ||
                    count($cParam_arr['kLink_arr']) === 0) &&
                $cParam_arr['nStartseite'] == 0
            ) {
                $cPlausi_arr['cOrt'] = 1;
            }
            // Ort Kategorie
            if (isset($cParam_arr['cKategorie']) && strlen($cParam_arr['cKategorie']) > 0) {
                $cKategorie_arr = explode(';', $cParam_arr['cKategorie']);

                if (!is_array($cKategorie_arr) || count($cKategorie_arr) === 0) {
                    $cPlausi_arr['cKategorie'] = 1;
                }
                if (!is_numeric($cKategorie_arr[0])) {
                    $cPlausi_arr['cKategorie'] = 2;
                }

                foreach ($cKategorie_arr as $cKategorie) {
                    if ((int)$cKategorie > 0 && strlen($cKategorie) > 0) {
                        if ($bUpdate) {
                            if (self::isCategoryTaken(
                                $cKategorie,
                                $cParam_arr['kSprache'],
                                $cParam_arr['kAuswahlAssistentGruppe'])
                            ) {
                                $cPlausi_arr['cKategorie'] = 3;
                            }
                        } else {
                            if (self::isCategoryTaken($cKategorie, $cParam_arr['kSprache'])) {
                                $cPlausi_arr['cKategorie'] = 3;
                            }
                        }
                    }
                }
            }
            // Ort Spezialseite
            if (isset($cParam_arr['kLink_arr']) &&
                is_array($cParam_arr['kLink_arr']) &&
                count($cParam_arr['kLink_arr']) > 0
            ) {
                foreach ($cParam_arr['kLink_arr'] as $kLink) {
                    if ((int)$kLink > 0) {
                        if ($bUpdate) {
                            if (self::isLinkTaken
                            ($kLink,
                                $cParam_arr['kSprache'],
                                $cParam_arr['kAuswahlAssistentGruppe'])
                            ) {
                                $cPlausi_arr['kLink_arr'] = 1;
                            }
                        } else {
                            if (self::isLinkTaken($kLink, $cParam_arr['kSprache'])) {
                                $cPlausi_arr['kLink_arr'] = 1;
                            }
                        }
                    }
                }
            }
            // Ort Startseite
            if (isset($cParam_arr['nStartseite']) && (int)$cParam_arr['nStartseite'] === 1) {
                if ($bUpdate) {
                    if (self::isStartPageTaken(
                        $cParam_arr['kSprache'],
                        $cParam_arr['kAuswahlAssistentGruppe'])
                    ) {
                        $cPlausi_arr['nStartseite'] = 1;
                    }
                } else {
                    if (self::isStartPageTaken($cParam_arr['kSprache'])) {
                        $cPlausi_arr['nStartseite'] = 1;
                    }
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @param int $kKategorie
         * @param int $kSprache
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function isCategoryTaken($kKategorie, $kSprache, $kAuswahlAssistentGruppe = 0)
        {
            if ((int)$kKategorie === 0 || (int)$kSprache === 0) {
                return false;
            }
            $cOrtSQL = '';
            if ($kAuswahlAssistentGruppe > 0) {
                $cOrtSQL = " AND tauswahlassistentort.kAuswahlAssistentGruppe != " . (int)$kAuswahlAssistentGruppe;
            }
            $oOrt = Shop::DB()->query(
                "SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort
                    JOIN tauswahlassistentgruppe 
                        ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                        AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                    WHERE tauswahlassistentort.cKey = '" . AUSWAHLASSISTENT_ORT_KATEGORIE . "'
                        " . $cOrtSQL . "
                        AND tauswahlassistentort.kKey = " . (int)$kKategorie, 1
            );

            return (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0);
        }

        /**
         * @param int $kLink
         * @param int $kSprache
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function isLinkTaken($kLink, $kSprache, $kAuswahlAssistentGruppe = 0)
        {
            if ((int)$kLink === 0 || (int)$kSprache === 0) {
                return false;
            }
            $cOrtSQL = '';
            if ($kAuswahlAssistentGruppe > 0) {
                $cOrtSQL = " AND tauswahlassistentort.kAuswahlAssistentGruppe != " . (int)$kAuswahlAssistentGruppe;
            }
            $oOrt = Shop::DB()->query(
                "SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort
                    JOIN tauswahlassistentgruppe 
                        ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                        AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                    WHERE tauswahlassistentort.cKey = '" . AUSWAHLASSISTENT_ORT_LINK . "'
                        " . $cOrtSQL . "
                        AND tauswahlassistentort.kKey = " . (int)$kLink, 1
            );

            return (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0);
        }

        /**
         * @param int $kSprache
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        public static function isStartPageTaken($kSprache, $kAuswahlAssistentGruppe = 0)
        {
            if ((int)$kSprache === 0) {
                return false;
            }
            $cOrtSQL = '';
            if ($kAuswahlAssistentGruppe > 0) {
                $cOrtSQL = " AND tauswahlassistentort.kAuswahlAssistentGruppe != " . (int)$kAuswahlAssistentGruppe;
            }

            $oOrt = Shop::DB()->query(
                "SELECT kAuswahlAssistentOrt
                    FROM tauswahlassistentort
                    JOIN tauswahlassistentgruppe 
                        ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                        AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                    WHERE tauswahlassistentort.cKey = '" . AUSWAHLASSISTENT_ORT_STARTSEITE . "'
                        " . $cOrtSQL . "
                        AND tauswahlassistentort.kKey = 1", 1
            );

            return (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0);
        }

        /**
         * @param string $cKey
         * @param int    $kKey
         * @param int    $kSprache
         * @param bool   $bBackend
         * @return AuswahlAssistentOrt|null
         */
        public static function getLocation($cKey, $kKey, $kSprache, $bBackend = false)
        {
            if ((int)$kKey > 0 && (int)$kSprache > 0 && strlen($cKey) > 0) {
                $oOrt = Shop::DB()->executeQueryPrepared(
                    "SELECT kAuswahlAssistentOrt
                        FROM tauswahlassistentort
                        JOIN tauswahlassistentgruppe 
                            ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                            AND tauswahlassistentgruppe.kSprache = :lang
                        WHERE tauswahlassistentort.cKey = :ckey
                            AND tauswahlassistentort.kKey = :kkey",
                    ['lang' => (int)$kSprache, 'ckey' => $cKey, 'kkey' => (int)$kKey],
                    1
                );

                if (isset($oOrt->kAuswahlAssistentOrt) && $oOrt->kAuswahlAssistentOrt > 0) {
                    return new self($oOrt->kAuswahlAssistentOrt, 0, $bBackend);
                }
            }

            return null;
        }
    }
}
