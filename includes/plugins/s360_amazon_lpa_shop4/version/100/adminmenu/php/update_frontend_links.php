<?php

// benötigt, um alle JTL-Funktionen zur Verfügung zu haben
require_once(dirname(__FILE__) . '/../../../../../../globalinclude.php');
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
 * - Prüfung, ob es die Linkgruppe "hidden" gibt, wenn nein -> erstelle sie
 * - Für jeden Frontendlink des Plugins:
 *      - Wenn der Name unerwartet ist (z.B. mit _1 oder -3 am Ende, wird er normalisiert auf den erwarteten Wert)
 *      - Wenn er nicht in der Linkgruppe hidden ist, wird er dort hin verschoben
 */
$pluginLinkSeos = array('lpalogin', 'lpacreate', 'lpamerge', 'lpacheckout', 'lpacomplete');

/*
 * Zunächst Prüfung auf Linkgruppe "hidden"
 */
$kLinkgruppeHidden = -1;
$result = Shop::DB()->query('SELECT kLinkgruppe FROM tlinkgruppe WHERE cName LIKE "hidden"', 2);
if (empty($result)) {
    $obj = new stdClass();
    $obj->cName = 'hidden';
    $obj->cTemplatename = 'hidden';
    $kLinkgruppeHidden = intval(Shop::DB()->insert('tlinkgruppe', $obj));
    $userMessages[] = 'Linkgruppe "hidden" angelegt';
} else {
    $kLinkgruppeHidden = intval($result[0]->kLinkgruppe);
}

/*
 * Zunächst wird die Gruppenzuordnung geprüft und ggf. korrigiert
 */
$result = Shop::DB()->query("SELECT * FROM tlink WHERE kPlugin = {$kPlugin}", 2);
if (!empty($result)) {
    foreach ($result as $link) {
        if (intval($link->kLinkgruppe) != $kLinkgruppeHidden) {
            /*
             * Link ist noch in falscher Linkgruppe - verschieben durch Zuordnung
             * zur hidden Linkgruppe
             */
            $link->kLinkgruppe = $kLinkgruppeHidden;
            /*
             * Workaround für NiceDB-Bug
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
 * Es könnte sein, dass noch das alte Amazon Plugin installiert/deaktiviert ist. 
 * In dem Fall müssen die SEO-Links des ALTEN PLUGINS zunächst mit dem Suffix _OLD versehen werden, 
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
                     * Korrekten Zustand herstellen - da cSeo aber PK von tseo ist, muss der Link erst gelöscht und dann neu eingefügt werden
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
                     * Korrekten Zustand herstellen - da der PK für tlinksprache aus kLink und cISOSprache zusammengesetzt ist, muss der Link erst gelöscht und dann neu eingefügt werden
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
 * SEO-Namen der Links prüfen und ggf. korrigieren.
 * 
 * Das passiert hier doppelt - einmal für die Tabelle tseo, einmal für die Tabelle tlinksprache (da ist auch cSeo enthalten)
 */
foreach ($pluginLinkSeos as $linkseo) {
    $result = Shop::DB()->query("SELECT * FROM tseo WHERE cKey LIKE 'kLink' AND cSeo LIKE '{$linkseo}%'", 2);
    if (!empty($result)) {
        /*
         * Die Abfrage liefert normalerweise 2 Ergebnisse: den Link als SEO und den englischen Link mit "-en" Suffix.
         */
        foreach ($result as $gefundenerLink) {
            if ($gefundenerLink->cSeo === $linkseo || $gefundenerLink->cSeo === $linkseo . '-en' || strpos($gefundenerLink->cSeo, "_OLD") !== FALSE) {
                // Soll-Zustand
            } else {
                $cSeoAlt = $gefundenerLink->cSeo;
                if (strpos($cSeoAlt, '-en') !== FALSE) {
                    $gefundenerLink->cSeo = $linkseo . '-en';
                } else {
                    $gefundenerLink->cSeo = $linkseo;
                }
                /*
                 * Korrekten Zustand herstellen - da cSeo aber PK von tseo ist, muss der Link erst gelöscht und dann neu eingefügt werden
                 */
                Shop::DB()->delete('tseo', 'cSeo', $cSeoAlt);
                Shop::DB()->insert('tseo', $gefundenerLink);
                $userMessages[] = 'SEO korrigiert in tseo: ' . $cSeoAlt . ' - ' . $gefundenerLink->cSeo;
            }
        }
    } else {
        $userMessages[] = 'Fehler: Keinen SEO-Link gefunden: ' . $linkseo;
        $checkResult = 'Fehlender SEO-Link! Fehler bei Plugin-Installation/-Update?';
    }
}


foreach ($pluginLinkSeos as $linkseo) {
    $result = Shop::DB()->query("SELECT * FROM tlinksprache WHERE cSeo LIKE '{$linkseo}%'", 2);
    if (!empty($result)) {
        /*
         * Die Abfrage liefert normalerweise 2 Ergebnisse: den Link als SEO und den englischen Link mit "-en" Suffix.
         */
        foreach ($result as $gefundenerLink) {
            if ($gefundenerLink->cSeo === $linkseo || $gefundenerLink->cSeo === $linkseo . '-en' || strpos($gefundenerLink->cSeo, "_OLD") !== FALSE) {
                // Soll-Zustand
            } else {
                $cSeoAlt = $gefundenerLink->cSeo;
                if (strpos($cSeoAlt, '-en') !== FALSE) {
                    $gefundenerLink->cSeo = $linkseo . '-en';
                } else {
                    $gefundenerLink->cSeo = $linkseo;
                }
                /*
                 * Korrekten Zustand herstellen - da der PK für tlinksprache aus kLink und cISOSprache zusammengesetzt ist, muss der Link erst gelöscht und dann neu eingefügt werden
                 */
                Shop::DB()->delete('tlinksprache', 'cSeo', $cSeoAlt);
                Shop::DB()->insert('tlinksprache', $gefundenerLink);
                $userMessages[] = 'SEO korrigiert in tlinksprache: ' . $cSeoAlt . ' - ' . $gefundenerLink->cSeo;
            }
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
