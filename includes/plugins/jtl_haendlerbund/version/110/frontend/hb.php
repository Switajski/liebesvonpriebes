<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/** @global Plugin $oPlugin */
if (isset($oPlugin->oPluginEinstellungAssoc_arr['hb_nutzen']) &&
    $oPlugin->oPluginEinstellungAssoc_arr['hb_nutzen'] === 'Y' &&
    strlen($oPlugin->oPluginEinstellungAssoc_arr['hb_token']) > 0
) {
    define('HB_LOGGING', 0); // Logging, 1 = true, 0 = false
    define('HB_LOGGING_FILE', PFAD_LOGFILES . 'haendlerbund.log'); // Logging
    define('HB_TYPE_AGB', '12766C46A8A');
    define('HB_TYPE_WRB', '12766C53647');
    define('HB_TYPE_IMP', '1293C20B491');
    define('HB_TYPE_DAT', '12766C5E204');
    define('HB_TYPE_BAT', '134CBB4D101');
    define('HB_FORMAT_HTML', 'html');
    define('HB_FORMAT_TEXT', 'txt');
    define('HB_URL', 'https://www.hb-intern.de/www/hbm/api/live_rechtstexte.htm');

    $nCaching = (int)$oPlugin->oPluginEinstellungAssoc_arr['hb_intervall']; // Hours
    if ($nCaching < 1) {
        $nCaching = 24;
    }
    checkHaendlerbund($oPlugin, $nCaching);
}

/**
 * @param Plugin $oPlugin
 * @param string $cType
 * @param string $cFormat
 * @param int    $kUpdate
 * @param int    $kSprache
 * @return bool
 */
function getNewHaendlerbund($oPlugin, $cType, $cFormat, $kUpdate, $kSprache)
{
    Jtllog::writeLog(
        'Update: kUpdate (haendlerbund) ' . $kUpdate . ' - cType ' .
            $cType . ' - kSprache ' . $kSprache . ' wird geladen ...',
        JTLLOG_LEVEL_DEBUG,
        false,
        'kPlugin',
        $oPlugin->kPlugin
    );
    // Neue Datei laden
    $cParams  = http_build_query([
        'APIkey'      => '1IqJF0ap6GdDNF7HKzhFyciibdml8t4v',
        'AccessToken' => $oPlugin->oPluginEinstellungAssoc_arr['hb_token'],
        'mode'        => $cFormat === 'html' ? 'classes' : 'plain',
        'did'         => $cType
    ], '', '&');
    $cURL     = HB_URL . '?' . $cParams;
    $cContent = @http_get_contents($cURL);
    $cContent = utf8_decode($cContent);
    if (in_array($cContent, ['DOCUMENT_NOT_AVAILABLE', 'SHOP_NOT_FOUND', 'WRONG_API_KEY'], true)) {
        Jtllog::writeLog('Fehler (haendlerbund): ' . $cContent, JTLLOG_LEVEL_ERROR, false, 'kPlugin', $oPlugin->kPlugin);
        $cContent = false;
    }
    // Wurde ein Text uebermittelt?
    if (empty($cContent)) {
        Jtllog::writeLog(
            "Fehler (haendlerbund): " . "Kein Inhalt fuer {$cType}, {$cFormat} vorhanden.",
            JTLLOG_LEVEL_ERROR,
            false,
            'kPlugin',
            $oPlugin->kPlugin
        );
        $cContent = false;
    }

    if ($cContent !== false) {
        $cContent = Shop::DB()->escape($cContent);
        $cSpalte  = '';
        $cTabelle = '';
        $cWhere   = '';
        switch ($cType) {
            // AGB
            case HB_TYPE_AGB:
                $cSpalte  = "cAGBContentHtml";
                $cTabelle = "ttext";
                $cWhere   = " kSprache = " . (int)$kSprache;
                if ($cFormat === HB_FORMAT_TEXT) {
                    // $cContent = strip_tags($cContent);
                    $cSpalte = "cAGBContentText";
                }
                break;

            // WRB
            case HB_TYPE_WRB:
                $cSpalte  = "cWRBContentHtml";
                $cTabelle = "ttext";
                $cWhere   = " kSprache = " . (int)$kSprache;
                if ($cFormat === HB_FORMAT_TEXT) {
                    // $cContent = strip_tags($cContent);
                    $cSpalte = "cWRBContentText";
                }
                break;

            // Impressum
            case HB_TYPE_IMP:
                $cSpalte  = "cContent";
                $cTabelle = "tlinksprache";
                $oLink    = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_IMPRESSUM, 1);
                $cWhere   = "cISOSprache = '" . gibSprachKeyISO("", $kSprache) . "' AND kLink = " . $oLink->kLink;
                break;

            // Datenschutz
            case HB_TYPE_DAT:
                $cSpalte  = "cContent";
                $cTabelle = "tlinksprache";
                $oLink    = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_DATENSCHUTZ, 1);
                $cWhere   = "cISOSprache = '" . gibSprachKeyISO("", $kSprache) . "' AND kLink = " . $oLink->kLink;
                break;

            // Batteriegesetzhinweise
            case HB_TYPE_BAT:
                $cSpalte  = "cContent";
                $cTabelle = "tlinksprache";
                $oLink    = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_BATTERIEGESETZ_HINWEISE, 1);
                $cWhere   = "cISOSprache = '" . gibSprachKeyISO("", $kSprache) . "' AND kLink = " . $oLink->kLink;
                break;

        }

        Shop::DB()->query("
                UPDATE " . $cTabelle . "
                    SET " . $cSpalte . " = '" . $cContent . "'
                    WHERE " . $cWhere, 3
        );
        Shop::DB()->query("
                UPDATE xplugin_jtl_haendlerbund_tupdate
                    SET dAktualisiert = now(), nVersuch = 0
                    WHERE kUpdate = " . (int)$kUpdate, 3
        );
        Jtllog::writeLog(
            '(haendlerbund) kUpdate: ' . $kUpdate . ' ' . $cType . ' wurde aktualisiert.',
            JTLLOG_LEVEL_DEBUG,
            false,
            'kPlugin',
            $oPlugin->kPlugin
        );
        
        return true;
    } else {
        Shop::DB()->query("
                UPDATE xplugin_jtl_haendlerbund_tupdate
                    SET nVersuch = nVersuch + 1
                    WHERE kUpdate = " . (int)$kUpdate, 3
        );
        Jtllog::writeLog(
            '(haendlerbund) kUpdate ' . $kUpdate . ' war nicht erfolgreich.',
            JTLLOG_LEVEL_ERROR,
            false,
            'kPlugin',
            $oPlugin->kPlugin
        );
    }
    
    return false;
}

