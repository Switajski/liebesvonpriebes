<?php

require_once __DIR__. '/ipl_xml_request.php';

/**
 * class ipl_update_order_request
 * 
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_update_order_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_update_params  = [];

    /**
     * @var array
     */
    private $_id_update_list = [];

    /**
     * @param $bptid
     * @param $reference
     */
    public function set_update_params($bptid, $reference)
    {
        $this->_update_params['bptid']     = $bptid;
        $this->_update_params['reference'] = $reference;
    }

    /**
     * @param $articleid
     * @param $updateid
     */
    public function add_id_update($articleid, $updateid)
    {
        $idUpdate = [];

        $idUpdate['articleid']   = $articleid;
        $idUpdate['updateid']    = $updateid;
        $this->_id_update_list[] = $idUpdate;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_update_order_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_update_params,
            $this->_id_update_list
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
