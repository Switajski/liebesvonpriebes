<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT.PFAD_INCLUDES_MODULES.'PaymentMethod.class.php';
require_once PFAD_ROOT.PFAD_INCLUDES.'bestellabschluss_inc.php';

require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/vendor/autoload.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad).'class/PayPal.helper.class.php';

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Sale;
use PayPal\Api\Transaction;
use PayPal\Api\Currency;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

/**
 * Class PayPalPlus.
 */
class PayPalPlus extends PaymentMethod
{
    /**
     * @var Plugin
     */
    public $plugin;

    /**
     * @var array
     */
    public $settings;

    /**
     * @var array
     */
    public $payment;

    /**
     * @var array
     */
    public $paymentId;

    /**
     * @var null|string
     */
    public $currencyIso;

    /**
     * @var string
     */
    public $languageIso;

    /**
     * @var Zahlungsart
     */
    public $paymentMethod;

    /**
     *
     */
    public function __construct()
    {
        $this->plugin = $this->getPlugin();
        $this->settings = $this->getSettings();
        $this->payment = $this->getPayment();
        $this->paymentId = $this->getPaymentId();
        $this->languageIso = $this->getLanguage();
        $this->currencyIso = gibStandardWaehrung(true);
        //$this->paymentMethod = $this->getPaymentMethod();

        parent::__construct($this->getModuleId());
    }

    /**
     * @param int $nAgainCheckout
     *
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name = 'PayPal PLUS';
        $this->caption = 'PayPal PLUS';

        return $this;
    }

    /**
     * determines, if the payment method can be selected in the checkout process.
     *
     * @return bool
     */
    public function isSelectable()
    {
        // Overwrite
        return false;
    }

