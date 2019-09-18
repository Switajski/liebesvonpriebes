<?php

/**
 * A single refund object.
 *
 * @author Sebastian Meyer (sebastian.meyer@solution360.de )
 */
class LPARefund {
    /*
     * Public vars (persisted in DB!)
     */
    public $cRefundId;
    public $cRefundStatus;
    public $cRefundStatusReason;
    public $fRefundAmount;
    public $cRefundCurrencyCode;
    public $cRefundType;
    public $bSandbox;
    public $cCaptureId;
    
    public function __construct($raw, $rawType = "Array") {
        switch($rawType) {
            default:
                $this->fillFromArray($raw);
                break;
        }
    }
    
    private function fillFromArray($array) {
        $this->cRefundId = $array['AmazonRefundId'];
        $this->cRefundStatus = $array['RefundStatus']['State'];
        $this->cRefundStatusReason = isset($array['RefundStatus']['ReasonCode']) ? $array['RefundStatus']['ReasonCode'] : '';
        $this->cRefundType = $array['RefundType'];
        $this->fRefundAmount = (float) $array['RefundAmount']['Amount'];
        $this->cRefundCurrencyCode = $array['RefundAmount']['CurrencyCode'];
        $this->bSandbox = ($this->cRefundId[0] === "S");
    }
}


