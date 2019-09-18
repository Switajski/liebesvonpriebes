<?php
    require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
    
    // < JTL-Shop3.15
    if (!function_exists('http_get_contents')) {
        function http_get_contents($cURL, $nTimeout = 15)
        {
            $cData = "";
            if (function_exists('curl_init')) {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $cURL);
                curl_setopt($curl, CURLOPT_TIMEOUT, $nTimeout);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_REFERER, URL_SHOP);

                $cData = curl_exec($curl);
                curl_close($curl);
            } elseif (ini_get("allow_url_fopen")) {
                @ini_set("default_socket_timeout", $nTimeout);
                $fileHandle = @fopen($cURL, "r");
                if ($fileHandle) {
                    @stream_set_timeout($fileHandle, $nTimeout);
                    $cData = fgets($fileHandle);
                    fclose($fileHandle);
                }
            }
            return $cData;
        }
    }

    if (isset($oPlugin->oPluginEinstellungAssoc_arr['hb_nutzen']) && $oPlugin->oPluginEinstellungAssoc_arr['hb_nutzen'] == "Y") {
        define("HB_LOGGING", 0); // Logging, 1 = true, 0 = false
        define("HB_LOGGING_FILE", PFAD_LOGFILES . "haendlerbund.log"); // Logging
        define("HB_TYPE_AGB", "12766C46A8A");
        define("HB_TYPE_WRB", "12766C53647");
        define("HB_TYPE_IMP", "1293C20B491");
        define("HB_TYPE_DAT", "12766C5E204");
        define("HB_FORMAT_HTML", "html");
        define("HB_FORMAT_TEXT", "txt");
        define("HB_URL", "https://www.hb-intern.de/www/hbm/api/live_rechtstexte.htm");
        
        $nCaching = intval($oPlugin->oPluginEinstellungAssoc_arr['hb_intervall']); // Hours

        // Logging
        Jtllog::writeLog("### Plugin haendlerbund gestartet => check ...", JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
        
        checkHaendlerbund($oPlugin, $nCaching);
    }
    
    function getNewHaendlerbund($oPlugin, $cType, $cFormat, $kUpdate, $kSprache)
    {
        // Logging
        Jtllog::writeLog("Update: kUpdate (haendlerbund) " . $kUpdate . " - cType " . $cType . " - kSprache " . $kSprache . " wird geladen ...", JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);

        // Neue Datei laden
        $cParams = http_build_query(array(
            'APIkey' => '1IqJF0ap6GdDNF7HKzhFyciibdml8t4v',
            'AccessToken' => $oPlugin->oPluginEinstellungAssoc_arr['hb_token'],
            'mode' => $cFormat == 'html' ? 'classes' : 'plain',
            'did' => $cType
        ), '', '&');
        
        $cURL = HB_URL . '?' . $cParams;
        
        // Logging
        Jtllog::writeLog("Hole URL (haendlerbund): " . $cURL, JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
        
        $cContent = @http_get_contents($cURL);
        $cContent = utf8_decode($cContent);
        
        if (in_array($cContent, array('DOCUMENT_NOT_AVAILABLE', 'SHOP_NOT_FOUND', 'WRONG_API_KEY'))) {
            Jtllog::writeLog("Fehler (haendlerbund): " . $cContent, JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
            $cContent = false;
        }
        
        // Wurde ein Text übermittelt?
        if (strlen($cContent) == 0) {
            Jtllog::writeLog("Fehler (haendlerbund): " . "Kein Inhalt für {$cType}, {$cFormat} vorhanden.", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
            $cContent = false;
        }

        if ($cContent !== false) {
            $cContent = $GLOBALS['DB']->escape($cContent);
            $cSpalte    = "";
            $cTabelle   = "";
            $cSet       = "";
            $kLink      = 0;
            switch ($cType) {
                // AGB
                case HB_TYPE_AGB:
                    $cSpalte    = "cAGBContentHtml";
                    $cTabelle   = "ttext";
                    $cWhere     = " kSprache = " . intval($kSprache);
                    if ($cFormat == HB_FORMAT_TEXT) {
                        // $cContent = strip_tags($cContent);
                        $cSpalte = "cAGBContentText";
                    }
                    break;

                // WRB
                case HB_TYPE_WRB:
                    $cSpalte    = "cWRBContentHtml";
                    $cTabelle   = "ttext";
                    $cWhere     = " kSprache = " . intval($kSprache);
                    if ($cFormat == HB_FORMAT_TEXT) {
                        // $cContent = strip_tags($cContent);
                        $cSpalte = "cWRBContentText";
                    }
                    break;
                    
                // Impressum
                case HB_TYPE_IMP:
                    $cSpalte    = "cContent";
                    $cTabelle   = "tlinksprache";
                    $oLink      = $GLOBALS['DB']->executeQuery("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_IMPRESSUM, 1);
                    $cWhere     = "cISOSprache = '" . gibSprachKeyISO("", $kSprache) . "' AND kLink = " . $oLink->kLink;
                    break;
                
                // Datenschutz
                case HB_TYPE_DAT:
                    $cSpalte    = "cContent";
                    $cTabelle   = "tlinksprache";
                    $oLink      = $GLOBALS['DB']->executeQuery("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_DATENSCHUTZ, 1);
                    $cWhere     = "cISOSprache = '" . gibSprachKeyISO("", $kSprache) . "' AND kLink = " . $oLink->kLink;
                    break;
            }

            $GLOBALS['DB']->executeQuery("UPDATE " . $cTabelle. "
                                            SET " . $cSpalte . " = '" . $cContent . "'
                                            WHERE " . $cWhere, 3);

            $GLOBALS['DB']->executeQuery("UPDATE xplugin_jtl_haendlerbund_tupdate
                                            SET dAktualisiert = now(),
                                                nVersuch = 0
                                            WHERE kUpdate = " . intval($kUpdate), 3);

            // Logging
            Jtllog::writeLog("(haendlerbund) kUpdate: " . $kUpdate . " " . $cType . " wurde aktualisiert.", JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
        } else {
            $GLOBALS['DB']->executeQuery("UPDATE xplugin_jtl_haendlerbund_tupdate
                                            SET nVersuch = nVersuch + 1
                                            WHERE kUpdate = " . intval($kUpdate), 3);
            
            // Logging
            Jtllog::writeLog("(haendlerbund) kUpdate " . $kUpdate . " war nicht erfolgreich.", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
        }
    }
    
    // Prueft ob DB noch aktuell ist
    function checkHaendlerbund($oPlugin, $nCaching)
    {
        $oSprache = $GLOBALS['DB']->executeQuery("SELECT kSprache
                                                    FROM tsprache
                                                    WHERE cISO = 'ger'", 1);
        
        // Logging
        Jtllog::writeLog("(haendlerbund) Sprache: " . print_r($oSprache, 1), JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
        
        if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
            $oUpdate_arr = $GLOBALS['DB']->executeQuery("SELECT *
                                                            FROM xplugin_jtl_haendlerbund_tupdate
                                                            WHERE (dAktualisiert = '0000-00-00 00:00:00' OR DATE_ADD(dAktualisiert, INTERVAL " . intval($nCaching) . " HOUR) < now())
                                                                AND nAktiv = 1", 2);
         
            // Logging
            Jtllog::writeLog("(haendlerbund) Text: " . print_r($oUpdate_arr, 1), JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);
            
            if (count($oUpdate_arr) > 0) {
                foreach ($oUpdate_arr as $oUpdate) {
                    if (isset($oUpdate->kUpdate) && $oUpdate->kUpdate > 0) {
                        if (isset($oUpdate->nVersuch) && $oUpdate->nVersuch >= 3) {
                            $GLOBALS['DB']->executeQuery("UPDATE xplugin_jtl_haendlerbund_tupdate
                                                            SET nAktiv = 0
                                                            WHERE kUpdate = " . intval($oUpdate->kUpdate), 3);

                            // Logging
                            Jtllog::writeLog("kUpdate (haendlerbund) " . $oUpdate->kUpdate . " wurde deaktiviert!", JTLLOG_LEVEL_ERROR, true, "kPlugin", $oPlugin->kPlugin);

                            continue;
                        }
                        
                        // Updaten
                        getNewHaendlerbund($oPlugin, $oUpdate->cType, $oUpdate->cFormat, $oUpdate->kUpdate, $oSprache->kSprache);
                    }
                }
            }
        }
    }
