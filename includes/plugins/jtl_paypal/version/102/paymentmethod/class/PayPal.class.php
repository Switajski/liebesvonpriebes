<?php

require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/vendor/autoload.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI;

/**
 * Class PayPal.
 */
class PayPal extends PaymentMethod
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
     * @var bool
     */
    public $duringCheckout = true;

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
        $sql                = "SELECT cName, kZahlungsart FROM tzahlungsart WHERE cModulId='kPlugin_" . $oPlugin->kPlugin . "_paypal'";
        $this->tZahlungsart = (class_exists('Shop')) ? Shop::DB()->query($sql, 1) : $GLOBALS['DB']->executeQuery($sql,
            1);
        $this->oPlugin     = $oPlugin;
        $this->pluginbez   = 'kPlugin_' . $oPlugin->kPlugin . '_paypal_paypal';
        $this->currencyISO = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung']->cISO : null;
        $this->cISOSprache = (isset($_SESSION['cISOSprache'])) ? $_SESSION['cISOSprache'] : null;
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
     * @param int $nAgainCheckout
     *
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name           = 'PayPal';
        $this->caption        = 'PayPal';
        $this->duringCheckout = 1;

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
        $sprache_arr                       = (class_exists('Shop')) ? Shop::DB()->query($sql, 2) : $GLOBALS['DB']->executeQuery($sql, 2);
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
     * @param string     $paymentHash
     * @param array      $args
     *
     * @return bool
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        //		echo '<br>handleNotification()';
//		Shop::dbg($order,false,'order:');
        $verification = $this->helper->getExpressCheckoutDetails($args['token']);
