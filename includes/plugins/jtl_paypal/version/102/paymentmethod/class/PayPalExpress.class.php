<?php

require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/vendor/autoload.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI;

/**
 * Class PayPalExpress.
 */
class PayPalExpress extends PaymentMethod
{
    /**
     * @var Plugin
     */
    public $oPlugin;

    /**
     * @var string
     */
    public $pluginbez;

    /**
     * @var
     */
    public $tZahlungsart;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $caption;

    /**
     * @var null|string
     */
    public $currencyISO;

    /**
     * @var string
     */
    public $cISOSprache;

    /**
     * @var string
     */
    public $PayPalURL;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $benutzer;

    /**
     * @var string
     */
    public $passwort;

    /**
     * @var string
     */
    public $signatur;

    /**
     * @var string
     */
    public $version = '119.0';

    /**
     * @var string
     */
    public $mode = 'undefined';

    /**
     * @var array
     */
    private $config;

    /**
     * @var null|PayPalHelper
     */
    private $helper = null;

    /**
     *
     */
    public function __construct()
    {
        $plugin             = Plugin::getPluginById('jtl_paypal');
        $oPlugin            = new Plugin($plugin->kPlugin);
        $sql                = "SELECT cName, kZahlungsart FROM tzahlungsart WHERE cModulId='kPlugin_" . $oPlugin->kPlugin . "_paypalexpress'";
        $this->tZahlungsart = (class_exists('Shop')) ? Shop::DB()->query($sql, 1) : $GLOBALS['DB']->executeQuery($sql, 1);
        $this->oPlugin      = $oPlugin;
        $this->pluginbez    = 'kPlugin_' . $oPlugin->kPlugin . '_paypalexpress';
        $this->currencyISO  = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung']->cISO : null;
        $this->cISOSprache  = (isset($_SESSION['cISOSprache'])) ? $_SESSION['cISOSprache'] : null;
        if ($oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_live_sandbox'] === 'live') {
            $this->PayPalURL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
            $this->endPoint  = 'https://api-3t.paypal.com/nvp';
            $this->benutzer  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_user'];
            $this->passwort  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_pass'];
            $this->signatur  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_signatur'];
            $this->mode      = 'live';
        } else {
            $this->PayPalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
            $this->endPoint  = 'https://api-3t.sandbox.paypal.com/nvp';
            $this->benutzer  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_sandbox_user'];
            $this->passwort  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_sandbox_pass'];
            $this->signatur  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_sandbox_signatur'];
            $this->mode      = 'sandbox';
        }
        $this->config = array(
            'mode'            => $this->mode,
            'acct1.UserName'  => $this->benutzer,
            'acct1.Password'  => $this->passwort,
            'acct1.Signature' => $this->signatur,
        );
        $this->helper = new PayPalHelper($this->oPlugin, $this->config, $this->currencyISO, $this->moduleID);
    }

    /**
     * test if the method is configured properly.
     *
     * @return array
     */
    public function test()
    {
        return $this->helper->test();
    }

