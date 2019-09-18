<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class JobQueue
 */
class JobQueue
{
    /**
     * @var int
     */
    public $kJobQueue;

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
    public $nLimitN;

    /**
     * @var int
     */
    public $nLimitM;

    /**
     * @var int
     */
    public $nInArbeit;

    /**
     * @var string
     */
    public $cJobArt;

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
    public $dStartZeit;

    /**
     * @var string
     */
    public $dZuletztGelaufen;

    /**
     * @return int
     */
    public function getKJobQueue()
    {
        return $this->kJobQueue;
    }

    /**
     * @param int $kJobQueue
     * @return $this
     */
    public function setKJobQueue($kJobQueue)
    {
        $this->kJobQueue = $kJobQueue;

        return $this;
    }

    /**
     * @return int
     */
    public function getKCron()
    {
        return $this->kCron;
    }

    /**
     * @param int $kCron
     * @return $this
     */
    public function setKCron($kCron)
    {
        $this->kCron = (int)$kCron;

        return $this;
    }

    /**
     * @return int
     */
    public function getKKey()
    {
        return $this->kKey;
    }

    /**
     * @param int $kKey
     * @return $this
     */
    public function setKKey($kKey)
    {
        $this->kKey = $kKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getNLimitN()
    {
        return $this->nLimitN;
    }

    /**
     * @param int $nLimitN
     * @return $this
     */
    public function setNLimitN($nLimitN)
    {
        $this->nLimitN = (int)$nLimitN;

        return $this;
    }

    /**
     * @return int
     */
    public function getNLimitM()
    {
        return $this->nLimitM;
    }

    /**
     * @param int $nLimitM
     * @return $this
     */
    public function setNLimitM($nLimitM)
    {
        $this->nLimitM = (int)$nLimitM;

        return $this;
    }

    /**
     * @return int
     */
    public function getNInArbeit()
    {
        return $this->nInArbeit;
    }

    /**
     * @param int $nInArbeit
     * @return $this
     */
    public function setNInArbeit($nInArbeit)
    {
        $this->nInArbeit = (int)$nInArbeit;

        return $this;
    }

    /**
     * @return string
     */
    public function getCJobArt()
    {
        return $this->cJobArt;
    }

    /**
     * @param string $cJobArt
     * @return $this
     */
    public function setCJobArt($cJobArt)
    {
        $this->cJobArt = $cJobArt;

        return $this;
    }

    /**
     * @return string
     */
    public function getCTabelle()
    {
        return $this->cTabelle;
    }

    /**
     * @param string $cTabelle
     * @return $this
     */
    public function setCTabelle($cTabelle)
    {
        $this->cTabelle = $cTabelle;

        return $this;
    }

    /**
     * @return string
     */
    public function getCKey()
    {
        return $this->cKey;
    }

    /**
     * @param string $cKey
     */
    public function setCKey($cKey)
    {
        $this->cKey = $cKey;
    }

    /**
     * @return string
     */
    public function getDStartZeit()
    {
        return $this->dStartZeit;
    }

    /**
     * @param string $dStartZeit
     * @return $this
     */
    public function setDStartZeit($dStartZeit)
    {
        $this->dStartZeit = $dStartZeit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDZuletztGelaufen()
    {
        return $this->dZuletztGelaufen;
    }

    /**
     * @param string $dZuletztGelaufen
     * @return $this
     */
    public function setDZuletztGelaufen($dZuletztGelaufen)
    {
        $this->dZuletztGelaufen = $dZuletztGelaufen;

        return $this;
    }

    /**
     * @param int|null $kJobQueue
     * @param int      $kCron
     * @param int      $kKey
     * @param int      $nLimitN
     * @param int      $nLimitM
     * @param int      $nInArbeit
     * @param string   $cJobArt
     * @param string   $cTabelle
     * @param string   $cKey
     * @param string   $dStartZeit
     * @param string   $dZuletztGelaufen
     */
    public function __construct($kJobQueue = null, $kCron = 0, $kKey = 0, $nLimitN = 0, $nLimitM = 0, $nInArbeit = 0, $cJobArt = '', $cTabelle = '', $cKey = '', $dStartZeit = 'now()', $dZuletztGelaufen = '0000-00-00')
    {
        $this->kJobQueue        = $kJobQueue;
        $this->kCron            = $kCron;
        $this->kKey             = $kKey;
        $this->nLimitN          = $nLimitN;
        $this->nLimitM          = $nLimitM;
        $this->nInArbeit        = $nInArbeit;
        $this->cJobArt          = $cJobArt;
        $this->cTabelle         = $cTabelle;
        $this->cKey             = $cKey;
        $this->dStartZeit       = $dStartZeit;
        $this->dZuletztGelaufen = $dZuletztGelaufen;
    }

    /**
     * @return object|null
     */
    public function holeJobArt()
    {
        if ($this->kKey > 0 && strlen($this->cTabelle) > 0) {
            return Shop::DB()->select(Shop::DB()->escape($this->cTabelle), Shop::DB()->escape($this->cKey), (int)$this->kKey);
        }

        return null;
    }

    /**
     * @return int
     */
    public function speicherJobInDB()
    {
        if ($this->kKey > 0 &&
            $this->nLimitM > 0 &&
            strlen($this->cJobArt) > 0 &&
            strlen($this->cKey) > 0 &&
            strlen($this->cTabelle) > 0 &&
            strlen($this->dStartZeit) > 0
        ) {
            $queue = kopiereMembers($this);
            unset($queue->kJobQueue);

            return Shop::DB()->insert('tjobqueue', $queue);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function updateJobInDB()
    {
        if ($this->kJobQueue > 0) {
            $_upd                   = new stdClass();
            $_upd->kCron            = (int)$this->kCron;
            $_upd->kKey             = (int)$this->kKey;
            $_upd->nLimitN          = (int)$this->nLimitN;
            $_upd->nLimitM          = (int)$this->nLimitM;
            $_upd->nInArbeit        = (int)$this->nInArbeit;
            $_upd->cJobArt          = $this->cJobArt;
            $_upd->cTabelle         = $this->cTabelle;
            $_upd->cKey             = $this->cKey;
            $_upd->dStartZeit       = $this->dStartZeit;
            $_upd->dZuletztGelaufen = $this->dZuletztGelaufen;

            return Shop::DB()->update('tjobqueue', 'kJobQueue', (int)$this->kJobQueue, $_upd);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function deleteJobInDB()
    {
        return ($this->kJobQueue > 0)
            ? Shop::DB()->delete('tjobqueue', 'kJobQueue', (int)$this->kJobQueue)
            : 0;
    }

    /**
     * @return bool
     */
    public function updateExportformatQueueBearbeitet()
    {
        if ($this->kJobQueue > 0) {
            Shop::DB()->delete('texportformatqueuebearbeitet', 'kJobQueue', (int)$this->kJobQueue);

            $ins                   = new stdClass();
            $ins->kJobQueue        = $this->kJobQueue;
            $ins->kExportformat    = $this->kKey;
            $ins->nLimitN          = $this->nLimitN;
            $ins->nLimitM          = $this->nLimitM;
            $ins->nInArbeit        = $this->nInArbeit;
            $ins->dStartZeit       = $this->dStartZeit;
            $ins->dZuletztGelaufen = $this->dZuletztGelaufen;

            Shop::DB()->insert('texportformatqueuebearbeitet', $ins);

            return true;
        }

        return false;
    }
}
