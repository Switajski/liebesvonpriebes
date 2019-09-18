<?php
    require_once '../../includes/config.JTL-Shop.ini.php';
    require_once PFAD_ROOT . 'includes/defines.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    //existiert Konfiguration?
    defined('DB_HOST') || die('Kein MySql-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_NAME') || die('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_USER') || die('Kein MySql-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_PASS') || die('Kein MySql-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');

    require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Shop.php';
    $shop = Shop::getInstance();
    require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
    // datenbankverbindung aufbauen
    try {
        $GLOBALS['DB'] = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (Exception $exc) {
        die($exc->getMessage());
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Shopsetting.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbupdater_inc.php';
    //datenbankverbindung aufbauen
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JTLCache.php';
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JTLCache.php';
    $cache = JTLCache::getInstance();
    $cache->setJtlCacheConfig();
    $options            = $cache->getOptions();
    $options['enabled'] = false;
    $cache->setOptions($options);

    session_name("eSIdAdm");
    session_start();
    if (!isset($_SESSION['AdminAccount'])) {
        header('Location: ' . URL_SHOP . "/" . PFAD_ADMIN . "index.php");
        exit;
    }
    
    $oVersion = $GLOBALS['DB']->executeQuery("SELECT * FROM tversion", 1);
    if ((int)$oVersion->nVersion > 312) {
        header('Location: ' . URL_SHOP . "/" . PFAD_ADMIN . "index.php");
        exit;
    }

    function fixEmailTemplates($cTable)
    {
        $oTemplate_arr = $GLOBALS['DB']->executeQuery("select * from {$cTable}", 2);

        foreach ($oTemplate_arr as $oTemplate) {
            $oTemplate->cContentHtml = str_replace('{$Kunde->cStrasse}', '{$Kunde->cStrasse} {$Kunde->cHausnummer}', $oTemplate->cContentHtml);
            $oTemplate->cContentHtml = str_replace('{$Bestellung->Lieferadresse->cStrasse}', '{$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}', $oTemplate->cContentHtml);
            
            $oTemplate->cContentText = str_replace('{$Kunde->cStrasse}', '{$Kunde->cStrasse} {$Kunde->cHausnummer}', $oTemplate->cContentText);
            $oTemplate->cContentText = str_replace('{$Bestellung->Lieferadresse->cStrasse}', '{$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}', $oTemplate->cContentText);
            
            $oTemplate->cContentHtml = $GLOBALS['DB']->realEscape($oTemplate->cContentHtml);
            $oTemplate->cContentText = $GLOBALS['DB']->realEscape($oTemplate->cContentText);
            
            $GLOBALS['DB']->executeQuery("UPDATE {$cTable} SET cContentHtml='{$oTemplate->cContentHtml}', cContentText='{$oTemplate->cContentText}' WHERE kEmailvorlage='{$oTemplate->kEmailvorlage}' AND kSprache='{$oTemplate->kSprache}'", 4);
        }
    }
    
    
    
    // ###############################
    // Version worauf upgedated wird
    $nVersionAfter = 313;
    // ###############################
    
    // ##### Anfang Script
    
    
    // Vorbereitung
    $nStartStamp = time();
    if (intval(ini_get('max_execution_time')) < 320) {
        @ini_set('max_execution_time', 320);
    }
    $nMaxLaufzeit = intval(ini_get('max_execution_time')) / 2;  // Maximale Laufzeit die das Script laufen darf
    //$nMaxLaufzeit = 2;
    $nEndeStamp = $nStartStamp + $nMaxLaufzeit;
    $cSQLDatei = "update1.sql";
    
    // ### Main Script
    if (intval($_GET['nFirstStart']) == 1) {
        resetteUpdateDB();                  // Fügt Spalten hinzu die vielleicht noch nicht vorhanden sind und setzt alle wichtigen Spalten auf 0
        updateZeilenBis($cSQLDatei);     // Läuft die Datei durch und zählt die Reihen. Danach wird die Anzahl in der DB hinterlegt.
    }
    
    // Logging
    define("UPDATER_LOGFILE", PFAD_LOGFILES . "update_" . intval($oVersion->nVersion) . ".log");
    
    if (!file_exists($cSQLDatei)) {
        header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=1");
        exit();
    }

    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 1", 4);
    $nRow = 1;
    
    switch ($oVersion->nTyp) {
        case 1:    // SQL
            $file_handle = @fopen($cSQLDatei, "r");
            if ($oVersion->nZeileVon <= $oVersion->nZeileBis) {
                while ($cData = fgets($file_handle)) {
                    if (time() < $nEndeStamp) {
                        if ($nRow > $oVersion->nZeileBis) {
                            //updateFertig($nVersionAfter); // Fertig
                            naechsterUpdateStep(2, 1);
                        }
                        
                        if ($nRow >= $oVersion->nZeileVon) {
                            // Wurde bei einem SQL 3x ein Fehler ausgegeben?
                            if (intval($oVersion->nFehler) >= 3) {
                                @fclose($file_handle);
                                header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=999");
                                exit();
                            }
                                                     
                            // SQL ausführen
                            $GLOBALS['DB']->executeQuery($cData, 4);
                    
                            $nErrno = Shop::DB()->getErrorCode();
                            
                            if (!$nErrno || $nErrno == 1062 || $nErrno == 1060 || $nErrno == 1267) {
                                writeLog(UPDATER_LOGFILE, $nRow . ": " . $cData . " erfolgreich ausgeführt. MySQL Errno: " . $nErrno . " - " . str_replace("'", "", Shop::DB()->getErrorMessage()), 1);
                                $nRow++;
                                $GLOBALS['DB']->executeQuery("UPDATE tversion SET nZeileVon = " . $nRow . ", nFehler=0, cFehlerSQL=''", 4);
                                
                                if ($nRow > $oVersion->nZeileBis) {
                                    @fclose($file_handle);
                                    //updateFertig($nVersionAfter); // Fertig
                                    naechsterUpdateStep(2, 1);
                                }
                            } else {
                                if (strpos(strtolower($cData), "alter table")) {// Alter Table darf nicht nochmal ausgeführt werden
                                    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nFehler=3, cFehlerSQL='Zeile " . $nRow . ": " . str_replace("'", "", Shop::DB()->getErrorMessage()) . "'", 4);
                                } else {
                                    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nFehler=nFehler+1, cFehlerSQL='Zeile " . $nRow . ": " . str_replace("'", "", Shop::DB()->getErrorMessage()) . "'", 4);
                                }
                                
                                writeLog(UPDATER_LOGFILE, "Fehler in Zeile " . $nRow . ": " . str_replace("'", "", Shop::DB()->getErrorMessage()), 1);
                                @fclose($file_handle);
                                $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 0", 4);
                                header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=1");
                                exit();
                            }
                        } else {
                            $nRow++;
                        }
                    } else {
                        break;
                    }
                }
                
                if ($nRow == $oVersion->nZeileBis) {
                    // Fertig!

                    //updateFertig($nVersionAfter); // Fertig
                    naechsterUpdateStep(2, 1);
                }
            } else {
                // Fertig!

                //updateFertig($nVersionAfter); // Fertig
                naechsterUpdateStep(2, 1);
            }
            break;
            
        // Migration von Emaileinstellungen
        case 2:
            if ($oVersion->nZeileVon <= $oVersion->nZeileBis) {
                function getEmailKey($cModulId)
                {
                    if (strlen($cModulId) > 0) {
                        $oEmailvorlage = $GLOBALS['DB']->executeQuery("SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = '{$cModulId}'", 1);
                        
                        if (isset($oEmailvorlage->kEmailvorlage) && $oEmailvorlage->kEmailvorlage > 0) {
                            return $oEmailvorlage->kEmailvorlage;
                        }
                    }
                    
                    return 0;
                }
                
                function saveEmailSetting($kEmailvorlage, $cKey, $cValue)
                {
                    if (intval($kEmailvorlage) > 0 && strlen($cKey) > 0 && strlen($cValue) > 0) {
                        $oEmailvorlageEinstellung = new stdClass();
                        $oEmailvorlageEinstellung->kEmailvorlage = intval($kEmailvorlage);
                        $oEmailvorlageEinstellung->cKey = $cKey;
                        $oEmailvorlageEinstellung->cValue = $cValue;
                        
                        $GLOBALS['DB']->insertRow("temailvorlageeinstellungen", $oEmailvorlageEinstellung);
                    }
                }
                
                $oEinstellung_arr = $GLOBALS['DB']->executeQuery("SELECT teinstellungen.*
																	FROM teinstellungenconf
																	JOIN teinstellungen ON teinstellungen.cName = teinstellungenconf.cWertName
																	    AND teinstellungen.kEinstellungenSektion = teinstellungenconf.kEinstellungenSektion
																	WHERE teinstellungenconf.kEinstellungenSektion = 3
																	    AND teinstellungenconf.kEinstellungenConf NOT IN(144,270,271,272,273,274,275,276,145,146)", 2);
                
                if (is_array($oEinstellung_arr) && count($oEinstellung_arr) > 0) {
                    $kEmailBestellbestaetigung    = getEmailKey(MAILTEMPLATE_BESTELLBESTAETIGUNG);
                    $kEmailGutschein            = getEmailKey(MAILTEMPLATE_GUTSCHEIN);
                    $kEmailNeukunde            = getEmailKey(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG);
                    $kEmailBezahlt                = getEmailKey(MAILTEMPLATE_BESTELLUNG_BEZAHLT);
                    $kEmailVersandt            = getEmailKey(MAILTEMPLATE_BESTELLUNG_VERSANDT);
                    $kEmailKdgZuweisung        = getEmailKey(MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN);
                    $kEmailVerfuegbar            = getEmailKey(MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR);
                    
                    foreach ($oEinstellung_arr as $oEinstellung) {
                        switch ($oEinstellung->cName) {
                            // * * * * * * * * * * * * * * *
                            // * Aktivierungen der Emails *
                            // * * * * * * * * * * * * * * *
                            
                            case "email_bestellabschluss_aktiv":            // Bestellbestätigungen verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_BESTELLBESTAETIGUNG ."'", 3);
                                }
                                break;
                                
                            case "email_stornomail_aktiv":                    // Stornomails verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_BESTELLUNG_STORNO ."' OR cModulId = '" . MAILTEMPLATE_BESTELLUNG_RESTORNO . "'", 3);
                                }
                                break;
                                
                            case "email_gutschein_aktiv":                    // Gutscheinemail verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_GUTSCHEIN ."'", 3);
                                }
                                break;
                                
                            case "email_account_aktiv":                        // Accounterstellungsemail verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_NEUKUNDENREGISTRIERUNG ."' OR cModulId = '" . MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER . "'", 3);
                                }
                                break;
                                
                            case "email_zahlung_aktiv":                        // Zahlungseingangemail verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_BESTELLUNG_BEZAHLT ."'", 3);
                                }
                                break;
                                
                            case "email_versandt_aktiv":                    // Versandbenachrichtigungemail verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_BESTELLUNG_VERSANDT ."'", 3);
                                }
                                break;
                            
                            case "email_kdgrpmail_aktiv":                    // Kundengruppenänderungsemail verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN ."'", 3);
                                }
                                break;
                                
                            case "email_verfuegbar_aktiv":                    // Verfügbarkeitsbenachrichtigung verschicken
                                if ($oEinstellung->cWert === "N") {
                                    $GLOBALS['DB']->executeQuery("UPDATE temailvorlage SET cAktiv = 'N' WHERE cModulId = '" . MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR ."'", 3);
                                }
                                break;
                                
                            // * * * * * * * * *
                            // * Einstellungen *
                            // * * * * * * * * *
                            
                            // MAILTEMPLATE_BESTELLBESTAETIGUNG
                            case "email_bestellabschluss_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailBestellbestaetigung, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_bestellabschluss_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailBestellbestaetigung, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_bestellabschluss_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailBestellbestaetigung, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_GUTSCHEIN
                            case "email_gutschein_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailGutschein, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_gutschein_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailGutschein, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_gutschein_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailGutschein, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_NEUKUNDENREGISTRIERUNG
                            case "email_account_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailNeukunde, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_account_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailNeukunde, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_account_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailNeukunde, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_BESTELLUNG_BEZAHLT
                            case "email_zahlung_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailBezahlt, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_zahlung_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailBezahlt, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_zahlung_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailBezahlt, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_BESTELLUNG_VERSANDT
                            case "email_versandt_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailVersandt, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_versandt_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailVersandt, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_versandt_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailVersandt, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN
                            case "email_kdgrpmail_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailKdgZuweisung, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_kdgrpmail_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailKdgZuweisung, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_kdgrpmail_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailKdgZuweisung, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR
                            case "email_verfuegbar_absender":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailVerfuegbar, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "email_verfuegbar_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailVerfuegbar, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                            case "email_verfuegbar_kopie":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailVerfuegbar, "cEmailCopyTo", $oEinstellung->cWert);
                                }
                                break;
                        }
                    }
                }
                
                // Special Settings
                $oEinstellung_arr = $GLOBALS['DB']->executeQuery("SELECT teinstellungen.*
																	FROM teinstellungenconf
																	JOIN teinstellungen ON teinstellungen.cName = teinstellungenconf.cWertName
																	    AND teinstellungen.kEinstellungenSektion = teinstellungenconf.kEinstellungenSektion
																	WHERE teinstellungenconf.kEinstellungenConf IN(300,296,313,312,613,612)", 2);
                
                if (is_array($oEinstellung_arr) && count($oEinstellung_arr) > 0) {
                    $kEmailKontakt            = getEmailKey(MAILTEMPLATE_KONTAKTFORMULAR);
                    $kEmailProduktanfrage    = getEmailKey(MAILTEMPLATE_PRODUKTANFRAGE);
                    $kEmailWeiterempfehlen    = getEmailKey(MAILTEMPLATE_ARTIKELWEITEREMPFEHLEN);
                    
                    foreach ($oEinstellung_arr as $oEinstellung) {
                        switch ($oEinstellung->cName) {
                            // MAILTEMPLATE_KONTAKTFORMULAR
                            case "kontakt_absender_mail":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailKontakt, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "kontakt_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailKontakt, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_PRODUKTANFRAGE
                            case "produktfrage_absender_mail":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailProduktanfrage, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "produktfrage_absender_name":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailProduktanfrage, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                                
                            // MAILTEMPLATE_ARTIKELWEITEREMPFEHLEN
                            case "artikeldetails_artikelweiterempfehlen_absenderemail":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailWeiterempfehlen, "cEmailOut", $oEinstellung->cWert);
                                }
                                break;
                            case "artikeldetails_artikelweiterempfehlen_absendername":
                                if (strlen($oEinstellung->cWert) > 0) {
                                    saveEmailSetting($kEmailWeiterempfehlen, "cEmailSenderName", $oEinstellung->cWert);
                                }
                                break;
                        }
                    }
                }
                
                $GLOBALS['DB']->executeQuery("DELETE teinstellungenconfwerte, teinstellungen, teinstellungenconf
               									FROM teinstellungenconf
               									LEFT JOIN teinstellungenconfwerte ON teinstellungenconfwerte.kEinstellungenConf = teinstellungenconf.kEinstellungenConf
               									LEFT JOIN teinstellungen ON teinstellungen.kEinstellungenSektion = teinstellungenconf.kEinstellungenSektion
               										AND teinstellungen.cName = teinstellungenconf.cWertName
               									WHERE teinstellungenconf.kEinstellungenConf NOT IN(144,270,271,272,273,274,275,276,145,146)
               										AND teinstellungenconf.kEinstellungenSektion = 3;", 3);
                   
                $GLOBALS['DB']->executeQuery("DELETE teinstellungenconfwerte, teinstellungen, teinstellungenconf
               									FROM teinstellungenconf
               									LEFT JOIN teinstellungenconfwerte ON teinstellungenconfwerte.kEinstellungenConf = teinstellungenconf.kEinstellungenConf
               									LEFT JOIN teinstellungen ON teinstellungen.kEinstellungenSektion = teinstellungenconf.kEinstellungenSektion
               										AND teinstellungen.cName = teinstellungenconf.cWertName
               									WHERE teinstellungenconf.kEinstellungenConf IN(300,296,313,312,613,612);", 3);

                $oVersion->nZeileVon++;
                $GLOBALS['DB']->executeQuery("UPDATE tversion SET nZeileVon = " . $oVersion->nZeileVon . ", nFehler=0, cFehlerSQL=''", 4);
               
                if ($oVersion->nZeileVon > $oVersion->nZeileBis) {
                    // updateFertig($nVersionAfter); // Fertig
                    naechsterUpdateStep(3, 1);
                }
            } else {
                // updateFertig($nVersionAfter); // Fertig
               naechsterUpdateStep(3, 1);
            }
             break;
            
        case 3:
            fixEmailTemplates('temailvorlagesprache');
            fixEmailTemplates('temailvorlagespracheoriginal');
            
            $GLOBALS['DB']->executeQuery("UPDATE tversion SET nZeileVon = " . ++$oVersion->nZeileVon . ", nFehler=0, cFehlerSQL=''", 4);
            
            updateFertig($nVersionAfter); // Fertig
        break;
    }
    
    // Abschluss
    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 0", 4);
    @fclose($file_handle);
    header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=-1");
    exit();
