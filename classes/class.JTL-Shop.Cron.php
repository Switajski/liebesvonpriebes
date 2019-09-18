<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Cron
 */
class Cron
{
    /**
     * @var int
     */
    public $kCron;

    /**
     * @var int
     */
    public $kKey;

    /**
     * @var int
     */
    public $nAlleXStd;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cTabelle;

    /**
     * @var string
     */
    public $cKey;

    /**
     * @var string
     */
    public $cJobArt;

    /**
     * @var string
     */
    public $dStart;

    /**
     * @var string
     */
    public $dStartZeit;

    /**
     * @var string
     */
    public $dLetzterStart;

    /**
     * @param int    $kCron
     * @param int    $kKey
     * @param int    $nAlleXStd
     * @param string $cName
     * @param string $cJobArt
     * @param string $cTabelle
     * @param string $cKey
     * @param string $dStart
     * @param string $dStartZeit
     * @param string $dLetzterStart
     */
    public function __construct($kCron = 0, $kKey = 0, $nAlleXStd = 0, $cName = '', $cJobArt = '', $cTabelle = '', $cKey = '', $dStart = '0000-00-00 00:00:00', $dStartZeit = '00:00:00', $dLetzterStart = '0000-00-00 00:00:00')
    {
        $this->kCron         = (int)$kCron;
        $this->kKey          = $kKey;
        $this->cKey          = $cKey;
        $this->cTabelle      = $cTabelle;
        $this->cName         = $cName;
        $this->cJobArt       = $cJobArt;
        $this->nAlleXStd     = $nAlleXStd;
        $this->dStart        = $dStart;
        $this->dStartZeit    = $dStartZeit;
        $this->dLetzterStart = $dLetzterStart;
    }

    /**
     * @return mixed|bool
     */
    public function holeCronArt()
    {
        if ($this->kKey > 0 && strlen($this->cTabelle > 0)) {
            return Shop::DB()->selectAll($this->cTabelle, $this->cKey, (int)$this->kKey);
        }

        return false;
    }

    /**
     * @return mixed|bool
     */
    public function speicherInDB()
    {
        if ($this->kKey > 0 && $this->cKey && $this->cTabelle && $this->cName && $this->nAlleXStd && $this->dStart) {
            return Shop::DB()->insert('tcron', $this);
        }

        return false;
    }

    /**
     * @param string $cJobArt
     * @param string $dStart
     * @param int    $nLimitM
     * @return int|bool
     */
    public function speicherInJobQueue($cJobArt, $dStart, $nLimitM)
    {
        if ($dStart && $nLimitM > 0 && strlen($cJobArt) > 0) {
            $oJobQueue             = new stdClass();
            $oJobQueue->kCron      = $this->kCron;
            $oJobQueue->kKey       = $this->kKey;
            $oJobQueue->cKey       = $this->cKey;
            $oJobQueue->cTabelle   = $this->cTabelle;
            $oJobQueue->cJobArt    = $cJobArt;
            $oJobQueue->dStartZeit = $dStart;
            $oJobQueue->nLimitN    = 0;
            $oJobQueue->nLimitM    = $nLimitM;
            $oJobQueue->nInArbeit  = 0;

            return Shop::DB()->insert('tjobqueue', $oJobQueue);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function updateCronDB()
    {
        if ($this->kCron > 0) {
            $_upd                = new stdClass();
            $_upd->kKey          = (int)$this->kKey;
            $_upd->cKey          = $this->cKey;
            $_upd->cTabelle      = $this->cTabelle;
            $_upd->cName         = $this->cName;
            $_upd->cJobArt       = $this->cJobArt;
            $_upd->nAlleXStd     = (int)$this->nAlleXStd;
            $_upd->dStart        = $this->dStart;
            $_upd->dLetzterStart = $this->dLetzterStart;

            return Shop::DB()->update('tcron', 'kCron', $this->kCron, $_upd) >= 0;
        }

        return false;
    }
}
