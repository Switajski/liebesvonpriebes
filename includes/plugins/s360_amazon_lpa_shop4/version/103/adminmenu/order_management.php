<?php

/*
 * Solution 360 GmbH
 */
global $oPlugin;
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAStatusHandler.php');

/*
 * Perform actions...
 */
if (!empty($_POST['lpa_management'])) {
    try {

        $adapter = new LPAAdapter();
        $handler = new LPAStatusHandler();
        $action = StringHandler::filterXSS($_POST['lpa_action']);
        $type = StringHandler::filterXSS($_POST['lpa_type']);
        $id = StringHandler::filterXSS($_POST['lpa_id']);
        $orid = StringHandler::filterXSS($_POST['lpa_orid']);
        $amount = 0;
        if (!empty($_POST['lpa_amount'])) {
            $amount = floatval(StringHandler::filterXSS($_POST['lpa_amount']));
        }

        switch ($type) {
            case 'order':
                switch ($action) {
                    case 'authorize':
                        $adapter->authorize($id, false, S360_LPA_AUTHORIZATION_TIMEOUT_DEFAULT, $amount, true);
                        break;
                    case 'cancel':
                        $adapter->cancelOrder($id, 'Manual Cancel by Seller', true);
                        break;
                    case 'close':
                        $adapter->closeOrder($id, 'Manual Close by Seller', true);
                        break;
                    case 'refresh':
                        // refresh will be triggered in any case
                        break;
                }
                break;
            case 'auth':
                switch ($action) {
                    case 'capture':
                        $adapter->capture($id, $amount, true);
                        break;
                    case 'close':
                        $adapter->closeAuthorization($id, 'Manual Close by Seller', true);
                        break;
                }
                break;
            case 'cap':
                switch ($action) {
                    case 'refund':
                        $adapter->refund($id, $amount, true);
                        break;
                }
                break;
        }
        lpaRefreshOrderReference($orid, $adapter, $handler);
    } catch (Exception $ex) {
        Jtllog::writeLog('LPA: LPA-Management-Fehler: ' . $ex->getMessage());
        Shop::Smarty()->assign('lpa_error_message', $ex->getMessage());
    }
}

$db = new LPADatabase();
/*
 * Render data to interface.
 */
$lpa_management = array();
$now = time();

$orders = $db->getOrders();
if (!empty($orders)) {
    foreach ($orders as &$order) {
        $state = 'success';
        $allowedActions = array();
        switch ($order->cOrderStatus) {
            case S360_LPA_STATUS_OPEN:
                $state = 'warning';
                $allowedActions = array('cancel', 'close', 'authorize');
                break;
            case S360_LPA_STATUS_CANCELED:
                $state = 'danger';
                break;
            case S360_LPA_STATUS_SUSPENDED:
                $state = 'danger';
                $allowedActions = array('cancel', 'close');
                break;
            case S360_LPA_STATUS_CLOSED:
                $state = 'success';
                break;
            default:
                $state = 'warning';
                break;
        }
        $order->displayState = $state;
        $order->actions = $allowedActions;
        if (!empty($order->nOrderExpirationTimestamp)) {
            $timediff = $order->nOrderExpirationTimestamp - $now;
            $order->expiresIn = $timediff;
            $order->expiresOnString = date('H:m:s d.m.Y', $order->nOrderExpirationTimestamp);
        } else {
            $order->expiresIn = 0;
            $order->expiresOnString = 'unbekannt';
        }
    }
    $orders = array_reverse($orders);
}
$lpa_management['orders'] = $orders;

$auths = $db->getAuthorizations();
if (!empty($auths)) {
    foreach ($auths as &$auth) {
        $state = 'ok';
        $allowedActions = array();
        switch ($auth->cAuthorizationStatus) {
            case S360_LPA_STATUS_PENDING:
                $state = 'warning';
                $allowedActions = array('close');
                break;
            case S360_LPA_STATUS_OPEN:
                $state = 'warning';
                $allowedActions = array('close', 'capture');
                break;
            case S360_LPA_STATUS_DECLINED:
                $state = 'danger';
                break;
            case S360_LPA_STATUS_CLOSED:
                $state = 'success';
                break;
            default:
                $state = 'warning';
                break;
        }
        $auth->displayState = $state;
        $auth->actions = $allowedActions;
        if (!empty($auth->nAuthorizationExpirationTimestamp)) {
            $timediff = $auth->nAuthorizationExpirationTimestamp - $now;
            $auth->expiresIn = $timediff;
            $auth->expiresOnString = date('H:m:s d.m.Y', $auth->nAuthorizationExpirationTimestamp);
        } else {
            $auth->expiresIn = 0;
            $auth->expiresOnString = 'unbekannt';
        }
    }
    $auths = array_reverse($auths);
}
$lpa_management['authorizations'] = $auths;