    /**
     *
     */
    public function zahlungsprozess()
    {
        $basket = PayPalHelper::getBasket();

        $paymentDetails                     = new PaymentDetailsType();
        $paymentDetails->PaymentDetailsItem = [];

        foreach ($basket->items as $item) {
            $itemPaymentDetails           = new \PayPal\EBLBaseComponents\PaymentDetailsItemType();
            $itemPaymentDetails->Quantity = 1;
            $itemPaymentDetails->Name     = "{$item->quantity}x {$item->name}";
            $itemPaymentDetails->Amount   = new BasicAmountType($basket->currency->cISO, $item->amount[WarenkorbHelper::GROSS]);
            $itemPaymentDetails->Tax      = new BasicAmountType($basket->currency->cISO, '0.00');

            $paymentDetails->PaymentDetailsItem[] = $itemPaymentDetails;
        }

        // discount
        if (floatval($basket->discount[WarenkorbHelper::GROSS]) > 0) {
            $itemPaymentDetails           = new \PayPal\EBLBaseComponents\PaymentDetailsItemType();
            $itemPaymentDetails->Quantity = 1;
            $itemPaymentDetails->Name     = Shop::Lang()->get('discount', 'global');
            $itemPaymentDetails->Amount   = new BasicAmountType($basket->currency->cISO, $basket->discount[WarenkorbHelper::GROSS] * -1);
            $itemPaymentDetails->Tax      = new BasicAmountType($basket->currency->cISO, '0.00');

            $paymentDetails->PaymentDetailsItem[] = $itemPaymentDetails;
        }

        $shoplogo = $this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_shoplogo'];
        if (strpos($shoplogo, 'http://') !== false || strpos($shoplogo, 'https://') !== false) {
            $shoplogo = ((class_exists('Shop')) ? Shop::getURL() : gibShopURL()) . '/' . $shoplogo;
        }
        $bordercolor = str_replace('#', '', $this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_bordercolor']);

        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->ItemTotal     = new BasicAmountType($basket->currency->cISO, $basket->article[WarenkorbHelper::GROSS] - $basket->discount[WarenkorbHelper::GROSS]);
        $paymentDetails->TaxTotal      = new BasicAmountType($basket->currency->cISO, $basket->diff[WarenkorbHelper::GROSS]);
        $paymentDetails->ShippingTotal = new BasicAmountType($basket->currency->cISO, $basket->shipping[WarenkorbHelper::GROSS]);
        $paymentDetails->OrderTotal    = new BasicAmountType($basket->currency->cISO, $basket->total[WarenkorbHelper::GROSS]);

        $link = PayPalHelper::getLinkByName($this->oPlugin, 'PayPalExpress');

        $setExpressCheckoutRequestDetails                       = new \PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType();
        $setExpressCheckoutRequestDetails->ReturnURL            = ((class_exists('Shop')) ? Shop::getURL() : URL_SHOP) . '/index.php?s=' . $link->kLink . '&return=1';
        $setExpressCheckoutRequestDetails->CancelURL            = ((class_exists('Shop')) ? Shop::getURL() : URL_SHOP) . '/warenkorb.php';
        $setExpressCheckoutRequestDetails->cpplogoimage         = $shoplogo;
        $setExpressCheckoutRequestDetails->cppheaderimage       = $shoplogo;
        $setExpressCheckoutRequestDetails->cppheaderbordercolor = $bordercolor;
        $setExpressCheckoutRequestDetails->PaymentDetails       = array();
        $setExpressCheckoutRequestDetails->PaymentDetails[0]    = $paymentDetails;
        $setExpressCheckoutRequestDetails->LocaleCode           = strtoupper(StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));

        $setExpressCheckoutRequestType                    = new PayPalAPI\SetExpressCheckoutRequestType($setExpressCheckoutRequestDetails);
        $setExpressCheckoutReq                            = new PayPalAPI\SetExpressCheckoutReq();
        $setExpressCheckoutReq->SetExpressCheckoutRequest = $setExpressCheckoutRequestType;

        $service   = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        $exception = null;
        $response  = null;

        try {
            $response = $service->SetExpressCheckout($setExpressCheckoutReq);
        } catch (Exception $e) {
            $exception = $e;
        }

        if (isset($response->Ack) && $response->Ack === 'Success') {
            $sessionData                  = array();
            $sessionData['Token']         = $response->Token;
            $sessionData['Ack']           = $response->Ack;
            $sessionData['Timestamp']     = $response->Timestamp;
            $sessionData['CorrelationID'] = $response->CorrelationID;
            $_SESSION['reshash']          = $sessionData;

            header('Location: ' . $this->PayPalURL . $response->Token);
        } else {
            if ($exception !== null) {
                ZahlungsLog::add($this->moduleID, "SetExpressCheckout exception:\n" . print_r($exception->getMessage(), true), '', LOGLEVEL_ERROR);
            } else {
                ZahlungsLog::add($this->moduleID, "SetExpressCheckout error:\n" . print_r($response, true), '', LOGLEVEL_ERROR);
            }
            die(var_dump('PayPalExpress.class.php', $response));
            echo 'Fehler in der Daten&uuml;bermittlung. Weitere Informationen finden Sie in der Logdatei der Zahlungsart.';
            die('error');
            exit;
        }
        exit;
    }

