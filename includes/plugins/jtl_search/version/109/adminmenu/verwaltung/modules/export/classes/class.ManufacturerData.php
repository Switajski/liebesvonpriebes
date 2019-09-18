<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Hersteller.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IItemData.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Manufacturer.php';

/**
 * Description of ManufacturerDara
 *
 * @author Andre Vermeulen
 */
class ManufacturerData extends Hersteller implements IItemData
{
    /**
     * @var IDebugger
     */
    private $oDebugger;

    /**
     * @var array|MySQL|null|object|stdClass
     */
    private $oSprache_arr;

    /**
     * @param IDebugger $oDebugger
     * @param int       $kHersteller
     */
    public function __construct(IDebugger $oDebugger, $kHersteller = 0)
    {
        try {
            $this->oDebugger    = $oDebugger;
            $this->oSprache_arr = Shop::DB()->query('SELECT tsprache.* FROM tsprache JOIN tjtlsearchexportlanguage ON tsprache.cISO = tjtlsearchexportlanguage.cISO ORDER BY cShopStandard DESC', 2);
            $oDefaultLanguage   = $this->getDefaultLanguage();
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Es ist ein Fehler beim Laden der ManufacturerData Klasse geschehen.');
            die('JTL-Search Fehler beim Datenexport: Klasse Manufacturer konnte nicht initialisiert werden.');
        }

        parent::__construct($kHersteller, $oDefaultLanguage->kSprache);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $oRes = Shop::DB()->query('SELECT COUNT(*) AS nAnzahl FROM thersteller', 1);
        if ($oRes !== false && $oRes->nAnzahl > 0) {
            return intval($oRes->nAnzahl);
        }

        return 0;
    }

    /**
     * @param             $nLimitN
     * @param             $nLimitM
     * @return array|MySQL|null|object|stdClass
     */
    public static function getItemKeys($nLimitN, $nLimitM)
    {
        $oRes = Shop::DB()->query('SELECT kHersteller AS kItem FROM thersteller ORDER BY kHersteller LIMIT ' . intval($nLimitN) . ', ' . intval($nLimitM), 2);
        if ($oRes !== false && count($oRes) > 0) {
            return $oRes;
        }

        return array();
    }

    /**
     * @param int  $kItem
     * @param int  $kSprache
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB($kItem, $kSprache = 0, $noCache = true)
    {
        try {
            $oDefaultLanguage = $this->getDefaultLanguage();
        } catch (Exception $oEx) {
            //@todo: Logging einbauen und Fehler behandeln
            var_dump($oEx);
            die();
        }
        $kSprache = $oDefaultLanguage->kSprache;

        return parent::loadFromDB($kItem, $kSprache, $noCache);
    }

    /**
     * @param $kSprache
     * @return bool
     */
    private function loadManufacturerLanguage($kSprache)
    {
        $oRes = Shop::DB()->query(
            "
            SELECT
                therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                tseo.cSeo
            FROM
                thersteller
                LEFT JOIN
                    therstellersprache ON therstellersprache.kHersteller=thersteller.kHersteller
                    AND therstellersprache.kSprache=" . intval($kSprache) . "
                LEFT JOIN
                    tseo ON tseo.kKey = thersteller.kHersteller
                    AND tseo.cKey = 'kHersteller'
                    AND tseo.kSprache = " . intval($kSprache) . "
            WHERE
                thersteller.kHersteller='{$this->kHersteller}'", 1
        );
        if (isset($oRes) && is_object($oRes) && count(get_object_vars($oRes)) > 0) {
            foreach (get_object_vars($oRes) as $cKey => $xValue) {
                $this->$cKey = $xValue;
            }
        } else {
            return false;
        }

        // URL bauen
        if (isset($oRes->cSeo) && strlen($oRes->cSeo) > 0) {
            $this->cURL = Shop::getURL() . '/' . $oRes->cSeo;
        } else {
            $this->cURL = Shop::getURL() . '/index.php?h=' . $oRes->kHersteller;
        }

        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function getDefaultLanguage()
    {
        $bLanguage = false;

        foreach ($this->oSprache_arr as $oSprache) {
            if ($oSprache->cShopStandard === 'Y') {
                return $oSprache;
            }
            $bLanguage = true;
        }

        if ($bLanguage) {
            throw new Exception('Es ist ein Fehler beim Auswählen der Standartsprache geschehen: Keine Standartsprache vorhanden.', 1);
        } else {
            throw new Exception('Es ist ein Fehler beim Auswählen der Standartsprache geschehen: Keine Sprachen vorhanden.', 2);
        }
    }

    /**
     * @return Manufacturer
     * @throws Exception
     */
    public function getFilledObject()
    {
        $oDefaultLanguage = $this->getDefaultLanguage();

        $oManufacturer = new Manufacturer();

        $oManufacturer->setId($this->kHersteller)
                      ->setPriority(5);
        if ($this->cBildpfadKlein !== BILD_KEIN_ARTIKELBILD_VORHANDEN) {
            $oManufacturer->setPictureURL(Shop::getURL() . '/' . $this->cBildpfadKlein);
        }

        $oManufacturer->setName($this->cName, $oDefaultLanguage->cISO)
                      ->setDescription($this->cBeschreibung, $oDefaultLanguage->cISO)
                      ->setKeywords($this->cMetaKeywords, $oDefaultLanguage->cISO)
                      ->setURL($this->cURL, $oDefaultLanguage->cISO);

        foreach ($this->oSprache_arr as $oSprache) {
            if ($oSprache->cShopStandard === 'N') {
                if ($this->loadManufacturerLanguage($oSprache->kSprache)) {
                    $oManufacturer->setName($this->cName, $oSprache->cISO)
                                  ->setDescription($this->cBeschreibung, $oSprache->cISO)
                                  ->setKeywords($this->cMetaKeywords, $oSprache->cISO)
                                  ->setURL($this->cURL, $oSprache->cISO);
                }
            }
        }

        return $oManufacturer;
    }
}
