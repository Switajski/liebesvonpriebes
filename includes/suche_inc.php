<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $Suchausdruck
 * @param int    $kSpracheExt
 * @return mixed
 */
function mappingBeachten($Suchausdruck, $kSpracheExt = 0)
{
    $kSprache = ((int)$kSpracheExt > 0) ? (int)$kSpracheExt : getDefaultLanguageID();
    if (strlen($Suchausdruck) > 0) {
        $SuchausdruckmappingTMP = Shop::DB()->select(
            'tsuchanfragemapping',
            'kSprache',
            $kSprache,
            'cSuche',
            $Suchausdruck,
            null,
            null,
            false,
            'cSucheNeu'
        );
        $Suchausdruckmapping    = $SuchausdruckmappingTMP;
        while ($SuchausdruckmappingTMP !== null &&
            isset($SuchausdruckmappingTMP->cSucheNeu) &&
            strlen($SuchausdruckmappingTMP->cSucheNeu) > 0
        ) {
            $SuchausdruckmappingTMP = Shop::DB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $kSprache,
                'cSuche',
                $SuchausdruckmappingTMP->cSucheNeu,
                null,
                null,
                false,
                'cSucheNeu'
            );
            if (isset($SuchausdruckmappingTMP->cSucheNeu) && strlen($SuchausdruckmappingTMP->cSucheNeu) > 0) {
                $Suchausdruckmapping = $SuchausdruckmappingTMP;
            }
        }
        if (isset($Suchausdruckmapping->cSucheNeu) && strlen($Suchausdruckmapping->cSucheNeu) > 0) {
            $Suchausdruck = $Suchausdruckmapping->cSucheNeu;
        }
    }

    return $Suchausdruck;
}

/**
 * @param string $cSuche
 * @return array
 */
function suchausdruckVorbereiten($cSuche)
{
    $cSuche       = str_replace(["'", '\\', '*', '%'], '', strip_tags($cSuche));
    $cSuch_arr    = [];
    $cSuchTMP_arr = explode(' ', $cSuche);

    $cSuche_stripped = stripslashes($cSuche);
    if ($cSuche_stripped{0} !== '"' || $cSuche_stripped{strlen($cSuche_stripped) - 1} !== '"') {
        foreach ($cSuchTMP_arr as $i => $cSuchTMP) {
            if (strpos($cSuchTMP, '+') !== false) {
                $cSuchTMP_teil = explode('+', $cSuchTMP);
                foreach ($cSuchTMP_teil as $cTeil) {
                    $cTeil = trim($cTeil);
                    if ($cTeil) {
                        $cSuch_arr[] = $cTeil;
                    }
                }
            } else {
                $cSuchTMP = trim($cSuchTMP);
                if ($cSuchTMP) {
                    $cSuch_arr[] = $cSuchTMP;
                }
            }
        }
    } else {
        $cSuch_arr[] = str_replace('"', '', $cSuche_stripped);
    }

    return $cSuch_arr;
}

/**
 * @param array $cSuch_arr
 * @return array
 */
function suchausdruckAlleKombis($cSuch_arr)
{
    $cSuchTMP_arr = [];
    $cnt          = count($cSuch_arr);
    if ($cnt > 3 || $cnt === 1) {
        return [];
    }

    switch ($cnt) {
        case 2:
            $cSuchTMP_arr[] = $cSuch_arr[0] . ' ' . $cSuch_arr[1];
            $cSuchTMP_arr[] = $cSuch_arr[1] . ' ' . $cSuch_arr[0];
            break;
        case 3:
            $cSuchTMP_arr[] = $cSuch_arr[0] . ' ' . $cSuch_arr[1] . ' ' . $cSuch_arr[2];
            $cSuchTMP_arr[] = $cSuch_arr[0] . ' ' . $cSuch_arr[2] . ' ' . $cSuch_arr[1];
            $cSuchTMP_arr[] = $cSuch_arr[2] . ' ' . $cSuch_arr[1] . ' ' . $cSuch_arr[0];
            $cSuchTMP_arr[] = $cSuch_arr[2] . ' ' . $cSuch_arr[0] . ' ' . $cSuch_arr[1];
            $cSuchTMP_arr[] = $cSuch_arr[1] . ' ' . $cSuch_arr[0] . ' ' . $cSuch_arr[2];
            $cSuchTMP_arr[] = $cSuch_arr[1] . ' ' . $cSuch_arr[2] . ' ' . $cSuch_arr[0];
            break;
        default:
            break;
    }

    return $cSuchTMP_arr;
}

