<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kampagne.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'newsletter_inc.php';

/**
 * @param JobQueue $oJobQueue
 * @return bool
 */
function bearbeiteNewsletterversand($oJobQueue)
{
    $oJobQueue->nInArbeit = 1;
    $oNewsletter          = $oJobQueue->holeJobArt();
    $Einstellungen        = Shop::getSettings([CONF_NEWSLETTER]);
    $mailSmarty           = bereiteNewsletterVor($Einstellungen);
    // Baue Arrays mit kKeys
    $kArtikel_arr      = gibAHKKeys($oNewsletter->cArtikel, true);
    $kHersteller_arr   = gibAHKKeys($oNewsletter->cHersteller);
    $kKategorie_arr    = gibAHKKeys($oNewsletter->cKategorie);
    $kKundengruppe_arr = gibAHKKeys($oNewsletter->cKundengruppe);
    // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
    $oKampagne = new Kampagne($oNewsletter->kKampagne);
    if (count($kKundengruppe_arr) === 0) {
        $oJobQueue->deleteJobInDB();
        // NewsletterQueue löschen
        Shop::DB()->delete('tnewsletterqueue', 'kNewsletter', $oJobQueue->kKey);
        unset($oJobQueue);

        return false;
    }

    // Baue Arrays von Objekten
    $oArtikel_arr   = [];
    $oKategorie_arr = [];

    $cSQL = '';
    if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
        foreach ($kKundengruppe_arr as $kKundengruppe) {
            $oArtikel_arr[$kKundengruppe]   = gibArtikelObjekte(
                $kArtikel_arr,
                $oKampagne,
                $kKundengruppe,
                $oNewsletter->kSprache
            );
            $oKategorie_arr[$kKundengruppe] = gibKategorieObjekte($kKategorie_arr, $oKampagne);
        }

        $cSQL = "AND (";
        foreach ($kKundengruppe_arr as $i => $kKundengruppe) {
            if ($i > 0) {
                $cSQL .= " OR tkunde.kKundengruppe = " . (int)$kKundengruppe;
            } else {
                $cSQL .= "tkunde.kKundengruppe = " . (int)$kKundengruppe;
            }
        }
    }

    if (in_array('0', explode(';', $oNewsletter->cKundengruppe))) {
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            $cSQL .= " OR tkunde.kKundengruppe IS NULL";
        } else {
            $cSQL .= "tkunde.kKundengruppe IS NULL";
        }
    }
    $cSQL .= ")";

    $oHersteller_arr           = gibHerstellerObjekte($kHersteller_arr, $oKampagne, $oNewsletter->kSprache);
    $oNewsletterEmpfaenger_arr = Shop::DB()->query(
        "SELECT tkunde.kKundengruppe, tkunde.kKunde, tsprache.cISO, tnewsletterempfaenger.kNewsletterEmpfaenger, 
            tnewsletterempfaenger.cAnrede, tnewsletterempfaenger.cVorname, tnewsletterempfaenger.cNachname, 
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.cLoeschCode
            FROM tnewsletterempfaenger
            LEFT JOIN tsprache 
                ON tsprache.kSprache = tnewsletterempfaenger.kSprache
            LEFT JOIN tkunde 
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            WHERE tnewsletterempfaenger.kSprache = " . (int)$oNewsletter->kSprache . "
                AND tnewsletterempfaenger.nAktiv = 1 " . $cSQL . "
            ORDER BY tnewsletterempfaenger.kKunde
            LIMIT " . $oJobQueue->nLimitN . ", " . $oJobQueue->nLimitM, 2
    );

    if (is_array($oNewsletterEmpfaenger_arr) && count($oNewsletterEmpfaenger_arr) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';
        $shopURL = Shop::getURL();
        foreach ($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger) {
            unset($oKunde);
            $oNewsletterEmpfaenger->cLoeschURL = $shopURL . '/newsletter.php?lang=' .
                $oNewsletterEmpfaenger->cISO . '&lc=' . $oNewsletterEmpfaenger->cLoeschCode;
            if ($oNewsletterEmpfaenger->kKunde > 0) {
                $oKunde = new Kunde($oNewsletterEmpfaenger->kKunde);
            }

            $kKundengruppeTMP = 0;
            if ((int)$oNewsletterEmpfaenger->kKundengruppe > 0) {
                $kKundengruppeTMP = (int)$oNewsletterEmpfaenger->kKundengruppe;
            }

            versendeNewsletter(
                $mailSmarty,
                $oNewsletter,
                $Einstellungen,
                $oNewsletterEmpfaenger,
                $oArtikel_arr[$kKundengruppeTMP],
                $oHersteller_arr,
                $oKategorie_arr[$kKundengruppeTMP],
                $oKampagne,
                (isset($oKunde) ? $oKunde : null)
            );
            // Newsletterempfaenger updaten
            Shop::DB()->query(
                "UPDATE tnewsletterempfaenger
                    SET dLetzterNewsletter = '" . date('Y-m-d H:m:s') . "'
                    WHERE kNewsletterEmpfaenger = " . (int)$oNewsletterEmpfaenger->kNewsletterEmpfaenger, 3
            );
            ++$oJobQueue->nLimitN;
            $oJobQueue->updateJobInDB();
        }
        $oJobQueue->nInArbeit = 0;
        $oJobQueue->updateJobInDB();
    } else {
        $oJobQueue->deleteJobInDB();
        // NewsletterQueue löschen
        Shop::DB()->delete('tnewsletterqueue', 'kNewsletter', (int)$oJobQueue->kKey);
        unset($oJobQueue);
    }

    return true;
}
