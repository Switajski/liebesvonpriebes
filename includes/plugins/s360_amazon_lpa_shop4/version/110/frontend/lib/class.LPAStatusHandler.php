<?php

/*
 * Solution 360 GmbH
 *
 * Handles status transition logic and required actions, if needed.
 */
require_once('lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once('lpa_defines.php');
require_once('class.LPAController.php');
require_once('class.LPADatabase.php');
require_once('class.LPAAdapter.php');
require_once('class.LPAAuthorization.php');
require_once('class.LPACapture.php');
require_once('class.LPAOrderReference.php');
require_once('class.LPARefund.php');

class LPAStatusHandler {

    var $db;
    var $controller;
    var $adapter;

    public function __construct($adapterMode = 'frontend') {
        $this->db = new LPADatabase();
        $this->controller = new LPAController();
        $this->adapter = new LPAAdapter($adapterMode);
    }

    /*
     * Handle Amazon *Details-Objects.
     */

    public function handleOrderReferenceDetails($details) {
        $newOrder = new LPAOrderReference($details);
        return $this->handleOrderReference($newOrder);
    }

    public function handleAuthorizationDetails($details) {
        $newAuth = new LPAAuthorization($details);
        return $this->handleAuthorization($newAuth);
    }

    public function handleCaptureDetails($details) {
        $newCap = new LPACapture($details);
        return $this->handleCapture($newCap);
    }

    public function handleRefundDetails($details) {
        $newRef = new LPARefund($details);
        return $this->handleRefund($newRef);
    }

    /*
     * Handle payment objects, not caring where they came from.
     */

    public function handleOrderReference($newOrder) {
        $oldOrder = $this->db->getOrder($newOrder->cOrderReferenceId, false);
        if (!$oldOrder) {
            // Error state - unknown authorization id, but Amazon can not create payment objects that we do not know about.
            Jtllog::writeLog("LPA: Unbekannte OrderReference-ID empfangen: {$newOrder->cOrderReferenceId}", JTLLOG_LEVEL_DEBUG);
            return S360_LPA_ERROR_UNKNOWN_ID;
        }

        if ($this->checkUnchanged($oldOrder, $newOrder, 'order')) {
            // unchanged payment object, ignore it.
            Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - unverändert, Update wird ignoriert.", JTLLOG_LEVEL_DEBUG);
            return;
        }

        // adjust kbestellung
        $newOrder->kBestellung = (int) $oldOrder->kBestellung;
        // adjust sandbox flag, it is per definition the same for the same payment object.
        $newOrder->bSandbox = (int) $oldOrder->bSandbox;

        switch ($newOrder->cOrderStatus) {
            case S360_LPA_STATUS_DRAFT:
                /*
                 * An Order Reference object is in the Draft state prior to be being confirmed by calling the ConfirmOrderReference operation.
                 * There should actually be no orders in the draft state in our database, at any given moment.
                 */
                $this->db->saveOrderObject($newOrder);
                Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: DRAFT", JTLLOG_LEVEL_DEBUG);
                break;
            case S360_LPA_STATUS_OPEN:
                /*
                 * An Order Reference object moves to the Open state after it is confirmed by calling the ConfirmOrderReference operation.
                 * Authorizations can only be requested on an order reference that is in the Open state.
                 * This is the default state orders have when created in our database.
                 */
                if ($oldOrder->cOrderStatus === S360_LPA_STATUS_SUSPENDED) {
                    /*
                     * The order was supended before, we must now trigger the authorization process again.
                     */
                    Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: OPEN (nach SUSPENDED), Erneute Autorisierung wird versucht.", JTLLOG_LEVEL_DEBUG);
                    $auths = $this->db->getAuthorizationsForOrder($newOrder->cOrderReferenceId);
                    if (!empty($auths) && is_array($auths)) {
                        $newAuth = $auths[0];

                        // create a new authorization (this will automatically be saved to the database, as well)
                        $realNewAuth = $this->adapter->authorize($newOrder->cOrderReferenceId, (boolean) $newAuth->bCaptureNow, S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT, NULL, false, true);

                        // if the new authorization we received is now OPEN (or even closed/max-captures processed), we also have to set the order ready for retrieval from WAWI
                        // essentially we do the same as we would when an authorization switches from PENDING to OPEN/CLOSED
                        if ($realNewAuth->cAuthorizationStatus === S360_LPA_STATUS_OPEN ||
                                ($realNewAuth->cAuthorizationStatus === S360_LPA_STATUS_CLOSED && $realNewAuth->cAuthorizationStatusReason === S360_LPA_REASON_MAX_CAPTURES_PROCESSED)) {
                            $billingaddress = $this->adapter->getRemoteAuthorizationDetails($realNewAuth->cAuthorizationId);
                            $billingaddress = $billingaddress['AuthorizationBillingAddress'];
                            $rechnungsAdresse = null;
                            if (!empty($billingaddress)) {
                                $rechnungsAdresse = new stdClass();

                                $aName_arr = explode(" ", utf8_decode($billingaddress['Name']), 2);
                                if (count($aName_arr) === 2) {
                                    $rechnungsAdresse->cVorname = $aName_arr[0];
                                    $rechnungsAdresse->cNachname = $aName_arr[1];
                                } else {
                                    $rechnungsAdresse->cNachname = utf8_decode($billingaddress['Name']);
                                }

                                $cStrasse_arr = array();
                                if (isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) && strlen(trim($billingaddress['AddressLine1'])) > 0) {
                                    $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine1']);
                                }
                                if (isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) && strlen(trim($billingaddress['AddressLine2'])) > 0) {
                                    $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine2']);
                                }
                                if (isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) && strlen(trim($billingaddress['AddressLine3'])) > 0) {
                                    $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine3']);
                                }

                                if (count($cStrasse_arr) === 1) {
                                    $rechnungsAdresse->cStrasse = $cStrasse_arr[0];
                                } else {
                                    $rechnungsAdresse->cFirma = isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) ? utf8_decode($billingaddress['AddressLine1']) : '';
                                    $rechnungsAdresse->cStrasse = isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) ? utf8_decode($billingaddress['AddressLine2']) : '';
                                    $rechnungsAdresse->cAdressZusatz = isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) ? utf8_decode($billingaddress['AddressLine3']) : '';
                                }

                                /*
                                 * heuristic correction for the street and streetnumber in the shop backend. same is done by wawi sync when
                                 * addresses come from the wawi (see function extractStreet in syncinclude.php)
                                 */
                                $cData_arr = explode(' ', $rechnungsAdresse->cStrasse);
                                if (count($cData_arr) > 1) {
                                    $rechnungsAdresse->cHausnummer = $cData_arr[count($cData_arr) - 1];
                                    unset($cData_arr[count($cData_arr) - 1]);
                                    $rechnungsAdresse->cStrasse = implode(' ', $cData_arr);
                                }

                                $rechnungsAdresse->cOrt = utf8_decode($billingaddress['City']);
                                $rechnungsAdresse->cPLZ = utf8_decode($billingaddress['PostalCode']);
                                $rechnungsAdresse->cLand = ISO2land($billingaddress['CountryCode']);
                            }
                            $this->db->setBestellungAuthorized($realNewAuth->cOrderReferenceId, $rechnungsAdresse);
                            Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: OPEN, wurde neu autorisiert, und zur Abholung in Wawi gesetzt.", JTLLOG_LEVEL_DEBUG);
                        } else {
                            // Authorization is not in a capturable state ... this will be handled later with an update to the authorization
                            Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: OPEN, wurde neu autorisiert, aber noch nicht zur Abholung in Wawi gesetzt.", JTLLOG_LEVEL_DEBUG);
                        }
                    } else {
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Fehler: Kann nicht neu autorisieren, da keine alte Autorisierung existiert!", JTLLOG_LEVEL_ERROR);
                    }
                }
                $this->db->saveOrderObject($newOrder);
                Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: OPEN", JTLLOG_LEVEL_DEBUG);
                break;
            case S360_LPA_STATUS_SUSPENDED:
                /*
                 * An Order Reference object moves to the Suspended state when there are problems with the payment method that is preventing Amazon from processing the authorizations.
                 * You cannot request new authorizations when the order reference is in the Suspended state. However, you still can capture existing authorizations and apply refunds to captures.
                 * You should request another form of payment from the buyer if the Order Reference object moves to the Suspended state.
                 * We inform the user already on the state switch of the respective AUTHORIZATION object, therefore we do nothing special here.
                 * There is also only ONE reason for a Suspended Order which is InvalidPaymentMethod, i.e. we cannot differentiate between soft decline and hard decline here.
                 */
                switch ($newOrder->cOrderStatusReason) {
                    case S360_LPA_REASON_INVALID_PAYMENT_METHOD:
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: SUSPENDED", JTLLOG_LEVEL_NOTICE);
                        break;
                    default:
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: SUSPENDED with unknown Reason: {$newOrder->cOrderStatusReason}", JTLLOG_LEVEL_ERROR);
                        break;
                }
                break;
            case S360_LPA_STATUS_CANCELED:
                /*
                 * The Order Reference object can be canceled by calling the CancelOrderReference operation or it can be canceled by Amazon.
                 * You can only cancel an order if no money has been captured or charged on the order reference.
                 * Any pending authorizations will be canceled and no new payment operations will be allowed in the future.
                 *
                 * This is equivalent to a STORNO operation.
                 */
                switch ($newOrder->cOrderStatusReason) {
                    case S360_LPA_REASON_SELLER_CANCELED:
                        /*
                         * You have explicitly canceled the order reference by calling the CancelOrderReference operation.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CANCELED (SELLER)", JTLLOG_LEVEL_DEBUG);
                        break;
                    case S360_LPA_REASON_STALE:
                        /*
                         * You did not confirm the order reference by calling the ConfirmOrderReference operation within the allowed period of 3 hours.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CANCELED (STALE)", JTLLOG_LEVEL_NOTICE);
                        break;
                    case S360_LPA_REASON_AMAZON_CANCELED:
                        /*
                         * Amazon has canceled the order reference.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CANCELED (AMAZON)", JTLLOG_LEVEL_NOTICE);
                        break;
                    default:
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CANCELED with unknown Reason: {$newOrder->cOrderStatusReason}", JTLLOG_LEVEL_ERROR);
                        break;
                }
                $this->db->setBestellungCanceled($newOrder->cOrderReferenceId);
                break;
            case S360_LPA_STATUS_CLOSED:
                /*
                 * The Order Reference object can be closed by calling the CloseOrderReference operation or it can be closed by Amazon.
                 * You cannot request new authorizations when the order reference is in the Closed state.
                 * Captures on existing authorizations are still allowed. Refunds on captures are also still allowed.
                 */
                switch ($newOrder->cOrderStatusReason) {
                    case S360_LPA_REASON_EXPIRED:
                        /*
                         * You can only authorize funds on the buyer?s payment instrument up to 180 days after the order reference is created.
                         * After this, Amazon will mark the order reference as closed and new authorizations will not be allowed.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: EXPIRED - Die Bestellung kann nicht mehr über Amazon gezahlt werden!", JTLLOG_LEVEL_NOTICE);
                        break;
                    case S360_LPA_REASON_MAX_AMOUNT_CHARGED:
                        /*
                         * You are allowed to capture the following amounts before the order reference will be closed by Amazon:
                         * In the US: up to 15% or $75 (whichever is less) above the order reference amount.
                         * In the UK: up to 15% or £75 (whichever is less) above the order reference amount.
                         * In Germany: up to 15% or ?75 (whichever is less) above the order reference amount.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: MAX_AMOUNT_CHARGED - Der Maximalbetrag wurde eingezogen.", JTLLOG_LEVEL_DEBUG);
                        break;
                    case S360_LPA_REASON_MAX_AUTHORIZATIONS_CAPTURED:
                        /*
                         * You have fully or partially captured 10 authorizations.
                         * After this, the order reference will be closed by Amazon.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: MAX_AUTHORIZATIONS_CAPTURED - Die maximale Anzahl Autorisierungen wurde erreicht.", JTLLOG_LEVEL_NOTICE);
                        break;
                    case S360_LPA_REASON_AMAZON_CLOSED:
                        /*
                         *  Amazon has closed the order reference due to a failed internal validation or due to an A-to-z claim being decided against you.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CLOSED (AMAZON) - Amazon hat die Bestellung geschlossen.", JTLLOG_LEVEL_NOTICE);
                        break;
                    case S360_LPA_REASON_SELLER_CLOSED:
                        /*
                         * You have explicitly closed the order reference by calling the CloseOrderReference operation.
                         * You can specify the reason for closure in the ClosureReason request parameter of the CloseOrderReference operation.
                         */
                        $this->db->saveOrderObject($newOrder);
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CLOSED (SELLER) - Verkäufer hat die Bestellung geschlossen.", JTLLOG_LEVEL_DEBUG);
                        break;
                    default:
                        Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} - Status: CLOSED mit unbekanntem Grund: {$newOrder->cOrderStatusReason}", JTLLOG_LEVEL_ERROR);
                        break;
                }
                $this->db->setBestellungClosed($newOrder->cOrderReferenceId);
                break;
            default:
                // Unknown state - this is NOT saved to the database
                Jtllog::writeLog("LPA: OrderReference-ID {$newOrder->cOrderReferenceId} hat unbekannten Status: {$newOrder->cOrderStatus}", JTLLOG_LEVEL_ERROR);
                break;
        }
    }

    public function handleAuthorization($newAuth) {
        /*
         * First load existing authorization object
         */
        $oldAuth = $this->db->getAuthorization($newAuth->cAuthorizationId);
        if (!$oldAuth) {
            // Error state - unknown authorization id, but Amazon can not create payment objects that we do not know about.
            Jtllog::writeLog("LPA: Unbekannte Authorization-ID empfangen: {$newAuth->cAuthorizationId}", JTLLOG_LEVEL_DEBUG);
            return S360_LPA_ERROR_UNKNOWN_ID;
        }

        if ($this->checkUnchanged($oldAuth, $newAuth, 'auth')) {
            // unchanged payment object, ignore it.
            Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - unverändert, Update wird ignoriert.", JTLLOG_LEVEL_DEBUG);
            return;
        }

        // adjust order reference id
        $newAuth->cOrderReferenceId = $oldAuth->cOrderReferenceId;
        // adjust sandbox flag, it is per definition the same for the same payment object.
        $newAuth->bSandbox = (int) $oldAuth->bSandbox;

        /*
         * Check the state of the new authorization object.
         */
        switch ($newAuth->cAuthorizationStatus) {
            case S360_LPA_STATUS_PENDING:
                /*
                 *  This is the initial state of an authorization. If we see this, we basically ignore it.
                 */
                $this->db->saveAuthorizationObject($newAuth);
                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: PENDING", JTLLOG_LEVEL_DEBUG);
                break;
            case S360_LPA_STATUS_OPEN:
                /*
                 * The authorization is open. If it was PENDING before, we are now allowed to execute Captures against it.
                 * The capturing of payments is initiated by the DELIVERED state from the WaWi, we just save the new state.
                 *
                 * Note that at this point we can set the billing address for the order - it is part of the authorization details!
                 *
                 * Note that the capture might happen even without our intervention if the authorization was done with CaptureNow = 1.
                 * If a capture is done, we will receive a separate notification for the capture.
                 *
                 * So, depending on the CaptureNow-value we signal the state change for the WaWi
                 */
                $this->db->saveAuthorizationObject($newAuth);
                $billingaddress = $this->adapter->getRemoteAuthorizationDetails($newAuth->cAuthorizationId);
                $billingaddress = $billingaddress['AuthorizationBillingAddress'];
                $rechnungsAdresse = null;
                if (!empty($billingaddress)) {
                    $rechnungsAdresse = new stdClass();

                    $aName_arr = explode(" ", utf8_decode($billingaddress['Name']), 2);
                    if (count($aName_arr) === 2) {
                        $rechnungsAdresse->cVorname = $aName_arr[0];
                        $rechnungsAdresse->cNachname = $aName_arr[1];
                    } else {
                        $rechnungsAdresse->cNachname = utf8_decode($billingaddress['Name']);
                    }

                    $cStrasse_arr = array();
                    if (isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) && strlen(trim($billingaddress['AddressLine1'])) > 0) {
                        $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine1']);
                    }
                    if (isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) && strlen(trim($billingaddress['AddressLine2'])) > 0) {
                        $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine2']);
                    }
                    if (isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) && strlen(trim($billingaddress['AddressLine3'])) > 0) {
                        $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine3']);
                    }

                    if (count($cStrasse_arr) === 1) {
                        $rechnungsAdresse->cStrasse = $cStrasse_arr[0];
                    } else {
                        $rechnungsAdresse->cFirma = isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) ? utf8_decode($billingaddress['AddressLine1']) : '';
                        $rechnungsAdresse->cStrasse = isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) ? utf8_decode($billingaddress['AddressLine2']) : '';
                        $rechnungsAdresse->cAdressZusatz = isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) ? utf8_decode($billingaddress['AddressLine3']) : '';
                    }


                    /*
                     * heuristic correction for the street and streetnumber in the shop backend. same is done by wawi sync when
                     * addresses come from the wawi (see function extractStreet in syncinclude.php)
                     */
                    $cData_arr = explode(' ', $rechnungsAdresse->cStrasse);
                    if (count($cData_arr) > 1) {
                        $rechnungsAdresse->cHausnummer = $cData_arr[count($cData_arr) - 1];
                        unset($cData_arr[count($cData_arr) - 1]);
                        $rechnungsAdresse->cStrasse = implode(' ', $cData_arr);
                    }

                    $rechnungsAdresse->cOrt = utf8_decode($billingaddress['City']);
                    $rechnungsAdresse->cPLZ = utf8_decode($billingaddress['PostalCode']);
                    $rechnungsAdresse->cLand = ISO2land($billingaddress['CountryCode']);
                }
                $this->db->setBestellungAuthorized($newAuth->cOrderReferenceId, $rechnungsAdresse);
                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: OPEN", JTLLOG_LEVEL_DEBUG);
                break;
            case S360_LPA_STATUS_DECLINED:
                /*
                 * The authorization was declined. 
                 */
                if ($oldAuth->cAuthorizationStatus !== S360_LPA_STATUS_DECLINED) {
                    switch ($newAuth->cAuthorizationStatusReason) {
                        // note that the same auhorization can not be logically in different declined states, so each of these actions
                        // should only be taken the first time that reason is encounted - after that, the authorization is forever DECLINED.
                        case S360_LPA_REASON_INVALID_PAYMENT_METHOD:
                            // soft decline, inform the buyer
                            Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: Zahlungsart durch Amazon abgelehnt. Informiere Kunde.", JTLLOG_LEVEL_NOTICE);
                            $this->adapter->informBuyerSoftDecline($newAuth->cOrderReferenceId);
                            break;
                        case S360_LPA_REASON_AMAZON_REJECTED:
                            // hard decline, inform the buyer, cancel the order here and in the database.
                            Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: Bezahlung komplett von Amazon abgelehnt. Informiere Kunde.", JTLLOG_LEVEL_NOTICE);
                            $this->adapter->informBuyerHardDecline($newAuth->cOrderReferenceId);
                            $this->db->setBestellungCanceled($newAuth->cOrderReferenceId);
                            $this->adapter->cancelOrder($newAuth->cOrderReferenceId, 'Hard Decline');
                            break;
                        case S360_LPA_REASON_PROCESSING_FAILURE:
                            // If the order reference is in OPEN state, we should retry the authorization for the order.
                            $order = $this->db->getOrder($newAuth->cOrderReferenceId);
                            if ($order->cOrderStatus === S360_LPA_STATUS_OPEN) {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: Verarbeitungsfehler, Versuche erneute Autorisierung.", JTLLOG_LEVEL_NOTICE);
                                $this->adapter->authorize($order->cOrderReferenceId, (boolean) $newAuth->bCaptureNow);
                            } else {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: Verarbeitungsfehler, kann NICHT autorisiert werden, da Order {$order->cOrderReferenceId} in Status {$order->cOrderStatus} ist.", JTLLOG_LEVEL_ERROR);
                            }
                            break;
                        case S360_LPA_REASON_TRANSACTION_TIMED_OUT:
                            // the last authorization failed with a timeout, let's retry the authorization for the order.
                            $order = $this->db->getOrder($newAuth->cOrderReferenceId);
                            if ($order->cOrderStatus === S360_LPA_STATUS_OPEN) {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Zeitüberschreitung, Versuche erneute Autorisierung.", JTLLOG_LEVEL_NOTICE);
                                $this->adapter->authorize($order->cOrderReferenceId, (boolean) $newAuth->bCaptureNow);
                            } else {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Zeitüberschreitung, kann NICHT autorisiert werden, da Order {$order->cOrderReferenceId} in Status {$order->cOrderStatus} ist.", JTLLOG_LEVEL_ERROR);
                            }
                            break;
                    }
                }
                $this->db->saveAuthorizationObject($newAuth);
                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: DECLINED {$newAuth->cAuthorizationStatusReason}", JTLLOG_LEVEL_NOTICE);
                break;
            case S360_LPA_STATUS_CLOSED:
                /*
                 * The authorization was closed. That can be either the normal procedure or be an error case, depending on the reason.
                 */
                if ($oldAuth->cAuthorizationStatus !== S360_LPA_STATUS_CLOSED) {
                    switch ($newAuth->cAuthorizationStatusReason) {
                        case S360_LPA_REASON_EXPIRED_UNUSED:
                            $order = $this->db->getOrder($newAuth->cOrderReferenceId);
                            if ($order->cOrderStatus === S360_LPA_STATUS_OPEN) {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Autorisierung abgelaufen! Versuche, erneute Autorisierung einzuholen.", JTLLOG_LEVEL_NOTICE);
                                $this->adapter->authorize($order->cOrderReferenceId, (boolean) $newAuth->bCaptureNow);
                            } else {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Autorisierung abgelaufen! Kann NICHT mehr autorisiert werden, da Order {$order->cOrderReferenceId} in Status {$order->cOrderStatus} ist.", JTLLOG_LEVEL_ERROR);
                            }
                            break;
                        case S360_LPA_REASON_MAX_CAPTURES_PROCESSED:
                            if ($oldAuth->cAuthorizationStatus === S360_LPA_STATUS_PENDING) {
                                /*
                                 * In case of immediate captures with an asynchronous authorization there may be a transition from PENDING to CLOSED instantly. 
                                 * This is essentially the same as if the authorization was now OPEN, i.e. the order must be made available for the Wawi.
                                 * 
                                 * As such we get the billing address and set the relevant data.
                                 */
                                $billingaddress = $this->adapter->getRemoteAuthorizationDetails($newAuth->cAuthorizationId);
                                $billingaddress = $billingaddress['AuthorizationBillingAddress'];
                                $rechnungsAdresse = null;
                                if (!empty($billingaddress)) {
                                    $rechnungsAdresse = new stdClass();

                                    $aName_arr = explode(" ", utf8_decode($billingaddress['Name']), 2);
                                    if (count($aName_arr) === 2) {
                                        $rechnungsAdresse->cVorname = $aName_arr[0];
                                        $rechnungsAdresse->cNachname = $aName_arr[1];
                                    } else {
                                        $rechnungsAdresse->cNachname = utf8_decode($billingaddress['Name']);
                                    }

                                    $cStrasse_arr = array();

                                    if (isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) && strlen(trim($billingaddress['AddressLine1'])) > 0) {
                                        $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine1']);
                                    }
                                    if (isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) && strlen(trim($billingaddress['AddressLine2'])) > 0) {
                                        $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine2']);
                                    }
                                    if (isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) && strlen(trim($billingaddress['AddressLine3'])) > 0) {
                                        $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine3']);
                                    }

                                    if (count($cStrasse_arr) === 1) {
                                        $rechnungsAdresse->cStrasse = $cStrasse_arr[0];
                                    } else {
                                        $rechnungsAdresse->cFirma = isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) ? utf8_decode($billingaddress['AddressLine1']) : '';
                                        $rechnungsAdresse->cStrasse = isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) ? utf8_decode($billingaddress['AddressLine2']) : '';
                                        $rechnungsAdresse->cAdressZusatz = isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) ? utf8_decode($billingaddress['AddressLine3']) : '';
                                    }


                                    /*
                                     * heuristic correction for the street and streetnumber in the shop backend. same is done by wawi sync when
                                     * addresses come from the wawi (see function extractStreet in syncinclude.php)
                                     */
                                    $cData_arr = explode(' ', $rechnungsAdresse->cStrasse);
                                    if (count($cData_arr) > 1) {
                                        $rechnungsAdresse->cHausnummer = $cData_arr[count($cData_arr) - 1];
                                        unset($cData_arr[count($cData_arr) - 1]);
                                        $rechnungsAdresse->cStrasse = implode(' ', $cData_arr);
                                    }

                                    $rechnungsAdresse->cOrt = utf8_decode($billingaddress['City']);
                                    $rechnungsAdresse->cPLZ = utf8_decode($billingaddress['PostalCode']);
                                    $rechnungsAdresse->cLand = ISO2land($billingaddress['CountryCode']);
                                }
                                $this->db->setBestellungAuthorized($newAuth->cOrderReferenceId, $rechnungsAdresse);
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: Autorisierung abgeschlossen, da Zahlungseinzug SOFORT durchgeführt wurde.", JTLLOG_LEVEL_DEBUG);
                            } else {
                                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Autorisierung abgeschlossen, da Zahlungseinzug durchgeführt wurde.", JTLLOG_LEVEL_DEBUG);
                            }
                            break;
                        case S360_LPA_REASON_AMAZON_CLOSED:
                            Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Autorisierung durch Amazon geschlossen.", JTLLOG_LEVEL_ERROR);
                            break;
                        case S360_LPA_REASON_SELLER_CLOSED:
                            Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Autorisierung durch Händler geschlossen.", JTLLOG_LEVEL_NOTICE);
                            break;
                        case S360_LPA_REASON_ORDER_REFERENCE_CANCELED:
                            Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status:  Autorisierung geschlossen, da Bestellung abgebrochen wurde.", JTLLOG_LEVEL_NOTICE);
                            break;
                    }
                }
                $this->db->saveAuthorizationObject($newAuth);
                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} - Status: CLOSED", JTLLOG_LEVEL_DEBUG);
                break;
            default:
                // Unknown state - this is NOT saved to the database
                Jtllog::writeLog("LPA: Authorization-ID {$newAuth->cAuthorizationId} hat unbekannten Status: {$newAuth->cAuthorizationStatus}", JTLLOG_LEVEL_ERROR);
                break;
        }
    }

    public function handleCapture($newCap) {
        /*
         * First load existing payment object
         */
        $oldCap = $this->db->getCapture($newCap->cCaptureId);
        if (!$oldCap) {
            // Error state - unknown payment object id, but Amazon can not create payment objects that we do not know about, unless these are created in the MWS backend directly.
            Jtllog::writeLog("LPA: Unbekannte Capture-ID empfangen: {$newCap->cCaptureId}", JTLLOG_LEVEL_DEBUG);
            return S360_LPA_ERROR_UNKNOWN_ID;
        }

        if ($this->checkUnchanged($oldCap, $newCap, 'cap')) {
            // unchanged payment object, ignore it.
            Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - unverändert, Update wird ignoriert.", JTLLOG_LEVEL_DEBUG);
            return;
        }

        // adjust authorization id (we know it from the database, but amazon does not provide it)
        $newCap->cAuthorizationId = $oldCap->cAuthorizationId;
        // adjust sandbox flag, it is per definition the same for the same payment object.
        $newCap->bSandbox = (int) $oldCap->bSandbox;

        switch ($newCap->cCaptureStatus) {
            case S360_LPA_STATUS_PENDING:
                /*
                 * A Capture object is in the Pending state until it is processed by Amazon.
                 * Nothing specific to do.
                 */
                $this->db->saveCaptureObject($newCap);
                Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - Status: PENDING", JTLLOG_LEVEL_DEBUG);
                break;
            case S360_LPA_STATUS_DECLINED:
                /*
                 * You must submit a capture against an authorization within 30 days (two days for the Sandbox environment). However, we strongly recommend requesting a capture within 7 days of authorization.
                 * If the capture is declined. you can still request a new authorization and capture if the order reference is in the Open state.
                 */
                $auth = $this->db->getAuthorization($newCap->cAuthorizationId);
                switch ($newCap->cCaptureStatusReason) {
                    case S360_LPA_REASON_AMAZON_REJECTED:
                        /*
                         *  Amazon has rejected the capture. You should only retry the capture if the authorization is in the Open state.
                         */
                        if ($auth->cAuthorizationStatus === S360_LPA_STATUS_OPEN) {
                            Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - Status: REJECTED, versuche erneut Capture für Authorization {$auth->cAuthorizationId} anzufordern.", JTLLOG_LEVEL_NOTICE);
                            $this->adapter->capture($auth->cAuthorizationId);
                        } else {
                            Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - Status: REJECTED, kann kein Capture anfordern, da Authorization {$auth->cAuthorizationId} nicht mehr OPEN ist.", JTLLOG_LEVEL_ERROR);
                        }
                        $this->db->saveCaptureObject($newCap);
                        break;
                    case S360_LPA_REASON_PROCESSING_FAILURE:
                        /*
                         * Amazon could not process the transaction due to an internal processing error. You should only retry the capture if the authorization is in the Open state.
                         * Otherwise, you should request a new authorization and then call Capture on it.
                         */
                        if ($auth->cAuthorizationStatus === S360_LPA_STATUS_OPEN) {
                            Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - Status: PROCESSING FAILURE, versuche erneut Capture für Authorization {$auth->cAuthorizationId} anzufordern.", JTLLOG_LEVEL_NOTICE);
                            $this->adapter->capture($auth->cAuthorizationId);
                        } else {
                            Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - Status: PROCESSING FAILURE, kann kein Capture anfordern, da Authorization {$auth->cAuthorizationId} nicht mehr OPEN ist. Erneute Authorization wird angefordert.", JTLLOG_LEVEL_NOTICE);
                            $this->adapter->authorizeAndCapture($auth->cOrderReferenceId);
                        }
                        $this->db->saveCaptureObject($newCap);
                        break;
                    default:
                        Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} ist DECLINED mit unbekanntem Grund: {$newCap->cCaptureStatusReason}", JTLLOG_LEVEL_ERROR);
                        break;
                }
                break;
            case S360_LPA_STATUS_COMPLETED:
                /*
                 * The Capture object request has been processed and funds will be moved to your account in the next disbursement cycle.
                 * Now, refunds are possible against the capture object.
                 */
                Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} - Status: COMPLETE.", JTLLOG_LEVEL_DEBUG);
                $this->db->saveCaptureObject($newCap);
                $auth = $this->db->getAuthorization($newCap->cAuthorizationId);
                $this->db->setBestellungCaptured($auth->cOrderReferenceId);
                break;
            case S360_LPA_STATUS_CLOSED:
                /*
                 * When the Capture object is moved to the Closed state, you cannot request refunds against it.
                 */
                switch ($newCap->cCaptureStatusReason) {
                    case S360_LPA_REASON_MAX_AMOUNT_REFUNDED:
                        /*
                         * You have already refunded the following amounts, including any A-to-z claims and chargebacks that you were responsible for:
                         * In the US: up to 15% or $75 (whichever is less) above the captured amount.
                         * In the UK: up to 15% or £75 (whichever is less) above the captured amount.
                         * In Germany: up to 15% or ?75 (whichever is less) above the captured amount.
                         */
                        Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} ist CLOSED - maximaler Rückerstattungsbetrag erreicht.", JTLLOG_LEVEL_DEBUG);
                        $this->db->saveCaptureObject($newCap);
                        break;
                    case S360_LPA_REASON_MAX_REFUNDS_PROCESSED:
                        /*
                         * You have already submitted 10 refunds for this Capture object.
                         */
                        Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} ist CLOSED - maximale Anzahl Rückerstattungen erreicht.", JTLLOG_LEVEL_DEBUG);
                        $this->db->saveCaptureObject($newCap);
                        break;
                    case S360_LPA_REASON_AMAZON_CLOSED:
                        /*
                         * Amazon has closed the capture due to a problem with your account or with the buyer's account.
                         */
                        Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} ist CLOSED durch Amazon! Bitte prüfen!", JTLLOG_LEVEL_ERROR);
                        $this->db->saveCaptureObject($newCap);
                        break;
                    default:
                        Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} ist CLOSED mit unbekanntem Grund: {$newCap->cCaptureStatusReason}", JTLLOG_LEVEL_ERROR);
                        break;
                }
                break;
            default:
                Jtllog::writeLog("LPA: Capture-ID {$newCap->cCaptureId} hat unbekannten Status: {$newCap->cCaptureStatus}", JTLLOG_LEVEL_ERROR);
                break;
        }
    }

    public function handleRefund($newRef) {
        /*
         * First load existing payment object
         */
        $oldRef = $this->db->getRefund($newRef->cRefundId);
        if (!$oldRef) {
            // Error state - unknown payment object id, but Amazon can not create payment objects that we do not know about.
            Jtllog::writeLog("LPA: Unbekannte Refund-ID empfangen: {$newRef->cRefundId}", JTLLOG_LEVEL_DEBUG);
            return S360_LPA_ERROR_UNKNOWN_ID;
        }

        if ($this->checkUnchanged($oldRef, $newRef, 'refund')) {
            // unchanged payment object, ignore it.
            Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} - unverändert, Update wird ignoriert.", JTLLOG_LEVEL_DEBUG);
            return;
        }

        // adjust capture id (we know it from the database, but amazon does not provide it)
        $newRef->cCaptureId = $oldRef->cCaptureId;
        // adjust sandbox flag, it is per definition the same for the same payment object.
        $newRef->bSandbox = (int) $oldRef->bSandbox;

        switch ($newRef->cRefundStatus) {
            case S360_LPA_STATUS_PENDING:
                /*
                 * A Refund object is in the Pending state until it is processed by Amazon.
                 */
                $this->db->saveRefundObject($newRef);
                break;
            case S360_LPA_STATUS_DECLINED:
                /*
                 * Amazon has declined the refund because the maximum amount has been refunded on the capture.
                 */
                $cap = $this->db->getCapture($newRef->cCaptureId);
                switch ($newRef->cRefundStatusReason) {
                    case S360_LPA_REASON_AMAZON_REJECTED:
                        Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} - Status: AMAZON REJECTED. Bitte erstatten Sie das Geld auf andere Weise.", JTLLOG_LEVEL_ERROR);
                        $this->db->saveRefundObject($newRef);
                        break;
                    case S360_LPA_REASON_PROCESSING_FAILURE:
                        if ($cap->cCaptureStatus === S360_LPA_STATUS_COMPLETED) {
                            Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} - Status: PROCESSING FAILURE. Versuche erneuten Refund.", JTLLOG_LEVEL_ERROR);
                            $this->adapter->refund($cap->cCaptureId);
                        } else {
                            Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} - Status: PROCESSING FAILURE. Kann nicht ausgeführt werden, da Capture {$cap->cCaptureId} nicht im Status COMPLETED ist. Bitte erstatten Sie das Geld auf andere Weise.", JTLLOG_LEVEL_ERROR);
                        }
                        $this->db->saveRefundObject($newRef);
                        break;
                    default:
                        Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} ist DECLINED mit unbekanntem Grund: {$newRef->cCaptureStatusReason}", JTLLOG_LEVEL_ERROR);
                        break;
                }
                break;
            case S360_LPA_STATUS_COMPLETED:
                /*
                 * The refund request has been processed and funds will be returned to the buyer.
                 */
                $this->db->saveRefundObject($newRef);
                Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} - Status: COMPLETED - Betrag wurde erstattet.", JTLLOG_LEVEL_DEBUG);
                break;
            default:
                Jtllog::writeLog("LPA: Refund-ID {$newRef->cRefundId} hat unbekannten Status: {$newRef->cRefundStatus}", JTLLOG_LEVEL_ERROR);
                break;
        }
    }

    private function checkUnchanged($old, $new, $type) {
        $result = true;
        switch ($type) {
            case 'order':
                $result = $result && ($old->cOrderStatus === $new->cOrderStatus);
                $result = $result && ($old->cOrderStatusReason === $new->cOrderStatusReason || (empty($old->cOrderStatusReason) && empty($new->cOrderStatusReason)));
                break;
            case 'auth':
                $result = $result && ($old->cAuthorizationStatus === $new->cAuthorizationStatus);
                $result = $result && ($old->cAuthorizationStatusReason === $new->cAuthorizationStatusReason || (empty($old->cAuthorizationStatusReason) && empty($new->cAuthorizationStatusReason)));
                $result = $result && ((float) $old->fCapturedAmount === (float) $new->fCapturedAmount);
                $result = $result && ($old->cCapturedCurrencyCode === $new->cCapturedCurrencyCode);
                break;
            case 'cap':
                $result = $result && ($old->cCaptureStatus === $new->cCaptureStatus);
                $result = $result && ($old->cCaptureStatusReason === $new->cCaptureStatusReason || (empty($old->cCaptureStatusReason) && empty($new->cCaptureStatusReason)));
                $result = $result && ((float) $old->fRefundedAmount === (float) $new->fRefundedAmount);
                $result = $result && ($old->cRefundedCurrencyCode === $new->cRefundedCurrencyCode);
                break;
            case 'refund':
                $result = $result && ($old->cRefundStatus === $new->cRefundStatus);
                $result = $result && ($old->cRefundStatusReason === $new->cRefundStatusReason || (empty($old->cRefundStatusReason) && empty($new->cRefundStatusReason)));
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }

}
