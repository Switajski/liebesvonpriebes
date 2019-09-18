<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Bewertung
 */
class Bewertung
{
    /**
     * @var array
     */
    public $oBewertung_arr;

    /**
     * @var array
     */
    public $nSterne_arr;

    /**
     * @var int
     */
    public $nAnzahlSprache;

    /**
     * @var object
     */
    public $oBewertungGesamt;

    /**
     * @param int    $kArtikel
     * @param int    $kSprache
     * @param int    $nAnzahlSeite
     * @param int    $nSeite
     * @param int    $nSterne
     * @param string $cFreischalten
     * @param int    $nOption
     * @param bool   $bAlleSprachen
     */
    public function __construct($kArtikel, $kSprache, $nAnzahlSeite = -1, $nSeite = 1, $nSterne = 0, $cFreischalten = 'N', $nOption = 0, $bAlleSprachen = false)
    {
        if (!$kSprache) {
            $kSprache = $_SESSION['kSprache'];
        }
        $kArtikel     = (int)$kArtikel;
        $kSprache     = (int)$kSprache;
        $nAnzahlSeite = (int)$nAnzahlSeite;
        $nSeite       = (int)$nSeite;
        $nSterne      = (int)$nSterne;
        if ($nOption == 1) { // Hilfreich holen
            $this->holeHilfreichsteBewertung($kArtikel, $kSprache);
        } else {
            $this->holeProduktBewertungen(
                $kArtikel,
                $kSprache,
                $nAnzahlSeite,
                $nSeite,
                $nSterne,
                $cFreischalten,
                $nOption,
                $bAlleSprachen
            );
        }
    }

    /**
     * @param int $kArtikel
     * @param int $kSprache
     * @return $this
     */
    public function holeHilfreichsteBewertung($kArtikel, $kSprache)
    {
        $this->oBewertung_arr = [];
        if ($kArtikel > 0 && $kSprache > 0) {
            $oBewertungHilfreich = Shop::DB()->query(
                "SELECT *, DATE_FORMAT(dDatum, '%d.%m.%Y') AS Datum,
                        DATE_FORMAT(dAntwortDatum, '%d.%m.%Y') AS AntwortDatum
                    FROM tbewertung
                    WHERE kSprache = " . (int)$kSprache . "
                        AND kArtikel = " . (int)$kArtikel . "
                        AND nAktiv = 1
                    ORDER BY nHilfreich DESC
                    LIMIT 1", 1
            );
            if (!empty($oBewertungHilfreich)) {
                $oBewertungHilfreich->nAnzahlHilfreich = $oBewertungHilfreich->nHilfreich + $oBewertungHilfreich->nNichtHilfreich;
            }

            executeHook(HOOK_BEWERTUNG_CLASS_HILFREICHSTEBEWERTUNG);
            $this->oBewertung_arr[] = $oBewertungHilfreich;
        }

        return $this;
    }

