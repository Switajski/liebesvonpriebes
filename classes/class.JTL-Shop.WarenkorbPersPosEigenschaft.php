<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPersPosEigenschaft
 */
class WarenkorbPersPosEigenschaft
{
    /**
     * @var int
     */
    public $kWarenkorbPersPosEigenschaft;

    /**
     * @var int
     */
    public $kWarenkorbPersPos;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var string
     */
    public $cFreifeldWert;

    /**
     * @var string
     */
    public $cEigenschaftName;

    /**
     * @var string
     */
    public $cEigenschaftWertName;

    /**
     * @param int    $kEigenschaft
     * @param int    $kEigenschaftWert
     * @param string $cFreifeldWert
     * @param string $cEigenschaftName
     * @param string $cEigenschaftWertName
     * @param int    $kWarenkorbPersPos
     */
    public function __construct($kEigenschaft, $kEigenschaftWert, $cFreifeldWert, $cEigenschaftName, $cEigenschaftWertName, $kWarenkorbPersPos)
    {
        $this->kWarenkorbPersPos    = (int)$kWarenkorbPersPos;
        $this->kEigenschaft         = (int)$kEigenschaft;
        $this->kEigenschaftWert     = (int)$kEigenschaftWert;
        $this->cFreifeldWert        = $cFreifeldWert;
        $this->cEigenschaftName     = $cEigenschaftName;
        $this->cEigenschaftWertName = $cEigenschaftWertName;
    }

    /**
     * @return $this
     */
    public function schreibeDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kWarenkorbPersPosEigenschaft);
        $this->kWarenkorbPersPosEigenschaft = Shop::DB()->insert('twarenkorbpersposeigenschaft', $obj);

        return $this;
    }
}