    /**
     * @param int $nAgainCheckout
     *
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name    = 'PayPal Express';
        $this->caption = 'PayPal Express';

        return $this;
    }

    /**
     * @param bool $kVersandart
     *
     * @return mixed
     */
    public function zahlungsartsession($kVersandart = false)
    {
        $_SESSION['Zahlungsart']           = $this->tZahlungsart;
        $_SESSION['Zahlungsart']->cModulId = gibPlugincModulId($this->oPlugin->kPlugin, $this->tZahlungsart->cName);
        $sql                               = "SELECT cName, cISOSprache FROM tzahlungsartsprache WHERE kZahlungsart='" . $this->tZahlungsart->kZahlungsart . "'";
        $sprache_arr                       = Shop::DB()->query($sql, 2);

        foreach ($sprache_arr as $sprache) {
            $_SESSION['Zahlungsart']->angezeigterName[$sprache->cISOSprache] = $sprache->cName;
        }

        return $_SESSION['Zahlungsart'];
    }

    /**
     * @return string
     */
    public function holeLocaleCode()
    {
        if ($this->cISOSprache === 'ger') {
            return 'DE';
        }

        return 'US';
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $basket = PayPalHelper::getBasket();

        $doExpressCheckoutPaymentReq                          = new PayPalAPI\DoExpressCheckoutPaymentReq();
        $doExpressCheckoutPaymentRequestDetails               = new \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType();
        $doExpressCheckoutPaymentRequestDetails->Token        = $_SESSION['reshash']['Token'];
        $doExpressCheckoutPaymentRequestDetails->PayerID      = $_SESSION['reshash']['PayerID'];
        $doExpressCheckoutPaymentRequestDetails->ButtonSource = 'JTL_Cart_ECS_CPI';

        $paymentDetails                = new PaymentDetailsType();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->OrderTotal    = new BasicAmountType($basket->currency->cISO, $basket->total[WarenkorbHelper::GROSS]);
        $paymentDetails->NotifyURL     = Shop::getURL() . '/includes/modules/notify.php';

        $paymentDetailsList[] = $paymentDetails;

        $doExpressCheckoutPaymentRequestDetails->PaymentDetails       = $paymentDetailsList;
        $doExpressCheckoutPaymentRequest                              = new PayPalAPI\DoExpressCheckoutPaymentRequestType($doExpressCheckoutPaymentRequestDetails);
        $doExpressCheckoutPaymentReq->DoExpressCheckoutPaymentRequest = $doExpressCheckoutPaymentRequest;

        $service = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);

        try {
            $response = $service->DoExpressCheckoutPayment($doExpressCheckoutPaymentReq);
        } catch (Exception $e) {
            ZahlungsLog::add($this->moduleID, $e->getMessage(), '', LOGLEVEL_ERROR);
        }

        if ($response->Ack === 'Success') {
            //set payment
            $sql = "UPDATE tbestellung SET
					dBezahltDatum=now(),
					cStatus='" . BESTELLUNG_STATUS_BEZAHLT . "' WHERE kBestellung=" . intval($order->kBestellung);

            Shop::DB()->query($sql, 4);

            $bestellung = new Bestellung($order->kBestellung);
            $bestellung->fuelleBestellung(0);

            //process payment
            $zahlungseingang                    = new stdClass();
            $zahlungseingang->kBestellung       = $bestellung->kBestellung;
            $zahlungseingang->cZahlungsanbieter = 'PayPal Express';
            $zahlungseingang->fBetrag           = $basket->total[WarenkorbHelper::GROSS];
            $zahlungseingang->fZahlungsgebuehr  = $basket->surcharge[WarenkorbHelper::GROSS];
            $zahlungseingang->cISO              = $basket->currency->cISO;
            $zahlungseingang->cEmpfaenger       = '';
            $zahlungseingang->cZahler           = $_SESSION['reshash']['Payer'];
            $zahlungseingang->cAbgeholt         = 'N';
            $zahlungseingang->cHinweis          = $response->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
            $zahlungseingang->dZeit             = date_format(date_create(), 'Y-m-d H:i:s');

            Shop::DB()->insert('tzahlungseingang', $zahlungseingang);
        } else {
            $debugString = "\n\nSESSION Reshash:\n";
            $debugString .= print_r($_SESSION['reshash'], true);
            $debugString .= "\n\nPayment Status:\n";
            $debugString .= print_r($response, true);
            $debugString .= "\n\nPOST Request:\n";
            ZahlungsLog::add($this->moduleID, "preparePaymentProcess error:\n" . $debugString, '', LOGLEVEL_ERROR);
        }
        unset($_SESSION['reshash']);
        unset($_SESSION['paypalexpress']);
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    public function GetExpressCheckoutDetails($token)
    {
        $getExpressCheckoutDetailsReq                                   = new PayPalAPI\GetExpressCheckoutDetailsReq();
        $getExpressCheckoutDetailsRequest                               = new PayPalAPI\GetExpressCheckoutDetailsRequestType($token);
        $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
        $service                                                        = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        try {
            $response = $service->GetExpressCheckoutDetails($getExpressCheckoutDetailsReq);
        } catch (Exception $e) {
            ZahlungsLog::add($this->moduleID, $e->getMessage(), '', LOGLEVEL_ERROR);
        }
        if ($response->Ack === 'Success') {
            return $response->GetExpressCheckoutDetailsResponseDetails;
        } else {
            ZahlungsLog::add($this->moduleID, $response->Errors[0]->LongMessage, '', LOGLEVEL_ERROR);
        }

        return false;
    }

