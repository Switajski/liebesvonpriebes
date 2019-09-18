<?php

/*
 * Solution 360 GmbH
 *
 * Database-access controller for LPA.
 */
require_once('lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Kunde.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Rechnungsadresse.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Lieferadresse.php");
require_once('lpa_defines.php');
require_once('class.LPAController.php');

class LPADatabase {

    var $config;
    var $oPlugin;
    var $csvSeparator;

    public function __construct() {
        $controller = new LPAController();
        $this->config = $controller->getConfig();
        $this->oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
        $this->csvSeparator = ";";
    }

    public function getOrder($orid, $getBestellung = false) {
        $result = Shop::DB()->select(S360_LPA_TABLE_ORDER, 'cOrderReferenceId', $orid);
        
        if ($result) {
            if ($getBestellung) {
                $bestellung = Shop::DB()->select('tbestellung', 'kBestellung', intval($result->kBestellung));
                if ($bestellung) {
                    $result->bestellung = $bestellung;
                } else {
                    Jtllog::writeLog('LPA: LPADatabase->getOrder() : $bestellung = NULL', JTLLOG_LEVEL_DEBUG);
                    $result->bestellung = NULL;
                }
            }
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getOrder() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    public function getAuthorization($authid) {
        $result = Shop::DB()->select(S360_LPA_TABLE_AUTHORIZATION, 'cAuthorizationId', $authid);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getAuthorization() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    public function getCapture($capid) {
        $result = Shop::DB()->select(S360_LPA_TABLE_CAPTURE, 'cCaptureId', $capid);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getCapture() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    public function getRefund($refid) {
        $result = Shop::DB()->select(S360_LPA_TABLE_REFUND, 'cRefundId', $refid);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getRefund() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    public function getAccountMapping($amazonid) {
        $result = Shop::DB()->select(S360_LPA_TABLE_ACCOUNTMAPPING, 'cAmazonId', $amazonid);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getAccountMapping() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    public function getOrders($getBestellung = true) {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_ORDER . " WHERE bSandbox = " . (int) $this->getSandboxFlag();
        $result = Shop::DB()->query($sql, 2);
        
        if ($result) {
            if ($getBestellung) {
                foreach ($result as &$res) {
                    $bestellung = Shop::DB()->select('tbestellung', 'kBestellung', intval($res->kBestellung));
                    if ($bestellung) {
                        $res->bestellung = $bestellung;
                    } else {
                        Jtllog::writeLog('LPA: LPADatabase->getOrders() : $bestellung = NULL (kBestellung=' . intval($res->kBestellung) . ')', JTLLOG_LEVEL_DEBUG);
                        $res->bestellung = NULL;
                    }
                }
            }
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getOrders() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    public function getAuthorizations() {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_AUTHORIZATION . " WHERE bSandbox = " . (int) $this->getSandboxFlag();
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getAuthorizations() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    public function getAuthorizationsForOrder($orid) {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_AUTHORIZATION . "," . S360_LPA_TABLE_ORDER . " WHERE " . S360_LPA_TABLE_ORDER . ".cOrderReferenceId = " . S360_LPA_TABLE_AUTHORIZATION . ".cOrderReferenceId AND " . S360_LPA_TABLE_ORDER . ".cOrderReferenceId = '{$orid}'";
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getAuthorizationsForOrder() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    public function getCaptures() {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_CAPTURE . " WHERE bSandbox = " . (int) $this->getSandboxFlag();
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getCaptures() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
        
    }
    
    public function getCapturesForAuthorization($authid) {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_CAPTURE . "," . S360_LPA_TABLE_AUTHORIZATION . " WHERE " . S360_LPA_TABLE_AUTHORIZATION . ".cAuthorizationId = " . S360_LPA_TABLE_CAPTURE . ".cAuthorizationId AND " . S360_LPA_TABLE_AUTHORIZATION . ".cAuthorizationId = '{$authid}'";
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getCapturesForAuthorization() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    public function getRefunds() {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_REFUND . " WHERE bSandbox = " . (int) $this->getSandboxFlag();
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getRefunds() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }
    
    public function getRefundsForCapture($capid) {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_REFUND . "," . S360_LPA_TABLE_CAPTURE . " WHERE " . S360_LPA_TABLE_CAPTURE . ".cCaptureId = " . S360_LPA_TABLE_REFUND . ".cCaptureId AND " . S360_LPA_TABLE_CAPTURE . ".cCaptureId = '{$capid}'";
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            return $result;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getRefundsForCapture() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    public function saveOrder($kBestellung, $cOrderReferenceId, $fOrderAmount = NULL, $cOrderCurrencyCode = NULL, $cOrderStatus = NULL, $cOrderStatusReason = NULL, $nOrderExpirationTimestamp = NULL) {
        $order = new stdClass();
        $order->kBestellung = $kBestellung;
        $order->cOrderReferenceId = $cOrderReferenceId;
        if (!is_null($cOrderStatus)) {
            $order->cOrderStatus = $cOrderStatus;
            $order->cOrderStatusReason = $cOrderStatusReason;
        }
        if (!is_null($fOrderAmount)) {
            $order->fOrderAmount = $fOrderAmount;
            $order->cOrderCurrencyCode = $cOrderCurrencyCode;
        }
        if (!is_null($nOrderExpirationTimestamp)) {
            $order->nOrderExpirationTimestamp = $nOrderExpirationTimestamp;
        }
        $this->insertOrUpdate($order, S360_LPA_TABLE_ORDER);
    }

    public function saveAuthorization($cOrderReferenceId, $cAuthorizationId, $fAuthorizationAmount = NULL, $cAuthorizationCurrencyCode = NULL, $bCaptureNow = NULL, $fCapturedAmount = NULL, $cCapturedCurrencyCode = NULL, $cAuthorizationStatus = NULL, $cAuthorizationStatusReason = NULL, $nAuthorizationExpirationTimestamp = NULL) {
        $auth = new stdClass();
        $auth->cOrderReferenceId = $cOrderReferenceId;
        $auth->cAuthorizationId = $cAuthorizationId;
        if (!is_null($cAuthorizationStatus)) {
            $auth->cAuthorizationStatus = $cAuthorizationStatus;
            $auth->cAuthorizationStatusReason = $cAuthorizationStatusReason;
        }
        if (!is_null($fAuthorizationAmount)) {
            $auth->fAuthorizationAmount = $fAuthorizationAmount;
            $auth->cAuthorizationCurrencyCode = $cAuthorizationCurrencyCode;
        }
        if (!is_null($fCapturedAmount)) {
            $auth->fCapturedAmount = $fCapturedAmount;
            $auth->cCapturedCurrencyCode = $cCapturedCurrencyCode;
        }
        if (!is_null($bCaptureNow)) {
            $auth->bCaptureNow = $bCaptureNow;
        }
        if (!is_null($nAuthorizationExpirationTimestamp)) {
            $auth->nAuthorizationExpirationTimestamp = $nAuthorizationExpirationTimestamp;
        }
        $this->insertOrUpdate($auth, S360_LPA_TABLE_AUTHORIZATION);
    }

    /*
     * This method is used to save objects as a whole.
     */

    public function saveOrderObject($order) {
        $this->insertOrUpdate($order, S360_LPA_TABLE_ORDER);
    }

    public function saveAuthorizationObject($auth) {
        $this->insertOrUpdate($auth, S360_LPA_TABLE_AUTHORIZATION);
    }

    public function saveCaptureObject($cap) {
        $this->insertOrUpdate($cap, S360_LPA_TABLE_CAPTURE);
    }

    public function saveRefundObject($ref) {
        $this->insertOrUpdate($ref, S360_LPA_TABLE_REFUND);
    }

    public function saveAccountMappingObject($accountMapping) {
        $this->insertOrUpdate($accountMapping, S360_LPA_TABLE_ACCOUNTMAPPING);
    }

    /*
     * Returns a complete Kunde-object for the given orderid
     */

    public function getKundeForOrder($orid) {
        $orderTable = S360_LPA_TABLE_ORDER;
        $sql = "SELECT * FROM tbestellung, {$orderTable} WHERE {$orderTable}.kBestellung = tbestellung.kBestellung AND {$orderTable}.cOrderReferenceId = '{$orid}'";
        $result = Shop::DB()->query($sql, 1);
        if ($result) {
            return new Kunde($result->kKunde);
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getKundeForOrder() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    /*
     * Tries to find the key of an existing lieferadresse by matching the values from the objects.
     * 
     * Returns the intval of the key (or -1 if the lieferadresse seems to be new).
     */

    public function getKeyForLieferadresse($kunde, $amazonlieferadresse) {
        // get all lieferadressen for this customer, then match each one.
        if (empty($kunde) || $kunde->kKunde <= 0) {
            return -1;
        }
        $sql = "SELECT * FROM tlieferadresse WHERE kKunde = {$kunde->kKunde}";
        $result = Shop::DB()->query($sql, 2);
        if ($result && !empty($result) && is_array($result)) {
            foreach ($result as $res) {
                // load the lieferadresse specifically - this is needed to decode the encoded part
                $la = new Lieferadresse($res->kLieferadresse);
                /*
                 * now match, we accept this as equal only if all the following data matches:
                 *  
                 * var $cAnrede;	
                 * var $cVorname;
                 * var $cNachname;
                 * var $cTitel;
                 * var $cFirma;
                 * var $cStrasse;
                 * var $cAdressZusatz;
                 * var $cPLZ;
                 * var $cOrt;
                 * var $cBundesland;
                 * var $cLand;
                 * var $cTel;
                 * var $cMobil;
                 * var $cFax;
                 * var $cMail;
                 * var $cHausnummer;
                 * var $cZusatz;
                 */
                $equal = true;
                $equal = $equal && ((empty($la->cAnrede) && empty($amazonlieferadresse->cAnrede)) || $la->cAnrede === $amazonlieferadresse->cAnrede);
                $equal = $equal && ((empty($la->cVorname) && empty($amazonlieferadresse->cVorname)) || $la->cVorname === $amazonlieferadresse->cVorname);
                $equal = $equal && ((empty($la->cNachname) && empty($amazonlieferadresse->cNachname)) || $la->cNachname === $amazonlieferadresse->cNachname);
                $equal = $equal && ((empty($la->cTitel) && empty($amazonlieferadresse->cTitel)) || $la->cTitel === $amazonlieferadresse->cTitel);
                $equal = $equal && ((empty($la->cFirma) && empty($amazonlieferadresse->cFirma)) || $la->cFirma === $amazonlieferadresse->cFirma);
                $equal = $equal && ((empty($la->cStrasse) && empty($amazonlieferadresse->cStrasse)) || $la->cStrasse === $amazonlieferadresse->cStrasse);
                $equal = $equal && ((empty($la->cAdressZusatz) && empty($amazonlieferadresse->cAdressZusatz)) || $la->cAdressZusatz === $amazonlieferadresse->cAdressZusatz);
                $equal = $equal && ((empty($la->cPLZ) && empty($amazonlieferadresse->cPLZ)) || $la->cPLZ === $amazonlieferadresse->cPLZ);
                $equal = $equal && ((empty($la->cOrt) && empty($amazonlieferadresse->cOrt)) || $la->cOrt === $amazonlieferadresse->cOrt);
                $equal = $equal && ((empty($la->cBundesland) && empty($amazonlieferadresse->cBundesland)) || $la->cBundesland === $amazonlieferadresse->cBundesland);
                $equal = $equal && ((empty($la->cLand) && empty($amazonlieferadresse->cLand)) || $la->cLand === $amazonlieferadresse->cLand);
                $equal = $equal && ((empty($la->cTel) && empty($amazonlieferadresse->cTel)) || $la->cTel === $amazonlieferadresse->cTel);
                $equal = $equal && ((empty($la->cMobil) && empty($amazonlieferadresse->cMobil)) || $la->cMobil === $amazonlieferadresse->cMobil);
                $equal = $equal && ((empty($la->cFax) && empty($amazonlieferadresse->cFax)) || $la->cFax === $amazonlieferadresse->cFax);
                $equal = $equal && ((empty($la->cMail) && empty($amazonlieferadresse->cMail)) || $la->cMail === $amazonlieferadresse->cMail);
                $equal = $equal && ((empty($la->cHausnummer) && empty($amazonlieferadresse->cHausnummer)) || $la->cHausnummer === $amazonlieferadresse->cHausnummer);
                $equal = $equal && ((empty($la->cZusatz) && empty($amazonlieferadresse->cZusatz)) || $la->cZusatz === $amazonlieferadresse->cZusatz);
                if ($equal) {
                    return intval($la->kLieferadresse);
                }
            }
            // if we get to this point, we found nothing
            return -1;
        } else {
            return -1;
        }
    }

    /*
     * Sets the JTL Bestellung to the state it should have after successful authorization.
     * It also triggers the sync with the WaWi.
     *
     * This means:
     * - The billing address is set correctly
     * - DOES NOT EXIST IN SHOP 4: the Bestellung nZahlungstyp is switched to signal for our own WaWi-Shop-Sync whether the order is authorized (just a safety measure), 1 means authorized
     * - the Bestellung is set to cAbgeholt = 'N'
     */

    public function setBestellungAuthorized($orid, $rechnungsadresse = NULL) {
        $order = $this->getOrder($orid, true);
        $bestellStatusOnAuth = $this->oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTHSTATE];
        try {
            $bestellNr = $order->bestellung->cBestellNr;
            if (!is_null($rechnungsadresse)) {
                // update rechnungsadresse from authorization data
                $kRechnungsadresse = $order->bestellung->kRechnungsadresse;
                if (intval($kRechnungsadresse) > 0) {
                    $neueRechnungsadresse = new Rechnungsadresse($kRechnungsadresse);
                    $neueRechnungsadresse->cVorname = $rechnungsadresse->cVorname;
                    $neueRechnungsadresse->cNachname = $rechnungsadresse->cNachname;
                    $neueRechnungsadresse->cStrasse = $rechnungsadresse->cStrasse;
                    $neueRechnungsadresse->cHausnummer = $rechnungsadresse->cHausnummer;
                    $neueRechnungsadresse->cOrt = $rechnungsadresse->cOrt;
                    $neueRechnungsadresse->cPLZ = $rechnungsadresse->cPLZ;
                    $neueRechnungsadresse->cLand = $rechnungsadresse->cLand;
                    $kunde = $this->getKundeForOrder($orid);
                    $neueRechnungsadresse->cMail = $kunde->cMail;
                    $neueRechnungsadresse->updateInDB();
                }
            }
            if(!$this->orderHasExpiredUnusedAuthorization($orid)) {
                $sql = "UPDATE tbestellung SET cAbgeholt='N', dBezahltDatum=CURDATE(), cStatus='{$bestellStatusOnAuth}' WHERE cBestellNr='{$bestellNr}'";
                Shop::DB()->query($sql, 4);
            }
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: Fehler beim Versuch, Bestellung auf authorized zu setzen: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
        }
    }

    /*
     * Sets the JTL Bestellung to the state it should have after successful capturing.
     * 
     * The Status of Bestellung is NOT changed, because this may lead to duplicate mails. It may be strange however, for the customer that the paid order is not marked as such.
     */

    public function setBestellungCaptured($orid) {
        $order = $this->getOrder($orid, true);
        $bestellNr = $order->bestellung->cBestellNr;
        try {
            /*
             * Also set the payment
             */
            $oZahlungseingang = new stdClass();
            $oZahlungseingang->kBestellung = $order->kBestellung;
            $oZahlungseingang->cZahlungsanbieter = "Amazon Payments";
            
            /*
             * Fix: The Order-Amount in the tbestellung table is in the default
             * currency. To have the Wawi match up the amounts correctly we have
             * to convert that amount to the original currency.
             */
            $paidAmount = $order->fOrderAmount;
            $kWaehrung = $order->bestellung->kWaehrung;
            
            // we convert from the default currency to the currency contained in the order
            $fromCurrency = $GLOBALS["DB"]->executeQuery("select * from twaehrung where cStandard = 'Y'", 1);
            $toCurrency = $GLOBALS["DB"]->executeQuery("select * from twaehrung where kWaehrung = {$kWaehrung}", 1);
            
            if($fromCurrency->kWaehrung !== $toCurrency->kWaehrung) {
                $paidAmount /= $fromCurrency->fFaktor;
                $paidAmount *= $toCurrency->fFaktor;
            }

            $oZahlungseingang->fBetrag = $paidAmount;
            $oZahlungseingang->fZahlungsgebuehr = "";
            $oZahlungseingang->cISO = "";
            $kunde = $this->getKundeForOrder($orid);
            $oZahlungseingang->cEmpfaenger = "";
            $oZahlungseingang->cZahler = $kunde->cMail;
            $oZahlungseingang->cAbgeholt = 'N';
            $oZahlungseingang->cHinweis = $orid;
            $oZahlungseingang->dZeit = strftime('%Y-%m-%d %H:%M:%S');
            Shop::DB()->insert('tzahlungseingang', $oZahlungseingang);
            Shop::DB()->query("UPDATE tbestellung SET dBezahltDatum=CURDATE() WHERE cBestellNr='{$bestellNr}'", 4);
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: Fehler beim Versuch, Bestellung auf captured zu setzen: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
        }
    }

    /*
     * Sets the JTL Bestellung to the state it should have after it was canceled from Amazon (or by the seller)
     *
     * The state is set to STORNO
     */

    public function setBestellungCanceled($orid) {
        $order = $this->getOrder($orid, true);
        try {
            $bestellNr = $order->bestellung->cBestellNr;
            $sql = "UPDATE tbestellung SET cStatus='-1' WHERE cBestellNr='{$bestellNr}'";
            Shop::DB()->query($sql, 4);
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: Fehler beim Versuch, Bestellung auf canceled zu setzen: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
        }
    }

    /*
     * Sets the JTL Bestellung to the state it should have after it was closed by amazon or the seller.
     *
     * This is currently not defined.
     */

    public function setBestellungClosed($orid) {
        return;
    }

    /*
     *
     */

    public function setBestellungAuthorizationPending($orid) {
        $order = $this->getOrder($orid, true);
        try {
            $bestellNr = $order->bestellung->cBestellNr;
            // ... cAbgeholt='Y' prevents it from going to the WaWi yet, cStatus='2' means it is now in state 'in Bearbeitung'.
            Shop::DB()->query("UPDATE tbestellung SET cAbgeholt='Y', cStatus='2' WHERE cBestellNr='{$bestellNr}'", 4);
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: Fehler beim Versuch, Bestellung auf authorization pending zu setzen: ' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
        }
    }

    public function getOrderReferenceIdByBestellNr($cBestellNr) {
        $sql = "SELECT * FROM " . S360_LPA_TABLE_ORDER . ", tbestellung WHERE tbestellung.kBestellung = " . S360_LPA_TABLE_ORDER . ".kBestellung AND tbestellung.cBestellNr = '{$cBestellNr}'";
        $result = Shop::DB()->query($sql, 1);
        if ($result) {
            return $result->cOrderReferenceId;
        } else {
            Jtllog::writeLog('LPA: LPADatabase->getOrderReferenceIdByBestellNr() : $result = false', JTLLOG_LEVEL_DEBUG);
            return false;
        }
    }

    /*
     * Returns true or false, indicating whether an object has reached its final stage. This also works with regard to child objects.
     */
    public function inFinalState($id, $type) {
        switch ($type) {
            case 'order':
                $order = $this->getOrder($id);
                $auths = $this->getAuthorizationsForOrder($id);
                foreach($auths as $auth) {
                    if(!$this->inFinalState($auth->cAuthorizationId, 'auth')) {
                        return false;
                    }
                }
                return ($order->cOrderStatus === S360_LPA_STATUS_CLOSED || $order->cOrderStatus === S360_LPA_STATUS_CANCELED);
            case 'auth':
                $auth = $this->getAuthorization($id);
                $caps = $this->getCapturesForAuthorization($id);
                /*
                 * We can only consider an auth closed if there is no capture still PENDING - this is due to the nature of how we handle
                 * captures during checkout - they always are considered PENDING (even if they are actually COMPLETED on the amazon side).
                 * 
                 * Therefore the related auth may be CLOSED, but the cap is still PENDING - we have to check the referencing captures first before
                 * determining whether the auth is in a final state or not.
                 */
                foreach($caps as $cap) {
                    if($cap->cCaptureStatus === S360_LPA_STATUS_PENDING) {
                        return false;
                    }
                }
                return ($auth->cAuthorizationStatus === S360_LPA_STATUS_CLOSED || $auth->cAuthorizationStatus === S360_LPA_STATUS_DECLINED);
            case 'cap':
                $cap = $this->getCapture($id);
                return ($cap->cCaptureStatus === S360_LPA_STATUS_CLOSED || $cap->cCaptureStatus === S360_LPA_STATUS_DECLINED);
            case 'refund':
                $refund = $this->getRefund($id);
                return ($refund->cRefundStatus === S360_LPA_STATUS_COMPLETED || $refund->cRefundStatus === S360_LPA_STATUS_DECLINED);
        }
    }

    public function exportTables($exportPath) {
        // create directories on the path if they dont exist yet, path is assumed to NOT end with /
        $parts = explode('/', $exportPath);
        $dir = '';
        foreach ($parts as $part) {
            if (!is_dir($dir .= "/$part")) {
                mkdir($dir);
            }
        }
        $this->exportOrderTable($exportPath . '/lpa_orders.csv');
        $this->exportAuthorizationTable($exportPath . '/lpa_authorizations.csv');
        $this->exportCaptureTable($exportPath . '/lpa_captures.csv');
        $this->exportRefundTable($exportPath . '/lpa_refunds.csv');
        $this->exportAccountMappingTable($exportPath . '/lpa_accounts.csv');
    }

    private function exportOrderTable($exportFile) {
        $sep = $this->csvSeparator;
        $sql = "SELECT * FROM " . S360_LPA_TABLE_ORDER;
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            $csvLine = "kBestellung{$sep}cOrderReferenceId{$sep}cOrderStatus{$sep}cOrderStatusReason{$sep}fOrderAmount{$sep}cOrderCurrencyCode{$sep}nOrderExpirationTimestamp{$sep}bSandbox\n";
            file_put_contents($exportFile, $csvLine);
            foreach ($result as $order) {
                /*
                  kBestellung INT(10) NOT NULL,
                  cOrderReferenceId VARCHAR(50) NOT NULL,
                  cOrderStatus VARCHAR(50),
                  cOrderStatusReason VARCHAR(50),
                  fOrderAmount DECIMAL(18,2),
                  cOrderCurrencyCode VARCHAR(50),
                  nOrderExpirationTimestamp INT,
                  bSandbox INT(1) NOT NULL
                 */
                $csvLine = "";
                $csvLine .= $order->kBestellung . $sep;
                $csvLine .= $order->cOrderReferenceId . $sep;
                $csvLine .= $order->cOrderStatus . $sep;
                $csvLine .= $order->cOrderStatusReason . $sep;
                $csvLine .= $order->fOrderAmount . $sep;
                $csvLine .= $order->cOrderCurrencyCode . $sep;
                $csvLine .= $order->nOrderExpirationTimestamp . $sep;
                $csvLine .= $order->bSandbox . "\n";
                file_put_contents($exportFile, $csvLine, FILE_APPEND);
            }
        } else {
            Jtllog::writeLog('LPA: LPADatabase->exportOrderTable() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    private function exportAuthorizationTable($exportFile) {
        $sep = $this->csvSeparator;
        $sql = "SELECT * FROM " . S360_LPA_TABLE_AUTHORIZATION;
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            $csvLine = "cOrderReferenceId{$sep}cAuthorizationId{$sep}cAuthorizationStatus{$sep}cAuthorizationStatusReason{$sep}fAuthorizationAmount{$sep}cAuthorizationCurrencyCode{$sep}fCapturedAmount{$sep}cCapturedCurrencyCode{$sep}bCaptureNow{$sep}nAuthorizationExpirationTimestamp{$sep}bSandbox\n";
            file_put_contents($exportFile, $csvLine);
            foreach ($result as $auth) {
                /*
                  cOrderReferenceId VARCHAR(50) NOT NULL,
                  cAuthorizationId VARCHAR(50) NOT NULL,
                  cAuthorizationStatus VARCHAR(50),
                  cAuthorizationStatusReason VARCHAR(50),
                  fAuthorizationAmount DECIMAL(18,2),
                  cAuthorizationCurrencyCode VARCHAR(50),
                  fCapturedAmount DECIMAL(18,2),
                  cCapturedCurrencyCode VARCHAR(50),
                  bCaptureNow INT(1) NOT NULL,
                  nAuthorizationExpirationTimestamp INT,
                  bSandbox INT(1) NOT NULL
                 */
                $csvLine = "";
                $csvLine .= $auth->cOrderReferenceId . $sep;
                $csvLine .= $auth->cAuthorizationId . $sep;
                $csvLine .= $auth->cAuthorizationStatus . $sep;
                $csvLine .= $auth->cAuthorizationStatusReason . $sep;
                $csvLine .= $auth->fAuthorizationAmount . $sep;
                $csvLine .= $auth->cAuthorizationCurrencyCode . $sep;
                $csvLine .= $auth->fCapturedAmount . $sep;
                $csvLine .= $auth->cCapturedCurrencyCode . $sep;
                $csvLine .= $auth->bCaptureNow . $sep;
                $csvLine .= $auth->nAuthorizationExpirationTimestamp . $sep;
                $csvLine .= $auth->bSandbox . "\n";
                file_put_contents($exportFile, $csvLine, FILE_APPEND);
            }
        } else {
            Jtllog::writeLog('LPA: LPADatabase->exportAuthorizationTable() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    private function exportCaptureTable($exportFile) {
        $sep = $this->csvSeparator;
        $sql = "SELECT * FROM " . S360_LPA_TABLE_CAPTURE;
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            $csvLine = "cAuthorizationId{$sep}cCaptureId{$sep}cCaptureStatus{$sep}cCaptureStatusReason{$sep}fCaptureAmount{$sep}cCaptureCurrencyCode{$sep}fRefundedAmount{$sep}cRefundedCurrencyCode{$sep}bSandbox\n";
            file_put_contents($exportFile, $csvLine);
            foreach ($result as $cap) {
                /*
                  cAuthorizationId VARCHAR(50) NOT NULL,
                  cCaptureId VARCHAR(50) NOT NULL,
                  cCaptureStatus VARCHAR(50),
                  cCaptureStatusReason VARCHAR(50),
                  fCaptureAmount DECIMAL(18,2),
                  cCaptureCurrencyCode VARCHAR(50),
                  fRefundedAmount DECIMAL(18,2),
                  cRefundedCurrencyCode VARCHAR(50),
                  bSandbox INT(1) NOT NULL
                 */
                $csvLine = "";
                $csvLine .= $cap->cAuthorizationId . $sep;
                $csvLine .= $cap->cCaptureId . $sep;
                $csvLine .= $cap->cCaptureStatus . $sep;
                $csvLine .= $cap->cCaptureStatusReason . $sep;
                $csvLine .= $cap->fCaptureAmount . $sep;
                $csvLine .= $cap->cCaptureCurrencyCode . $sep;
                $csvLine .= $cap->fRefundedAmount . $sep;
                $csvLine .= $cap->cRefundedCurrencyCode . $sep;
                $csvLine .= $cap->bSandbox . "\n";
                file_put_contents($exportFile, $csvLine, FILE_APPEND);
            }
        } else {
            Jtllog::writeLog('LPA: LPADatabase->exportCaptureTable() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    private function exportRefundTable($exportFile) {
        $sep = $this->csvSeparator;
        $sql = "SELECT * FROM " . S360_LPA_TABLE_REFUND;
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            $csvLine = "cCaptureId{$sep}cRefundId{$sep}cRefundStatus{$sep}cRefundStatusReason{$sep}cRefundType{$sep}fRefundAmount{$sep}cRefundCurrencyCode{$sep}bSandbox\n";
            file_put_contents($exportFile, $csvLine);
            foreach ($result as $refund) {
                /*
                  cCaptureId VARCHAR(50) NOT NULL,
                  cRefundId VARCHAR(50) NOT NULL,
                  cRefundStatus VARCHAR(50),
                  cRefundStatusReason VARCHAR(50),
                  cRefundType VARCHAR(50),
                  fRefundAmount DECIMAL(18,2),
                  cRefundCurrencyCode VARCHAR(50),
                  bSandbox INT(1) NOT NULL
                 */
                $csvLine = "";
                $csvLine .= $refund->cCaptureId . $sep;
                $csvLine .= $refund->cRefundId . $sep;
                $csvLine .= $refund->cRefundStatus . $sep;
                $csvLine .= $refund->cRefundStatusReason . $sep;
                $csvLine .= $refund->cRefundType . $sep;
                $csvLine .= $refund->fRefundAmount . $sep;
                $csvLine .= $refund->cRefundCurrencyCode . $sep;
                $csvLine .= $refund->bSandbox . "\n";
                file_put_contents($exportFile, $csvLine, FILE_APPEND);
            }
        } else {
            Jtllog::writeLog('LPA: LPADatabase->exportRefundTable() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    private function exportAccountMappingTable($exportFile) {
        $sep = $this->csvSeparator;
        $sql = "SELECT * FROM " . S360_LPA_TABLE_ACCOUNTMAPPING;
        $result = Shop::DB()->query($sql, 2);
        if ($result) {
            $csvLine = "kKunde{$sep}cAmazonId{$sep}nVerifiziert{$sep}cVerifizierungsCode\n";
            file_put_contents($exportFile, $csvLine);
            foreach ($result as $acc) {
                /*
                  kKunde INT(10) NOT NULL,
                  cAmazonId VARCHAR(255) NOT NULL,
                  nVerifiziert INT(1) NOT NULL DEFAULT 0,
                  cVerifizierungsCode VARCHAR(255)
                 */
                $csvLine = "";
                $csvLine .= $acc->kKunde . $sep;
                $csvLine .= $acc->cAmazonId . $sep;
                $csvLine .= $acc->nVerifiziert . $sep;
                $csvLine .= $acc->cVerifizierungsCode . "\n";
                file_put_contents($exportFile, $csvLine, FILE_APPEND);
            }
        } else {
            Jtllog::writeLog('LPA: LPADatabase->exportAccountMappingTable() : $result = array()', JTLLOG_LEVEL_DEBUG);
            return array();
        }
    }

    public function importTables($importPath) {
        $this->importOrderTable($importPath . '/lpa_orders.csv');
        $this->importAuthorizationTable($importPath . '/lpa_authorizations.csv');
        $this->importCaptureTable($importPath . '/lpa_captures.csv');
        $this->importRefundTable($importPath . '/lpa_refunds.csv');
        $this->importAccountMappingTable($importPath . '/lpa_accounts.csv');
    }

    private function importOrderTable($importFile) {
        if (!file_exists($importFile)) {
            return;
        }
        $data = $this->csv_to_array($importFile, $this->csvSeparator);
        foreach ($data as $obj) {
            $existingObj = $this->getOrder($obj->cOrderReferenceId);
            if (empty($existingObj)) {
                $this->saveOrderObject($obj);
            }
        }
    }

    private function importAuthorizationTable($importFile) {
        if (!file_exists($importFile)) {
            return;
        }
        $data = $this->csv_to_array($importFile, $this->csvSeparator);
        foreach ($data as $obj) {
            $existingObj = $this->getAuthorization($obj->cAuthorizationId);
            if (empty($existingObj)) {
                $this->saveAuthorizationObject($obj);
            }
        }
    }

    private function importCaptureTable($importFile) {
        if (!file_exists($importFile)) {
            return;
        }
        $data = $this->csv_to_array($importFile, $this->csvSeparator);
        foreach ($data as $obj) {
            $existingObj = $this->getCapture($obj->cCaptureId);
            if (empty($existingObj)) {
                $this->saveCaptureObject($obj);
            }
        }
    }

    private function importRefundTable($importFile) {
        if (!file_exists($importFile)) {
            return;
        }
        $data = $this->csv_to_array($importFile, $this->csvSeparator);
        foreach ($data as $obj) {
            $existingObj = $this->getRefund($obj->cRefundId);
            if (empty($existingObj)) {
                $this->saveRefundObject($obj);
            }
        }
    }

    private function importAccountMappingTable($importFile) {
        if (!file_exists($importFile)) {
            return;
        }
        $data = $this->csv_to_array($importFile, $this->csvSeparator);
        foreach ($data as $obj) {
            $existingObj = $this->getAccountMapping($obj->cAmazonId);
            if (empty($existingObj)) {
                $this->saveAccountMappingObject($obj);
            }
        }
    }

    public function insertOrUpdate($object, $table, $ignoreSandbox = FALSE) {
        $keyName = '';
        $checkSandbox = true;
        if ($table === S360_LPA_TABLE_ORDER) {
            $keyName = 'cOrderReferenceId';
        }
        if ($table === S360_LPA_TABLE_AUTHORIZATION) {
            $keyName = 'cAuthorizationId';
        }
        if ($table === S360_LPA_TABLE_CAPTURE) {
            $keyName = 'cCaptureId';
        }
        if ($table === S360_LPA_TABLE_REFUND) {
            $keyName = 'cRefundId';
        }
        if ($table === S360_LPA_TABLE_ACCOUNTMAPPING) {
            $keyName = 'cAmazonId';
            $checkSandbox = false; // accounts have no sandbox flag
        }
        $keyValue = $object->$keyName;

        // check if this object exists
        $sql = "SELECT {$keyName} FROM {$table} WHERE {$keyName} LIKE '{$keyValue}' LIMIT 1";
        $test = Shop::DB()->query($sql, 1);
        if ($test) {
            Shop::DB()->update($table, $keyName, $keyValue, $object);
        } else {
            // IMPORTANT: Only on insert is the sandbox-flag evaluated, else the objects themselves bring the right value
            if ($checkSandbox && empty($object->bSandbox) && !$ignoreSandbox) {
                $object->bSandbox = $this->getSandboxFlag();
            }
            Shop::DB()->insert($table, $object);
        }
    }

    private function getSandboxFlag() {
        if (!isset($this->config['sandbox']) || $this->config['sandbox'] === true) {
            return 1;
        } else {
            return 0;
        }
    }

    private function csv_to_array($filename = '', $delimiter = ',') {
        if (!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if ($header && count($row) !== count($header)) {
                    continue; // ignore non-matching lines
                }
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = (object) array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
    
    /**
     * Checks whether an order has an authorization assigned to it which has expired unused.
     * This is used to determine whether the tbestellung table should be updated when an order
     * is set to authorized.
     * 
     * @param type $orid
     * @return boolean
     */
    private function orderHasExpiredUnusedAuthorization($orid) {
        $auths = $this->getAuthorizationsForOrder($orid);
        foreach($auths as $auth) {
            if($auth->cAuthorizationStatus === S360_LPA_STATUS_CLOSED && $auth->cAuthorizationStatusReason === S360_LPA_REASON_EXPIRED_UNUSED) {
                return true;
            }
        }
        return false;
    }
}
