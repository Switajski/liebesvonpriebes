<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Emailvorlage
 *
 * @author Daniel Böhmer JTL-Software GmbH
 */
class Emailvorlage
{
    /**
     * @access protected
     * @var int
     */
    protected $kEmailvorlage;

    /**
     * @access protected
     * @var string
     */
    protected $cName;

    /**
     * @access protected
     * @var string
     */
    protected $cBeschreibung;

    /**
     * @access protected
     * @var string
     */
    protected $cMailTyp;

    /**
     * @access protected
     * @var string
     */
    protected $cModulId;

    /**
     * @access protected
     * @var string
     */
    protected $cDateiname;

    /**
     * @access protected
     * @var string
     */
    protected $cAktiv;

    /**
     * @access protected
     * @var int
     */
    protected $nAKZ;

    /**
     * @access protected
     * @var int
     */
    protected $nAGB;

    /**
     * @access protected
     * @var int
     */
    protected $nWRB;

    /**
     * @access protected
     * @var int
     */
    protected $nWRBForm;

    /**
     * @access protected
     * @var int
     */
    protected $nFehlerhaft;

    /**
     * @var array
     */
    protected $oEinstellung_arr;

    /**
     * @var array
     */
    protected $oEinstellungAssoc_arr;

    /**
     * Constructor
     *
     * @param int  $kEmailvorlage - primary key
     * @param bool $bPlugin
     */
    public function __construct($kEmailvorlage = 0, $bPlugin = false)
    {
        if ((int)$kEmailvorlage > 0) {
            $this->loadFromDB($kEmailvorlage, $bPlugin);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int  $kEmailvorlage
     * @param bool $bPlugin
     * @return $this
     */
    private function loadFromDB($kEmailvorlage, $bPlugin)
    {
        $kEmailvorlage = (int)$kEmailvorlage;
        $cTable        = $bPlugin ? 'tpluginemailvorlage' : 'temailvorlage';
        $cTableSetting = $bPlugin ? 'tpluginemailvorlageeinstellungen' : 'temailvorlageeinstellungen';
        $oObj          = Shop::DB()->select($cTable, 'kEmailvorlage', $kEmailvorlage);

        if (isset($oObj->kEmailvorlage) && $oObj->kEmailvorlage > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
            // Settings
            $this->oEinstellung_arr = Shop::DB()->selectAll($cTableSetting, 'kEmailvorlage', $this->kEmailvorlage);
            // Assoc bauen
            if (is_array($this->oEinstellung_arr) && count($this->oEinstellung_arr) > 0) {
                $this->oEinstellungAssoc_arr = [];
                foreach ($this->oEinstellung_arr as $oEinstellung) {
                    $this->oEinstellungAssoc_arr[$oEinstellung->cKey] = $oEinstellung->cValue;
                }
            }
        }

        return $this;
    }

    /**
     * Sets the kEmailvorlage
     *
     * @var int
     * @return $this
     */
    public function setEmailvorlage($kEmailvorlage)
    {
        $this->kEmailvorlage = (int)$kEmailvorlage;

        return $this;
    }

    /**
     * Sets the cName
     *
     * @var string
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * Sets the cBeschreibung
     *
     * @var string
     * @return $this
     */
    public function setBeschreibung($cBeschreibung)
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * Sets the cMailTyp
     *
     * @var string
     * @return $this
     */
    public function setMailTyp($cMailTyp)
    {
        $this->cMailTyp = $cMailTyp;

        return $this;
    }

    /**
     * Sets the cModulId
     *
     * @var string
     * @return $this
     */
    public function setModulId($cModulId)
    {
        $this->cModulId = $cModulId;

        return $this;
    }

    /**
     * Sets the cDateiname
     *
     * @var string
     * @return $this
     */
    public function setDateiname($cDateiname)
    {
        $this->cDateiname = $cDateiname;

        return $this;
    }

    /**
     * Sets the cAktiv
     *
     * @var string
     * @return $this
     */
    public function setAktiv($cAktiv)
    {
        $this->cAktiv = $cAktiv;

        return $this;
    }

    /**
     * Sets the nAKZ
     *
     * @var int
     * @return $this
     */
    public function setAKZ($nAKZ)
    {
        $this->nAKZ = (int)$nAKZ;

        return $this;
    }

    /**
     * Sets the nAGB
     *
     * @var int
     * @return $this
     */
    public function setAGB($nAGB)
    {
        $this->nAGB = (int)$nAGB;

        return $this;
    }

    /**
     * Sets the nWRB
     *
     * @var int
     * @return $this
     */
    public function setWRB($nWRB)
    {
        $this->nWRB = (int)$nWRB;

        return $this;
    }

    /**
     * Sets the nWRBForm
     *
     * @var int
     * @return $this
     */
    public function setWRBForm($nWRBForm)
    {
        $this->nWRBForm = (int)$nWRBForm;

        return $this;
    }

    /**
     * Sets the nFehlerhaft
     *
     * @var int
     * @return $this
     */
    public function setFehlerhaft($nFehlerhaft)
    {
        $this->nFehlerhaft = (int)$nFehlerhaft;

        return $this;
    }

    /**
     * Gets the kEmailvorlage
     *
     * @access public
     * @return int
     */
    public function getEmailvorlage()
    {
        return (int)$this->kEmailvorlage;
    }

    /**
     * Gets the cName
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * Gets the cBeschreibung
     *
     * @access public
     * @return string
     */
    public function getBeschreibung()
    {
        return $this->cBeschreibung;
    }

    /**
     * Gets the cMailTyp
     *
     * @access public
     * @return string
     */
    public function getMailTyp()
    {
        return $this->cMailTyp;
    }

    /**
     * Gets the cModulId
     *
     * @access public
     * @return string
     */
    public function getModulId()
    {
        return $this->cModulId;
    }

    /**
     * Gets the cDateiname
     *
     * @return string
     */
    public function getDateiname()
    {
        return $this->cDateiname;
    }

    /**
     * Gets the cAktiv
     *
     * @return string
     */
    public function getAktiv()
    {
        return $this->cAktiv;
    }

    /**
     * Gets the nAKZ
     *
     * @return int
     */
    public function getAKZ()
    {
        return $this->nAKZ;
    }

    /**
     * Gets the nAGB
     *
     * @return int
     */
    public function getAGB()
    {
        return $this->nAGB;
    }

    /**
     * Gets the nWRB
     *
     * @return int
     */
    public function getWRB()
    {
        return $this->nWRB;
    }

    /**
     * Gets the nWRBForm
     *
     * @return int
     */
    public function getWRBForm()
    {
        return $this->nWRBForm;
    }

    /**
     * Gets the nFehlerhaft
     *
     * @return int
     */
    public function getFehlerhaft()
    {
        return $this->nFehlerhaft;
    }

    /**
     * @param string $modulId
     * @param bool   $isPlugin
     * @return Emailvorlage|null
     */
    public static function load($modulId, $isPlugin = false)
    {
        $modulId = StringHandler::filterXSS($modulId);

        $table = $isPlugin ? 'tpluginemailvorlage' : 'temailvorlage';
        $obj   = Shop::DB()->select($table, 'cModulId', Shop::DB()->escape($modulId), null, null, null, null, false, 'kEmailvorlage');

        if (is_object($obj) && isset($obj->kEmailvorlage) && (int)$obj->kEmailvorlage > 0) {
            return new self($obj->kEmailvorlage, $isPlugin);
        }

        return null;
    }
}
