<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';

define('SOAP_ERROR', -1);

// 0 = Test
// 1 = Produktiv
define('TS_MODUS', 1);

if (!defined('TS_BUYERPROT_CLASSIC') || !defined('TS_BUYERPROT_EXCELLENCE')) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'defines_inc.php';
}

if (TS_MODUS == 1) {
    // Produktiv
    //define('TS_SERVER', 'https://protection.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_SERVER', 'https://www.trustedshops.de/ts/services/TsProtection?wsdl');
    define('TS_SERVER_PROTECTION', 'https://www.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_CHECK_SERVER', 'https://www.trustedshops.de/ts/services/TsRating?wsdl');
    define('TS_RATING_SERVER', 'https://www.trustedshops.de/ts/services/TsRating?wsdl');
} else {
    // Test
    //define('TS_SERVER', 'https://protection-qa.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_SERVER', 'https://qa.trustedshops.de/ts/services/TsProtection?wsdl');
    define('TS_SERVER_PROTECTION', 'https://protection-qa.trustedshops.com/ts/protectionservices/ApplicationRequestService?wsdl');
    define('TS_CHECK_SERVER', 'https://qa.trustedshops.de/ts/services/TsProtection?wsdl');
    define('TS_RATING_SERVER', 'https://qa.trustedshops.de/ts/services/TsRating?wsdl');
}

/**
 * Class TrustedShops
 */
class TrustedShops
{
    /**
     * @var int
     */
    public $kTrustedShopsZertifikat;

    /**
     * @var string
     */
    public $tsId;

    /**
     * @var string
     */
    public $tsProductId;

    /**
     * @var string
     */
    public $partnerPackage = 'JTL';

    /**
     * @var string
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $paymentType;

    /**
     * @var string
     */
    public $buyerEmail;

    /**
     * @var string
     */
    public $shopCustomerID;

    /**
     * @var string
     */
    public $shopOrderID;

    /**
     * @var string
     */
    public $orderDate;

    /**
     * @var string
     */
    public $shopSystemVersion;

    /**
     * @var string
     */
    public $wsUser;

    /**
     * @var string
     */
    public $wsPassword;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var int
     */
    public $eType;

    /**
     * @var string
     */
    public $dChecked;

    /**
     * @var string
     */
    public $cBoxText;

    /**
     * @var string
     */
    public $cLogoURL;

    /**
     * @var string
     */
    public $cSpeicherungURL;

    /**
     * @var string
     */
    public $cBedingungURL;

    /**
     * @var object
     */
    public $oKaeuferschutzProdukte;

    /**
     * @var object
     */
    public $oKaeuferschutzProdukteDB;

    /**
     * @var object
     */
    public $oZertifikat;

    /**
     * @var array
     */
    public $cLogoSiegelBoxURL;

    /**
     * @var int
     */
    public $kTrustedshopsKundenbewertung;

    /**
     * Konstruktor
     *
     * @param string|int $tsId - Falls angegeben, wird das Zertifikat mit Käuferschutzprodukten aus der DB geholt
     * @param string     $cISOSprache
     */
    public function __construct($tsId = '', $cISOSprache = '')
    {
        if ($tsId != -1 && strlen($tsId) > 0 && strlen($cISOSprache) > 0) {
            $this->fuelleTrustedShops($tsId, $cISOSprache);
        } elseif ($tsId != -1 && strlen($tsId) > 0) {
            $this->fuelleTrustedShops($tsId);
        } elseif (strlen($cISOSprache) > 0) {
            $this->fuelleTrustedShops($tsId, $cISOSprache);
        }
        if ($this->eType === TS_BUYERPROT_CLASSIC) {
            $this->deaktiviereZertifikat($tsId, $cISOSprache);
        }
    }