//		Shop::dbg($verification,false, 'GetExpressCheckoutDetails:');
        $res = false;
        if ($verification !== false) {
            $response = $this->helper->doExpressCheckout($order, $verification->Token,
                $verification->PayerInfo->PayerID, $verification->PayerInfo->Payer);
//			Shop::dbg($response,false,'Response from doExpressCheckout():');
            if ($response->Ack === 'Success' || $response->Ack === 'SuccessWithWarning') { //@todo: SuccessWithWarning only for debugging - remove
//				require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
//				finalisiereBestellung();
//				$session = Session::getInstance();
//				$session->cleanUp();
                $res = true;
            }
        }

        return $res;
    }

    /**
     * @return bool
     *
     * @param Bestellung $order
     * @param array      $args
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        //		echo '<br>verifyNotification()';
//		Shop::dbg($args,false,'args:');
//		Shop::dbg($paymentHash,false,'paymenthash:');
//		die();
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        //		echo '<br>preparePaymentProcess()';
        $paymentDetails                     = new PaymentDetailsType();
        $paymentDetails->PaymentDetailsItem = array();
        $i                                  = 0;
        $shippingTotal                      = 0.0;
        $itemTotal                          = 0;
        $taxTotal                           = 0;
        $orderTotal                         = 0;
        $handlingTotal                      = 0;
        foreach ($_SESSION['Warenkorb']->PositionenArr as $_position) {
            if (isset($_position->nPosTyp) && $_position->nPosTyp === C_WARENKORBPOS_TYP_VERSANDPOS) {
                $shippingTotal += $_position->fPreis * $_SESSION['Waehrung']->fFaktor * ((100 + gibUst($_position->kSteuerklasse)) / 100);
                if ($shippingTotal < 0) {
                    $shippingTotal = $shippingTotal * pow(10, 2) - 0.5;
                    $shippingTotal = ceil($shippingTotal) * pow(10, -2);
                } else {
                    $shippingTotal = round($shippingTotal, 2);
                }
                $orderTotal += $shippingTotal;
            } else {
                if (isset($_position->nPosTyp) && $_position->nPosTyp === C_WARENKORBPOS_TYP_ZAHLUNGSART && isset($_position->fPreis) && $_position->fPreis > 0) {
                    $fTax = 0.0;
                    if (isset($_position->kSteuerklasse)) {
                        $handlingAmount = $_position->fPreis;
                        $fTax           = $_position->fPreis * $_SESSION['Waehrung']->fFaktor * ((100 + gibUst($_position->kSteuerklasse)) / 100) - $_position->fPreis;
                    } else {
                        $handlingAmount = $_position->fPreis;// * $_SESSION['Waehrung']->fFaktor * ((100 + $_position->fMwSt) / 100);
                    }
                    if ($handlingAmount < 0) {
                        $handlingAmount = $handlingAmount * pow(10, 2) - 0.5;
                        $handlingAmount = ceil($handlingAmount) * pow(10, -2);
                    } else {
                        $handlingAmount = round($handlingAmount, 2);
                    }

                    $handlingTotal += $handlingAmount;
                    $taxTotal += $fTax;
                    $orderTotal += ($handlingAmount + $fTax);
                } else {
                    $fTax = 0.0;
                    if (isset($_position->kSteuerklasse)) {
                        $ArticleAmount = $_position->fPreis;
                        $fTax          = $_position->fPreis * $_SESSION['Waehrung']->fFaktor * ((100 + gibUst($_position->kSteuerklasse)) / 100) - $_position->fPreis;
                    } else {
                        $ArticleAmount = $_position->fPreis;// * $_SESSION['Waehrung']->fFaktor * ((100 + $_position->fMwSt) / 100);
                    }
                    if ($ArticleAmount < 0) {
                        $ArticleAmount = $ArticleAmount * pow(10, 2) - 0.5;
                        $ArticleAmount = ceil($ArticleAmount) * pow(10, -2);
                    } else {
                        $ArticleAmount = round($ArticleAmount, 2);
                    }
                    $qty = (isset($_position->nAnzahl)) ? (int) $_position->nAnzahl : 1;
                    if (is_array($_position->cName)) {
                        $name = $_position->cName[$this->cISOSprache];
                    } else {
                        $name = $_position->cName;
                    }

                    $itemPaymentDetails           = new \PayPal\EBLBaseComponents\PaymentDetailsItemType();
                    $itemPaymentDetails->Name     = $name;
                    $itemPaymentDetails->Quantity = '' . $qty;
                    $itemPaymentDetails->Amount   = new BasicAmountType($_SESSION['Waehrung']->cISO,
                        number_format($ArticleAmount, 2, '.', ''));
                    $itemPaymentDetails->Tax = new BasicAmountType($_SESSION['Waehrung']->cISO,
                        number_format(round($fTax, 2), 2, '.', ''));
                    $itemTotal += $itemPaymentDetails->Amount->value * $qty;
                    $taxTotal += ($itemPaymentDetails->Tax->value * $qty);
                    $orderTotal += (($itemPaymentDetails->Amount->value * $qty) + ($itemPaymentDetails->Tax->value * $qty));

                    $paymentDetails->PaymentDetailsItem[$i] = $itemPaymentDetails;
                    ++$i;
                }
            }
        }

//		Shop::dbg($paymentDetails->PaymentDetailsItem, false, 'Items:');

        $shippingTotal = number_format($shippingTotal, 2, '.', '');
        $itemTotal     = number_format($itemTotal, 2, '.', '');
        $taxTotal      = number_format($taxTotal, 2, '.', '');
        $orderTotal    = number_format($orderTotal, 2, '.', '');
//        echo '<br>shipping total: ' . $shippingTotal;
//        echo '<br>item total: ' . $itemTotal;
//        echo '<br>tax total: ' . $taxTotal;
//        echo '<br>order total: ' . $orderTotal;

        $shoplogo = $this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_shoplogo'];
        if (strpos($shoplogo, 'http://') !== false || strpos($shoplogo, 'https://') !== false) {
            $shoplogo = ((class_exists('Shop')) ? Shop::getURL() : gibShopURL()) . '/' . $shoplogo;
        }
        $bordercolor = $this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_bordercolor'];

        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->ItemTotal     = new BasicAmountType($_SESSION['Waehrung']->cISO, $itemTotal);
        $paymentDetails->TaxTotal      = new BasicAmountType($_SESSION['Waehrung']->cISO, $taxTotal);
        $paymentDetails->ShippingTotal = new BasicAmountType($_SESSION['Waehrung']->cISO, $shippingTotal);
        $paymentDetails->OrderTotal    = new BasicAmountType($_SESSION['Waehrung']->cISO, $orderTotal);
        if ($handlingTotal > 0) {
            $handlingTotal = number_format($handlingTotal, 2, '.', '');
//            echo '<br>handling total: ' . $handlingTotal;
            $paymentDetails->HandlingTotal = new BasicAmountType($_SESSION['Waehrung']->cISO, $handlingTotal);
        }

//		die();

        $cancelUrl = (class_exists('Shop')) ? Shop::getURL() : URL_SHOP;
        if ($order->kBestellung > 0) {
            $cancelUrl .= '/jtl.php?bestellung=' . $order->kBestellung;
        }

        $setExpressCheckoutRequestDetails                       = new \PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType();
        $setExpressCheckoutRequestDetails->ReturnURL            = ((class_exists('Shop')) ? Shop::getURL() : URL_SHOP) . '/includes/modules/notify.php?payment_method=jtl_paypal';
        $setExpressCheckoutRequestDetails->CancelURL            = $cancelUrl;
        $setExpressCheckoutRequestDetails->cpplogoimage         = $shoplogo;
        $setExpressCheckoutRequestDetails->cppheaderbordercolor = $bordercolor;
        $setExpressCheckoutRequestDetails->PaymentDetails       = array();
        $setExpressCheckoutRequestDetails->PaymentDetails[0]    = $paymentDetails;
        $setExpressCheckoutRequestDetails->LocaleCode           = 'DE';//@todo

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
//		Shop::dbg($response, false, 'Response: ');
        if (isset($response->Ack) && $response->Ack === 'Success') {
            $sessionData                  = array();
            $sessionData['Token']         = $response->Token;
            $sessionData['Ack']           = $response->Ack;
            $sessionData['Timestamp']     = $response->Timestamp;
            $sessionData['CorrelationID'] = $response->CorrelationID;
            $sessionData['kBestellung']   = $order->kBestellung;
            $sessionData['hash']          = $this->generateHash($order);
            $_SESSION['jtl_paypal']       = $sessionData;
            header('Location: ' . $this->PayPalURL . $response->Token);
        } else {
            if ($exception !== null) {
                ZahlungsLog::add($this->moduleID,
                    "SetExpressCheckout exception:\n" . print_r($exception->getMessage(), true), '', LOGLEVEL_ERROR);
            } else {
                ZahlungsLog::add($this->moduleID, "SetExpressCheckout error:\n" . print_r($response, true), '',
                    LOGLEVEL_ERROR);
            }
            die(var_dump('PayPal.class.php', $response));
            echo 'Fehler in der Daten&uuml;bermittlung. Weitere Informationen finden Sie in der Logdatei der Zahlungsart. ';
            //@todo: redirect to somewhere?
            die('error');
            exit;
        }

        exit;
    }

    /**
     * @param array $oArtikel_arr
     *
     * @return bool
     */
    public function zahlungErlaubt($oArtikel_arr = array())
    {
        foreach ($oArtikel_arr as $oArtikel) {
            if (isset($oArtikel->FunktionsAttribute['no_paypal']) && intval($oArtikel->FunktionsAttribute['no_paypals']) === 1) {
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
            $sql = 'SELECT *
                                                FROM tversandart
                                                LEFT JOIN tversandartzahlungsart
                                                	ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                                                		AND tversandartzahlungsart.kZahlungsart = ' . $this->tZahlungsart->kZahlungsart . "
                                                WHERE (cVersandklassen='-1' OR (cVersandklassen LIKE '% " . $oArtikel->kVersandklasse . " %' OR cVersandklassen LIKE '% " . $oArtikel->kVersandklasse . "'))
                                                   AND (cKundengruppen='-1' OR cKundengruppen LIKE '%;" . $kKundengruppe . ";%')";
            $oVersandart_arr = (class_exists('Shop')) ? Shop::DB()->query($sql, 2) : $GLOBALS['DB']->executeQuery($sql,
                2);
            $oVersandart_arr = $this->pruefeobVersandartPayPalExpressenthaelt($oVersandart_arr);

            if (count($oVersandart_arr) <= 0) {
                return false;
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

    /**
     * @param array $args_arr
     *
     * @return bool
     */
    public function isValidIntern($args_arr = array())
    {
        //@todo
        return true;
    }

    /**
     * @return bool
     */
    public function redirectOnPaymentSuccess()
    {
        return true;
    }

    /**
     * Payment Provider redirects customer to this URL when Payment is complete.
     *
     * @param Bestellung $order
     *
     * @return string
     */
    public function getReturnURL($order)
    {
        return Shop::getURL() . '/bestellabschluss.php?i=' . $_SESSION['jtl_paypal']['hash'];
        global $Einstellungen;

        if (!isset($_SESSION['Zahlungsart']->nWaehrendBestellung) || $_SESSION['Zahlungsart']->nWaehrendBestellung == 0) {
            if ($Einstellungen['kaufabwicklung']['bestellabschluss_abschlussseite'] === 'A') { // Abschlussseite
                $oZahlungsID = Shop::DB()->query('SELECT cId FROM tbestellid WHERE kBestellung = ' . intval($order->kBestellung),
                    1);
                if (is_object($oZahlungsID)) {
                    return Shop::getURL() . '/bestellabschluss.php?i=' . $oZahlungsID->cId;
                }
            }

            return $order->BestellstatusURL;
        }

        return Shop::getURL() . '/bestellvorgang.php';
    }
}
