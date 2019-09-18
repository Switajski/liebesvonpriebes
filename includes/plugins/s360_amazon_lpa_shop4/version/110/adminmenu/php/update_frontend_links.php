<?php

// benoetigt, um alle JTL-Funktionen zur Verfuegung zu haben
require_once(dirname(__FILE__) . '/../../frontend/lib/lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");

$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');

// die Antwort ist im JSON Format
header('Content-Type: application/json');

if (!$oPlugin || $oPlugin->kPlugin == 0) {
    Jtllog::writeLog('LPA: Fehler beim Funktionsaufruf: Plugin-Objekt konnte nicht geladen werden!', JTLLOG_LEVEL_ERROR);
    echo json_encode(array('status' => 'error'));
    return;
}

$kPlugin = $oPlugin->kPlugin;
$checkResult = 'unknown';
$userMessages = array();

/*
 * Dieses Skript manipuliert die Frontendlinks im Shop, damit diese passen, d.h.:
 * 
 * - Pruefung, ob es die Linkgruppe "hidden" gibt, wenn nein -> erstelle sie
 * - Fuer jeden Frontendlink des Plugins:
 *      - Wenn der Name unerwartet ist (z.B. mit _1 oder -3 am Ende, wird er normalisiert auf den erwarteten Wert)
 *      - Wenn er nicht in der Linkgruppe hidden ist, wird er dort hin verschoben
 */
$pluginLinkSeos = array('lpalogin', 'lpacreate', 'lpamerge', 'lpacheckout', 'lpacomplete');

/*
 * Zunaechst Pruefung auf Linkgruppe "hidden"
 */
$kLinkgruppeHidden = -1;
$result = Shop::DB()->query('SELECT kLinkgruppe FROM tlinkgruppe WHERE cName LIKE "hidden"', 2);
if (empty($result)) {
    $obj = new stdClass();
    $obj->cName = 'hidden';
    $obj->cTemplatename = 'hidden';
    $kLinkgruppeHidden = (int) Shop::DB()->insert('tlinkgruppe', $obj);
    $userMessages[] = 'Linkgruppe "hidden" angelegt';
} else {
    $kLinkgruppeHidden = (int) $result[0]->kLinkgruppe;
}

/*
 * Zunaechst wird die Gruppenzuordnung geprueft und ggf. korrigiert
 */
$result = Shop::DB()->query("SELECT * FROM tlink WHERE kPlugin = {$kPlugin}", 2);
if (!empty($result)) {
    foreach ($result as $link) {
        if ((int) $link->kLinkgruppe != $kLinkgruppeHidden) {
            /*
             * Link ist noch in falscher Linkgruppe - verschieben durch Zuordnung
             * zur hidden Linkgruppe
             */
            $link->kLinkgruppe = $kLinkgruppeHidden;
            /*
             * Workaround fuer NiceDB-Bug
             */
            $cURL = $link->cURL;
            if (empty($cURL)) {
                $link->cURL = "NULL";
            }
            $cKundengruppen = $link->cKundengruppen;
            if (empty($cKundengruppen)) {
                $link->cKundengruppen = "NULL";
            }
            Shop::DB()->update('tlink', 'kLink', $link->kLink, $link);
            $userMessages[] = "Link {$link->cName} in versteckte Linkgruppe verschoben.";
        }
    }
} else {
    $userMessages[] = 'Fehler: Keine Frontendlinks gefunden!';
    $checkResult = 'Keine Frontendlinks gefunden!';
}

/*
 * Es koennte sein, dass noch das alte Amazon Plugin installiert/deaktiviert ist. 
 * In dem Fall muessen die SEO-Links des ALTEN PLUGINS zunaechst mit dem Suffix _OLD versehen werden, 
 * um Kollisionen zu vermeiden.
 */
$oldPlugin = Plugin::getPluginById('s360_amazon_lpa');
if (!empty($oldPlugin)) {
    $error = false;
    /*
     * Disable Shop 3 Frontend-Links by appending _OLD to their seo in tseo and tlinksprache
     */
    $userMessages[] = "Deaktiviere SEO-Links vom alten Plugin durch Umbenennung...";
    $oldLinkKeys = array();
    $kOldPlugin = $oldPlugin->kPlugin;
    $result = Shop::DB()->query("SELECT * FROM tlink WHERE kPlugin = {$kOldPlugin}", 2);
    if (!empty($result)) {
        foreach ($result as $link) {
            $oldLinkKeys[] = $link->kLink;
        }
        $userMessages[] = count($oldLinkKeys) . " SEO-Links vom alten Plugin gefunden.";
    } else {
        $userMessages[] = "Keine SEO-Links vom alten Plugin gefunden.";
    }
    foreach ($oldLinkKeys as $oldLinkKey) {
        // correct tseo
        $queryResult = Shop::DB()->query("SELECT * FROM tseo WHERE cKey LIKE 'kLink' AND kKey = {$oldLinkKey}", 2);
        if (!empty($queryResult)) {
            /*
             * Die Abfrage liefert normalerweise 2 Ergebnisse: den Link als SEO und den englischen Link mit "-en" Suffix.
             */
            foreach ($queryResult as $gefundenerLink) {
                if (strpos($gefundenerLink->cSeo, "_OLD") === FALSE) {
                    $cSeoAlt = $gefundenerLink->cSeo;
                    $gefundenerLink->cSeo = $cSeoAlt . '_OLD';
                    /*
                     * Korrekten Zustand herstellen - da cSeo aber PK von tseo ist, muss der Link erst geloescht und dann neu eingefuegt werden
                     */
                    Shop::DB()->delete('tseo', 'cSeo', $cSeoAlt);
                    Shop::DB()->insert('tseo', $gefundenerLink);
                    $userMessages[] = 'SEO vom alten Plugin deaktiviert in tseo: ' . $cSeoAlt . ' - ' . $gefundenerLink->cSeo;
                } else {
                    $userMessages[] = 'SEO vom alten Plugin bereits vorher deaktiviert in tseo: ' . $gefundenerLink->cSeo;
                }
            }
        }
        // correct tlinksprache
        $queryResult = Shop::DB()->query("SELECT * FROM tlinksprache WHERE kLink = {$oldLinkKey}", 2);
        if (!empty($queryResult)) {
            /*
             * Die Abfrage liefert normalerweise 2 Ergebnisse: den Link als SEO und den englischen Link mit "-en" Suffix.
             */
            foreach ($queryResult as $gefundenerLink) {
                if (strpos($gefundenerLink->cSeo, "_OLD") === FALSE) {
                    $cSeoAlt = $gefundenerLink->cSeo;
                    $gefundenerLink->cSeo = $cSeoAlt . '_OLD';
                    /*
                     * Korrekten Zustand herstellen - da der PK fuer tlinksprache aus kLink und cISOSprache zusammengesetzt ist, muss der Link erst geloescht und dann neu eingefuegt werden
                     */
                    Shop::DB()->delete('tlinksprache', 'cSeo', $cSeoAlt);
                    Shop::DB()->insert('tlinksprache', $gefundenerLink);
                    $userMessages[] = 'SEO vom alten Plugin deaktiviert in tlinksprache: ' . $cSeoAlt . ' - ' . $gefundenerLink->cSeo;
                } else {
                    $userMessages[] = 'SEO vom alten Plugin bereits vorher deaktiviert in tlinksprache: ' . $gefundenerLink->cSeo;
                }
            }
        }
    }
}

/*
 * Sprachinformation laden (ISO ist zwar immer ger und eng, kSprache kann aber abweichen)
 */
$languages = array(
    "ger" => array("iso" => "ger", "key" => null, "suffix" => ""),
    "eng" => array("iso" => "eng", "key" => null, "suffix" => "-en")
);
foreach ($languages as $k => $v) {
    $oSprache = Shop::DB()->select('tsprache', 'cISO', $v['iso']);
    if (isset($oSprache->kSprache) && (int) $oSprache->kSprache > 0) {
        $languages[$k]['key'] = (int) $oSprache->kSprache;
    } else {
        $languages[$k]['key'] = null;
    }
}



/*
 * SEO-Namen der Links pruefen und ggf. korrigieren.
 * 
 * Das passiert hier doppelt - einmal fuer die Tabelle tseo, einmal fuer die Tabelle tlinksprache (da ist auch cSeo enthalten)
 */
$seoLinksFound = array();
$seoLinksKeys = array();
foreach ($pluginLinkSeos as $linkseo) {
    $seoLinksFound[$linkseo] = array('ger' => false, 'eng' => false);
    $seoLinksKeys[$linkseo] = null;
    /*
     * Links aus tseo lesen.
     * Der query holt auch alle lokalisierten Links!
     */
    $result = Shop::DB()->query("SELECT * FROM tseo WHERE cKey LIKE 'kLink' AND cSeo LIKE '{$linkseo}%'", 2);
    if (!empty($result)) {
        /*
         * Die Abfrage liefert normalerweise 2 Ergebnisse: den Link als SEO und den englischen Link mit "-en" Suffix.
         * Er holt aber auch ggf. fuer weitere Sprachen dynamisch generierte Links. ("-en_1_1_1" etc.)
         */
        foreach ($result as $gefundenerLink) {

            if (!isset($seoLinksKeys[$linkseo]) || $seoLinksKeys[$linkseo] === null) {
                $seoLinksKeys[$linkseo] = (int) $gefundenerLink->kKey;
            }

            if ($gefundenerLink->cSeo === $linkseo && (int) $gefundenerLink->kSprache === $languages['ger']['key'] || $gefundenerLink->cSeo === $linkseo . '-en' && (int) $gefundenerLink->kSprache === $languages['eng']['key'] || strpos($gefundenerLink->cSeo, "_OLD") !== FALSE) {

                // merken, welche links ueberhaupt gefunden wurden, um den Shop 4 Bug zu umgehen, dass Frontendlinks in tseo geloescht werden.
                if ($gefundenerLink->cSeo === $linkseo) {
                    $seoLinksFound[$linkseo]['ger'] = true;
                } elseif ($gefundenerLink->cSeo === $linkseo . '-en') {
                    $seoLinksFound[$linkseo]['eng'] = true;
                }

                // Soll-Zustand, der Link ist entweder der deutsche Link oder der englische Link und im richtigen Format oder ein Link vom alten Plugin
                $userMessages[] = 'SEO korrekt in tseo: ' . $gefundenerLink->cSeo . ' fuer Sprache ' . $gefundenerLink->kSprache;
                continue;
            }

            if ((int) $gefundenerLink->kSprache !== $languages['ger']['key'] && (int) $gefundenerLink->kSprache !== $languages['eng']['key']) {
                // kein deutscher oder englischer Link
                if ($gefundenerLink->cSeo === $linkseo || $gefundenerLink->cSeo === $linkseo . '-en') {
                    // trotzdem entspricht der link dem, was eigentlich der deutsche oder englische link sein sollte - das darf nicht sein. wir loeschen den Link.
                    Shop::DB()->delete('tseo', array('cSeo', 'kSprache'), array($gefundenerLink->cSeo, $gefundenerLink->kSprache));
                    $userMessages[] = 'Folgender Link wird geloescht in tseo fuer nicht unterstuetzte Sprache, um Kollisionen zu vermeiden: ' . $gefundenerLink->cSeo . ' fuer Sprache ' . $gefundenerLink->kSprache;
                } else {
                    $userMessages[] = 'Folgender Link wird ignoriert in tseo fuer nicht unterstuetzte Sprache: ' . $gefundenerLink->cSeo . ' fuer Sprache ' . $gefundenerLink->kSprache;
                }
                continue;
            } else {
                // merken, welche links ueberhaupt gefunden wurden, um den Shop 4 Bug zu umgehen, dass Frontendlinks in tseo geloescht werden.
                if ((int) $gefundenerLink->kSprache === $languages['ger']['key']) {
                    $seoLinksFound[$linkseo]['ger'] = true;
                } elseif ((int) $gefundenerLink->kSprache === $languages['eng']['key']) {
                    $seoLinksFound[$linkseo]['eng'] = true;
                }
            }

            // an dem Punkt wissen wir, dass der gefundene Link entweder deutsch oder englisch ist, aber nicht das richtige Format hat.
            // alten Wert fuer Seo speichern, damit wir den Link gleich loeschen koennen
            $cSeoAlt = $gefundenerLink->cSeo;

            // Unterscheidung, ob es sich um den deutschen oder englischen Link handelt
            if (strpos($cSeoAlt, '-en') !== FALSE) {
                // englischer Link, wir fuegen -en an.
                $gefundenerLink->cSeo = $linkseo . '-en';
            } else {
                // deutscher Link, bleibt so.
                $gefundenerLink->cSeo = $linkseo;
            }

            /*
             * Korrekten Zustand herstellen - da cSeo aber PK von tseo ist, muss der Link erst geloescht und dann neu eingefuegt werden (Update des PK geht nicht!)
             */
            Shop::DB()->delete('tseo', array('cSeo', 'kSprache'), array($cSeoAlt, $gefundenerLink->kSprache));
            Shop::DB()->insert('tseo', $gefundenerLink);
            $userMessages[] = 'SEO korrigiert in tseo: ' . $cSeoAlt . ' - ' . $gefundenerLink->cSeo .' fuer Sprache ' . $gefundenerLink->kSprache;
        }
        // seo links behandeln, die ueberhaupt nicht gefunden worden sind
        foreach ($languages as $language) {
            if ($language['key'] !== null) {
                // Sprache existiert im Shop
                $iso = $language['iso'];
                $suffix = $language['suffix'];
                if (!$seoLinksFound[$linkseo][$iso]) {
                    $userMessages[] = "Fehler: Keinen SEO-Link {$linkseo}{$suffix} gefunden fuer Sprache $iso. Versuche Fix.";
                    if (isset($seoLinksKeys[$linkseo]) && $seoLinksKeys[$linkseo] !== null) {
                        // as we did not find the seo key and all non-conforming language links should be deleted now, we should be able to just insert it.
                        $newLink = new stdClass();
                        $newLink->cSeo = $linkseo . $suffix;
                        $newLink->kSprache = $language['key'];
                        $newLink->cKey = "kLink";
                        $newLink->kKey = $seoLinksKeys[$linkseo];
                        Shop::DB()->insert('tseo', $newLink);
                        $userMessages[] = "Erfolg: SEO-Link {$linkseo}{$suffix} fuer Sprache $iso eingefuegt.";
                    } else {
                        $userMessages[] = "Fehler: Fix nicht moeglich, da kLink unbekannt ist.";
                        $checkResult = "Fehlender SEO-Link fuer Sprache $iso! Fehler bei Plugin-Installation/-Update? Bitte korrigieren Sie die SEO-Links von Hand im Bereich Eigene Seiten!";
                    }
                }
            }
        }
    } else {
        $userMessages[] = 'Fehler: Keinen SEO-Link gefunden: ' . $linkseo;
        $checkResult = "Fehlender SEO-Link! Fehler bei Plugin-Installation/-Update? Bitte korrigieren Sie die SEO-Links von Hand im Bereich Eigene Seiten!";
    }
}

/*
 * Selbes Spiel fuer tlinksprache. Hier wird die Sprache aber statt kSprache ueber cISOSprache gemappt. Hier muessen Links auch nicht unique sein.
 * Das heisst, hier kann einfach der suffix "_1" entfernt werden.
 */
foreach ($pluginLinkSeos as $linkseo) {
    $result = Shop::DB()->query("SELECT * FROM tlinksprache WHERE cSeo LIKE '{$linkseo}%'", 2);
    if (!empty($result)) {
        /*
         * Die Abfrage liefert normalerweise 2 Ergebnisse: den Link als SEO und den englischen Link mit "-en" Suffix.
         */
        foreach ($result as $gefundenerLink) {
            if ($gefundenerLink->cSeo === $linkseo || $gefundenerLink->cSeo === $linkseo . '-en' || strpos($gefundenerLink->cSeo, "_OLD") !== FALSE) {
                // Soll-Zustand
                continue;
            }

            $cSeoAlt = $gefundenerLink->cSeo;
            if (strpos($cSeoAlt, '-en') !== FALSE) {
                $gefundenerLink->cSeo = $linkseo . '-en';
            } else {
                $gefundenerLink->cSeo = $linkseo;
            }
            /*
             * Korrekten Zustand herstellen - der PK fuer tlinksprache ist aus kLink und cISOSprache zusammengesetzt
             */
            Shop::DB()->update('tlinksprache', array('kLink', 'cISOSprache'), array($gefundenerLink->kLink, $gefundenerLink->cISOSprache), $gefundenerLink);
            $userMessages[] = "SEO korrigiert in tlinksprache fuer Sprache {$gefundenerLink->cISOSprache}: " . $cSeoAlt . ' - ' . $gefundenerLink->cSeo;
        }
    } else {
        $userMessages[] = 'Fehler: Keinen SEO-Link gefunden: ' . $linkseo;
        $checkResult = 'Fehlender SEO-Link! Fehler bei Plugin-Installation/-Update?';
    }
}

if ($checkResult === 'unknown') {
    echo json_encode(array('status' => 'success', 'messages' => $userMessages));
} else {
    echo json_encode(array('status' => 'fail', 'error' => $checkResult));
}