    /**
     * @param string $tsId
     * @param string $cISOSprache
     * @return $this
     */
    public function fuelleTrustedShops($tsId, $cISOSprache = '')
    {
        $conf    = Shop::getSettings([CONF_GLOBAL]);
        $cacheID = 'jtl_ts_' . $tsId . '_' . $cISOSprache;
        if (($artikel = Shop::Cache()->get($cacheID)) !== false) {
            foreach (get_object_vars($artikel) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        if ($tsId != -1 && strlen($tsId) > 0 && strlen($cISOSprache) > 0) {
            $this->oZertifikat = $this->gibTrustedShopsZertifikatISO($cISOSprache, $tsId);
            if (strlen($this->oZertifikat->cTSID) > 0) {
                $this->kTrustedShopsZertifikat = $this->oZertifikat->kTrustedShopsZertifikat;
                $this->tsId                    = $this->oZertifikat->cTSID;
                $this->wsUser                  = $this->oZertifikat->cWSUser;
                $this->wsPassword              = $this->oZertifikat->cWSPasswort;
                $this->nAktiv                  = $this->oZertifikat->nAktiv;
                $this->eType                   = $this->oZertifikat->eType;
                $this->dChecked                = $this->oZertifikat->dChecked;
            }
        } elseif ($tsId == -1) {
            $this->oZertifikat = $this->gibTrustedShopsZertifikatISO($cISOSprache);
            if (isset($this->oZertifikat->cTSID) && strlen($this->oZertifikat->cTSID) > 0) {
                $this->kTrustedShopsZertifikat = $this->oZertifikat->kTrustedShopsZertifikat;
                $this->tsId                    = $this->oZertifikat->cTSID;
                $this->wsUser                  = $this->oZertifikat->cWSUser;
                $this->wsPassword              = $this->oZertifikat->cWSPasswort;
                $this->nAktiv                  = $this->oZertifikat->nAktiv;
                $this->eType                   = $this->oZertifikat->eType;
                $this->dChecked                = $this->oZertifikat->dChecked;
            }
        } else {
            $this->oZertifikat = $this->gibTrustedShopsZertifikatTSID($tsId);
            if (isset($this->oZertifikat->cTSID) && strlen($this->oZertifikat->cTSID) > 0) {
                $this->kTrustedShopsZertifikat = $this->oZertifikat->kTrustedShopsZertifikat;
                $this->tsId                    = $this->oZertifikat->cTSID;
                $this->wsUser                  = $this->oZertifikat->cWSUser;
                $this->wsPassword              = $this->oZertifikat->cWSPasswort;
                $this->nAktiv                  = $this->oZertifikat->nAktiv;
                $this->eType                   = $this->oZertifikat->eType;
                $this->dChecked                = $this->oZertifikat->dChecked;
            }
        }

        if (isset($this->oZertifikat->kTrustedShopsZertifikat) && $this->oZertifikat->kTrustedShopsZertifikat > 0 && $this->oZertifikat->nAktiv == 1) {
            $this->holeKaeuferschutzProdukteDB($this->oZertifikat->cISOSprache);

            $cShopName = 'JTL-Shop';
            if (isset($conf['global']['global_shopname']) && strlen($conf['global']['global_shopname']) > 0) {
                $cShopName = $conf['global']['global_shopname'];
            }

            $this->cLogoURL                = 'https://www.trustedshops.com/shop/certificate.php?shop_id=' . $this->tsId;
            $this->cSpeicherungURL         = 'https://www.trustedshops.com/shop/data_privacy.php?shop_id=' . $this->tsId;
            $this->cBedingungURL           = 'https://www.trustedshops.com/shop/protection_conditions.php?shop_id=' . $this->tsId;
            $this->cLogoSiegelBoxURL['de'] = 'https://www.trustedshops.de/profil/' . urlencode($cShopName) . '_' . $this->tsId . '.html';
            $this->cLogoSiegelBoxURL['en'] = 'https://www.trustedshops.com/profile/' . urlencode($cShopName) . '_' . $this->tsId . '.html';
            $this->cLogoSiegelBoxURL['nl'] = 'https://www.trustedshops.nl/shop/certificate.php?shop_id=' . $this->tsId;
            $this->cLogoSiegelBoxURL['it'] = 'https://www.trustedshops.it/shop/certificate.php?shop_id=' . $this->tsId;

            $this->cBoxText['de']      = "Die im K&auml;uferschutz enthaltene <a href='" . $this->cBedingungURL . "' target='_blank'>Trusted Shops Garantie</a> sichert Ihren Online-Kauf ab. " .
                "Mit der &Uuml;bermittlung und <a href='" . $this->cSpeicherungURL . "' target='_blank'>Speicherung</a> meiner E-Mail-Adresse zur " .
                "Abwicklung des K&auml;uferschutzes durch Trusted Shops bin ich einverstanden. <a href='" . $this->cBedingungURL . "' target='_blank'>Garantiebedingungen</a> f&uuml;r den K&auml;uferschutz.";
            $this->cBoxText['en']      = "The Trusted Shops buyer protection secures your online purchase. I agree with the transfer and <a href='" . $this->cSpeicherungURL .
                "' target='_blank'>saving</a> of my email address for the buyer protection handling by Trusted Shops. <a href='" . $this->cBedingungURL .
                "' target='_blank'>Conditions</a> for the buyer protection.";
            $this->cBoxText['nl']      = "De in de koperbescherming inbegrepen Trusted Shops Garantie beveiligt uw online aankoop. Ik ga akkoord met de doorgifte en de <a href='" .
                $this->cSpeicherungURL . "' target='_blank'>opslag</a> van mijn E-mailadres voor de afwikkeling van de koperbescherming door Trusted Shops. <a href='" .
                $this->cBedingungURL . "' target='_blank'>Garantievoorwaarden</a>  voor de koperbescherming.";
            $this->cBoxText['it']      = "La protezione acquirenti di Trusted Shops rende sicuri i tuoi acquisti online. Do il mio assenso al trasferimento e al <a href='" .
                $this->cSpeicherungURL . "' target='_blank'>salvataggio</a> del mio indirizzo e-mail per lelaborazione della protezione acquirenti da parte di Trusted Shops. <a href='" .
                $this->cBedingungURL . "' target='_blank'>Condizioni</a> della protezione acquirenti.";
            $this->cBoxText['default'] = $this->cBoxText['de'];
        }
        Shop::Cache()->set($cacheID, $this, [CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);

        return $this;
    }

    /**
     * Sendet nach einer Bestellung den Käuferschutzantrag nach TrustedShops
     *
     * @return bool
     */
    public function sendeBuchung()
    {
        if ($this->pruefeZertifikat(StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])) != 1) {
            writeLog(PFAD_LOGFILES . 'tskaeuferschutz.log', 'TS certificate is invalid.', 1);

            return false;
        }
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);
        $returnValue = '';
        $wsdlUrl     = TS_SERVER_PROTECTION;
        if (pruefeSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //set return value for the case if a SOAP exception occurs
            $returnValue = SOAP_ERROR;
            //call WS method
            $returnValue = $client->requestForProtectionV2(
                $this->tsId,
                $this->tsProductId,
                (float)$this->amount,
                $this->currency,
                $this->paymentType,
                $this->buyerEmail,
                $this->shopCustomerID,
                $this->shopOrderID,
                $this->orderDate,
                $this->shopSystemVersion,
                $this->wsUser,
                $this->wsPassword
            );
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                writeLog(PFAD_LOGFILES . 'trustedshops.log', $errorText, 1);
            }
        } else {
            writeLog(PFAD_LOGFILES . 'trustedshops.log', 'SOAP could not be loaded.', 1);
        }
        //check return value
        //negative return value is an error code, positive value is the protection number
        if ($returnValue == SOAP_ERROR) {
            writeLog(PFAD_LOGFILES . 'trustedshops.log', SOAP_ERROR, 1);
        } elseif ($returnValue < 0) {
            switch ($returnValue) {
                case -10001 :
                    break;
            }
        } else {
            writeLog(
                PFAD_LOGFILES . 'tskaeuferschutz.log',
                'TS Web Service has successfully protected transaction, protectioner is ' .
                    $returnValue . ' - Amount: ' . (float)$this->amount,
                1
            );
        }

