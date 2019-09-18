<?php

/**
 * Warning: this request uses different service than every other requests.
 */
require_once __DIR__. '/ipl_xml_request.php';

/**
 * Class ipl_get_installment_request
 */
class ipl_get_installment_request extends ipl_xml_request
{
    /**
     * @var
     */
    public $baseAmount;

    /**
     * @var
     */
    public $cartTotalGross;

    /**
     * @var
     */
    public $billingCountry;

    /**
     * @var
     */
    public $orderCurrency;

    /**
     * @var
     */
    public $lang;

    /**
     * @var
     */
    public $plans; // installment plans
    # http://de20:8092/rest/getInstallmentOptions?apiKey=5194d0e447cbad07584238b5dae63287&cartTotalGross=33388&baseAmount=32388&billingCountry=DEU&orderCurrency=EUR&lang=de

    /**
     * @param $baseAmount
     * @param $cartTotalGross
     */
    public function set_rate_request_params($baseAmount, $cartTotalGross)
    {
        $this->baseAmount     = $baseAmount;
        $this->cartTotalGross = $cartTotalGross;
    }

    /**
     * @param $billingCountry
     * @param $orderCurrency
     * @param $lang
     */
    public function set_locale($billingCountry, $orderCurrency, $lang)
    {
        $this->billingCountry = $billingCountry;
        $this->orderCurrency  = $orderCurrency;
        $this->lang           = $lang;
    }

    /**
     * @return array
     */
    public function _send()
    {
        # requestXml, resultXml, data

        $data = [
            'apiKey'            => $this->_getApiKey(),
            'cartTotalGross'    => $this->cartTotalGross,
            'baseAmount'        => $this->baseAmount,
            'billingCountry'    => $this->billingCountry,
            'orderCurrency'     => $this->orderCurrency,
            'lang'              => $this->lang,
        ];
        $a = [];
        foreach ($data as $key => $val) {
            $a[] = implode('=', [$key, $val]);
        }
        $method = '/getInstallmentOptions';
        $query  = $method . "?" . implode('&', $a);
        $url    = $this->_ipl_request_url . $query;
        # this could have used ipl_core_send, but it's designed to communicate in a very rigid format
        if (false && IPL_CORE_HTTP_CLIENT === "fake") {
            $response = ipl_fake_send_request($this->_ipl_request_url . $method, $data);
        } else {
            $response = file_get_contents($url);
        }

        return [$url, $response, $response];
    }

    /**
     * @param $data
     */
    public function _process_response_xml($data)
    {
        $json              = json_decode($data);
        $plans             = [];
        $installment_plans = $json->instlPlanList;
        foreach ($installment_plans->instlPlan as $installment_plan) {
            $plans[(int) $installment_plan->numInst] = (array) $installment_plan->calc;
        }
        $this->plans = $plans;
    }

    /**
     * @return string
     */
    public function _getApiKey()
    {
        # apiKey = md5(mid + pid + substr(0,10, md5(securityKey)))
        # bpsecure is already md5'ed
        $apiKey = md5($this->_default_params['pid'] . '+' . $this->_default_params['mid'] . '+' . substr($this->_default_params['bpsecure'], 0, 10));

        return $apiKey;
    }
}
