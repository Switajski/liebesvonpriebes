<?php

require_once(PFAD_ROOT.PFAD_CLASSES.'class.JTL-Shop.Kategorie.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'interface.IItemData.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Category.php');
/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */
/**
 * Description of CategoryData
 *
 * @author Andre Vermeulen
 */
class CategoryData implements IItemData
{
    private $oDB;

    private $oDebugger;

    private $oSprache_arr;

    private $oKundengruppe_arr;

    private $oCategoryData;

    public function __construct(JTLSearchDB $oDB, IDebugger $oDebugger, $kKategorie = 0)
    {
        try {
            $this->oDB = $oDB;
            $this->oDebugger = $oDebugger;
            
            $this->oSprache_arr         = $this->oDB->getAsObject('SELECT tsprache.* FROM tsprache JOIN tjtlsearchexportlanguage ON tsprache.cISO = tjtlsearchexportlanguage.cISO ORDER BY cShopStandard DESC', 2);
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '. count($this->oSprache_arr) .' Sprachen geladen.');
            
            $this->oKundengruppe_arr    = $this->oDB->getAsObject("SELECT kKundengruppe FROM tkundengruppe", 2);
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '. count($this->oKundengruppe_arr) .' Kundengruppen geladen.');

            if (intval($kKategorie) > 0) {
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Direkt Kategorie laden im Konstruktor ($kKategorie = '. $kKategorie .').');
                $this->loadFromDB(intval($kKategorie));
            }
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.': Fehler beim erstellen eines CategorieData-Objekts');
        }
    }

    public function getCount()
    {
        $oRes = $this->oDB->getAsObject('SELECT COUNT(*) AS nAnzahl FROM tkategorie', 1);
        if ($oRes !== false && $oRes->nAnzahl > 0) {
            return intval($oRes->nAnzahl);
        }
        return 0;
    }

    public static function getItemKeys(JTLSearchDB $oDB, $nLimitN, $nLimitM)
    {
        $oRes = $oDB->getAsObject('SELECT kKategorie AS kItem FROM tkategorie ORDER BY kKategorie LIMIT ' .  intval($nLimitN) . ', ' . intval($nLimitM), 2);
        if ($oRes !== false && count($oRes) > 0) {
            return $oRes;
        }
    }

    public function loadFromDB($kItem)
    {
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Ladem der Kategorie '. $kItem .'.');
        unset($this->oCategoryData);
        $kItem = intval($kItem);
        if ($kItem > 0) {
            $oKategorie = $this->oDB->getAsObject("
                SELECT
                    tkategorie.*,
                    tkategoriepict.cPfad,
                    (SELECT cWert FROM tkategorieattribut WHERE cName = 'meta_keywords' AND kKategorie = {$kItem} LIMIT 0, 1) AS cKeywords
                FROM
                    tkategorie LEFT JOIN tkategoriepict ON tkategoriepict.kKategorie = tkategorie.kKategorie
                WHERE
                    tkategorie.kKategorie = {$kItem}", 1);

            if ($oKategorie === false) {
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Es ist ein Fehler beim Laden der Kategorie geschehen: Kein Datensatz zu kKategorie: '. $kItem .' vorhanden.');
                unset($this->oCategoryData);
            } else {
                $this->oCategoryData = $oKategorie;
                $this->loadCategoryLanguageFromDB();
                $this->loadCategoryVisibilityFromDB();
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Kategorie '. $kItem .' wurde geladen.');
            }
        }
    }

    private function loadCategoryLanguageFromDB()
    {
        $oKategorieSprache_arr = $this->oDB->getAsObject("
            SELECT
                *
            FROM
                tkategoriesprache
            WHERE
                kKategorie = {$this->oCategoryData->kKategorie}", 2);

        if ($oKategorieSprache_arr === false || !is_array($oKategorieSprache_arr)) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Keine weiteren Sprachen geladen.');
            $this->oCategoryData->oCategoryLanguage_arr = array();
        } else {
            foreach ($oKategorieSprache_arr as $oKategorieSprache) {
                $this->oCategoryData->oCategoryLanguage_arr[$oKategorieSprache->kSprache] = $oKategorieSprache;
            }
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '.count($this->oCategoryData->oCategoryLanguage_arr).' weitere Sprachen geladen.');
        }
    }

    private function loadCategoryVisibilityFromDB()
    {
        $oRes_arr = $this->oDB->getAsObject("SELECT kKundengruppe FROM tkategoriesichtbarkeit WHERE kKategorie = {$this->oCategoryData->kKategorie}", 2);
        foreach ($this->oKundengruppe_arr as $oKundengruppe) {
            $this->oCategoryData->bUsergroupVisible[$oKundengruppe->kKundengruppe] = true;
        }
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Kategorie für alle Benutzergruppen sichtbar.');
        if ($oRes_arr !== false && is_array($oRes_arr)) {
            foreach ($oRes_arr as $oRes) {
                $this->oCategoryData->bUsergroupVisible[$oRes->kKundengruppe] = false;
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Kategorie für Benutzergruppe '.$oRes->kKundengruppe.' nicht sichtbar.');
            }
        }
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
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Es ist ein Fehler beim Auswählen der Standartsprache geschehen: Keine Standartsprache vorhanden.');
            die('Es ist ein Fehler passiet. Für weitere Infos Debugging aktivieren');
        } else {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Es ist ein Fehler beim Auswählen der Standartsprache geschehen: Keine Sprachen vorhanden.');
            die('Es ist ein Fehler passiet. Für weitere Infos Debugging aktivieren');
        }
    }

    public function getFilledObject()
    {
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Categorieobjekt erstellen.');
        $oDefaultLanguage = $this->getDefaultLanguage();

        $oCategory = new Category();
        $oCategory->setId($this->oCategoryData->kKategorie);
        $oCategory->setMasterCategory($this->oCategoryData->kOberKategorie);
        $oCategory->setPictureURL($cPictureURL);
        $oCategory->setPriority(5);
        if (isset($this->oCategoryData->cPfad) && !empty($this->oCategoryData->cPfad)) {
            $oCategory->setPictureURL(URL_SHOP . "/" . PFAD_KATEGORIEBILDER . $this->oCategoryData->cPfad);
        }

        $oCategory->setName($this->oCategoryData->cName, $oDefaultLanguage->cISO);
        $oCategory->setDescription($this->oCategoryData->cBeschreibung, $oDefaultLanguage->cISO);
        $oCategory->setKeywords($this->oCategoryData->cKeywords, $oDefaultLanguage->cISO);
        $oCategory->setURL(URL_SHOP.'/'.baueURL($this->oCategoryData, URLART_KATEGORIE), $oDefaultLanguage->cISO);

        $_SESSION['Sprachen'] = $this->oSprache_arr;
        foreach ($this->oSprache_arr as $oSprache) {
            if ($oSprache->cShopStandard == 'N') {
                $oCategory->setName($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cName, $oSprache->cISO);
                $oCategory->setDescription($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cBeschreibung, $oSprache->cISO);
                $oCategory->setKeywords($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cKeywords, $oSprache->cISO);
                $_SESSION['kSprache'] = $oSprache->kSprache;
                $_SESSION['cISOSprache'] = $oSprache->cISO;
                $oCategory->setURL(URL_SHOP.'/'.baueURL($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache], URLART_KATEGORIE), $oSprache->cISO);
                unset($_SESSION['kSprache']);
                unset($_SESSION['cISOSprache']);
            }
        }
        unset($_SESSION['Sprachen']);

        foreach ($this->oKundengruppe_arr as $oKundengruppe) {
            $oCategory->setVisibility($this->oCategoryData->bUsergroupVisible[$oKundengruppe->kKundengruppe], $oKundengruppe->kKundengruppe);
        }
        
        return $oCategory;
    }
}
