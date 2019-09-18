<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * Class ipl_cancel_request
 *
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_cancel_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_cancel_params = [];

    /**
     * @param $reference
     * @param $cart_total_gross
     * @param $currency
     */
    public function set_cancel_params($reference, $cart_total_gross, $currency)
    {
        $this->_cancel_params['reference']      = $reference;
        $this->_cancel_params['carttotalgross'] = $cart_total_gross;
        $this->_cancel_params['currency']       = $currency;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_cancel_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_cancel_params
        );
    }

    /**
     * @param $data
     */
    protected function _process_response_xml($data)
    {
    }
}
