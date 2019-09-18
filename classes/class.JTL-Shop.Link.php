<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Link
 */
class Link extends MainModel
{
    /**
     * @var int
     */
    public $kLink;

    /**
     * @var int
     */
    public $kVaterLink;

    /**
     * @var int
     */
    public $kLinkgruppe;

    /**
     * @var int
     */
    public $kPlugin;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var int
     */
    public $nLinkart;

    /**
     * @var string
     */
    public $cNoFollow;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cSichtbarNachLogin;

    /**
     * deprecated
     *
     * @var string
     */
    public $cDruckButton;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var int
     */
    public $bSSL = 0;

    /**
     * @var int
     */
    public $bIsFluid = 0;

    /**
     * @var string
     */
    public $cIdentifier = '';

    /**
     * @var int
     */
    public $bIsActive = 1;

    /**
     * @var array
     */
    public $oSub_arr = [];

    /**
     * @var string
     */
    public $cISO;

    /**
     * @var int
     */
    public $kSprache = 0;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var int
     */
    public $nHTTPRedirectCode = 0;

    /**
     * @var bool
     */
    public $bHideContent = false;

    /**
     * @var int
     */
    public $nPluginStatus = 0;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cURLFullSSL;

    /**
     * @var int
     */
    public $kSpezialSeite = 0;

