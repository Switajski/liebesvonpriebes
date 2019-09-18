<?php

/**
 * A single order reference object.
 *
 * @author Sebastian Meyer (sebastian.meyer@solution360.de )
 */
class LPAOrderReference {
    /*
     * Public vars (persisted in DB!)
     */
    public $cOrderReferenceId;
    public $cOrderStatus;
    public $cOrderStatusReason;
    public $fOrderAmount;
    public $cOrderCurrencyCode;
    public $nOrderExpirationTimestamp;
    public $bSandbox;
    public $kBestellung;
    
    public function __construct($raw, $rawType = "Array") {
        switch($rawType) {
            default:
                $this->fillFromArray($raw);
                break;
        }
    }
    
    private function fillFromArray($array) {
        $this->cOrderReferenceId = $array['AmazonOrderReferenceId'];
        $this->cOrderStatus = $array['OrderReferenceStatus']['State'];
        $this->cOrderStatusReason = isset($array['OrderReferenceStatus']['ReasonCode']) ? $array['OrderReferenceStatus']['ReasonCode'] : '';
        $this->fOrderAmount = floatval($array['OrderTotal']['Amount']);
        $this->cOrderCurrencyCode = $array['OrderTotal']['CurrencyCode'];
        $expiration = $array['ExpirationTimestamp'];
        if (!empty($expiration)) {
            $timezone = ini_get("date.timezone");
            if (empty($timezone)) {
                date_default_timezone_set("Europe/Berlin");
            }
            $this->nOrderExpirationTimestamp = intval(date_timestamp_get(new DateTime($expiration)));
        }
        $this->bSandbox = ($this->cOrderReferenceId[0] === "S");
    }
}