    /**
     * @param array $oArtikel_arr
     *
     * @return bool
     */
    public function zahlungErlaubt($oArtikel_arr = array())
    {
        foreach ($oArtikel_arr as $oArtikel) {
            if ($oArtikel !== null) {
                if (isset($oArtikel->FunktionsAttribute['no_paypalexpress']) && intval($oArtikel->FunktionsAttribute['no_paypalexpress']) === 1) {
                    return false;
                }

                $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe)) ? $_SESSION['Kunde']->kKundengruppe : null;
                if (!$kKundengruppe) {
                    if (method_exists('Kundengruppe', 'getDefaultGroupID')) {
                        $kKundengruppe = Kundengruppe::getDefaultGroupID();
                    } else {
                        $kKundengruppe = gibStandardKundenGruppe();
                    }
                }
                $sql = 'SELECT tversandart.kVersandart, tversandartzahlungsart.kZahlungsart
                                                    FROM tversandart
                                                    LEFT JOIN tversandartzahlungsart
                                                        ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                                                    WHERE tversandartzahlungsart.kZahlungsart = ' . $this->tZahlungsart->kZahlungsart . " AND (cVersandklassen='-1' OR (cVersandklassen LIKE '% " . $oArtikel->kVersandklasse . " %' OR cVersandklassen LIKE '% " . $oArtikel->kVersandklasse . "'))
                                                       AND (cKundengruppen='-1' OR cKundengruppen LIKE '%;" . $kKundengruppe . ";%')";
                $oVersandart_arr = (class_exists('Shop')) ? Shop::DB()->query($sql,
                    2) : $GLOBALS['DB']->executeQuery($sql,
                    2);
                $oVersandart_arr = $this->pruefeobVersandartPayPalExpressenthaelt($oVersandart_arr);

                if (count($oVersandart_arr) <= 0) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $oVersandart_arr
     *
     * @return mixed
     */
    public function pruefeobVersandartPayPalExpressenthaelt($oVersandart_arr)
    {
        foreach ($oVersandart_arr as $key => $oVersandart) {
            if (!isset($oVersandart->kZahlungsart)) {
                $sql = 'SELECT kZahlungsart
                                                FROM tversandartzahlungsart
                                                	WHERE tversandartzahlungsart.kVersandart = ' . $oVersandart->kVersandart . '
                                                	AND tversandartzahlungsart.kZahlungsart = ' . $this->tZahlungsart->kZahlungsart;
                $oVersandartzahlungsart = (class_exists('Shop')) ? Shop::DB()->query($sql,
                    1) : $GLOBALS['DB']->executeQuery($sql, 1);
                $oVersandart->kZahlungsart = $oVersandartzahlungsart->kZahlungsart;
            }

            if ($oVersandart->kZahlungsart <= 0 && $oVersandart->kZahlungsart != $this->tZahlungsart->kZahlungsart) {
                unset($oVersandart_arr[$key]);
            }
        }
        sort($oVersandart_arr);

        return $oVersandart_arr;
    }
}
