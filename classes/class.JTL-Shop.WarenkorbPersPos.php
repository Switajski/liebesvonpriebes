<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPersPos
 */
class WarenkorbPersPos
{
    /**
     * @var int
     */
    public $kWarenkorbPersPos;

    /**
     * @var int
     */
    public $kWarenkorbPers;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fAnzahl;

    /**
     * @var string
     */
    public $cArtikelName;

    /**
     * @var string
     */
    public $dHinzugefuegt;

    /**
     * @var string
     */
    public $dHinzugefuegt_de;

    /**
     * @var string
     */
    public $cUnique;

    /**
     * @var string
     */
    public $cResponsibility;

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var int
     */
    public $nPosTyp;

    /**
     * @var array
     */
    public $oWarenkorbPersPosEigenschaft_arr = [];

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * @param int        $kArtikel
     * @param string     $cArtikelName
     * @param float      $fAnzahl
     * @param int        $kWarenkorbPers
     * @param string     $cUnique
     * @param int        $kKonfigitem
     * @param int|string $nPosTyp
     * @param string     $cResponsibility
     */
    public function __construct(
        $kArtikel,
        $cArtikelName,
        $fAnzahl,
        $kWarenkorbPers,
        $cUnique = '',
        $kKonfigitem = 0,
        $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL,
        $cResponsibility = 'core'
    ) {
        $this->kArtikel        = (int)$kArtikel;
        $this->cArtikelName    = $cArtikelName;
        $this->fAnzahl         = $fAnzahl;
        $this->dHinzugefuegt   = 'now()';
        $this->kWarenkorbPers  = (int)$kWarenkorbPers;
        $this->cUnique         = $cUnique;
        $this->cResponsibility = !empty($cResponsibility) ? $cResponsibility : 'core';
        $this->kKonfigitem     = $kKonfigitem;
        $this->nPosTyp         = $nPosTyp;
    }

    /**
     * @param array $oEigenschaftwerte_arr
     * @return $this
     */
    public function erstellePosEigenschaften($oEigenschaftwerte_arr)
    {
        foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
            if (isset($oEigenschaftwerte->kEigenschaft)) {
                $oWarenkorbPersPosEigenschaft = new WarenkorbPersPosEigenschaft(
                    $oEigenschaftwerte->kEigenschaft,
                    (isset($oEigenschaftwerte->kEigenschaftWert) ? $oEigenschaftwerte->kEigenschaftWert : null),
                    (isset($oEigenschaftwerte->cFreifeldWert) ? $oEigenschaftwerte->cFreifeldWert : null),
                    (isset($oEigenschaftwerte->cEigenschaftName) ? $oEigenschaftwerte->cEigenschaftName : null),
                    (isset($oEigenschaftwerte->cEigenschaftWertName) ? $oEigenschaftwerte->cEigenschaftWertName : null),
                    $this->kWarenkorbPersPos
                );
                $oWarenkorbPersPosEigenschaft->schreibeDB();
                $this->oWarenkorbPersPosEigenschaft_arr[] = $oWarenkorbPersPosEigenschaft;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB()
    {
        $oTemp                   = new stdClass();
        $oTemp->kWarenkorbPers   = $this->kWarenkorbPers;
        $oTemp->kArtikel         = $this->kArtikel;
        $oTemp->cArtikelName     = $this->cArtikelName;
        $oTemp->fAnzahl          = $this->fAnzahl;
        $oTemp->dHinzugefuegt    = $this->dHinzugefuegt;
        $oTemp->cUnique          = $this->cUnique;
        $oTemp->cResponsibility  = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $oTemp->kKonfigitem      = $this->kKonfigitem;
        $oTemp->nPosTyp          = $this->nPosTyp;
        $this->kWarenkorbPersPos = Shop::DB()->insert('twarenkorbperspos', $oTemp);

        return $this;
    }

    /**
     * @return int
     */
    public function updateDB()
    {
        $oTemp                    = new stdClass();
        $oTemp->kWarenkorbPersPos = $this->kWarenkorbPersPos;
        $oTemp->kWarenkorbPers    = $this->kWarenkorbPers;
        $oTemp->kArtikel          = $this->kArtikel;
        $oTemp->cArtikelName      = $this->cArtikelName;
        $oTemp->fAnzahl           = $this->fAnzahl;
        $oTemp->dHinzugefuegt     = $this->dHinzugefuegt;
        $oTemp->cUnique           = $this->cUnique;
        $oTemp->cResponsibility   = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $oTemp->kKonfigitem       = $this->kKonfigitem;
        $oTemp->nPosTyp           = $this->nPosTyp;

        return Shop::DB()->update('twarenkorbperspos', 'kWarenkorbPersPos', $this->kWarenkorbPersPos, $oTemp);
    }

    /**
     * @param int    $kEigenschaft
     * @param int    $kEigenschaftWert
     * @param string $cFreifeldWert
     * @return bool
     */
    public function istEigenschaftEnthalten($kEigenschaft, $kEigenschaftWert, $cFreifeldWert = '')
    {
        foreach ($this->oWarenkorbPersPosEigenschaft_arr as $oWarenkorbPersPosEigenschaft) {
            if ($oWarenkorbPersPosEigenschaft->kEigenschaft == $kEigenschaft &&
                ((!empty($oWarenkorbPersPosEigenschaft->kEigenschaftWert) &&
                    $oWarenkorbPersPosEigenschaft->kEigenschaftWert === $kEigenschaftWert) ||
                ($oWarenkorbPersPosEigenschaft->kEigenschaftWert === 0 &&
                    $oWarenkorbPersPosEigenschaft->cFreifeldWert === $cFreifeldWert))) {
                return true;
            }
        }

        return false;
    }
}
