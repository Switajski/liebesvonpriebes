<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class HerstellerHelper
 */
class HerstellerHelper
{
    /**
     * @var HerstellerHelper
     */
    private static $_instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array|mixed
     */
    public $manufacturers;

    /**
     * @var int
     */
    private static $langID;

    /**
     *
     */
    public function __construct()
    {
        $lagerfilter   = gibLagerfilter();
        $this->cacheID = 'manuf_' . Shop::Cache()->getBaseID() . (($lagerfilter !== '') ? md5($lagerfilter) : '');
        self::$langID  = Shop::getLanguage();
        if (self::$langID <= 0) {
            if (isset($_SESSION['kSprache'])) {
                self::$langID = (int)$_SESSION['kSprache'];
            } else {
                $_lang        = gibStandardsprache();
                self::$langID = (int)$_lang->kSprache;
            }
        }
        $this->manufacturers = $this->getManufacturers();
        self::$_instance     = $this;
    }

    /**
     * @return HerstellerHelper
     */
    public static function getInstance()
    {
        return (self::$_instance === null || (int)Shop::$kSprache !== self::$langID)
            ? new self()
            : self::$_instance;
    }

    /**
     * @return array|mixed
     */
    public function getManufacturers()
    {
        if ($this->manufacturers === null) {
            if (($manufacturers = Shop::Cache()->get($this->cacheID)) === false) {
                $lagerfilter = gibLagerfilter();
                //fixes for admin backend
                $manufacturers   = Shop::DB()->query(
                    "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                            thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                            therstellersprache.cMetaDescription, therstellersprache.cBeschreibung, tseo.cSeo
                        FROM thersteller
                        LEFT JOIN therstellersprache 
                            ON therstellersprache.kHersteller = thersteller.kHersteller
                            AND therstellersprache.kSprache = " . self::$langID . "
                        LEFT JOIN tseo 
                            ON tseo.kKey = thersteller.kHersteller
                            AND tseo.cKey = 'kHersteller'
                            AND tseo.kSprache = " . self::$langID . "
                        WHERE EXISTS (
                            SELECT 1
                            FROM tartikel
                            WHERE tartikel.kHersteller = thersteller.kHersteller
                                {$lagerfilter}
                                AND NOT EXISTS (
                                    SELECT 1 FROM tartikelsichtbarkeit
                                    WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                        AND tartikelsichtbarkeit.kKundengruppe = " . Kundengruppe::getDefaultGroupID() . "
							        )
                            )
                        ORDER BY thersteller.nSortNr, thersteller.cName", 2
                );
                if (is_array($manufacturers) && count($manufacturers) > 0) {
                    foreach ($manufacturers as $i => $oHersteller) {
                        if (isset($oHersteller->cBildpfad) && strlen($oHersteller->cBildpfad) > 0) {
                            $manufacturers[$i]->cBildpfadKlein  = PFAD_HERSTELLERBILDER_KLEIN . $oHersteller->cBildpfad;
                            $manufacturers[$i]->cBildpfadNormal = PFAD_HERSTELLERBILDER_NORMAL . $oHersteller->cBildpfad;
                        } else {
                            $manufacturers[$i]->cBildpfadKlein  = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                            $manufacturers[$i]->cBildpfadNormal = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                        }
                    }
                }
                $cacheTags = [CACHING_GROUP_MANUFACTURER, CACHING_GROUP_CORE];
                executeHook(HOOK_GET_MANUFACTURERS, [
                    'cached'        => false,
                    'cacheTags'     => &$cacheTags,
                    'manufacturers' => &$manufacturers
                ]);
                Shop::Cache()->set($this->cacheID, $manufacturers, $cacheTags);
            } else {
                executeHook(HOOK_GET_MANUFACTURERS, [
                    'cached'        => true,
                    'cacheTags'     => [],
                    'manufacturers' => &$manufacturers
                ]);
            }
            $this->manufacturers = $manufacturers;
        }

        return $this->manufacturers;
    }
}
