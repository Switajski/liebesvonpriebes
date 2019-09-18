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
    if ((int)$oVersion->nVersion > 308) {
        header('Location: ' . URL_SHOP . "/" . PFAD_ADMIN . "index.php");
        exit;
    }

    // ###############################
    // Version worauf upgedated wird
    $nVersionAfter = 309;
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
                            updateFertig($nVersionAfter);
                        } // Fertig
                            //naechsterUpdateStep(2, 1);
                        
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
                                    updateFertig($nVersionAfter); // Fertig
                                    //naechsterUpdateStep(2, 1);
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
                
                if ($nRow == $oVersion->nZeileBis) {// Fertig!
                    updateFertig($nVersionAfter);
                } // Fertig
                    //naechsterUpdateStep(2, 1);
            } else {// Fertig!
                updateFertig($nVersionAfter);
            } // Fertig
                //naechsterUpdateStep(2, 1);
            break;
    }
    
    // Abschluss
    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 0", 4);
    @fclose($file_handle);
    header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=-1");
    exit();
