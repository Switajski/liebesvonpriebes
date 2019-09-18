<?php

/*
 * Solution 360 GmbH
 *
 * Handles direct calls to amazon and mails.
 */
require_once('lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Kunde.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "mailTools.php");
require_once('lpa_defines.php');
require_once('class.LPAController.php');
require_once('class.LPADatabase.php');

class LPAAdapter {

    var $controller;
    var $db;
    var $mode;
    var $oPlugin;
    var $additionalBackendBackoff;

    /**
     * Constructor for the Adapter.
     * Mode can be frontend (default) or backend.
     * 
     * This can be used to distinguish certain operations between frontend and backend calls, e.g. exponential backoff can
     * have higher backoff values in backend operations.
     * 
     * @param type $mode
     */
    public function __construct($mode = 'frontend') {
        $this->db = new LPADatabase();
        $this->controller = new LPAController();
        $this->mode = $mode;
        $this->oPlugin = Plugin::getPluginById(S360_LPA_PLUGIN_ID);
        $this->additionalBackendBackoff = ((int) $this->oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_BACKOFF_BACKEND]) * 1000000 ; // variable in config is in seconds, we need microseconds
    }

    /*
     * Cancels an order against amazon.
     */

    public function cancelOrder($orid, $reason = NULL, $throwException = false) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        if (is_null($reason)) {
            $reason = 'Unspecified';
        }
        $cancelOrderReferenceParameters = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_order_reference_id' => $orid,
            'cancelation_reason' => $reason
        );
        try {
            $response = $client->cancelOrderReference($cancelOrderReferenceParameters);
            $responseArray = $response->toArray();
            if (isset($responseArray['Error'])) {
                if ($throwException) {
                    throw new Exception($responseArray['Error']['Message'] . "(" . $responseArray['Error']['Code'] . ")", 0, null);
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: CancelOrderReferenceRequest fehlgeschlagen:' . $responseArray['Error']['Code'] . ',' . $responseArray['Error']['Message'], JTLLOG_LEVEL_ERROR);
                }
            } else {
                // this request does not return any useful data, but if it does not fail with an exception, we assume it went through
                Jtllog::writeLog("LPA: LPA-Payment: OrderReference {$orid} wurde gecanceled/storniert.", JTLLOG_LEVEL_DEBUG);
            }
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: CancelOrderReferenceRequest fehlgeschlagen:' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw $ex;
            }
        }
    }

    /*
     * Closes an order against amazon.
     */

    public function closeOrder($orid, $reason = NULL, $throwException = false) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        if (is_null($reason)) {
            $reason = 'Unspecified';
        }
        $closeOrderReferenceParameters = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_order_reference_id' => $orid,
            'closure_reason' => $reason
        );
        try {
            $response = $client->closeOrderReference($closeOrderReferenceParameters);
            $responseArray = $response->toArray();
            if (isset($responseArray['Error'])) {
                if ($throwException) {
                    throw new Exception($responseArray['Error']['Message'] . "(" . $responseArray['Error']['Code'] . ")", 0, null);
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: CloseOrderReferenceRequest fehlgeschlagen:' . $responseArray['Error']['Code'] . ',' . $responseArray['Error']['Message'], JTLLOG_LEVEL_ERROR);
                }
            } else {
// this request does not return any useful data, but if it does not fail with an exception, we assume it went through
                Jtllog::writeLog("LPA: LPA-Payment: OrderReference {$orid} wurde geschlossen.", JTLLOG_LEVEL_DEBUG);
            }
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: CloseOrderReferenceRequest fehlgeschlagen:' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw $ex;
            }
        }
    }

    /*
     * Sets an order to automatically completed if the corresponding setting is enabled in the plugin, else it does nothing.
     * Completed means closing the order as a seller instead of waiting for Amazon Pay to set it after 6 months.
     */
    public function setOrderCompleted($orid) {
        if(isset($this->oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTOCLOSE]) && $this->oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTOCLOSE] === 'Y') {
            Jtllog::writeLog("LPA: OrderReference {$orid} wird aufgrund von AutoClose geschlossen.", JTLLOG_LEVEL_DEBUG);
            // Note that Amazon *should* send us an IPN in response to mark the order closed, no need to refresh it ourselves here
            $this->closeOrder($orid, 'Completed', false);
        }
    }

    /*
     * Closes an authorization against amazon.
     */

    public function closeAuthorization($authid, $reason = NULL, $throwException = false) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        if (is_null($reason)) {
            $reason = 'Unspecified';
        }
        $closeAuthorizationParameters = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_authorization_id' => $authid,
            'closure_reason' => $reason
        );
        try {
            $response = $client->closeAuthorization($closeAuthorizationParameters);
            $responseArray = $response->toArray();
            if (isset($responseArray['Error'])) {
                if ($throwException) {
                    throw new Exception($responseArray['Error']['Message'] . "(" . $responseArray['Error']['Code'] . ")", 0, null);
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: CloseAuthorizationRequest fehlgeschlagen:' . $responseArray['Error']['Code'] . ',' . $responseArray['Error']['Message'], JTLLOG_LEVEL_ERROR);
                }
            } else {
// this request does not return any useful data, but if it does not fail with an exception, we assume it went through
                Jtllog::writeLog("LPA: LPA-Payment: Authorization {$authid} wurde geschlossen.", JTLLOG_LEVEL_DEBUG);
            }
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: CloseAuthorizationRequest fehlgeschlagen:' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw $ex;
            }
        }
    }

    /*
     * Tries to get a new authorization over the full amount for an existing order reference id.
     */

    public function authorize($orid, $immediateCapture = false, $timeout = S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT, $amount = NULL, $throwException = false, $skipStatusCheck = false) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $order = $this->db->getOrder($orid, true);
        /*
         * Safety check: only open orders may be authorized upon.
         */
        if (!$skipStatusCheck && $order->cOrderStatus !== S360_LPA_STATUS_OPEN) {
            $error = "LPA: LPA-Payment-Fehler: AuthorizationRequest gegen nicht offene Bestellung {$orid} versucht.";
            Jtllog::writeLog($error, JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw new Exception($error);
            }
            return;
        }
        if (is_null($amount)) {
            $amount = $order->fOrderAmount;
        }
        $authorizeParameters = array(
            'merchant_id' => $config['merchant_id'],
            'seller_authorization_note' => $order->bestellung->cBestellNr,
            'amazon_order_reference_id' => $orid,
            'authorization_reference_id' => $orid . "-A-" . time(),
            'authorization_amount' => (string) $amount,
            'currency_code' => $order->cOrderCurrencyCode,
            'transaction_timeout' => $timeout,
            'capture_now' => $immediateCapture, // Capture funds immedately if authorization was successful
        );

        try {
            $authorizationResponseWrapper = $client->authorize($authorizeParameters);
            $authorizationResponseWrapper = $authorizationResponseWrapper->toArray();
            if (isset($authorizationResponseWrapper['Error'])) {
                if ($throwException) {
                    throw new Exception($authorizationResponseWrapper['Error']['Message']);
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: AuthorizationRequest fehlgeschlagen:' . $authorizationResponseWrapper['Error']['Message'], JTLLOG_LEVEL_ERROR);
                    return;
                }
            } else {
                $authorizationResponseWrapper = $authorizationResponseWrapper['AuthorizeResult'];
            }
            $authorizationDetails = $authorizationResponseWrapper['AuthorizationDetails'];
            $amazonAuthorizationId = $authorizationDetails['AmazonAuthorizationId'];
            $authorizationStatus = $authorizationDetails['AuthorizationStatus'];
            $auth = new stdClass();
            $auth->cOrderReferenceId = $orid;
            $auth->cAuthorizationId = $amazonAuthorizationId;
            $auth->fAuthorizationAmount = (float) $authorizationDetails['AuthorizationAmount']['Amount'];
            $auth->cAuthorizationCurrencyCode = $authorizationDetails['AuthorizationAmount']['CurrencyCode'];
            $auth->bCaptureNow = (int) $immediateCapture;
            $auth->fCapturedAmount = (float) $authorizationDetails['CapturedAmount']['Amount'];
            $auth->cCapturedCurrencyCode = $authorizationDetails['CapturedAmount']['CurrencyCode'];
            $auth->cAuthorizationStatus = $authorizationStatus['State'];
            $auth->cAuthorizationStatusReason = isset($authorizationStatus['ReasonCode']) ? $authorizationStatus['ReasonCode'] : '';
            $expiration = $authorizationDetails['ExpirationTimestamp'];
            if (!empty($expiration)) {
                $timezone = ini_get("date.timezone");
                if (empty($timezone)) {
                    date_default_timezone_set("Europe/Berlin");
                }
                $auth->nAuthorizationExpirationTimestamp = (int) date_timestamp_get(new DateTime($expiration));
            }
            if (!empty($amazonAuthorizationId)) {
                $this->db->saveAuthorizationObject($auth);
            }
            if ($immediateCapture && !empty($amazonAuthorizationId)) {
// in case of immediate capture, save the capture object as well now
                $captureIdList = $authorizationDetails['IdList']['member'];
                if (!is_array($captureIdList) && !empty($captureIdList)) {
                    $captureIdList = array($captureIdList);
                }
                foreach ($captureIdList as $capid) {
                    $cap = new stdClass();
                    $cap->cCaptureId = $capid;
                    $cap->cAuthorizationId = $amazonAuthorizationId;
                    $cap->cCaptureStatus = S360_LPA_STATUS_PENDING;
                    $cap->cCaptureStatusReason = '';
                    $cap->fCaptureAmount = $auth->fCapturedAmount;
                    $cap->cCaptureCurrencyCode = $auth->cCapturedCurrencyCode;
                    $cap->fRefundedAmount = 0;
                    $cap->cRefundedCurrencyCode = $cap->cCaptureCurrencyCode;
                    $cap->bSandbox = (int) $config['sandbox'];
                    $this->db->saveCaptureObject($cap);
                }
            }
            return $auth;
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: AuthorizationRequest fehlgeschlagen:' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw $ex;
            }
            return;
        }
    }

    /*
     * Captures an amount priorly authorized.
     */

    public function capture($authid, $amount = NULL, $throwException = false) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $auth = $this->db->getAuthorization($authid);
        /**
         * Safety check: only open authorization may be captured upon.
         * @Improvement: Check if Authorization has changed in the meantime.
         */
        if ($auth->cAuthorizationStatus !== S360_LPA_STATUS_OPEN) {
            $error = "LPA: LPA-Payment-Fehler: CaptureRequest gegen nicht offene Authorization {$auth->cAuthorizationId} versucht.";
            Jtllog::writeLog($error, JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw new Exception($error);
            }
            return;
        }
        $order = $this->db->getOrder($auth->cOrderReferenceId, true);
        if (is_null($amount)) {
            $amount = $auth->fAuthorizationAmount;
        }
        $captureParameters = array(
            'merchant_id' => $config['merchant_id'],
            'seller_capture_note' => $order->bestellung->cBestellNr,
            'amazon_authorization_id' => $auth->cAuthorizationId,
            'capture_reference_id' => $order->cOrderReferenceId . "-C-" . time(),
            'capture_amount' => (string) $amount,
            'currency_code' => $auth->cAuthorizationCurrencyCode
        );

        try {
            $captureResponseWrapper = $client->capture($captureParameters);
            $captureResponseWrapper = $captureResponseWrapper->toArray();
            if (isset($captureResponseWrapper['Error'])) {
                if ($throwException) {
                    throw new Exception($captureResponseWrapper['Error']['Message']);
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: CaptureRequest fehlgeschlagen:' . $captureResponseWrapper['Error']['Message'], JTLLOG_LEVEL_ERROR);
                    return;
                }
            } else {
                $captureResponseWrapper = $captureResponseWrapper['CaptureResult'];
            }
            $captureDetails = $captureResponseWrapper['CaptureDetails'];
            $cap = new stdClass();
            $cap->cCaptureId = $captureDetails['AmazonCaptureId'];
            $cap->fCaptureAmount = (float) $captureDetails['CaptureAmount']['Amount'];
            $cap->cCaptureCurrencyCode = $captureDetails['CaptureAmount']['CurrencyCode'];
            $cap->cCaptureStatus = $captureDetails['CaptureStatus']['State'];
            $cap->cCaptureStatusReason = isset($captureDetails['CaptureStatus']['ReasonCode']) ? $captureDetails['CaptureStatus']['ReasonCode'] : '';
            $cap->fRefundedAmount = (float) $captureDetails['RefundedAmount']['Amount'];
            $cap->cRefundedCurrencyCode = $captureDetails['RefundedAmount']['CurrencyCode'];
            $cap->bSandbox = (int) $auth->bSandbox;
            $cap->cAuthorizationId = $auth->cAuthorizationId;

            /*
             * A capture may DIRECTLY return in 3 states: complete, pending or declined.
             * To have our status handler correctly handle a capture that was completed or declined here, we force the state to pending.
             * That way, the following IPN (or CRON) will update the state to completed and do the necessary DB changes of the JTL database.
             */
            $cap->cCaptureStatus = S360_LPA_STATUS_PENDING;
            $cap->cCaptureStatusReason = '';

            if (!empty($cap->cCaptureId)) {
                $this->db->saveCaptureObject($cap);
            }
            return $cap;
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: CaptureRequest fehlgeschlagen:' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw $ex;
            }
            return;
        }
    }

    public function authorizeAndCapture($orid) {
        /*
         * Special function that authorizes and captures after another. Note that this is not the same as an immediate capture.
         * This function is used, e.g. when a capture processing has failed and the parent authorization is not open anymore.
         */
        $newAuth = $this->authorize($orid, false, 0); // trigger synchronous authorization
        if (!empty($newAuth) && !empty($newAuth->cAuthorizationId)) {
            if ($newAuth->cAuthorizationStatus === S360_LPA_STATUS_OPEN) {
// success, try to cap.
                $newCap = $this->capture($newAuth->cAuthorizationId);
                if (empty($newCap) || empty($newCap->cCaptureId)) {
                    Jtllog::writeLog("LPA: LPA-Payment-Fehler: AuthorizeAndCapture fehlgeschlagen, CAPTURE nicht erzeugt.", JTLLOG_LEVEL_ERROR);
                } else {
                    Jtllog::writeLog("LPA: LPA-Payment-Fehler: AuthorizeAndCapture erfolgreich. Authorization: {$newAuth->cAuthorizationId}, Capture: {$newCap->cCaptureId}", JTLLOG_LEVEL_DEBUG);
                }
            } else {
                Jtllog::writeLog("LPA: LPA-Payment-Fehler: AuthorizeAndCapture fehlgeschlagen, Authorization {$newAuth->cAuthorizationId} nicht OPEN.", JTLLOG_LEVEL_ERROR);
            }
        } else {
            Jtllog::writeLog("LPA: LPA-Payment-Fehler: AuthorizeAndCapture fehlgeschlagen, keine Authorization erzeugt.", JTLLOG_LEVEL_ERROR);
        }
    }

    public function refund($capid, $amount = NULL, $throwException = false) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $cap = $this->db->getCapture($capid);
        $auth = $this->db->getAuthorization($cap->cAuthorizationId);
        $order = $this->db->getOrder($auth->cOrderReferenceId, true);
        /*
         * Safety check: only completed captures may be refunded.
         */
        if ($cap->cCaptureStatus !== S360_LPA_STATUS_COMPLETED) {
            $error = "LPA: LPA-Payment-Fehler: RefundRequest gegen nicht vollständigen Capture {$cap->cCaptureId} versucht.";
            Jtllog::writeLog($error, JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw new Exception($error);
            }
            return;
        }
        if (is_null($amount)) {
            $amount = $cap->fCaptureAmount;
        }
        $refundParameters = array(
            'merchant_id' => $config['merchant_id'],
            'seller_refund_note' => $order->bestellung->cBestellNr,
            'amazon_capture_id' => $cap->cCaptureId,
            'refund_reference_id' => $order->cOrderReferenceId . "-R-" . time(),
            'refund_amount' => (string) $amount,
            'currency_code' => $cap->cCaptureCurrencyCode
        );

        try {
            $refundResponseWrapper = $client->refund($refundParameters);
            $refundResponseWrapper = $refundResponseWrapper->toArray();
            if (isset($refundResponseWrapper['Error'])) {
                if ($throwException) {
                    throw new Exception($refundResponseWrapper['Error']['Message']);
                } else {
                    Jtllog::writeLog('LPA: LPA-Payment-Fehler: RefundRequest fehlgeschlagen:' . $refundResponseWrapper['Error']['Message'], JTLLOG_LEVEL_ERROR);
                    return;
                }
            } else {
                $refundResponseWrapper = $refundResponseWrapper['RefundResult'];
            }
            $refundDetails = $refundResponseWrapper['RefundDetails'];
            $ref = new stdClass();
            $ref->cRefundId = $refundDetails['AmazonRefundId'];
            $ref->fRefundAmount = (float) $refundDetails['RefundAmount']['Amount'];
            $ref->cRefundCurrencyCode = $refundDetails['RefundAmount']['CurrencyCode'];
            $ref->cRefundStatus = $refundDetails['RefundStatus']['State'];
            $ref->cRefundStatusReason = isset($refundDetails['RefundStatus']['ReasonCode']) ? $refundDetails['RefundStatus']['ReasonCode'] : '';
            $ref->cRefundType = $refundDetails['RefundType'];
            $ref->bSandbox = (int) $cap->bSandbox;
            $ref->cCaptureId = $cap->cCaptureId;
            if (!empty($ref->cRefundId)) {
                $this->db->saveRefundObject($ref);
            }
            return $ref;
        } catch (Exception $ex) {
            Jtllog::writeLog('LPA: LPA-Payment-Fehler: RefundRequest fehlgeschlagen:' . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
            if ($throwException) {
                throw $ex;
            } else {
                return;
            }
        }
    }

    public function getRemoteOrderReferenceDetails($orid) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $getOrderReferenceDetailsParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_order_reference_id' => $orid
        );
