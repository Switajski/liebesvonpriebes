<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/BillpayData.class.php';
include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/api/ipl_xml_api.php';

// api
define('BILLPAY_API', 'https://api.billpay.de/xml');
define('BILLPAY_API_TEST', 'https://test-api.billpay.de/xml/offline');

// api paylater
define('BILLPAY_API_PL', 'https://paylater.billpay.de/checkout/options');
define('BILLPAY_API_PL_TEST', 'https://test-paylater.billpay.de/checkout/options');
define('BILLPAY_API_PL_VERSION', '1.3.6');

define('BILLPAY_PRIVACY', 'https://www.billpay.de/api/ratenkauf/datenschutz');
define('BILLPAY_TERMS_RATE', 'https://www.billpay.de/s/agb/tc/%publicApiKey%.html');
define('BILLPAY_TERMS_PAYMENT', 'https://www.billpay.de/api/ratenkauf/zahlungsbedingungen');

// pdfs
define('BILLPAY_PDF_ATTACHMENT', 'billpay_%s_attach.pdf');
define('BILLPAY_PDF_INFORMATION', 'billpay_%s_info.pdf');

define('FILE_HEADER_PDF', '%PDF');
define('FILE_TYPE_PDF', 'application/pdf');
define('FILE_EXT_PDF', '.pdf');

// amount types
define('AMT_NET', 0);
define('AMT_GROSS', 1);

// billpay defaults
define('BILLPAY_MAX_DELAY_IN_DAYS', 20);

/**
 * Billpay implementation
 */
class Billpay extends PaymentMethod
{
    /**
     * @var int
     */
    public $nPaymentType;

