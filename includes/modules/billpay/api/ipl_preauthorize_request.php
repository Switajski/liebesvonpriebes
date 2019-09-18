<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial
 */
class ipl_preauthorize_request extends ipl_xml_request
{
    private $_customer_details  = [];
    private $_shippping_details = [];
    private $_totals            = [];
    private $_bank_account      = [];
    private $_rate_request_data = [];

    private $_article_data       = [];
    private $_order_history_attr = [];
    private $_order_history_data = [];
    private $_company_details    = [];

    private $_payment_info_params = [];
    private $_fraud_detection     = [];

    private $_preauth_params       = [];
    private $_async_capture_params = [];

    private $_payment_type;

    private $bptid;

    private $corrected_street;
    private $corrected_street_no;
    private $corrected_zip;
    private $corrected_city;
    private $corrected_country;

    // parameters needed for auto-capture
    private $account_holder;
    private $account_number;
    private $bank_code;
    private $bank_name;
    private $invoice_reference;
    private $invoice_duedate;
    private $activation_performed;

    private $_terms_accepted              = false;
    private $_capture_request_necessary   = true;
    private $_expected_days_till_shipping = 0;

    private $standard_information_pdf;
    private $email_attachment_pdf;

    private $payment_info_html;
    private $payment_info_plain;

    // rate payment specific
    private $instalment_count;
    private $duration;
    private $fee_percent;
    private $fee_total;
    private $total_amount;
    private $effective_annual;
    private $nominal_annual;
    private $base_amount;
    private $cart_amount;
    private $surcharge;
    private $interest;
    private $dues = [];

    // pre approved specific
    private $async_amount;
    private $rate_plan_url;
    private $external_redirect_url;
    private $campaign_type;
    private $campaign_display_text;
    private $campaign_display_image_url;

    // parameters needed for prescore
    private $is_prescored = 0;

    /**
     * ipl_preauthorize_request constructor.
     * @param $ipl_request_url
     * @param $payment_type
     */
    public function __construct($ipl_request_url, $payment_type)
    {
        $this->_payment_type = $payment_type;
        parent::__construct($ipl_request_url);
    }

    /**
     * @param $sShopType
     * @return $this
     */
    public function setTraceShopType($sShopType)
    {
        $this->aTraceData['shop_type'] = $sShopType;

        return $this;
    }

    /**
     * @param $sVersion
     * @return $this
     */
    public function setTraceShopVersion($sVersion)
    {
        $this->aTraceData['shop_version'] = $sVersion;

        return $this;
    }

    /**
     * @param $sShopDomain
     * @return $this
     */
    public function setTraceShopDomain($sShopDomain)
    {
        $this->aTraceData['shop_domain'] = $sShopDomain;

        return $this;
    }

    /**
     * @param $sVersion
     * @return $this
     */
    public function setTracePluginVersion($sVersion)
    {
        $this->aTraceData['plugin_version'] = $sVersion;

        return $this;
    }

    /**
     * @return array
     */
    protected function getTraceData()
    {
        $aTraceData = parent::getTraceData();

        if (isset($aTraceData['shop_domain']) === false) {
            $aTraceData['shop_domain'] = $_SERVER['SERVER_NAME'];
        }

        $aTraceData['php_version'] = PHP_VERSION;
        $aTraceData['os_version']  = @php_uname('a');
        $aTraceData['api_version'] = IPL_CORE_API_VERSION;

        ksort($aTraceData);

        return $aTraceData;
    }

    /**
     * @return bool
     */
    public function get_terms_accepted()
    {
        return $this->_terms_accepted;
    }

    /**
     * @param $val
     */
    public function set_terms_accepted($val)
    {
        $this->_terms_accepted = $val;
    }

    /**
     * @param $val
     */
    public function set_expected_days_till_shipping($val)
    {
        $this->_expected_days_till_shipping = $val;
    }

    /**
     * @param $val
     */
    public function set_capture_request_necessary($val)
    {
        $this->_capture_request_necessary = $val;
    }

    /**
     * @return int
     */
    public function get_expected_days_till_shipping()
    {
        return $this->_expected_days_till_shipping;
    }

    /**
     * @return bool
     */
    public function get_capture_request_nesessary()
    {
        return $this->_capture_request_necessary;
    }

    /**
     * @return mixed
     */
    public function get_payment_type()
    {
        return $this->_payment_type;
    }

    /**
     * @return mixed
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function get_bptid()
    {
        return $this->bptid;
    }

    /**
     * @return mixed
     */
    public function get_corrected_street()
    {
        return $this->corrected_street;
    }

    /**
     * @return mixed
     */
    public function get_corrected_street_no()
    {
        return $this->corrected_street_no;
    }