// exponential backoff
        $retries = 0;
        $success = false;
        while (!$success) {
            $result = $client->getOrderReferenceDetails($getOrderReferenceDetailsParameter);
            $result = $result->toArray();
            if (isset($result['Error'])) {
                if ($retries < S360_LPA_BACKOFF_MAX_RETRIES && $result['Error']['Code'] === 'RequestThrottled') {
                    $timeout = pow(2, $retries) * 1000000 + rand(1000, 1000000);
                    if ($this->mode === S360_LPA_ADAPTER_MODE_BACKEND) {
                        // add an additional timeout for backend functionality to reduce the likelihood of blocking frontend calls
                        $timeout = $timeout + $this->additionalBackendBackoff;
                    }
                    usleep($timeout);
                    $retries = $retries + 1;
                    continue;
                } else {
                    throw new Exception($result['Error']['Message']);
                }
            }
            $success = true;
        }
        $result = $result['GetOrderReferenceDetailsResult']['OrderReferenceDetails'];
        return $result;
    }

    public function getRemoteAuthorizationDetails($authid) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $getAuthorizationDetailsParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_authorization_id' => $authid
        );
// exponential backoff
        $retries = 0;
        $success = false;
        while (!$success) {
            $result = $client->getAuthorizationDetails($getAuthorizationDetailsParameter);
            $result = $result->toArray();
            if (isset($result['Error'])) {
                if ($retries < S360_LPA_BACKOFF_MAX_RETRIES && $result['Error']['Code'] === 'RequestThrottled') {
                    $timeout = pow(2, $retries) * 1000000 + rand(1000, 1000000);
                    if ($this->mode === S360_LPA_ADAPTER_MODE_BACKEND) {
                        // add an additional timeout for backend functionality to reduce the likelihood of blocking frontend calls
                        $timeout = $timeout + $this->additionalBackendBackoff;
                    }
                    usleep($timeout);
                    $retries = $retries + 1;
                    continue;
                } else {
                    throw new Exception($result['Error']['Message']);
                }
            }
            $success = true;
        }
        $result = $result['GetAuthorizationDetailsResult']['AuthorizationDetails'];
        return $result;
    }

    public function getRemoteCaptureDetails($capid) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $getCaptureDetailsParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_capture_id' => $capid
        );
