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

        $adapter = new LPAAdapter(S360_LPA_ADAPTER_MODE_BACKEND);
        $handler = new LPAStatusHandler(S360_LPA_ADAPTER_MODE_BACKEND);
        $action = StringHandler::filterXSS($_POST['lpa_action']);
        $type = StringHandler::filterXSS($_POST['lpa_type']);
        $id = StringHandler::filterXSS($_POST['lpa_id']);
        $orid = StringHandler::filterXSS($_POST['lpa_orid']);
        $amount = 0;
        if (!empty($_POST['lpa_amount'])) {
            $amount = (float) StringHandler::filterXSS($_POST['lpa_amount']);
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

/**
 * Initialize pagination or set it by Request Parameters
 */
$pagination = array("page" => 0, "count" => S360_LPA_ADMIN_ORDERS_PER_PAGE);
if (!isset($_SESSION['lpa_admin_pagination']) && (isset($_REQUEST['page']))) {
    $_SESSION['lpa_admin_pagination'] = array();
}
if (isset($_REQUEST['page'])) {
    $_SESSION['lpa_admin_pagination']['page'] = (int) $_REQUEST['page'];
}
if (isset($_SESSION['lpa_admin_pagination'])) {
    if (isset($_SESSION['lpa_admin_pagination']['page'])) {
        $pagination['page'] = $_SESSION['lpa_admin_pagination']['page'];
    }
} else {
    $_SESSION['lpa_admin_pagination'] = $pagination;
}

$db = new LPADatabase();
/*
 * Render data to interface.
 */
$lpa_management = array();
$lpa_search = array();
$now = time();

$orders = array();
if (isset($_REQUEST['lpa_search']) && isset($_REQUEST['search']) && strlen($_REQUEST['search']) > 0) {
    $search = Shop::DB()->escape($_REQUEST['search']);
    $lpa_search['lastsearch'] = StringHandler::filterXSS($search);

    $result = $db->findOrderById($search);
    if (empty($result)) {
        $result = $db->findOrderByJTLOrderNumber($search);
    }

    if (!empty($result)) {
        $orders[] = $result;
        $lpa_search['lastsearch_success'] = 1;
    } else {
        $lpa_search['lastsearch_success'] = 0;
        $lpa_search['message'] = "Ihre Suche nach '" . StringHandler::filterXSS($search) . "' hat kein Ergebnis geliefert.";
    }
}
if (empty($orders)) {
    // gets orders, limited by the current count; ordered by creation order reversed (newest order first)
    $orders = $db->getOrdersPaged($pagination['page'] * S360_LPA_ADMIN_ORDERS_PER_PAGE, $pagination['count']);
}

$orderIds = array();
if (!empty($orders)) {

    foreach ($orders as &$order) {
        $orderIds[] = $order->cOrderReferenceId;
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
}
$lpa_management['orders'] = $orders;

$auths = $db->getAuthorizationsForOrders($orderIds);
$authIds = array();
if (!empty($auths)) {
    foreach ($auths as &$auth) {
        $authIds[] = $auth->cAuthorizationId;
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

$caps = $db->getCapturesForAuthorizations($authIds);
$capIds = array();
if (!empty($caps)) {
    foreach ($caps as &$cap) {
        $capIds[] = $cap->cCaptureId;
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

$refunds = $db->getRefundsForCaptures($capIds);
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

// setup pagination
$pagination['currentPage'] = $pagination['page'] + 1;
if (empty($orders) || count($orders) < S360_LPA_ADMIN_ORDERS_PER_PAGE) {
    $pagination['nextPage'] = $pagination['page'];
} else {
    $pagination['nextPage'] = $pagination['page'] + 1;
}
if ($pagination['page'] > 0) {
    $pagination['prevPage'] = $pagination['page'] - 1;
} else {
    $pagination['prevPage'] = $pagination['page'];
}

// Perform sanity checks for hint display
$lpa_warnings = array();
if(stripos(Shop::getURL(true), 'https') !== 0) {
    $lpa_warnings[] = 'Ihr Shop ist anscheinend nicht f&uuml;r SSL (https) konfiguriert. Dies verursacht technische Probleme bei der Anzeige der Amazon Pay Widgets.';
}
$sandboxConfigResult = Shop::DB()->select(S360_LPA_TABLE_CONFIG, 'cName', S360_LPA_CONFKEY_ENVIRONMENT);
if(!empty($sandboxConfigResult) && $sandboxConfigResult->cWert === 'sandbox') {
    $lpa_warnings[] = 'Sie haben momentan den Sandbox-Modus aktiviert. Es k&ouml;nnen <b>keine</b> echten Bestellungen/Zahlungen durchgef&uuml;hrt werden! &Auml;ndern Sie den Modus bei den Account-Einstellungen, sobald Sie bereit sind.';
}
if((int) $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_HIDDENBUTTONS_ACTIVE] === 1) {
    $lpa_warnings[] = 'Der Hidden-Button-Modus ist aktiviert. Die Buttons sind daher unsichtbar und nur zu sehen, wenn Sie "?lpa-show" an die jeweilige URL anh&auml;ngen.';
}
if((int) $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_GENERAL_ACTIVE] !== 1) {
    $lpa_warnings [] = 'Das Plugin ist momentan deaktiviert. Sie k&ouml;nnen es bei den allgemeinen Einstellungen aktivieren.';
}
if($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTHMODE] === 'async') {
    $lpa_warnings[] = 'Sie haben momentan den asynchronen Autorisierungsmodus aktiviert. Es wird empfohlen, stattdessen den optimierten Modus zu nutzen. (Sie finden diese Einstellung bei den erweiterten Einstellungen.)';
}
// sanity check the frontendlinks
$languages = Shop::DB()->executeQueryPrepared('SELECT * FROM tsprache', array(), 2);
$expectedSeoValues = array('lpamerge', 'lpacreate', 'lpalogin', 'lpacheckout', 'lpacomplete');
$missingLinks = array();
foreach($languages as $language) {
    if($language->cISO === 'ger') {
        foreach($expectedSeoValues as $seoValue) {
            $res = Shop::DB()->select('tseo', array('cSeo', 'kSprache'), array($seoValue, $language->kSprache));
            if(empty($res)) {
                $missingLinks[] = $seoValue;
            }
        }
    }
    if($language->cISO === 'eng') {
        foreach($expectedSeoValues as $seoValue) {
            $res = Shop::DB()->select('tseo', array('cSeo', 'kSprache'), array($seoValue . '-en', $language->kSprache));
            if(empty($res)) {
                $missingLinks[] = $seoValue;
            }
        }
    }
}
if(count($missingLinks) > 0) {
    $lpa_warnings[] = 'Der Frontendlinks f&uuml;r ' . implode(', ', $missingLinks) . ' fehlen oder sind falsch. Nutzen Sie die "Frontendlinks Aktualisieren"-Funktion bei den Erweiterten Funktionen und &ouml;ffnen Sie das Plugin danach erneut. Falls das Problem bestehen bleibt, wenden Sie sich bitte an die FAQ in der Dokumentation.';
}
$merchantIdConfigResult = Shop::DB()->select(S360_LPA_TABLE_CONFIG, 'cName', S360_LPA_CONFKEY_MERCHANT_ID);
if(empty($merchantIdConfigResult) || $merchantIdConfigResult->cWert === '') {
    $lpa_warnings[] = 'Sie haben noch keine Merchant-ID gesetzt. Bitte konfigurieren Sie Ihre Account-Daten.';
} else {
    try {
        $adapter = new LPAAdapter();
        $accountStatus = $adapter->getRemoteAccountStatus();
        if($accountStatus !== S360_LPA_MERCHANT_ACCOUNT_STATUS_ACTIVE) {
            $lpa_warnings[] = 'Ihr Amazon Pay Account ist nicht im Status "aktiv". Sie k&ouml;nnen das Plugin derzeit nicht produktiv einsetzen.';
        }
    } catch(Exception $e) {
        $lpa_warnings[] = 'Der Accountstatus Ihres Merchant-Accounts konnte aufgrund eines technischen Fehlers nicht abgefragt werden.';
    }
}



Shop::Smarty()->assign('pluginAdminUrl', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&')
        ->assign('lpa_management', $lpa_management)
        ->assign('lpa_pagination', $pagination)
        ->assign('lpa_search', $lpa_search)
        ->assign('s360_jtl_token', getTokenInput()) // Workaround for failed save attempts on first opening
        ->assign('lpa_warnings', $lpa_warnings);

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
