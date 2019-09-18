<?php

require_once __DIR__. '/ipl_xml_request.php';

/** 
 * class ipl_edit_cart_content_request
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_edit_cart_content_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_totals       = [];

    /**
     * @var array
     */
    private $_article_data = [];

    /**
     * @var array
     */
    private $_invoice_list = [];

    /**
     * @var
     */
    private $due_update;

    /**
     * @var
     */
    private $number_of_rates;

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
     * @var array
     */
    private $dues = [];

    /**
     * @var
     */
    private $async_amount;

    /**
     * @return mixed
     */
    public function get_due_update()
    {
        return $this->due_update;
    }

    /**
     * @return mixed
     */
    public function get_number_of_rates()
    {
        return $this->number_of_rates;
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
     * @return array
     */
    public function get_dues()
    {
        return $this->dues;
    }

    /**
     * @return mixed
     */
    public function get_prepayment_amount()
    {
        return $this->async_amount;
    }

    /**
     * @param        $articleid
     * @param        $articlequantity
     * @param        $articlename
     * @param        $articledescription
     * @param        $article_price
     * @param        $article_price_gross
     * @param string $invoice_number
     */
    public function add_article($articleid, $articlequantity, $articlename, $articledescription,
        $article_price, $article_price_gross, $invoice_number = "")
    {
        if ($articlequantity < 1) {
            return; // we don't send empty records
        }
        $article                       = [];
        $article['articleid']          = $articleid;
        $article['articlequantity']    = $articlequantity;
        $article['articlename']        = $articlename;
        $article['articledescription'] = $articledescription;
        $article['articleprice']       = $article_price;
        $article['articlepricegross']  = $article_price_gross;

        $this->_article_data[] = $article;
        if ($invoice_number !== '') {
            $this->_invoice_list[$invoice_number]['article_data'][] = $article;
        }
    }

    /**
     * @param $rebate
     * @param $rebate_gross
     * @param $shipping_price
     * @param $shipping_price_gross
     * @param $cart_total_price
     * @param $cart_total_price_gross
     * @param $currency
     * @param $invoice_number
     */
    public function add_invoice($rebate, $rebate_gross, $shipping_price, $shipping_price_gross,
                                $cart_total_price, $cart_total_price_gross,
                                $currency, $invoice_number)
    {
        $invoice                              = [];
        $invoice['rebate']                    = $rebate;
        $invoice['rebategross']               = $rebate_gross;
        $invoice['shippingprice']             = $shipping_price;
        $invoice['shippingpricegross']        = $shipping_price_gross;
        $invoice['carttotalprice']            = $cart_total_price;
        $invoice['carttotalpricegross']       = $cart_total_price_gross;
        $invoice['currency']                  = $currency;
        $invoice['article_data']              = [];
        $this->_invoice_list[$invoice_number] = $invoice;
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
     * @param $reference
     */
    public function set_total($rebate, $rebate_gross, $shipping_name, $shipping_price,
            $shipping_price_gross, $cart_total_price, $cart_total_price_gross,
            $currency, $reference)
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
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_edit_cart_content_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_totals,
            $this->_article_data,
            $this->_invoice_list
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