    /**
     * @param int    $kArtikel
     * @param int    $kSprache
     * @param int    $nAnzahlSeite
     * @param int    $nSeite
     * @param int    $nSterne
     * @param string $cFreischalten
     * @param int    $nOption
     * @param bool   $bAlleSprachen
     * @return $this
     */
    public function holeProduktBewertungen($kArtikel, $kSprache, $nAnzahlSeite, $nSeite = 1, $nSterne = 0, $cFreischalten = 'N', $nOption = 0, $bAlleSprachen = false)
    {
        $kArtikel             = (int)$kArtikel;
        $kSprache             = (int)$kSprache;
        $nAnzahlSeite         = (int)$nAnzahlSeite;
        $nSeite               = (int)$nSeite;
        $nSterne              = (int)$nSterne;
        $this->oBewertung_arr = [];
        if ($kArtikel > 0 && $kSprache > 0) {
            $oBewertungAnzahl_arr = [];
            $cSQL                 = '';
            // Sortierung beachten
            switch ($nOption) {
                case 2:
                    $cOrderSQL = ' dDatum DESC';
                    break;
                case 3:
                    $cOrderSQL = ' dDatum ASC';
                    break;
                case 4:
                    $cOrderSQL = ' nSterne DESC';
                    break;
                case 5:
                    $cOrderSQL = ' nSterne ASC';
                    break;
                case 6:
                    $cOrderSQL = ' nHilfreich DESC';
                    break;
                case 7:
                    $cOrderSQL = ' nHilfreich ASC';
                    break;
                default:
                    $cOrderSQL = ' dDatum DESC';
                    break;

            }
            executeHook(HOOK_BEWERTUNG_CLASS_SWITCH_SORTIERUNG);

            $cSQLFreischalten = ($cFreischalten === 'Y')
                ? ' AND nAktiv = 1'
                : '';
            // Bewertungen nur in einer bestimmten Sprache oder in allen Sprachen?
            $cSprachSQL = ' AND kSprache = ' . $kSprache;
            if ($bAlleSprachen) {
                $cSprachSQL = '';
            }
            // Anzahl Bewertungen für jeden Stern
            if ($nSterne !== -1) {
                if ($nSterne > 0) {
                    $cSQL = ' AND nSterne = ' . $nSterne;
                }
                $oBewertungAnzahl_arr = Shop::DB()->query(
                    "SELECT count(*) AS nAnzahl, nSterne
                        FROM tbewertung
                        WHERE kArtikel = " . $kArtikel . $cSprachSQL . $cSQLFreischalten . "
                        GROUP BY nSterne
                        ORDER BY nSterne DESC", 2
                );
            }
            if ($nSeite > 0) {
                $nLimit = '';
                if ($nAnzahlSeite > 0) {
                    $nLimit = ($nSeite > 1)
                        ? ' LIMIT ' . (($nSeite - 1) * $nAnzahlSeite) . ', ' . $nAnzahlSeite
                        : ' LIMIT ' . $nAnzahlSeite;
                }
                $this->oBewertung_arr = Shop::DB()->query(
                    "SELECT *, DATE_FORMAT(dDatum, '%d.%m.%Y') AS Datum,
                            DATE_FORMAT(dAntwortDatum, '%d.%m.%Y') AS AntwortDatum
                        FROM tbewertung
                        WHERE kArtikel = " . $kArtikel . $cSprachSQL . $cSQL . $cSQLFreischalten . "
                        ORDER BY" . $cOrderSQL . $nLimit, 2
                );
            }
            $oBewertungGesamt = Shop::DB()->query(
                "SELECT count(*) AS nAnzahl, tartikelext.fDurchschnittsBewertung AS fDurchschnitt
                    FROM tartikelext
                    JOIN tbewertung ON tbewertung.kArtikel = tartikelext.kArtikel
                    WHERE tartikelext.kArtikel = " . $kArtikel . $cSQLFreischalten . "
                    GROUP BY tartikelext.kArtikel", 1
            );
            // Anzahl Bewertungen für aktuelle Sprache
            $oBewertungGesamtSprache = Shop::DB()->query(
                "SELECT count(*) AS nAnzahlSprache
                    FROM tbewertung
                    WHERE kArtikel = " . $kArtikel . $cSprachSQL . $cSQLFreischalten, 1
            );
            if (isset($oBewertungGesamt->fDurchschnitt) && (int)$oBewertungGesamt->fDurchschnitt > 0) {
                $oBewertungGesamt->fDurchschnitt = round($oBewertungGesamt->fDurchschnitt * 2) / 2;
                $oBewertungGesamt->nAnzahl       = (int)$oBewertungGesamt->nAnzahl;
                $this->oBewertungGesamt          = $oBewertungGesamt;
            } else {
                $oBewertungGesamt                = new stdClass();
                $oBewertungGesamt->fDurchschnitt = 0;
                $oBewertungGesamt->nAnzahl       = 0;
                $this->oBewertungGesamt          = $oBewertungGesamt;
            }
            $this->nAnzahlSprache = ((int)$oBewertungGesamtSprache->nAnzahlSprache > 0)
                ? (int)$oBewertungGesamtSprache->nAnzahlSprache
                : 0;
            if (is_array($this->oBewertung_arr) && count($this->oBewertung_arr) > 0) {
                foreach ($this->oBewertung_arr as $i => $oBewertung) {
                    $this->oBewertung_arr[$i]->nAnzahlHilfreich = $oBewertung->nHilfreich + $oBewertung->nNichtHilfreich;
                }
                $nSterne_arr = [0, 0, 0, 0, 0];
                foreach ($oBewertungAnzahl_arr as $oBewertungAnzahl) {
                    $nSterne_arr[5 - $oBewertungAnzahl->nSterne] = $oBewertungAnzahl->nAnzahl;
                }

                $this->nSterne_arr = $nSterne_arr;
            }
            executeHook(HOOK_BEWERTUNG_CLASS_BEWERTUNG, ['oBewertung' => &$this]);
        }

        return $this;
    }
}
