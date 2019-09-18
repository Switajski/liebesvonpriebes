<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial
 */
class ipl_invoice_created_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_invoice_params      = [];

    /**
     * @var array
     */
    private $_payment_info_params = [];

    /**
     * @var array
     */
    private $_article_data        = [];

    /**
     * @var
     */
    private $account_holder;

    /**
     * @var
     */
    private $account_number;

    /**
     * @var
     */
    private $bank_code;

    /**
     * @var
     */
    private $bank_name;

    /**
     * @var
     */
    private $invoice_reference;

    /**
     * @var
     */
    private $invoice_duedate;

    /**
     * @var
     */
    private $activation_performed;

    /**
     * @var
     */
    private $payment_info_html;

    /**
     * @var
     */
    private $payment_info_plain;

    /**
     * @var
     */
    private $instalment_count;

    /**
     * @var
     */
    private $duration;

    /**
     * @var
     */
    private $fee_percent;

    /**
     * @var
     */
    private $fee_total;

    /**
     * @var
     */
    private $async_amount;

    /**
     * @var
     */
    private $total_amount;

    /**
     * @var
     */
    private $effective_annual;

    /**
     * @var
     */
    private $nominal_annual;

    /**
     * @var
     */
    private $base_amount;

    /**
     * @var
     */
    private $cart_amount;

    /**
     * @var
     */
    private $surcharge;

    /**
     * @var
     */
    private $interest;

    /**
     * @var array
     */
    private $dues = [];

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
    public function get_dues()
    {
        return $this->dues;
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
    public function get_prepayment_amount()
    {
        return $this->async_amount;
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
     * @param        $carttotalgross
     * @param        $currency
     * @param        $reference
     * @param int    $delayindays
     * @param int    $is_partial
     * @param int    $invoice_number
     * @param int    $rebate
     * @param int    $rebate_gross
     * @param string $shipping_name
     * @param int    $shipping_price
     * @param int    $shipping_price_gross
     * @param int    $cart_total_price
     */
    public function set_invoice_params($carttotalgross, $currency, $reference,
                $delayindays = 0, $is_partial = 0, $invoice_number = 0,
                $rebate = 0, $rebate_gross = 0, $shipping_name = "", $shipping_price = 0,
                $shipping_price_gross = 0, $cart_total_price = 0)
    {
        $this->_invoice_params['carttotalgross'] = $carttotalgross;
        $this->_invoice_params['currency']       = $currency;
        $this->_invoice_params['reference']      = $reference;
        $this->_invoice_params['delayindays']    = $delayindays;
        //Partial activation
        if ($is_partial == 1) {
            $this->_invoice_params['is_partial']         = $is_partial;
            $this->_invoice_params['invoice_number']     = $invoice_number;
            $this->_invoice_params['shippingname']       = $shipping_name;
            $this->_invoice_params['shippingprice']      = $shipping_price;
            $this->_invoice_params['shippingpricegross'] = $shipping_price_gross;
            $this->_invoice_params['rebate']             = $rebate;
            $this->_invoice_params['rebategross']        = $rebate_gross;
            $this->_invoice_params['carttotalprice']     = $cart_total_price;
        }
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
     * @param $showhtmlinfo
     * @param $showplaininfo
     */
    public function set_payment_info_params($showhtmlinfo, $showplaininfo)
    {
        $this->_payment_info_params['htmlinfo']  = $showhtmlinfo ? "1" : "0";
        $this->_payment_info_params['plaininfo'] = $showplaininfo ? "1" : "0";
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_invoice_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_invoice_params,
            $this->_payment_info_params,
            $this->_article_data
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
}