    /**
     * @param int $kSpezialSeite
     * @return $this
     */
    public function setSpezialSeite($kSpezialSeite)
    {
        $this->kSpezialSeite = (int)$kSpezialSeite;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpezialSeite()
    {
        return (int)$this->kSpezialSeite;
    }

    /**
     * @return string
     */
    public function getURLFullSSL()
    {
        return $this->cURLFullSSL;
    }

    /**
     * @param string $cURLFullSSL
     * @return $this
     */
    public function setURLFullSSL($cURLFullSSL)
    {
        $this->cURLFullSSL = $cURLFullSSL;

        return $this;
    }

    /**
     * @return string
     */
    public function getURLFull()
    {
        return $this->cURLFull;
    }

    /**
     * @param string $cURLFull
     * @return $this
     */
    public function setURLFull($cURLFull)
    {
        $this->cURLFull = $cURLFull;

        return $this;
    }

    /**
     * @param int $nPluginStatus
     * @return $this
     */
    public function setPluginStatus($nPluginStatus)
    {
        $this->nPluginStatus = (int)$nPluginStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getPluginStatus()
    {
        return (int)$this->nPluginStatus;
    }

    /**
     * @param string $cISO
     * @return $this
     */
    public function setISO($cISO)
    {
        $this->cISO = $cISO;

        return $this;
    }

    /**
     * @return string
     */
    public function getISO()
    {
        return $this->cISO;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache($kSprache)
    {
        $this->kSprache = (int)$kSprache;

        return $this;
    }

    /**
     * @return int
     */
    public function getSprache()
    {
        return (int)$this->kSprache;
    }

    /**
     * @param string $cSeo
     * @return $this
     */
    public function setSeo($cSeo)
    {
        $this->cSeo = $cSeo;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeo()
    {
        return $this->cSeo;
    }

    /**
     * @return int
     */
    public function getLink()
    {
        return (int)$this->kLink;
    }

    /**
     * @param int $kLink
     * @return $this
     */
    public function setLink($kLink)
    {
        $this->kLink = (int)$kLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getVaterLink()
    {
        return (int)$this->kVaterLink;
    }

    /**
     * @param int $kVaterLink
     * @return $this
     */
    public function setVaterLink($kVaterLink)
    {
        $this->kVaterLink = (int)$kVaterLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkgruppe()
    {
        return (int)$this->kLinkgruppe;
    }

    /**
     * @param int $kLinkgruppe
     * @return $this
     */
    public function setLinkgruppe($kLinkgruppe)
    {
        $this->kLinkgruppe = (int)$kLinkgruppe;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlugin()
    {
        return (int)$this->kPlugin;
    }

    /**
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin($kPlugin)
    {
        $this->kPlugin = (int)$kPlugin;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkart()
    {
        return (int)$this->nLinkart;
    }

    /**
     * @param int $nLinkart
     * @return $this
     */
    public function setLinkart($nLinkart)
    {
        $this->nLinkart = (int)$nLinkart;

        return $this;
    }

    /**
     * @return string
     */
    public function getNoFollow()
    {
        return $this->cNoFollow;
    }

    /**
     * @param string $cNoFollow
     * @return $this
     */
    public function setNoFollow($cNoFollow)
    {
        $this->cNoFollow = $cNoFollow;

        return $this;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->cURL;
    }

    /**
     * @param string $cURL
     * @return $this
     */
    public function setURL($cURL)
    {
        $this->cURL = $cURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getKundengruppen()
    {
        return $this->cKundengruppen;
    }

    /**
     * @param string $cKundengruppen
     * @return $this
     */
    public function setKundengruppen($cKundengruppen)
    {
        $this->cKundengruppen = $cKundengruppen;

        return $this;
    }

    /**
     * @return string
     */
    public function getSichtbarNachLogin()
    {
        return $this->cSichtbarNachLogin;
    }

    /**
     * @param string $cSichtbarNachLogin
     * @return $this
     */
    public function setSichtbarNachLogin($cSichtbarNachLogin)
    {
        $this->cSichtbarNachLogin = $cSichtbarNachLogin;

        return $this;
    }

    /**
     * deprecated
     *
     * @return string
     */
    public function getDruckButton()
    {
        return $this->cDruckButton;
    }

    /**
     * deprecated
     *
     * @param string $cDruckButton
     * @return $this
     */
    public function setDruckButton($cDruckButton)
    {
        $this->cDruckButton = $cDruckButton;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return (int)$this->nSort;
    }

    /**
     * @param int $nSort
     * @return $this
     */
    public function setSort($nSort)
    {
        $this->nSort = (int)$nSort;

        return $this;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setSSL($mode)
    {
        $this->bSSL = (int)$mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSSL()
    {
        return (int)$this->bSSL;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setIsFluid($mode)
    {
        $this->bIsFluid = (int)$mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsFluid()
    {
        return (int)$this->bIsFluid;
    }

    /**
     * @param string $ident
     * @return $this
     */
    public function setIdentifier($ident)
    {
        $this->cIdentifier = $ident;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->cIdentifier;
    }

    /**
     * @param null|int    $kKey
     * @param null|object $oObj
     * @param null|mixed  $xOption
     * @param null|int   $kLinkgruppe
     */
    public function __construct($kKey = null, $oObj = null, $xOption = null, $kLinkgruppe = null)
    {
        if (is_object($oObj)) {
            $this->loadObject($oObj);
        } elseif ($kKey !== null) {
            $this->load($kKey, $oObj, $xOption, $kLinkgruppe);
        }
    }

    /**
     * @param int         $kKey
     * @param object|null $oObj
     * @param mixed|null  $xOption
     * @param int         $kLinkgruppe
     * @return $this
     */
    public function load($kKey, $oObj = null, $xOption = null, $kLinkgruppe = null)
    {
        if (!empty($kLinkgruppe)) {
            $oObj = Shop::DB()->select('tlink', ['kLink', 'kLinkgruppe'], [(int)$kKey, (int)$kLinkgruppe]);
        } else {
            $oObj = Shop::DB()->select('tlink', 'kLink', (int)$kKey);
        }
        if (!empty($oObj->kLink)) {
            $this->loadObject($oObj);

            if ($xOption) {
                $this->oSub_arr = self::getSub($this->getLink(), $this->getLinkgruppe());
            }
        }

        return $this;
    }

    /**
     * @param int $kVaterLink
     * @param int $kVaterLinkgruppe
     * @return null|array
     */
    public static function getSub($kVaterLink, $kVaterLinkgruppe = null)
    {
        $kVaterLink       = (int)$kVaterLink;
        $kVaterLinkgruppe = (int)$kVaterLinkgruppe;
        if ($kVaterLink > 0) {
            if (!empty($kVaterLinkgruppe)) {
                $oLink_arr = Shop::DB()->selectAll('tlink', ['kVaterLink', 'kLinkgruppe'],
                    [$kVaterLink, $kVaterLinkgruppe]);
            } else {
                $oLink_arr = Shop::DB()->selectAll('tlink', 'kVaterLink', $kVaterLink);
            }

            if (is_array($oLink_arr) && count($oLink_arr) > 0) {
                foreach ($oLink_arr as &$oLink) {
                    $oLink = new self($oLink->kLink, null, true, $kVaterLinkgruppe);
                }
            }

            return $oLink_arr;
        }

        return null;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save($bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            $oObj->kLink              = $this->kLink;
            $oObj->kVaterLink         = $this->kVaterLink;
            $oObj->kLinkgruppe        = $this->kLinkgruppe;
            $oObj->kPlugin            = $this->kPlugin;
            $oObj->cName              = $this->cName;
            $oObj->nLinkart           = $this->nLinkart;
            $oObj->cNoFollow          = $this->cNoFollow;
            $oObj->cURL               = $this->cURL;
            $oObj->cKundengruppen     = $this->cKundengruppen;
            $oObj->bIsActive          = $this->bIsActive;
            $oObj->cSichtbarNachLogin = $this->cSichtbarNachLogin;
            $oObj->cDruckButton       = $this->cDruckButton;
            $oObj->nSort              = $this->nSort;
            $oObj->bSSL               = $this->bSSL;
            $oObj->bIsFluid           = $this->bIsFluid;
            $oObj->cIdentifier        = $this->cIdentifier;
        }

        $kPrim = Shop::DB()->insert('tlink', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @throws Exception
     * @return int
     */
    public function update()
    {
        $cQuery   = "UPDATE tlink SET ";
        $cSet_arr = [];

        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $mValue = "'" . Shop::DB()->realEscape(
                            call_user_func(
                                [
                                    &$this,
                                    $cMethod
                                ]
                            )
                        ) . "'";
                    if (call_user_func(
                            [
                                &$this,
                                $cMethod
                            ]
                        ) === null
                    ) {
                        $mValue = 'NULL';
                    }
                    $cSet_arr[] = "{$cMember} = {$mValue}";
                }
            }

            $cQuery .= implode(', ', $cSet_arr);
            $cQuery .= " WHERE kLink = {$this->getLink()} AND klinkgruppe = {$this->getLinkgruppe()}";

            return Shop::DB()->query($cQuery, 3);
        } else {
            throw new Exception("ERROR: Object has no members!");
        }
    }

    /**
     * @param bool $bSub
     * @param int  $kLinkgruppe
     * @return int
     */
    public function delete($bSub = true, $kLinkgruppe = null)
    {
        $nRows = 0;
        if ($this->kLink > 0) {
            if (!empty($kLinkgruppe)) {
                $nRows = Shop::DB()->delete('tlink', ['kLink', 'kLinkgruppe'], [$this->getLink(), $kLinkgruppe]);
            } else {
                $nRows = Shop::DB()->delete('tlink', 'kLink', $this->getLink());
            }
            $nLinkAnz = Shop::DB()->selectAll('tlink', 'kLink', $this->getLink());
            if (count($nLinkAnz) === 0) {
                Shop::DB()->delete('tlinksprache', 'kLink', $this->getLink());
                Shop::DB()->delete('tseo', ['kKey', 'cKey'], [$this->getLink(), 'kLink']);

                $cDir = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER . $this->getLink();
                if (is_dir($cDir) && $this->getLink() > 0 && delDirRecursively($cDir)) {
                    rmdir($cDir);
                }
            }

            if ($bSub && count($this->oSub_arr) > 0) {
                foreach ($this->oSub_arr as $oSub) {
                    $oSub->delete(true, $kLinkgruppe);
                }
            }
        }

        return $nRows;
    }
}
