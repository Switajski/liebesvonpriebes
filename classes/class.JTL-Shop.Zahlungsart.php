<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Zahlungsart
 */
class Zahlungsart extends MainModel
{
    /**
     * @var int
     */
    public $kZahlungsart;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cZusatzschrittTemplate;

    /**
     * @var string
     */
    public $cPluginTemplate;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var int
     */
    public $nMailSenden;

    /**
     * @var int
     */
    public $nActive;

    /**
     * @var string
     */
    public $cAnbieter;

    /**
     * @var string
     */
    public $cTSCode;

    /**
     * @var int
     */
    public $nWaehrendBestellung;

    /**
     * @var string
     */
    public $nCURL;

    /**
     * @var int
     */
    public $nSOAP;

    /**
     * @var int
     */
    public $nSOCKETS;

    /**
     * @var int
     */
    public $nNutzbar;

    /**
     * @var string
     */
    public $cHinweisText;

    /**
     * @var string
     */
    public $cHinweisTextShop;

    /**
     * @var string
     */
    public $cGebuehrname;

    /**
     * @var array
     */
    public $einstellungen;

    /**
     * @return int
     */
    public function getZahlungsart()
    {
        return $this->kZahlungsart;
    }

    /**
     * @param int $kZahlungsart
     * @return $this
     */
    public function setZahlungsart($kZahlungsart)
    {
        $this->kZahlungsart = (int)$kZahlungsart;

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
     * @return string
     */
    public function getModulId()
    {
        return $this->cModulId;
    }

    /**
     * @param string $cModulId
     * @return $this
     */
    public function setModulId($cModulId)
    {
        $this->cModulId = $cModulId;

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
    public function getZusatzschrittTemplate()
    {
        return $this->cZusatzschrittTemplate;
    }

    /**
     * @param string $cZusatzschrittTemplate
     * @return $this
     */
    public function setZusatzschrittTemplate($cZusatzschrittTemplate)
    {
        $this->cZusatzschrittTemplate = $cZusatzschrittTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPluginTemplate()
    {
        return $this->cPluginTemplate;
    }

    /**
     * @param string $cPluginTemplate
     * @return $this
     */
    public function setPluginTemplate($cPluginTemplate)
    {
        $this->cPluginTemplate = $cPluginTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getBild()
    {
        return $this->cBild;
    }

    /**
     * @param string $cBild
     * @return $this
     */
    public function setBild($cBild)
    {
        $this->cBild = $cBild;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->nSort;
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
     * @return int
     */
    public function getMailSenden()
    {
        return $this->nMailSenden;
    }

    /**
     * @param int $nMailSenden
     * @return $this
     */
    public function setMailSenden($nMailSenden)
    {
        $this->nMailSenden = (int)$nMailSenden;

        return $this;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->nActive;
    }

    /**
     * @param int $nActive
     * @return $this
     */
    public function setActive($nActive)
    {
        $this->nActive = (int)$nActive;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnbieter()
    {
        return $this->cAnbieter;
    }

    /**
     * @param string $cAnbieter
     * @return $this
     */
    public function setAnbieter($cAnbieter)
    {
        $this->cAnbieter = $cAnbieter;

        return $this;
    }

    /**
     * @return string
     */
    public function getTSCode()
    {
        return $this->cTSCode;
    }

    /**
     * @param $cTSCode
     * @return $this
     */
    public function setTSCode($cTSCode)
    {
        $this->cTSCode = $cTSCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getWaehrendBestellung()
    {
        return $this->nWaehrendBestellung;
    }

    /**
     * @param int $nWaehrendBestellung
     * @return $this
     */
    public function setWaehrendBestellung($nWaehrendBestellung)
    {
        $this->nWaehrendBestellung = (int)$nWaehrendBestellung;

        return $this;
    }

    /**
     * @return string
     */
    public function getCURL()
    {
        return $this->nCURL;
    }

    /**
     * @param int $nCURL
     * @return $this
     */
    public function setCURL($nCURL)
    {
        $this->nCURL = (int)$nCURL;

        return $this;
    }

    /**
     * @return int
     */
    public function getSOAP()
    {
        return $this->nSOAP;
    }

    /**
     * @param int $nSOAP
     * @return $this
     */
    public function setSOAP($nSOAP)
    {
        $this->nSOAP = (int)$nSOAP;

        return $this;
    }

    /**
     * @return int
     */
    public function getSOCKETS()
    {
        return $this->nSOCKETS;
    }

    /**
     * @param int $nSOCKETS
     * @return $this
     */
    public function setSOCKETS($nSOCKETS)
    {
        $this->nSOCKETS = (int)$nSOCKETS;

        return $this;
    }

    /**
     * @return int
     */
    public function getNutzbar()
    {
        return $this->nNutzbar;
    }

    /**
     * @param int $nNutzbar
     * @return $this
     */
    public function setNutzbar($nNutzbar)
    {
        $this->nNutzbar = (int)$nNutzbar;

        return $this;
    }

    /**
     * @return string
     */
    public function getHinweisText()
    {
        return $this->cHinweisText;
    }

    /**
     * @param string $cHinweisText
     * @return $this
     */
    public function setHinweisText($cHinweisText)
    {
        $this->cHinweisText = $cHinweisText;

        return $this;
    }

    /**
     * @return string
     */
    public function getHinweisTextShop()
    {
        return $this->cHinweisTextShop;
    }

    /**
     * @param string $cHinweisTextShop
     * @return $this
     */
    public function setHinweisTextShop($cHinweisTextShop)
    {
        $this->cHinweisTextShop = $cHinweisTextShop;

        return $this;
    }

    /**
     * @return string
     */
    public function getGebuehrname()
    {
        return $this->cGebuehrname;
    }

    /**
     * @param string $cGebuehrname
     * @return $this
     */
    public function setGebuehrname($cGebuehrname)
    {
        $this->cGebuehrname = $cGebuehrname;

        return $this;
    }

    /**
     * @param int         $kKey
     * @param null|object $oObj
     * @param null|array  $xOption
     * @return $this
     */
    public function load($kKey, $oObj = null, $xOption = null)
    {
        $kKey = (int)$kKey;
        if ($kKey > 0) {
            if ($xOption['iso'] !== null) {
                $iso = $xOption['iso'];
            } else {
                if (isset($_SESSION['cISOSprache'])) {
                    $iso = $_SESSION['cISOSprache'];
                } else {
                    $language = gibStandardsprache(true);
                    $iso      = $language->cISO;
                }
            }

            $oObj = Shop::DB()->query(
                "SELECT *
                    FROM tzahlungsart AS z
                    LEFT JOIN tzahlungsartsprache AS s ON s.kZahlungsart = z.kZahlungsart
                        AND s.cISOSprache = '{$iso}'
                    WHERE z.kZahlungsart = {$kKey}
                    LIMIT 1", 1
            );

            $this->loadObject($oObj);
        }

        return $this;
    }

    /**
     * @param bool        $active
     * @param string|null $iso
     * @return array
     */
    public static function loadAll($active = true, $iso = null)
    {
        $payments = [];
        $where    = $active ? ' WHERE z.nActive = 1' : '';

        if ($iso === null) {
            if (isset($_SESSION['cISOSprache'])) {
                $iso = $_SESSION['cISOSprache'];
            } else {
                $language = gibStandardsprache(true);
                $iso      = $language->cISO;
            }
        }

        $objs = Shop::DB()->query("
            SELECT *
                FROM tzahlungsart AS z
                LEFT JOIN tzahlungsartsprache AS s 
                    ON s.kZahlungsart = z.kZahlungsart
                    AND s.cISOSprache = '{$iso}'
                {$where}", 2
        );

        foreach ($objs as $obj) {
            $payments[] = new self(null, $obj);
        }

        return $payments;
    }
}
