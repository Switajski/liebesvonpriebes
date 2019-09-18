<?php

/**
 * Class Kupon
 *
 * @access public
 */
class KuponBestellung
{
    /**
     * @access public
     * @var int
     */
    public $kKupon;

    /**
     * @access public
     * @var int
     */
    public $kBestellung;

    /**
     * @access public
     * @var int
     */
    public $kKunde;

    /**
     * @access public
     * @var string
     */
    public $cBestellNr;

    /**
     * @access public
     * @var float
     */
    public $fGesamtsummeBrutto;

    /**
     * @access public
     * @var float
     */
    public $fKuponwertBrutto;

    /**
     * @access public
     * @var string
     */
    public $cKuponTyp;

    /**
     * @access public
     * @var string
     */
    public $dErstellt;

    /**
     * Constructor
     *
     * @param int $kKupon - primarykey
     * @param int $kBestellung - primarykey
     * @access public
     */
    public function __construct($kKupon = 0, $kBestellung = 0)
    {
        if ((int)$kKupon > 0 && (int)$kBestellung > 0) {
            $this->loadFromDB($kKupon, $kBestellung);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kKupon
     * @param int $kBestellung
     * @return $this
     * @access private
     */
    private function loadFromDB($kKupon = 0, $kBestellung = 0)
    {
        $oObj = Shop::DB()->select(
            'tkuponbestelllung',
            'kKupon', (int)$kKupon,
            'kBestellung', (int)$kBestellung
        );

        if (isset($oObj->kKupon) && $oObj->kKupon > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }

        return $this;
    }

    /**
     * Store the class in the database
     *
     * @param bool $bPrim - Controls the return of the method
     * @return bool|int
     * @access public
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

        $kPrim = Shop::DB()->insert('tkuponbestellung', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @return int
     * @access public
     */
    public function update()
    {
        $_upd                      = new stdClass();
        $_upd->kKupon              = $this->kKupon;
        $_upd->kBestellung         = $this->kBestellung;
        $_upd->kKunde              = $this->kKunde;
        $_upd->cBestellNr          = $this->cBestellNr;
        $_upd->fGesammtsummeBrutto = $this->fGesamtsummeBrutto;
        $_upd->fKuponwertBrutto    = $this->fKuponwertBrutto;
        $_upd->cKuponTyp           = $this->cKuponTyp;
        $_upd->dErstellt           = $this->dErstellt;

        return Shop::DB()->update(
            'tkuponbestellung',
            ['kKupon','kBestellung'],
            [(int)$this->kKupon,(int)$this->kBestellung],
            $_upd
        );
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tkupon', ['kKupon','kBestellung'], [(int)$this->kKupon,(int)$this->kBestellung]);
    }

    /**
     * Sets the kKupon
     *
     * @access public
     * @param int $kKupon
     * @return $this
     */
    public function setKupon($kKupon)
    {
        $this->kKupon = (int)$kKupon;

        return $this;
    }

    /**
     * Sets the kBestellung
     *
     * @access public
     * @param int $kBestellung
     * @return $this
     */
    public function setBestellung($kBestellung)
    {
        $this->kBestellung = (int)$kBestellung;

        return $this;
    }

    /**
     * Sets the kKunde
     *
     * @access public
     * @param int $kKunde
     * @return $this
     */
    public function setKunden($kKunde)
    {
        $this->kKunde = (int)$kKunde;

        return $this;
    }

    /**
     * Sets the cBestellNr
     *
     * @access public
     * @param string $cBestellNr
     * @return $this
     */
    public function setBestellNr($cBestellNr)
    {
        $this->cBestellNr = Shop::DB()->escape($cBestellNr);

        return $this;
    }

    /**
     * Sets the fGesamtsummeBrutto
     *
     * @access public
     * @param float $fGesamtsummeBrutto
     * @return $this
     */
    public function setGesamtsummeBrutto($fGesamtsummeBrutto)
    {
        $this->fGesamtsummeBrutto = (float)$fGesamtsummeBrutto;

        return $this;
    }

    /**
     * Sets the fKuponwertBrutto
     *
     * @access public
     * @param float $fKuponwertBrutto
     * @return $this
     */
    public function setKuponwertBrutto($fKuponwertBrutto)
    {
        $this->fKuponwertBrutto = (float)$fKuponwertBrutto;

        return $this;
    }

    /**
     * Sets the cKuponTyp
     *
     * @access public
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp)
    {
        $this->cKuponTyp = Shop::DB()->escape($cKuponTyp);

        return $this;
    }

    /**
     * Sets the dErstellt
     *
     * @access public
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = Shop::DB()->escape($dErstellt);

        return $this;
    }

    /**
     * Gets the kKupon
     *
     * @access public
     * @return int
     */
    public function getKupon()
    {
        return $this->kKupon;
    }

    /**
     * Gets the kBestellung
     *
     * @access public
     * @return int
     */
    public function getBestellung()
    {
        return $this->kBestellung;
    }

    /**
     * Gets the kKunde
     *
     * @access public
     * @return int
     */
    public function getKunde()
    {
        return $this->kKunde;
    }

    /**
     * Gets the cBestellNr
     *
     * @access public
     * @return string
     */
    public function getBestellNr()
    {
        return $this->cBestellNr;
    }

    /**
     * Gets the fGesamtsummeBrutto
     *
     * @access public
     * @return float
     */
    public function getGesamtsummeBrutto()
    {
        return $this->fGesamtsummeBrutto;
    }

    /**
     * Gets the fKuponwertBrutto
     *
     * @access public
     * @return float
     */
    public function getKuponwertBrutto()
    {
        return $this->fKuponwertBrutto;
    }

    /**
     * Gets the cKuponTyp
     *
     * @access public
     * @return string
     */
    public function getKuponTyp()
    {
        return $this->cKuponTyp;
    }

    /**
     * Gets the dErstellt
     *
     * @access public
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * Gets used coupons from orders
     *
     * @access public
     * @param string $dStart
     * @param string $dEnd
     * @return array
     */
    public static function getOrdersWithUsedCoupons($dStart, $dEnd, $kKupon = 0)
    {
        $ordersWithUsedCoupons = Shop::DB()->query(
            "SELECT kbs.*, wkp.cName, kp.kKupon
                FROM tkuponbestellung AS kbs
                LEFT JOIN tbestellung AS bs 
                   ON kbs.kBestellung = bs.kBestellung
                LEFT JOIN twarenkorbpos AS wkp 
                    ON bs.kWarenkorb = wkp.kWarenkorb
                LEFT JOIN tkupon AS kp 
                    ON kbs.kKupon = kp.kKupon
                WHERE kbs.dErstellt BETWEEN '" . $dStart . "'
                    AND '" . $dEnd . "'
                    AND bs.cStatus != " . BESTELLUNG_STATUS_STORNO . "
                    AND (wkp.nPosTyp = 3 OR wkp.nPosTyp = 7) " .
                ((int)$kKupon > 0 ? " AND kp.kKupon = " . (int)$kKupon : '') . "
                ORDER BY kbs.dErstellt DESC", 9
        );

        return $ordersWithUsedCoupons;
    }
}