/**
 * Prueft ob DB noch aktuell ist
 *
 * @param Plugin $oPlugin
 * @param int $nCaching
 */
function checkHaendlerbund($oPlugin, $nCaching)
{
    $oLatestTry = Shop::DB()->query("SELECT * FROM xplugin_jtl_haendlerbund_tupdate ORDER BY dVersuch DESC LIMIT 1", 1);
    $nDelay     = pow($oLatestTry->nVersuch, 2) * 60;
    if($nDelay > 86400) {
        $nDelay = 86400;  
    }
    $time = strtotime($oLatestTry->dVersuch);
    if ($time + $nDelay > time()) {
        Jtllog::writeLog(
            '(haendlerbund) Aktualisierung pausiert aufgrund von Fehlern beim letzten Abruf. ' .
            'N&auml;chste Aktualisierung fr&uuml;hstens '.  date('d.m.Y H:i:s', $time + $nDelay),
            JTLLOG_LEVEL_DEBUG,
            false,
            'kPlugin',
            $oPlugin->kPlugin
        );
        return;
    }    
    
    $oSprache = Shop::DB()->query("
            SELECT kSprache
                FROM tsprache
                WHERE cISO = 'ger'", 1
    );

    if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
        $oUpdate_arr = Shop::DB()->query("
            SELECT *
                FROM xplugin_jtl_haendlerbund_tupdate
                WHERE (dAktualisiert = '0000-00-00 00:00:00'
                    OR DATE_ADD(dAktualisiert, INTERVAL " . (int)$nCaching . " HOUR) < now())
                    AND nAktiv = 1", 2
        );
        Jtllog::writeLog(
            '(haendlerbund) Text: ' . print_r($oUpdate_arr, true),
            JTLLOG_LEVEL_DEBUG,
            false,
            'kPlugin',
            $oPlugin->kPlugin
        );

        if (count($oUpdate_arr) > 0) {
            foreach ($oUpdate_arr as $oUpdate) {
                if (isset($oUpdate->kUpdate) && $oUpdate->kUpdate > 0) {
                    if (isset($oUpdate->nVersuch) && $oUpdate->nVersuch >= 999) {
                        Shop::DB()->query("
                            UPDATE xplugin_jtl_haendlerbund_tupdate
                                SET nAktiv = 0
                                WHERE kUpdate = " . (int)$oUpdate->kUpdate, 3
                        );
                        Jtllog::writeLog(
                            'kUpdate (haendlerbund) ' . $oUpdate->kUpdate . ' wurde deaktiviert!',
                            JTLLOG_LEVEL_ERROR,
                            true,
                            'kPlugin',
                            $oPlugin->kPlugin
                        );

                        continue;
                    }
                    
                    // $nDelay
                    Shop::DB()->query("
                        UPDATE xplugin_jtl_haendlerbund_tupdate 
                            SET dVersuch = NOW() 
                            WHERE kUpdate = " . (int)$oUpdate->kUpdate, 3
                    );
                    
                    // Updaten
                    if (!getNewHaendlerbund(
                        $oPlugin,
                        $oUpdate->cType,
                        $oUpdate->cFormat,
                        $oUpdate->kUpdate,
                        $oSprache->kSprache
                    )) {
                        break;
                    }
                }
            }
        }
    }
}
