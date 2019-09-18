<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES . 'interface.JTL-Shop.ExtensionPoint.php';

/**
 * Class ImageMap
 */
class ImageMap implements IExtensionPoint
{
    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     *
     */
    public function __construct()
    {
        $this->kSprache      = Shop::getLanguage();
        $this->kKundengruppe = (isset($_SESSION['Kundengruppe']->kKundengruppe) ? $_SESSION['Kundengruppe']->kKundengruppe : null);
        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
            $this->kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        }
    }

    /**
     * @param int  $kInitial
     * @param bool $fetch_all
     * @return $this
     */
    public function init($kInitial, $fetch_all = false)
    {
        $oImageMap = $this->fetch($kInitial, $fetch_all);
        if (is_object($oImageMap)) {
            $smarty = Shop::Smarty();
            $smarty->assign('oImageMap', $oImageMap);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function fetchAll()
    {
        return Shop::DB()->query("SELECT * FROM timagemap", 2);
    }

    /**
     * @param int  $kImageMap
     * @param bool $fetch_all
     * @param bool $fill
     * @return mixed
     */
    public function fetch($kImageMap, $fetch_all = false, $fill = true)
    {
        $kImageMap = (int)$kImageMap;
        $cSQL      = "SELECT *
                FROM timagemap
                WHERE kImageMap = " . $kImageMap;
        if (!$fetch_all) {
            // vDate und bDate sollten immer '0000-00-00 00:00:00' statt NULL enthalten
            $cSQL .= " AND (CURDATE() >= DATE(vDatum)) AND (CURDATE() <= DATE(bDatum) OR bDatum = 0)";
        }
        $oImageMap = Shop::DB()->query($cSQL, 1);
        if (!is_object($oImageMap)) {
            return false;
        }

        $oImageMap->oArea_arr = Shop::DB()->selectAll('timagemaparea', 'kImageMap', (int)$oImageMap->kImageMap);
        $cBildPfad            = PFAD_ROOT . PFAD_IMAGEMAP . $oImageMap->cBildPfad;
        $oImageMap->cBildPfad = Shop::getURL() . '/' . PFAD_IMAGEMAP . $oImageMap->cBildPfad;
        $cParse_arr           = parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = substr($cParse_arr['path'], strrpos($cParse_arr['path'], '/') + 1);
        list($width, $height) = getimagesize($cBildPfad);
        $oImageMap->fWidth    = $width;
        $oImageMap->fHeight   = $height;
        $defaultOptions       = Artikel::getDefaultOptions();

        foreach ($oImageMap->oArea_arr as &$oArea) {
            $oArea->oCoords = new stdClass();
            $aMap           = explode(',', $oArea->cCoords);
            if (count($aMap) === 4) {
                $oArea->oCoords->x = (int)$aMap[0];
                $oArea->oCoords->y = (int)$aMap[1];
                $oArea->oCoords->w = (int)$aMap[2];
                $oArea->oCoords->h = (int)$aMap[3];
            }

            $oArea->oArtikel = null;
            if ((int)$oArea->kArtikel > 0) {
                $oArea->oArtikel = new Artikel();
                if ($fill === true) {
                    $oArea->oArtikel->fuelleArtikel(
                        $oArea->kArtikel,
                        $defaultOptions,
                        $this->kKundengruppe,
                        $this->kSprache
                    );
                } else {
                    $oArea->oArtikel->kArtikel = $oArea->kArtikel;
                    $oArea->oArtikel->cName    = utf8_encode(
                        Shop::DB()->select(
                            'tartikel', 'kArtikel', $oArea->kArtikel, null, null, null, null, false, 'cName'
                        )->cName
                    );
                }
                if (strlen($oArea->cTitel) === 0) {
                    $oArea->cTitel = $oArea->oArtikel->cName;
                }
                if (strlen($oArea->cUrl) === 0) {
                    $oArea->cUrl = $oArea->oArtikel->cURL;
                }
                if (strlen($oArea->cBeschreibung) === 0) {
                    $oArea->cBeschreibung = $oArea->oArtikel->cKurzBeschreibung;
                }
            }
        }

        return $oImageMap;
    }

    /**
     * @param string $cTitel
     * @param string $cBildPfad
     * @param string $vDatum
     * @param string $bDatum
     * @return mixed
     */
    public function save($cTitel, $cBildPfad, $vDatum, $bDatum)
    {
        $oData            = new stdClass();
        $oData->cTitel    = Shop::DB()->escape($cTitel);
        $oData->cBildPfad = Shop::DB()->escape($cBildPfad);
        $oData->vDatum    = $vDatum;
        $oData->bDatum    = $bDatum;

        return Shop::DB()->insert('timagemap', $oData);
    }

    /**
     * @param int    $kImageMap
     * @param string $cTitel
     * @param string $cBildPfad
     * @param string $vDatum
     * @param string $bDatum
     * @return mixed
     */
    public function update($kImageMap, $cTitel, $cBildPfad, $vDatum, $bDatum)
    {
        $cTitel    = Shop::DB()->escape($cTitel);
        $cBildPfad = Shop::DB()->escape($cBildPfad);

        if (empty($vDatum)) {
            $vDatum = '0000-00-00 00:00:00';
        }
        if (empty($bDatum)) {
            $bDatum = '0000-00-00 00:00:00';
        }
        $_upd            = new stdClass();
        $_upd->cTitel    = $cTitel;
        $_upd->cBildPfad = $cBildPfad;
        $_upd->vDatum    = $vDatum;
        $_upd->bDatum    = $bDatum;

        return Shop::DB()->update('timagemap', 'kImageMap', (int)$kImageMap, $_upd) >= 0;
    }

    /**
     * @param int $kImageMap
     * @return mixed
     */
    public function delete($kImageMap)
    {
        return Shop::DB()->delete('timagemap', 'kImageMap', (int)$kImageMap) >= 0;
    }

    /**
     * @param stdClass $oData
     */
    public function saveAreas($oData)
    {
        Shop::DB()->delete('timagemaparea', 'kImageMap', (int)$oData->kImageMap);
        foreach ($oData->oArea_arr as $oArea) {
            $oTmp                = new stdClass();
            $oTmp->kImageMap     = $oArea->kImageMap;
            $oTmp->kArtikel      = $oArea->kArtikel;
            $oTmp->cStyle        = $oArea->cStyle;
            $oTmp->cTitel        = $oArea->cTitel;
            $oTmp->cUrl          = $oArea->cUrl;
            $oTmp->cBeschreibung = $oArea->cBeschreibung;
            $oTmp->cCoords       = "{$oArea->oCoords->x},{$oArea->oCoords->y},{$oArea->oCoords->w},{$oArea->oCoords->h}";

            Shop::DB()->insert('timagemaparea', $oTmp);
        }
    }
}
