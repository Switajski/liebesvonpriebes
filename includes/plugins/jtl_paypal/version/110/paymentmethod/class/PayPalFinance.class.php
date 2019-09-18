<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/vendor/autoload.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

use PayPal\Api\Amount;
use PayPal\Api\Capture;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\Presentment;
use PayPal\Api\FinancingCurrency;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Authorization;
use PayPal\Api\ShippingAddress;
use PayPal\Api\PayerInfo;

/**
 * Class PayPalFinance.
 */
class PayPalFinance extends PaymentMethod
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
    public $localeCode;

    /**
     * @var Zahlungsart
     */
    public $paymentMethod;

    /**
     *
     */
    public function __construct()
    {
        $this->plugin      = $this->getPlugin();
        $this->settings    = $this->getSettings();
        $this->payment     = $this->getPayment();
        $this->paymentId   = $this->getPaymentId();
        $this->currencyIso = gibStandardWaehrung(true);
        $this->localeCode  = PayPalHelper::getDefaultLocale(
            PayPalHelper::getLanguageISO()
        );

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

        $this->name    = 'PayPal Finance';
        $this->caption = 'PayPal Finance';

        return $this;
    }

    /**
     * determines, if the payment method can be selected in the checkout process.
     *
     * @return bool
     */
    public function isSelectable()
    {
        return true;
    }
    /**
     * @param array $args_arr
     *
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (!$this->isConfigured(false)) {
            return false;
        }

        $items      = PayPalHelper::getProducts();
        $shippingId = @$_SESSION['Versandart']->kVersandart;

        if (!$this->isUseable($items, $shippingId)) {
            return false;
        }

        return true;
    }

    public function getContext()
    {
        $sandbox = $this->getModus() === 'sandbox';

        $apiContext = new ApiContext(new OAuthTokenCredential(
            $this->settings[$sandbox ? 'api_sandbox_client_id' : 'api_live_client_id'],
            $this->settings[$sandbox ? 'api_sandbox_secret' : 'api_live_secret']
        ));

        $apiContext->setConfig([
            'http.Retry'                                 => 1,
            'http.ConnectionTimeOut'                     => 30,
            'http.headers.PayPal-Partner-Attribution-Id' => 'JTL4_Cart_Inst',
            'mode'                                       => $this->getModus(),
            'cache.enabled'                              => true,
            'cache.FileName'                             => PFAD_ROOT . PFAD_COMPILEDIR . 'paypalfinance.auth.cache',
        ]);

        return $apiContext;
    }

    public function isConfigured($tryCall = false)
    {
        $sandbox = $this->getModus() === 'sandbox';

        $clientId = $this->settings[$sandbox ? 'api_sandbox_client_id' : 'api_live_client_id'];
        $secret   = $this->settings[$sandbox ? 'api_sandbox_secret' : 'api_live_secret'];

        if (strlen($clientId) == 0 || strlen($secret) == 0) {
            return false;
        }

        if (!$tryCall) {
            return true;
        }

        try {
            $presentment = $this->getPresentment(100.00, 'EUR');
            if ($presentment) {
                $options = $presentment->getFinancingOptions();
                return is_array($options) && count($options) > 0;
            }
        }
        catch (Exception $ex) { }
        return false;
    }

    public function getModuleId()
    {
        $crap = 'kPlugin_' . $this->plugin->kPlugin . '_paypalfinance';

        return $crap;
    }

    public function isVisible($amount)
    {
        if (!$this->isConfigured(false)) {
            return false;
        }

        if ($this->getSetting('min') > 0 && $amount <= $this->getSetting('min')) {
            return false;
        }

        if ($this->getSetting('max') > 0 && $amount >= $this->getSetting('max')) {
            return false;
        }

        return true;
    }

    public function getWebProfileId()
    {
        $webProfileId = null;

        if (($webProfileId = $this->getCache('webProfileId')) == null) {
            $presentation = new \PayPal\Api\Presentation();
            $presentation->setLocaleCode($this->localeCode);

            $shoplogo = $this->settings['shoplogo'];
            if (!empty($shoplogo)) {
                if (strpos($shoplogo, 'http') !== 0) {
                    $shoplogo = Shop::getURL() . '/' . $shoplogo;
                }
                $presentation->setLogoImage($shoplogo);
            }
            if (!empty($this->settings['brand'])) {
                $presentation->setBrandName(utf8_encode($this->settings['brand']));
            }

            $webProfile = new \PayPal\Api\WebProfile();
            $webProfile->setName('JTL-PayPalFinance' . uniqid())
                ->setPresentation($presentation)
                ->setTemporary(true);

            $request = clone $webProfile;

            try {
                $createProfileResponse = $webProfile->create($this->getContext());
                $webProfileId          = $createProfileResponse->getId();
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
        $link   = PayPalHelper::getLinkByName($plugin, 'PayPalFinance');

        $params = array_merge(
            ['s' => $link->kLink],
            $params
        );

        $paramlist   = http_build_query($params, '', '&');
        $callbackUrl = Shop::getURL($forceSsl) . '/index.php?' . $paramlist;

        return $callbackUrl;
    }

    public function getSettings()
    {
        $settings = [];
        $crap     = 'kPlugin_' . $this->plugin->kPlugin . '_paypalfinance_';

        foreach ($this->plugin->oPluginEinstellungAssoc_arr as $key => $value) {
            $key            = str_replace($crap, '', $key);
            $settings[$key] = $value;
        }

        return $settings;
    }

    public function getPayment()
    {
        return Shop::DB()->query("SELECT cName, kZahlungsart FROM tzahlungsart WHERE cModulId='kPlugin_" . $this->plugin->kPlugin . "_paypalfinance'", 1);
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
        $message = $e->getMessage();

        if ($error = $this->getError($e)) {
            $message = $error->getMessage();
        }

        return $message;
    }

    /**
     * @param \PayPal\Exception\PayPalConnectionException $exception
     *
     * @return null|\PayPal\Api\Error
     */
    public function getError($exception)
    {
        if ($exception instanceof PayPal\Exception\PayPalConnectionException) {
            try {
                $error = new \PayPal\Api\Error($exception->getData());
                return $error;
            }
            catch (Exception $ex) {}
        }

        return null;
    }

    public function logResult($type, $request, $response = null, $level = LOGLEVEL_NOTICE)
    {
        if ($request && $response) {
            $request  = $this->formatObject($request);
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
        // Ignore error types
        // inputValidationError (Invalid country)
        if ($error = $this->getError($e)) {
            if (in_array($error->getName(), ['inputValidationError'])) {
                return;
            }
        }

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

    public function getPresentment($amount, $currencyCode)
    {
        $hash = md5($amount . $currencyCode);

        if ($array = $this->getCache($hash)) {
            $presentment = new Presentment();
            $presentment->fromArray($array);

            return $presentment;
        }

        $currency = new FinancingCurrency();
        $currency->setCurrencyCode($currencyCode);
        $currency->setValue($amount);

        $presentment = new Presentment();
        $presentment->setFinancingCountryCode(PayPalHelper::getCountryISO());
        $presentment->setTransactionAmount($currency);

        $request = clone $presentment;

        try {
            $presentment->create($this->getContext());
            // $this->logResult('CreatePresentment', $request, $presentment);

            $this->addCache($hash, $presentment->toArray());

            return $presentment;
        } catch (Exception $ex) {
            // TODO: Handle Once
            // $this->handleException('CreatePresentment', $presentment, $ex);
        }

        return;
    }

    public function prepareAmount($basket)
    {
        $details = new Details();
        $details->setShipping($basket->shipping[WarenkorbHelper::GROSS])
            ->setSubtotal($basket->article[WarenkorbHelper::GROSS] - $basket->discount[WarenkorbHelper::GROSS])
            ->setHandlingFee($basket->surcharge[WarenkorbHelper::GROSS])
            //->setShippingDiscount($basket->discount[WarenkorbHelper::GROSS] * -1)
            ->setTax(0.00);

        $amount = new Amount();
        $amount->setCurrency($basket->currency->cISO)
            ->setTotal($basket->total[WarenkorbHelper::GROSS])
            ->setDetails($details);

        return $amount;
    }

    /**
     * @param array $oArtikel_arr
     *
     * @return bool
     */
    public function isUseable($oArtikel_arr = [], $shippingId = 0)
    {
        $versandklassen = VersandartHelper::getShippingClasses($_SESSION['Warenkorb']);
        $shippingId     = intval($shippingId);

        foreach ($oArtikel_arr as $oArtikel) {
            if ($oArtikel !== null) {
                if (isset($oArtikel->FunktionsAttribute['no_paypalfinance']) && intval($oArtikel->FunktionsAttribute['no_paypalfinance']) === 1) {
                    return false;
                }

                $kKundengruppe = PayPalHelper::getCustomerGroupId();

                $sql = 'SELECT tversandart.kVersandart, tversandartzahlungsart.kZahlungsart
                        FROM tversandart
                        LEFT JOIN tversandartzahlungsart
                            ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                        WHERE tversandartzahlungsart.kZahlungsart = ' . $this->paymentId . "
                AND (cVersandklassen='-1' OR (cVersandklassen LIKE '% " . $versandklassen . " %' OR cVersandklassen LIKE '% " . $versandklassen . "'))
                           AND (cKundengruppen='-1' OR cKundengruppen LIKE '%;" . $kKundengruppe . ";%')";

                if ($shippingId > 0) {
                    $sql .= ' AND tversandart.kVersandart = ' . $shippingId;
                }

                $oVersandart_arr = Shop::DB()->query($sql, 2);

                if (count($oVersandart_arr) <= 0) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array $aPost_arr
     *
     * @return bool
     */
    public function handleAdditional($aPost_arr)
    {
        if ($this->duringCheckout() === true) {
            $this->createPayment();
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        if (isset($_POST['zahlungsartzusatzschritt'])) {
            $this->createPayment();
            return false;
        }

        return $this->isValidIntern() && $this->getCache('token') !== null;
    }

    /**
     * @param Lieferadresse $address
     * @return ShippingAddress
     */
    public function prepareShippingAddress($address)
    {
        $shippingAddress = clone $address;
        $shippingAddress = utf8_convert_recursive($shippingAddress);

        $a = new ShippingAddress();

        $a->setRecipientName("{$shippingAddress->cVorname} {$shippingAddress->cNachname}")
            ->setLine1("{$shippingAddress->cStrasse} {$shippingAddress->cHausnummer}")
            ->setLine2($shippingAddress->cAdressZusatz)
            ->setCity($shippingAddress->cOrt)
            ->setPostalCode($shippingAddress->cPLZ)
            ->setCountryCode($shippingAddress->cLand);

        if ($state = PayPalHelper::getState($shippingAddress)) {
            $a->setState($state);
        }

        return $a;
    }

    /**
     * @param Kunde $customer
     * @return PayerInfo
     */
    public function getPayerInfo(Kunde $customer)
    {
        $c = clone $customer;
        $c = utf8_convert_recursive($c);

        $address = new \PayPal\Api\Address();
        $address->setLine1("{$c->cStrasse} {$c->cHausnummer}")
            ->setCity($c->cOrt)
            ->setPostalCode($c->cPLZ)
            ->setCountryCode($c->cLand);

        $payerInfo = new PayerInfo();
        $payerInfo->setEmail($c->cMail)
            ->setFirstName($c->cVorname)
            ->setLastName($c->cNachname)
            ->setBillingAddress($address);

        return $payerInfo;
    }

    /**
     * @param Payment $payment
     * @param Kunde $customer
     * @param Lieferadresse $shippingAddress
     */
    public function updateAddress(Payment $payment, $customer, $shippingAddress)
    {
        $payerInfo = $payment
            ->getPayer()
            ->getPayerInfo()
            ->toArray();

        $converted = utf8_convert_recursive($payerInfo, false);
        $payerInfo = PayPalHelper::toObject($converted);

        if (($address = $payerInfo->billing_address) !== null) {
            $this->logResult('Update billing address', $address);

            $street = PayPalHelper::extractStreet($address->line1);
            $map = [ 'MR' => 'm', 'MS' => 'w' ];
            if (array_key_exists($payerInfo->salutation, $map)) {
                $customer->cAnrede = $map[$payerInfo->salutation];
                $customer->cAnredeLocalized = mappeKundenanrede($customer->cAnrede, $customer->kSprache);
            }

            $customer->cVorname = $payerInfo->first_name;
            $customer->cNachname = $payerInfo->last_name;
            $customer->cStrasse = $street->name;
            $customer->cHausnummer = $street->number;
            $customer->cAdressZusatz = $address->line2;
            $customer->cBundesland = $address->state;
            $customer->cPLZ = $address->postal_code;
            $customer->cOrt = $address->city;
            $customer->cLand = $address->country_code;

            Session::getInstance()->setCustomer($customer);
        }

        if (($address = $payerInfo->shipping_address) !== null) {
            $this->logResult('Update shipping address', $address);

            $street = PayPalHelper::extractStreet($address->line1);
            $name = PayPalHelper::extractName($address->recipient_name);

            $shippingAddress->kKunde = $customer->kKunde;
            $shippingAddress->kLieferadresse = 0;

            $shippingAddress->cVorname = $name->first;
            $shippingAddress->cNachname = $name->last;
            $shippingAddress->cStrasse = $street->name;
            $shippingAddress->cHausnummer = $street->number;
            $shippingAddress->cAdressZusatz = $address->line2;
            $shippingAddress->cBundesland = $address->state;
            $shippingAddress->cPLZ = $address->postal_code;
            $shippingAddress->cOrt = $address->city;
            $shippingAddress->cLand = $address->country_code;
            $shippingAddress->angezeigtesLand = ISO2land($address->country_code);

            Session::set('Lieferadresse', $shippingAddress);

            $_SESSION['Bestellung']->kLieferadresse = -1;
        }
    }

    /**
     * @return bool
     */
    public function createPayment()
    {
        $items       = [];
        $basket      = PayPalHelper::getBasket();
        $currencyIso = $basket->currency->cISO;

        foreach ($basket->items as $i => $p) {
            $item = new Item();
            $item->setName($p->name)
                ->setCurrency($currencyIso)
                ->setQuantity($p->quantity)
                ->setPrice($p->amount[WarenkorbHelper::GROSS]);
            if ($p->desc) {
                $item->setDescription($p->desc);
            }
            if ($p->url) {
                $item->setUrl($p->url);
            }
            $items[] = $item;
        }

        if ($basket->discount[WarenkorbHelper::GROSS] > 0) {
            $discountItem = new Item();
            $discountItem->setName(Shop::Lang()->get('discount', 'global'))
                ->setCurrency($currencyIso)
                ->setQuantity(1)
                ->setPrice($basket->discount[WarenkorbHelper::GROSS] * -1);
            $items[] = $discountItem;
        }

        $itemList = new ItemList();
        $itemList->setItems($items);
        $amount = $this->prepareAmount($basket);

        if ((int)$_SESSION['Bestellung']->kLieferadresse !== 0) {
            $shipping = $this->prepareShippingAddress($_SESSION['Lieferadresse']);
            $itemList->setShippingAddress($shipping);
        }

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Payment');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->getCallbackUrl(['a' => 'return', 'r' => 'true']))
            ->setCancelUrl($this->getCallbackUrl(['a' => 'return', 'r' => 'false']));

        $payer = new Payer();
        $payer->setPaymentMethod('paypal')
            ->setExternalSelectedFundingInstrumentType('CREDIT')
            ->setPayerInfo($this->getPayerInfo($_SESSION['Kunde']));

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

            if ($payment->getState() !== "created" || $payment->payer->getPaymentMethod() !== "paypal") {
                throw new Exception(sprintf('Error processing checkout.(%s / %s)',
                    $payment->getState(), $payment->payer->getPaymentMethod()));
            }

            header('location: ' . $payment->getApprovalLink());
            exit;
        } catch (Exception $ex) {
            $this->handleException('CreatePayment', $payment, $ex);
            Shop::Smarty()->assign('error', $ex->getMessage());
        }
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        try {
            $paymentId = $this->getCache('paymentId');
            $payerId   = $this->getCache('payerId');

            $helper = new WarenkorbHelper();
            $basket = PayPalHelper::getBasket($helper);

            $apiContext  = $this->getContext();
            $orderNumber = baueBestellnummer();
            $payment     = Payment::get($paymentId, $apiContext);

            if ($payment->getState() !== 'created') {
                throw new Exception(sprintf('Unhandled payment state "%s"', $payment->getState()));
            }

            $this->patch($payment, $orderNumber);

            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            /*
            $details = new Details();
            $details->setShipping($basket->shipping[WarenkorbHelper::GROSS])
                ->setSubtotal($basket->article[WarenkorbHelper::GROSS] - $basket->discount[WarenkorbHelper::GROSS])
                ->setHandlingFee($basket->surcharge[WarenkorbHelper::GROSS])
                ->setTax(0.00);

            $amount = new Amount();
            $amount->setCurrency($basket->currency->cISO)
                ->setTotal($basket->total[WarenkorbHelper::GROSS])
                ->setDetails($details);

            $transaction = new Transaction();
            $transaction->setAmount($amount);

            $execution->addTransaction($transaction);
            */

            $payment->execute($execution, $apiContext);
            $this->logResult('ExecutePayment', $execution, $payment);

            if ($payment->getState() === 'failed') {
                throw new Exception(sprintf('Unhandled payment state %s', $payment->getState()));
            }

            $sale = $payment->getTransactions()[0]
                ->getRelatedResources()[0]
                ->getSale();

            $amount = $sale->getAmount();

            /*
             * TODO
             * Create new Option "Seller Protection"
             * 'ELIGIBLE', 'PARTIALLY_ELIGIBLE', 'DISABLE'
             */
            $protectionEligibility = $sale->getProtectionEligibility();

            $order = finalisiereBestellung($orderNumber, true);
            $this->updateOrder($payment, $order);

            if ($sale->getState() === 'completed') {

                $fee = 0.0;
                foreach ($order->Positionen as $pos) {
                    if ($pos->nPosTyp == 13) {
                        $fee = $pos->fPreisEinzelNetto;
                        break;
                    }
                }

                $payer = $payment->getPayer()->getPayerInfo();

                $this->addIncomingPayment($order, [
                    'fBetrag' => $amount->getTotal(),
                    'fZahlungsgebuehr' => $sale->getTransactionFee()->getValue(),
                    'cISO' => $amount->getCurrency(),
                    'cZahler' => $payer->getEmail(),
                    'cHinweis' => $sale->getId(),
                ]);

                if ($fee > 0) {
                    $this->addIncomingPayment($order, [
                        'fBetrag' => $fee,
                        'cZahlungsanbieter' => 'Finanzierungskosten',
                        'fZahlungsgebuehr' => 0,
                        'cISO' => $amount->getCurrency(),
                        'cZahler' => $payer->getEmail(),
                        'cHinweis' => $sale->getId(),
                    ]);
                }

                $this->setOrderStatusToPaid($order);
                $this->sendConfirmationMail($order);
            }

            $this->unsetCache();

            $paymentHash = $this->generateHash($order);
            $returnUrl = Shop::getURL() . '/bestellabschluss.php?i=' . $paymentHash;

            header("location: {$returnUrl}");
            exit;
        } catch (Exception $ex) {
            $this->handleException('ExecutePayment', $payment, $ex);
            Shop::Smarty()->assign('error', $ex->getMessage());
        }
    }

    /**
     * @param Payment $payment
     * @param $order
     */
    public function updateOrder(Payment $payment, $order)
    {
        $update = new stdClass();
        $update->cSession = $payment->getId();

        if ($this->getModus() === 'sandbox') {
            $update->cKommentar = implode(PHP_EOL,
                array_filter(['SANDBOX MODE', $order->cKommentar]));
        }

        Shop::DB()->update('tbestellung', 'kBestellung', $order->kBestellung, $update);
    }

    public function getTaxClass()
    {
        foreach ($_SESSION['Steuersatz'] as $taxClass => $taxRate) {
            if ((float)$taxRate === 0.0) {
                return $taxClass;
            }
        }

        $taxRate  = Shop::DB()->select('tsteuersatz', 'fSteuersatz', 0);
        if (is_object($taxRate)) {
            return $taxRate->kSteuerklasse;
        }

        return null;
    }

    public function addSurcharge(PayPal\Api\CreditFinancingOffered $offer)
    {
        $taxClass = $this->getTaxClass();
        $interest = $offer->getTotalInterest();

        $_SESSION['Warenkorb']->erstelleSpezialPos(
            'Finanzierungskosten', 1, $interest->getValue(), $taxClass,
            C_WARENKORBPOS_TYP_ZINSAUFSCHLAG,
            true, true, ''
        );
    }

    public function duringCheckout()
    {
        return (int) $this->duringCheckout !== 0;
    }

    /**
     * Is payment method selected in current checkout
     *
     * @return bool
     */
    public function isSelected()
    {
        if (!isset($_SESSION['Zahlungsart']) || !is_object($_SESSION['Zahlungsart']))
            return false;
        return (int) $_SESSION['Zahlungsart']->kZahlungsart === (int) $this->payment->kZahlungsart;
    }

    /**
     * Get PayPal Payment
     *
     * @param $transactionId
     * @return null|Payment
     */
    public function get($transactionId)
    {
        try {
            $apiContext = $this->getContext();
            return Payment::get($transactionId, $apiContext);
        }
        catch (Exception $e) { }
        return null;
    }

    /**
     * Patch invoice number + shipping address
     *
     * @param Payment $payment
     * @param $invoiceNumber
     */
    protected function patch(PayPal\Api\Payment &$payment, $invoiceNumber)
    {
        $patchInvoice = new \PayPal\Api\Patch();
        $patchInvoice->setOp('add')
            ->setPath('/transactions/0/invoice_number')
            ->setValue($invoiceNumber);

        $patchRequest = new \PayPal\Api\PatchRequest();
        $patchRequest->setPatches([$patchInvoice]);

        $payment->update($patchRequest, $this->getContext());
        $this->logResult('Patch', $patchRequest, $payment);
    }
}
