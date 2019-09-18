<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Boxen
 */
class Boxen
{
    /**
     * @var array
     */
    public $boxes = [];

    /**
     * @var array
     */
    public $boxConfig = [];

    /**
     * @var string
     */
    public $lagerFilter = '';

    /**
     * @var string
     */
    public $cVaterSQL = ' AND tartikel.kVaterArtikel = 0';

    /**
     * unrendered box template file name + data
     *
     * @var array
     */
    public $rawData = [];

    /**
     * @var array
     */
    public $visibility;

    /**
     * @var Boxen
     */
    private static $_instance;

    /**
     * @return Boxen
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->boxConfig = Shop::getSettings([
            CONF_GLOBAL,
            CONF_BOXEN,
            CONF_VERGLEICHSLISTE,
            CONF_NAVIGATIONSFILTER,
            CONF_NEWS,
            CONF_UMFRAGE,
            CONF_TRUSTEDSHOPS
        ]);
        self::$_instance = $this;
    }

    /**
     * @param int $nSeite
     * @return array
     */
    public function holeVorlagen($nSeite = -1)
    {
        $cSQL          = '';
        $oVorlagen_arr = [];

        if ($nSeite >= 0) {
            $cSQL = 'WHERE (cVerfuegbar = "' . (int)$nSeite . '" OR cVerfuegbar = "0")';
        }
        $oVorlage_arr = Shop::DB()->query("SELECT * FROM tboxvorlage " . $cSQL . " ORDER BY cVerfuegbar ASC", 2);
        foreach ($oVorlage_arr as $oVorlage) {
            $nID   = 0;
            $cName = 'Vorlage';
            if ($oVorlage->eTyp === 'text') {
                $nID   = 1;
                $cName = 'Inhalt';
            } elseif ($oVorlage->eTyp === 'link') {
                $nID   = 2;
                $cName = 'Linkliste';
            } elseif ($oVorlage->eTyp === 'plugin') {
                $nID   = 3;
                $cName = 'Plugin';
            } elseif ($oVorlage->eTyp === 'catbox') {
                $nID   = 4;
                $cName = 'Kategorie';
            }

            if (!isset($oVorlagen_arr[$nID])) {
                $oVorlagen_arr[$nID]               = new stdClass();
                $oVorlagen_arr[$nID]->oVorlage_arr = [];
            }

            $oVorlagen_arr[$nID]->cName          = $cName;
            $oVorlagen_arr[$nID]->oVorlage_arr[] = $oVorlage;
        }

        return $oVorlagen_arr;
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @return mixed
     */
    public function gibBoxInhalt($kBox, $cISO = '')
    {
        return (strlen($cISO) > 0)
            ? Shop::DB()->select('tboxsprache', 'kBox', (int)$kBox, 'cISO', $cISO)
            : Shop::DB()->selectAll('tboxsprache', 'kBox', (int)$kBox);
    }

    /**
     * @param int  $nSeite
     * @param bool $bAktiv
     * @param bool $bVisible
     * @param bool $force
     * @return array|mixed
     */
    public function holeBoxen($nSeite = 0, $bAktiv = true, $bVisible = false, $force = false)
    {
        $nSeite  = (int)$nSeite;
        $cacheID = 'box_' . $nSeite . '_' . (($bAktiv === true) ? '1' : '0') .
            '_' . (($bVisible === true) ? '1' : '0') . '_' . Shop::getLanguage();

        if (($oBox_arr = Shop::Cache()->get($cacheID)) !== false) {
            return $oBox_arr;
        }
        $this->visibility = $this->holeBoxAnzeige($nSeite);
        $validPageTypes   = $this->getValidPageTypes();
        $oBox_arr         = [];
        $cacheTags        = [CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes'];
        $cSQLAktiv        = $bAktiv ? " AND bAktiv = 1 " : "";
        $cPluginAktiv     = $bAktiv
            ? " AND (tplugin.nStatus IS NULL OR tplugin.nStatus = 2  OR tboxvorlage.eTyp != 'plugin')"
            : "";
        $oBoxen_arr       = Shop::DB()->query("
            SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.kContainer, 
                   tboxen.cTitel, tboxen.ePosition, tboxensichtbar.kSeite, tboxensichtbar.nSort, 
                   tboxensichtbar.bAktiv, tboxensichtbar.cFilter, tboxvorlage.eTyp, 
                   tboxvorlage.cName, tboxvorlage.cTemplate, tplugin.nStatus AS pluginStatus
                FROM tboxen
                LEFT JOIN tboxensichtbar
                    ON tboxen.kBox = tboxensichtbar.kBox
                LEFT JOIN tplugin
                    ON tboxen.kCustomID = tplugin.kPlugin
                LEFT JOIN tboxvorlage
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxensichtbar.kSeite = " . $nSeite . $cPluginAktiv .
                    " AND tboxen.kContainer = 0 " . $cSQLAktiv . "
                ORDER BY tboxensichtbar.nSort ASC", 2
        );
        if (is_array($oBoxen_arr)) {
            foreach ($oBoxen_arr as $oBox) {
                $oBox->kBox        = (int)$oBox->kBox;
                $oBox->kBoxvorlage = (int)$oBox->kBoxvorlage;
                $oBox->kCustomID   = (int)$oBox->kCustomID;
                $oBox->kContainer  = (int)$oBox->kContainer;
                $oBox->kSeite      = (int)$oBox->kSeite;
                $oBox->nSort       = (int)$oBox->nSort;
                $oBox->bAktiv      = (int)$oBox->bAktiv;
                unset($oBox->pluginStatus);
                if ($oBox->eTyp === 'plugin') {
                    $cacheTags[] = CACHING_GROUP_PLUGIN . '_' . $oBox->kCustomID;
                }
                if ($force === true ||
                    (isset($this->visibility[$oBox->ePosition]) && $this->visibility[$oBox->ePosition] === true)
                ) {
                    $kContainer           = (int)$oBox->kBox;
                    $oBox->oContainer_arr = [];
                    $oBox->nContainer     = 0;
                    if ($kContainer > 0) {
                        $oContainerBoxen_arr = Shop::DB()->query("
                            SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.kContainer, tboxen.cTitel, 
                                tboxen.ePosition, tboxensichtbar.kSeite, tboxensichtbar.nSort, tboxensichtbar.bAktiv, 
                                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cTemplate
                                FROM tboxen
                                LEFT JOIN tboxensichtbar
                                    ON tboxen.kBox = tboxensichtbar.kBox
                                LEFT JOIN tboxvorlage
                                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                                WHERE (tboxensichtbar.kSeite = " . $nSeite . ")
                                    AND tboxen.kContainer = " . $kContainer . $cSQLAktiv . "
                                    ORDER BY tboxensichtbar.nSort ASC", 2
                        );
                        if (count($oContainerBoxen_arr) > 0) {
                            foreach ($oContainerBoxen_arr as $childBox) {
                                $childBox->bContainer  = 0;
                                $childBox->kBox        = (int)$childBox->kBox;
                                $childBox->kBoxvorlage = (int)$childBox->kBoxvorlage;
                                $childBox->kCustomID   = (int)$childBox->kCustomID;
                                $childBox->kContainer  = (int)$childBox->kContainer;
                                $childBox->kSeite      = (int)$childBox->kSeite;
                                $childBox->nSort       = (int)$childBox->nSort;
                                $childBox->bAktiv      = (int)$childBox->bAktiv;
                            }
                            $oBox->oContainer_arr = $oContainerBoxen_arr;
                            $oBox->nContainer     = count($oContainerBoxen_arr);
                        }
                    }
                    if (empty($oBox->cTitel)) {
                        $oBox->cTitel = $oBox->cName;
                    }
                    if ($bAktiv && ($oBox->eTyp === 'text' || $oBox->eTyp === 'catbox')) {
                        $cISO           = isset($_SESSION['cISOSprache']) && strlen($_SESSION['cISOSprache'])
                            ? $_SESSION['cISOSprache']
                            : 'ger';
                        $oBox->cTitel   = '';
                        $oBox->cInhalt  = '';
                        $oSpracheInhalt = $this->gibBoxInhalt($oBox->kBox, $cISO);
                        if (is_object($oSpracheInhalt)) {
                            $oBox->cTitel  = $oSpracheInhalt->cTitel;
                            $oBox->cInhalt = $oSpracheInhalt->cInhalt;
                        }
                    } elseif ($bAktiv && $oBox->kBoxvorlage === 0 && !empty($oBox->oContainer_arr)) { //container
                        foreach ($oBox->oContainer_arr as $_box) {
                            if (isset($_box->eTyp) && ($_box->eTyp === 'text' || $_box->eTyp === 'catbox')) {
                                $cISO           = isset($_SESSION['cISOSprache']) && strlen($_SESSION['cISOSprache'])
                                    ? $_SESSION['cISOSprache']
                                    : 'ger';
                                $_box->cTitel   = '';
                                $_box->cInhalt  = '';
                                $oSpracheInhalt = $this->gibBoxInhalt($_box->kBox, $cISO);
                                if (is_object($oSpracheInhalt)) {
                                    $_box->cTitel  = $oSpracheInhalt->cTitel;
                                    $_box->cInhalt = $oSpracheInhalt->cInhalt;
                                }
                            }
                        }
                    }
                    $oBox->bContainer = $oBox->kBoxvorlage === 0;
                    if ($bVisible) {
                        $oBox->cVisibleOn = '';
                        $oVisible_arr     = Shop::DB()->selectAll('tboxensichtbar', ['kBox', 'bAktiv'], [(int)$oBox->kBox, 1]);
                        if (count($oVisible_arr) >= count($validPageTypes)) {
                            $oBox->cVisibleOn = "\n- Auf allen Seiten";
                        } elseif (count($oVisible_arr) === 0) {
                            $oBox->cVisibleOn = "\n- Auf allen Seiten deaktiviert";
                        } else {
                            foreach ($oVisible_arr as $oVisible) {
                                if ($oVisible->kSeite > 0 && $oVisible->kSeite != PAGE_NEWSARCHIV) {
                                    $oBox->cVisibleOn .= "\n- " . $this->mappekSeite($oVisible->kSeite);
                                }
                            }
                        }
                        //add the filter for admin backend
                        foreach ($oVisible_arr as $oVisible) {
                            if ((int)$nSeite === (int)$oVisible->kSeite) {
                                if (!empty($oVisible->cFilter)) {
                                    $_tmp          = explode(',', $oVisible->cFilter);
                                    $filterOptions = [];
                                    foreach ($_tmp as $_filterValue) {
                                        $filterEntry       = [];
                                        $filterEntry['id'] = $_filterValue;
                                        $name              = null;
                                        if ($nSeite === PAGE_ARTIKELLISTE) { //map category name
                                            $name = Shop::DB()->select(
                                                'tkategorie',
                                                'kKategorie', (int)$_filterValue,
                                                null, null,
                                                null, null,
                                                false,
                                                'cName'
                                            );
                                        } elseif ($nSeite === PAGE_ARTIKEL) { //map article name
                                            $name = Shop::DB()->select(
                                                'tartikel',
                                                'kArtikel', (int)$_filterValue,
                                                null, null,
                                                null, null,
                                                false,
                                                'cName'
                                            );
                                        } elseif ($nSeite === PAGE_HERSTELLER) { //map manufacturer name
                                            $name = Shop::DB()->select(
                                                'thersteller',
                                                'kHersteller', (int)$_filterValue,
                                                null, null,
                                                null, null,
                                                false,
                                                'cName'
                                            );
                                        } elseif ($nSeite === PAGE_EIGENE) { //map page name
                                            $name = Shop::DB()->select(
                                                'tlink',
                                                'kLink', (int)$_filterValue,
                                                null, null,
                                                null, null,
                                                false,
                                                'cName'
                                            );
                                        }
                                        $filterEntry['name'] = (!empty($name->cName)) ? $name->cName : '???';
                                        $filterOptions[]     = $filterEntry;
                                    }
                                    $oBox->cFilter = $filterOptions;
                                } else {
                                    $oBox->cFilter = [];
                                }
                                break;
                            }
                        }
                    }
                    $oBox_arr[$oBox->ePosition][] = $oBox;
                }
            }
        }
        Shop::Cache()->set($cacheID, $oBox_arr, array_unique($cacheTags));

        return $oBox_arr;
    }

    /**
     * generate array of currently active boxes
     *
     * @param int  $nSeite
     * @param bool $bAktiv
     * @param bool $bVisible
     * @return $this
     */
    public function build($nSeite = 0, $bAktiv = true, $bVisible = false)
    {
        if (count($this->boxes) === 0) {
            $this->boxes = $this->holeBoxen($nSeite, $bAktiv, $bVisible);
        }

        return $this;
    }

    /**
     * read linkgroup array and search for specific ID
     *
     * @param int $id
     * @return array|null
     */
    private function getLinkGroupByID($id)
    {
        $linkHelper = LinkHelper::getInstance();
        $linkGroups = $linkHelper->getLinkGroups();
        foreach ($linkGroups as $_tpl => $_lnkgrp) {
            if (isset($_lnkgrp->kLinkgruppe) && $_lnkgrp->kLinkgruppe === $id) {
                return ['tpl' => $_tpl, 'grp' => $_lnkgrp];
            }
        }

        return null;
    }

    /**
     * supply data for specific box types
     *
     * @param int    $kBoxVorlage
     * @param object $oBox
     * @return mixed
     */
    public function prepareBox($kBoxVorlage, $oBox)
    {
        $kKundengruppe     = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $kBoxVorlage       = (int)$kBoxVorlage;
        $currencyCachePart = isset($_SESSION['Waehrung']->kWaehrung)
            ? '_cur_' . $_SESSION['Waehrung']->kWaehrung
            : '';
        $kSprache          = Shop::getLanguage();
        switch ($kBoxVorlage) {
            case BOX_BESTSELLER :
                $oBox->compatName = 'Bestseller';
                if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $kArtikel_arr = [];
                $limit        = (int)$this->boxConfig['boxen']['box_bestseller_anzahl_basis'];
                $anzahl       = (int)$this->boxConfig['boxen']['box_bestseller_anzahl_anzeige'];
                $nAnzahl      = ((int)$this->boxConfig['global']['global_bestseller_minanzahl'] > 0)
                    ? (int)$this->boxConfig['global']['global_bestseller_minanzahl']
                    : 100;
                if ($limit < 1) {
                    $limit = 10;
                }
                $cacheID = 'box_bestseller_' . $kKundengruppe . $currencyCachePart . '_' .
                    $kSprache . '_' . md5($this->cVaterSQL . $this->lagerFilter);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $menge = Shop::DB()->query(
                        "SELECT tartikel.kArtikel
                            FROM tbestseller, tartikel
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tbestseller.kArtikel = tartikel.kArtikel
                                AND round(tbestseller.fAnzahl) >= " . $nAnzahl . "
                                $this->cVaterSQL
                                $this->lagerFilter
                            ORDER BY fAnzahl DESC LIMIT " . $limit, 2
                    );
                    if (is_array($menge) && count($menge) > 0) {
                        $rndkeys = array_rand($menge, min($anzahl, count($menge)));
                        if (is_array($rndkeys)) {
                            foreach ($rndkeys as $key) {
                                if (isset($menge[$key]->kArtikel) && $menge[$key]->kArtikel > 0) {
                                    $kArtikel_arr[] = $menge[$key]->kArtikel;
                                }
                            }
                        } elseif (is_int($rndkeys)) {
                            if (isset($menge[$rndkeys]->kArtikel) && $menge[$rndkeys]->kArtikel > 0) {
                                $kArtikel_arr[] = $menge[$rndkeys]->kArtikel;
                            }
                        }
                    }

                    if (count($kArtikel_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $oBox->Artikel  = new ArtikelListe();
                        $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                        $oBox->cURL = baueSuchSpecialURL(SEARCHSPECIALS_BESTSELLER);
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    executeHook(HOOK_BOXEN_INC_BESTSELLER, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_TRUSTEDSHOPS_GUETESIEGEL :
                $oBox->compatName = 'TrustedShopsSiegelbox';
                if ($this->boxConfig['trustedshops']['trustedshops_nutzen'] === 'Y') {
                    $oTrustedShops    = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                    $shopURL          = Shop::getURL(true) . '/';
                    if ($oTrustedShops->nAktiv == 1 && strlen($oTrustedShops->tsId) > 0) {
                        $oBox->anzeigen          = 'Y';
                        $oBox->cLogoURL          = $oTrustedShops->cLogoURL;
                        $oBox->cLogoSiegelBoxURL = $oTrustedShops->cLogoSiegelBoxURL[StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])];
                        $oBox->cBild             = $shopURL . PFAD_GFX_TRUSTEDSHOPS . 'trustedshops_m.png';
                        $oBox->cBGBild           = $shopURL . PFAD_GFX_TRUSTEDSHOPS . 'bg_yellow.jpg';
                    }
                }
                break;

            case BOX_TRUSTEDSHOPS_KUNDENBEWERTUNGEN :
                $oBox->compatName    = 'TrustedShopsKundenbewertung';
                $cValidSprachISO_arr = ['de', 'en', 'fr', 'pl', 'es'];
                if ($this->boxConfig['trustedshops']['trustedshops_nutzen'] === 'Y' &&
                    in_array(StringHandler::convertISO2ISO639($_SESSION['cISOSprache']), $cValidSprachISO_arr, true)) {
                    $oTrustedShops                = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                    $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                    if (isset($oTrustedShopsKundenbewertung->cTSID) &&
                        $oTrustedShopsKundenbewertung->nStatus == 1 &&
                        strlen($oTrustedShopsKundenbewertung->cTSID) > 0
                    ) {
                        $cURLSprachISO_arr = [
                            'de' => 'https://www.trustedshops.com/bewertung/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
                            'en' => 'https://www.trustedshops.com/buyerrating/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
                            'fr' => 'https://www.trustedshops.com/evaluation/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
                            'es' => 'https://www.trustedshops.com/evaluacion/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
                            'pl' => ''
                        ];
                        $oBox->anzeigen = 'Y';
                        if (!$this->cachecheck($filename = $oTrustedShopsKundenbewertung->cTSID . '.gif', 10800)) {
                            if (!$oTrustedShops::ladeKundenbewertungsWidgetNeu($filename)) {
                                $oBox->anzeigen = 'N';
                            }
                            // Prüft alle X Stunden ob ein Zertifikat noch gültig ist
                            $oTrustedShops->pruefeZertifikat(StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                        }
                        $oBox->cBildPfad    = Shop::getURL(true) . '/' . PFAD_GFX_TRUSTEDSHOPS . $filename;
                        $oBox->cBildPfadURL = $cURLSprachISO_arr[StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])];
                        $oBox->oStatistik   = $oTrustedShops->gibKundenbewertungsStatistik();
                    }
                }
                break;

            case BOX_UMFRAGE :
                $oBox->compatName = 'Umfrage';
                $oBox->anzeigen   = 'N';
                $cSQL             = '';
                if (isset($this->boxConfig['umfrage']['news_anzahl_box']) &&
                    (int)$this->boxConfig['umfrage']['news_anzahl_box'] > 0
                ) {
                    $cSQL = ' LIMIT ' . (int)$this->boxConfig['umfrage']['umfrage_box_anzahl'];
                }
                $cacheID = 'bu_' . $kSprache . '_' . $_SESSION['Kundengruppe']->kKundengruppe . md5($cSQL);
                if (($oUmfrage_arr = Shop::Cache()->get($cacheID)) === false) {
                    // Umfrage Übersicht
                    $oUmfrage_arr = Shop::DB()->query(
                        "SELECT tumfrage.kUmfrage, tumfrage.kSprache, tumfrage.kKupon, tumfrage.cKundengruppe, 
                            tumfrage.cName, tumfrage.cBeschreibung, tumfrage.fGuthaben, tumfrage.nBonuspunkte, 
                            tumfrage.nAktiv, tumfrage.dGueltigVon, tumfrage.dGueltigBis, tumfrage.dErstellt, tseo.cSeo,
                            DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de,
                            DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y  %H:%i') AS dGueltigBis_de, 
                            count(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                            FROM tumfrage
                            JOIN tumfragefrage 
                                ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kUmfrage'
                                AND tseo.kKey = tumfrage.kUmfrage
                                AND tseo.kSprache = " . $kSprache . "
                            WHERE tumfrage.nAktiv = 1
                                AND tumfrage.kSprache = " . $kSprache . "
                                AND (cKundengruppe LIKE '%;-1;%' 
                                    OR FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe . "', REPLACE(cKundengruppe, ';', ',')) > 0)
                                AND ((dGueltigVon <= now() 
                                    AND dGueltigBis >= now()) || (dGueltigVon <= now() 
                                    AND dGueltigBis = '0000-00-00 00:00:00'))
                            GROUP BY tumfrage.kUmfrage
                            ORDER BY tumfrage.dGueltigVon DESC" . $cSQL, 2
                    );

                    if (is_array($oUmfrage_arr) && count($oUmfrage_arr) > 0) {
                        foreach ($oUmfrage_arr as $i => $oUmfrage) {
                            $oUmfrage_arr[$i]->cURL = baueURL($oUmfrage, URLART_UMFRAGE);
                        }
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_CORE];
                    executeHook(HOOK_BOXEN_INC_UMFRAGE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oUmfrage_arr, $cacheTags); //@todo: invalidate
                }
                $oBox->oUmfrage_arr = $oUmfrage_arr;

                break;

            case BOX_PREISRADAR :
                $oBox->compatName = 'Preisradar';
                $oBox->anzeigen   = 'N';
                $nLimit  = (isset($this->boxConfig['boxen']['boxen_preisradar_anzahl']) && (int)$this->boxConfig['boxen']['boxen_preisradar_anzahl'] > 0)
                    ? (int)$this->boxConfig['boxen']['boxen_preisradar_anzahl']
                    : 3;
                $nTage   = (isset($this->boxConfig['boxen']['boxen_preisradar_anzahltage']) && (int)$this->boxConfig['boxen']['boxen_preisradar_anzahltage'] > 0)
                    ? (int)$this->boxConfig['boxen']['boxen_preisradar_anzahltage']
                    : 30;
                $cacheID = 'box_price_radar_' . $currencyCachePart . $nTage . '_' . $nLimit . '_' . $kSprache . '_' . $_SESSION['Kundengruppe']->kKundengruppe;
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $oPreisradar_arr         = Preisradar::getProducts($_SESSION['Kundengruppe']->kKundengruppe, $nLimit, $nTage);
                    $oBox->Artikel           = new stdClass();
                    $oBox->Artikel->elemente = [];
                    if (count($oPreisradar_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $defaultOptions = Artikel::getDefaultOptions();
                        foreach ($oPreisradar_arr as $oPreisradar) {
                            $oArtikel = new Artikel();
                            $oArtikel->fuelleArtikel($oPreisradar->kArtikel, $defaultOptions);
                            $oArtikel->oPreisradar                     = new stdClass();
                            $oArtikel->oPreisradar->fDiff              = $oPreisradar->fDiff * -1;
                            $oArtikel->oPreisradar->fDiffLocalized[0]  = gibPreisStringLocalized(berechneBrutto($oArtikel->oPreisradar->fDiff, $oArtikel->Preise->fUst));
                            $oArtikel->oPreisradar->fDiffLocalized[1]  = gibPreisStringLocalized($oArtikel->oPreisradar->fDiff);
                            $oArtikel->oPreisradar->fOldVKLocalized[0] = gibPreisStringLocalized(berechneBrutto($oArtikel->Preise->fVKNetto + $oArtikel->oPreisradar->fDiff, $oArtikel->Preise->fUst));
                            $oArtikel->oPreisradar->fOldVKLocalized[1] = gibPreisStringLocalized($oArtikel->Preise->fVKNetto + $oArtikel->oPreisradar->fDiff);
                            $oArtikel->oPreisradar->fProzentDiff       = $oPreisradar->fProzentDiff;

                            if ((int)$oArtikel->kArtikel > 0) {
                                $oBox->Artikel->elemente[] = $oArtikel;
                            }
                        }
                    }
                    Shop::Cache()->set($cacheID, $oBox, [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE]);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_NEWS_KATEGORIEN :
                $oBox->compatName = 'NewsKategorie';
                $cSQL             = '';
                if ((int)$this->boxConfig['news']['news_anzahl_box'] > 0) {
                    $cSQL = " LIMIT " . (int)$this->boxConfig['news']['news_anzahl_box'];
                }
                $cacheID = 'bnk_' . $kSprache . '_' . (int)$_SESSION['Kundengruppe']->kKundengruppe . '_' . md5($cSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $oNewsKategorie_arr = Shop::DB()->query(
                        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
                            tnewskategorie.cPreviewImage, tseo.cSeo,
                            count(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                            FROM tnewskategorie
                            LEFT JOIN tnewskategorienews ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                            LEFT JOIN tnews ON tnews.kNews = tnewskategorienews.kNews
                            LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                                AND tseo.kKey = tnewskategorie.kNewsKategorie
                                AND tseo.kSprache = " . $kSprache . "
                            WHERE tnewskategorie.kSprache = " . $kSprache . "
                                AND tnewskategorie.nAktiv = 1
                                AND tnews.nAktiv = 1
                                AND tnews.dGueltigVon <= now()
                                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                                    OR FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe
                                    . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                                AND tnews.kSprache = " . $kSprache . "
                            GROUP BY tnewskategorienews.kNewsKategorie
                            ORDER BY tnewskategorie.nSort DESC" . $cSQL, 2
                    );
                    if (is_array($oNewsKategorie_arr) && count($oNewsKategorie_arr) > 0) {
                        foreach ($oNewsKategorie_arr as $i => $oNewsKategorie) {
                            $oNewsKategorie_arr[$i]->cURL = baueURL($oNewsKategorie, URLART_NEWSKATEGORIE);
                        }
                    }
                    $oBox->anzeigen           = 'Y';
                    $oBox->oNewsKategorie_arr = $oNewsKategorie_arr;
                    $cacheTags                = [CACHING_GROUP_BOX, CACHING_GROUP_NEWS];
                    executeHook(HOOK_BOXEN_INC_NEWSKATEGORIE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_HERSTELLER :
                $helper              = HerstellerHelper::getInstance();
                $oBox->compatName    = 'Hersteller';
                $oBox->anzeigen      = 'Y';
                $oBox->manufacturers = $helper->getManufacturers();
                break;

            case BOX_NEWS_AKTUELLER_MONAT :
                $oBox->compatName = 'News';
                $cSQL             = '';
                if ((int)$this->boxConfig['news']['news_anzahl_box'] > 0) {
                    $cSQL = ' LIMIT ' . (int)$this->boxConfig['news']['news_anzahl_box'];
                }
                $oNewsMonatsUebersicht_arr = Shop::DB()->query(
                    "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                        month(tnews.dGueltigVon) AS nMonat, year( tnews.dGueltigVon ) AS nJahr, count(*) AS nAnzahl
                        FROM tnews
                        JOIN tnewsmonatsuebersicht ON tnewsmonatsuebersicht.nMonat = month(tnews.dGueltigVon)
                            AND tnewsmonatsuebersicht.nJahr = year(tnews.dGueltigVon)
                            AND tnewsmonatsuebersicht.kSprache = " . $kSprache . "
                        LEFT JOIN tseo ON cKey = 'kNewsMonatsUebersicht'
                            AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                            AND tseo.kSprache = " . $kSprache . "
                        WHERE tnews.dGueltigVon < now()
                            AND tnews.nAktiv = 1
                            AND tnews.kSprache = " . $kSprache . "
                        GROUP BY year(tnews.dGueltigVon) , month(tnews.dGueltigVon)
                        ORDER BY tnews.dGueltigVon DESC" . $cSQL, 2
                );
                if (is_array($oNewsMonatsUebersicht_arr) && count($oNewsMonatsUebersicht_arr) > 0) {
                    foreach ($oNewsMonatsUebersicht_arr as $i => $oNewsMonatsUebersicht) {
                        $oNewsMonatsUebersicht_arr[$i]->cURL = baueURL($oNewsMonatsUebersicht, URLART_NEWSMONAT);
                    }
                }
                $oBox->anzeigen                  = 'Y';
                $oBox->oNewsMonatsUebersicht_arr = $oNewsMonatsUebersicht_arr;

                executeHook(HOOK_BOXEN_INC_NEWS);
                break;

            case BOX_TOP_BEWERTET :
                $oBox->compatName = 'TopBewertet';
                $cacheID          = 'box_top_rated_' . $currencyCachePart . $this->boxConfig['boxen']['boxen_topbewertet_minsterne'] . '_' .
                    $kSprache . '_' . $this->boxConfig['boxen']['boxen_topbewertet_basisanzahl'] . md5($this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $oTopBewertet_arr = Shop::DB()->query(
                        "SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                            FROM tartikel
                            JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel
                            WHERE round(fDurchschnittsBewertung) >= " . (int)$this->boxConfig['boxen']['boxen_topbewertet_minsterne'] . "
                            $this->cVaterSQL
                            ORDER BY tartikelext.fDurchschnittsBewertung DESC
                            LIMIT " . (int)$this->boxConfig['boxen']['boxen_topbewertet_basisanzahl'], 2
                    );

                    if (is_array($oTopBewertet_arr) && count($oTopBewertet_arr) > 0) {
                        $kArtikel_arr = [];
                        $oArtikel_arr = [];
                        // Alle kArtikels aus der DB Menge in ein Array speichern
                        foreach ($oTopBewertet_arr as $oTopBewertet) {
                            $kArtikel_arr[] = $oTopBewertet->kArtikel;
                        }
                        // Wenn das Array Elemente besitzt
                        if (is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
                            // Gib mir X viele Random Keys
                            $nAnzahlKeys = (int)$this->boxConfig['boxen']['boxen_topbewertet_anzahl'];
                            if (count($oTopBewertet_arr) < (int)$this->boxConfig['boxen']['boxen_topbewertet_anzahl']) {
                                $nAnzahlKeys = count($oTopBewertet_arr);
                            }
                            $kKey_arr = array_rand($kArtikel_arr, $nAnzahlKeys);

                            if (is_array($kKey_arr) && count($kKey_arr) > 0) {
                                // Lauf die Keys durch und hole baue Artikelobjekte
                                $defaultOptions = Artikel::getDefaultOptions();
                                foreach ($kKey_arr as $i => $kKey) {
                                    $oArtikel = new Artikel();
                                    $oArtikel->fuelleArtikel($kArtikel_arr[$kKey], $defaultOptions);
                                    $oArtikel_arr[] = $oArtikel;
                                }
                            }
                            // Laufe die DB Menge durch und assigne zu jedem Artikelobjekt noch die Durchschnittsbewertung
                            foreach ($oTopBewertet_arr as $oTopBewertet) {
                                foreach ($oArtikel_arr as $j => $oArtikel) {
                                    if ($oTopBewertet->kArtikel == $oArtikel->kArtikel) {
                                        $oArtikel_arr[$j]->fDurchschnittsBewertung = round($oTopBewertet->fDurchschnittsBewertung * 2) / 2;
                                    }
                                }
                            }
                        }
                        $oBox->anzeigen     = 'Y';
                        $oBox->oArtikel_arr = $oArtikel_arr;
                        $oBox->cURL         = baueSuchSpecialURL(SEARCHSPECIALS_TOPREVIEWS);
                        $cacheTags          = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                        executeHook(HOOK_BOXEN_INC_TOPBEWERTET, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                        Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                    }
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_VERGLEICHSLISTE :
                $oBox->compatName = 'Vergleichsliste';
                $oArtikel_arr     = [];
                if (isset($_SESSION['Vergleichsliste']->oArtikel_arr)) {
                    $oArtikel_arr = $_SESSION['Vergleichsliste']->oArtikel_arr;
                }
                if (count($oArtikel_arr) > 0) {
                    $cGueltigePostVars_arr = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
                    $cZusatzParams         = '';
                    $cPostMembers_arr      = array_keys($_REQUEST);
                    foreach ($cPostMembers_arr as $cPostMember) {
                        if ((int)$_REQUEST[$cPostMember] > 0 && in_array($cPostMember, $cGueltigePostVars_arr, true)) {
                            $cZusatzParams .= '&' . $cPostMember . '=' . $_REQUEST[$cPostMember];
                        }
                    }
                    $cZusatzParams = StringHandler::filterXSS($cZusatzParams);
                    $oTMP_arr      = [];
                    $cRequestURI   = Shop::getRequestUri();
                    if ($cRequestURI === 'io.php') {
                        // Box wird von einem Ajax-Call gerendert
                        $cRequestURI = LinkHelper::getInstance()->getStaticRoute('vergleichsliste.php');
                    }
                    foreach ($oArtikel_arr as $oArtikel) {
                        $nPosAnd     = strrpos($cRequestURI, '&');
                        $nPosQuest   = strrpos($cRequestURI, '?');
                        $nPosWD      = strpos($cRequestURI, 'vlplo=');

                        if ($nPosWD) {
                            $cRequestURI = substr($cRequestURI, 0, $nPosWD);
                        }
                        // z.b. index.php
                        $cDeleteParam = '?vlplo=';
                        if ($nPosAnd === strlen($cRequestURI) - 1) {
                            // z.b. index.php?a=4&
                            $cDeleteParam = 'vlplo=';
                        } elseif ($nPosAnd) {
                            // z.b. index.php?a=4&b=2
                            $cDeleteParam = '&vlplo=';
                        } elseif ($nPosQuest) {
                            // z.b. index.php?a=4
                            $cDeleteParam = '&vlplo=';
                        } elseif ($nPosQuest === strlen($cRequestURI) - 1) {
                            // z.b. index.php?
                            $cDeleteParam = 'vlplo=';
                        }
                        if (TEMPLATE_COMPATIBILITY === false) {
                            $artikel = new Artikel();
                            $artikel->fuelleArtikel($oArtikel->kArtikel, Artikel::getDefaultOptions());
                            $artikel->cURLDEL = $cRequestURI . $cDeleteParam . $oArtikel->kArtikel . $cZusatzParams;
                            if (isset($oArtikel->oVariationen_arr) && count($oArtikel->oVariationen_arr) > 0) {
                                $artikel->Variationen = $oArtikel->oVariationen_arr;
                            }
                            if ($artikel->kArtikel > 0) {
                                $oTMP_arr[] = $artikel;
                            }
                        } else {
                            $oArtikel->cURLDEL = $cRequestURI . $cDeleteParam . $oArtikel->kArtikel . $cZusatzParams;
                        }
                    }

                    $oBox->anzeigen  = 'Y';
                    $oBox->cAnzeigen = $this->boxConfig['boxen']['boxen_vergleichsliste_anzeigen'];
                    $oBox->nAnzahl   = (int)$this->boxConfig['vergleichsliste']['vergleichsliste_anzahl'];
                    if (TEMPLATE_COMPATIBILITY === false) {
                        $oBox->Artikel = $oTMP_arr;
                    }

                    executeHook(HOOK_BOXEN_INC_VERGLEICHSLISTE, ['box' => $oBox]);
                }
                break;

            case BOX_WUNSCHLISTE :
                $oBox->compatName = 'Wunschliste';
                if (isset($_SESSION['Wunschliste']->kWunschliste) && $_SESSION['Wunschliste']->kWunschliste) {
                    $CWunschlistePos_arr   = $_SESSION['Wunschliste']->CWunschlistePos_arr;
                    $cGueltigePostVars_arr = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
                    $cZusatzParams         = '';
                    $cPostMembers_arr      = array_keys($_REQUEST);
                    foreach ($cPostMembers_arr as $cPostMember) {
                        if ((int)$_REQUEST[$cPostMember] > 0 && in_array($cPostMember, $cGueltigePostVars_arr, true)) {
                            $cZusatzParams .= '&' . $cPostMember . '=' . $_REQUEST[$cPostMember];
                        }
                    }
                    $cZusatzParams = StringHandler::filterXSS($cZusatzParams);
                    foreach ($CWunschlistePos_arr as $CWunschlistePos) {
                        $cRequestURI  = isset($_SERVER['REQUEST_URI'])
                            ? $_SERVER['REQUEST_URI']
                            : $_SERVER['SCRIPT_NAME'];
                        $nPosAnd      = strrpos($cRequestURI, '&');
                        $nPosQuest    = strrpos($cRequestURI, '?');
                        $nPosWD       = strpos($cRequestURI, 'wlplo=');
                        $cDeleteParam = '?wlplo='; // z.b. index.php
                        if ($nPosWD) {
                            $cRequestURI = substr($cRequestURI, 0, $nPosWD);
                        }
                        if ($nPosAnd === strlen($cRequestURI) - 1) {
                            // z.b. index.php?a=4&
                            $cDeleteParam = 'wlplo=';
                        } elseif ($nPosAnd) {
                            // z.b. index.php?a=4&b=2
                            $cDeleteParam = '&wlplo=';
                        } elseif ($nPosQuest) {
                            // z.b. index.php?a=4
                            $cDeleteParam = '&wlplo=';
                        } elseif ($nPosQuest === strlen($cRequestURI) - 1) {
                            // z.b. index.php?
                            $cDeleteParam = 'wlplo=';
                        }
                        $CWunschlistePos->cURL = $cRequestURI .
                            $cDeleteParam .
                            $CWunschlistePos->kWunschlistePos .
                            $cZusatzParams;
                        if ((int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
                            $fPreis = isset($CWunschlistePos->Artikel->Preise->fVKNetto)
                                ? (int)$CWunschlistePos->fAnzahl * $CWunschlistePos->Artikel->Preise->fVKNetto
                                : 0;
                        } else {
                            $fPreis = isset($CWunschlistePos->Artikel->Preise->fVKNetto)
                                ? (int)$CWunschlistePos->fAnzahl * ($CWunschlistePos->Artikel->Preise->fVKNetto *
                                    (100 + $_SESSION['Steuersatz'][$CWunschlistePos->Artikel->kSteuerklasse]) / 100)
                                : 0;
                        }
                        $CWunschlistePos->cPreis = gibPreisStringLocalized($fPreis, $_SESSION['Waehrung']);
                    }
                    $oBox->anzeigen            = 'Y';
                    $oBox->nAnzeigen           = (int)$this->boxConfig['boxen']['boxen_wunschzettel_anzahl'];
                    $oBox->nBilderAnzeigen     = $this->boxConfig['boxen']['boxen_wunschzettel_bilder'];
                    $oBox->CWunschlistePos_arr = array_reverse($CWunschlistePos_arr);

                    executeHook(HOOK_BOXEN_INC_WUNSCHZETTEL, ['box' => &$oBox]);
                }
                break;

            case BOX_TAGWOLKE :
                $oBox->compatName = 'Tagwolke';
                $limit            = (int)$this->boxConfig['boxen']['boxen_tagging_count'];
                $limitSQL         = ($limit > 0) ? ' LIMIT ' . $limit : '';
                $cacheID          = 'box_tag_cloud_' . $currencyCachePart . $kSprache . '_' . $limit;
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $Tagwolke_arr  = [];
                    $tagwolke_objs = Shop::DB()->query(
                        "SELECT ttag.kTag,ttag.cName, tseo.cSeo,sum(ttagartikel.nAnzahlTagging) AS Anzahl 
                            FROM ttag
                            JOIN ttagartikel 
                                ON ttagartikel.kTag = ttag.kTag
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kTag'
                                AND tseo.kKey = ttag.kTag
                                AND tseo.kSprache = " . $kSprache . "
                            WHERE ttag.nAktiv = 1 
                                AND ttag.kSprache = " . $kSprache . " 
                            GROUP BY ttag.kTag 
                            ORDER BY Anzahl DESC" . $limitSQL, 2
                    );

                    if (is_array($tagwolke_objs) && ($count = count($tagwolke_objs)) > 0) {
                        // Priorität berechnen
                        $prio_step = ($tagwolke_objs[0]->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / 9;
                        foreach ($tagwolke_objs as $tagwolke) {
                            if ($tagwolke->kTag > 0) {
                                $tagwolke->Klasse = ($prio_step < 1) ?
                                    rand(1, 10) :
                                    (round(($tagwolke->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / $prio_step) + 1);
                                $tagwolke->cURL = baueURL($tagwolke, URLART_TAG);
                                $Tagwolke_arr[] = $tagwolke;
                            }
                        }
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    if (count($Tagwolke_arr) > 0) {
                        $oBox->anzeigen    = 'Y';
                        $oBox->Tagbegriffe = $Tagwolke_arr;
                        shuffle($oBox->Tagbegriffe);
                        $oBox->TagbegriffeJSON = self::gibJSONString($oBox->Tagbegriffe);
                        executeHook(HOOK_BOXEN_INC_TAGWOLKE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    }
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_SUCHWOLKE :
                $oBox->compatName = 'Suchwolke';
                $nWolkenLimit     = (int)$this->boxConfig['boxen']['boxen_livesuche_count'];
                $cacheID          = 'box_search_tags_' . $currencyCachePart . $kSprache . '_' . $nWolkenLimit;
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $oSuchwolke_arr = Shop::DB()->query(
                        "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, 
                            tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
                            tsuchanfrage.dZuletztGesucht, tseo.cSeo
                            FROM tsuchanfrage
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kSuchanfrage'
                                AND tseo.kKey = tsuchanfrage.kSuchanfrage
                                AND tseo.kSprache = " . $kSprache . "
                            WHERE tsuchanfrage.kSprache = " . $kSprache . "
                                AND tsuchanfrage.nAktiv = 1
                            GROUP BY tsuchanfrage.kSuchanfrage
                            ORDER BY tsuchanfrage.nAnzahlGesuche DESC
                            LIMIT " . $nWolkenLimit, 2
                    );
                    if (is_array($oSuchwolke_arr) && ($count = count($oSuchwolke_arr)) > 0) {
                        // Priorität berechnen
                        $prio_step = ($oSuchwolke_arr[0]->nAnzahlGesuche - $oSuchwolke_arr[$count - 1]->nAnzahlGesuche) / 9;
                        foreach ($oSuchwolke_arr as $i => $oSuchwolke) {
                            if ($oSuchwolke->kSuchanfrage > 0) {
                                $oSuchwolke->Klasse = ($prio_step < 1) ?
                                    rand(1, 10) :
                                    (round(($oSuchwolke->nAnzahlGesuche - $oSuchwolke_arr[$count - 1]->nAnzahlGesuche) / $prio_step) + 1);
                                $oSuchwolke->cURL   = baueURL($oSuchwolke, URLART_LIVESUCHE);
                                $oSuchwolke_arr[$i] = $oSuchwolke;
                            }
                        }
                        $oBox->anzeigen = 'Y';
                        //hole anzuzeigende Suchwolke
                        $oBox->Suchbegriffe = $oSuchwolke_arr;
                        shuffle($oBox->Suchbegriffe);
                        $oBox->SuchbegriffeJSON = self::gibJSONString($oBox->Suchbegriffe);
                        $cacheTags              = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                        executeHook(HOOK_BOXEN_INC_SUCHWOLKE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                        Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                    }
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_IN_KUERZE_VERFUEGBAR :
                $oBox->compatName = 'ErscheinendeProdukte';
                if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen || !$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $kArtikel_arr = [];
                $limit        = (int)$this->boxConfig['boxen']['box_erscheinende_anzahl_anzeige'];
                $cacheID      = 'box_ikv_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $menge = Shop::DB()->query(
                        "SELECT tartikel.kArtikel
                            FROM tartikel
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                $this->lagerFilter
                                $this->cVaterSQL
                                AND now() < tartikel.dErscheinungsdatum
                            ORDER BY rand() LIMIT " . $limit, 2
                    );
                    if (is_array($menge)) {
                        foreach ($menge as $obj) {
                            if ($obj->kArtikel > 0) {
                                $kArtikel_arr[] = $obj->kArtikel;
                            }
                        }
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    if (count($kArtikel_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $oBox->Artikel  = new ArtikelListe();
                        $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                        $oBox->cURL = baueSuchSpecialURL(SEARCHSPECIALS_UPCOMINGPRODUCTS);
                        executeHook(HOOK_BOXEN_INC_ERSCHEINENDEPRODUKTE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    }
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_ZULETZT_ANGESEHEN :
                $oBox->compatName = 'ZuletztAngesehen';
                if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (isset($_SESSION['ZuletztBesuchteArtikel']) &&
                    is_array($_SESSION['ZuletztBesuchteArtikel']) &&
                    count($_SESSION['ZuletztBesuchteArtikel']) > 0
                ) {
                    $oTMP_arr       = [];
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($_SESSION['ZuletztBesuchteArtikel'] as $i => $oArtikel) {
                        $artikel = new Artikel();
                        $artikel->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                        if ($artikel->kArtikel > 0) {
                            $oTMP_arr[$i] = $artikel;
                        }
                    }
                    $oBox->Artikel    = array_reverse($oTMP_arr);
                    $oBox->anzeigen   = 'Y';
                    $oBox->compatName = 'ZuletztAngesehen';

                    executeHook(HOOK_BOXEN_INC_ZULETZTANGESEHEN, ['box' => $oBox]);
                }
                break;

            case BOX_TOP_ANGEBOT :
                $oBox->compatName = 'TopAngebot';
                if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $kArtikel_arr = [];
                $limit        = $this->boxConfig['boxen']['box_topangebot_anzahl_anzeige'];
                $cacheID      = 'box_top_offer_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $menge = Shop::DB()->query(
                        "SELECT tartikel.kArtikel
                            FROM tartikel
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tartikel.cTopArtikel = 'Y'
                                $this->lagerFilter
                                $this->cVaterSQL
                            ORDER BY rand() LIMIT " . $limit, 2
                    );
                    if (is_array($menge) && count($menge) > 0) {
                        foreach ($menge as $obj) {
                            if ($obj->kArtikel > 0) {
                                $kArtikel_arr[] = $obj->kArtikel;
                            }
                        }
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    if (count($kArtikel_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $oBox->Artikel  = new ArtikelListe();
                        $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                        $oBox->cURL = baueSuchSpecialURL(SEARCHSPECIALS_TOPOFFERS);
                        executeHook(HOOK_BOXEN_INC_TOPANGEBOTE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    }
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_NEUE_IM_SORTIMENT :
                $oBox->compatName = 'NeuImSortiment';
                if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $kArtikel_arr = [];
                $limit        = $this->boxConfig['boxen']['box_neuimsortiment_anzahl_anzeige'];
                $alter_tage   = 30;
                if ($this->boxConfig['boxen']['box_neuimsortiment_alter_tage'] > 0) {
                    $alter_tage = $this->boxConfig['boxen']['box_neuimsortiment_alter_tage'];
                }
                $cacheID = 'box_new_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $alter_tage . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $menge = Shop::DB()->query(
                        "SELECT tartikel.kArtikel
                            FROM tartikel
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tartikel.cNeu = 'Y'
                                $this->lagerFilter
                                $this->cVaterSQL
                                AND cNeu = 'Y' 
                                AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < dErstellt
                            ORDER BY rand() LIMIT " . $limit, 2
                    );
                    if (is_array($menge) && count($menge) > 0) {
                        foreach ($menge as $obj) {
                            if ($obj->kArtikel > 0) {
                                $kArtikel_arr[] = $obj->kArtikel;
                            }
                        }
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    if (count($kArtikel_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $oBox->Artikel  = new ArtikelListe();
                        $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                        $oBox->cURL = baueSuchSpecialURL(SEARCHSPECIALS_NEWPRODUCTS);
                        executeHook(HOOK_BOXEN_INC_NEUIMSORTIMENT, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    }
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_SONDERANGEBOT :
                $oBox->compatName = 'Sonderangebote';
                if (!$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                if (!$kKundengruppe) {
                    $oBox->anzeigen = 'N';
                    break;
                }
                $kArtikel_arr = [];
                $limit        = $this->boxConfig['boxen']['box_sonderangebote_anzahl_anzeige'];
                $cacheID      = 'box_special_offer_' . $currencyCachePart . $kKundengruppe . '_' .
                    $kSprache . '_' . $limit . md5($this->lagerFilter . $this->cVaterSQL);
                if (($oBoxCached = Shop::Cache()->get($cacheID)) === false) {
                    $menge = Shop::DB()->query(
                        "SELECT tartikel.kArtikel, tsonderpreise.fNettoPreis
                            FROM tartikel
                            JOIN tartikelsonderpreis 
                                ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                            JOIN tsonderpreise 
                                ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                AND tsonderpreise.kKundengruppe = $kKundengruppe
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= now()
                                AND (tartikelsonderpreis.dEnde >= CURDATE() 
                                    OR tartikelsonderpreis.dEnde = '0000-00-00')
                                $this->lagerFilter
                                $this->cVaterSQL
                            ORDER BY rand() LIMIT " . $limit, 2
                    );
                    if (is_array($menge) && count($menge) > 0) {
                        foreach ($menge as $obj) {
                            if ($obj->kArtikel > 0) {
                                $kArtikel_arr[] = $obj->kArtikel;
                            }
                        }
                    }
                    $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
                    if (count($kArtikel_arr) > 0) {
                        $oBox->anzeigen = 'Y';
                        $oBox->Artikel  = new ArtikelListe();
                        $oBox->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
                        $oBox->cURL = baueSuchSpecialURL(SEARCHSPECIALS_SPECIALOFFERS);
                        executeHook(HOOK_BOXEN_INC_SONDERANGEBOTE, ['box' => &$oBox, 'cache_tags' => &$cacheTags]);
                    }
                    Shop::Cache()->set($cacheID, $oBox, $cacheTags);
                } else {
                    $oBox = $oBoxCached;
                }
                break;

            case BOX_WARENKORB :
                $oBox->compatName = 'Warenkorb';
                if (isset($_SESSION['Warenkorb'], $_SESSION['Warenkorb']->PositionenArr)) {
                    $oArtikel_arr = [];
                    foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
                        $oArtikel_arr[] = $oPosition;
                    }
                    $oBox->elemente = array_reverse($oArtikel_arr);
                    $oBox->anzeigen = (count($oArtikel_arr) > 0) ? 'Y' : 'N';
                }
                break;

            case BOX_SCHNELLKAUF :
                $oBox->compatName = 'Schnellkauf';
                $oBox->anzeigen   = 'Y';
                executeHook(HOOK_BOXEN_INC_SCHNELLKAUF);
                break;

            case BOX_GLOBALE_MERKMALE :
                $oBox->compatName = 'oGlobalMerkmal_arr';
                $oBox->anzeigen   = 'Y';
                require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';
                $oBox->globaleMerkmale = $_SESSION['Kundengruppe']->darfArtikelKategorienSehen
                    ? gibSitemapGlobaleMerkmale()
                    : [];
                break;

            case BOX_LOGIN :
                $oBox->compatName = 'Login';
                $oBox->anzeigen   = 'Y';
                break;

            case BOX_KATEGORIEN :
                $oBox->compatName = 'Kategorien';
                $oBox->anzeigen   = 'Y';
                break;

            default :
                if ($oBox->eTyp === 'plugin' && !empty($oBox->kCustomID)) {
                    $_plgn           = new Plugin($oBox->kCustomID);
                    $oBox->cTemplate = $_plgn->cFrontendPfad . PFAD_PLUGIN_BOXEN . $oBox->cTemplate;
                    $oBox->oPlugin   = $_plgn;
                }
                $oBox->anzeigen = 'Y';
                break;
        }

        return $oBox;
    }

    /**
     * @param string $filename_cache
     * @param int    $timeout
     * @return bool
     */
    private function cachecheck($filename_cache, $timeout = 10800)
    {
        $filename_cache = PFAD_ROOT . PFAD_GFX_TRUSTEDSHOPS . $filename_cache;

        if (file_exists($filename_cache)) {
            $timestamp = filemtime($filename_cache);
            // Seconds
            return ((time() - $timestamp) < $timeout);
        }

        return false;
    }

    /**
     * @return string
     */
    public function render()
    {
        $smarty          = Shop::Smarty();
        $originalArticle = $smarty->getTemplateVars('Artikel');
        if (isset($_SESSION['Kundengruppe']->nNettoPreise)) {
            $smarty->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise);
        }
        //check whether filters should be displayed after a box
        $filterAfter = (!empty($this->boxConfig) && isset($GLOBALS['NaviFilter']) && isset($GLOBALS['oSuchergebnisse']))
            ? $this->gibBoxenFilterNach(Shop::$NaviFilter, $GLOBALS['oSuchergebnisse'])
            : 0;
        $path              = 'boxes/';
        $this->lagerFilter = gibLagerfilter();
        $htmlArray         = [
            'top'    => null,
            'right'  => null,
            'bottom' => null,
            'left'   => null
        ];
        $smarty->assign('BoxenEinstellungen', $this->boxConfig)
               ->assign('bBoxenFilterNach', $filterAfter);
        foreach ($this->boxes as $_position => $_boxes) {
            $htmlArray[$_position]     = '';
            $this->rawData[$_position] = [];
            if (is_array($_boxes)) {
                foreach ($_boxes as $_box) {
                    if (!empty($_box->cFilter)) {
                        $pageType   = (int)$_box->kSeite;
                        $allowedIDs = explode(',', $_box->cFilter);
                        if ($pageType === PAGE_ARTIKELLISTE) {
                            if (!in_array(Shop::$kKategorie, $allowedIDs)) {
                                continue;
                            }
                        } elseif ($pageType === PAGE_ARTIKEL) {
                            if (!in_array(Shop::$kArtikel, $allowedIDs)) {
                                continue;
                            }
                        } elseif ($pageType === PAGE_EIGENE) {
                            if (!in_array(Shop::$kLink, $allowedIDs)) {
                                continue;
                            }
                        } elseif ($pageType === PAGE_HERSTELLER) {
                            if (!in_array(Shop::$kHersteller, $allowedIDs)) {
                                continue;
                            }
                        }
                    }
                    if (isset($_box->bContainer) && $_box->bContainer === true) {
                        //prepare boxes within a container
                        $_box->cTemplate = 'box_container.tpl';
                        $_box->innerHTML = '';
                        $_box->children  = [];
                        foreach ($_box->oContainer_arr as $_cbox) {
                            $_cbox = $this->prepareBox($_cbox->kBoxvorlage, $_cbox);
                            if ($_cbox->eTyp === 'link') {
                                $linkGroup = $this->getLinkGroupByID($_cbox->kCustomID);
                                if ($linkGroup !== null) {
                                    $_cbox->oLinkGruppe         = $linkGroup['grp'];
                                    $_cbox->oLinkGruppeTemplate = $linkGroup['tpl'];
                                    $smarty->assign('oBox', $_cbox);
                                } else {
                                    continue;
                                }
                            } else {
                                $smarty->assign('oBox', $_cbox);
                            }
                            if (!empty($_cbox->cTemplate)) {
                                $_box->innerHTML .= trim(($_cbox->eTyp === 'plugin')
                                    ? $smarty->fetch($_cbox->cTemplate)
                                    : $smarty->fetch($path . $_cbox->cTemplate));
                            }
                            $_box->children[] = ['obj' => $_cbox, 'tpl' => $path . $_cbox->cTemplate];
                        }
                    }
                    $_box = $this->prepareBox($_box->kBoxvorlage, $_box);
                    if ($_box->eTyp === 'link') {
                        $linkGroup = $this->getLinkGroupByID($_box->kCustomID);
                        if ($linkGroup !== null) {
                            $_box->oLinkGruppe         = $linkGroup['grp'];
                            $_box->oLinkGruppeTemplate = $linkGroup['tpl'];
                            $smarty->assign('oBox', $_box);
                        } else {
                            continue;
                        }
                    } else {
                        $smarty->assign('oBox', $_box);
                    }
                    $this->rawData[$_position][] = ['obj' => $_box, 'tpl' => ($_box->eTyp === 'plugin')
                        ? $_box->cTemplate
                        : ($path . $_box->cTemplate)];
                    $_oldPlugin                  = null;
                    if ($_box->eTyp === 'plugin') {
                        $_oldPlugin = $smarty->getTemplateVars('oPlugin');
                        $smarty->assign('oPlugin', $_box->oPlugin);
                    }
                    if (!empty($_box->cTemplate)) {
                        $htmlArray[$_position] .= trim(($_box->eTyp === 'plugin')
                            ? $smarty->fetch($_box->cTemplate)
                            : $smarty->fetch($path . $_box->cTemplate));
                    }
                    if ($_oldPlugin !== null) {
                        $smarty->assign('oPlugin', $_oldPlugin);
                    }
                }
            }
        }
        if ($originalArticle !== null) {
            //avoid modification of article object on render loop
            $smarty->assign('Artikel', $originalArticle);
        }

        return $htmlArray;
    }

    /**
     * @param int $kArtikel
     * @param int $nMaxAnzahl
     */
    public function addRecentlyViewed($kArtikel, $nMaxAnzahl = null)
    {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0) {
            if ($nMaxAnzahl === null) {
                $nMaxAnzahl = (int)$this->boxConfig['boxen']['box_zuletztangesehen_anzahl'];
            }
            if (!isset($_SESSION['ZuletztBesuchteArtikel']) || !is_array($_SESSION['ZuletztBesuchteArtikel'])) {
                $_SESSION['ZuletztBesuchteArtikel'] = [];
            }
            $oArtikel           = new stdClass();
            $oArtikel->kArtikel = $kArtikel;
            if (isset($_SESSION['ZuletztBesuchteArtikel']) && count($_SESSION['ZuletztBesuchteArtikel']) > 0) {
                $alreadyPresent = false;
                foreach ($_SESSION['ZuletztBesuchteArtikel'] as $_article) {
                    if (isset($_article->kArtikel) && $_article->kArtikel === $oArtikel->kArtikel) {
                        $alreadyPresent = true;
                        break;
                    }
                }
                if ($alreadyPresent === false) {
                    if (count($_SESSION['ZuletztBesuchteArtikel']) < $nMaxAnzahl) {
                        $_SESSION['ZuletztBesuchteArtikel'][] = $oArtikel;
                    } else {
                        $oTMP_arr = array_reverse($_SESSION['ZuletztBesuchteArtikel']);
                        array_pop($oTMP_arr);
                        $oTMP_arr                           = array_reverse($oTMP_arr);
                        $oTMP_arr[]                         = $oArtikel;
                        $_SESSION['ZuletztBesuchteArtikel'] = $oTMP_arr;
                    }
                }
            } else {
                $_SESSION['ZuletztBesuchteArtikel'][] = $oArtikel;
            }
            executeHook(HOOK_ARTIKEL_INC_ZULETZTANGESEHEN);
        }
    }

    /**
     * @param int $kSeite
     * @return string
     */
    public function mappekSeite($kSeite)
    {
        switch ($kSeite) {
            default:
            case PAGE_UNBEKANNT:
                return 'Unbekannt';
            case PAGE_ARTIKEL:
                return 'Artikeldetails';
            case PAGE_ARTIKELLISTE:
                return 'Artikelliste';
            case PAGE_WARENKORB:
                return 'Warenkorb';
            case PAGE_MEINKONTO:
                return 'Mein Konto';
            case PAGE_KONTAKT:
                return 'Kontakt';
            case PAGE_UMFRAGE:
                return 'Umfrage';
            case PAGE_NEWS:
                return 'News';
            case PAGE_NEWSLETTER:
                return 'Newsletter';
            case PAGE_LOGIN:
                return 'Login';
            case PAGE_REGISTRIERUNG:
                return 'Registrierung';
            case PAGE_BESTELLVORGANG:
                return 'Bestellvorgang';
            case PAGE_BEWERTUNG:
                return 'Bewertung';
            case PAGE_DRUCKANSICHT:
                return 'Druckansicht';
            case PAGE_PASSWORTVERGESSEN:
                return 'Passwort vergessen';
            case PAGE_WARTUNG:
                return 'Wartung';
            case PAGE_WUNSCHLISTE:
                return 'Wunschliste';
            case PAGE_VERGLEICHSLISTE:
                return 'Vergleichsliste';
            case PAGE_STARTSEITE:
                return 'Startseite';
            case PAGE_VERSAND:
                return 'Versand';
            case PAGE_AGB:
                return 'AGB';
            case PAGE_DATENSCHUTZ:
                return 'Datenschutz';
            case PAGE_TAGGING:
                return 'Tagging';
            case PAGE_LIVESUCHE:
                return 'Livesuche';
            case PAGE_HERSTELLER:
                return 'Hersteller';
            case PAGE_SITEMAP:
                return 'Sitemap';
            case PAGE_GRATISGESCHENK:
                return 'Gratis Geschenk';
            case PAGE_WRB:
                return 'WRB';
            case PAGE_PLUGIN:
                return 'Plugin';
            case PAGE_NEWSLETTERARCHIV:
                return 'Newsletterarchiv';
            case PAGE_EIGENE:
                return 'Eigene Seite';
            case PAGE_AUSWAHLASSISTENT:
                return 'Auswahlassistent';
            case PAGE_BESTELLABSCHLUSS:
                return 'Bestellabschluss';
            case PAGE_RMA:
                return 'Warenr&uuml;cksendung';
        }
    }

    /**
     * @param int  $nSeite
     * @param bool $bGlobal
     * @return array|bool
     */
    public function holeBoxAnzeige($nSeite, $bGlobal = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $nSeite      = (int)$nSeite;
        $oBoxAnzeige = [];
        $oBox_arr    = Shop::DB()->selectAll('tboxenanzeige', 'nSeite', $nSeite);
        if (is_array($oBox_arr) && count($oBox_arr)) {
            foreach ($oBox_arr as $oBox) {
                $oBoxAnzeige[$oBox->ePosition] = (boolean)$oBox->bAnzeigen;
            }
            $this->visibility = $oBoxAnzeige;

            return $oBoxAnzeige;
        }

        if ($nSeite !== 0 && $bGlobal) {
            return $this->holeBoxAnzeige(0);
        }

        return false;
    }

    /**
     * @param int      $nSeite
     * @param string   $ePosition
     * @param bool|int $bAnzeigen
     * @return bool
     */
    public function setzeBoxAnzeige($nSeite, $ePosition, $bAnzeigen)
    {
        $bAnzeigen      = (int)$bAnzeigen;
        $nSeite         = (int)$nSeite;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $bOk = Shop::DB()->executeQueryPrepared("
                  REPLACE INTO tboxenanzeige 
                      SET bAnzeigen = :show,
                          nSeite = :page, 
                          ePosition = :position",
                        ['show' => $bAnzeigen, 'page' => $i, 'position' => $ePosition],
                        4
                    ) && $bOk;
            }

            return $bOk;
        }

        return Shop::DB()->executeQueryPrepared("
            REPLACE INTO tboxenanzeige 
                SET bAnzeigen = :show, 
                    nSeite = :page, 
                    ePosition = :position",
            ['show' => $bAnzeigen, 'page' => $nSeite, 'position' => $ePosition],
            4
        );
    }

    /**
     * @param int $kBoxvorlage
     * @return mixed
     */
    public function holeVorlage($kBoxvorlage)
    {
        return Shop::DB()->select('tboxvorlage', 'kBoxvorlage', (int)$kBoxvorlage);
    }

    /**
     * @param string $ePosition
     * @return mixed
     */
    public function holeContainer($ePosition)
    {
        return Shop::DB()->selectAll('tboxen', ['kBoxvorlage', 'ePosition'], [0, $ePosition], 'kBox', 'kBox ASC');
    }

    /**
     * @param int    $kBoxvorlage
     * @param int    $nSeite
     * @param string $ePosition
     * @param int    $kContainer
     * @return bool
     */
    public function setzeBox($kBoxvorlage, $nSeite, $ePosition = 'left', $kContainer = 0)
    {
        $kBoxvorlage    = (int)$kBoxvorlage;
        $nSeite         = (int)$nSeite;
        $validPageTypes = $this->getValidPageTypes();
        $oBox           = new stdClass();
        $oBoxVorlage    = $this->holeVorlage($kBoxvorlage);
        $oBox->cTitel   = '';
        if ($oBoxVorlage) {
            $oBox->cTitel = $oBoxVorlage->cName;
        }

        $oBox->kBoxvorlage = $kBoxvorlage;
        $oBox->ePosition   = $ePosition;
        $oBox->kContainer  = $kContainer;
        $oBox->kCustomID   = (isset($oBoxVorlage->kCustomID) && is_numeric($oBoxVorlage->kCustomID))
            ? (int)$oBoxVorlage->kCustomID
            : 0;

        $kBox = Shop::DB()->insert('tboxen', $oBox);
        if ($kBox) {
            $oBoxSichtbar       = new stdClass();
            $oBoxSichtbar->kBox = $kBox;
            for ($i = 0; $i < count($validPageTypes); $i++) {
                $oBoxSichtbar->nSort  = $this->letzteSortierID($nSeite, $ePosition, $kContainer);
                $oBoxSichtbar->kSeite = $i;
                $oBoxSichtbar->bAktiv = ($nSeite == $i || $nSeite == 0) ? 1 : 0;
                Shop::DB()->insert('tboxensichtbar', $oBoxSichtbar);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $kBox
     * @return mixed
     */
    public function holeBox($kBox)
    {
        $kBox = (int)$kBox;
        $oBox = Shop::DB()->query(
            "SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = " . $kBox, 1
        );

        if ($oBox && ($oBox->eTyp === 'text' || $oBox->eTyp === 'catbox')) {
            $oBox->oSprache_arr = $this->gibBoxInhalt($kBox);
        }

        return $oBox;
    }

    /**
     * @param int    $kBox
     * @param string $cTitel
     * @param int    $kCustomID
     * @return mixed
     */
    public function bearbeiteBox($kBox, $cTitel, $kCustomID = 0)
    {
        $oBox            = new stdClass();
        $oBox->cTitel    = $cTitel;
        $oBox->kCustomID = $kCustomID;

        return Shop::DB()->update('tboxen', 'kBox', (int)$kBox, $oBox) >= 0;
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @param string $cTitel
     * @param string $cInhalt
     * @return bool
     */
    public function bearbeiteBoxSprache($kBox, $cISO, $cTitel, $cInhalt)
    {
        $kBox    = (int)$kBox;
        $oBox    = Shop::DB()->select('tboxsprache', 'kBox', $kBox, 'cISO', $cISO);
        if (isset($oBox->kBox)) {
            $_upd          = new stdClass();
            $_upd->cTitel  = $cTitel;
            $_upd->cInhalt = $cInhalt;

            return Shop::DB()->update('tboxsprache', ['kBox', 'cISO'], [$kBox, $cISO], $_upd) >= 0;
        }
        $_ins          = new stdClass();
        $_ins->kBox    = $kBox;
        $_ins->cISO    = $cISO;
        $_ins->cTitel  = $cTitel;
        $_ins->cInhalt = $cInhalt;

        return Shop::DB()->insert('tboxsprache', $_ins) > 0;
    }

    /**
     * @param int    $nSeite
     * @param string $ePosition
     * @param int    $kContainer
     * @return int
     */
    public function letzteSortierID($nSeite, $ePosition = 'left', $kContainer = 0)
    {
        if ($kContainer === null) {
            $kContainer = 0;
        }
        $oBox = Shop::DB()->query(
            "SELECT tboxensichtbar.nSort, tboxen.ePosition
                FROM tboxensichtbar
                LEFT JOIN tboxen
                    ON tboxensichtbar.kBox = tboxen.kBox
                    WHERE tboxensichtbar.kSeite = " . (int)$nSeite . "
                        AND tboxen.ePosition = '" . $ePosition . "'
                        AND tboxen.kContainer = " . (int)$kContainer . "
                ORDER BY tboxensichtbar.nSort DESC LIMIT 1", 1
        );

        return $oBox ? ++$oBox->nSort : 0;
    }

    /**
     * @param int $kBox
     * @param int $kSeite
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility($kBox, $kSeite, $cFilter = '')
    {
        if (is_array($cFilter)) {
            $cFilter = array_unique($cFilter);
            $cFilter = implode(',', $cFilter);
        }

        $_upd           = new stdClass();
        $_upd->cFilter  = $cFilter;

        return Shop::DB()->update('tboxensichtbar', ['kBox', 'kSeite'], [(int)$kBox, (int)$kSeite], $_upd);
    }

    /**
     * @param int      $kBox
     * @param int      $nSeite
     * @param int      $nSort
     * @param bool|int $bAktiv
     * @return bool
     */
    public function sortBox($kBox, $nSeite, $nSort, $bAktiv = true)
    {
        $bAktiv         = (int)$bAktiv;
        $kBox           = (int)$kBox;
        $nSeite         = (int)$nSeite;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $oBox = Shop::DB()->select('tboxensichtbar', 'kBox', $kBox);
                $bOk  = (!empty($oBox))
                    ? (Shop::DB()->query("
                        UPDATE tboxensichtbar 
                            SET nSort = " . $nSort . ",
                                bAktiv = " . $bAktiv . " 
                            WHERE kBox = " . $kBox . " 
                                AND kSeite = " . $i, 4
                        ) !== false)
                    : (Shop::DB()->query("
                        INSERT INTO tboxensichtbar 
                            SET kBox = " . $kBox . ",
                                kSeite = " . $i . ", 
                                nSort = " . $nSort . ", 
                                bAktiv = " . $bAktiv, 4
                        ) != false);
            }

            return $bOk;
        }

        return Shop::DB()->query("
            REPLACE INTO tboxensichtbar 
              SET kBox = " . $kBox . ", 
                  kSeite = " . $nSeite . ", 
                  nSort = " . $nSort . ", 
                  bAktiv = " . $bAktiv, 3
            ) !== false;
    }

    /**
     * @param int      $kBox
     * @param int      $nSeite
     * @param bool|int $bAktiv
     * @return bool
     */
    public function aktiviereBox($kBox, $nSeite, $bAktiv = true)
    {
        $bAktiv         = (int)$bAktiv;
        $kBox           = (int)$kBox;
        $nSeite         = (int)$nSeite;
        $validPageTypes = $this->getValidPageTypes();
        if ($nSeite === 0) {
            $bOk = true;
            for ($i = 0; $i < count($validPageTypes) && $bOk; $i++) {
                $_upd          = new stdClass();
                $_upd->bAktiv  = $bAktiv;
                $bOk           = Shop::DB()->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, $i], $_upd) >= 0;
            }

            return $bOk;
        }
        $_upd          = new stdClass();
        $_upd->bAktiv  = $bAktiv;

        return Shop::DB()->update('tboxensichtbar', ['kBox', 'kSeite'], [$kBox, 0], $_upd) >= 0;
    }

    /**
     * @param int $kBox
     * @return bool
     */
    public function loescheBox($kBox)
    {
        $kBox = (int)$kBox;
        $bOk  = Shop::DB()->delete('tboxen', 'kBox', $kBox) > 0;

        return $bOk
            ? (Shop::DB()->delete('tboxensichtbar', 'kBox', $kBox) > 0)
            : false;
    }

    /**
     * @return array
     */
    public function gibLinkGruppen()
    {
        return Shop::DB()->query("SELECT * FROM tlinkgruppe", 2);
    }

    /**
     * @param int $kBoxvorlage
     * @return bool
     */
    public function isVisible($kBoxvorlage)
    {
        foreach ($this->boxes as $_position => $_boxes) {
            foreach ($_boxes as $_box) {
                if ((int)$_box->kBoxvorlage === (int)$kBoxvorlage) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param stdClass $NaviFilter
     * @param stdClass $oSuchergebnisse
     * @return bool
     */
    public function gibBoxenFilterNach($NaviFilter, $oSuchergebnisse)
    {
        $conf = Shop::getSettings([CONF_GLOBAL]);

        return ((isset($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0 &&
                $this->boxConfig['navigationsfilter']['allgemein_kategoriefilter_benutzen'] === 'Y')
            || (isset($NaviFilter->HerstellerFilter->kHersteller) && $NaviFilter->HerstellerFilter->kHersteller > 0 &&
                $this->boxConfig['navigationsfilter']['allgemein_herstellerfilter_benutzen'] === 'Y')
            || (isset($NaviFilter->PreisspannenFilter->fBis) && ($NaviFilter->PreisspannenFilter->fVon >= 0 &&
                    $NaviFilter->PreisspannenFilter->fBis > 0) &&
                $this->boxConfig['navigationsfilter']['preisspannenfilter_benutzen'] !== 'N' &&
                $conf['global']['global_sichtbarkeit'] == 1)
            || (isset($NaviFilter->BewertungFilter->nSterne) && $NaviFilter->BewertungFilter->nSterne > 0 &&
                $this->boxConfig['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N')
            || (isset($NaviFilter->TagFilter) &&
                count($NaviFilter->TagFilter) > 0 &&
                $this->boxConfig['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y')
            || (isset($oSuchergebnisse->MerkmalFilter) &&
                count($oSuchergebnisse->MerkmalFilter) > 0 &&
                $this->boxConfig['navigationsfilter']['merkmalfilter_verwenden'] === 'box')
            || (isset($NaviFilter->MerkmalFilter) &&
                count($NaviFilter->MerkmalFilter) > 0 &&
                $this->boxConfig['navigationsfilter']['merkmalfilter_verwenden'] === 'box')
            || (isset($oSuchergebnisse->Bewertung) &&
                count($oSuchergebnisse->Bewertung) > 0 &&
                $this->boxConfig['navigationsfilter']['bewertungsfilter_benutzen'] === 'box')
            || (isset($oSuchergebnisse->Preisspanne) &&
                count($oSuchergebnisse->Preisspanne) > 0 &&
                $this->boxConfig['navigationsfilter']['preisspannenfilter_benutzen'] === 'box' &&
                $conf['global']['global_sichtbarkeit'] == 1)
            || (isset($oSuchergebnisse->Suchspecialauswahl) &&
                count($oSuchergebnisse->Suchspecialauswahl) > 0 &&
                $this->boxConfig['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] === 'Y')
            || (isset($NaviFilter->SuchspecialFilter->kKey) &&
                $NaviFilter->SuchspecialFilter->kKey > 0 &&
                $this->boxConfig['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] === 'Y')
            || (isset($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0 &&
                $this->boxConfig['navigationsfilter']['suchtrefferfilter_nutzen'] === 'Y')
        );
    }

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     */
    public function compatGet()
    {
        $boxes = [];
        foreach ($this->rawData as $_type => $_boxes) {
            $boxes[$_type] = [];
            foreach ($_boxes as $_box) {
                $boxes[$_type][] = $_box['obj'];
            }
        }
        if (TEMPLATE_COMPATIBILITY === true) {
            $boxen = [];
            foreach ($this->boxes as $_position => $_boxes) {
                foreach ($_boxes as $_box) {
                    $_box = $this->prepareBox($_box->kBoxvorlage, $_box);
                    if (isset($_box->compatName)) {
                        $boxen[$_box->compatName] = ($_box->compatName === 'oGlobalMerkmal_arr') ?
                            $_box->globaleMerkmale :
                            $_box;
                    }
                }
            }
            Shop::Smarty()->assign('Boxen', $boxen);
        }

        return $boxes;
    }

    /**
     * special json string for sidebar clouds
     *
     * @param array  $oCloud_arr
     * @param string $nSpeed
     * @param string $nOpacity
     * @param bool   $cColor
     * @param bool   $cColorHover
     * @return string
     */
    public static function gibJSONString($oCloud_arr, $nSpeed = '1', $nOpacity = '0.2', $cColor = false, $cColorHover = false)
    {
        $iCur = 0;
        $iMax = 15;
        if (!count($oCloud_arr)) {
            return '';
        }
        $oTags_arr                       = [];
        $oTags_arr['options']['speed']   = $nSpeed;
        $oTags_arr['options']['opacity'] = $nOpacity;
        $gibTagFarbe                     = function () {
            $cColor = '';
            $cCodes = ['00', '33', '66', '99', 'CC', 'FF'];
            for ($i = 0; $i < 3; $i++) {
                $cColor .= $cCodes[rand(0, count($cCodes) - 1)];
            }

            return '0x' . $cColor;
        };

        foreach ($oCloud_arr as $oCloud) {
            if ($iCur++ >= $iMax) {
                break;
            }
            $cName               = isset($oCloud->cName) ? $oCloud->cName : $oCloud->cSuche;
            $cRandomColor        = (!$cColor || !$cColorHover) ? $gibTagFarbe() : '';
            $cName               = urlencode($cName);
            $cName               = str_replace('+', ' ', $cName); /* fix :) */
            $oTags_arr['tags'][] = [
                'name'  => $cName,
                'url'   => $oCloud->cURL,
                'size'  => (count($oCloud_arr) <= 5) ? '100' : (string)($oCloud->Klasse * 10), /* 10 bis 100 */
                'color' => $cColor ?: $cRandomColor,
                'hover' => $cColorHover ?: $cRandomColor
            ];
        }
        return urlencode(json_encode($oTags_arr));
    }

    /**
     * get classname for sidebar panels
     *
     * @return string
     */
    public function getClass()
    {
        $class = '';
        $i     = 0;
        foreach ($this->boxes as $position => $_boxes) {
            if ($_boxes !== null) {
                $class .= (($i !== 0) ? ' ' : '') . 'panel_' . $position;
            }
            ++$i;
        }

        return $class;
    }

    /**
     * @return array
     */
    public function getInvisibleBoxes()
    {
        $tpl            = Template::getInstance();
        $layout         = $tpl->getBoxLayoutXML();
        $invisibleBoxes = [];
        foreach ($layout as $position => $isAvailable) {
            if ($isAvailable === false) {
                $box = Shop::DB()->select('tboxen', 'ePosition', $position);
                if ($box !== null && isset($box->kBox)) {
                    $boxes = Shop::DB()->query("
                        SELECT tboxen.*, tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cTemplate 
                            FROM tboxen 
                                LEFT JOIN tboxvorlage
                                ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                            WHERE ePosition = '" . $position . "'", 2
                    );
                    foreach ($boxes as $box) {
                        $invisibleBoxes[] = $box;
                    }
                }
            }
        }

        return $invisibleBoxes;
    }

    /**
     * @return array
     */
    public function getValidPageTypes()
    {
        $validPageTypes = [
            PAGE_UNBEKANNT, PAGE_ARTIKEL, PAGE_ARTIKELLISTE, PAGE_WARENKORB, PAGE_MEINKONTO,
            PAGE_KONTAKT, PAGE_UMFRAGE, PAGE_NEWS, PAGE_NEWSLETTER, PAGE_LOGIN, PAGE_REGISTRIERUNG, PAGE_BESTELLVORGANG,
            PAGE_BEWERTUNG, PAGE_DRUCKANSICHT, PAGE_PASSWORTVERGESSEN, PAGE_WARTUNG, PAGE_WUNSCHLISTE,
            PAGE_VERGLEICHSLISTE, PAGE_STARTSEITE, PAGE_VERSAND, PAGE_AGB, PAGE_DATENSCHUTZ, PAGE_TAGGING,
            PAGE_LIVESUCHE, PAGE_HERSTELLER, PAGE_SITEMAP, PAGE_GRATISGESCHENK, PAGE_WRB, PAGE_PLUGIN,
            PAGE_NEWSLETTERARCHIV, PAGE_NEWSARCHIV, PAGE_EIGENE, PAGE_AUSWAHLASSISTENT, PAGE_BESTELLABSCHLUSS,
            PAGE_RMA
        ];

        return $validPageTypes;
    }
}