    /**
     * @param array $args_arr
     *
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        return false;
    }

    public function getContext()
    {
        $sandbox = $this->getModus() === 'sandbox';

        $apiContext = new ApiContext(new OAuthTokenCredential(
            $this->settings[$sandbox ? 'api_sandbox_client_id' : 'api_live_client_id'],
            $this->settings[$sandbox ? 'api_sandbox_secret' : 'api_live_secret']
        ));

        $apiContext->setConfig([
            'http.Retry' => 1,
            'http.ConnectionTimeOut' => 30,
            'http.headers.PayPal-Partner-Attribution-Id' => 'JTL_Cart_REST_Plus',
            'mode' => $this->getModus(),
            'cache.enabled' => true,
            'cache.FileName' => PFAD_ROOT.PFAD_COMPILEDIR.'paypalplus.auth.cache',
        ]);

        return $apiContext;
    }

    public function isConfigured($tryCall = true)
    {
        $sandbox = $this->getModus() === 'sandbox';

        $clientId = $this->settings[$sandbox ? 'api_sandbox_client_id' : 'api_live_client_id'];
        $secret = $this->settings[$sandbox ? 'api_sandbox_secret' : 'api_live_secret'];

        if (strlen($clientId) == 0 || strlen($secret) == 0) {
            return false;
        }

        if (!$tryCall) {
            return true;
        }

        try {
            \PayPal\Api\Webhook::getAll($this->getContext());

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getLanguage()
    {
        if (!isset($_SESSION['cISOSprache'])) {
            $_SESSION['cISOSprache'] = 'ger';
        }

        return strtoupper(StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
    }

    public function getModuleId()
    {
        $crap = 'kPlugin_'.$this->plugin->kPlugin.'_paypalplus';

        return $crap;
    }

    public function getWebProfileId()
    {
        $webProfileId = null;

        if (($webProfileId = $this->getCache('webProfileId')) == null) {
            $flowConfig = new \PayPal\Api\FlowConfig();
            // $flowConfig->setLandingPageType($this->settings['landing']);

            $shoplogo = $this->settings['shoplogo'];
            if (strlen($shoplogo) > 0 && strpos($shoplogo, 'http') !== 0) {
                $shoplogo = Shop::getURL().'/'.$shoplogo;
            }

            $presentation = new \PayPal\Api\Presentation();
            $presentation->setLogoImage($shoplogo)
                ->setBrandName(utf8_encode($this->settings['brand']))
                ->setLocaleCode($this->languageIso);

            $inputFields = new \PayPal\Api\InputFields();
            $inputFields->setAllowNote(true)
                ->setNoShipping(1)
                ->setAddressOverride(1);

            $webProfile = new \PayPal\Api\WebProfile();
            $webProfile->setName('JTL-PayPalPlus'.uniqid())
                ->setFlowConfig($flowConfig)
                ->setPresentation($presentation)
                ->setInputFields($inputFields);

            $request = clone $webProfile;

            try {
                $createProfileResponse = $webProfile->create($this->getContext());
                $webProfileId = $createProfileResponse->getId();
                $this->addCache('webProfileId', $webProfileId);
                $this->logResult('WebProfile', $request, $createProfileResponse);
            } catch (Exception $ex) {
                $this->handleException('WebProfile', $request, $ex);
            }
        }

        return $webProfileId;
    }

    public function getCallbackUrl(array $params = [], $forceSsl = false)
    {
        $plugin = $this->getPlugin();
        $link = PayPalHelper::getLinkByName($plugin, 'PayPalPLUS');

        $params = array_merge(
            ['s' => $link->kLink],
            $params
        );

        $paramlist = http_build_query($params, '', '&');
        $callbackUrl = Shop::getURL($forceSsl).'/index.php?'.$paramlist;

        return $callbackUrl;
    }

    public function getSettings()
    {
        $settings = [];
        $crap = 'kPlugin_'.$this->plugin->kPlugin.'_paypalplus_';

        foreach ($this->plugin->oPluginEinstellungAssoc_arr as $key => $value) {
            $key = str_replace($crap, '', $key);
            $settings[$key] = $value;
        }

        return $settings;
    }

    public function getPayment()
    {
        return Shop::DB()->query("SELECT cName, kZahlungsart FROM tzahlungsart WHERE cModulId='kPlugin_".$this->plugin->kPlugin."_paypalplus'", 1);
    }

    public function getPaymentId()
    {
        $payment = $this->getPayment();
        if (is_object($payment)) {
            return $payment->kZahlungsart;
        }

        return 0;
    }

    public function getModus()
    {
        return $this->settings['api_live_sandbox'];
    }

    public function getPlugin()
    {
        $ppp = Plugin::getPluginById('jtl_paypal');

        return new Plugin($ppp->kPlugin);
    }

    public function getExceptionMessage($e)
    {
        $message = '';

        if ($e instanceof PayPal\Exception\PayPalConnectionException) {
            $message = $e->getData();
            if (strlen($message) == 0) {
                $message = $e->getMessage();
            }
        } else {
            $message = $e->getMessage();
        }

        return $message;
    }

    public function logResult($type, $request, $response = null, $level = LOGLEVEL_NOTICE)
    {
        if ($request && $response) {
            $request = $this->formatObject($request);
            $response = $this->formatObject($response);
            $this->doLog("{$type}: {$request} - {$response}", $level);
        } else {
            if ($request || $response) {
                $data = $this->formatObject($request ? $request : $response);
                $this->doLog("{$type}: {$data}", $level);
            }
        }
    }

    public function handleException($type, $request, $e, $level = LOGLEVEL_ERROR)
    {
        $message = $this->getExceptionMessage($e);
        $request = $this->formatObject($request);
        $this->doLog("{$type}: ERROR: {$message} - {$request}", $level);
    }

    protected function formatObject($object)
    {
        if ($object) {
            if (is_a($object, 'PayPal\Common\PayPalModel')) {
                $object = $object->toJSON(128);
            } elseif (is_string($object) && \PayPal\Validation\JsonValidator::validate($object, true)) {
                $object = str_replace('\\/', '/', json_encode(json_decode($object), 128));
            } else {
                $object = print_r($object, true);
            }
        }

        if (!is_string($object)) {
            $object = 'No Data';
        }

        $object = "<pre>{$object}</pre>";

        return $object;
    }

    public function prepareAmount($basket)
    {
        $details = new Details();
        $details->setShipping($basket->shipping[WarenkorbHelper::GROSS])
            ->setSubtotal($basket->article[WarenkorbHelper::GROSS])
            ->setHandlingFee($basket->surcharge[WarenkorbHelper::GROSS])
            ->setShippingDiscount($basket->discount[WarenkorbHelper::GROSS] * -1)
            ->setTax(0.00);

        $amount = new Amount();
        $amount->setCurrency($basket->currency->cISO)
            ->setTotal($basket->total[WarenkorbHelper::GROSS])
            ->setDetails($details);

        return $amount;
    }

    public function prepareShippingAddress($address)
    {
        $shippingAddress = clone $address;
        $shippingAddress = utf8_convert_recursive($shippingAddress);

        $a = new \PayPal\Api\ShippingAddress();

        $a->setRecipientName("{$shippingAddress->cVorname} {$shippingAddress->cNachname}")
            ->setLine1("{$shippingAddress->cStrasse} {$shippingAddress->cHausnummer}")
            ->setCity($shippingAddress->cOrt)
            ->setPostalCode($shippingAddress->cPLZ)
            ->setCountryCode($shippingAddress->cLand);

        if (in_array($shippingAddress->cLand, ['AR', 'BR', 'IN', 'US', 'CA', 'IT', 'JP', 'MX', 'TH'])) {
            $state = Staat::getRegionByName($address->cBundesland);
            if ($state !== null) {
                $a->setState($state->cCode);
            }
        }

        return $a;
    }

    public function createPayment()
    {
        $items = [];
        $basket = PayPalHelper::getBasket();
        $currencyIso = $basket->currency->cISO;

        foreach ($basket->items as $i => $p) {
            $item = new Item();
            $item->setName($p->name)
                ->setCurrency($currencyIso)
                ->setQuantity($p->quantity)
                ->setPrice($p->amount[WarenkorbHelper::GROSS]);
            $items[] = $item;
        }

        $itemList = new ItemList();
        $itemList->setItems($items);

        $amount = $this->prepareAmount($basket);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Payment');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->getCallbackUrl(['a' => 'return', 'r' => 'true']))
            ->setCancelUrl($this->getCallbackUrl(['a' => 'return', 'r' => 'false']));

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction])
            ->setExperienceProfileId($this->getWebProfileId());

        $request = clone $payment;

        try {
            $payment->create($this->getContext());
            $this->logResult('CreatePayment', $request, $payment);

            return $payment;
        } catch (Exception $ex) {
            $this->handleException('CreatePayment', $payment, $ex);
        }

        return;
    }

    public function getWebhooks()
    {
        try {
            return \PayPal\Api\Webhook::getAll($this->getContext());
        } catch (Exception $ex) {
            $this->handleException('GetWebhooks', null, $ex);
        }

        return;
    }

    public function clearWebhooks()
    {
        try {
            $webhookList = $this->getWebhooks();
            if ($webhookList !== null) {
                foreach ($webhookList->getWebhooks() as $webhook) {
                    $webhook->delete($this->getContext());
                }

                return true;
            }
        } catch (Exception $ex) {
            $this->handleException('ClearWebhooks', null, $ex);
        }

        return false;
    }

    public function setWebhooks()
    {
        $webhookEventTypes = [
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.AUTHORIZATION.CREATED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.AUTHORIZATION.VOIDED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.CAPTURE.COMPLETED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.CAPTURE.PENDING" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.CAPTURE.REFUNDED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.CAPTURE.REVERSED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.SALE.COMPLETED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.SALE.PENDING" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.SALE.REFUNDED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "PAYMENT.SALE.REVERSED" }'),
            new \PayPal\Api\WebhookEventType('{ "name": "RISK.DISPUTE.CREATED" }'),
        ];

        $webhook = new \PayPal\Api\Webhook();
        $webhook->setUrl($this->getCallbackUrl(['a' => 'webhook'], true))
            ->setEventTypes($webhookEventTypes);

        $request = clone $webhook;

        try {
            $webhook->create($this->getContext());
            $this->logResult('SetWebhooks', $request, $webhook);

            return true;
        } catch (Exception $ex) {
            $this->handleException('SetWebhooks', $request, $ex);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getOrderNumber($renew = false)
    {
        if (($orderNumber = $this->getCache('orderNumber')) == null || $renew) {
            $orderNumber = baueBestellnummer();
            $this->addCache('orderNumber', $orderNumber);
        }

        return $orderNumber;
    }

    /**
     * @return mixed
     */
    public function createPaymentSession()
    {
        $_SESSION['Zahlungsart'] = $this->payment;
        $_SESSION['Zahlungsart']->cModulId = $this->moduleID;
        $_SESSION['Zahlungsart']->nWaehrendBestellung = 1;

        $languages = Shop::DB()->query("SELECT cName, cISOSprache FROM tzahlungsartsprache WHERE kZahlungsart='".$this->paymentId."'", 2);

        foreach ($languages as $language) {
            $_SESSION['Zahlungsart']->angezeigterName[$language->cISOSprache] = $language->cName;
        }

        PayPalHelper::addSurcharge();
    }

