<?php

require_once __DIR__ . '/ipl_xml_request.php';

/**
 * @author    Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license   commercial
 */
class ipl_prescore_request extends ipl_xml_request
{
    /**
     * @var
     */
    private $_capture_request_necessary;

    /**
     * @var array
     */
    private $_customer_details = [];

    /**
     * @var array
     */
    private $_shippping_details = [];

    /**
     * @var array
     */
    private $_totals = [];

    /**
     * @var array
     */
    private $_article_data = [];

    /**
     * @var array
     */
    private $_order_history_attr = [];

    /**
     * @var array
     */
    private $_order_history_data = [];

    /**
     * @var array
     */
    private $_company_details = [];

    /**
     * @var array
     */
    private $_payment_info_params = [];

    /**
     * @var array
     */
    private $_fraud_detection = [];

    /**
     * @var
     */
    private $_payment_type;

    /**
     * @var
     */
    private $bptid;

    /**
     * @var
     */
    private $corrected_street;

    /**
     * @var
     */
    private $corrected_street_no;

    /**
     * @var
     */
    private $corrected_zip;

    /**
     * @var
     */
    private $corrected_city;

    /**
     * @var
     */
    private $corrected_country;

    /**
     * @var int
     */
    private $_expected_days_till_shipping = 0;

    /**
     * @var
     */
    private $payment_info_html;

    /**
     * @var
     */
    private $payment_info_plain;

    /**
     * @var array
     */
    private $_payments_allowed = [];

    /**
     * @var array
     */
    private $_rate_info = [];

    /**
     * @var array
     */
    private $_payments_allowed_all = [];
    /**
     * @var array
     */
    private $_terms = [];

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
     * @return array
     */
    public function get_payments_allowed_all()
    {
        return $this->_payments_allowed_all;
    }

    /**
     * @return array
     */
    public function get_payments_allowed()
    {
        return $this->_payments_allowed;
    }

    /**
     * @return array
     */
    public function get_rate_info()
    {
        return $this->_rate_info;
    }

    /**
     * @return array
     */
    public function get_terms()
    {
        return $this->_terms;
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
    public function set_customer_details(
        $customer_id,
        $customer_type,
        $salutation,
        $title,
        $first_name,
        $last_name,
        $street,
        $street_no,
        $address_addition,
        $zip,
        $city,
        $country,
        $email,
        $phone,
        $cell_phone,
        $birthday,
        $language,
        $ip,
        $customerGroup
    ) {
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
    public function set_shipping_details(
        $use_billing_address,
        $salutation = null,
        $title = null,
        $first_name = null,
        $last_name = null,
        $street = null,
        $street_no = null,
        $address_addition = null,
        $zip = null,
        $city = null,
        $country = null,
        $phone = null,
        $cell_phone = null
    ) {
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
    public function add_article(
        $articleid,
        $articlequantity,
        $articlename,
        $articledescription,
        $article_price,
        $article_price_gross
    ) {
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
            'merchant_customer_limit' => (int)$iMerchantCustomerLimit,
            'repeat_customer'         => (int)$iRepeatCustomer,
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
     * @param $rebate
     * @param $rebate_gross
     * @param $shipping_name
     * @param $shipping_price
     * @param $shipping_price_gross
     * @param $cart_total_price
     * @param $cart_total_price_gross
     * @param $currency
     */
    public function set_total(
        $rebate,
        $rebate_gross,
        $shipping_name,
        $shipping_price,
        $shipping_price_gross,
        $cart_total_price,
        $cart_total_price_gross,
        $currency
    ) {
        $this->_totals['shippingname']        = $shipping_name;
        $this->_totals['shippingprice']       = $shipping_price;
        $this->_totals['shippingpricegross']  = $shipping_price_gross;
        $this->_totals['rebate']              = $rebate;
        $this->_totals['rebategross']         = $rebate_gross;
        $this->_totals['carttotalprice']      = $cart_total_price;
        $this->_totals['carttotalpricegross'] = $cart_total_price_gross;
        $this->_totals['currency']            = $currency;
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
     * @return array|bool
     */
    protected function _send()
    {
        $attributes = [];

        return ipl_core_send_prescore_request(
            $this->_ipl_request_url,
            $attributes,
            $this->getTraceData(),
            $this->_default_params,
            $this->_customer_details,
            $this->_shippping_details,
            $this->_totals,
            $this->_article_data,
            $this->_order_history_attr,
            $this->_order_history_data,
            $this->_company_details,
            $this->_payment_info_params,
            $this->_fraud_detection
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
    }
}
