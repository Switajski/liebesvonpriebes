<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
include_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsLog.php';
require_once PFAD_ROOT . PFAD_INCLUDES_LIBS . PFAD_NUSOAP . 'nusoap.php';

// Debug
define('CAB_D_MODE', 0); // 1 = An / 0 = Aus
define('CAB_D_PFAD', PFAD_LOGFILES . 'clickandbuy.log');

define('SOAP_NAMESPACE', "http://api.clickandbuy.com/webservices/pay_1_0_0/\" xmlns=\"http://api.clickandbuy.com/webservices/pay_1_0_0/");
define('SOAP_ACTION', 'http://api.clickandbuy.com/webservices/pay_1_0_0/');

// Live
define('SOAP_ENDPOINT', 'https://api.clickandbuy.com/webservices/soap/pay_1_0_0');

// Test
//define('SOAP_ENDPOINT',  'https://api.clickandbuy-s1.com/webservices/soap/pay_1_0_0');

/**
 * ClickandBuy
 */
class ClickandBuy extends PaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name    = 'ClickandBuy';
        $this->caption = 'ClickandBuy';

        return $this;
    }

    /**
     * @return string|null
     */
    private function getMerchantId()
    {
        $conf = Shop::getSettings([CONF_ZAHLUNGSARTEN]);

        return isset($conf['zahlungsarten']['zahlungsart_clickandbuy_merchant_id'])
            ? $conf['zahlungsarten']['zahlungsart_clickandbuy_merchant_id']
            :             null;
    }

    /**
     * @return string|null
     */
    private function getProjectId()
    {
        $conf = Shop::getSettings([CONF_ZAHLUNGSARTEN]);

        return isset($conf['zahlungsarten']['zahlungsart_clickandbuy_project_id'])
            ? $conf['zahlungsarten']['zahlungsart_clickandbuy_project_id']
            : null;
    }

    /**
     * @return string|null
     */
    private function getSecretKey()
    {
        $conf = Shop::getSettings([CONF_ZAHLUNGSARTEN]);

        return isset($conf['zahlungsarten']['zahlungsart_clickandbuy_secretkey_id'])
            ? $conf['zahlungsarten']['zahlungsart_clickandbuy_secretkey_id']
            : null;
    }

    /**
     * @param string $cProjectID
     * @param string $cSecretKey
     * @return string
     */
    private function generateToken($cProjectID, $cSecretKey)
    {
        $nTimestamp  = gmdate('YmdHis');
        $cHash       = $cProjectID . '::' . $cSecretKey . '::' . $nTimestamp;
        $cToBeHashed = strtoupper(sha1($cHash));

        return $nTimestamp . '::' . $cToBeHashed;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $customer = $_SESSION['Kunde'];
        $cHash    = $this->generateHash($order);
        // Authentifikations Parameter
        $cAuthentication_arr['merchantID'] = trim($this->getMerchantId());
        $cAuthentication_arr['projectID']  = trim($this->getProjectId());
        $cAuthentication_arr['secretKey']  = trim($this->getSecretKey());

        if (CAB_D_MODE === 1) {
            ZahlungsLog::add('za_clickandbuy_jtl', 'cAuthentication_arr: ' . print_r($cAuthentication_arr, 1));
        }
        // failureURL
        $cFailURL = $order->BestellstatusURL;
        if ($this->duringCheckout == 1) {
            $cFailURL = Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1';
        }
        // Bestellinformationen
        $cDetails_arr['amount']            = number_format($order->fGesamtsummeKundenwaehrung, 2, '.', '');
        $cDetails_arr['currency']          = $order->Waehrung->cISO;
        $cDetails_arr['basketRisk']        = '';
        $cDetails_arr['clientRisk']        = '';
        $cDetails_arr['authExpiration']    = '';
        $cDetails_arr['confirmExpiration'] = '';
        $cDetails_arr['successExpiration'] = '1439';
        $cDetails_arr['successURL']        = $this->getNotificationURL($cHash);
        $cDetails_arr['failureURL']        = $cFailURL;
        $cDetails_arr['consumerIPAddress'] = '';
        $cDetails_arr['externalID']        = $cHash;
        $cDetails_arr['consumerLanguage']  = strtolower($customer->cLand);
        $cDetails_arr['consumerCountry']   = strtoupper($customer->cLand);
        $cDetails_arr['orderDescription']  = Shop::Lang()->get('order', 'global') . ' ' . $order->cBestellNr;
        //$cDetails_arr['consumerCountry'] = strtolower($customer->cLand);
        $cDetails_arr = $this->removeEmptyTag($cDetails_arr);

        if (CAB_D_MODE === 1) {
            ZahlungsLog::add('za_clickandbuy_jtl', 'cDetails_arr: ' . print_r($cDetails_arr, 1));
        }
        // Rechnungsinformationen
        $cBillingAddress_arr['salutation']                   = $customer->cAnrede === 'm' ? 'MR' : 'MS';
        $cBillingAddress_arr['title']                        = utf8_encode($customer->cTitel);
        $cBillingAddress_arr['firstName']                    = utf8_encode($customer->cVorname);
        $cBillingAddress_arr['lastName']                     = utf8_encode($customer->cNachname);
        $cBillingAddress_arr['maidenName']                   = '';
        $cBillingAddress_arr['gender']                       = '';
        $cBillingAddress_arr['dateOfBirth']                  = '';
        $cBillingAddress_arr['language']                     = strtolower($customer->cLand);
        $cBillingAddress_arr['address']['street']            = utf8_encode($customer->cStrasse);
        $cBillingAddress_arr['address']['houseNumber']       = utf8_encode($customer->cHausnummer);
        $cBillingAddress_arr['address']['houseNumberSuffix'] = '';
        $cBillingAddress_arr['address']['zip']               = $customer->cPLZ;
        $cBillingAddress_arr['address']['city']              = utf8_encode($customer->cOrt);
        //$cBillingAddress_arr['address']['country']              = strtolower($customer->cLand);
        $cBillingAddress_arr['address']['country']       = strtoupper($customer->cLand);
        $cBillingAddress_arr['address']['state']         = utf8_encode($customer->cBundesland);
        $cBillingAddress_arr['address']['addressSuffix'] = '';

        $cBillingAddress_arr = $this->removeEmptyTag($cBillingAddress_arr, ['title']);

        if (CAB_D_MODE === 1) {
            ZahlungsLog::add('za_clickandbuy_jtl', 'cBillingAddress_arr: ' . print_r($cBillingAddress_arr, 1));
        }

        $cShippingType = 'consumer';
        $cBillingType  = 'consumer';
        // Lieferinformationen
        $cShippingAddress_arr = $cBillingAddress_arr;
        if (isset($order->Lieferadresse)) {
            $cShippingAddress_arr['salutation']  = $order->Lieferadresse->cAnrede === 'm' ? 'MR' : 'MS';
            $cShippingAddress_arr['title']       = $order->Lieferadresse->cTitel;
            $cShippingAddress_arr['firstName']   = utf8_encode($order->Lieferadresse->cVorname);
            $cShippingAddress_arr['lastName']    = utf8_encode($order->Lieferadresse->cNachname);
            $cShippingAddress_arr['maidenName']  = '';
            $cShippingAddress_arr['gender']      = '';
            $cShippingAddress_arr['dateOfBirth'] = '';

            $cLandISO = '';
            if (strlen($order->Lieferadresse->cLand) < 3) {
                //$cLandISO = strtolower($order->Lieferadresse->cLand);
                $cLandISO = strtoupper($order->Lieferadresse->cLand);
            } elseif (landISO($order->Lieferadresse->cLand) !== 'noISO') {
                //$cLandISO = strtolower(landISO($order->Lieferadresse->cLand));
                $cLandISO = strtoupper(landISO($order->Lieferadresse->cLand));
            }

            //$cShippingAddress_arr['language']                       = $cLandISO;
            $cShippingAddress_arr['language']                     = strtolower($cLandISO);
            $cShippingAddress_arr['address']['street']            = utf8_encode($order->Lieferadresse->cStrasse);
            $cShippingAddress_arr['address']['houseNumber']       = utf8_encode($order->Lieferadresse->cHausnummer);
            $cShippingAddress_arr['address']['houseNumberSuffix'] = '';
            $cShippingAddress_arr['address']['zip']               = $order->Lieferadresse->cPLZ;
            $cShippingAddress_arr['address']['city']              = utf8_encode($order->Lieferadresse->cOrt);
            $cShippingAddress_arr['address']['country']           = $cLandISO;
            $cShippingAddress_arr['address']['state']             = utf8_encode($order->Lieferadresse->cBundesland);
            $cShippingAddress_arr['address']['addressSuffix']     = '';
            $cShippingAddress_arr                                 = $this->removeEmptyTag($cShippingAddress_arr, ['title', 'firstName']);

            if (CAB_D_MODE === 1) {
                ZahlungsLog::add('za_clickandbuy_jtl', 'cShippingAddress_arr: ' . print_r($cShippingAddress_arr, 1));
            }
        }
        // Warenkorbinformationen
        $cItem_arr = [];
        foreach ($order->Positionen as $i => $oBestellPos) {
            $cItem_arr[$i]['itemType']               = 'item' . $i . 'Item';
            $cItem_arr[$i]['itemDescription']        = utf8_encode(trim($oBestellPos->cName));
            $cItem_arr[$i]['itemQuantity']           = $oBestellPos->nAnzahl;
            $cItem_arr[$i]['itemUnitPriceAmount']    = number_format($oBestellPos->fPreis + ($oBestellPos->fPreis * $oBestellPos->fMwSt / 100), 2, '.', '');
            $cItem_arr[$i]['itemUnitPriceCurrency']  = $order->Waehrung->cISO;
            $cItem_arr[$i]['itemTotalPriceAmount']   = number_format(($oBestellPos->fPreis + ($oBestellPos->fPreis * $oBestellPos->fMwSt / 100)) * $oBestellPos->nAnzahl, 2, '.', '');
            $cItem_arr[$i]['itemTotalPriceCurrency'] = $order->Waehrung->cISO;
        }

        $cRequestResult_arr = $this->payRequest($cAuthentication_arr, $cDetails_arr, $cShippingType, $cShippingAddress_arr, $cBillingType, $cBillingAddress_arr, $cItem_arr);

        if (!$cRequestResult_arr['success']) {
            ZahlungsLog::add('za_clickandbuy_jtl', 'cRequestResult_arr: ' . print_r($cRequestResult_arr, 1));
            header('Location: ' . $cFailURL);
            exit();
        } else {
            if (isset($cRequestResult_arr['values']['transaction']['transactionID']) &&
                strlen($cRequestResult_arr['values']['transaction']['transactionID']) > 0
            ) {
                $order->cKommentar .= "\n" . "ClickandBuy Transaction ID: " .
                    $cRequestResult_arr['values']['transaction']['transactionID'];
                $_upd             = new stdClass();
                $_upd->cKommentar = $order->cKommentar;
                Shop::DB()->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $_upd);
            }
            Shop::Smarty()->assign('url', $cRequestResult_arr['values']['transaction']['redirectURL']);
        }
    }

    /**
     * @param $cAuthentication_arr
     * @param $cDetails_arr
     * @param $cShippingType
     * @param $cShippingAddress_arr
     * @param $cBillingType
     * @param $cBillingAddress_arr
     * @param $cItem_arr
     * @return mixed
     */
    private function payRequest($cAuthentication_arr, $cDetails_arr, $cShippingType, $cShippingAddress_arr, $cBillingType, $cBillingAddress_arr, $cItem_arr)
    {
        $cToken    = $this->generateToken($cAuthentication_arr['projectID'], $cAuthentication_arr['secretKey']);
        $amountArr = [
            'amount'   => $cDetails_arr['amount'],
            'currency' => $cDetails_arr['currency']
        ];
        $shippingAddressArr = [
            $cShippingType => $cShippingAddress_arr
        ];
        $billingAddressArr = [
            $cBillingType => $cBillingAddress_arr
        ];
        $itemListArr = [];
        if (count($cItem_arr) > 0) {
            foreach ($cItem_arr as $cItem) {
                $itemListArr[] = new soapval(
                    'item',
                    false,
                    [
                        'itemType'    => 'ITEM',
                        'description' => $cItem['itemDescription'],
                        'quantity'    => $cItem['itemQuantity'],
                        new soapval('unitPrice', false, ['amount' => $cItem['itemUnitPriceAmount'], 'currency' => $cItem['itemUnitPriceCurrency']]),
                        new soapval('totalPrice', false, ['amount' => $cItem['itemTotalPriceAmount'], 'currency' => $cItem['itemTotalPriceCurrency']])
                    ]
                );
            }
        }
        $orderDetailsArr = [
            'text'     => $cDetails_arr['orderDescription'],
            'itemList' => $itemListArr
        ];
        $detailsArr      = [
            'amount'            => $amountArr,
            'basketRisk'        => $cDetails_arr['basketRisk'],
            'clientRisk'        => $cDetails_arr['clientRisk'],
            'authExpiration'    => $cDetails_arr['authExpiration'],
            'confirmExpiration' => $cDetails_arr['confirmExpiration'],
            'successExpiration' => $cDetails_arr['successExpiration'],
            'successURL'        => $cDetails_arr['successURL'],
            'failureURL'        => $cDetails_arr['failureURL'],
            'consumerIPAddress' => $cDetails_arr['consumerIPAddress'],
            'externalID'        => $cDetails_arr['externalID'],
            'consumerLanguage'  => strtolower($cDetails_arr['consumerLanguage']),
            'consumerCountry'   => strtoupper($cDetails_arr['consumerCountry']),
            'orderDetails'      => $orderDetailsArr,
            'shipping'          => $shippingAddressArr,
            'billing'           => $billingAddressArr
        ];
        $detailsArr        = $this->removeEmptyTag($detailsArr);
        $authenticationArr = [
            'merchantID' => $cAuthentication_arr['merchantID'],
            'projectID'  => $cAuthentication_arr['projectID'],
            'token'      => $cToken
        ];
        $reqParam          = [
            'authentication' => $authenticationArr,
            'details'        => $detailsArr
        ];
        if (CAB_D_MODE === 1) {
            ZahlungsLog::add('za_clickandbuy_jtl', 'doSoapRequest: ' . print_r($authenticationArr, 1));
        }

        return $this->doSoapRequest('payRequest_Request', $reqParam);
    }

    /**
     * @param $cAuthentication_arr
     * @param $statusType
     * @param $ids
     * @return mixed
     */
    private function statusRequest($cAuthentication_arr, $statusType, $ids)
    {
        $cToken            = $this->generateToken($cAuthentication_arr['projectID'], $cAuthentication_arr['secretKey']);
        $authenticationArr = [
            'merchantID' => $cAuthentication_arr['merchantID'],
            'projectID'  => $cAuthentication_arr['projectID'],
            'token'      => $cToken
        ];
        $idListArr = [];
        if (count($ids) > 0) {
            // Fill idListArr
            foreach ($ids as $key => $value) {
                $idListArr[] = new soapval($statusType, false, $value);
            }
        }
        $detailsArr = [
            $statusType . 'List' => $idListArr
        ];
        $reqParam   = [
            'authentication' => $authenticationArr,
            'details'        => $detailsArr
        ];

        return $this->doSoapRequest('statusRequest_Request', $reqParam);
    }

    /**
     * @param array $arr
     * @param array $excludes
     * @return mixed
     */
    private function removeEmptyTag($arr, $excludes = [])
    {
        foreach ($arr as $key => $value) {
            if (in_array($key, $excludes)) {
                continue;
            }
            if (is_array($arr[$key])) {
                foreach ($arr[$key] as $key2 => $value2) {
                    if (empty($arr[$key][$key2])) {
                        unset($arr[$key][$key2]);
                    }
                }
            }
            if (empty($arr[$key])) {
                unset($arr[$key]);
            }
        }

        return $arr;
    }

    /**
     * @param $reqName
     * @param $reqParam
     * @return mixed
     */
    private function doSoapRequest($reqName, $reqParam)
    {
        $client                   = new nusoap_client(SOAP_ENDPOINT);
        $client->soap_defencoding = 'UTF-8';
        $success                  = false;
        $result                   = $client->call($reqName, $reqParam, SOAP_NAMESPACE, SOAP_ACTION, false, null, 'rpc', 'literal');
        if ($client->fault) {
            $nusoapResult['error_type']  = 'fault';
            $nusoapResult['faultcode']   = $client->faultcode;
            $nusoapResult['faultstring'] = $client->faultstring;
            $nusoapResult['faultdetail'] = $client->fault_detail;
        } elseif ($client->getError()) {
            $nusoapResult['error_type'] = 'error';
            $nusoapResult['error']      = $client->getError();
        } else {
            $success = true;
        }
        $nusoapResult['success']  = $success;
        $nusoapResult['values']   = $result;
        $nusoapResult['req_name'] = $reqName;
        $nusoapResult['request']  = $client->request;
        $nusoapResult['response'] = $client->response;

        return $nusoapResult;
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        if ($this->verifyNotification($order, $paymentHash, $args)) {
            $this->setOrderStatusToPaid($order);
            $this->sendConfirmationMail($order);
            $incomingPayment          = new stdClass();
            $incomingPayment->fBetrag = $order->fGesamtsummeKundenwaehrung;
            $incomingPayment->cISO    = $order->Waehrung->cISO;
            $this->addIncomingPayment($order, $incomingPayment);
            $this->deletePaymentHash($paymentHash);
            $this->updateNotificationID($order->kBestellung, $paymentHash);
            if (CAB_D_MODE === 1) {
                writeLog(CAB_D_PFAD, 'handleNotification Zahlung OK', 1);
            }
        }

        header('Location: ' . $this->getReturnURL($order));
        exit();
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool|true
     */
    public function finalizeOrder($order, $hash, $args)
    {
        return $this->verifyNotification($order, '_' . $hash, $args);
    }

    /**
     * @param $order
     * @param $paymentHash
     * @param $args
     * @return bool
     */
    private function verifyNotification($order, $paymentHash, $args)
    {
        // Authentifikations Parameter
        $cAuthentication_arr['merchantID'] = $this->getMerchantId();
        $cAuthentication_arr['projectID']  = $this->getProjectId();
        $cAuthentication_arr['secretKey']  = $this->getSecretKey();
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('verifyNotification args: ' . print_r($args, 1), JTLLOG_LEVEL_DEBUG, 'ClickandBuy');
            Jtllog::writeLog('verifyNotification paymentHash: ' . $paymentHash, JTLLOG_LEVEL_DEBUG, 'ClickandBuy');
        }

        $ids                = ['externalID1' => $paymentHash];
        $cRequestResult_arr = $this->statusRequest($cAuthentication_arr, 'externalID', $ids);

        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('verifyNotification cRequestResult_arr: ' . print_r($cRequestResult_arr, 1), JTLLOG_LEVEL_DEBUG, 'ClickandBuy');
        }

        if (CAB_D_MODE === 1) {
            writeLog(CAB_D_PFAD, 'verifyNotification cRequestResult_arr: ' . print_r($cRequestResult_arr, 1), 1);
            writeLog(CAB_D_PFAD, 'verifyNotification args: ' . print_r($args, 1), 1);
        }

        return (isset($cRequestResult_arr['success'], $cRequestResult_arr['values']['transactionList']['transaction']['transactionStatus']) &&
            $cRequestResult_arr['success'] &&
            $cRequestResult_arr['values']['transactionList']['transaction']['transactionStatus'] === 'SUCCESS'
        );
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        // Authentifikations Parameter
        $cAuthentication_arr               = [];
        $cAuthentication_arr['merchantID'] = $this->getMerchantId();
        $cAuthentication_arr['projectID']  = $this->getProjectId();
        $cAuthentication_arr['secretKey']  = $this->getSecretKey();

        if (strlen($cAuthentication_arr['merchantID']) === 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'merchantID' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($cAuthentication_arr['projectID']) === 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'projectID' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($cAuthentication_arr['secretKey']) === 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'secretKey' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return true;
    }
}