$caps = $db->getCaptures();
if (!empty($caps)) {
    foreach ($caps as &$cap) {
        $state = 'success';
        $allowedActions = array();
        switch ($cap->cCaptureStatus) {
            case S360_LPA_STATUS_PENDING:
                $state = 'warning';
                break;
            case S360_LPA_STATUS_DECLINED:
                $state = 'danger';
                break;
            case S360_LPA_STATUS_COMPLETED:
                $state = 'success';
                $allowedActions = array('refund');
                break;
            case S360_LPA_STATUS_CLOSED:
                $state = 'success';
                break;
            default:
                $state = 'warning';
                break;
        }
        $cap->displayState = $state;
        $cap->actions = $allowedActions;
    }
    $caps = array_reverse($caps);
}
$lpa_management['captures'] = $caps;

$refunds = $db->getRefunds();
if (!empty($refunds)) {
    foreach ($refunds as &$refund) {
        $state = 'success';
        switch ($refund->cRefundStatus) {
            case S360_LPA_STATUS_DECLINED:
                $state = 'danger';
                break;
            case S360_LPA_STATUS_COMPLETED:
                $state = 'success';
                break;
            default:
                $state = 'warning';
                break;
        }
        $refund->displayState = $state;
    }
    $refunds = array_reverse($refunds);
}
$lpa_management['refunds'] = $refunds;

Shop::Smarty()->assign('pluginAdminUrl', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&');
Shop::Smarty()->assign('lpa_management', $lpa_management);

// Workaround for failed save attempts on first opening
Shop::Smarty()->assign('s360_jtl_token', getTokenInput());

Shop::Smarty()->display($oPlugin->cAdminmenuPfad . "template/order_management.tpl");

function lpaRefreshOrderReference($orid, $adapter, $handler) {
    $orderDetails = $adapter->getRemoteOrderReferenceDetails($orid);
    $handler->handleOrderReferenceDetails($orderDetails);
    $authIdList = isset($orderDetails['IdList']['member']) ? $orderDetails['IdList']['member'] : array();
    if (!is_array($authIdList) && !empty($authIdList)) {
        $authIdList = array($authIdList);
    }
    foreach ($authIdList as $authId) {
        lpaRefreshAuthorization($authId, $adapter, $handler);
    }
}

function lpaRefreshAuthorization($authid, $adapter, $handler) {
    $authDetails = $adapter->getRemoteAuthorizationDetails($authid);
    $handler->handleAuthorizationDetails($authDetails);
    $capIdList = isset($authDetails['IdList']['member']) ? $authDetails['IdList']['member'] : array();
    if (!is_array($capIdList) && !empty($capIdList)) {
        $capIdList = array($capIdList);
    }
    foreach ($capIdList as $capId) {
        lpaRefreshCapture($capId, $adapter, $handler);
    }
}

function lpaRefreshCapture($capid, $adapter, $handler) {
    $capDetails = $adapter->getRemoteCaptureDetails($capid);
    $handler->handleCaptureDetails($capDetails);
    $refIdList = isset($capDetails['IdList']['member']) ? $capDetails['IdList']['member'] : array();
    if (!is_array($refIdList) && !empty($refIdList)) {
        $refIdList = array($refIdList);
    }
    foreach ($refIdList as $refId) {
        lpaRefreshRefund($refId, $adapter, $handler);
    }
}

function lpaRefreshRefund($refid, $adapter, $handler) {
    $refDetails = $adapter->getRemoteRefundDetails($refid);
    $handler->handleRefundDetails($refDetails);
}
