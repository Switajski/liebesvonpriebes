<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Warenlager
 */
class Warenlager extends MainModel
{
    /**
     * @var int
     */
    public $kWarenlager;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cKuerzel;

    /**
     * @var string
     */
    public $cLagerTyp;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var int
     */
    public $nFulfillment;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var
     */
    public $oLageranzeige;

    /**
     * @var array
     */
    public $cSpracheAssoc_arr;

    /**
     * @var float
     */
    public $fBestand;

    /**
     * @var float
     */
    public $fZulauf;

    /**
     * @var string
     */
    public $dZulaufDatum;

    /**
     * @var string
     */
    public $dZulaufDatum_de;

    /**
     * @return int
     */
    public function getWarenlager()
    {
        return $this->kWarenlager;
    }

    /**
     * @param $kWarenlager
     * @return $this
     */
    public function setWarenlager($kWarenlager)
    {
        $this->kWarenlager = (int)$kWarenlager;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @param $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return string
     */
    public function getKuerzel()
    {
        return $this->cKuerzel;
    }

    /**
     * @param $cKuerzel
     * @return $this
     */
    public function setKuerzel($cKuerzel)
    {
        $this->cKuerzel = $cKuerzel;

        return $this;
    }

    /**
     * @return string
     */
    public function getLagerTyp()
    {
        return $this->cLagerTyp;
    }

    /**
     * @param $cLagerTyp
     * @return $this
     */
    public function setLagerTyp($cLagerTyp)
    {
        $this->cLagerTyp = $cLagerTyp;

        return $this;
    }

    /**
     * @return string
     */
    public function getBeschreibung()
    {
        return $this->cBeschreibung;
    }

    /**
     * @param $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung)
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * @return string
     */
    public function getStrasse()
    {
        return $this->cStrasse;
    }

    /**
     * @param $cStrasse
     * @return $this
     */
    public function setStrasse($cStrasse)
    {
        $this->cStrasse = $cStrasse;

        return $this;
    }

    /**
     * @return string
     */
    public function getPLZ()
    {
        return $this->cPLZ;
    }

    /**
     * @param $cPLZ
     * @return $this
     */
    public function setPLZ($cPLZ)
    {
        $this->cPLZ = $cPLZ;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrt()
    {
        return $this->cOrt;
    }

    /**
     * @param $cOrt
     * @return $this
     */
    public function setOrt($cOrt)
    {
        $this->cOrt = $cOrt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLand()
    {
        return $this->cLand;
    }

    /**
     * @param $cLand
     * @return $this
     */
    public function setLand($cLand)
    {
        $this->cLand = $cLand;

        return $this;
    }

    /**
     * @return int
     */
    public function getFulfillment()
    {
        return $this->nFulfillment;
    }

    /**
     * @param $nFulfillment
     * @return $this
     */
    public function setFulfillment($nFulfillment)
    {
        $this->nFulfillment = (int)$nFulfillment;

        return $this;
    }

    /**
     * @return int
     */
    public function getAktiv()
    {
        return $this->nAktiv;
    }

    /**
     * @param $nAktiv
     * @return $this
     */
    public function setAktiv($nAktiv)
    {
        $this->nAktiv = (int)$nAktiv;

        return $this;
    }

    /**
     * @param int         $kKey
     * @param null|object $oObj
     * @param int|null    $xOption
     */
    public function load($kKey, $oObj = null, $xOption = null)
    {
        if ($kKey !== null) {
            $kKey = (int)$kKey;

            if ($kKey > 0) {
                $cSqlSelect = '';
                $cSqlJoin   = '';
                // $xOption = kSprache
                if ($xOption !== null && (int)$xOption > 0) {
                    $xOption    = (int)$xOption;
                    $cSqlSelect = ", IF (twarenlagersprache.cName IS NOT NULL, twarenlagersprache.cName, twarenlager.cName) AS cName";
                    $cSqlJoin   = "LEFT JOIN twarenlagersprache ON twarenlagersprache.kWarenlager = twarenlager.kWarenlager
                                    AND twarenlagersprache.kSprache = {$xOption}";
                }

                $oObj = Shop::DB()->query(
                    "SELECT twarenlager.* {$cSqlSelect}
                         FROM twarenlager
                         {$cSqlJoin}
                         WHERE twarenlager.kWarenlager = {$kKey}", 1
                );
            }
        }

        if (isset($oObj->kWarenlager) && $oObj->kWarenlager > 0) {
            $this->loadObject($oObj);
        }
    }

    /**
     * @param bool $bPrim
     * @return bool|int
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

        if ($this->getWarenlager() === null) {
            $kPrim = Shop::DB()->insert('twarenlager', $oObj);

            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }
        } else {
            $xResult = $this->update();

            if ($xResult) {
                return $bPrim ? -1 : true;
            }
        }

        return false;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function update()
    {
        $cQuery      = "UPDATE twarenlager SET ";
        $cSet_arr    = [];
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $mValue = "'" . Shop::DB()->escape(call_user_func([&$this, $cMethod])) . "'";
                    if (call_user_func([&$this, $cMethod]) === null) {
                        $mValue = 'NULL';
                    }
                    $cSet_arr[] = "{$cMember} = {$mValue}";
                }
            }

            $cQuery .= implode(', ', $cSet_arr);
            $cQuery .= " WHERE kWarenlager = {$this->kWarenlager}";

            return Shop::DB()->query($cQuery, 3);
        } else {
            throw new Exception('ERROR: Object has no members!');
        }
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $nRows = Shop::DB()->query(
            "DELETE twarenlager, twarenlagersprache
                FROM twarenlager
                LEFT JOIN twarenlagersprache 
                    ON twarenlagersprache.kWarenlager = twarenlager.kWarenlager
                WHERE twarenlager.kWarenlager = " . (int)$this->kWarenlager, 3
        );

        return $nRows;
    }

    /**
     * @return bool
     */
    public function loadLanguages()
    {
        if ($this->getWarenlager() > 0) {
            $oObj_arr = Shop::DB()->selectAll('twarenlagersprache', 'kWarenlager', $this->getWarenlager());

            if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                $this->cSpracheAssoc_arr = [];
                foreach ($oObj_arr as $oObj) {
                    $this->cSpracheAssoc_arr[$oObj->kSprache] = $oObj->cName;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $bActive
     * @param bool $bLoadLanguages
     * @return array|null
     */
    public static function getAll($bActive = true, $bLoadLanguages = false)
    {
        $oWarenlager_arr = [];
        $cSql            = '';
        if ($bActive) {
            $cSql = " WHERE nAktiv = 1";
        }
        $oObj_arr = Shop::DB()->query(
            "SELECT *
               FROM twarenlager
               {$cSql}", 2
        );

        if (is_array($oObj_arr) && count($oObj_arr) > 0) {
            foreach ($oObj_arr as $oObj) {
                $oWarenlager = new self(null, $oObj);
                // Languages?
                if ($bLoadLanguages) {
                    $oWarenlager->loadLanguages();
                }
                $oWarenlager_arr[] = $oWarenlager;
            }
        }

        return $oWarenlager_arr;
    }

    /**
     * @param int        $kArtikel
     * @param int|null   $kSprache
     * @param null|array $xOption_arr
     * @param bool       $bActive
     * @return array|null
     */
    public static function getByProduct($kArtikel, $kSprache = null, $xOption_arr = null, $bActive = true)
    {
        $oWarenlager_arr = [];
        $kArtikel        = (int)$kArtikel;
        if ($kArtikel > 0) {
            $cSql = '';
            if ($bActive) {
                $cSql = " AND twarenlager.nAktiv = 1";
            }
            $oObj_arr = Shop::DB()->query(
                "SELECT tartikelwarenlager.*
                    FROM tartikelwarenlager
                    JOIN twarenlager 
                        ON twarenlager.kWarenlager = tartikelwarenlager.kWarenlager
                       {$cSql}
                    WHERE tartikelwarenlager.kArtikel = {$kArtikel}", 2
            );
            if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                $oWarenlager_arr = [];
                foreach ($oObj_arr as $oObj) {
                    $oWarenlager               = new self($oObj->kWarenlager, null, $kSprache);
                    $oWarenlager->fBestand     = $oObj->fBestand;
                    $oWarenlager->fZulauf      = $oObj->fZulauf;
                    $oWarenlager->dZulaufDatum = $oObj->dZulaufDatum;
                    if (strlen($oWarenlager->dZulaufDatum) > 1) {
                        try {
                            $oDateTime                    = new DateTime($oObj->dZulaufDatum);
                            $oWarenlager->dZulaufDatum_de = $oDateTime->format('d.m.Y');
                        } catch (Exception $exc) {
                            $oWarenlager->dZulaufDatum_de = '00.00.0000';
                        }
                    }
                    if (is_array($xOption_arr)) {
                        $oWarenlager->buildWarehouseInfo($oWarenlager->fBestand, $xOption_arr);
                    }
                    $oWarenlager_arr[] = $oWarenlager;
                }
            }
        }

        return $oWarenlager_arr;
    }

    /**
     * @param float $fBestand
     * @param array $xOption_arr
     * @return $this
     */
    public function buildWarehouseInfo($fBestand, array $xOption_arr)
    {
        $this->oLageranzeige                = new stdClass();
        $this->oLageranzeige->cLagerhinweis = [];
        $conf                               = Shop::getSettings([CONF_GLOBAL, CONF_ARTIKELDETAILS]);

        if ($xOption_arr['cLagerBeachten'] === 'Y') {
            if ($fBestand > 0) {
                $this->oLageranzeige->cLagerhinweis['genau']          = $fBestand . ' ' .
                    $xOption_arr['cEinheit'] . ' ' . Shop::Lang()->get('inStock', 'global');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productAvailable', 'global');
                if (isset($conf['artikeldetails']['artikel_lagerbestandsanzeige']) &&
                    $conf['artikeldetails']['artikel_lagerbestandsanzeige'] === 'verfuegbarkeit'
                ) {
                    $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen', 'global');
                }
            } elseif ($xOption_arr['cLagerKleinerNull'] === 'Y') {
                $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen', 'global');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen', 'global');
            } else {
                $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('productNotAvailable', 'global');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productNotAvailable', 'global');
            }
        } else {
            $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen', 'global');
            $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen', 'global');
        }
        if ($xOption_arr['cLagerBeachten'] === 'Y') {
            // ampel
            $this->oLageranzeige->nStatus   = 1;
            $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gelb'];
            if ($fBestand <= (int)$conf['global']['artikel_lagerampel_rot']) {
                $this->oLageranzeige->nStatus   = 0;
                $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_rot'];
            }
            if ($xOption_arr['cLagerBeachten'] !== 'Y' || $fBestand >= (int)$conf['global']['artikel_lagerampel_gruen'] ||
                ($xOption_arr['cLagerBeachten'] === 'Y' && $xOption_arr['cLagerKleinerNull'] === 'Y' &&
                    $conf['global']['artikel_ampel_lagernull_gruen'] === 'Y'
                )
            ) {
                $this->oLageranzeige->nStatus   = 2;
                $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gruen'];
            }
        } else {
            $this->oLageranzeige->nStatus = (int)$conf['global']['artikel_lagerampel_keinlager'];
            if ($this->oLageranzeige->nStatus < 0 || $this->oLageranzeige->nStatus > 2) {
                $this->oLageranzeige->nStatus = 2;
            }

            switch ($this->oLageranzeige->nStatus) {
                case 1:
                    $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gelb'];
                    break;
                case 0:
                    $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_rot'];
                    break;
                case 2:
                    $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gruen'];
                    break;
            }
        }

        return $this;
    }
}