    /**
     * @var ipl_module_config_request
     */
    protected static $oModuleConfig;

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name         = 'BillPay';
        $this->caption      = 'BillPay';
        $this->nPaymentType = 0;

        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getOptions()
    {
        $oCustomer   = $_SESSION['Kunde'];
        $oBasketInfo = $this->getBasketTotal($_SESSION['Warenkorb']);
        $cBaseUrl    = ($this->getCoreSetting('mode') === 'live') ? BILLPAY_API_PL : BILLPAY_API_PL_TEST;

        $params = [
            'version'        => BILLPAY_API_PL_VERSION,
            'apiKey'         => $this->getCoreSetting('publicapicode'),
            'cartTotalGross' => BPHelper::fmtAmount($oBasketInfo->fTotal[AMT_GROSS], true),
            'baseAmount'     => BPHelper::fmtAmount($oBasketInfo->fTotal[AMT_GROSS] - $oBasketInfo->fShipping[AMT_GROSS], true),
            'orderCurrency'  => $oBasketInfo->cCurrency->cISO,
            'lang'           => BPHelper::toISO6391(BPHelper::getLanguage()),
            'billingCountry' => BPHelper::mapCountryCode($oCustomer->cLand)
        ];

        $cOptionsUrl  = sprintf('%s?%s', $cBaseUrl, http_build_query($params, null, '&'));
        $cOptionsHash = md5($cOptionsUrl);

        if (!isset($_SESSION['za_billpay_jtl']['oOptions_arr'])) {
            $_SESSION['za_billpay_jtl']['oOptions_arr'] = [];
        }

        if (isset($_SESSION['za_billpay_jtl']['oOptions_arr'][$cOptionsHash])) {
            return $_SESSION['za_billpay_jtl']['oOptions_arr'][$cOptionsHash];
        } else {
            $jsonResults = http_get_contents($cOptionsUrl);

            if (empty($jsonResults)) {
                return null;
            }

            $oResult = json_decode($jsonResults, true);

            if (json_last_error() > 0) {
                return null;
            }

            if (isset($oResult['responseStatus'], $oResult['responseStatus']['errorCode'])) {
                if ((int)$oResult['responseStatus']['errorCode'] === 0) {
                    $_SESSION['za_billpay_jtl']['oOptions_arr'][$cOptionsHash] = $oResult;

                    return $oResult;
                } else {
                    $cCustomerMessage = isset($oResult['responseStatus']['customerMessage'])
                        ? $oResult['responseStatus']['customerMessage']
                        : null;
                    $cMerchantMessage = isset($oResult['responseStatus']['merchantMessage'])
                        ? $oResult['responseStatus']['merchantMessage']
                        : null;

                    if ($cCustomerMessage !== null) {
                        $this->assignMessage($cCustomerMessage, 'error');
                    }
                    if ($cMerchantMessage !== null) {
                        $this->log($cMerchantMessage);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Bestellung $oOrder
     */
    public function preparePaymentProcess($oOrder)
    {
        if (is_object($_SESSION['za_billpay_jtl']['oOrderEx'])) {
            $oData = new BillpayData($_SESSION['za_billpay_jtl']['oOrderEx']);
            unset($_SESSION['za_billpay_jtl']['oOrderEx']);

            $cOrderNumber = baueBestellnummer();
            $oBasketInfo  = $oData->oBasketInfo;
            $nPaymentType = $this->nPaymentType;
            // capture
            $oCapture = $this->getApi('capture');
            $oCapture->set_capture_params(
                $oData->cTXID,
                BPHelper::fmtAmount($oBasketInfo->fTotal[AMT_GROSS], true),
                BPHelper::strEncode(strtoupper($oBasketInfo->cCurrency->cISO)),
                BPHelper::strEncode($cOrderNumber),
                $oOrder->kKunde
            );

            try {
                $oCapture->send();
                $this->log("send capture request", LOGLEVEL_DEBUG);

                if (!$oCapture->has_error()) {
                    $oOrder = finalisiereBestellung($cOrderNumber, false);
                    // set order status to paid
                    if ($this->getCoreSetting('aspaid') === 'Y') {
                        $oIncomingPayment          = new stdClass();
                        $oIncomingPayment->fBetrag = $oOrder->fGesamtsummeKundenwaehrung;
                        $oIncomingPayment->cISO    = $oOrder->Waehrung->cISO;
                        $this->addIncomingPayment($oOrder, $oIncomingPayment);
                        $this->setOrderStatusToPaid($oOrder);
                    }
                    // additional payment information
                    $oPaymentInfo = new ZahlungsInfo(0, $oOrder->kBestellung);
                    // additional mail information
                    $oMail = null;

                    switch ($nPaymentType) {
                        case IPL_CORE_PAYMENT_TYPE_INVOICE:
                            $oPaymentInfo->kKunde            = $oOrder->kKunde;
                            $oPaymentInfo->kBestellung       = $oOrder->kBestellung;
                            $oPaymentInfo->cInhaber          = BPHelper::strDecode($oCapture->get_account_holder());
                            $oPaymentInfo->cIBAN             = BPHelper::strDecode($oCapture->get_account_number());
                            $oPaymentInfo->cBIC              = BPHelper::strDecode($oCapture->get_bank_code());
                            $oPaymentInfo->cKontoNr          = $oPaymentInfo->cIBAN;
                            $oPaymentInfo->cBLZ              = $oPaymentInfo->cBIC;
                            $oPaymentInfo->cBankName         = BPHelper::strDecode($oCapture->get_bank_name());
                            $oPaymentInfo->cVerwendungszweck = $oCapture->get_invoice_reference();
                            // save payment information
                            if (isset($oPaymentInfo->kZahlungsInfo)) {
                                $oPaymentInfo->updateInDB();
                            } else {
                                $oPaymentInfo->insertInDB();
                            }
                            break;

                        case IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT:
                            $oPaymentInfo->kKunde      = $oOrder->kKunde;
                            $oPaymentInfo->kBestellung = $oOrder->kBestellung;
                            $oPaymentInfo->cInhaber    = $oData->cAccountholder;
                            $oPaymentInfo->cIBAN       = $oData->cAccountnumber;
                            $oPaymentInfo->cBIC        = $oData->cSortcode;
                            $oPaymentInfo->cKontoNr    = $oPaymentInfo->cIBAN;
                            $oPaymentInfo->cBLZ        = $oPaymentInfo->cBIC;
                            // save payment information
                            if (isset($oPaymentInfo->kZahlungsInfo)) {
                                $oPaymentInfo->updateInDB();
                            } else {
                                $oPaymentInfo->insertInDB();
                            }
                            break;

                        case IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT:
                            $oMail                  = new stdClass();
                            $oMail->oAttachment_arr = [];

                            $oPDFAttach = BPHelper::savePDF(BILLPAY_PDF_ATTACHMENT, $cOrderNumber, $oCapture->get_email_attachment_pdf());
                            $oPDFInfo   = BPHelper::savePDF(BILLPAY_PDF_INFORMATION, $cOrderNumber, $oCapture->get_standard_information_pdf());

                            if (is_object($oPDFAttach)) {
                                $oPDFAttach->cName        = 'Bestellung_' . $oOrder->cBestellNr . FILE_EXT_PDF;
                                $oPDFAttach->cType        = FILE_TYPE_PDF;
                                $oMail->oAttachment_arr[] = $oPDFAttach;
                            } else {
                                $this->log('PDF-Datei konnte nicht erstellt werden', LOGLEVEL_ERROR);
                            }
                            if (is_object($oPDFInfo)) {
                                $oPDFInfo->cName          = 'Standardinformationen_' . $oOrder->cBestellNr . FILE_EXT_PDF;
                                $oPDFInfo->cType          = FILE_TYPE_PDF;
                                $oMail->oAttachment_arr[] = $oPDFInfo;
                            } else {
                                $this->log('PDF-Datei konnte nicht erstellt werden', LOGLEVEL_ERROR);
                            }

                            /*
                            $oPaymentInfo->kKunde      = $oOrder->kKunde;
                            $oPaymentInfo->kBestellung = $oOrder->kBestellung;
                            $oPaymentInfo->cInhaber    = $oData->cAccountholder;
                            $oPaymentInfo->cIBAN       = $oData->cAccountnumber;
                            $oPaymentInfo->cBIC        = $oData->cSortcode;
                            $oPaymentInfo->cKontoNr    = $oPaymentInfo->cIBAN;
                            $oPaymentInfo->cBLZ        = $oPaymentInfo->cBIC;
                            
                            // save payment information
                            if (isset($oPaymentInfo->kZahlungsInfo)) {
                                $oPaymentInfo->updateInDB();
                            } else {
                                $oPaymentInfo->insertInDB();
                            }
                            */

                            $oPaymentInfo->kKunde            = $oOrder->kKunde;
                            $oPaymentInfo->kBestellung       = $oOrder->kBestellung;
                            $oPaymentInfo->cInhaber          = BPHelper::strDecode($oCapture->get_account_holder());
                            $oPaymentInfo->cIBAN             = BPHelper::strDecode($oCapture->get_account_number());
                            $oPaymentInfo->cBIC              = BPHelper::strDecode($oCapture->get_bank_code());
                            $oPaymentInfo->cKontoNr          = $oPaymentInfo->cIBAN;
                            $oPaymentInfo->cBLZ              = $oPaymentInfo->cBIC;
                            $oPaymentInfo->cBankName         = BPHelper::strDecode($oCapture->get_bank_name());
                            $oPaymentInfo->cVerwendungszweck = $oCapture->get_invoice_reference();
                            // save payment information
                            if (isset($oPaymentInfo->kZahlungsInfo)) {
                                $oPaymentInfo->updateInDB();
                            } else {
                                $oPaymentInfo->insertInDB();
                            }

                            break;
                    }

                    // mail
                    $this->sendMail($oOrder->kBestellung, MAILTEMPLATE_BESTELLBESTAETIGUNG, $oMail);

                    // smarty
                    Shop::Smarty()->assign('oOrder', $oOrder)
                        ->assign('oPaymentInfo', $oPaymentInfo)
                        ->assign('nPaymentType', $nPaymentType)
                        ->assign('nSSL', pruefeSSL())
                        ->assign('nState', 1)
                        ->assign('abschlussseite', 1);

                    // clear session
                    $session = Session::getInstance();
                    $session->cleanUp();
                } else {
                    $this->assignMessage($oCapture->get_customer_error_message(), 'error');
                    $this->log($oCapture->get_merchant_error_message());
                }
            } catch (Exception $e) {
                $this->log($e->getMessage());
            }
        } else {
            $this->log("canceled capture, invalid session information");
            header("location: bestellvorgang.php?editZahlungsart=1");
        }
    }

    /**
     *
     */
    public function handleConfirmation()
    {
        /*
        $oData  = $_SESSION['za_billpay_jtl']['oOrderEx'];
        $nRate     = intval($oData->nRate);
        $oRateInfo = (isset($oData->oRateInfo)) ? $oData->oRateInfo : null;
        $oRate     = (isset($oRateInfo->aRates_arr[$nRate])) ? $oRateInfo->aRates_arr[$nRate] : null;
        Shop::Smarty()->assign('oRate', $oRate);
        */
    }

    /**
     * @param $nPaymentType
     * @return bool
     */
    public function isUseable($nPaymentType)
    {
        $oOptions = $this->getOptions();

        if ($oOptions === null) {
            return false;
        }

        switch ($nPaymentType) {
            case IPL_CORE_PAYMENT_TYPE_INVOICE: {
                return (int)$oOptions['paymentOptions']['b2C']['invoice']['enabled'] === 1;
                break;
            }

            case IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT: {
                return (int)$oOptions['paymentOptions']['b2C']['directDebit']['enabled'] === 1;
                break;
            }

            case IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT: {
                return (int)$oOptions['paymentOptions']['b2C']['transactionCredit']['enabled'] === 1;
                break;
            }

            case IPL_CORE_PAYMENT_TYPE_PAY_LATER: {
                return (int)$oOptions['paymentOptions']['b2C']['paylater']['enabled'] === 1;
                break;
            }

            case IPL_CORE_PAYMENT_TYPE_PAY_LATER_COLLATERAL: {
                return (int)$oOptions['paymentOptions']['b2C']['paylaterCollateralPromise']['enabled'] === 1;
                break;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        return isset($_SESSION['za_billpay_jtl']['validated']) && (boolean) $_SESSION['za_billpay_jtl']['validated'];
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        // Identitaets- und Bonitaetspruefung fehlgeschlagen
        if (isset($_SESSION['za_billpay_jtl']['bUse']) && !$_SESSION['za_billpay_jtl']['bUse']) {
            if (isset($_SESSION['za_billpay_jtl']['cMessage'])) {
                Shop::Smarty()->assign('cFehler', $_SESSION['za_billpay_jtl']['cMessage']);
            }

            return false;
        }

        if (!BPHelper::isValidCountry($this->nPaymentType, $_SESSION['Kunde']->cLand)) {
            return false;
        }

        // Nur anbieten, wenn keine abweichende Lieferadresse setzt wurde
        if (isset($_SESSION['Bestellung']->kLieferadresse) && $_SESSION['Bestellung']->kLieferadresse == -1) {
            return false;
        }

        return $this->isUseable($this->nPaymentType);
    }

    /**
     * @param mixed $mixedData
     * @param bool  $preauthError
     * @return bool
     */
    public function handleAdditional($mixedData, $preauthError = false)
    {
        $oData       = new BillpayData();
        $oCustomer   = $_SESSION['Kunde'];
        $oBasketInfo = $this->getBasketTotal($_SESSION['Warenkorb']);

        $_SESSION['za_billpay_jtl']['oOrderEx']  = null;
        $_SESSION['za_billpay_jtl']['validated'] = false;

        // form submit
        if (isset($mixedData['billpay'])) {
            $cBillpay_arr = $mixedData['billpay'];

            $oData->bB2B        = false;
            $oData->bToc        = false;
            $oData->oBasketInfo = $oBasketInfo;

            $oData->cTel        = $cBillpay_arr['customer_phone_number'];
            $oData->cAnrede     = $cBillpay_arr['customer_salutation'];
            $oData->dGeburtstag = $cBillpay_arr['customer_day_of_birth'];
            // addressAddition

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_INVOICE) {
                $oData->bToc = $cBillpay_arr['invoice_toc'] == 'true';
                $oData->bB2B = $cBillpay_arr['invoice_customer_group'] === 'business';
            }

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT) {
                $oData->bToc = $cBillpay_arr['direct_debit_toc'] == 'true';
                $oData->bB2B = $cBillpay_arr['direct_debit_customer_group'] === 'business';
            }

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER) {
                $oData->bToc         = $cBillpay_arr['paylater_toc'] == 'true';
                $oData->bB2B         = $cBillpay_arr['paylater_customer_group'] === 'business';
                $oData->nInstalments = $cBillpay_arr['paylater_instalments_count'];
                $oData->nDuration    = $cBillpay_arr['paylater_instalment_amount'];
                $oData->nFeeTotal    = $cBillpay_arr['paylater_fee_absolute'];
                $oData->nTotalAmount = $cBillpay_arr['paylater_total_amount'];
            }

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT) {
                $oData->bToc  = $cBillpay_arr['transaction_credit_toc'] == 'true';
                $oData->nRate = $cBillpay_arr['transaction_credit_instalments_count'];

                $oData->oRate = (object) [
                    'duration'              => $cBillpay_arr['transaction_credit_duration'],
                    'instalmentsCount'      => $cBillpay_arr['transaction_credit_instalments_count'],
                    'instalmentAmount'      => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_instalment_amount']),
                    'firstInstalmentAmount' => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_first_instalment_amount']),
                    'totalAmount'           => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_total_amount']),
                    'feeAbsolute'           => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_fee_absolute']),
                    'feePercentage'         => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_fee_percentage']),
                    'processingFee'         => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_processing_fee_absolute']),
                    'annualPercentageRate'  => BPHelper::fmtAmountX($cBillpay_arr['transaction_credit_annual_percentage_rate'])
                ];
            }

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT || $this->nPaymentType == IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT || $this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER) {
                $oData->cAccountholder = $cBillpay_arr['account_holder'];
                $oData->cAccountnumber = $cBillpay_arr['customer_iban'];
                $oData->cSortcode      = $cBillpay_arr['customer_bic'];
            }

            if ($oData->bB2B) {
                $oData->cFirma      = $cBillpay_arr['company_name'];
                $oData->cInhaber    = $cBillpay_arr['company_holder'];
                $oData->cRechtsform = $cBillpay_arr['company_legal_form'];
                $oData->cHrn        = $cBillpay_arr['company_register_number'];
                $oData->cUSTID      = $cBillpay_arr['company_tax_number'];
            }

            // validation
            $cMissing_arr = [];

            if (!$oData->bToc) {
                $cMissing_arr[] = 'payment_toc';
            }

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT ||
                $this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER ||
                $this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER_COLLATERAL
            ) {
                if (strlen($oData->cTel) === 0) {
                    $cMissing_arr[] = 'customer_phone_number';
                }
            }

            if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT ||
                $this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER
            ) {
                if (strlen($oData->cAccountholder) === 0) {
                    $cMissing_arr[] = 'account_holder';
                }
                if (strlen($oData->cAccountnumber) === 0) {
                    $cMissing_arr[] = 'customer_iban';
                }
                if (strtoupper(substr($oData->cAccountnumber, 0, 2)) !== 'DE' && strlen($oData->cSortcode) === 0) {
                    $cMissing_arr[] = 'customer_bic';
                }
            }

            if ($oData->bB2B) {
                if (strlen($oData->cFirma) === 0) {
                    $cMissing_arr[] = 'company_name';
                }
                if (strlen($oData->cInhaber) === 0) {
                    $cMissing_arr[] = 'company_holder';
                }
                if (strlen($oData->cRechtsform) === 0) {
                    $cMissing_arr[] = 'company_legal_form';
                }
                if (strlen($oData->cHrn) === 0) {
                    $cMissing_arr[] = 'company_register_number';
                }
            } else {
                if (strlen($oData->cAnrede) === 0) {
                    $cMissing_arr[] = 'customer_salutation';
                }
                if (strlen($oData->dGeburtstag) === 0) {
                    $cMissing_arr[] = 'customer_day_of_birth';
                } else {
                    $date = new DateTime($oData->dGeburtstag);
                    $age  = $date->diff(new DateTime())->format('%y');
                    if ($age < 18) {
                        $oData->dGeburtstag = '';
                        $cMissing_arr[]     = 'customer_day_of_birth';
                    }
                }
            }

            // override form data
            $oData->ckundengruppe = $oData->bB2B ? 'business' : 'private';
            $oCustomer->cTel      = $oData->cTel;

            if ($oData->bB2B) {
                $oCustomer->cFirma = $oData->cFirma;
                $oCustomer->cUSTID = $oData->cUSTID;
            } else {
                $oCustomer->dGeburtstag = $oData->dGeburtstag;
                $oCustomer->cAnrede     = $oCustomer->cAnrede = BPHelper::mapSalutation($oData->cAnrede, true);
            }

            if ($preauthError === false && count($cMissing_arr) === 0) {
                $_SESSION['za_billpay_jtl']['validated'] = true;
                $_SESSION['za_billpay_jtl']['oOrderEx']  = (object)(array)$oData;

                return true;
            }

            Shop::Smarty()->assign('cMissing_arr', $cMissing_arr);
        }

        $widgetOptions = [
            'checkout' => [],
            'type'     => BPHelper::getPaymentType($this->nPaymentType),
            'shop'     => [
                'apiKey' => $this->getCoreSetting('publicapicode'),
                'live'   => $this->getCoreSetting('mode') === 'live'
            ],
            'order' => [
                'cartAmount'  => $oBasketInfo->fArticle[AMT_GROSS] - $oBasketInfo->fRebate[AMT_GROSS],
                'orderAmount' => $oBasketInfo->fTotal[AMT_GROSS],
                'currency'    => $oBasketInfo->cCurrency->cISO
            ],
            'customer' => [
                'customerGroup'  => $oData->cKundengruppe,
                'salutation'     => BPHelper::mapSalutation($oCustomer->cAnrede),
                'firstName'      => BPHelper::strEncode($oCustomer->cVorname, 50),
                'lastName'       => BPHelper::strEncode($oCustomer->cNachname, 50),
                'street'         => BPHelper::strEncode($oCustomer->cStrasse, 50),
                'streetNo'       => BPHelper::strEncode($oCustomer->cHausnummer, 50),
                'zip'            => BPHelper::strEncode($oCustomer->cPLZ, 10),
                'city'           => BPHelper::strEncode($oCustomer->cOrt, 50),
                'dayOfBirth'     => $oCustomer->dGeburtstag,
                'phoneNumber'    => BPHelper::strEncode($oCustomer->cTel, 50),
                'countryIso2'    => $oCustomer->cLand,
                'countryIso3'    => BPHelper::mapCountryCode($oCustomer->cLand),
                'language'       => BPHelper::toISO6391(BPHelper::getLanguage()),
                'identifier'     => md5(session_id()),
                'companyName'    => BPHelper::strEncode($oCustomer->cFirma, 200),
                'companyHolder'  => BPHelper::strEncode($oData->cInhaber, 100),
                'legalForm'      => $oData->cRechtsform,
                'registerNumber' => BPHelper::strEncode($oData->cHrn, 20),
                'taxNumber'      => $oCustomer->cUSTID,
                'accountHolder'  => BPHelper::strEncode($oData->cAccountholder, 50)
            ],
            'inputNames' => [
                'order' => [
                    'cartAmount'  => 'billpay[cart_amount]',
                    'orderAmount' => 'billpay[order_amount]',
                    'currency'    => 'billpay[currency]'
                    ],
                'customer' => [
                    'salutation'     => 'billpay[customer_salutation]',
                    'phoneNumber'    => 'billpay[customer_phone_number]',
                    'dayOfBirth'     => 'billpay[customer_day_of_birth]',
                    'language'       => 'billpay[customer_language]',
                    'companyName'    => 'billpay[company_name]',
                    'companyHolder'  => 'billpay[company_holder]',
                    'legalForm'      => 'billpay[company_legal_form]',
                    'registerNumber' => 'billpay[company_register_number]',
                    'taxNumber'      => 'billpay[company_tax_number]',
                    'bankAccount'    => [
                        'iban' => 'billpay[customer_iban]',
                        'bic'  => 'billpay[customer_bic]'
                    ],
                    'accountHolder' => 'billpay[account_holder]'
                ],
                'paymentMethods' => [
                    'invoice' => [
                        'customerGroup'  => 'billpay[invoice_customer_group]',
                        'termsOfService' => 'billpay[invoice_toc]'
                    ],
                    'directDebit' => [
                        'customerGroup'  => 'billpay[direct_debit_customer_group]',
                        'termsOfService' => 'billpay[direct_debit_toc]'
                    ],
                    'transactionCredit' => [
                        'customerGroup'         => 'billpay[transaction_credit_customer_group]',
                        'duration'              => 'billpay[transaction_credit_duration]',
                        'instalmentsCount'      => 'billpay[transaction_credit_instalments_count]',
                        'instalmentAmount'      => 'billpay[transaction_credit_instalment_amount]',
                        'firstInstalmentAmount' => 'billpay[transaction_credit_first_instalment_amount]',
                        'totalAmount'           => 'billpay[transaction_credit_total_amount]',
                        'feeAbsolute'           => 'billpay[transaction_credit_fee_absolute]',
                        'feePercentage'         => 'billpay[transaction_credit_fee_percentage]',
                        'processingFee'         => 'billpay[transaction_credit_processing_fee_absolute]',
                        'annualPercentageRate'  => 'billpay[transaction_credit_annual_percentage_rate]',
                        'termsOfService'        => 'billpay[transaction_credit_toc]'
                    ],
                    'paylater' => [
                        'customerGroup'    => 'billpay[paylater_customer_group]',
                        'duration'         => 'billpay[paylater_duration]',
                        'instalmentsCount' => 'billpay[paylater_instalments_count]',
                        'instalmentAmount' => 'billpay[paylater_instalment_amount]',
                        'feeAbsolute'      => 'billpay[paylater_fee_absolute]',
                        'totalAmount'      => 'billpay[paylater_total_amount]',
                        'termsOfService'   => 'billpay[paylater_toc]'
                    ]
                ]
            ]
        ];

        $widgetOptionsJSON = json_encode($widgetOptions, true);

        if (json_last_error() > 0 || $widgetOptionsJSON === null) {
            $this->assignMessage(json_last_error_msg());
        } else {
            Shop::Smarty()
                ->assign('widgetOptionsJSON', $widgetOptionsJSON)
                ->assign('widgetType', BPHelper::getPaymentType($this->nPaymentType));
        }

        return false;
    }