    public function getSaleId(PayPal\Api\Payment &$payment)
    {
        $transactions = $payment->getTransactions();
        if (count($transactions) > 0) {
            $relatedResources = $transactions[0]->getRelatedResources();
            if (count($relatedResources) > 0) {
                $sale = $relatedResources[0]->getSale();

                return $sale->getId();
            }
        }

        return;
    }

    public function patchInvoiceNumber(PayPal\Api\Payment &$payment, $invoiceNumber)
    {
        $patch = new \PayPal\Api\Patch();
        $patch->setOp('add')
            ->setPath('/transactions/0/invoice_number')
            ->setValue($invoiceNumber);

        $patchRequest = new \PayPal\Api\PatchRequest();
        $patchRequest->setPatches([$patch]);

        $payment->update($patchRequest, $this->getContext());
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        try {
            $paymentId = $this->getCache('paymentId');
            $payerId = $this->getCache('payerId');

            $helper = new WarenkorbHelper();
            $basket = PayPalHelper::getBasket($helper);

            $apiContext = $this->getContext();
            $orderNumber = baueBestellnummer();
            $payment = Payment::get($paymentId, $apiContext);

            if ($payment->getState() != 'created') {
                throw new Exception(sprintf('Unhandled payment state %s', $payment->getState()));
            }

            /*
             * #437 Update invoice number
             */
            $this->patchInvoiceNumber($payment, $orderNumber);

            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $details = new Details();
            $details->setShipping($basket->shipping[WarenkorbHelper::GROSS])
                ->setSubtotal($basket->article[WarenkorbHelper::GROSS])
                ->setHandlingFee($basket->surcharge[WarenkorbHelper::GROSS])
                ->setShippingDiscount($basket->discount[WarenkorbHelper::GROSS] * -1)
                ->setTax(0.00);

            $amount = new Amount();
            $amount->setCurrency($basket->currency->cISO)
                ->setTotal($basket->total[WarenkorbHelper::GROSS])
                ->setDetails($details);

            $transaction = new Transaction();
            $transaction->setAmount($amount);
                //->setInvoiceNumber($orderNumber) // #437

            $execution->addTransaction($transaction);

            $payment->execute($execution, $apiContext);
            $this->logResult('ExecutePayment', $execution, $payment);

            $order = finalisiereBestellung($orderNumber, true);
            $order->cSession = $paymentId;

            if ($instruction = $payment->getPaymentInstruction()) {
                $type = $instruction->getInstructionType();

                if ($type == 'PAY_UPON_INVOICE') {
                    $banking = $instruction->getRecipientBankingInstruction();
                    $amount = $instruction->getAmount();

                    $company = new Firma();
                    $date = strftime('%d.%m.%Y', strtotime($instruction->getPaymentDueDate()));

                    $replacement = [
                        '%reference_number%' => $instruction->getReferenceNumber(),
                        '%bank_name%' => $banking->getBankName(),
                        '%account_holder_name%' => $banking->getAccountHolderName(),
                        '%international_bank_account_number%' => $banking->getInternationalBankAccountNumber(),
                        '%bank_identifier_code%' => $banking->getBankIdentifierCode(),
                        '%value%' => $amount->getValue(),
                        '%currency%' => $amount->getCurrency(),
                        '%payment_due_date%' => $date,
                        '%company%' => $company->cName,
                    ];

                    $pui = sprintf("%s\r\n\r\n%s",
                        $this->plugin->oPluginSprachvariableAssoc_arr['jtl_paypal_pui'],
                        $this->plugin->oPluginSprachvariableAssoc_arr['jtl_paypal_pui_legal']);

                    $order->cPUIZahlungsdaten = str_replace(
                        array_keys($replacement), array_values($replacement), $pui);

                    $paymentName = $this->plugin->oPluginSprachvariableAssoc_arr['jtl_paypal_payment_invoice_name'];

                    $order->cZahlungsartName = strlen($paymentName) > 0
                        ? $paymentName : $order->cZahlungsartName;
                }
            }

            $order->updateInDB();

            if ($payment->getState() === 'approved') {
                $state = $payment->getTransactions()[0]
                    ->getRelatedResources()[0]
                    ->getSale()
                    ->getState();

                if ($state === 'completed') {
                    $ip = new stdClass();

                    $ip->cISO = $basket->currency->cISO;
                    $ip->fBetrag = $basket->total[WarenkorbHelper::GROSS];

                    $ip->cEmpfaenger = '';
                    $ip->cZahler = $payment->getPayer()->getPayerInfo()->getEmail();

                    $ip->cHinweis = $this->getSaleId($payment);
                    $ip->fZahlungsgebuehr = $basket->surcharge[WarenkorbHelper::GROSS];

                    $this->setOrderStatusToPaid($order);
                    $this->addIncomingPayment($order, $ip);

                    // send confirmationMail - except for payment upon invoice (see https://gitlab.jtl-software.de/jtlshop/shop4/issues/618)
                    if (empty($order->cPUIZahlungsdaten)) {
                        $this->sendConfirmationMail($order);
                    }
                }
            }

            // smarty
            Shop::Smarty()->assign('abschlussseite', 1);

            // clean up
            $session = Session::getInstance();
            $session->cleanUp();

            $this->unsetCache();
        } catch (Exception $ex) {
            $this->handleException('ExecutePayment', $payment, $ex);
            Shop::Smarty()->assign('error', $ex->getMessage());
        }
    }