        return true;
    }

    /**
     * @return bool
     * @todo: $cISOSprache???
     */
    public function holeStatus()
    {
        $returnValue = null;
        $wsdlUrl     = TS_SERVER;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);

        if (pruefeSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //set return value for the case if a SOAP exception occurs
            $returnValue = SOAP_ERROR;
            //call WS method
            $returnValue = $client->getRequestState($this->tsId);
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                writeLog(PFAD_LOGFILES . 'trustedshops.log', $errorText, 1);
            }
        } else {
            writeLog(PFAD_LOGFILES . 'trustedshops.log', 'SOAP could not be loaded.', 1);
        }
        // Geaendert aufgrund Mail von Herrn van der Wielen
        // Quote: 'Tatsächlich jedoch sollten Zertifikate mit den Status 'PRODUCTION', 'INTEGRATION' (und 'TEST') akzeptiert werden.'
        $languageIso = StringHandler::convertISO2ISO639($_SESSION['cISOSprache']);
        return (
            ($returnValue->stateEnum === 'PRODUCTION' ||
                $returnValue->stateEnum === 'TEST' ||
                $returnValue->stateEnum === 'INTEGRATION'
            ) && $returnValue->certificationLanguage == $languageIso
        );
    }

    /**
     * Lädt anhand der tsID von der TrustedShops API, die Käuferschutzprodukte und
     * speichert diese direkt in die DB
     *
     * @param int $kTrustedShopsZertifikat
     * @return $this
     */
    public function holeKaeuferschutzProdukte($kTrustedShopsZertifikat)
    {
        $returnValue = null;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);
        $wsdlUrl = TS_SERVER;
        if (pruefeSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //set return value for the case if a SOAP exception occurs
            $returnValue = SOAP_ERROR;
            //call WS method
            $returnValue = $client->getProtectionItems($this->tsId);

            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                writeLog(PFAD_LOGFILES . 'trustedshops.log', $errorText, 1);
            }
        } else {
            writeLog(PFAD_LOGFILES . 'trustedshops.log', 'SOAP could not be loaded.', 1);
        }

        if (isset($returnValue->item) && is_object($returnValue->item)) {
            $oTmp                = $returnValue->item;
            $returnValue->item   = [];
            $returnValue->item[] = $oTmp;
        }

        $this->oKaeuferschutzProdukte = $returnValue;

        if (isset($this->oKaeuferschutzProdukte->item) && is_array($this->oKaeuferschutzProdukte->item) && count($this->oKaeuferschutzProdukte->item) > 0) {
            $cLandISO = isset($_SESSION['Lieferadresse']->cLand) ? $_SESSION['Lieferadresse']->cLand : null;
            if (!$cLandISO) {
                $cLandISO = isset($_SESSION['TrustedShops']->oSprache->cISOSprache)
                    ? $_SESSION['TrustedShops']->oSprache->cISOSprache
                    : $_SESSION['Kunde']->cLand;
            }

            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Warenkorb.php';
            unset($_SESSION['Warenkorb']);
            $_SESSION['Warenkorb'] = new Warenkorb();
            foreach ($this->oKaeuferschutzProdukte->item as $i => $oItem) {
                $this->oKaeuferschutzProdukte->item[$i]->protectedAmountDecimalLocalized =
                    gibPreisStringLocalized($oItem->protectedAmountDecimal);

                if (isset($_SESSION['Warenkorb'], $_SESSION['Steuersatz']) &&
                    (!isset($_SESSION['Kundengruppe']->nNettoPreise) || !$_SESSION['Kundengruppe']->nNettoPreise)
                ) {
                    $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = gibPreisStringLocalized($oItem->netFee *
                        ((100 + (float)$_SESSION['Steuersatz'][$_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLandISO)]) / 100));
                    $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('incl', 'productDetails') .
                        ' ' . Shop::Lang()->get('vat', 'productDetails');
                } else {
                    $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = gibPreisStringLocalized($oItem->netFee);
                    $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('excl', 'productDetails') .
                        ' ' . Shop::Lang()->get('vat', 'productDetails');
                }

                // DB Member füllen
                if (!isset($this->oKaeuferschutzProdukteDB->item[$i])) {
                    $this->oKaeuferschutzProdukteDB->item[$i] = new stdClass();
                }
                $this->oKaeuferschutzProdukteDB->item[$i]->nID        = $oItem->id;
                $this->oKaeuferschutzProdukteDB->item[$i]->nWert      = $oItem->protectedAmountDecimal;
                $this->oKaeuferschutzProdukteDB->item[$i]->cWaehrung  = $oItem->currency;
                $this->oKaeuferschutzProdukteDB->item[$i]->cProduktID = $oItem->tsProductID;
                $this->oKaeuferschutzProdukteDB->item[$i]->fNetto     = $oItem->netFee;
                $this->oKaeuferschutzProdukteDB->item[$i]->fBrutto    = $oItem->grossFee;
                $this->oKaeuferschutzProdukteDB->item[$i]->dDatum     = date('Y-m-d H:i:s');
            }
            $this->speicherKaeuferschutzProdukteDB($kTrustedShopsZertifikat);
        }

        return $this;
    }

    /**
     * Lädt die Käuferschutzprodukte aus der Datenbank
     *
     * @param string $cISOSprache
     * @param bool   $bWaehrendBestellung
     * @return $this
     */
    public function holeKaeuferschutzProdukteDB($cISOSprache, $bWaehrendBestellung = false)
    {
        $oZertifikat = $this->gibTrustedShopsZertifikatISO($cISOSprache);

        $cSQL = '';
        if ($bWaehrendBestellung) {
            $cISOWaehrung = 'EUR';
            if (strlen($_SESSION['cWaehrungName']) > 0) {
                $cISOWaehrung = $_SESSION['cWaehrungName'];
            }

            $cSQL = " AND cWaehrung = '" . $cISOWaehrung . "' ";
        }

        if (isset($oZertifikat->kTrustedShopsZertifikat) && $oZertifikat->kTrustedShopsZertifikat > 0) {
            if ($this->oKaeuferschutzProdukteDB === null) {
                $this->oKaeuferschutzProdukteDB = new stdClass();
            }

            $this->oKaeuferschutzProdukteDB->item = Shop::DB()->query(
                "SELECT *
                    FROM ttrustedeshopsprodukt
                    WHERE kTrustedShopsZertifikat = " . $oZertifikat->kTrustedShopsZertifikat . $cSQL . "
                    ORDER BY cWaehrung, nWert", 2
            );
        }

        if ($this->oKaeuferschutzProdukteDB !== null && is_array($this->oKaeuferschutzProdukteDB->item) && count($this->oKaeuferschutzProdukteDB->item) > 0) {
            $cLandISO = isset($_SESSION['Lieferadresse']->cLand) ? $_SESSION['Lieferadresse']->cLand : null;
            if (!$cLandISO && isset($_SESSION['Kunde']->cLand)) {
                $cLandISO = $_SESSION['Kunde']->cLand;
            } else {
                $cLandISO = null;
            }
            foreach ($this->oKaeuferschutzProdukteDB->item as $i => $oItem) {
                if ($bWaehrendBestellung) {
                    $fPreis = $oItem->fNetto;
                    $nWert  = $oItem->nWert;
                    // Std Währung
                    $oWaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
                    // Nicht Standard im Shop?
                    if ($_SESSION['Waehrung']->kWaehrung != $oWaehrung->kWaehrung) {
                        $fPreis = $oItem->fNetto / $_SESSION['Waehrung']->fFaktor;
                        $nWert  = $oItem->nWert / $_SESSION['Waehrung']->fFaktor;
                    }
                    if ($this->oKaeuferschutzProdukte === null) {
                        $this->oKaeuferschutzProdukte = new stdClass();
                    }
                    if (!isset($this->oKaeuferschutzProdukte->item)) {
                        $this->oKaeuferschutzProdukte->item = [];
                    }
                    if (!isset($this->oKaeuferschutzProdukte->item[$i])) {
                        $this->oKaeuferschutzProdukte->item[$i] = new stdClass();
                    }
                    $this->oKaeuferschutzProdukte->item[$i]->protectedAmountDecimalLocalized = gibPreisStringLocalized($nWert);
                    $this->oKaeuferschutzProdukte->item[$i]->id                              = $oItem->nID;
                    $this->oKaeuferschutzProdukte->item[$i]->currency                        = $oItem->cWaehrung;
                    $this->oKaeuferschutzProdukte->item[$i]->grossFee                        = $oItem->fBrutto;
                    $this->oKaeuferschutzProdukte->item[$i]->netFee                          = $oItem->fNetto;
                    $this->oKaeuferschutzProdukte->item[$i]->protectedAmountDecimal          = $oItem->nWert;
                    $this->oKaeuferschutzProdukte->item[$i]->tsProductID                     = $oItem->cProduktID;

                    if (!$_SESSION['Kundengruppe']->nNettoPreise && isset($_SESSION['Warenkorb'], $_SESSION['Steuersatz'])) {
                        $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = gibPreisStringLocalized(
                            $fPreis *
                            ((100 + (float)$_SESSION['Steuersatz'][$_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLandISO)]) / 100)
                        );
                        $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('incl', 'productDetails') .
                            ' ' .
                            Shop::Lang()->get('vat', 'productDetails');
                    } else {
                        $this->oKaeuferschutzProdukte->item[$i]->grossFeeLocalized = gibPreisStringLocalized($fPreis);
                        $this->oKaeuferschutzProdukte->item[$i]->cFeeTxt           = Shop::Lang()->get('excl', 'productDetails') .
                            ' ' .
                            Shop::Lang()->get('vat', 'productDetails');
                    }
                }
                $this->oKaeuferschutzProdukteDB->item[$i]->protectedAmountDecimalLocalized =
                    gibPreisStringLocalized(isset($oItem->protectedAmountDecimal) ? $oItem->protectedAmountDecimal : 0);
            }
        }

        return $this;
    }

    /**
     * Speichert Käuferschutzprodukte in die Datenbank
     *
     * @param int $kTrustedShopsZertifikat
     * @return bool
     */
    public function speicherKaeuferschutzProdukteDB($kTrustedShopsZertifikat)
    {
        if ($kTrustedShopsZertifikat > 0 &&
            is_array($this->oKaeuferschutzProdukteDB->item) &&
            count($this->oKaeuferschutzProdukteDB->item) > 0
        ) {
            Shop::DB()->delete('ttrustedeshopsprodukt', 'kTrustedShopsZertifikat', (int)$kTrustedShopsZertifikat);
            foreach ($this->oKaeuferschutzProdukteDB->item as $oKaeuferschutzProdukt) {
                $oKaeuferschutzProdukt->kTrustedShopsZertifikat = $kTrustedShopsZertifikat;
                if (!isset($oKaeuferschutzProdukt->kSprache)) {
                    $oKaeuferschutzProdukt->kSprache = 0;
                }
                unset($oKaeuferschutzProdukt->protectedAmountDecimalLocalized);
                Shop::DB()->insert('ttrustedeshopsprodukt', $oKaeuferschutzProdukt);
            }

            return true;
        }

        return false;
    }

    /**
     * Prüft über die API von Trusted Shops, ob die tsID gültig ist
     *
     * @param string $cISOSprache
     * @param bool   $bSaved
     * @return bool|int
     */
    public function pruefeZertifikat($cISOSprache, $bSaved = false)
    {
        //@todo: $cTSClassicID is undefined?
        //$cTSClassicID = filterXSS($cTSClassicID);
        $returnValue = null;
        $bForce      = $bSaved;
        if ($this->dChecked !== null && $this->dChecked !== '0000-00-00 00:00:00') {
            $oDateTime = new DateTime($this->dChecked);
            $oDateTime->modify('+1 day');
            if ($oDateTime->format('U') < time()) {
                $bForce = true;
            }
        }

        if ($this->dChecked === null || $this->dChecked === '0000-00-00 00:00:00' || $bForce) {
            Jtllog::writeLog(utf8_decode('Die Zertifikatsprüfung von TrustedShops wurde eingeleitet!'), JTLLOG_LEVEL_NOTICE);
            //call TS protection web service
            ini_set('soap.wsdl_cache_enabled', 1);

            $wsdlUrl = TS_SERVER;
            $cTSID   = $this->tsId;

            if (pruefeSOAP($wsdlUrl)) {
                $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
                //set return value for the case if a SOAP exception occurs
                $returnValue = SOAP_ERROR;
                //call WS method
                $returnValue = $client->checkCertificate($cTSID);
                if (is_soap_fault($returnValue)) {
                    $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                    writeLog(PFAD_LOGFILES . 'trustedshops.log', $errorText, 1);
                    Jtllog::writeLog(utf8_decode('Bei der Zertifikatsprüfung von TrustedShops ist ein Fehler aufgetreten! Error: ') . $errorText, JTLLOG_LEVEL_ERROR);

                    return 11; // SOAP Fehler
                } else {
                    writeLog(PFAD_LOGFILES . 'trustedshops.log', print_r($returnValue, 1), 1);
                    Jtllog::writeLog(utf8_decode('Die Zertifikatsprüfung von TrustedShops ergab folgendes Ergebnis: ') . print_r($returnValue, true), JTLLOG_LEVEL_NOTICE);

                    $this->dChecked = date('Y-m-d H:i:s');
                    if (!$bSaved) {
                        Shop::DB()->query("
                            UPDATE ttrustedshopszertifikat 
                                SET dChecked = '{$this->dChecked}' 
                                WHERE kTrustedShopsZertifikat = {$this->kTrustedShopsZertifikat}", 3
                        );
                    }
                }
            } else {
                writeLog(PFAD_LOGFILES . 'trustedshops.log', 'SOAP could not be loaded.', 1);
                Jtllog::writeLog(utf8_decode('Es ist kein SOAP möglich um eine Zertifikatsprüfung von TrustedShops durchzuführen!'), JTLLOG_LEVEL_ERROR);

                return 11; // SOAP Fehler
            }
        } else {
            return 1;
        } // keine Prüfung, OK zurückgeben

        // Geaendert aufgrund Mail von Herrn van der Wielen
        // Quote: 'Tatsächlich jedoch sollten Zertifikate mit den Status 'PRODUCTION', 'INTEGRATION' (und 'TEST') akzeptiert werden.'
        if (($returnValue->stateEnum === 'PRODUCTION' || $returnValue->stateEnum === 'TEST' || $returnValue->stateEnum === 'INTEGRATION') &&
            ($cISOSprache === null || $returnValue->certificationLanguage === $cISOSprache) && $returnValue->typeEnum === $this->eType) {
            return 1;
        } // Alles O.K.
        if ($returnValue->stateEnum === 'INVALID_TS_ID') {
            Jtllog::writeLog("TrustedShops Zertifikat {$cTSID} existiert nicht!", JTLLOG_LEVEL_ERROR);
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 2; // Das Zertifikat existiert nicht
        }
        if ($returnValue->stateEnum === 'CANCELLED') {
            Jtllog::writeLog("TrustedShops Zertifikat {$cTSID} ist abgelaufen!", JTLLOG_LEVEL_ERROR);
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 3; // Das Zertifikat ist abgelaufen
        }
        if ($returnValue->stateEnum === 'DISABLED') {
            Jtllog::writeLog("TrustedShops Zertifikat {$cTSID} ist gesperrt!", JTLLOG_LEVEL_ERROR);
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 4; // Das Zertifikat ist gesperrt
        }
        if (strlen($returnValue->certificationLanguage) > 0 && strtolower($returnValue->certificationLanguage) !== strtolower($cISOSprache)) {
            Jtllog::writeLog("TrustedShops Zertifikat {$cTSID} wurde aufgrund falscher Sprache {$cISOSprache} deaktiviert (erwartet: {$returnValue->certificationLanguage})!", JTLLOG_LEVEL_ERROR);
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 7; // Falsche Sprache
        }
        if ($returnValue->typeEnum !== $this->eType) {
            Jtllog::writeLog("TrustedShops Zertifikat {$cTSID} deaktiviert. (falsche TS-Variante)!", JTLLOG_LEVEL_ERROR);
            $this->deaktiviereZertifikat($cTSID, $cISOSprache);

            return 10; // Falsche Variante
        }

        return false;
    }

    /**
     * Prüft über die API von Trusted Shops, ob die Logindaten gültig sind
     *
     * @return bool
     */
    public function pruefeLogin()
    {
        $returnValue = null;
        $wsdlUrl     = TS_SERVER;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);

        if (pruefeSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //set return value for the case if a SOAP exception occurs
            $returnValue = SOAP_ERROR;
            //call WS method
            $returnValue = $client->checkLogin($this->tsId, $this->wsUser, $this->wsPassword);

            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                writeLog(PFAD_LOGFILES . 'trustedshops.log', $errorText, 1);
            }
        } else {
            writeLog(PFAD_LOGFILES . 'trustedshops.log', 'SOAP could not be loaded.', 1);
        }

        if ($returnValue == 1) {
            return true;
        }
        Jtllog::writeLog("TrustedShops Fehler {$returnValue} bei Client Authentifizierung mit tsId={$this->tsId}, wsUser={$this->wsUser}, wsPasswort={$this->wsPassword}", JTLLOG_LEVEL_ERROR);

        return false;
    }

    /**
     * Schau ob ein Zertifikat in der Datenbank vorhanden ist und deaktiviert dieses dann
     *
     * @param string $cTSID
     * @param string $cISOSprache
     * @return bool
     */
    public function deaktiviereZertifikat($cTSID, $cISOSprache)
    {
        if (strlen($cTSID) > 0) {
            $this->nAktiv = 0;

            // Prüfe ob das Zertifikat vorhanden ist
            $oZertifikat = Shop::DB()->select('ttrustedshopszertifikat', 'cTSID', Shop::DB()->escape($cTSID), 'cISOSprache', Shop::DB()->escape($cISOSprache));
            if (isset($oZertifikat->kTrustedShopsZertifikat) && $oZertifikat->kTrustedShopsZertifikat > 0) {
                $nRow = Shop::DB()->query(
                    "UPDATE ttrustedshopszertifikat
                        SET nAktiv = 0
                        WHERE kTrustedShopsZertifikat = " . (int)$oZertifikat->kTrustedShopsZertifikat, 3
                );

                if ($nRow > 0) {
                    Jtllog::writeLog('Das TrustedShops Zertifikat mit der ID ' . $cTSID . ' wurde deaktiviert!', JTLLOG_LEVEL_NOTICE);

                    return true;
                }

                return false;
            }

            return false;
        }

        return false;
    }

    /**
     * Holt anhand der cISOSprache das Trusted Shops Zertifikat aus der Datenbank
     *
     * @param string $cISOSprache
     * @param string $tsId
     * @return null|stdClass
     */
    public function gibTrustedShopsZertifikatISO($cISOSprache, $tsId = '')
    {
        $oZertifikat = null;
        if (strlen($cISOSprache) > 0 && strlen($tsId) > 0) {
            $oZertifikat = Shop::DB()->select('ttrustedshopszertifikat', 'cISOSprache', $cISOSprache, 'cTSID', $tsId);
            $oZertifikat = $this->entschluesselTSDaten($oZertifikat);
        } elseif (strlen($cISOSprache) > 0) {
            $oZertifikat = Shop::DB()->select('ttrustedshopszertifikat', 'cISOSprache', $cISOSprache);
            $oZertifikat = $this->entschluesselTSDaten($oZertifikat);
        }

        return $oZertifikat;
    }

    /**
     * Holt anhand der tsID das Trusted Shops Zertifikat aus der Datenbank
     *
     * @param $cTSID
     * @return null|stdClass
     */
    public function gibTrustedShopsZertifikatTSID($cTSID)
    {
        $oZertifikat = null;

        if (strlen($cTSID) > 0) {
            $oZertifikat = Shop::DB()->select('ttrustedshopszertifikat', 'cTSID', $cTSID);
            $oZertifikat = $this->entschluesselTSDaten($oZertifikat);
        }

        return $oZertifikat;
    }

    // Speichert ein Zertifikat in die Datenbank
    // 1 = Alles O.K.
    // 2 = Das Zertifikat existiert nicht
    // 3 = Das Zertifikat ist abgelaufen
    // 4 = Das Zertifikat ist gesperrt
    // 5 = Shop befindet sich in der Zertifizierung
    // 6 = Keine Excellence-Variante mit Käuferschutz im Checkout-Prozess
    // 7 = Falsche Sprache
    // 8 = Benutzername & Passwort ungültig
    // 9 = Zertifikat konnte nicht gespeichert werden
    // 10 = Falsche Käuferschutzvariante
    // 11 = SOAP Fehler
    /**
     * @param stdClass $oZertifikat
     * @param int      $kTrustedShopsZertifikat
     * @return int
     */
    public function speicherTrustedShopsZertifikat($oZertifikat, $kTrustedShopsZertifikat = 0)
    {
        if (strlen($oZertifikat->cISOSprache) > 0 && strlen($oZertifikat->cTSID) > 0) {
            if ($kTrustedShopsZertifikat > 0) {
                $oZertifikat->kTrustedShopsZertifikat = $kTrustedShopsZertifikat;
                $this->kTrustedShopsZertifikat        = $kTrustedShopsZertifikat;
            }
            $this->tsId       = $oZertifikat->cTSID;
            $this->wsUser     = $oZertifikat->cWSUser;
            $this->wsPassword = $oZertifikat->cWSPasswort;
            $this->nAktiv     = $oZertifikat->nAktiv;
            $this->eType      = $oZertifikat->eType;

            // 1 = Alles O.K.
            // 2 = Das Zertifikat existiert nicht
            // 3 = Das Zertifikat ist abgelaufen
            // 4 = Das Zertifikat ist gesperrt
            // 5 = Shop befindet sich in der Zertifizierung
            // 6 = Keine Excellence-Variante mit Käuferschutz im Checkout-Prozess
            // 7 = Falsche Sprache
            // 10 = Falsche Käuferschutzvariante

            $nReturnValue = $this->pruefeZertifikat($oZertifikat->cISOSprache, true);

            $this->nAktiv        = 0;
            $oZertifikat->nAktiv = 0;

            if ($nReturnValue == 1) {
                if ($this->eType === TS_BUYERPROT_CLASSIC) {
                    $this->nAktiv        = 0;
                    $oZertifikat->nAktiv = 0;
                } elseif ($this->pruefeLogin()) {
                    $this->nAktiv        = 1;
                    $oZertifikat->nAktiv = 1;
                }
            }
            $oZertifikat = $this->verschluesselTSDaten($oZertifikat);
            Shop::DB()->query(
                "DELETE ttrustedshopszertifikat, ttrustedeshopsprodukt 
                    FROM ttrustedshopszertifikat
                    LEFT JOIN ttrustedeshopsprodukt 
                        ON ttrustedeshopsprodukt.kTrustedShopsZertifikat = ttrustedshopszertifikat.kTrustedShopsZertifikat
                        WHERE ttrustedshopszertifikat.cISOSprache = '" . $oZertifikat->cISOSprache . "'", 4
            );

            $oZertifikat->dChecked = $this->dChecked;
            if ($oZertifikat->dChecked === '') {
                $oZertifikat->dChecked = 'now()';
            }
            unset($oZertifikat->kTrustedShopsZertifikat);
            $kTrustedShopsZertifikat = Shop::DB()->insert('ttrustedshopszertifikat', $oZertifikat);

            if ($kTrustedShopsZertifikat > 0) {
                if ($this->eType === TS_BUYERPROT_EXCELLENCE) {
                    $this->holeKaeuferschutzProdukte($kTrustedShopsZertifikat);
                }

                if ($nReturnValue == 2) {
                    return 2;
                } // Das Zertifikat existiert nich
                if ($nReturnValue == 3) {
                    return 3;
                } // Das Zertifikat ist abgelaufen
                if ($nReturnValue == 4) {
                    return 4;
                } // Das Zertifikat ist gesperrt
                if ($nReturnValue == 5) {
                    return 5;
                } // Shop befindet sich in der Zertifizierung
                if ($nReturnValue == 6) {
                    return 6;
                } // Keine Excellence-Variante mit Käuferschutz im Checkout-Prozess
                if ($nReturnValue == 7) {
                    return 7;
                } // Falsche Sprache
                if ($nReturnValue == 10) {
                    return 10;
                } // Falsche Variante
                if ($nReturnValue == 11) {
                    return 11;
                } // SOAP Fehler

                return -1;
            } else {
                return 9; // Zertifikat konnte nicht gespeichert werden
            }
        }

        return 1;
    }

    /**
     * Löscht ein Zertifikat aus der Datenbank
     *
     * @param int $kTrustedShopsZertifikat
     * @return bool
     */
    public function loescheTrustedShopsZertifikat($kTrustedShopsZertifikat)
    {
        if ((int)$kTrustedShopsZertifikat > 0) {
            $nRows = Shop::DB()->delete('ttrustedshopszertifikat', 'kTrustedShopsZertifikat', (int)$kTrustedShopsZertifikat);

            return ($nRows > 0);
        }

        return false;
    }

    /**
     * Gibt den aktuellen Kundenbewertungsstatus aus der DB zurück
     *
     * @param string $cISOSprache
     * @return object|bool
     */
    public function holeKundenbewertungsstatus($cISOSprache)
    {
        if (strlen($cISOSprache) > 0) {
            $oTrustedShopsKundenbewertung = Shop::DB()->select('ttrustedshopskundenbewertung', 'cISOSprache', Shop::DB()->escape($cISOSprache));
            if (isset($oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung) && $oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung > 0) {
                return $oTrustedShopsKundenbewertung;
            }
        }

        return false;
    }

    /**
     * Prüft ob eine TSID bereits in anderen Sprachen vorhanden ist
     *
     * @param string $cTSID
     * @param string $cISOSprache
     * @return bool
     */
    public function pruefeKundenbewertungsstatusAndereSprache($cTSID, $cISOSprache)
    {
        if (strlen($cTSID) > 0 && strlen($cISOSprache) > 0) {
            $oTrustedShopsKundenbewertung_arr = Shop::DB()->executeQueryPrepared(
                "SELECT *
                    FROM ttrustedshopskundenbewertung
                    WHERE cTSID = :id
                        AND cISOSprache != :iso",
                ['id' => $cTSID, 'iso' => $cISOSprache],
                2
            );

            return (is_array($oTrustedShopsKundenbewertung_arr) && count($oTrustedShopsKundenbewertung_arr) > 0);
        }

        return false;
    }

    /**
     * @param int    $nStatus
     * @param string $cISOSprache
     * @return $this
     */
    public function aenderKundenbewertungsstatusDB($nStatus = 0, $cISOSprache)
    {
        if (strlen($cISOSprache) > 0) {
            $oTrustedShopsKundenbewertung = $this->holeKundenbewertungsstatus($cISOSprache);

            if (isset($oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung) && $oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung > 0) {
                $_upd                = new stdClass();
                $_upd->nStatus       = (int)$nStatus;
                $_upd->cISOSprache   = $cISOSprache;
                $_upd->dAktualisiert = 'now()';
                Shop::DB()->update('ttrustedshopskundenbewertung', 'kTrustedshopsKundenbewertung', (int)$oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung, $_upd);
            } else {
                $oTrustedShopsKundenbewertung                = new stdClass();
                $oTrustedShopsKundenbewertung->nStatus       = $nStatus;
                $oTrustedShopsKundenbewertung->cTSID         = '';
                $oTrustedShopsKundenbewertung->cISOSprache   = $cISOSprache;
                $oTrustedShopsKundenbewertung->dAktualisiert = 'now()';

                Shop::DB()->insert('ttrustedshopskundenbewertung', $oTrustedShopsKundenbewertung);
            }
        }

        return $this;
    }

    /**
     * @param string $cTSID
     * @param string $cISOSprache
     * @return $this
     */
    public function aenderKundenbewertungtsIDDB($cTSID, $cISOSprache)
    {
        if (strlen($cISOSprache) > 0 && strlen($cTSID) > 0) {
            $oTrustedShopsKundenbewertung = $this->holeKundenbewertungsstatus($cISOSprache);

            if (isset($oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung) && $oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung > 0) {
                // Updaten
                $_upd        = new stdClass();
                $_upd->cTSID = $cTSID;
                Shop::DB()->update('ttrustedshopskundenbewertung', 'kTrustedshopsKundenbewertung', (int)$oTrustedShopsKundenbewertung->kTrustedshopsKundenbewertung, $_upd);
            } else {
                $oTrustedShopsKundenbewertung                = new stdClass();
                $oTrustedShopsKundenbewertung->nStatus       = 0;
                $oTrustedShopsKundenbewertung->cTSID         = $cTSID;
                $oTrustedShopsKundenbewertung->cISOSprache   = $cISOSprache;
                $oTrustedShopsKundenbewertung->dAktualisiert = 'now()';

                Shop::DB()->insert('ttrustedshopskundenbewertung', $oTrustedShopsKundenbewertung);
            }
        }

        return $this;
    }

    /**
     * @param string $cTSID
     * @param int    $nStatus
     * @param string $cISOSprache
     * @return int
     */
    public function aenderKundenbewertungsstatus($cTSID, $nStatus = 0, $cISOSprache = "de")
    {
        $returnValue = null;
        //call TS protection web service
        ini_set('soap.wsdl_cache_enabled', 1);
        $wsdlUrl = TS_RATING_SERVER;

        if (pruefeSOAP($wsdlUrl)) {
            $client = new SoapClient($wsdlUrl, ['exceptions' => 0]);
            //set return value for the case if a SOAP exception occurs
            $returnValue = SOAP_ERROR;
            //call WS method
            $returnValue = $client->updateRatingWidgetState($cTSID, $nStatus, 'jtl-software', 'eKgxL2vm', 'JTL');
            if (is_soap_fault($returnValue)) {
                $errorText = "SOAP Fault: (faultcode: {$returnValue->faultcode}, faultstring: {$returnValue->faultstring})";
                writeLog(PFAD_LOGFILES . 'trustedshops.log', $errorText, 1);
            } else {
                writeLog(PFAD_LOGFILES . 'trustedshops.log', "Der Kundenbewertungsstatus ('TSID: " . $cTSID . "') wurde versucht zu ändern, ReturnCode: " . $returnValue, 1);
            }
        } else {
            writeLog(PFAD_LOGFILES . 'trustedshops.log', 'SOAP could not be loaded.', 1);
        }

        if ($returnValue === 'OK') {
            $this->aenderKundenbewertungsstatusDB($nStatus, $cISOSprache);

            return 1;
        }
        if ($returnValue == SOAP_ERROR) {
            return 2;
        }
        if ($returnValue === 'INVALID_TSID') {
            return 3;
        }
        if ($returnValue === 'NOT_REGISTERED_FOR_TRUSTEDRATING') {
            return 4;
        }
        if ($returnValue === 'WRONG_WSUSERNAME_WSPASSWORD') {
            return 5;
        }

        return 0;
    }

    /**
     * @param stdClass $oZertifikat
     * @return stdClass
     */
    public function verschluesselTSDaten($oZertifikat)
    {
        if (!is_object($oZertifikat)) {
            $oZertifikat = new stdClass();
        }
        $oZertifikat->cWSUser     = trim(verschluesselXTEA($oZertifikat->cWSUser));
        $oZertifikat->cWSPasswort = trim(verschluesselXTEA($oZertifikat->cWSPasswort));

        return $oZertifikat;
    }

    /**
     * @param stdClass $oZertifikat
     * @return stdClass
     */
    public function entschluesselTSDaten($oZertifikat)
    {
        if ($oZertifikat === false || $oZertifikat === null) {
            $oZertifikat              = new stdClass();
            $oZertifikat->cWSUser     = null;
            $oZertifikat->cWSPasswort = null;
        }
        $oZertifikat->cWSUser     = trim(entschluesselXTEA($oZertifikat->cWSUser));
        $oZertifikat->cWSPasswort = trim(entschluesselXTEA($oZertifikat->cWSPasswort));

        return $oZertifikat;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public static function ladeKundenbewertungsWidgetNeu($filename)
    {
        // Load fresh widget from trustedshops Website
        // and write in local file
        // Open the file to get existing content
        $bMoeglich = false;
        $current   = null;
        if (TS_MODUS > 0) {
            $cURL = 'https://www.trustedshops.com/bewertung/widget/widgets/' . $filename; // Produktiv

            if (pruefeALLOWFOPEN()) {
                $current = @file_get_contents($cURL); // Produktiv
                if ($current) {
                    $bMoeglich = true;
                }
            } elseif (pruefeCURL()) {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $cURL);
                curl_setopt($curl, CURLOPT_TIMEOUT, 15);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

                $current = curl_exec($curl);
                curl_close($curl);

                if ($current) {
                    $bMoeglich = true;
                }
            }
        } else {
            $cURL = 'https://qa.trustedshops.com/bewertung/widget/widgets/' . $filename; // Test

            if (pruefeALLOWFOPEN()) {
                $current = @file_get_contents($cURL); // Test
                if ($current) {
                    $bMoeglich = true;
                }
            } elseif (pruefeCURL()) {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $cURL);
                curl_setopt($curl, CURLOPT_TIMEOUT, 15);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

                $current = curl_exec($curl);
                curl_close($curl);

                if ($current) {
                    $bMoeglich = true;
                }
            }
        }

        // Write the contents back to the file
        if ($bMoeglich) {
            @file_put_contents(PFAD_ROOT . PFAD_GFX_TRUSTEDSHOPS . $filename, $current);
        }

        return $bMoeglich;
    }

    /**
     * @return stdClass
     */
    private function holeKundenbewertungsStatistik()
    {
        $content = null;

        $url = sprintf(
            "http://www.trustedshops.com/api/ratings/v1/%s.xml",
            trim($this->tsId)
        );

        if (pruefeALLOWFOPEN()) {
            $content = @file_get_contents($url); // Test
        } elseif (pruefeCURL()) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

            $content = curl_exec($curl);
            curl_close($curl);
        }

        $xml             = simplexml_load_string($content);
        $rating          = new stdClass();
        $rating->nAnzahl = (int)$xml->ratings['amount'];

        $dDurchschnitt = null;
        foreach ($xml->ratings->result as $result) {
            if ($result['name'] === 'average') {
                $dDurchschnitt = (double)$result;
                break;
            }
        }

        if ($dDurchschnitt === null) {
            $dDurchschnitt = (double)$xml->ratings->result[0];
        }
        $rating->dDurchschnitt = $dDurchschnitt;

        return $rating;
    }

    /**
     * @return stdClass
     */
    public function gibKundenbewertungsStatistik()
    {
        $arrStatistik = Shop::DB()->selectAll('ttrustedshopsstatistik', 'cTSID', trim($this->tsId));

        if (count($arrStatistik) === 0) {
            // Erstimport
            $oData = $this->holeKundenbewertungsStatistik();

            $oData->cTSID    = trim($this->tsId);
            $oData->dUpdated = 'now()';
            Shop::DB()->insert('ttrustedshopsstatistik', $oData);

            $oStatistik                = new stdClass();
            $oStatistik->nAnzahl       = $oData->nAnzahl;
            $oStatistik->dMaximum      = 5.0;
            $oStatistik->dDurchschnitt = $oData->dDurchschnitt;
        } else {
            $oStatistikDB = $arrStatistik[0];
            $updateTime   = new DateTime($oStatistikDB->dUpdated);
            $updateTime->modify('+1 day');
            if ($updateTime < new DateTime()) {
                // Update
                $oData = $this->holeKundenbewertungsStatistik();

                $oStatistikDB->nAnzahl       = $oData->nAnzahl;
                $oStatistikDB->dDurchschnitt = $oData->dDurchschnitt;
                $oStatistikDB->dUpdated      = 'now()';
                Shop::DB()->update('ttrustedshopsstatistik', 'kStatistik', $oStatistikDB->kStatistik, $oStatistikDB);
            }

            $oStatistik                = new stdClass();
            $oStatistik->nAnzahl       = $oStatistikDB->nAnzahl;
            $oStatistik->dMaximum      = 5.0;
            $oStatistik->dDurchschnitt = $oStatistikDB->dDurchschnitt;
        }

        return $oStatistik;
    }
}