    /**
     * @return bool
     */
    public function preauthRequest()
    {
        // push all positions to basket
        pruefeGuthabenNutzen();
        $nCheck = plausiKupon($_POST);

        $oCustomer = $_SESSION['Kunde'];
        $oShipAddr = $_SESSION['Lieferadresse'];

        BPHelper::removeHTML($oCustomer, $oShipAddr);

        $bOk = $this->preAuthorize($oCustomer, $oShipAddr, $_SESSION['Warenkorb'], $_SESSION['Versandart']);

        if (!$bOk) {
            $this->handleAdditional($_POST, true);
        }

        return $bOk;
    }

    /**
     * @param string   $cType
     * @param null|int $nPaymentType
     * @return bool|ipl_xml_request
     */
    public function getApi($cType, $nPaymentType = null)
    {
        $cClass     = 'ipl_' . $cType . '_request';
        $cClassFile = PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/api/' . $cClass . '.php';

        if (!file_exists($cClassFile)) {
            $this->log("file " . $cClassFile . " could not be found");

            return false;
        }
        require_once $cClassFile;

        if (!class_exists($cClass)) {
            $this->log("class " . $cClass . " could not be found");

            return false;
        }
        // auth
        $cMID    = $this->getCoreSetting('mid');
        $cPID    = $this->getCoreSetting('pid');
        $cSecure = md5($this->getCoreSetting('bpsecure'));
        // mode
        $cMode = $this->getCoreSetting('mode');
        $cURL  = ($cMode === 'live') ? BILLPAY_API : BILLPAY_API_TEST;
        // default api params
        $oReq = new $cClass($cURL, $nPaymentType);
        $oReq->set_default_params($cMID, $cPID, $cSecure);

        if (!is_object($oReq)) {
            $this->log("error while initializing api function");

            return false;
        }

        return $oReq;
    }

