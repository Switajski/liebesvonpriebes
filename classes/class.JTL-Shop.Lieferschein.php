<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferschein
 */
class Lieferschein
{
    /**
     * @access protected
     * @var int
     */
    protected $kLieferschein;

    /**
     * @access protected
     * @var int
     */
    protected $kInetBestellung;

    /**
     * @access protected
     * @var string
     */
    protected $cLieferscheinNr;

    /**
     * @access protected
     * @var string
     */
    protected $cHinweis;

    /**
     * @access protected
     * @var int
     */
    protected $nFulfillment;

    /**
     * @access protected
     * @var int
     */
    protected $nStatus;

    /**
     * @access protected
     * @var string
     */
    protected $dErstellt;

    /**
     * @access protected
     * @var bool
     */
    protected $bEmailVerschickt;

    /**
     * @access protected
     * @var array
     */
    public $oLieferscheinPos_arr = [];

    /**
     * @access protected
     * @var array
     */
    public $oVersand_arr = [];

    /**
     * @var array
     */
    public $oPosition_arr = [];

    /**
     * Constructor
     *
     * @param int    $kLieferschein
     * @param object $oData
     * @access public
     */
    public function __construct($kLieferschein = 0, $oData = null)
    {
        if ((int)$kLieferschein > 0) {
            $this->loadFromDB($kLieferschein, $oData);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int    $kLieferschein primary key
     * @param object $oData
     * @return $this
     * @access private
     */
    private function loadFromDB($kLieferschein = 0, $oData = null)
    {
        $kLieferschein = (int)$kLieferschein;
        $oObj          = Shop::DB()->select('tlieferschein', 'kLieferschein', $kLieferschein);
        if ($oObj->kLieferschein > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $setter = 'set' . substr($cMember, 1);
                if (is_callable([$this, $setter])) {
                    $this->$setter($oObj->$cMember);
                } else {
                    $this->$cMember = $oObj->$cMember;
                }
            }

            $kLieferscheinPos_arr = Shop::DB()->selectAll('tlieferscheinpos', 'kLieferschein', $kLieferschein, 'kLieferscheinPos');

            foreach ($kLieferscheinPos_arr as $oLieferscheinPos) {
                $oLieferscheinpos                           = new Lieferscheinpos($oLieferscheinPos->kLieferscheinPos);
                $oLieferscheinpos->oLieferscheinPosInfo_arr = [];

                $kLieferscheinPosInfo_arr = Shop::DB()->selectAll(
                    'tlieferscheinposinfo',
                    'kLieferscheinPos',
                    (int)$oLieferscheinPos->kLieferscheinPos,
                    'kLieferscheinPosInfo'
                );
                if (is_array($kLieferscheinPosInfo_arr) && !empty($kLieferscheinPosInfo_arr)) {
                    foreach ($kLieferscheinPosInfo_arr as $oLieferscheinPosInfo) {
                        $oLieferscheinpos->oLieferscheinPosInfo_arr[] = new Lieferscheinposinfo($oLieferscheinPosInfo->kLieferscheinPosInfo);
                    }
                }

                $this->oLieferscheinPos_arr[] = $oLieferscheinpos;
            }

            $kVersand_arr = Shop::DB()->selectAll('tversand', 'kLieferschein', $kLieferschein, 'kVersand');

            foreach ($kVersand_arr as $oVersand) {
                $this->oVersand_arr[] = new Versand($oVersand->kVersand, $oData);
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
        $oObj                   = new stdClass();
        $oObj->kInetBestellung  = $this->kInetBestellung;
        $oObj->cLieferscheinNr  = $this->cLieferscheinNr;
        $oObj->cHinweis         = $this->cHinweis;
        $oObj->nFulfillment     = $this->nFulfillment;
        $oObj->nStatus          = $this->nStatus;
        $oObj->dErstellt        = $this->dErstellt;
        $oObj->bEmailVerschickt = $this->bEmailVerschickt ? 1 : 0;
        $kPrim                  = Shop::DB()->insert('tlieferschein', $oObj);
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
        $upd                   = new stdClass();
        $upd->kInetBestellung  = $this->kInetBestellung;
        $upd->cLieferscheinNr  = $this->cLieferscheinNr;
        $upd->cHinweis         = $this->cHinweis;
        $upd->nFulfillment     = $this->nFulfillment;
        $upd->nStatus          = $this->nStatus;
        $upd->dErstellt        = $this->dErstellt;
        $upd->bEmailVerschickt = $this->bEmailVerschickt ? 1 : 0;

        return Shop::DB()->update('tlieferschein', 'kLieferschein', (int)$this->kLieferschein, $upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tlieferschein', 'kLieferschein', (int)$this->getLieferschein());
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
     * Sets the kInetBestellung
     *
     * @access public
     * @param int $kInetBestellung
     * @return $this
     */
    public function setInetBestellung($kInetBestellung)
    {
        $this->kInetBestellung = (int)$kInetBestellung;

        return $this;
    }

    /**
     * Sets the cLieferscheinNr
     *
     * @access public
     * @param string $cLieferscheinNr
     * @return $this
     */
    public function setLieferscheinNr($cLieferscheinNr)
    {
        $this->cLieferscheinNr = Shop::DB()->escape($cLieferscheinNr);

        return $this;
    }

    /**
     * Sets the cHinweis
     *
     * @access public
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis)
    {
        $this->cHinweis = Shop::DB()->escape($cHinweis);

        return $this;
    }

    /**
     * Sets the nFulfillment
     *
     * @access public
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment($nFulfillment)
    {
        $this->nFulfillment = (int)$nFulfillment;

        return $this;
    }

    /**
     * Sets the nStatus
     *
     * @access public
     * @param int $nStatus
     * @return $this
     */
    public function setStatus($nStatus)
    {
        $this->nStatus = (int)$nStatus;

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
     * Sets the bEmaiLVerschickt
     *
     * @access public
     * @param bool $bEmailVerschickt
     * @return $this
     */
    public function setEmailVerschickt($bEmailVerschickt)
    {
        $this->bEmailVerschickt = (bool)$bEmailVerschickt;

        return $this;
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
     * Gets the kInetBestellung
     *
     * @access public
     * @return int
     */
    public function getInetBestellung()
    {
        return $this->kInetBestellung;
    }

    /**
     * Gets the cLieferscheinNr
     *
     * @access public
     * @return string
     */
    public function getLieferscheinNr()
    {
        return $this->cLieferscheinNr;
    }

    /**
     * Gets the cHinweis
     *
     * @access public
     * @return string
     */
    public function getHinweis()
    {
        return $this->cHinweis;
    }

    /**
     * Gets the nFulfillment
     *
     * @access public
     * @return int
     */
    public function getFulfillment()
    {
        return $this->nFulfillment;
    }

    /**
     * Gets the nStatus
     *
     * @access public
     * @return int
     */
    public function getStatus()
    {
        return $this->nStatus;
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
     * Gets the bEmailVerschickt
     *
     * @access public
     * @return bool
     */
    public function getEmailVerschickt()
    {
        return $this->bEmailVerschickt;
    }
}
