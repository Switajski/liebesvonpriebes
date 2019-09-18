<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Nummern
 */
class Nummern
{
    /**
     * @access protected
     * @var int
     */
    protected $nNummer;

    /**
     * @access protected
     * @var int
     */
    protected $nArt;

    /**
     * @access protected
     * @var string
     */
    protected $dAktualisiert;

    /**
     * Constructor
     *
     * @param int $nArt
     * @access public
     */
    public function __construct($nArt = 0)
    {
        if ((int)$nArt > 0) {
            $this->loadFromDB($nArt);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $nArt
     * @return $this
     * @access private
     */
    private function loadFromDB($nArt = 0)
    {
        $oObj = Shop::DB()->select('tnummern', 'nArt', (int)$nArt);
        if (isset($oObj->nArt) && $oObj->nArt > 0) {
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
        $kPrim = Shop::DB()->insert('tnummern', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @access public
     * @param bool $bDate
     * @return int
     */
    public function update($bDate = true)
    {
        if ($bDate) {
            $this->setAktualisiert('now()');
        }
        $_upd                = new stdClass();
        $_upd->nNummer       = $this->nNummer;
        $_upd->dAktualisiert = $this->dAktualisiert;

        return Shop::DB()->update('tnummern', 'nArt', $this->nArt, $_upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tnummern', 'nArt', $this->nArt);
    }

    /**
     * Sets the nNummer
     *
     * @access public
     * @param int $nNummer
     * @return $this
     */
    public function setNummer($nNummer)
    {
        $this->nNummer = (int)$nNummer;

        return $this;
    }

    /**
     * Sets the nArt
     *
     * @access public
     * @param int $nArt
     * @return $this
     */
    public function setArt($nArt)
    {
        $this->nArt = (int)$nArt;

        return $this;
    }

    /**
     * Sets the dAktualisiert
     *
     * @access public
     * @param string $dAktualisiert
     * @return $this
     */
    public function setAktualisiert($dAktualisiert)
    {
        $this->dAktualisiert = $dAktualisiert === 'now()'
            ? date('Y-m-d H:i:s')
            : Shop::DB()->escape($dAktualisiert);

        return $this;
    }

    /**
     * Gets the nNummer
     *
     * @access public
     * @return int
     */
    public function getNummer()
    {
        return $this->nNummer;
    }

    /**
     * Gets the nArt
     *
     * @access public
     * @return int
     */
    public function getArt()
    {
        return $this->nArt;
    }

    /**
     * Gets the dAktualisiert
     *
     * @access public
     * @return string
     */
    public function getAktualisiert()
    {
        return $this->dAktualisiert;
    }
}