    /**
     * @return mixed
     */
    public function get_corrected_zip()
    {
        return $this->corrected_zip;
    }

    /**
     * @return mixed
     */
    public function get_corrected_city()
    {
        return $this->corrected_city;
    }

    /**
     * @return mixed
     */
    public function get_corrected_country()
    {
        return $this->corrected_country;
    }

    /**
     * @return mixed
     */
    public function get_account_holder()
    {
        return $this->account_holder;
    }

    /**
     * @return mixed
     */
    public function get_account_number()
    {
        return $this->account_number;
    }

    /**
     * @return mixed
     */
    public function get_bank_code()
    {
        return $this->bank_code;
    }

    /**
     * @return mixed
     */
    public function get_bank_name()
    {
        return $this->bank_name;
    }

    /**
     * @return mixed
     */
    public function get_invoice_reference()
    {
        return $this->invoice_reference;
    }

    /**
     * @return mixed
     */
    public function get_invoice_duedate()
    {
        return $this->invoice_duedate;
    }

    /**
     * @return mixed
     */
    public function get_activation_performed()
    {
        return $this->activation_performed;
    }

    /**
     * @return mixed
     */
    public function get_standard_information_pdf()
    {
        return $this->standard_information_pdf;
    }

    /**
     * @return mixed
     */
    public function get_email_attachment_pdf()
    {
        return $this->email_attachment_pdf;
    }

    /**
     * @return mixed
     */
    public function get_payment_info_html()
    {
        return $this->payment_info_html;
    }

    /**
     * @return mixed
     */
    public function get_payment_info_plain()
    {
        return $this->payment_info_plain;
    }

    /**
     * @return mixed
     */
    public function get_async_amount()
    {
        return $this->async_amount;
    }

    /**
     * @return mixed
     */
    public function get_prepayment_amount()
    {
        return $this->async_amount;
    }

    /**
     * @return mixed
     */
    public function get_external_redirect_url()
    {
        return $this->external_redirect_url;
    }

    /**
     * @return mixed
     */
    public function get_rate_plan_url()
    {
        return $this->rate_plan_url;
    }

    /**
     * @return mixed
     */
    public function get_campaign_type()
    {
        return $this->campaign_type;
    }

    /**
     * @return mixed
     */
    public function get_campaign_display_text()
    {
        return $this->campaign_display_text;
    }

    /**
     * @return mixed
     */
    public function get_campaign_display_image_url()
    {
        return $this->campaign_display_image_url;
    }

    /**
     * @return mixed
     */
    public function get_instalment_count()
    {
        return $this->instalment_count;
    }

    /**
     * @return mixed
     */
    public function get_duration()
    {
        return $this->duration;
    }

    /**
     * @return mixed
     */
    public function get_fee_percent()
    {
        return $this->fee_percent;
    }

    /**
     * @return mixed
     */
    public function get_fee_total()
    {
        return $this->fee_total;
    }

    /**
     * @return mixed
     */
    public function get_total_amount()
    {
        return $this->total_amount;
    }

    /**
     * @return mixed
     */
    public function get_effective_annual()
    {
        return $this->effective_annual;
    }

    /**
     * @return mixed
     */
    public function get_nominal_annual()
    {
        return $this->nominal_annual;
    }

    /**
     * Returns base value of an order (base order + tax)
     * @return int
     */
    public function get_base_amount()
    {
        return (int) $this->base_amount;
    }

    /**
     * Returns cart value (base order + shipping fee + tax)
     * @return int
     */
    public function get_cart_amount()
    {
        return (int) $this->cart_amount;
    }

    /**
     * Returns interest surcharge (how much TC/PL costs)
     * @return int
     */
    public function get_surcharge()
    {
        return (int) $this->surcharge;
    }

    /**
     * Returns interest rate in 0.01 of percent
     * ie. 100 means 1% interest rate
     * @return int
     */
    public function get_interest()
    {
        return (int) $this->interest;
    }

    /**
     * @return array
     */
    public function get_dues()
    {
        return $this->dues;
    }

