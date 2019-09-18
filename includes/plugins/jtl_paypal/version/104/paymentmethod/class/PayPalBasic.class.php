<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/vendor/autoload.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\IPN\PPIPNMessage;
use PayPal\PayPalAPI;

/**
 * Class PayPalBasic.
 */
class PayPalBasic extends PaymentMethod
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
     *
     */
    public function __construct()
    {
        $plugin             = Plugin::getPluginById('jtl_paypal');
        $oPlugin            = new Plugin($plugin->kPlugin);
        $sql                = "SELECT cName, kZahlungsart FROM tzahlungsart WHERE cModulId='kPlugin_" . $oPlugin->kPlugin . "_paypalbasic'";
        $this->tZahlungsart = Shop::DB()->query($sql, 1);
        $this->oPlugin      = $oPlugin;
        $this->pluginbez    = 'kPlugin_' . $oPlugin->kPlugin . '_paypalbasic';
        $this->currencyISO  = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung']->cISO : null;
        $this->cISOSprache  = (isset($_SESSION['cISOSprache'])) ? $_SESSION['cISOSprache'] : null;
        if ($oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_live_sandbox'] === 'live') {
            $this->PayPalURL = 'https://www.paypal.com/checkoutnow?useraction=%s&token=%s';
            $this->endPoint  = 'https://api-3t.paypal.com/nvp';
            $this->benutzer  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_user'];
            $this->passwort  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_pass'];
            $this->signatur  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_signatur'];
            $this->mode      = 'live';
        } else {
            $this->PayPalURL = 'https://www.sandbox.paypal.com/checkoutnow?useraction=%s&token=%s';
            $this->endPoint  = 'https://api-3t.sandbox.paypal.com/nvp';
            $this->benutzer  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_sandbox_user'];
            $this->passwort  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_sandbox_pass'];
            $this->signatur  = $oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_api_sandbox_signatur'];
            $this->mode      = 'sandbox';
        }
        $this->config = [
            'mode'            => $this->mode,

            'acct1.UserName'  => $this->benutzer,
            'acct1.Password'  => $this->passwort,
            'acct1.Signature' => $this->signatur,

            'cache.enabled'   => true,
            'cache.FileName'  => PFAD_ROOT . PFAD_COMPILEDIR . 'paypalbasic.auth.cache'
        ];
        parent::__construct($this->pluginbez);
    }

    /**
     * return current mode [live/sandbox].
     *
     * @return bool
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * is 'live' mode enabled.
     *
     * @return bool
     */
    public function isLive()
    {
        return $this->getMode() === 'live';
    }

    /**
     * is 'sandbox' mode enabled.
     *
     * @return bool
     */
    public function isSandbox()
    {
        return $this->isLive() === false;
    }

    /**
     * test if the method is configured properly.
     *
     * @return array
     */
    public function test()
    {
        return PayPalHelper::test($this->config);
    }

    /**
     * handle instant payment notifications.
     *
     * @return PPIPNMessage
     */
    public function handleNotify()
    {
        return new PPIPNMessage('', $this->config);
    }

    /**
     * @param int $nAgainCheckout
     *
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name    = 'PayPal Basic';
        $this->caption = 'PayPal Basic';

        return $this;
    }

    public function setExpressCheckout($helper)
    {
        $order  = $helper->getObject();
        $basket = PayPalHelper::getBasket($helper);

        $shippingAddress = $helper->getShippingAddress();
        $languageISO     = $helper->getLanguageISO();
        $countryISO      = $helper->getCountryISO();
        $stateISO        = $helper->getStateISO();

        $paymentDetails                     = new PaymentDetailsType();
        $paymentDetails->PaymentDetailsItem = [];

        foreach ($basket->items as $item) {
            $itemPaymentDetails           = new \PayPal\EBLBaseComponents\PaymentDetailsItemType();
            $itemPaymentDetails->Quantity = $item->quantity;
            $itemPaymentDetails->Name     = $item->name;
            $itemPaymentDetails->Amount   = new BasicAmountType($helper->getCurrencyISO(), $item->amount[WarenkorbHelper::GROSS]);
            $itemPaymentDetails->Tax      = new BasicAmountType($helper->getCurrencyISO(), '0.00');

            $paymentDetails->PaymentDetailsItem[] = $itemPaymentDetails;
        }

        $shopLogo = $this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_shoplogo'];
        if (strlen($shopLogo) > 0 && strpos($shopLogo, 'http') !== 0) {
            $shopLogo = Shop::getURL() . '/' . $shopLogo;
        }
        $borderColor = str_replace('#', '', $this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_bordercolor']);
        $brandName   = utf8_encode($this->oPlugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_brand']);

        $paymentAddress = new \PayPal\EBLBaseComponents\AddressType();

        $paymentAddress->Name            = "{$shippingAddress->cVorname} {$shippingAddress->cNachname}";
        $paymentAddress->Street1         = "{$shippingAddress->cStrasse} {$shippingAddress->cHausnummer}";
        $paymentAddress->Street2         = @$shippingAddress->cAdressZusatz;
        $paymentAddress->CityName        = $shippingAddress->cOrt;
        $paymentAddress->StateOrProvince = $stateISO;
        $paymentAddress->Country         = $countryISO;
        $paymentAddress->Phone           = $shippingAddress->cTel;
        $paymentAddress->PostalCode      = $shippingAddress->cPLZ;

        $paymentDetails->PaymentAction    = 'Sale';
        $paymentDetails->ButtonSource     = 'JTL_Cart_ECM_CPI2';
        $paymentDetails->ItemTotal        = new BasicAmountType($helper->getCurrencyISO(), $basket->article[WarenkorbHelper::GROSS]);
        $paymentDetails->TaxTotal         = new BasicAmountType($helper->getCurrencyISO(), '0.00'); // $basket->diff[WarenkorbHelper::GROSS]
        $paymentDetails->ShippingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->shipping[WarenkorbHelper::GROSS]);
        $paymentDetails->OrderTotal       = new BasicAmountType($helper->getCurrencyISO(), $basket->total[WarenkorbHelper::GROSS]);
        $paymentDetails->HandlingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->surcharge[WarenkorbHelper::GROSS]);
        $paymentDetails->ShippingDiscount = new BasicAmountType($helper->getCurrencyISO(), $basket->discount[WarenkorbHelper::GROSS] * -1);

        $paymentDetails->InvoiceID = $helper->getInvoiceID();
        $paymentDetails->Custom    = $helper->getIdentifier();
        $paymentDetails->NotifyURL = $this->oPlugin->cFrontendPfadURLSSL . 'notify.php?type=basic';

        $setExpressCheckoutRequestDetails = new \PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType();

        if ($this->duringCheckout() === true) {
            $setExpressCheckoutRequestDetails->ReturnURL = $this->getCallbackUrl('s', true);
            $setExpressCheckoutRequestDetails->CancelURL = $this->getCallbackUrl('s', false);
        } else {
            $hash                                        = $this->generateHash($order);
            $setExpressCheckoutRequestDetails->ReturnURL = $this->getNotificationURL($hash);
            $setExpressCheckoutRequestDetails->CancelURL = Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1';//$this->getReturnURL($order);
        }

        $setExpressCheckoutRequestDetails->BrandName            = $brandName;
        $setExpressCheckoutRequestDetails->cpplogoimage         = $shopLogo;
        $setExpressCheckoutRequestDetails->cppheaderimage       = $shopLogo;
        $setExpressCheckoutRequestDetails->cppheaderbordercolor = $borderColor;
        $setExpressCheckoutRequestDetails->PaymentDetails       = [$paymentDetails];
        $setExpressCheckoutRequestDetails->LocaleCode           = $languageISO;
        $setExpressCheckoutRequestDetails->Address              = utf8_convert_recursive($paymentAddress);
        $setExpressCheckoutRequestDetails->AddressOverride      = 1;
        $setExpressCheckoutRequestDetails->NoShipping           = 0;

        $setExpressCheckoutRequestType                    = new PayPalAPI\SetExpressCheckoutRequestType($setExpressCheckoutRequestDetails);
        $setExpressCheckoutReq                            = new PayPalAPI\SetExpressCheckoutReq();
        $setExpressCheckoutReq->SetExpressCheckoutRequest = $setExpressCheckoutRequestType;

        $exception = $response = null;
        $service   = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);

        try {
            $r = print_r($setExpressCheckoutReq, true);
            $this->doLog("Request: SetExpressCheckout:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->SetExpressCheckout($setExpressCheckoutReq);

            $r = print_r($response, true);
            $this->doLog("Response: SetExpressCheckout:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        } catch (Exception $e) {
            $exception = $e;
        }

        if (isset($response->Ack) && $response->Ack === 'Success') {
            $redirect = $this->getApiUrl($response->Token);
            $this->addCache('token', $response->Token);
        } else {
            $r = $exception !== null ? $exception->getMessage() : print_r($response, true);
            $this->doLog("Error: SetExpressCheckout:\n\n<pre>{$r}</pre>", LOGLEVEL_ERROR);

            PayPalHelper::setFlashMessage($response->Errors[0]->LongMessage);
            $redirect = 'bestellvorgang.php?editZahlungsart=1';
        }

        header("location: {$redirect}");
        exit;
    }

    public function getApiUrl($token)
    {
        $useraction = $this->duringCheckout() ? 'continue' : 'commit';

        return sprintf($this->PayPalURL, $useraction, $token);
    }

    public function getCallbackUrl($type, $success = true)
    {
        $link        = PayPalHelper::getLinkByName($this->oPlugin, 'PayPalBasic');
        $callbackUrl = sprintf('%s/index.php?s=%d&t=%s&r=%s', Shop::getUrl(true), $link->kLink, $type, $success ? '1' : '0');

        return $callbackUrl;
    }

    public function doExpressCheckoutPayment(BestellungHelper $helper, $args = [])
    {
        $order  = $helper->getObject();
        $basket = PayPalHelper::getBasket($helper);

        $token   = $this->getCache('token');
        $details = $this->getExpressCheckoutDetails($token);

        if (!is_object($details)) {
            header('location: ' . Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1');
            exit;
        }

        $doExpressCheckoutPaymentReq                          = new PayPalAPI\DoExpressCheckoutPaymentReq();
        $doExpressCheckoutPaymentRequestDetails               = new \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType();
        $doExpressCheckoutPaymentRequestDetails->Token        = $details->Token;
        $doExpressCheckoutPaymentRequestDetails->PayerID      = $details->PayerInfo->PayerID;
        $doExpressCheckoutPaymentRequestDetails->ButtonSource = 'JTL_Cart_ECM_CPI2';

        $shippingAddress = $helper->getShippingAddress();
        $paymentAddress  = new \PayPal\EBLBaseComponents\AddressType();

        $paymentAddress->Name            = "{$shippingAddress->cVorname} {$shippingAddress->cNachname}";
        $paymentAddress->Street1         = "{$shippingAddress->cStrasse} {$shippingAddress->cHausnummer}";
        $paymentAddress->Street2         = @$shippingAddress->cAdressZusatz;
        $paymentAddress->CityName        = $shippingAddress->cOrt;
        $paymentAddress->StateOrProvince = @$shippingAddress->cBundesland;
        $paymentAddress->Country         = $shippingAddress->cLand;
        $paymentAddress->Phone           = $shippingAddress->cTel;
        $paymentAddress->PostalCode      = $shippingAddress->cPLZ;

        $paymentDetails                   = new PaymentDetailsType();
        $paymentDetails->PaymentAction    = 'Sale';
        $paymentDetails->ShipToAddress    = utf8_convert_recursive($paymentAddress);
        $paymentDetails->ButtonSource     = $doExpressCheckoutPaymentRequestDetails->ButtonSource;
        $paymentDetails->OrderDescription = Shop::Lang()->get('order', 'global') . ' ' . $helper->getInvoiceID();
        $paymentDetails->ItemTotal        = new BasicAmountType($helper->getCurrencyISO(), $basket->article[WarenkorbHelper::GROSS]);
        $paymentDetails->TaxTotal         = new BasicAmountType($helper->getCurrencyISO(), '0.00');
        $paymentDetails->ShippingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->shipping[WarenkorbHelper::GROSS]);
        $paymentDetails->OrderTotal       = new BasicAmountType($helper->getCurrencyISO(), $basket->total[WarenkorbHelper::GROSS]);
        $paymentDetails->ShippingDiscount = new BasicAmountType($helper->getCurrencyISO(), $basket->discount[WarenkorbHelper::GROSS] * -1);
        $paymentDetails->HandlingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->surcharge[WarenkorbHelper::GROSS]);

        $doExpressCheckoutPaymentRequestDetails->PaymentDetails       = [$paymentDetails];
        $doExpressCheckoutPaymentRequest                              = new PayPalAPI\DoExpressCheckoutPaymentRequestType($doExpressCheckoutPaymentRequestDetails);
        $doExpressCheckoutPaymentReq->DoExpressCheckoutPaymentRequest = $doExpressCheckoutPaymentRequest;

        $service = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);

        try {
            $r = print_r($doExpressCheckoutPaymentReq, true);
            $this->doLog("Request: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->DoExpressCheckoutPayment($doExpressCheckoutPaymentReq);

            $r = print_r($response, true);
            $this->doLog("Response: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        } catch (Exception $e) {
            $r = $e->getMessage();
            $this->doLog("Response: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        }

        if ($response->Ack === 'Success') {
            $pseudo      = (object) ['kBestellung' => $helper->getIdentifier()];
            $paymentInfo = $response->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0];

            $this->doLog("Payment status: {$paymentInfo->PaymentStatus} (Order: {$order->kBestellung}, Reason: {$paymentInfo->PendingReason})", LOGLEVEL_NOTICE);

            if (strcasecmp($paymentInfo->PaymentStatus, 'Completed') === 0) {
                $this->addIncomingPayment($pseudo, [
                    'fBetrag'          => $basket->total[WarenkorbHelper::GROSS],
                    'fZahlungsgebuehr' => $basket->surcharge[WarenkorbHelper::GROSS],
                    'cISO'             => $helper->getCurrencyISO(),
                    'cZahler'          => $details->PayerInfo->Payer,
                    'cHinweis'         => $details->PaymentDetails[0]->TransactionID,
                ]);
                $this->setOrderStatusToPaid($pseudo);
            }

            if ($this->duringCheckout() === false) {
                Session::getInstance()->cleanUp();
            }

            return true;
        } else {
            $r = print_r($response, true);
            $this->doLog("Response: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            PayPalHelper::setFlashMessage($response->Errors[0]->LongMessage);
        }

        return false;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     *
     * @return true, if $order should be finalized
     */
    public function finalizeOrder($order, $hash, $args)
    {
        /*
        $helper = new BestellungHelper($order);
        return $this->doExpressCheckoutPayment($helper, $args);
        */
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
        $helper = new BestellungHelper($order);
        $this->doExpressCheckoutPayment($helper, $args);
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $helper = new BestellungHelper($order);

        if ($this->duringCheckout() === false) {
            $this->setExpressCheckout($helper);
        } else {
            $this->doExpressCheckoutPayment($helper);
        }
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
     * @param string $token
     *
     * @return bool
     */
    public function getExpressCheckoutDetails($token)
    {
        $getExpressCheckoutDetailsReq                                   = new PayPalAPI\GetExpressCheckoutDetailsReq();
        $getExpressCheckoutDetailsRequest                               = new PayPalAPI\GetExpressCheckoutDetailsRequestType($token);
        $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
        $service                                                        = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        try {
            $r = print_r($getExpressCheckoutDetailsReq, true);
            $this->doLog("Request: GetExpressCheckoutDetails:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->GetExpressCheckoutDetails($getExpressCheckoutDetailsReq);

            $r = print_r($response, true);
            $this->doLog("Response: GetExpressCheckoutDetails:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
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

    public function duringCheckout()
    {
        return (int) $this->duringCheckout !== 0;
    }

    /**
     * @param array $args_arr
     *
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        $result = PayPalHelper::test($this->config, false);

        return $result['status'] != 'failure';
    }

    /**
     * @return bool
     */
    public function redirectOnPaymentSuccess()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function redirectOnCancel()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return false;
        // return $this->duringCheckout() === false;
    }

    /**
     * @param array $aPost_arr
     * @return bool
     */
    public function handleAdditional($aPost_arr)
    {
        if ($this->duringCheckout() === true) {
            $helper = new WarenkorbHelper();
            $this->setExpressCheckout($helper);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        return $this->getCache('token') !== null;
    }
}
