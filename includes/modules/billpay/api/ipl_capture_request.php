<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * Class ipl_capture_request
 *
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_capture_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_capture_params      = [];

    /**
     * @var array
     */
    private $_payment_info_params = [];

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
    private $standard_information_pdf;

    /**
     * @var
     */
    private $email_attachment_pdf;

    /**
     * @var
     */
    private $payment_info_html;

    /**
     * @var
     */
    private $payment_info_plain;

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
     * @param $bptid
     * @param $cart_total_gross
     * @param $currency
     * @param $reference
     * @param $customer_id
     */
    public function set_capture_params($bptid, $cart_total_gross, $currency, $reference, $customer_id)
    {
        $this->_capture_params['bptid']          = $bptid;
        $this->_capture_params['carttotalgross'] = $cart_total_gross;
        $this->_capture_params['currency']       = $currency;
        $this->_capture_params['reference']      = $reference;
        $this->_capture_params['customerid']     = $customer_id;
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
        return ipl_core_send_capture_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_capture_params,
            $this->_payment_info_params
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
