<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * class ipl_partialcancel_request
 * 
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_partialcancel_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_cancel_params     = [];

    /**
     * @var array
     */
    private $_canceled_articles = [];

    /**
     * @var
     */
    private $due_update;

    /**
     * @var
     */
    private $number_of_rates;

    /**
     * @return mixed
     */
    public function is_transaction_credit_order()
    {
        return $this->due_update;
    }

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
     * @param $reference
     * @param $rebatedecrease
     * @param $rebatedecreasegross
     * @param $shippingdecrease
     * @param $shippingdecreasegross
     * @param $currency
     */
    public function set_cancel_params($reference, $rebatedecrease, $rebatedecreasegross, $shippingdecrease, $shippingdecreasegross, $currency)
    {
        $this->_cancel_params['reference']             = $reference;
        $this->_cancel_params['rebatedecrease']        = $rebatedecrease;
        $this->_cancel_params['rebatedecreasegross']   = $rebatedecreasegross;
        $this->_cancel_params['shippingdecrease']      = $shippingdecrease;
        $this->_cancel_params['shippingdecreasegross'] = $shippingdecreasegross;
        $this->_cancel_params['currency']              = $currency;
    }

    /**
     * @param $articleid
     * @param $articlequantity
     */
    public function add_canceled_article($articleid, $articlequantity)
    {
        $article                    = [];
        $article['articleid']       = $articleid;
        $article['articlequantity'] = $articlequantity;

        $this->_canceled_articles[] = $article;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_partialcancel_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_cancel_params,
            $this->_canceled_articles
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