    /**
     * @param $oCustomer
     * @param $oShipAddr
     * @param $oBasket
     * @param $oShipping
     * @param $cReference
     * @return int
     */
    public function preAuthorize($oCustomer, $oShipAddr, $oBasket, $oShipping, $cReference = '')
    {
        $oData = $_SESSION['za_billpay_jtl']['oOrderEx'];

        $eCustomerType = 'g';// g: guest, n: new, e: existing
        if ($oCustomer->kKunde > 0) {
            $eCustomerType = 'e';
        }
        // p: private customer, b: business customer
        $eCustomerGroup = $oData->bB2B ? 'b' : 'p';
        $oPreAuth       = $this->getApi('preauthorize', $this->nPaymentType);
        // customer address
        $oPreAuth->set_customer_details(
            (int)$oCustomer->kKunde,                           // customerid
            BPHelper::strEncode($eCustomerType, 1),            // customertype
            BPHelper::mapSalutation($oCustomer->cAnrede),      // salutation
            BPHelper::strEncode($oCustomer->cTitel, 20),       // title
            BPHelper::strEncode($oCustomer->cVorname, 50),     // firstname
            BPHelper::strEncode($oCustomer->cNachname, 50),    // lastname
            BPHelper::strEncode($oCustomer->cStrasse, 50),     // street
            BPHelper::strEncode($oCustomer->cHausnummer, 50),  // streetno
            BPHelper::strEncode($oCustomer->cAdressZusatz, 50),// addressaddition
            BPHelper::strEncode($oCustomer->cPLZ, 10),         // zip
            BPHelper::strEncode($oCustomer->cOrt, 50),         // city
            BPHelper::mapCountryCode($oCustomer->cLand),       // country
            BPHelper::strEncode($oCustomer->cMail, 50),        // email
            BPHelper::strEncode($oCustomer->cTel, 50),         // phone
            BPHelper::strEncode($oCustomer->cMobil, 50),       // cellphone
            BPHelper::fmtDate($oCustomer->dGeburtstag, true),  // birthday
            BPHelper::toISO6391(BPHelper::getLanguage()),      // language
            BPHelper::strEncode(getRealIp()),                  // ip address
            BPHelper::strEncode($eCustomerGroup, 1)            // customerGroup
        );

        // shipping address
        if ($oShipAddr->kLieferadresse !== 0) {
            $oPreAuth->set_shipping_details(
                0,                                                 // usebillingaddress
                BPHelper::mapSalutation($oShipAddr->cAnrede),      // salutation
                BPHelper::strEncode($oShipAddr->cTitel, 20),       // title
                BPHelper::strEncode($oShipAddr->cVorname, 50),     // firstname
                BPHelper::strEncode($oShipAddr->cNachname, 50),    // lastname
                BPHelper::strEncode($oShipAddr->cStrasse, 50),     // street
                BPHelper::strEncode($oShipAddr->cHausnummer, 50),  // streetno
                BPHelper::strEncode($oShipAddr->cAdressZusatz, 50), // addressaddition
                BPHelper::strEncode($oShipAddr->cPLZ, 10),         // zip
                BPHelper::strEncode($oShipAddr->cOrt, 50),         // city
                BPHelper::mapCountryCode($oShipAddr->cLand),       // country
                BPHelper::strEncode($oShipAddr->cTel, 50),         // phone
                BPHelper::strEncode($oShipAddr->cMobil, 50)        // cellphone
            );
        } else {
            $oPreAuth->set_shipping_details(1);
        }
        // company information
        if ($eCustomerGroup === 'b') {
            $oPreAuth->set_company_details(
                BPHelper::strEncode($oCustomer->cFirma, 200), // name
                BPHelper::strEncode($oData->cRechtsform),     // legal form
                BPHelper::strEncode($oData->cHrn),            // register number
                BPHelper::strEncode($oData->cInhaber),        // holder name
                BPHelper::strEncode($oCustomer->cUSTID)       // tax number
            );
        }
        $cISOSprache        = BPHelper::getLanguage();
        $oBasketInfo        = $this->getBasketTotal($oBasket);
        $oData->oBasketInfo = $oBasketInfo;
        // add article and freebie
        foreach ($oBasket->PositionenArr as $oPosition) {
            $fPreisEinzelNetto = $oPosition->fPreisEinzelNetto;
            if (isset($oPosition->WarenkorbPosEigenschaftArr) && is_array($oPosition->WarenkorbPosEigenschaftArr) &&
                (!isset($oPosition->Artikel->kVaterArtikel) || (int)$oPosition->Artikel->kVaterArtikel === 0)
            ) {
                foreach ($oPosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->fAufpreis > 0) {
                        $fPreisEinzelNetto += $oWarenkorbPosEigenschaft->fAufpreis;
                    }
                }
            }
            $fNet = $fPreisEinzelNetto * $oBasketInfo->cCurrency->fFaktor;

            $fAmount[AMT_NET]   = BPHelper::fmtAmount($fNet);
            $fAmount[AMT_GROSS] = BPHelper::fmtAmount(berechneBrutto($fNet, gibUst($oPosition->kSteuerklasse)));

            if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL || $oPosition->nPosTyp == C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $oPreAuth->add_article(
                    (int)$oPosition->kArtikel,                                       // articleid
                    (float)$oPosition->nAnzahl,                                      // articlequantity
                    BPHelper::strEncode($oPosition->cName[$cISOSprache], 50),        // articlename
                    BPHelper::strEncode($oPosition->Artikel->cKurzBeschreibung, 50), // articledescription
                    BPHelper::fmtAmount($fAmount[AMT_NET], true),                    // articleprice
                    BPHelper::fmtAmount($fAmount[AMT_GROSS], true)                   // articlepricegross
                );
            }
        }
        // set total
        $shippingName = '';
        if (isset($oShipping->cName[$cISOSprache])) {
            $shippingName = BPHelper::strEncode($oShipping->cName[$cISOSprache], 50);
        } elseif (is_string($oShipping->cName)) {
            $shippingName = BPHelper::strEncode($oShipping->cName, 50);
        }

        $oPreAuth->set_total(
            BPHelper::fmtAmount($oBasketInfo->fRebate[AMT_NET], true),     // rebate
            BPHelper::fmtAmount($oBasketInfo->fRebate[AMT_GROSS], true),   // rebate gross
            $shippingName,                                                 // shippingname
            BPHelper::fmtAmount(
                $oBasketInfo->fShipping[AMT_NET] +
                $oBasketInfo->fSurcharge[AMT_NET], true
            ),                                                             // shippingprice
            BPHelper::fmtAmount(
                $oBasketInfo->fShipping[AMT_GROSS] +
                $oBasketInfo->fSurcharge[AMT_GROSS], true
            ),                                                             // shippingpricegross
            BPHelper::fmtAmount($oBasketInfo->fTotal[AMT_NET], true),      // carttotalprice
            BPHelper::fmtAmount($oBasketInfo->fTotal[AMT_GROSS], true),    // carttotalpricegross
            BPHelper::strEncode($_SESSION['Waehrung']->cISO, 3),           // currency
            $cReference                                                    // reference
        );

        $oRate        = new stdClass();
        $bBankAccount = false;

        switch ($this->nPaymentType) {
            // rate
            case IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT: {
                $nRate = (int)$oData->nRate;
                $oRate = $oData->oRate;

                $oPreAuth->set_rate_request(
                    $nRate,                                        // ratecount
                    BPHelper::fmtAmount($oRate->totalAmount, true) // totalamount
                );

                // $oData->oRateInfo = $oRateInfo; // @todo - check if oRateInfo is still needed
                break;
            }
            // bank account
            case IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT: {
                $bBankAccount = true;
                break;
            }
            // pay later
            case IPL_CORE_PAYMENT_TYPE_PAY_LATER: {
                $bBankAccount = true;
                $oPreAuth->set_rate_request(
                    BPHelper::strEncode($oData->nInstalments),  // ratecount
                    BPHelper::strEncode($oData->nTotalAmount)   // totalamount
                );
                $oPreAuth->set_async_capture(
                    Shop::getURL(), // redirect url
                    Shop::getURL()  // notify url
                );
                break;
            }
        }

        if ($bBankAccount) {
            $oPreAuth->set_bank_account(
                BPHelper::strEncode($oData->cAccountholder, 100),  // accountholder
                BPHelper::strEncode($oData->cAccountnumber, 34),   // accountnumber
                BPHelper::strEncode($oData->cSortcode, 11)         // sortcode
            );
        }

        // order history
        if ($eCustomerType == 'e') {
            $oOrder_arr = Shop::DB()->query(
                "SELECT tbestellung.dErstellt, tbestellung.fGesamtsumme, twaehrung.cISO, tbestellung.kBestellung
                    FROM tbestellung
                    LEFT JOIN twaehrung
                        ON tbestellung.kWaehrung = twaehrung.kWaehrung
                    WHERE kKunde = " . (int)$oCustomer->kKunde . "
                    ORDER BY dErstellt DESC 
                    LIMIT 20", 2
            );
            if (is_array($oOrder_arr) && count($oOrder_arr) > 0) {
                foreach ($oOrder_arr as $oOrder) {
                    $oPreAuth->add_order_history(
                        $oOrder->kBestellung,                              // horderid
                        BPHelper::fmtDate($oOrder->dErstellt, true, true), // hdate
                        BPHelper::fmtAmount($oOrder->fGesamtsumme, true),  // hamount
                        BPHelper::strEncode($oOrder->cISO, 3),             // hcurrency
                        100,                                               // hpaymenttype (100: other)
                        0                                                  // hstatus (0: paid, 1: open)
                    );
                }
            }
        }

        if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER) {
            $oPreAuth->set_capture_request_necessary(false);
        }

        $oPreAuth->set_terms_accepted(true);

        try {
            $oPreAuth->send();

            switch ($oPreAuth->get_status()) {
                case 'APPROVED': {
                    $oData->cTXID = $oPreAuth->get_bptid();

                    if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT) {
                        // new positions
                        $cName['ger']   = 'Zinsaufschlag';
                        $cName['eng']   = 'Interest charge';
                        $currencyFactor = (isset($oBasketInfo->cCurrency) && isset($oBasketInfo->cCurrency->fFaktor))
                            ? $oBasketInfo->cCurrency->fFaktor
                            : 1;
                        $this->addSpecialPosition($cName, 1, $oRate->feeAbsolute / $currencyFactor, C_WARENKORBPOS_TYP_ZINSAUFSCHLAG, true, true/*, $cNotice*/);
                        $cName['ger'] = 'Bearbeitungsgeb&uuml;hr';
                        $cName['eng'] = 'Processing fee';
                        $this->addSpecialPosition($cName, 1, $oRate->processingFee / $currencyFactor, C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR, true, true, '');
                    }

                    return 1;
                    break;
                }

                case 'PRE_APPROVED': {
                    $oData->cTXID = $oPreAuth->get_bptid();

                    if ($this->nPaymentType == IPL_CORE_PAYMENT_TYPE_PAY_LATER) {
                        $oData->cAmount           = BPHelper::strDecode($oPreAuth->get_prepayment_amount());
                        $oData->cRedirectUrl      = BPHelper::strDecode($oPreAuth->get_external_redirect_url());
                        $oData->cRateUrl          = BPHelper::strDecode($oPreAuth->get_rate_plan_url());
                        $oData->cCampaignType     = BPHelper::strDecode($oPreAuth->get_campaign_type());
                        $oData->cCampaignText     = BPHelper::strDecode($oPreAuth->get_campaign_display_text());
                        $oData->cCampaignImageUrl = BPHelper::strDecode($oPreAuth->get_campaign_display_image_url());
                    }

                    return 2;
                    break;
                }

                case 'DENIED': {
                    $_SESSION['za_billpay_jtl']['bUse']     = false;
                    $_SESSION['za_billpay_jtl']['cMessage'] = utf8_decode($oPreAuth->get_customer_error_message());

                    $this->logEx($oPreAuth->get_merchant_error_message(), $oCustomer);
                    header("location: bestellvorgang.php?editZahlungsart=1");
                    exit;
                    break;
                }

                default: {
                    if ($oPreAuth->has_error()) {
                        $this->assignMessage($oPreAuth->get_customer_error_message(), 'error');
                        $this->logEx($oPreAuth->get_merchant_error_message(), $oCustomer);
                    }
                    break;
                }
            }
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }

        return 0;
    }

    /**
     * @param $kBestellung
     * @param $kSprache
     * @return object|stdClass
     */
    public function createInvoice($kBestellung, $kSprache)
    {
        $oInv        = new stdClass();
        $oInv->nType = 0;
        $oInv->cInfo = '';

        $oOrder = new Bestellung($kBestellung);
        $oOrder->fuelleBestellung(0);

        $fAmount = $oOrder->fGesamtsumme;
        if ($oOrder->Zahlungsart->cModulId === 'za_billpay_rate_payment_jtl' || $oOrder->Zahlungsart->cModulId === 'za_billpay_paylater_jtl') {
            $oBasket                = new Warenkorb($oOrder->kWarenkorb);
            $oBasket->Waehrung      = $oOrder->Waehrung;
            $oBasket->PositionenArr = $oOrder->Positionen;
            $deliveryCountry        = 'DE';
            if (isset($oOrder->Lieferadresse, $oOrder->Lieferadresse->cLand)) {
                $deliveryCountry = $oOrder->Lieferadresse->cLand;
            } elseif (isset($oOrder->oRechnungsadresse, $oOrder->oRechnungsadresse->cLand)) {
                $deliveryCountry = $oOrder->oRechnungsadresse->cLand;
            }
            setzeSteuersaetze($deliveryCountry);
            $fAmount = $oBasket->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_ZINSAUFSCHLAG, C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR], true) * $oBasket->Waehrung->fFaktor;
        }

        if ($oOrder) {
            $nDelayInDays = (int)$this->getCoreSetting('delayindays');
            $nDelayInDays = ($nDelayInDays >= 0 && $nDelayInDays <= BILLPAY_MAX_DELAY_IN_DAYS)
                ? $nDelayInDays
                : BILLPAY_MAX_DELAY_IN_DAYS;

            $oInvoice = $this->getApi('invoice_created');
            $oInvoice->set_invoice_params(

                BPHelper::fmtAmount($fAmount, true),
                //BPHelper::strEncode('EUR'),
                BPHelper::strEncode(strtoupper($oOrder->Waehrung->cISO)),
                BPHelper::strEncode($oOrder->cBestellNr),
                $nDelayInDays
            );
            try {
                $oInvoice->send();
                $this->log("send invoice create", LOGLEVEL_DEBUG);

                if (!$oInvoice->has_error()) {
                    $oInv->nType = 1;
                    switch ($oOrder->Zahlungsart->cModulId) {
                        case 'za_billpay_jtl':
                        case 'za_billpay_invoice_jtl': {
                            $cInvoiceDueDate = BPHelper::fmtDate(BPHelper::strDecode($oInvoice->get_invoice_duedate()));
                            $oInv->cInfo     = "Bitte &uuml;berweisen Sie den Gesamtbetrag bis zum " . $cInvoiceDueDate . " auf folgendes Konto:\r\n";
                            $oInv->cInfo .= "Kontoinhaber: " . BPHelper::strDecode($oInvoice->get_account_holder()) . "\r\n";
                            $oInv->cInfo .= "IBAN: " . BPHelper::strDecode($oInvoice->get_account_number()) . "\r\n";
                            $oInv->cInfo .= "BIC: " . BPHelper::strDecode($oInvoice->get_bank_code()) . "\r\n";
                            $oInv->cInfo .= "Geldinstitut: " . BPHelper::strDecode($oInvoice->get_bank_name()) . "\r\n";
                            $oInv->cInfo .= "Verwendungszweck: " . BPHelper::strDecode($oInvoice->get_invoice_reference()) . "\r\n";
                            $oInv->cInfo .= "F&auml;lligkeit: " . $cInvoiceDueDate;
                            break;
                        }

                        case 'za_billpay_direct_debit_jtl': {
                            $oInv->cInfo = "Vielen Dank, dass Sie sich f&uuml;r die Zahlung per Lastschrift mit Billpay entschieden haben.\r\n";
                            $oInv->cInfo .= "Wir buchen den f&auml;lligen Betrag in den n&auml;chsten Tagen von dem bei der Bestellung angegebenen Konto ab.";
                            break;
                        }

                        case 'za_billpay_rate_payment_jtl': {
                            $oInv->cInfo = "Vielen Dank, dass Sie sich f&uuml;r die Zahlart Ratenkauf entschieden haben.\r\n";
                            $oInv->cInfo .= "Die f&auml;lligen Betr&auml;ge werden monatlich von dem bei der Bestellung angegebenen Konto abgebucht.\r\n\r\n";

                            foreach ($oInvoice->get_dues() as $i => $aDue) {
                                $oInv->cInfo .= ($i + 1) . ". Rate: " . BPHelper::fmtAmountX($aDue['value'], true, false) . " (f&auml;llig am " . BPHelper::fmtDate(BPHelper::strDecode($aDue['date'])) . ")\r\n";
                            }

                            break;
                        }

                        case 'za_billpay_paylater_jtl': {
                            $oInv->cInfo = "Sie haben sich f&uuml;r die Zahlungsweise PayLater entschieden.\r\n";
                            $oInv->cInfo .= "Bitte beachten Sie, dass zus&auml;tzlich zu dem auf dieser Rechnung genannten Rechnungsbetrag weitere Kosten im Zusammenhang mit dem Teilzahlungsgesch&auml;ft entstehen.\r\n";
                            $oInv->cInfo .= "Diese Kosten wurden Ihnen vor Abschluss der Bestellung und auf der Bestellbest&auml;tigung angezeigt.\r\n";
                            $oInv->cInfo .= "Die vollst&auml;ndige Berechnung der zu leistenden Betr&auml;ge im Zusammenhang mit dem Teilzahlungsgesch&auml;ft, sowie s&auml;mtliche dazugeh&ouml;rige Informationen haben Sie direkt per E-Mail von der BillPay GmbH erhalten.";
                            break;
                        }

                        default: {
                            $oInv->nType = 0;
                            $oInv->cInfo = "Bestellung ist ung&uuml;ltig";
                            break;
                        }
                    }
                } else {
                    $oInv->nType = 0;
                    $oInv->cInfo = BPHelper::strDecode($oInvoice->get_merchant_error_message());
                    $this->log($oInvoice->get_merchant_error_message());
                }
            } catch (Exception $e) {
                $oInv->cInfo = $e->getMessage();
                $this->log($e->getMessage());
            }
        }

        return $oInv;
    }

    /**
     * @param int  $kBestellung
     * @param bool $bDelete
     * @return bool
     */
    public function cancelOrder($kBestellung, $bDelete = false)
    {
        parent::cancelOrder($kBestellung, $bDelete);
        $oOrder = new Bestellung($kBestellung);
        $oOrder->fuelleBestellung(0);
        $fAmount = $oOrder->fGesamtsumme;
        if ($oOrder->Zahlungsart->cModulId === 'za_billpay_rate_payment_jtl' || $oOrder->Zahlungsart->cModulId === 'za_billpay_paylater_jtl') {
            $oBasket                = new Warenkorb($oOrder->kWarenkorb);
            $oBasket->Waehrung      = $oOrder->Waehrung;
            $oBasket->PositionenArr = $oOrder->Positionen;
            $deliveryCountry        = 'DE';
            if (isset($oOrder->Lieferadresse, $oOrder->Lieferadresse->cLand)) {
                $deliveryCountry = $oOrder->Lieferadresse->cLand;
            } elseif (isset($oOrder->oRechnungsadresse, $oOrder->oRechnungsadresse->cLand)) {
                $deliveryCountry = $oOrder->oRechnungsadresse->cLand;
            }
            setzeSteuersaetze($deliveryCountry);
            $fAmount = $oBasket->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_ZINSAUFSCHLAG, C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR], true) * $oBasket->Waehrung->fFaktor;
        }
        if ($oOrder) {
            $oCancel = $this->getApi('cancel');
            $oCancel->set_cancel_params(
                BPHelper::strEncode($oOrder->cBestellNr),
                BPHelper::fmtAmount($fAmount, true),
                BPHelper::strEncode(strtoupper($oOrder->Waehrung->cISO))
            );
            try {
                $oCancel->send();
                $this->log("send cancel request, order number: " . $oOrder->cBestellNr . ", amount: " . $oOrder->fGesamtsumme, LOGLEVEL_DEBUG);
                if ($oCancel->has_error()) {
                    $this->log($oCancel->get_merchant_error_message());
                } else {
                    return true;
                }
            } catch (Exception $e) {
                $this->log($e->getMessage());
            }
        } else {
            // order not found
            $this->log("Order " . $kBestellung . " not found");
        }

        return false;
    }

    /**
     * @param $oBasket
     * @return stdClass
     */
    public function getBasketTotal($oBasket)
    {
        $oBasketInfo             = new stdClass();
        $oBasketInfo->fArticle   = [0, 0];// artikel
        $oBasketInfo->fShipping  = [0, 0];// versand
        $oBasketInfo->fRebate    = [0, 0];// rabatt
        $oBasketInfo->fSurcharge = [0, 0];// zuschlag
        $oBasketInfo->fTotal     = [0, 0];// warenkorb

        $cCurrency = isset($_SESSION['Waehrung']) ? $_SESSION['Waehrung'] : null;
        if ($cCurrency === null || !isset($cCurrency->kWaehrung)) {
            $cCurrency = $oBasket->Waehrung;
        }
        if ($cCurrency === null || !isset($cCurrency->kWaehrung)) {
            $cCurrency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        }

        $oBasketInfo->cCurrency = $cCurrency;

        foreach ($oBasket->PositionenArr as $oPosition) {
            $fPreisEinzelNetto = $oPosition->fPreisEinzelNetto;
            if (isset($oPosition->WarenkorbPosEigenschaftArr) && is_array($oPosition->WarenkorbPosEigenschaftArr) &&
                (!isset($oPosition->Artikel->kVaterArtikel) || (int)$oPosition->Artikel->kVaterArtikel === 0)
            ) {
                foreach ($oPosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->fAufpreis > 0) {
                        $fPreisEinzelNetto += $oWarenkorbPosEigenschaft->fAufpreis;
                    }
                }
            }
            $fAmount      = $fPreisEinzelNetto * $oBasketInfo->cCurrency->fFaktor;
            $fAmountGross = $fAmount * ((100 + gibUst($oPosition->kSteuerklasse)) / 100);

            switch ($oPosition->nPosTyp) {
                /*case C_WARENKORBPOS_TYP_GRATISGESCHENK:*/
                case C_WARENKORBPOS_TYP_ARTIKEL: {
                    $oBasketInfo->fArticle[AMT_NET] += $fAmount * $oPosition->nAnzahl;
                    $oBasketInfo->fArticle[AMT_GROSS] += $fAmountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_VERSANDPOS:
                case C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case C_WARENKORBPOS_TYP_VERPACKUNG:
                case C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG: {
                    $oBasketInfo->fShipping[AMT_NET] += $fAmount * $oPosition->nAnzahl;
                    $oBasketInfo->fShipping[AMT_GROSS] += $fAmountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_KUPON:
                case C_WARENKORBPOS_TYP_GUTSCHEIN:
                case C_WARENKORBPOS_TYP_NEUKUNDENKUPON: {
                    $oBasketInfo->fRebate[AMT_NET] += $fAmount * $oPosition->nAnzahl;
                    $oBasketInfo->fRebate[AMT_GROSS] += $fAmountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_ZAHLUNGSART:
                case C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR: {
                    $oBasketInfo->fSurcharge[AMT_NET] += $fAmount * $oPosition->nAnzahl;
                    $oBasketInfo->fSurcharge[AMT_GROSS] += $fAmountGross * $oPosition->nAnzahl;
                    break;
                }
            }
        }
        // rabate fix (only positive amounts)
        $oBasketInfo->fRebate[AMT_NET] *= -1;
        $oBasketInfo->fRebate[AMT_GROSS] *= -1;
        // total
        $oBasketInfo->fTotal[AMT_NET] = $oBasketInfo->fArticle[AMT_NET] + $oBasketInfo->fShipping[AMT_NET] -
            $oBasketInfo->fRebate[AMT_NET] + $oBasketInfo->fSurcharge[AMT_NET];

        $oBasketInfo->fTotal[AMT_GROSS] = $oBasketInfo->fArticle[AMT_GROSS] + $oBasketInfo->fShipping[AMT_GROSS] -
            $oBasketInfo->fRebate[AMT_GROSS] + $oBasketInfo->fSurcharge[AMT_GROSS];

        return $oBasketInfo;
    }

    /**
     * @param $oBasket
     * @return bool|stdClass
     */
    public function calculateRates($oBasket)
    {
        $oRateInfo                 = new stdClass();
        $oRateInfo->aRates_arr     = [];
        $oRateInfo->nAvailable_arr = [];
        $oBasketInfo               = $this->getBasketTotal($oBasket);
        // load from cache if exists
        $oRateInfo = $this->getCachedRate($oBasketInfo);
        if ($oRateInfo) {
            return $oRateInfo;
        }

        $fAmountBaseGross = $oBasketInfo->fArticle[AMT_GROSS] - $oBasketInfo->fRebate[AMT_GROSS];

        $oRates = $this->getApi('calculate_rates');
        $oRates->set_rate_request_params(
            BPHelper::fmtAmount($fAmountBaseGross, true), // baseamount
            BPHelper::fmtAmount($oBasketInfo->fTotal[AMT_GROSS], true)
        );   // carttotalgross
        try {
            $oRates->send();
            if (!$oRates->has_error()) {
                $aRates_arr = $oRates->get_options();
                foreach ($aRates_arr as $nRate => $aRates) {
                    $oRateInfo->aRates_arr[$nRate]        = new stdClass();
                    $oRateInfo->aRates_arr[$nRate]->nRate = $nRate;
                    // plain
                    $oRateInfo->aRates_arr[$nRate]->fBase      = BPHelper::fmtAmountX($aRates['calculation']['base']);
                    $oRateInfo->aRates_arr[$nRate]->fCart      = BPHelper::fmtAmountX($aRates['calculation']['cart']);
                    $oRateInfo->aRates_arr[$nRate]->fSurcharge = BPHelper::fmtAmountX($aRates['calculation']['surcharge']);
                    $oRateInfo->aRates_arr[$nRate]->fTotal     = BPHelper::fmtAmountX($aRates['calculation']['total']);
                    $oRateInfo->aRates_arr[$nRate]->fInterest  = BPHelper::fmtAmountX($aRates['calculation']['interest']);
                    $oRateInfo->aRates_arr[$nRate]->fAnual     = BPHelper::fmtAmountX($aRates['calculation']['anual']);
                    $oRateInfo->aRates_arr[$nRate]->fFee       = BPHelper::fmtAmountX($aRates['calculation']['fee']);
                    // format
                    $oRateInfo->aRates_arr[$nRate]->fBaseFmt      = BPHelper::fmtAmountX($aRates['calculation']['base'], true);
                    $oRateInfo->aRates_arr[$nRate]->fCartFmt      = BPHelper::fmtAmountX($aRates['calculation']['cart'], true);
                    $oRateInfo->aRates_arr[$nRate]->fSurchargeFmt = BPHelper::fmtAmountX($aRates['calculation']['surcharge'], true);
                    $oRateInfo->aRates_arr[$nRate]->fTotalFmt     = BPHelper::fmtAmountX($aRates['calculation']['total'], true);
                    $oRateInfo->aRates_arr[$nRate]->fAnualFmt     = BPHelper::fmtAmountX($aRates['calculation']['anual'], true);
                    $oRateInfo->aRates_arr[$nRate]->fFeeFmt       = BPHelper::fmtAmountX($aRates['calculation']['fee'], true);
                    // custom
                    $oRateInfo->aRates_arr[$nRate]->fOtherSurcharge    = BPHelper::fmtAmount($oBasketInfo->fSurcharge[AMT_GROSS] + $oBasketInfo->fShipping[AMT_GROSS]);
                    $oRateInfo->aRates_arr[$nRate]->fOtherSurchargeFmt = BPHelper::fmtAmount($oBasketInfo->fSurcharge[AMT_GROSS] + $oBasketInfo->fShipping[AMT_GROSS], false, true);

                    $oRateInfo->nAvailable_arr[]              = $nRate;
                    $oRateInfo->aRates_arr[$nRate]->oDues_arr = [];
                    foreach ($aRates['dues'] as $i => $cDue_arr) {
                        $oRateInfo->aRates_arr[$nRate]->oDues_arr[$i]             = new stdClass();
                        $oRateInfo->aRates_arr[$nRate]->oDues_arr[$i]->cType      = BPHelper::strDecode($cDue_arr['type']);
                        $oRateInfo->aRates_arr[$nRate]->oDues_arr[$i]->cDate      = BPHelper::strDecode($cDue_arr['date']);
                        $oRateInfo->aRates_arr[$nRate]->oDues_arr[$i]->fAmount    = BPHelper::fmtAmountX($cDue_arr['value']);
                        $oRateInfo->aRates_arr[$nRate]->oDues_arr[$i]->fAmountFmt = BPHelper::fmtAmountX($cDue_arr['value'], true);
                    }
                }
                // cache rate
                $oRateInfo->bFromCache  = false;
                $oRateInfo->oBasketInfo = $oBasketInfo;
                $this->cacheRate($oRateInfo);
            } else {
                $this->log($oRates->get_merchant_error_message());
            }
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }

        return $oRateInfo;
    }

    /**
     * rate caching
     *
     * @param $oBasketInfo
     * @return bool
     */
    public function getCachedRate($oBasketInfo)
    {
        $cHash = $this->getRateHash($oBasketInfo);
        if (isset($_SESSION['za_billpay_jtl']['oCashedRates_arr'], $_SESSION['za_billpay_jtl']['oCashedRates_arr'][$cHash]) &&
            is_array($_SESSION['za_billpay_jtl']['oCashedRates_arr'])
        ) {
            $_SESSION['za_billpay_jtl']['oCashedRates_arr'][$cHash]->bFromCache = true;

            return $_SESSION['za_billpay_jtl']['oCashedRates_arr'][$cHash];
        }

        return false;
    }

    /**
     * @param $oRateInfo
     */
    public function cacheRate($oRateInfo)
    {
        $cHash = $this->getRateHash($oRateInfo->oBasketInfo);
        if (!isset($_SESSION['za_billpay_jtl']['oCashedRates_arr'])) {
            $_SESSION['za_billpay_jtl']['oCashedRates_arr'] = [];
        }
        $_SESSION['za_billpay_jtl']['oCashedRates_arr'][$cHash] = $oRateInfo;
    }

    /**
     * @param $oBasketInfo
     * @return string
     */
    public function getRateHash($oBasketInfo)
    {
        return md5(
            $oBasketInfo->fArticle[AMT_GROSS] . $oBasketInfo->fShipping[AMT_GROSS] .
            $oBasketInfo->fRebate[AMT_GROSS] . $oBasketInfo->fSurcharge[AMT_GROSS]
        );
    }

    /**
     * @param        $cName
     * @param        $fQuantity
     * @param        $fAmount
     * @param        $nType
     * @param        $bDelSamePosType
     * @param bool   $bGross
     * @param string $cNotice
     */
    public function addSpecialPosition($cName, $fQuantity, $fAmount, $nType, $bDelSamePosType, $bGross = true, $cNotice = "")
    {
        $kSteuerklasse = $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse('');
        $_SESSION['Warenkorb']->erstelleSpezialPos($cName, $fQuantity, $fAmount, $kSteuerklasse, $nType, $bDelSamePosType, $bGross, $cNotice);
    }

    /**
     * @param      $kBestellung
     * @param      $nType
     * @param null $oAdditional
     * @return $this
     */
    public function sendMail($kBestellung, $nType, $oAdditional = null)
    {
        if ($nType != MAILTEMPLATE_BESTELLUNG_AKTUALISIERT) {
            parent::sendMail($kBestellung, $nType, $oAdditional);
        }

        return $this;
    }

    /**
     * @param        $cCustomerMessage
     * @param string $cType
     */
    public function assignMessage($cCustomerMessage, $cType = 'info')
    {
        $oMessage                   = new stdClass();
        $oMessage->cType            = $cType;
        $oMessage->cCustomerMessage = BPHelper::strDecode($cCustomerMessage);
        Shop::Smarty()->assign('billpay_message', $oMessage);
    }

    /**
     * @param string $key
     * @param bool   $root
     * @return string|null
     */
    public function getCoreSetting($key, $root = false)
    {
        global $Einstellungen;
        if (!is_array($Einstellungen)) {
            $Einstellungen = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
        }

        return isset($Einstellungen['zahlungsarten']['zahlungsart_billpay_' . $key])
            ? $Einstellungen['zahlungsarten']['zahlungsart_billpay_' . $key]
            : null;
    }

    /**
     * @param string $cMessage
     * @param int    $nLevel
     */
    public function log($cMessage, $nLevel = LOGLEVEL_ERROR)
    {
        $this->logEx($cMessage, null, $nLevel);
    }

    /**
     * @param string $cMessage
     * @param string $oObject
     * @param int $nLevel
     */
    public function logEx($cMessage, $oObject, $nLevel = LOGLEVEL_ERROR)
    {
        $cMessage = BPHelper::strDecode($cMessage);
        if ($oObject) {
            $oObject = serialize($oObject);
        }
        ZahlungsLog::add($this->moduleID, $cMessage, $oObject, $nLevel);
    }
}

/**
 * Class BPHelper
 */
class BPHelper
{
    /**
     * @param $cFile
     * @param $cOrderNumber
     * @param $cData
     * @return bool|stdClass
     */
    public static function savePDF($cFile, $cOrderNumber, $cData)
    {
        $cData = base64_decode($cData);
        if (strcasecmp(substr($cData, 0, 4), FILE_HEADER_PDF) === 0) {
            $oFile            = new stdClass();
            $oFile->cFile     = sprintf($cFile, $cOrderNumber);
            $oFile->cFilePath = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS . $oFile->cFile;
            if (file_put_contents($oFile->cFilePath, $cData) !== false) {
                return $oFile;
            }
        }

        return false;
    }

    /**
     * @param      $cDate
     * @param bool $bYmd
     * @param bool $bMis
     * @return bool|string
     */
    public static function fmtDate($cDate, $bYmd = false, $bMis = false)
    {
        if ($bYmd) {
            $nTime = strtotime($cDate);

            return date('Ymd' . ($bMis ? ' H:i:s' : ''), $nTime);
        } elseif (strlen($cDate) === 8) {
            $nYear  = substr($cDate, 0, 4);
            $nMonth = substr($cDate, 4, 2);
            $nDay   = substr($cDate, 6, 2);
            $nTime  = mktime(0, 0, 0, $nMonth, $nDay, $nYear);

            return date('d.m.Y', $nTime);
        }

        return false;
    }

    /**
     * @param      $fAmount
     * @param bool $bToSmallestCurrencyUnit
     * @param bool $bFmt
     * @return float|int|string
     */
    public static function fmtAmount($fAmount, $bToSmallestCurrencyUnit = false, $bFmt = false)
    {
        $cAmount = round($fAmount, 2);
        if ($bToSmallestCurrencyUnit) {
            $cAmount *= 100;
        }
        if ($bFmt) {
            $cAmount = gibPreisStringLocalized($cAmount, $_SESSION['Waehrung']->cISO, true, 2);
        }

        return $cAmount;
    }

    /**
     * @param      $fAmount
     * @param bool $bFmt
     * @param bool $bHTML
     * @return string
     */
    public static function fmtAmountX($fAmount, $bFmt = false, $bHTML = true)
    {
        $fAmount = round($fAmount, 2);
        $cAmount = number_format($fAmount / 100, 2, '.', '');
        if ($bFmt) {
            $cAmount = gibPreisStringLocalized($cAmount, $_SESSION['Waehrung']->cISO, $bHTML, 2);
        }

        return $cAmount;
    }

    /**
     * @param     $cStr
     * @param int $nMaxLength
     * @return string
     */
    public static function strEncode($cStr, $nMaxLength = 0)
    {
        if ($nMaxLength > 0) {
            $cStr = substr($cStr, 0, $nMaxLength);
        }

        return utf8_encode($cStr);
    }

    /**
     * @param $cStr
     * @return string
     */
    public static function strDecode($cStr)
    {
        return utf8_decode($cStr);
    }

    /**
     * @param $cStr
     * @return string
     */
    public static function toISO6391($cStr)
    {
        $cStr = strtolower(StringHandler::convertISO2ISO639($cStr));
        if (!strlen($cStr)) {
            $cStr = 'de';
        }

        return $cStr;
    }

    /**
     * @param $cStr
     * @return string
     */
    public static function toISO6392($cStr)
    {
        $cStr = strtolower(StringHandler::convertISO6392ISO($cStr));
        if (!strlen($cStr)) {
            $cStr = 'deu';
        }

        return $cStr;
    }

    /**
     * @param string $cStr
     * @param bool  $from
     * @return string
     */
    public static function mapSalutation($cStr, $from = false)
    {
        if ($from) {
            if ($cStr === 'herr') {
                return 'm';
            }

            return 'w';
        } else {
            if ($cStr === 'm' || $cStr !== 'w') {
                return 'herr';
            }

            return 'frau';
        }
    }

    /**
     * @return string
     */
    public static function getLanguage()
    {
        $cISOSprache = '';
        if (strlen($_SESSION['cISOSprache']) > 0) {
            $cISOSprache = $_SESSION['cISOSprache'];
        } else {
            $oSprache = Shop::DB()->query("SELECT kSprache, cISO FROM tsprache WHERE cShopStandard = 'Y'", 1);
            if ($oSprache->kSprache > 0) {
                $cISOSprache = $oSprache->cISO;
            }
        }

        return $cISOSprache;
    }

    /**
     * https://techdocs.billpay.de/en/For_decision_makers/Possible_Country_and_Payment_Method_Combinations.html
     *
     * @param int    $nPaymentType
     * @param string $cISO
     * @return bool
     */
    public static function isValidCountry($nPaymentType, $cISO)
    {
        $cISO = self::mapCountryCode($cISO);

        $aValidCombinations = [
            IPL_CORE_PAYMENT_TYPE_INVOICE              => ['DEU', 'AUT', 'CHE', 'NLD'],
            IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT         => ['DEU', 'AUT'],
            IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT         => ['CHE'],
            IPL_CORE_PAYMENT_TYPE_PAY_LATER            => ['DEU', 'AUT'],
            IPL_CORE_PAYMENT_TYPE_PAY_LATER_COLLATERAL => []
        ];

        if (!array_key_exists($nPaymentType, $aValidCombinations)) {
            return false;
        }

        return in_array($cISO, $aValidCombinations[$nPaymentType], true);
    }

    /**
     * @param $cISO
     * @return string
     */
    public static function mapCountryCode($cISO)
    {
        switch (strtoupper($cISO)) {
            case 'AT':
                $cISO = 'AUT';
                break;

            case 'DE':
                $cISO = 'DEU';
                break;

            case 'CH':
                $cISO = 'CHE';
                break;

            case 'NL':
                $cISO = 'NLD';
                break;

            default:
                $cISO = null;
                break;
        }

        return $cISO;
    }

    /**
     * @return array
     */
    public static function getTermUrls()
    {
        return [
            'DEU' => 'https://www.billpay.de/api/agb',
            'CHE' => 'https://www.billpay.de/api/agb-ch',
            'AUT' => 'https://www.billpay.de/api/agb-at'
        ];
    }

    /**
     * @param int $nType
     * @return string|null
     */
    public static function getPaymentType($nType)
    {
        switch ($nType) {
            case IPL_CORE_PAYMENT_TYPE_INVOICE:
                return 'invoice';
            case IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT:
                return 'directDebit';
            case IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT:
                return 'transactionCredit';
            case IPL_CORE_PAYMENT_TYPE_PAY_LATER:
                return 'paylater';
            case IPL_CORE_PAYMENT_TYPE_PAY_LATER_COLLATERAL:
                return 'paylaterCollateralPromise';
        }

        return null;
    }

    /**
     * @param $oCustomer
     * @param $oShipAddr
     */
    public static function removeHTML(&$oCustomer, &$oShipAddr)
    {
        $oCustomer->cAnrede       = StringHandler::unhtmlentities($oCustomer->cAnrede);
        $oCustomer->cVorname      = StringHandler::unhtmlentities($oCustomer->cVorname);
        $oCustomer->cNachname     = StringHandler::unhtmlentities($oCustomer->cNachname);
        $oCustomer->cStrasse      = StringHandler::unhtmlentities($oCustomer->cStrasse);
        $oCustomer->cPLZ          = StringHandler::unhtmlentities($oCustomer->cPLZ);
        $oCustomer->cOrt          = StringHandler::unhtmlentities($oCustomer->cOrt);
        $oCustomer->cLand         = StringHandler::unhtmlentities($oCustomer->cLand);
        $oCustomer->cMail         = StringHandler::unhtmlentities($oCustomer->cMail);
        $oCustomer->cTel          = StringHandler::unhtmlentities($oCustomer->cTel);
        $oCustomer->cFax          = StringHandler::unhtmlentities($oCustomer->cFax);
        $oCustomer->cFirma        = StringHandler::unhtmlentities($oCustomer->cFirma);
        $oCustomer->cTitel        = StringHandler::unhtmlentities($oCustomer->cTitel);
        $oCustomer->cAdressZusatz = StringHandler::unhtmlentities($oCustomer->cAdressZusatz);
        $oCustomer->cMobil        = StringHandler::unhtmlentities($oCustomer->cMobil);
        $oCustomer->cWWW          = StringHandler::unhtmlentities($oCustomer->cWWW);
        $oCustomer->cUSTID        = StringHandler::unhtmlentities($oCustomer->cUSTID);
        $oCustomer->dGeburtstag   = StringHandler::unhtmlentities($oCustomer->dGeburtstag);
        $oCustomer->cBundesland   = StringHandler::unhtmlentities($oCustomer->cBundesland);
        $oShipAddr->cVorname      = StringHandler::unhtmlentities($oShipAddr->cVorname);
        $oShipAddr->cNachname     = StringHandler::unhtmlentities($oShipAddr->cNachname);
        $oShipAddr->cFirma        = StringHandler::unhtmlentities($oShipAddr->cFirma);
        $oShipAddr->cStrasse      = StringHandler::unhtmlentities($oShipAddr->cStrasse);
        $oShipAddr->cPLZ          = StringHandler::unhtmlentities($oShipAddr->cPLZ);
        $oShipAddr->cOrt          = StringHandler::unhtmlentities($oShipAddr->cOrt);
        $oShipAddr->cLand         = StringHandler::unhtmlentities($oShipAddr->cLand);
        $oShipAddr->cAnrede       = StringHandler::unhtmlentities($oShipAddr->cAnrede);
        $oShipAddr->cMail         = StringHandler::unhtmlentities($oShipAddr->cMail);
        $oShipAddr->cBundesland   = StringHandler::unhtmlentities($oShipAddr->cBundesland);
        $oShipAddr->cTel          = StringHandler::unhtmlentities($oShipAddr->cTel);
        $oShipAddr->cFax          = StringHandler::unhtmlentities($oShipAddr->cFax);
        $oShipAddr->cTitel        = StringHandler::unhtmlentities($oShipAddr->cTitel);
        $oShipAddr->cAdressZusatz = StringHandler::unhtmlentities($oShipAddr->cAdressZusatz);
        $oShipAddr->cMobil        = StringHandler::unhtmlentities($oShipAddr->cMobil);
    }
}
