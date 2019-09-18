<?php

require_once PFAD_ROOT . PFAD_INCLUDES_LIBS . PFAD_NUSOAP . 'nusoap.php';
require_once __DIR__ . '/safetypaySha256.inc.php';

/**
 * Class SafetyPayProxy
 */
class SafetyPayProxy
{
    /**
     *
     */
    public function __construct()
    {
        // Web Services Credentials: API Key
        $this->ApiKey          = SAFETYPAY_APIKEY;
        $this->SignatureKey    = SAFETYPAY_SIGNTATURE_KEY;
        $this->CurrCodeDefault = 'EUR'; // Set the Currency Code of Virtual Store (USD, PEn, MXN, EUR, etc.)
        $this->Test            = 1; // Set 1: For Test - Set 0: For Production
        // Production Links v2.2
        $this->wsdlURL = 'https://secure.saftpay.com/prod/WebServices/v2.2/Merchants/MerchantWS.asmx?WSDL';
        if ($this->Test) {
            // Sandbox Links v2.2
            $this->wsdlURL = 'https://secure.saftpay.com/Prod_QAS/WebServices/v2.2/Merchants/MerchantWS.asmx?WSDL';
        }
        // Request Headers for WS v2.2
        $this->headers = '<RequesterCredentials xmlns="SaftpayMerchant v.2.2"><ApiKey>' . $this->ApiKey . '</ApiKey></RequesterCredentials>';
        // Request Date Time
        $this->RequestDateTime = substr((string) date(DATE_ATOM, mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'))), 0, 19);
        // Expiration Time for Transactions
        $this->ExpirationTime = 5;// minutes
    }

    /**
     * @param string $sApiKey
     * @param string $sSignatureKey
     * @return bool
     */
    public function LetKeys($sApiKey = '', $sSignatureKey = '')
    {
        if ($sApiKey !== '') {
            $this->ApiKey = $sApiKey;
        }
        if ($sSignatureKey !== '') {
            $this->SignatureKey = $sSignatureKey;
        }
        $this->headers = '<RequesterCredentials xmlns="SaftpayMerchant v.2.2"><ApiKey>' . $this->ApiKey . '</ApiKey></RequesterCredentials>';

        return true;
    }

    /**
     * @param $testUmgebung
     * @return bool
     */
    public function SetEnvironment($testUmgebung)
    {
        // $testUmgebung: 1 = test; 0 = prod
        $this->Test = (int) $testUmgebung;
        if ($this->Test) {
            // Sandbox Links v2.2
            $this->wsdlURL = 'https://secure.saftpay.com/Prod_QAS/WebServices/v2.2/Merchants/MerchantWS.asmx?WSDL';
        } else {
            // Production Links v2.2
            $this->wsdlURL = 'https://secure.saftpay.com/prod/WebServices/v2.2/Merchants/MerchantWS.asmx?WSDL';
        }

        return true;
    }

    /**
     * Get Proxy Method
     *
     * @return nusoap_client
     */
    public function GetProxy()
    {
        $soap = new nusoap_client($this->wsdlURL, 'wsdl');
        $soap->setHeaders($this->headers);
        $err = $soap->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }

        return $soap;
    }

    /**
     * Communication Test Method
     *
     * @return string
     */
    public function CommunicationTest()
    {
        $param = array(
            'CommunicationTestRequest' => array(
                'RequestDateTime' => $this->RequestDateTime,
                'Signature'       => safetypay_sha256($this->RequestDateTime . $this->SignatureKey)));
        $soap   = $this->GetProxy();
        $Result = $soap->call('CommunicationTest', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }
        if ($Result['CommunicationTestResult']['ErrorManager']['ErrorNumber'] == '0') {
            $response = 'Communication Successful';
        } else {
            $response = 'Error: ' . $Result['CommunicationTestResult']['ErrorManager']['ErrorNumber'] . ' - ' . $Result['CommunicationTestResult']['ErrorManager']['Description'];
        }

        return $response;
    }

    /**
     * Calculation Quote Method
     *
     * @param $CurrencyCode
     * @param $Amount
     * @param $ToCurrencyCode
     * @return mixed
     */
    public function CalculationQuote($CurrencyCode, $Amount, $ToCurrencyCode)
    {
        $param = array(
            'FxCalculationQuoteRequest' => array(
                'RequestDateTime' => $this->RequestDateTime,
                'CurrencyCode'    => $CurrencyCode,
                'Amount'          => $Amount,
                'ToCurrencyCode'  => $ToCurrencyCode,
                'Signature'       => safetypay_sha256($this->RequestDateTime . $CurrencyCode . $Amount . $ToCurrencyCode . $this->SignatureKey)));
        $soap   = $this->GetProxy();
        $Result = $soap->call('FxCalculationQuote', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['FxCalculationQuoteResult'];
    }

    /**
     * Create Express Token Method
     *
     * @param $CurrencyCode
     * @param $Amount
     * @param $MerchantReferenceNo
     * @param $Language
     * @param $TrackingCode
     * @param $TransactionOkURL
     * @param $TransactionErrorURL
     * @return mixed
     */
    public function CreateExpressToken($CurrencyCode, $Amount, $MerchantReferenceNo, $Language, $TrackingCode, $TransactionOkURL, $TransactionErrorURL)
    {
        $param = array(
            'CreateExpressTokenRequest' => array(
                'RequestDateTime' => $this->RequestDateTime,
                'ExpressInfo'     => array(
                    'CurrencyCode'        => $CurrencyCode,
                    'Amount'              => $Amount,
                    'MerchantReferenceNo' => $MerchantReferenceNo,
                    'Language'            => $Language,
                    'TrackingCode'        => $TrackingCode,
                    'ExpirationTime'      => $this->ExpirationTime,
                    'TransactionOkURL'    => $TransactionOkURL,
                    'TransactionErrorURL' => $TransactionErrorURL),
                'Signature'       => safetypay_sha256($this->RequestDateTime . $CurrencyCode . $Amount . $MerchantReferenceNo . $Language . $TrackingCode . $this->ExpirationTime . $TransactionOkURL . $TransactionErrorURL . $this->SignatureKey)));

        $soap   = $this->GetProxy();
        $Result = $soap->call('CreateExpressToken', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['CreateExpressTokenResult'];
    }

    /**
     * Create Transaction Method
     *
     * @param $CurrencyCode
     * @param $Amount
     * @param $ToCurrencyCode
     * @param $MerchantReferenceNo
     * @param $Language
     * @param $FxCalculationQuoteReferenceNo
     * @param $TrackingCode
     * @param $BankID
     * @param $TransactionOkURL
     * @param $TransactionErrorURL
     * @return mixed
     */
    public function CreateTransaction($CurrencyCode, $Amount, $ToCurrencyCode, $MerchantReferenceNo, $Language, $FxCalculationQuoteReferenceNo, $TrackingCode, $BankID, $TransactionOkURL, $TransactionErrorURL)
    {
        $param = array(
            'CreateTransactionRequest' => array(
                'RequestDateTime'               => $this->RequestDateTime,
                'CurrencyCode'                  => $CurrencyCode,
                'Amount'                        => $Amount,
                'ToCurrencyCode'                => $ToCurrencyCode,
                'MerchantReferenceNo'           => $MerchantReferenceNo,
                'Language'                      => $Language,
                'FxCalculationQuoteReferenceNo' => $FxCalculationQuoteReferenceNo,
                'TrackingCode'                  => $TrackingCode,
                'BankID'                        => $BankID,
                'TransactionOkURL'              => $TransactionOkURL,
                'TransactionErrorURL'           => $TransactionErrorURL,
                'Signature'                     => safetypay_sha256($this->RequestDateTime . $CurrencyCode . $Amount . $ToCurrencyCode . $MerchantReferenceNo . $Language . $FxCalculationQuoteReferenceNo . $TrackingCode . $BankID . $TransactionOkURL . $TransactionErrorURL . $this->SignatureKey)));
        $soap   = $this->GetProxy();
        $Result = $soap->call('CreateTransaction', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['CreateTransactionResult'];
    }

    /**
     * @param $CurrencyCode
     * @return mixed
     */
    public function GetBanks($CurrencyCode)
    {
        $param = array(
            'GetBanksRequest' => array(
                'RequestDateTime' => $this->RequestDateTime,
                'CurrencyCode'    => $CurrencyCode,
                'Signature'       => safetypay_sha256($this->RequestDateTime . $this->SignatureKey)));
        $soap   = $this->GetProxy();
        $Result = $soap->call('GetBanks', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['GetBanksResult'];
    }

    /**
     * Get Currencies Method
     *
     * @param        $Language
     * @param        $PreferredToCurrencyCode
     * @param string $ShopperIP
     * @return mixed
     */
    public function GetCurrencies($Language, $PreferredToCurrencyCode, $ShopperIP = '')
    {
        $param = array(
            'GetCurrenciesRequest' => array(
                'RequestDateTime'         => $this->RequestDateTime,
                'Language'                => $Language,
                'PreferredToCurrencyCode' => $PreferredToCurrencyCode,
                'ShopperIP'               => $ShopperIP,
                'Signature'               => safetypay_sha256($this->RequestDateTime . $this->SignatureKey)));
        $soap   = $this->GetProxy();
        $Result = $soap->call('GetCurrencies', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['GetCurrenciesResult'];
    }

    /**
     * Get New Paid Orders Method
     *
     * @return mixed
     */
    public function GetNewPaidOrders()
    {
        $param = array(
            'GetNewPaidOrdersRequest' => array(
                'RequestDateTime' => $this->RequestDateTime,
                'Signature'       => safetypay_sha256($this->RequestDateTime . $this->SignatureKey)));
        $soap   = $this->GetProxy();
        $Result = $soap->call('GetNewPaidOrders', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['GetNewPaidOrdersResult'];
    }

    /**
     * Confirm New Paid Orders Method
     *
     * @param $Items
     * @return mixed
     */
    public function ConfirmNewPaidOrders($Items)
    {
        $ItemsPlain = '';
        foreach ($Items['Items'] as $key => $value) {
            $ItemsPlain .= $value['ReferenceNo'] . $value['MerchantOrderNo'] . $value['IssueCode'];
        }
        $param = array(
            'ConfirmNewPaidOrdersRequest' => array(
                'RequestDateTime'     => $this->RequestDateTime,
                'ListOfNewPaidOrders' => $Items,
                'Signature'           => safetypay_sha256($this->RequestDateTime . $ItemsPlain . $this->SignatureKey)));

        $soap   = $this->GetProxy();
        $Result = $soap->call('ConfirmNewPaidOrders', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['ConfirmNewPaidOrdersResult'];
    }

    /**
     * Confirm Shipped Orders Method
     *
     * @param $ShippingDetail
     * @return mixed
     */
    public function ConfirmShippedOrders($ShippingDetail)
    {
        $ItemsPlain = '';
        foreach ($ShippingDetail as $value) {
            $ItemsPlain .= $value;
        }
        $param = array(
            'ConfirmShippedOrderRequest' => array(
                'RequestDateTime' => $this->RequestDateTime,
                'ShippingDetail'  => $ShippingDetail,
                'Signature'       => safetypay_sha256($this->RequestDateTime . $ItemsPlain . $this->SignatureKey)));

        $soap   = $this->GetProxy();
        $Result = $soap->call('ConfirmShippedOrder', $param, '', '', false, true);
        if ($error = $soap->getError()) {
            die($error);
        }

        return $Result['ConfirmShippedOrderResult'];
    }
}
