<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kategorie.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IItemData.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Category.php';

/**
 * @author Andre Vermeulen
 */
class CategoryData implements IItemData
{
    /**
     * @var IDebugger
     */
    private $oDebugger;

    /**
     * @var array
     */
    private $oSprache_arr;

    /**
     * @var array
     */
    private $oKundengruppe_arr;
    /**
     * @var
     */
    private $oCategoryData;

    /**
     * @param IDebugger $oDebugger
     * @param int       $kKategorie
     */
    public function __construct(IDebugger $oDebugger, $kKategorie = 0)
    {
        try {
            $this->oDebugger = $oDebugger;

            $this->oSprache_arr = Shop::DB()->query('
                SELECT tsprache.* 
                    FROM tsprache 
                    JOIN tjtlsearchexportlanguage 
                        ON tsprache.cISO = tjtlsearchexportlanguage.cISO 
                    ORDER BY cShopStandard DESC', 2
            );
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' .
                count($this->oSprache_arr) . ' Sprachen geladen.');

            $this->oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe", 2);
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' .
                count($this->oKundengruppe_arr) . ' Kundengruppen geladen.');

            if (intval($kKategorie) > 0) {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                    '; Direkt Kategorie laden im Konstruktor ($kKategorie = ' . $kKategorie . ').');
                $this->loadFromDB(intval($kKategorie));
            }
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                ': Fehler beim erstellen eines CategorieData-Objekts');
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $oRes = Shop::DB()->query('SELECT COUNT(*) AS nAnzahl FROM tkategorie', 1);
        if ($oRes !== false && $oRes->nAnzahl > 0) {
            return intval($oRes->nAnzahl);
        }

        return 0;
    }

    /**
     * @param int $nLimitN
     * @param int $nLimitM
     * @return array|MySQL|null|object|stdClass
     */
    public static function getItemKeys($nLimitN, $nLimitM)
    {
        $oRes = Shop::DB()->query('
            SELECT kKategorie AS kItem 
                FROM tkategorie 
                ORDER BY kKategorie 
                LIMIT ' . (int)$nLimitN . ', ' . (int)$nLimitM, 2
        );
        if ($oRes !== false && count($oRes) > 0) {
            return $oRes;
        }

        return [];
    }

    /**
     * @param $kItem
     * @return $this
     */
    public function loadFromDB($kItem)
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
            '; Laden der Kategorie ' . $kItem . '.');
        unset($this->oCategoryData);
        $kItem = (int)$kItem;
        if ($kItem > 0) {
            $oKategorie = Shop::DB()->query("
                SELECT tkategorie.*, tkategoriepict.cPfad,
                    (SELECT cWert 
                        FROM tkategorieattribut 
                        WHERE cName = 'meta_keywords' 
                            AND kKategorie = {$kItem} 
                        LIMIT 0, 1) AS cKeywords
                    FROM tkategorie 
                    LEFT JOIN tkategoriepict 
                        ON tkategoriepict.kKategorie = tkategorie.kKategorie
                    WHERE tkategorie.kKategorie = {$kItem}", 1
            );

            if ($oKategorie === false && !is_object($oKategorie)) {
                $this->oDebugger->doDebug(
                    __FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                    '; Es ist ein Fehler beim Laden der Kategorie aufgetreten: Kein Datensatz zu kKategorie: ' .
                    $kItem . ' vorhanden.'
                );
                unset($this->oCategoryData);
            } else {
                $this->oCategoryData = $oKategorie;
                $this->loadCategoryLanguageFromDB()
                     ->loadCategoryVisibilityFromDB();
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                    '; Kategorie ' . $kItem . ' wurde geladen.');
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadCategoryLanguageFromDB()
    {
        $oKategorieSprache_arr = false;
        if (isset($this->oCategoryData->kKategorie)) {
            $oKategorieSprache_arr = Shop::DB()->selectAll(
                'tkategoriesprache',
                'kKategorie',
                $this->oCategoryData->kKategorie
            );
        }

        if ($oKategorieSprache_arr === false || !is_array($oKategorieSprache_arr)) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Keine weiteren Sprachen geladen.');
            $this->oCategoryData->oCategoryLanguage_arr = [];
        } else {
            $this->oCategoryData->oCategoryLanguage_arr = [];
            foreach ($oKategorieSprache_arr as $oKategorieSprache) {
                $this->oCategoryData->oCategoryLanguage_arr[$oKategorieSprache->kSprache] = $oKategorieSprache;
            }
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' .
                count($this->oCategoryData->oCategoryLanguage_arr) . ' weitere Sprachen geladen.');
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadCategoryVisibilityFromDB()
    {
        if (isset($this->oCategoryData->kKategorie)) {
            $oRes_arr = Shop::DB()->selectAll(
                'tkategoriesichtbarkeit',
                'kKategorie',
                $this->oCategoryData->kKategorie
            );
            foreach ($this->oKundengruppe_arr as $oKundengruppe) {
                $this->oCategoryData->bUsergroupVisible[$oKundengruppe->kKundengruppe] = true;
            }
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Kategorie für alle Benutzergruppen sichtbar.');
            if ($oRes_arr !== false && is_array($oRes_arr)) {
                foreach ($oRes_arr as $oRes) {
                    $this->oCategoryData->bUsergroupVisible[$oRes->kKundengruppe] = false;
                    $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                        '; Kategorie für Benutzergruppe ' . $oRes->kKundengruppe . ' nicht sichtbar.');
                }
            }
        }

        return $this;
    }

    /**
     * @return mixed
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
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Es ist ein Fehler beim Auswählen der Standardsprache aufgetreten: Keine Standardsprache vorhanden.');
            die('JTL-Search Fehler beim Datenexport: Keine Standard-Shopsprache vorhanden.');
        } else {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Es ist ein Fehler beim Auswählen der Standardsprache aufgetreten: Keine Sprachen vorhanden.');
            die('JTL-Search Fehler beim Datenexport: Keine Shop-Sprachen vorhanden.');
        }
    }

    /**
     * @return Category
     */
    public function getFilledObject()
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Categorieobjekt erstellen.');
        $oDefaultLanguage = $this->getDefaultLanguage();
        $shopURL          = Shop::getURL();
        $oCategory        = new Category();
        if (isset($this->oCategoryData->cPfad) && !empty($this->oCategoryData->cPfad)) {
            $oCategory->setPictureURL($shopURL . '/' . PFAD_KATEGORIEBILDER . $this->oCategoryData->cPfad);
        }
        $oCategory->setId($this->oCategoryData->kKategorie)
                  ->setMasterCategory($this->oCategoryData->kOberKategorie)
                  ->setPriority(5);
        if($oDefaultLanguage->cStandard == $oDefaultLanguage->cShopStandard) {
            $oCategory->setName($this->oCategoryData->cName, $oDefaultLanguage->cISO)
                ->setDescription($this->oCategoryData->cBeschreibung, $oDefaultLanguage->cISO)
                ->setKeywords($this->oCategoryData->cKeywords, $oDefaultLanguage->cISO)
                ->setURL($shopURL . '/' . baueURL($this->oCategoryData, URLART_KATEGORIE), $oDefaultLanguage->cISO);
        }

        $_SESSION['Sprachen'] = $this->oSprache_arr;
        foreach ($this->oSprache_arr as $oSprache) {
            if (isset($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache])) {
                $oCategory->setName(
                    $this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cName,
                    $oSprache->cISO
                )->setDescription(
                    $this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cBeschreibung,
                    $oSprache->cISO
                );
                if (isset($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cKeywords)) {
                    $oCategory->setKeywords(
                        $this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache]->cKeywords,
                        $oSprache->cISO
                    );
                }
                $_SESSION['kSprache']    = $oSprache->kSprache;
                $_SESSION['cISOSprache'] = $oSprache->cISO;
                $oCategory->setURL(
                    $shopURL . '/' .
                    baueURL($this->oCategoryData->oCategoryLanguage_arr[$oSprache->kSprache], URLART_KATEGORIE),
                    $oSprache->cISO
                );
                unset($_SESSION['kSprache']);
                unset($_SESSION['cISOSprache']);
            }
        }
        unset($_SESSION['Sprachen']);

        foreach ($this->oKundengruppe_arr as $oKundengruppe) {
            $oCategory->setVisibility(
                $this->oCategoryData->bUsergroupVisible[$oKundengruppe->kKundengruppe],
                $oKundengruppe->kKundengruppe
            );
        }

        return $oCategory;
    }
}