// exponential backoff
        $retries = 0;
        $success = false;
        while (!$success) {
            $result = $client->getCaptureDetails($getCaptureDetailsParameter);
            $result = $result->toArray();
            if (isset($result['Error'])) {
                if ($retries < S360_LPA_BACKOFF_MAX_RETRIES && $result['Error']['Code'] === 'RequestThrottled') {
                    $timeout = pow(2, $retries) * 1000000 + rand(1000, 1000000);
                    if ($this->mode === S360_LPA_ADAPTER_MODE_BACKEND) {
                        // add an additional timeout for backend functionality to reduce the likelihood of blocking frontend calls
                        $timeout = $timeout + $this->additionalBackendBackoff;
                    }
                    usleep($timeout);
                    $retries = $retries + 1;
                    continue;
                } else {
                    throw new Exception($result['Error']['Message']);
                }
            }
            $success = true;
        }
        $result = $result['GetCaptureDetailsResult']['CaptureDetails'];
        return $result;
    }

    public function getRemoteRefundDetails($refid) {
        $config = $this->controller->getConfig();
        $client = $this->controller->getClient();
        $getRefundDetailsParameter = array(
            'merchant_id' => $config['merchant_id'],
            'amazon_refund_id' => $refid
        );
// exponential backoff
        $retries = 0;
        $success = false;
        while (!$success) {

            $result = $client->getRefundDetails($getRefundDetailsParameter);
            $result = $result->toArray();
            if (isset($result['Error'])) {
                if ($retries < S360_LPA_BACKOFF_MAX_RETRIES && $result['Error']['Code'] === 'RequestThrottled') {
                    $timeout = pow(2, $retries) * 1000000 + rand(1000, 1000000);
                    if ($this->mode === S360_LPA_ADAPTER_MODE_BACKEND) {
                        // add an additional timeout for backend functionality to reduce the likelihood of blocking frontend calls
                        $timeout = $timeout + $this->additionalBackendBackoff;
                    }
                    usleep($timeout);
                    $retries = $retries + 1;
                    continue;
                } else {
                    throw new Exception($result['Error']['Message']);
                }
            }
            $success = true;
        }
        $result = $result['GetRefundDetailsResult']['RefundDetails'];
        return $result;
    }

    public function informBuyerSoftDecline($orid) {
        try {
            $oMail = new stdClass();
            $oMail->tkunde = $this->db->getKundeForOrder($orid);
// add Order-Object so it can be used in the mail template, i.e. {$oPluginMail->order->bestellung->cBestellNr}
            $oMail->order = $this->db->getOrder($orid, true);
            sendeMail("kPlugin_" . $this->oPlugin->kPlugin . "_softdecline", $oMail);
        } catch (Exception $ex) {
            Jtllog::writeLog("LPA: LPA-Fehler: E-Mail (Soft-Decline) für Order {$orid} konnte nicht versandt werden: " . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
        }
    }

    public function informBuyerHardDecline($orid) {
        try {
            $oMail = new stdClass();
            $oMail->tkunde = $this->db->getKundeForOrder($orid);
// add Order-Object so it can be used in the mail template, i.e. {$oPluginMail->order->bestellung->cBestellNr}
            $oMail->order = $this->db->getOrder($orid, true);
            sendeMail("kPlugin_" . $this->oPlugin->kPlugin . "_harddecline", $oMail);
        } catch (Exception $ex) {
            Jtllog::writeLog("LPA: LPA-Fehler: E-Mail (Hard-Decline) für Order {$orid} konnte nicht versandt werden: " . $ex->getMessage(), JTLLOG_LEVEL_ERROR);
        }
    }

}