    /**
     * @param $customer_id
     * @param $customer_type
     * @param $salutation
     * @param $title
     * @param $first_name
     * @param $last_name
     * @param $street
     * @param $street_no
     * @param $address_addition
     * @param $zip
     * @param $city
     * @param $country
     * @param $email
     * @param $phone
     * @param $cell_phone
     * @param $birthday
     * @param $language
     * @param $ip
     * @param $customerGroup
     */
    public function set_customer_details($customer_id, $customer_type, $salutation, $title,
        $first_name, $last_name, $street, $street_no, $address_addition, $zip,
        $city, $country, $email, $phone, $cell_phone, $birthday, $language, $ip, $customerGroup)
    {
        $this->_customer_details['customerid']      = $customer_id;
        $this->_customer_details['customertype']    = $customer_type;
        $this->_customer_details['salutation']      = $salutation;
        $this->_customer_details['title']           = $title;
        $this->_customer_details['firstName']       = $first_name;
        $this->_customer_details['lastName']        = $last_name;
        $this->_customer_details['street']          = $street;
        $this->_customer_details['streetNo']        = $street_no;
        $this->_customer_details['addressAddition'] = $address_addition;
        $this->_customer_details['zip']             = $zip;
        $this->_customer_details['city']            = $city;
        $this->_customer_details['country']         = $country;
        $this->_customer_details['email']           = $email;
        $this->_customer_details['phone']           = $phone;
        $this->_customer_details['cellPhone']       = $cell_phone;
        $this->_customer_details['birthday']        = $birthday;
        $this->_customer_details['language']        = $language;
        $this->_customer_details['ip']              = $ip;
        $this->_customer_details['customerGroup']   = $customerGroup;
    }

    /**
     * @param      $use_billing_address
     * @param null $salutation
     * @param null $title
     * @param null $first_name
     * @param null $last_name
     * @param null $street
     * @param null $street_no
     * @param null $address_addition
     * @param null $zip
     * @param null $city
     * @param null $country
     * @param null $phone
     * @param null $cell_phone
     */
    public function set_shipping_details($use_billing_address, $salutation = null, $title = null, $first_name = null, $last_name = null,
        $street = null, $street_no = null, $address_addition = null, $zip = null, $city = null, $country = null, $phone = null, $cell_phone = null)
    {
        $this->_shippping_details['useBillingAddress'] = $use_billing_address ? '1' : '0';
        $this->_shippping_details['salutation']        = $salutation;
        $this->_shippping_details['title']             = $title;
        $this->_shippping_details['firstName']         = $first_name;
        $this->_shippping_details['lastName']          = $last_name;
        $this->_shippping_details['street']            = $street;
        $this->_shippping_details['streetNo']          = $street_no;
        $this->_shippping_details['addressAddition']   = $address_addition;
        $this->_shippping_details['zip']               = $zip;
        $this->_shippping_details['city']              = $city;
        $this->_shippping_details['country']           = $country;
        $this->_shippping_details['phone']             = $phone;
        $this->_shippping_details['cellPhone']         = $cell_phone;
    }

    /**
     * @param $articleid
     * @param $articlequantity
     * @param $articlename
     * @param $articledescription
     * @param $article_price
     * @param $article_price_gross
     */
    public function add_article($articleid, $articlequantity, $articlename, $articledescription,
        $article_price, $article_price_gross)
    {
        $article                       = [];
        $article['articleid']          = $articleid;
        $article['articlequantity']    = $articlequantity;
        $article['articlename']        = $articlename;
        $article['articledescription'] = $articledescription;
        $article['articleprice']       = $article_price;
        $article['articlepricegross']  = $article_price_gross;

        $this->_article_data[] = $article;
    }

    /**
     * @param $iMerchantCustomerLimit
     * @param $iRepeatCustomer
     * @return $this
     */
    public function add_order_history_attributes($iMerchantCustomerLimit, $iRepeatCustomer)
    {
        $this->_order_history_attr = [
            'merchant_customer_limit' => (int) $iMerchantCustomerLimit,
            'repeat_customer'         => (int) $iRepeatCustomer,
        ];

        return $this;
    }

    /**
     * @param $horderid
     * @param $hdate
     * @param $hamount
     * @param $hcurrency
     * @param $hpaymenttype
     * @param $hstatus
     */
    public function add_order_history($horderid, $hdate, $hamount, $hcurrency, $hpaymenttype, $hstatus)
    {
        $histOrder                 = [];
        $histOrder['horderid']     = $horderid;
        $histOrder['hdate']        = $hdate;
        $histOrder['hamount']      = $hamount;
        $histOrder['hcurrency']    = $hcurrency;
        $histOrder['hpaymenttype'] = $hpaymenttype;
        $histOrder['hstatus']      = $hstatus;

        $this->_order_history_data[] = $histOrder;
    }

