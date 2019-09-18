<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferscheinpos
 */
class Lieferscheinpos
{
    /**
     * @access protected
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @access protected
     * @var int
     */
    protected $kLieferschein;

    /**
     * @access protected
     * @var int
     */
    protected $kBestellPos;

    /**
     * @access protected
     * @var int
     */
    protected $kWarenlager;

    /**
     * @access protected
     * @var float
     */
    protected $fAnzahl;

    /**
     * @var array
     */
    public $oLieferscheinPosInfo_arr;

    /**
     * Constructor
     *
     * @param int $kLieferscheinPos primarykey
     * @access public
     */
    public function __construct($kLieferscheinPos = 0)
    {
        if ((int)$kLieferscheinPos > 0) {
            $this->loadFromDB($kLieferscheinPos);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kLieferscheinPos
     * @return $this
     * @access private
     */
    private function loadFromDB($kLieferscheinPos = 0)
    {
        $oObj = Shop::DB()->select('tlieferscheinpos', 'kLieferscheinPos', (int)$kLieferscheinPos);
        if (!empty($oObj->kLieferscheinPos)) {
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
     * @access public
     * @param bool $bPrim - Controls the return of the method
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

        unset($oObj->kLieferscheinPos, $oObj->oLieferscheinPosInfo_arr);
        $kPrim = Shop::DB()->insert('tlieferscheinpos', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @access public
     * @return int
     */
    public function update()
    {
        $_upd                = new stdClass();
        $_upd->kLieferschein = $this->getLieferschein();
        $_upd->kBestellPos   = $this->getBestellPos();
        $_upd->kWarenlager   = $this->getWarenlager();
        $_upd->fAnzahl       = $this->getAnzahl();

        return Shop::DB()->update('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos(), $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @access public
     * @return int
     */
    public function delete()
    {
        return Shop::DB()->delete('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos());
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
     * Sets the kLieferschein
     *
     * @access public
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein($kLieferschein)
    {
        $this->kLieferschein = (int)$kLieferschein;

        return $this;
    }

    /**
     * Sets the kBestellPos
     *
     * @access public
     * @param int $kBestellPos
     * @return $this
     */
    public function setBestellPos($kBestellPos)
    {
        $this->kBestellPos = (int)$kBestellPos;

        return $this;
    }

    /**
     * Sets the kWarenlager
     *
     * @access public
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager($kWarenlager)
    {
        $this->kWarenlager = (int)$kWarenlager;

        return $this;
    }

    /**
     * Sets the fAnzahl
     *
     * @access public
     * @param float $fAnzahl
     * @return $this
     */
    public function setAnzahl($fAnzahl)
    {
        $this->fAnzahl = (float)$fAnzahl;

        return $this;
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
     * Gets the kLieferschein
     *
     * @access public
     * @return int
     */
    public function getLieferschein()
    {
        return (int)$this->kLieferschein;
    }

    /**
     * Gets the kBestellPos
     *
     * @access public
     * @return int
     */
    public function getBestellPos()
    {
        return (int)$this->kBestellPos;
    }

    /**
     * Gets the kWarenlager
     *
     * @access public
     * @return int
     */
    public function getWarenlager()
    {
        return (int)$this->kWarenlager;
    }

    /**
     * Gets the fAnzahl
     *
     * @access public
     * @return float
     */
    public function getAnzahl()
    {
        return $this->fAnzahl;
    }
}
