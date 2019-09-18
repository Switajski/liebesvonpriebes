<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferscheinposinfo
 */
class Lieferscheinposinfo
{
    /**
     * @access protected
     * @var int
     */
    protected $kLieferscheinPosInfo;

    /**
     * @access protected
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @access protected
     * @var string
     */
    protected $cSeriennummer;

    /**
     * @access protected
     * @var string
     */
    protected $cChargeNr;

    /**
     * @access protected
     * @var string
     */
    protected $dMHD;

    /**
     * Constructor
     *
     * @param int $kLieferscheinPosInfo
     * @access public
     */
    public function __construct($kLieferscheinPosInfo = 0)
    {
        if ((int)$kLieferscheinPosInfo > 0) {
            $this->loadFromDB($kLieferscheinPosInfo);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kLieferscheinPosInfo
     * @return $this
     * @access private
     */
    private function loadFromDB($kLieferscheinPosInfo = 0)
    {
        $oObj = Shop::DB()->select('tlieferscheinposinfo', 'kLieferscheinPosInfo', (int)$kLieferscheinPosInfo);

        if ($oObj->kLieferscheinPosInfo > 0) {
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
     * @param bool $bPrim Controls the return of the method
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

        unset($oObj->kLieferscheinPosInfo);

        $kPrim = Shop::DB()->insert('tlieferscheinposinfo', $oObj);

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
        $_upd                   = new stdClass();
        $_upd->kLieferscheinPos = $this->getLieferscheinPos();
        $_upd->cSeriennummer    = $this->getSeriennummer();
        $_upd->cChargeNr        = $this->getChargeNr();
        $_upd->dMHD             = $this->getMHD();

        return Shop::DB()->update('tlieferscheinposinfo', 'kLieferscheinPosInfo', $this->getLieferscheinPosInfo(), $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tlieferscheinposinfo', 'kLieferscheinPosInfo', $this->getLieferscheinPosInfo());
    }

    /**
     * Sets the kLieferscheinPosInfo
     *
     * @access public
     * @param int $kLieferscheinPosInfo
     * @return $this
     */
    public function setLieferscheinPosInfo($kLieferscheinPosInfo)
    {
        $this->kLieferscheinPosInfo = (int)$kLieferscheinPosInfo;

        return $this;
    }

    /**
     * Sets the kLieferscheinPos
     *
     * @access public
     * @param int $kLieferscheinPos
     * @return $this
     */
    public function setLieferscheinPos($kLieferscheinPos)
    {
        $this->kLieferscheinPos = (int)$kLieferscheinPos;

        return $this;
    }

    /**
     * Sets the cSeriennummer
     *
     * @access public
     * @param string $cSeriennummer
     * @return $this
     */
    public function setSeriennummer($cSeriennummer)
    {
        $this->cSeriennummer = Shop::DB()->escape($cSeriennummer);

        return $this;
    }

    /**
     * Sets the cChargeNr
     *
     * @access public
     * @var string
     */
    public function setChargeNr($cChargeNr)
    {
        $this->cChargeNr = Shop::DB()->escape($cChargeNr);
    }

    /**
     * Sets the dMHD
     *
     * @access public
     * @param string $dMHD
     * @return $this
     */
    public function setMHD($dMHD)
    {
        $this->dMHD = Shop::DB()->escape($dMHD);

        return $this;
    }

    /**
     * Gets the kLieferscheinPosInfo
     *
     * @access public
     * @return int
     */
    public function getLieferscheinPosInfo()
    {
        return (int)$this->kLieferscheinPosInfo;
    }

    /**
     * Gets the kLieferscheinPos
     *
     * @access public
     * @return int
     */
    public function getLieferscheinPos()
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * Gets the cSeriennummer
     *
     * @access public
     * @return string
     */
    public function getSeriennummer()
    {
        return $this->cSeriennummer;
    }

    /**
     * Gets the cChargeNr
     *
     * @access public
     * @return string
     */
    public function getChargeNr()
    {
        return $this->cChargeNr;
    }

    /**
     * Gets the dMHD
     *
     * @access public
     * @return string
     */
    public function getMHD()
    {
        return $this->dMHD;
    }
}
