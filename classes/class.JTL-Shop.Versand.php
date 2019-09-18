<?php

/**
 * Class Versand
 */
class Versand
{
    /**
     * @access protected
     * @var int
     */
    protected $kVersand;

    /**
     * @access protected
     * @var int
     */
    protected $kLieferschein;

    /**
     * @access protected
     * @var string
     */
    protected $cLogistik;

    /**
     * @access protected
     * @var string
     */
    protected $cLogistikURL;

    /**
     * @access protected
     * @var string
     */
    protected $cIdentCode;

    /**
     * @access protected
     * @var string
     */
    protected $cHinweis;

    /**
     * @access protected
     * @var string
     */
    protected $dErstellt;

    /**
     * @access protected
     * @var object
     */
    protected $oData;

    /**
     * Constructor
     *
     * @param int         $kVersand
     * @param null|object $oData
     * @access public
     */
    public function __construct($kVersand = 0, $oData = null)
    {
        if ((int)$kVersand > 0) {
            $this->loadFromDB($kVersand, $oData);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int         $kVersand
     * @param null|object $oData
     * @access private
     */
    private function loadFromDB($kVersand = 0, $oData = null)
    {
        $oObj = Shop::DB()->select('tversand', 'kVersand', (int)$kVersand);

        $this->oData = $oData;

        if (!empty($oObj->kVersand)) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }
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

        unset($oObj->kVersand);

        $kPrim = Shop::DB()->insert('tversand', $oObj);

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
        $_upd                = new stdClass();
        $_upd->kLieferschein = (int)$this->kLieferschein;
        $_upd->cLogistik     = $this->cLogistik;
        $_upd->cLogistikURL  = $this->cLogistikURL;
        $_upd->cIdentCode    = $this->cIdentCode;
        $_upd->cHinweis      = $this->cHinweis;
        $_upd->dErstellt     = $this->dErstellt;

        return Shop::DB()->update('tversand', 'kVersand', (int)$this->kVersand, $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tversand', 'kVersand', (int)$this->kVersand);
    }

    /**
     * Sets the kVersand
     *
     * @access public
     * @param int $kVersand
     * @return $this
     */
    public function setVersand($kVersand)
    {
        $this->kVersand = (int)$kVersand;

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
     * Sets the cLogistik
     *
     * @access public
     * @param string $cLogistik
     * @return $this
     */
    public function setLogistik($cLogistik)
    {
        $this->cLogistik = Shop::DB()->escape($cLogistik);

        return $this;
    }

    /**
     * Sets the cLogistikURL
     *
     * @access public
     * @param string $cLogistikURL
     * @return $this
     */
    public function setLogistikURL($cLogistikURL)
    {
        $this->cLogistikURL = Shop::DB()->escape($cLogistikURL);

        return $this;
    }

    /**
     * Sets the cIdentCode
     *
     * @access public
     * @param string $cIdentCode
     * @return $this
     */
    public function setIdentCode($cIdentCode)
    {
        $this->cIdentCode = Shop::DB()->escape($cIdentCode);

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
     * Gets the kVersand
     *
     * @access public
     * @return int
     */
    public function getVersand()
    {
        return $this->kVersand;
    }

    /**
     * Gets the kLieferschein
     *
     * @access public
     * @return int
     */
    public function getLieferschein()
    {
        return $this->kLieferschein;
    }

    /**
     * Gets the cLogistik
     *
     * @access public
     * @return string
     */
    public function getLogistik()
    {
        return $this->cLogistik;
    }

    /**
     * Gets the cLogistikURL
     *
     * @access public
     * @return string
     */
    public function getLogistikURL()
    {
        return $this->cLogistikURL;
    }

    /**
     * Gets the cIdentCode
     *
     * @access public
     * @return string
     */
    public function getIdentCode()
    {
        return $this->cIdentCode;
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
     * Gets the Replaced Logistic Url
     *
     * @access public
     * @return string
     */
    public function getLogistikVarUrl()
    {
        $cVarUrl = $this->cLogistikURL;

        if (isset($this->oData->cPLZ)) {
            $cVarUrl = str_replace(
                ['#PLZ#', '#IdentCode#'],
                [$this->oData->cPLZ, $this->cIdentCode],
                $this->cLogistikURL
            );
        }

        return $cVarUrl;
    }
}
