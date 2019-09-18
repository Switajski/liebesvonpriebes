<?php

require_once(PFAD_ROOT.PFAD_CLASSES.'class.JTL-Shop.Hersteller.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'interface.IItemData.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Manufacturer.php');
/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */
/**
 * Description of ManufacturerDara
 *
 * @author Andre Vermeulen
 */
class ManufacturerData extends Hersteller implements IItemData
{
    private $oDB;

    private $oDebugger;

    private $oSprache_arr;

    public function __construct(JTLSearchDB $oDB, IDebugger $oDebugger, $kHersteller = 0)
    {
        try {
            $this->oDB = $oDB;
            $this->oDebugger = $oDebugger;
            
            $this->oSprache_arr = $this->oDB->getAsObject('SELECT tsprache.* FROM tsprache JOIN tjtlsearchexportlanguage ON tsprache.cISO = tjtlsearchexportlanguage.cISO ORDER BY cShopStandard DESC', 2);
            $oDefaultLanguage = $this->getDefaultLanguage();
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Es ist ein Fehler beim Laden der ManufacturerData Klasse geschehen.');
            die('Es ist ein Fehler passiet. Für weitere Infos Debugging aktivieren');
        }
        
        parent::__construct($kHersteller, $oDefaultLanguage->kSprache);
    }

    public function getCount()
    {
        $oRes = $this->oDB->getAsObject('SELECT COUNT(*) AS nAnzahl FROM thersteller', 1);
        if ($oRes !== false && $oRes->nAnzahl > 0) {
            return intval($oRes->nAnzahl);
        }
        return 0;
    }

    public static function getItemKeys(JTLSearchDB $oDB, $nLimitN, $nLimitM)
    {
        $oRes = $oDB->getAsObject('SELECT kHersteller AS kItem FROM thersteller ORDER BY kHersteller LIMIT ' .  intval($nLimitN) . ', ' . intval($nLimitM), 2);
        if ($oRes !== false && count($oRes) > 0) {
            return $oRes;
        } else {
            return array();
        }
    }

    public function loadFromDB($kItem, $kSprache = 0)
    {
        try {
            $oDefaultLanguage = $this->getDefaultLanguage();
        } catch (Exception $oEx) {
            //@todo: Logging einbauen und Fehler behandeln
            vardump($oEx);
            die();
        }
        $kSprache = $oDefaultLanguage->kSprache;

        parent::loadFromDB($kItem, $kSprache);
    }

    private function loadManufacturerLanguage($kSprache)
    {
        $oRes = $this->oDB->getAsObject("
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
                thersteller.kHersteller='{$this->kHersteller}'", 1);
        if (isset($oRes) && is_object($oRes) && count(get_object_vars($oRes)) > 0) {
            foreach (get_object_vars($oRes) as $cKey => $xValue) {
                $this->$cKey = $xValue;
            }
        } else {
            return false;
        }

        // URL bauen
        if (isset($oRes->cSeo) && strlen($oRes->cSeo) > 0) {
            $this->cURL = gibShopURL() . "/" . $oRes->cSeo;
        } else {
            $this->cURL = gibShopURL() . "/index.php?h=" . $oRes->kHersteller;
        }
        return true;
    }

    private function getDefaultLanguage()
    {
        $bLanguage = false;
        
        foreach ($this->oSprache_arr as $oSprache) {
            if ($oSprache->cShopStandard == "Y") {
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

    public function getFilledObject()
    {
        $oDefaultLanguage = $this->getDefaultLanguage();

        $oManufacturer = new Manufacturer();
        
        $oManufacturer->setId($this->kHersteller);
        $oManufacturer->setPriority(5);
        if ($this->cBildpfadKlein != 'gfx/keinBild.gif') {
            $oManufacturer->setPictureURL(URL_SHOP.'/'.$this->cBildpfadKlein);
        }

        $oManufacturer->setName($this->cName, $oDefaultLanguage->cISO);
        $oManufacturer->setDescription($this->cBeschreibung, $oDefaultLanguage->cISO);
        $oManufacturer->setKeywords($this->cMetaKeywords, $oDefaultLanguage->cISO);
        $oManufacturer->setURL($this->cURL, $oDefaultLanguage->cISO);

        foreach ($this->oSprache_arr as $oSprache) {
            if ($oSprache->cShopStandard == 'N') {
                if ($this->loadManufacturerLanguage($oSprache->kSprache)) {
                    $oManufacturer->setName($this->cName, $oSprache->cISO);
                    $oManufacturer->setDescription($this->cBeschreibung, $oSprache->cISO);
                    $oManufacturer->setKeywords($this->cMetaKeywords, $oSprache->cISO);
                    $oManufacturer->setURL($this->cURL, $oSprache->cISO);
                }
            }
        }
        return $oManufacturer;
    }
}
