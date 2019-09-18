<?php

/*
 * Solution 360 GmbH
 *
 * Triggers the synchronization of Payment-Objects with Amazon.
 */
require_once(dirname(__FILE__) . '/lib/lpa_includes.php');
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
if (!$oPlugin || $oPlugin->kPlugin == 0) {
    header("HTTP/1.1 503 Service Unavailable");
    exit(0);
}
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAStatusHandler.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");

$database = new LPADatabase();
$adapter = new LPAAdapter(S360_LPA_ADAPTER_MODE_BACKEND);
$handler = new LPAStatusHandler(S360_LPA_ADAPTER_MODE_BACKEND);


/*
 * First check if enough time has passed since the last run.
 */
$interval = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_JTL_CONFIG . ' WHERE cName LIKE "' . S360_LPA_CONFKEY_ADVANCED_POLLINTERVAL . '"', 1);
$interval = (int) $interval->cWert;

$result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CRON . ' WHERE cCronId LIKE "LPA-CRON" LIMIT 1', 1);
$now = time();
$runNow = false;
if (!empty($result)) {
    $lastRunTimestamp = $result->nLastRunTimestamp;
    if (!empty($lastRunTimestamp)) {
        if ($now > ($lastRunTimestamp + $interval) || !empty($_GET['force'])) {
            // enough time has passed, run again
            Shop::DB()->query('UPDATE ' . S360_LPA_TABLE_CRON . ' SET nLastRunTimestamp=' . $now . ' WHERE cCronId LIKE "LPA-CRON"', 4);
            $runNow = true;
        } else {
            // not enough time has passed, don't run
            Jtllog::writeLog('LPA: Cron: Intervall noch nicht abgelaufen. Wird nicht ausgeführt.', JTLLOG_LEVEL_DEBUG);
        }
    } else {
        Shop::DB()->query('UPDATE ' . S360_LPA_TABLE_CRON . ' SET nLastRunTimestamp=' . $now . ' WHERE cCronId LIKE "LPA-CRON"', 4);
        $runNow = true;
    }
} else {
    Shop::DB()->query('INSERT INTO ' . S360_LPA_TABLE_CRON . ' (cCronId, nLastRunTimestamp) VALUES ("LPA-CRON", ' . $now . ')', 4);
    $runNow = true;
}

if(!$runNow) {
    return;
}


$orders = $database->getOrders(false, array(S360_LPA_STATUS_OPEN, S360_LPA_STATUS_SUSPENDED));
$ordersCount = count($orders);

Jtllog::writeLog("LPA: Cron gestartet. Verarbeite {$ordersCount} Bestellungen.", JTLLOG_LEVEL_DEBUG);

/*
 * Now work through all payment objects we know.
 *
 * Computation is done by "parent object first" logic in a DFS pattern whereas each order is the root of a payment object tree.
 * "Parent object first" ensures that the object in the database has the up-to-date state needed for its child elements to be handled properly.
 *
 * The cron job does not handle any objects that are in a FINAL state in the database to prevent unnecessary overhead.
 */
foreach ($orders as $order) {
    try {
        lpaCronSyncOrderReference($order->cOrderReferenceId, $database, $adapter, $handler);
    } catch (Exception $ex) {
        $orid = $order->cOrderReferenceId;
        Jtllog::writeLog("LPA: Cron: Exception bei Order {$orid}:" . $ex->getMessage());
    }
}

function lpaCronSyncOrderReference($orid, $database, $adapter, $handler) {
    if (!$database->isKnownId($orid, 'order')) {
        Jtllog::writeLog('LPA: Cron: Unbekannte Order ID "' . $orid .'" wird übersprungen', JTLLOG_LEVEL_DEBUG);
        return;
    }
    if ($database->inFinalState($orid, 'order')) {
        return;
    }
    Jtllog::writeLog("LPA: Cron: Verarbeite Order {$orid}", JTLLOG_LEVEL_DEBUG);
    $orderDetails = $adapter->getRemoteOrderReferenceDetails($orid);
    $handler->handleOrderReferenceDetails($orderDetails);
    $authIdList = $orderDetails['IdList']['member'];
    if(!is_array($authIdList) && !empty($authIdList)) {
        $authIdList = array($authIdList);
    }
    foreach ($authIdList as $authId) {
        lpaCronSyncAuthorization($authId, $database, $adapter, $handler);
    }
}

function lpaCronSyncAuthorization($authid, $database, $adapter, $handler) {
    if (!$database->isKnownId($authid, 'auth')) {
        Jtllog::writeLog('LPA: Cron: Unbekannte Authorization ID "' . $authid .'" wird übersprungen', JTLLOG_LEVEL_DEBUG);
        return;
    }
    if ($database->inFinalState($authid, 'auth')) {
        return;
    }
    Jtllog::writeLog("LPA: Cron: Verarbeite Authorization {$authid}", JTLLOG_LEVEL_DEBUG);
    $authDetails = $adapter->getRemoteAuthorizationDetails($authid);
    $handler->handleAuthorizationDetails($authDetails);
    $capIdList = $authDetails['IdList']['member'];
    if(!is_array($capIdList) && !empty($capIdList)) {
        $capIdList = array($capIdList);
    }
    foreach ($capIdList as $capId) {
        lpaCronSyncCapture($capId, $database, $adapter, $handler);
    }
}

function lpaCronSyncCapture($capid, $database, $adapter, $handler) {
    if (!$database->isKnownId($capid, 'cap')) {
        Jtllog::writeLog('LPA: Cron: Unbekannte Capture ID "' . $capid .'" wird übersprungen', JTLLOG_LEVEL_DEBUG);
        return;
    }
    if ($database->inFinalState($capid, 'cap')) {
        return;
    }
    Jtllog::writeLog("LPA: Cron: Verarbeite Capture {$capid}", JTLLOG_LEVEL_DEBUG);
    $capDetails = $adapter->getRemoteCaptureDetails($capid);
    $handler->handleCaptureDetails($capDetails);
    $refIdList = $capDetails['IdList']['member'];
    if(!is_array($refIdList) && !empty($refIdList)) {
        $refIdList = array($refIdList);
    }
    foreach ($refIdList as $refId) {
        lpaCronSyncRefund($refId, $database, $adapter, $handler);
    }
}

function lpaCronSyncRefund($refid, $database, $adapter, $handler) {
    if (!$database->isKnownId($refid, 'refund')) {
        Jtllog::writeLog('LPA: Cron: Unbekannte Refund ID "' . $refid .'" wird übersprungen', JTLLOG_LEVEL_DEBUG);
        return;
    }
    if ($database->inFinalState($refid, 'refund')) {
        return;
    }
    Jtllog::writeLog("LPA: Cron: Verarbeite Refund {$refid}", JTLLOG_LEVEL_DEBUG);
    $refDetails = $adapter->getRemoteRefundDetails($refid);
    $handler->handleRefundDetails($refDetails);
}
