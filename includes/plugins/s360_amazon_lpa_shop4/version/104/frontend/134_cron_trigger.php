<?php

/*
 * Solution 360 GmbH
 *
 * Simply hooked to the "LASTJOBS" Hook on Webshopabgleich, to trigger the cron-job if IPN is deactivated.
 */
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAStatusHandler.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");

if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_GENERAL_ACTIVE] === '0') {
    /*
     * Plugin disabled, do nothing.
     */
    return;
}

if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_USEIPN] === '0') {
    include_once('cron.php');
} else {
    /*
     * If we do use IPNs: 
     * 
     * Fix for Authorizations staying in status "PENDING" for too long.
     * Sometimes, Authorization IPNs might not be received. We try to update all orders
     * which have a PENDING authorization. 
     * The time interval used is the same as the one which is else used for the regular cron job,
     * however the running time stamps of both cron operations are separated.
     */
    try {

        /*
         * First check if enough time has passed since the last run.
         */
        $interval = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_JTL_CONFIG . ' WHERE cName LIKE "' . S360_LPA_CONFKEY_ADVANCED_POLLINTERVAL . '"', 1);
        $interval = (int) $interval->cWert;

        $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CRON . ' WHERE cCronId LIKE "LPA-AUTH-CRON" LIMIT 1', 1);
        $now = time();
        $runNow = false;
        if (!empty($result)) {
            $lastRunTimestamp = $result->nLastRunTimestamp;
            if (!empty($lastRunTimestamp)) {
                if ($now > ($lastRunTimestamp + $interval)) {
                    // enough time has passed, run again
                    Shop::DB()->query('UPDATE ' . S360_LPA_TABLE_CRON . ' SET nLastRunTimestamp=' . $now . ' WHERE cCronId LIKE "LPA-AUTH-CRON"', 4);
                    $runNow = true;
                } else {
                    // not enough time has passed, don't run
                    Jtllog::writeLog('LPA: Authorization-Fix: Intervall noch nicht abgelaufen. Wird nicht ausgeführt.', JTLLOG_LEVEL_DEBUG);
                }
            } else {
                Shop::DB()->query('UPDATE ' . S360_LPA_TABLE_CRON . ' SET nLastRunTimestamp=' . $now . ' WHERE cCronId LIKE "LPA-AUTH-CRON"', 4);
                $runNow = true;
            }
        } else {
            Shop::DB()->query('INSERT INTO ' . S360_LPA_TABLE_CRON . ' (cCronId, nLastRunTimestamp) VALUES ("LPA-AUTH-CRON", ' . $now . ')', 4);
            $runNow = true;
        }

        if (!$runNow) {
            return;
        }

        $database = new LPADatabase();
        $adapter = new LPAAdapter();
        $handler = new LPAStatusHandler();

        // Load all orders without the backing Bestellung information (faster this way)
        $orders = $database->getOrders(false);
        $ordersToRefresh = array();


        // Find all orders that need to be refreshed because of PENDING auths.
        foreach ($orders as $order) {
            $auths = $database->getAuthorizationsForOrder($order->cOrderReferenceId);
            foreach ($auths as $auth) {
                if ($auth->cAuthorizationStatus === S360_LPA_STATUS_PENDING) {
                    // at least one auth belonging to the order is in status PENDING
                    $ordersToRefresh[] = $order;
                    break;
                }
            }
        }
        
        Jtllog::writeLog('LPA: Authorization-Fix: '.count($ordersToRefresh).' Orders gefunden, die PENDING Autorisierungen haben.', JTLLOG_LEVEL_DEBUG);

        foreach ($ordersToRefresh as $order) {
            $orid = $order->cOrderReferenceId;
            $orderDetails = $adapter->getRemoteOrderReferenceDetails($orid);
            $handler->handleOrderReferenceDetails($orderDetails);
            $authIdList = $orderDetails['IdList']['member'];
            if(!is_array($authIdList) && !empty($authIdList)) {
                $authIdList = array($authIdList);
            }
            foreach ($authIdList as $authId) {
                $authDetails = $adapter->getRemoteAuthorizationDetails($authId);
                $handler->handleAuthorizationDetails($authDetails);
                $capIdList = array();
                if(array_key_exists('member', $authDetails['IdList'])) {
                    $capIdList = $authDetails['IdList']['member'];
                }
                if(!is_array($capIdList) && !empty($capIdList)) {
                    $capIdList = array($capIdList);
                }
                foreach ($capIdList as $capId) {
                    $capDetails = $adapter->getRemoteCaptureDetails($capId);
                    $handler->handleCaptureDetails($capDetails);
                    $refIdList = array();
                    if(array_key_exists('member', $capDetails['IdList'])) {
                        $refIdList = $capDetails['IdList']['member'];
                    }                    
                    if(!is_array($refIdList) && !empty($refIdList)) {
                        $refIdList = array($refIdList);
                    }
                    foreach ($refIdList as $refId) {
                        $refDetails = $adapter->getRemoteRefundDetails($refId);
                        $handler->handleRefundDetails($refDetails);
                    }
                }
            }
        }
    } catch (Exception $ex) {
        Jtllog::writeLog("LPA: Authorization-Fix: Fehler beim Update von PENDING Autorisierungen: ".$ex->getMessage(), JTLLOG_LEVEL_NOTICE);
    }
}

