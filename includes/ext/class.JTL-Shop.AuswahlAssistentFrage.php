<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (class_exists('AuswahlAssistent')) {
    /**
     * Class AuswahlAssistentFrage
     */
    class AuswahlAssistentFrage
    {
        /**
         * @var int
         */
        public $kAuswahlAssistentFrage = 0;

        /**
         * @var int
         */
        public $kAuswahlAssistentGruppe = 0;

        /**
         * @var int
         */
        public $kMerkmal = 0;

        /**
         * @var string
         */
        public $cFrage = '';

        /**
         * @var int
         */
        public $nSort = 0;

        /**
         * @var int
         */
        public $nAktiv = 0;

        /**
         * @var array
         */
        public $oWert_arr = [];

        /**
         * @var array - mapping from kMerkmalWert to tmerkmalwert object
         */
        public $oWert_assoc = [];

        /**
         * @var int - how many products found that have a value of this attribute
         */
        public $nTotalResultCount = 0;

        /**
         * @var object - used by old AWA
         */
        public $oMerkmal;

        /**
         * @param int  $kAuswahlAssistentFrage
         * @param bool $bOnlyActive
         */
        public function __construct($kAuswahlAssistentFrage = 0, $bOnlyActive = true)
        {
            $kAuswahlAssistentFrage = (int)$kAuswahlAssistentFrage;

            if ($kAuswahlAssistentFrage > 0) {
                $this->loadFromDB($kAuswahlAssistentFrage, $bOnlyActive);
            }
        }

        /**
         * @param int  $kAuswahlAssistentFrage
         * @param bool $bOnlyActive
         */
        private function loadFromDB($kAuswahlAssistentFrage, $bOnlyActive = true)
        {
            $oDbResult = Shop::DB()->query(
                "SELECT af.*, m.cBildpfad, COALESCE(ms.cName, m.cName) AS cName, m.cBildpfad
                    FROM tauswahlassistentfrage AS af
                        JOIN tauswahlassistentgruppe as ag
                            ON ag.kAuswahlAssistentGruppe = af.kAuswahlAssistentGruppe 
                        JOIN tmerkmal AS m
                            ON m.kMerkmal = af.kMerkmal 
                        LEFT JOIN tmerkmalsprache AS ms
                            ON ms.kMerkmal = m.kMerkmal 
                                AND ms.kSprache = ag.kSprache
                    WHERE af.kAuswahlAssistentFrage = " . $kAuswahlAssistentFrage .
                        ($bOnlyActive ? " AND af.nAktiv = 1" : ""),
                1
            );

            if ($oDbResult !== null && $oDbResult !== false) {
                foreach (get_object_vars($oDbResult) as $name => $value) {
                    $this->$name = $value;
                }
                $this->kAuswahlAssistentFrage  = (int)$this->kAuswahlAssistentFrage;
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kMerkmal                = (int)$this->kMerkmal;
                $this->nSort                   = (int)$this->nSort;
                $this->nAktiv                  = (int)$this->nAktiv;

                if (TEMPLATE_COMPATIBILITY === true) {
                    // Used by old AWA
                    $this->oMerkmal = self::getMerkmal($this->kMerkmal, true);
                }
            }
        }

        /**
         * @param int  $kAuswahlAssistentGruppe
         * @param bool $bAktiv
         * @return array
         */
        public static function getQuestions($kAuswahlAssistentGruppe, $bAktiv = true)
        {
            $oAuswahlAssistentFrage_arr = [];
            if ((int)$kAuswahlAssistentGruppe > 0) {
                $cAktivSQL = '';
                if ($bAktiv) {
                    $cAktivSQL = " AND nAktiv = 1";
                }
                $oFrage_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tauswahlassistentfrage
                        WHERE kAuswahlAssistentGruppe = " . (int)$kAuswahlAssistentGruppe .
                        $cAktivSQL . "
                        ORDER BY nSort", 2
                );
                if (count($oFrage_arr) > 0) {
                    foreach ($oFrage_arr as $oFrage) {
                        $oAuswahlAssistentFrage_arr[] = new self($oFrage->kAuswahlAssistentFrage, $bAktiv);
                    }
                }
            }

            return $oAuswahlAssistentFrage_arr;
        }

        /**
         * @param bool $bPrimary
         * @return array|bool
         */
        public function saveQuestion($bPrimary = false)
        {
            $cPlausi_arr = $this->checkQuestion();
            if (count($cPlausi_arr) === 0) {
                $obj                          = new stdClass();
                $obj->kAuswahlAssistentFrage  = $this->kAuswahlAssistentFrage;
                $obj->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
                $obj->kMerkmal                = $this->kMerkmal;
                $obj->cFrage                  = $this->cFrage;
                $obj->nSort                   = $this->nSort;
                $obj->nAktiv                  = $this->nAktiv;
                $kAuswahlAssistentFrage       = Shop::DB()->insert('tauswahlassistentfrage', $obj);

                if ($kAuswahlAssistentFrage > 0) {
                    return $bPrimary ? $kAuswahlAssistentFrage : true;
                }

                return false;
            }

            return $cPlausi_arr;
        }

        /**
         * @return array|bool
         */
        public function updateQuestion()
        {
            $cPlausi_arr = $this->checkQuestion(true);
            if (count($cPlausi_arr) === 0) {
                $_upd                          = new stdClass();
                $_upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
                $_upd->kMerkmal                = $this->kMerkmal;
                $_upd->cFrage                  = $this->cFrage;
                $_upd->nSort                   = $this->nSort;
                $_upd->nAktiv                  = $this->nAktiv;

                Shop::DB()->update(
                    'tauswahlassistentfrage',
                    'kAuswahlAssistentFrage',
                    (int)$this->kAuswahlAssistentFrage,
                    $_upd
                );

                return true;
            }

            return $cPlausi_arr;
        }

        /**
         * @param array $cParam_arr
         * @return bool
         */
        public static function deleteQuestion($cParam_arr)
        {
            if (isset($cParam_arr['kAuswahlAssistentFrage_arr']) &&
                is_array($cParam_arr['kAuswahlAssistentFrage_arr']) &&
                count($cParam_arr['kAuswahlAssistentFrage_arr']) > 0) {
                foreach ($cParam_arr['kAuswahlAssistentFrage_arr'] as $kAuswahlAssistentFrage) {
                    Shop::DB()->delete(
                        'tauswahlassistentfrage',
                        'kAuswahlAssistentFrage',
                        (int)$kAuswahlAssistentFrage
                    );
                }

                return true;
            }

            return false;
        }

        /**
         * @param bool $bUpdate
         * @return array
         */
        public function checkQuestion($bUpdate = false)
        {
            $cPlausi_arr = [];
            // Frage
            if (strlen($this->cFrage) === 0) {
                $cPlausi_arr['cFrage'] = 1;
            }
            // Gruppe
            if ($this->kAuswahlAssistentGruppe === null ||
                $this->kAuswahlAssistentGruppe === 0 ||
                $this->kAuswahlAssistentGruppe === -1
            ) {
                $cPlausi_arr['kAuswahlAssistentGruppe'] = 1;
            }
            // Merkmal
            if ($this->kMerkmal === null || $this->kMerkmal === 0 || $this->kMerkmal === -1) {
                $cPlausi_arr['kMerkmal'] = 1;
            }
            if (!$bUpdate && $this->isMerkmalTaken($this->kMerkmal, $this->kAuswahlAssistentGruppe)) {
                $cPlausi_arr['kMerkmal'] = 2;
            }
            // Sortierung
            if ($this->nSort <= 0) {
                $cPlausi_arr['nSort'] = 1;
            }
            // Aktiv
            if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
                $cPlausi_arr['nAktiv'] = 1;
            }

            return $cPlausi_arr;
        }

        /**
         * @param int $kMerkmal
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        private function isMerkmalTaken($kMerkmal, $kAuswahlAssistentGruppe)
        {
            if ($kMerkmal > 0 && $kAuswahlAssistentGruppe > 0) {
                $oFrage = Shop::DB()->select(
                    'tauswahlassistentfrage',
                    'kMerkmal',
                    (int)$kMerkmal,
                    'kAuswahlAssistentGruppe',
                    (int)$kAuswahlAssistentGruppe
                );
                if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param int  $kMerkmal
         * @param bool $bMMW
         * @return Merkmal|stdClass
         */
        public static function getMerkmal($kMerkmal, $bMMW = false)
        {
            $kMerkmal = (int)$kMerkmal;
            if ($kMerkmal > 0) {
                return new Merkmal($kMerkmal, $bMMW);
            }

            return new stdClass();
        }
    }
}
