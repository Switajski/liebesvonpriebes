<?php

/**
 * A single capture object.
 *
 * @author Sebastian Meyer (sebastian.meyer@solution360.de )
 */
class LPACapture {
    
    /*
     * Public vars (persisted in DB!)
     */
    public $cCaptureId;
    public $cCaptureStatus;
    public $cCaptureStatusReason;
    public $fCaptureAmount;
    public $cCaptureCurrencyCode;
    public $fRefundedAmount;
    public $cRefundedCurrencyCode;
    public $cAuthorizationId;
    public $bSandbox;
    
    public function __construct($raw, $rawType = "Array") {
        switch($rawType) {
            default:
                $this->fillFromArray($raw);
                break;
        }
    }
    
    private function fillFromArray($array) {
        $this->cCaptureId = $array['AmazonCaptureId'];
        $this->cCaptureStatus = $array['CaptureStatus']['State'];
        $this->cCaptureStatusReason = isset($array['CaptureStatus']['ReasonCode']) ? $array['CaptureStatus']['ReasonCode'] : '';
        $this->fCaptureAmount = $array['CaptureAmount']['Amount'];
        $this->cCaptureCurrencyCode = $array['CaptureAmount']['CurrencyCode'];
        $this->fRefundedAmount = $array['RefundedAmount']['Amount'];
        $this->cRefundedCurrencyCode = $array['RefundedAmount']['CurrencyCode'];
        $this->bSandbox = ($this->cCaptureId[0] === "S");
    }
    
    
}