/**
 * @return array
 */
function gibSuchSpalten()
{
    $cSuchspalten_arr = [];
    for ($i = 0; $i < 10; ++$i) {
        $cSuchspalten_arr[] = gibMaxPrioSpalte($cSuchspalten_arr);
    }
    // Leere Spalten entfernen
    if (is_array($cSuchspalten_arr) && count($cSuchspalten_arr) > 0) {
        foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
            if (strlen($cSuchspalten) === 0) {
                unset($cSuchspalten_arr[$i]);
            }
        }
        $cSuchspalten_arr = array_merge($cSuchspalten_arr);
    }

    return $cSuchspalten_arr;
}

/**
 * @param array $exclude
 * @return string
 */
function gibMaxPrioSpalte($exclude)
{
    $max             = 0;
    $aktEle          = '';
    $cTabellenPrefix = 'tartikel.';
    $conf            = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);

    if (!standardspracheAktiv()) {
        $cTabellenPrefix = 'tartikelsprache.';
    }
    if (!in_array($cTabellenPrefix . 'cName', $exclude, true) && $conf['artikeluebersicht']['suche_prio_name'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_name'];
        $aktEle = $cTabellenPrefix . 'cName';
    }
    if (!in_array($cTabellenPrefix . 'cSeo', $exclude, true) && $conf['artikeluebersicht']['suche_prio_name'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_name'];
        $aktEle = $cTabellenPrefix . 'cSeo';
    }
    if (!in_array('tartikel.cSuchbegriffe', $exclude, true) && $conf['artikeluebersicht']['suche_prio_suchbegriffe'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_suchbegriffe'];
        $aktEle = 'tartikel.cSuchbegriffe';
    }
    if (!in_array('tartikel.cArtNr', $exclude, true) && $conf['artikeluebersicht']['suche_prio_artikelnummer'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_artikelnummer'];
        $aktEle = 'tartikel.cArtNr';
    }
    if (!in_array($cTabellenPrefix . 'cKurzBeschreibung', $exclude, true) && $conf['artikeluebersicht']['suche_prio_kurzbeschreibung'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_kurzbeschreibung'];
        $aktEle = $cTabellenPrefix . 'cKurzBeschreibung';
    }
    if (!in_array($cTabellenPrefix . 'cBeschreibung', $exclude, true) && $conf['artikeluebersicht']['suche_prio_beschreibung'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_beschreibung'];
        $aktEle = $cTabellenPrefix . 'cBeschreibung';
    }
    if (!in_array('tartikel.cBarcode', $exclude, true) && $conf['artikeluebersicht']['suche_prio_ean'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_ean'];
        $aktEle = 'tartikel.cBarcode';
    }
    if (!in_array('tartikel.cISBN', $exclude, true) && $conf['artikeluebersicht']['suche_prio_isbn'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_isbn'];
        $aktEle = 'tartikel.cISBN';
    }
    if (!in_array('tartikel.cHAN', $exclude, true) && $conf['artikeluebersicht']['suche_prio_han'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_han'];
        $aktEle = 'tartikel.cHAN';
    }
    if (!in_array('tartikel.cAnmerkung', $exclude, true) && $conf['artikeluebersicht']['suche_prio_anmerkung'] > $max) {
        $aktEle = 'tartikel.cAnmerkung';
    }

    return $aktEle;
}

/**
 * @param array $cSuchspalten_arr
 * @return array
 */
function gibSuchspaltenKlassen($cSuchspalten_arr)
{
    $cSuchspaltenKlasse_arr = [];
    if (is_array($cSuchspalten_arr) && count($cSuchspalten_arr) > 0) {
        foreach ($cSuchspalten_arr as $cSuchspalten) {
            // Klasse 1: Artikelname und Artikel SEO
            if (strpos($cSuchspalten, 'cName') !== false ||
                strpos($cSuchspalten, 'cSeo') !== false ||
                strpos($cSuchspalten, 'cSuchbegriffe') !== false) {
                $cSuchspaltenKlasse_arr[1][] = $cSuchspalten;
            }
            // Klasse 2: Artikelname und Artikel SEO
            if (strpos($cSuchspalten, 'cKurzBeschreibung') !== false ||
                strpos($cSuchspalten, 'cBeschreibung') !== false ||
                strpos($cSuchspalten, 'cAnmerkung') !== false) {
                $cSuchspaltenKlasse_arr[2][] = $cSuchspalten;
            }
            // Klasse 3: Artikelname und Artikel SEO
            if (strpos($cSuchspalten, 'cArtNr') !== false ||
                strpos($cSuchspalten, 'cBarcode') !== false ||
                strpos($cSuchspalten, 'cISBN') !== false ||
                strpos($cSuchspalten, 'cHAN') !== false) {
                $cSuchspaltenKlasse_arr[3][] = $cSuchspalten;
            }
        }
    }

    return $cSuchspaltenKlasse_arr;
}

/**
 * @param array  $cSuchspaltenKlasse_arr
 * @param string $cSuchspalte
 * @param array  $nNichtErlaubteKlasse_arr
 * @return bool
 */
function pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalte, $nNichtErlaubteKlasse_arr)
{
    if (is_array($cSuchspaltenKlasse_arr) &&
        is_array($nNichtErlaubteKlasse_arr) &&
        count($cSuchspaltenKlasse_arr) > 0 &&
        strlen($cSuchspalte) > 0 &&
        count($nNichtErlaubteKlasse_arr) > 0
    ) {
        foreach ($nNichtErlaubteKlasse_arr as $nNichtErlaubteKlasse) {
            if (isset($cSuchspaltenKlasse_arr[$nNichtErlaubteKlasse]) && count($cSuchspaltenKlasse_arr[$nNichtErlaubteKlasse]) > 0) {
                foreach ($cSuchspaltenKlasse_arr[$nNichtErlaubteKlasse] as $cSuchspaltenKlasse) {
                    if ($cSuchspaltenKlasse == $cSuchspalte) {
                        return false;
                    }
                }
            }
        }
    }

    return true;
}

/**
 * @param string $cSuche
 * @param int    $nAnzahlTreffer
 * @param bool   $bEchteSuche
 * @param int    $kSpracheExt
 * @param bool   $bSpamFilter
 * @return bool
 */
function suchanfragenSpeichern($cSuche, $nAnzahlTreffer, $bEchteSuche = false, $kSpracheExt = 0, $bSpamFilter = true)
{
    if (strlen($cSuche) > 0) {
        $Suchausdruck = str_replace(["'", "\\", "*", "%"], '', $cSuche);
        $kSprache     = ((int)$kSpracheExt > 0) ? (int)$kSpracheExt : getDefaultLanguageID();
        //db füllen für auswertugnen / suggest, dabei Blacklist beachten
        $Suchausdruck_tmp_arr = explode(';', $Suchausdruck);
        $blacklist_erg        = Shop::DB()->select(
            'tsuchanfrageblacklist',
            'kSprache',
            $kSprache,
            'cSuche',
            Shop::DB()->escape($Suchausdruck_tmp_arr[0])
        );
        if (!$bSpamFilter || !isset($blacklist_erg->kSuchanfrageBlacklist) || $blacklist_erg->kSuchanfrageBlacklist == 0) {
            // Ist MD5(IP) bereits X mal im Cache
            $conf         = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
            $max_ip_count = (int)$conf['artikeluebersicht']['livesuche_max_ip_count'] * 100;
            $ip_cache_erg = Shop::DB()->query(
                "SELECT count(*) AS anzahl
                    FROM tsuchanfragencache
                    WHERE kSprache = " . $kSprache . "
                    AND cIP = '" . gibIP() . "'", 1
            );
            $ip_used = Shop::DB()->select(
                'tsuchanfragencache',
                'kSprache',
                $kSprache,
                'cSuche',
                $Suchausdruck,
                'cIP',
                gibIP(),
                false,
                'kSuchanfrageCache'
            );
            if (!$bSpamFilter || (isset($ip_cache_erg->anzahl) && $ip_cache_erg->anzahl < $max_ip_count &&
                    (!isset($ip_used->kSuchanfrageCache) || !$ip_used->kSuchanfrageCache))) {
                // Fülle Suchanfragencache
                $tsuchanfragencache_obj           = new stdClass();
                $tsuchanfragencache_obj->kSprache = $kSprache;
                $tsuchanfragencache_obj->cIP      = gibIP();
                $tsuchanfragencache_obj->cSuche   = $Suchausdruck;
                $tsuchanfragencache_obj->dZeit    = 'now()';
                Shop::DB()->insert('tsuchanfragencache', $tsuchanfragencache_obj);
                // Cacheeinträge die > 1 Stunde sind, löschen
                Shop::DB()->query("
                    DELETE 
                        FROM tsuchanfragencache 
                        WHERE dZeit < DATE_SUB(now(),INTERVAL 1 HOUR)", 4
                );
                if ($nAnzahlTreffer > 0) {
                    require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
                    if (!isset($suchanfrage)) {
                        $suchanfrage = new stdClass();
                    }
                    $suchanfrage->kSprache        = $kSprache;
                    $suchanfrage->cSuche          = $Suchausdruck;
                    $suchanfrage->nAnzahlTreffer  = $nAnzahlTreffer;
                    $suchanfrage->nAnzahlGesuche  = 1;
                    $suchanfrage->dZuletztGesucht = 'now()';
                    $suchanfrage->cSeo            = getSeo($Suchausdruck);
                    $suchanfrage->cSeo            = checkSeo($suchanfrage->cSeo);
                    $suchanfrage_old              = Shop::DB()->select(
                        'tsuchanfrage',
                        'kSprache', (int)$suchanfrage->kSprache,
                        'cSuche', $Suchausdruck,
                        null, null,
                        false,
                        'kSuchanfrage'
                    );
                    if (isset($suchanfrage_old->kSuchanfrage) && $suchanfrage_old->kSuchanfrage > 0 && $bEchteSuche) {
                        Shop::DB()->query(
                            "UPDATE tsuchanfrage
                                SET nAnzahlTreffer = $suchanfrage->nAnzahlTreffer, 
                                    nAnzahlGesuche = nAnzahlGesuche+1, 
                                    dZuletztGesucht = now()
                                WHERE kSuchanfrage = " . (int)$suchanfrage_old->kSuchanfrage, 4
                        );
                    } elseif (!isset($suchanfrage_old->kSuchanfrage) || !$suchanfrage_old->kSuchanfrage) {
                        Shop::DB()->delete(
                            'tsuchanfrageerfolglos',
                            ['kSprache', 'cSuche'],
                            [(int)$suchanfrage->kSprache, Shop::DB()->realEscape($Suchausdruck)]
                        );
                        $kSuchanfrage = Shop::DB()->insert('tsuchanfrage', $suchanfrage);
                        writeLog(PFAD_LOGFILES . 'suchanfragen.log', print_r($suchanfrage, true), 1);

                        return $kSuchanfrage;
                    }
                } else {
                    $suchanfrageerfolglos                  = new stdClass();
                    $suchanfrageerfolglos->kSprache        = $kSprache;
                    $suchanfrageerfolglos->cSuche          = $Suchausdruck;
                    $suchanfrageerfolglos->nAnzahlGesuche  = 1;
                    $suchanfrageerfolglos->dZuletztGesucht = 'now()';
                    $suchanfrageerfolglos_old              = Shop::DB()->select(
                        'tsuchanfrageerfolglos',
                        'kSprache', (int)$suchanfrageerfolglos->kSprache,
                        'cSuche', $Suchausdruck,
                        null, null,
                        false,
                        'kSuchanfrageErfolglos'
                    );
                    if (isset($suchanfrageerfolglos_old->kSuchanfrageErfolglos) &&
                        $suchanfrageerfolglos_old->kSuchanfrageErfolglos > 0 &&
                        $bEchteSuche) {
                        Shop::DB()->query(
                            "UPDATE tsuchanfrageerfolglos
                                SET nAnzahlGesuche = nAnzahlGesuche+1, 
                                    dZuletztGesucht = now()
                                WHERE kSuchanfrageErfolglos = " . (int)$suchanfrageerfolglos_old->kSuchanfrageErfolglos, 4
                        );
                    } else {
                        Shop::DB()->delete(
                            'tsuchanfrage',
                            ['kSprache', 'cSuche'],
                            [(int)$suchanfrageerfolglos->kSprache, Shop::DB()->realEscape($Suchausdruck)]
                        );
                        Shop::DB()->insert('tsuchanfrageerfolglos', $suchanfrageerfolglos);
                    }
                }
            }
        }
    }

    return false;
}
