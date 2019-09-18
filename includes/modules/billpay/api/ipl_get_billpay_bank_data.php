<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * class ipl_get_billpay_bank_data
 * 
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_get_billpay_bank_data extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_get_billpay_bank_data_params = [];

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
    private $reference;

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
     * @param $reference
     */
    public function set_order_reference($reference)
    {
        $this->_get_billpay_bank_data_params['reference'] = $reference;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_get_billpay_bank_data_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_get_billpay_bank_data_params
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
