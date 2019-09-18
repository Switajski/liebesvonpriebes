<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WunschlistePosEigenschaft
 */
class WunschlistePosEigenschaft
{
    /**
     * @var int
     */
    public $kWunschlistePosEigenschaft;

    /**
     * @var int
     */
    public $kWunschlistePos;

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
     * @param int    $kWunschlistePos
     */
    public function __construct($kEigenschaft, $kEigenschaftWert, $cFreifeldWert, $cEigenschaftName, $cEigenschaftWertName, $kWunschlistePos)
    {
        $this->kEigenschaft         = (int)$kEigenschaft;
        $this->kEigenschaftWert     = (int)$kEigenschaftWert;
        $this->kWunschlistePos      = (int)$kWunschlistePos;
        $this->cFreifeldWert        = $cFreifeldWert;
        $this->cEigenschaftName     = $cEigenschaftName;
        $this->cEigenschaftWertName = $cEigenschaftWertName;
    }

    /**
     * @return $this
     */
    public function schreibeDB()
    {
        $this->kWunschlistePosEigenschaft = Shop::DB()->insert('twunschlisteposeigenschaft', kopiereMembers($this));

        return $this;
    }
}