    /**
     * @param        $rebate
     * @param        $rebate_gross
     * @param        $shipping_name
     * @param        $shipping_price
     * @param        $shipping_price_gross
     * @param        $cart_total_price
     * @param        $cart_total_price_gross
     * @param        $currency
     * @param        $reference
     * @param string $reference2
     */
    public function set_total($rebate, $rebate_gross, $shipping_name, $shipping_price,
            $shipping_price_gross, $cart_total_price, $cart_total_price_gross,
            $currency, $reference, $reference2 = "")
    {
        $this->_totals['shippingname']        = $shipping_name;
        $this->_totals['shippingprice']       = $shipping_price;
        $this->_totals['shippingpricegross']  = $shipping_price_gross;
        $this->_totals['rebate']              = $rebate;
        $this->_totals['rebategross']         = $rebate_gross;
        $this->_totals['carttotalprice']      = $cart_total_price;
        $this->_totals['carttotalpricegross'] = $cart_total_price_gross;
        $this->_totals['currency']            = $currency;
        $this->_totals['reference']           = $reference;
        $this->_totals['reference2']          = $reference2;
    }

    /**
     * @param $reference
     */
    public function set_reference($reference)
    {
        $this->_totals['reference'] = $reference;
    }

    /**
     * @param $account_holder
     * @param $account_number
     * @param $sort_code
     */
    public function set_bank_account($account_holder, $account_number, $sort_code)
    {
        $this->_bank_account['accountholder'] = $account_holder;
        $this->_bank_account['accountnumber'] = $account_number;
        $this->_bank_account['sortcode']      = $sort_code;
    }

    /**
     * @param $name
     * @param $legalForm
     * @param $registerNumber
     * @param $holderName
     * @param $taxNumber
     */
    public function set_company_details($name, $legalForm, $registerNumber, $holderName, $taxNumber)
    {
        $this->_company_details['name']           = $name;
        $this->_company_details['legalForm']      = $legalForm;
        $this->_company_details['registerNumber'] = $registerNumber;
        $this->_company_details['holderName']     = $holderName;
        $this->_company_details['taxNumber']      = $taxNumber;
    }

    /**
     * Sets rate info for TC and PL.
     *      Usually, term is the same as rate count, so it's not sent
     *      In case of big TC CHF order, rate count is always "four" and we need to set real term
     * @param int $rate_count
     * @param int $total_amount
     * @param int $term             (optional) Set, if different than $rate_count.
     */
    public function set_rate_request($rate_count, $total_amount, $term = 0)
    {
        $this->_rate_request_data['ratecount']   = $rate_count;
        $this->_rate_request_data['totalamount'] = $total_amount;
        if ($term) {
            $this->_rate_request_data['term'] = $term;
        }
    }

    /**
     * @param $showhtmlinfo
     * @param $showplaininfo
     */
    public function set_payment_info_params($showhtmlinfo, $showplaininfo)
    {
        $this->_payment_info_params['htmlinfo']  = $showhtmlinfo ? "1" : "0";
        $this->_payment_info_params['plaininfo'] = $showplaininfo ? "1" : "0";
    }

    /**
     * @param $session_id
     */
    public function set_fraud_detection($session_id)
    {
        $this->_fraud_detection['session_id'] = $session_id;
    }

    /**
     * @param $is_prescored
     * @param $bptid
     */
    public function set_prescore_enable($is_prescored, $bptid)
    {
        if ($is_prescored == true) {
            $this->is_prescored                    = 1;
            $this->bptid                           = $bptid;
            $this->_preauth_params['is_prescored'] = 1;
            $this->_preauth_params['bptid']        = $bptid;
        } else {
            $this->is_prescored                    = 0;
            $this->_preauth_params['is_prescored'] = 0;
        }
    }

    /**
     * @param $redirect_url
     * @param $notify_url
     */
    public function set_async_capture($redirect_url, $notify_url)
    {
        $this->_async_capture_params['redirect_url'] = $redirect_url;
        $this->_async_capture_params['notify_url']   = $notify_url;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        $attributes                             = [];
        $attributes['tcaccepted']               = $this->_terms_accepted;
        $attributes['expecteddaystillshipping'] = $this->_expected_days_till_shipping;
        $attributes['capturerequestnecessary']  = $this->_capture_request_necessary;
        $attributes['paymenttype']              = $this->_payment_type;

        return ipl_core_send_preauthorize_request(
            $this->_ipl_request_url,
            $attributes,
            $this->getTraceData(),
            $this->_default_params,
            $this->_preauth_params,
            $this->_customer_details,
            $this->_shippping_details,
            $this->_bank_account,
            $this->_totals,
            $this->_article_data,
            $this->_order_history_attr,
            $this->_order_history_data,
            $this->_rate_request_data,
            $this->_company_details,
            $this->_payment_info_params,
            $this->_fraud_detection,
            $this->_async_capture_params
        );
    }

    /**
     * @param $data
     */
    protected function _process_response_xml($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $data
     */
    protected function _process_error_response_xml($data)
    {
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
        if (isset($data['validation_errors'])) {
            $this->_validation_errors = $data['validation_errors'];
        }
    }
}
