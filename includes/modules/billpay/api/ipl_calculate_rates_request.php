<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_calculate_rates_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_rate_params = [];
    /**
     * @var
     */
    private $options;
    /**
     * @var array
     */
    private $_locale = [];

    /**
     * @return mixed
     */
    public function get_options()
    {
        return $this->options;
    }

    /**
     * @param $baseamount
     * @param $carttotalgross
     */
    public function set_rate_request_params($baseamount, $carttotalgross)
    {
        $this->_rate_params['baseamount']     = $baseamount;
        $this->_rate_params['carttotalgross'] = $carttotalgross;
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
        return ipl_core_send_calculate_rates_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_rate_params,
            $this->_locale
        );
    }

    /**
     * @param $data
     */
    protected function _process_response_xml($data)
    {
        $this->options = $data['options'];
    }
}