    /**
     * @param array $oArtikel_arr
     *
     * @return bool
     */
    public function isUseable($oArtikel_arr = [], $shippingId = 0)
    {
        $versandklassen = VersandartHelper::getShippingClasses($_SESSION['Warenkorb']);
        $shippingId = intval($shippingId);

        foreach ($oArtikel_arr as $oArtikel) {
            if ($oArtikel !== null) {
                if (isset($oArtikel->FunktionsAttribute['no_paypalplus']) && intval($oArtikel->FunktionsAttribute['no_paypalplus']) === 1) {
                    return false;
                }

                $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe))
                    ? $_SESSION['Kunde']->kKundengruppe
                    : Kundengruppe::getDefaultGroupID();

                $sql = 'SELECT tversandart.kVersandart, tversandartzahlungsart.kZahlungsart
                        FROM tversandart
                        LEFT JOIN tversandartzahlungsart
                            ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                        WHERE tversandartzahlungsart.kZahlungsart = '.$this->paymentId."
                AND (cVersandklassen='-1' OR (cVersandklassen LIKE '% ".$versandklassen." %' OR cVersandklassen LIKE '% ".$versandklassen."'))
                           AND (cKundengruppen='-1' OR cKundengruppen LIKE '%;".$kKundengruppe.";%')";

                if ($shippingId > 0) {
                    $sql .= ' AND tversandart.kVersandart = '.$shippingId;
                }

                $oVersandart_arr = Shop::DB()->query($sql, 2);

                if (count($oVersandart_arr) <= 0) {
                    return false;
                }
            }
        }

        return true;
    }
}
