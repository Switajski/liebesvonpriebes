<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * class ipl_module_config_request
 * 
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_module_config_request extends ipl_xml_request
{
    /**
     * @var int
     */
    private $invoicestatic         = 0;

    /**
     * @var int
     */
    private $invoicebusinessstatic = 0;

    /**
     * @var int
     */
    private $directdebitstatic     = 0;

    /**
     * @var int
     */
    private $hirepurchasestatic    = 0;

    /**
     * @var int
     */
    private $invoicemin         = 0;

    /**
     * @var int
     */
    private $invoicebusinessmin = 0;

    /**
     * @var int
     */
    private $directdebitmin     = 0;

    /**
     * @var int
     */
    private $hirepurchasemin    = 0;

    /**
     * @var bool
     */
    private $active                 = false;

    /**
     * @var bool
     */
    private $invoiceallowed         = false;

    /**
     * @var bool
     */
    private $invoicebusinessallowed = false;

    /**
     * @var bool
     */
    private $directdebitallowed     = false;

    /**
     * @var bool
     */
    private $hirepurchaseallowed    = false;

    /**
     * @var array
     */
    private $terms = [];

    /**
     * @var array
     */
    private $_locale = [];

    /**
     * @return bool
     */
    public function is_active()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function is_invoice_allowed()
    {
        return $this->invoiceallowed;
    }

    /**
     * @return bool
     */
    public function is_invoicebusiness_allowed()
    {
        return $this->invoicebusinessallowed;
    }

    /**
     * @return bool
     */
    public function is_direct_debit_allowed()
    {
        return $this->directdebitallowed;
    }

    /**
     * @return bool
     */
    public function is_hire_purchase_allowed()
    {
        return $this->hirepurchaseallowed;
    }

    /**
     * @return int
     */
    public function get_invoice_min_value()
    {
        return $this->invoicemin;
    }

    /**
     * @return int
     */
    public function get_invoicebusiness_min_value()
    {
        return $this->invoicebusinessmin;
    }

    /**
     * @return int
     */
    public function get_direct_debit_min_value()
    {
        return $this->directdebitmin;
    }

    /**
     * @return int
     */
    public function get_hire_purchase_min_value()
    {
        return $this->hirepurchasemin;
    }

    /**
     * @return int
     */
    public function get_static_limit_invoice()
    {
        return $this->invoicestatic;
    }

    /**
     * @return int
     */
    public function get_static_limit_invoicebusiness()
    {
        return $this->invoicebusinessstatic;
    }

    /**
     * @return int
     */
    public function get_static_limit_direct_debit()
    {
        return $this->directdebitstatic;
    }

    /**
     * @return int
     */
    public function get_static_limit_hire_purchase()
    {
        return $this->hirepurchasestatic;
    }

    /**
     * @return array
     */
    public function get_terms()
    {
        return $this->terms;
    }

    /**
     * @return bool
     */
    public function is_paylater_allowed()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function is_paylaterbusiness_allowed()
    {
        return false;
    }

    /**
     * @return int
     */
    public function get_paylater_min_value()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function get_paylaterbusiness_min_value()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function get_static_limit_paylater()
    {
        return PHP_INT_MAX;
    }

    /**
     * @return int
     */
    public function get_static_limit_paylaterbusiness()
    {
        return 0;
    }

    /**
     * @return array
     */
    public function get_config_data()
    {
        return [
            'is_active'                    => $this->is_active(),
            'is_allowed_invoice'           => $this->is_invoice_allowed(),
            'is_allowed_invoicebusiness'   => $this->is_invoicebusiness_allowed(),
            'is_allowed_directdebit'       => $this->is_direct_debit_allowed(),
            'is_allowed_transactioncredit' => $this->is_hire_purchase_allowed(),
            'is_allowed_paylater'          => $this->is_paylater_allowed(),
            'is_allowed_paylaterbusiness'  => $this->is_paylaterbusiness_allowed(),

            'minvalue_invoice'             => $this->get_invoice_min_value(),
            'minvalue_invoicebusiness'     => $this->get_invoicebusiness_min_value(),
            'minvalue_directdebit'         => $this->get_direct_debit_min_value(),
            'minvalue_transactioncredit'   => $this->get_hire_purchase_min_value(),
            'minvalue_paylater'            => $this->get_paylater_min_value(),
            'minvalue_paylaterbusiness'    => $this->get_paylaterbusiness_min_value(),

            'maxvalue_invoice'             => $this->get_static_limit_invoice(),
            'maxvalue_invoicebusiness'     => $this->get_static_limit_invoicebusiness(),
            'maxvalue_directdebit'         => $this->get_static_limit_direct_debit(),
            'maxvalue_transactioncredit'   => $this->get_static_limit_hire_purchase(),
            'maxvalue_paylater'            => $this->get_static_limit_hire_purchase(),
            'maxvalue_paylaterbusiness'    => $this->get_static_limit_hire_purchase(),
        ];
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
     * @param $country
     * @param $currency
     * @param $language
     */
    public function set_locale($country, $currency, $language)
    {
        $this->_locale['country']  = $country;
        $this->_locale['currency'] = $currency;
        $this->_locale['language'] = $language;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_module_config_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_locale
        );
    }
}
