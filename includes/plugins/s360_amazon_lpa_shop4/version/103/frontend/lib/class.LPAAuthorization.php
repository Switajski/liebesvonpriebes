<?php

/**
 * A single authorization object.
 *
 * @author Sebastian Meyer (sebastian.meyer@solution360.de )
 */
class LPAAuthorization {
    /*
     * Public vars (persisted in DB!)
     */
    public $cAuthorizationId;
    public $cAuthorizationStatus;
    public $cAuthorizationStatusReason;
    public $fAuthorizationAmount;
    public $cAuthorizationCurrencyCode;
    public $fCapturedAmount;
    public $cCapturedCurrencyCode;
    public $bCaptureNow;
    public $nAuthorizationExpirationTimestamp;
    public $cOrderReferenceId;
    public $bSandbox;
    
    public function __construct($raw, $rawType = "Array") {
        switch($rawType) {
            default:
                $this->fillFromArray($raw);
                break;
        }
    }
    
    private function fillFromArray($array) {
        $this->cAuthorizationId = $array['AmazonAuthorizationId'];
        $this->cAuthorizationStatus = $array['AuthorizationStatus']['State'];
        $this->cAuthorizationStatusReason = isset($array['AuthorizationStatus']['ReasonCode']) ? $array['AuthorizationStatus']['ReasonCode'] : '';
        $this->fAuthorizationAmount = floatval($array['AuthorizationAmount']['Amount']);
        $this->cAuthorizationCurrencyCode = $array['AuthorizationAmount']['CurrencyCode'];
        $this->fCapturedAmount = floatval($array['CapturedAmount']['Amount']);
        $this->cCapturedCurrencyCode = $array['CapturedAmount']['CurrencyCode'];
        $this->bCaptureNow = (int) $array['CaptureNow'];
        $expiration = $array['ExpirationTimestamp'];
        if (!empty($expiration)) {
            $timezone = ini_get("date.timezone");
            if (empty($timezone)) {
                date_default_timezone_set("Europe/Berlin");
            }
            $this->nAuthorizationExpirationTimestamp = intval(date_timestamp_get(new DateTime($expiration)));
        }
        $this->bSandbox = ($this->cAuthorizationId[0] === "S");
    }
}
