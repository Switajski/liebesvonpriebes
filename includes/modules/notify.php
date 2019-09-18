<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/../../includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

// Debug
define('NO_MODE', 0); // 1 = An / 0 = Aus
define('NO_PFAD', PFAD_LOGFILES . 'notify.log');

$moduleId            = null;
$Sprache             = Shop::DB()->select('tsprache', 'cShopStandard', 'Y');
$Einstellungen       = Shop::getSettings([
    CONF_GLOBAL,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_ZAHLUNGSARTEN
]);
$cEditZahlungHinweis = '';
//Session Hash
$cPh = verifyGPDataString('ph');
$cSh = verifyGPDataString('sh');

executeHook(HOOK_NOTIFY_HASHPARAMETER_DEFINITION);

if (strlen(verifyGPDataString('ph')) === 0 && strlen(verifyGPDataString('externalBDRID')) > 0) {
    $cPh = verifyGPDataString('externalBDRID');
    if ($cPh[0] === '_') {
        $cPh = '';
        $cSh = verifyGPDataString('externalBDRID');
    }
}
// Work around Sofortüberweisung
if (strlen(verifyGPDataString('key')) > 0 && strlen(verifyGPDataString('sid')) > 0) {
    $cPh = verifyGPDataString('sid');
    if (verifyGPDataString('key') === 'sh') {
        $cPh = '';
        $cSh = verifyGPDataString('sid');
    }
}

if (strlen($cSh) > 0) {
    Jtllog::writeLog('Notify SH: ' . print_r($_REQUEST, true), JTLLOG_LEVEL_DEBUG, false, 'Notify');

    if (NO_MODE === 1) {
        writeLog(NO_PFAD, 'Session Hash: ' . $cSh, 1);
    }
    // Load from Session Hash / Session Hash starts with "_"
    $sessionHash    = substr(StringHandler::htmlentities(StringHandler::filterXSS($cSh)), 1);
    $paymentSession = Shop::DB()->select(
        'tzahlungsession',
        'cZahlungsID',
        $sessionHash,
        null,
        null,
        null,
        null,
        false,
        'cSID, kBestellung'
    );
    if ($paymentSession === false) {
        Jtllog::writeLog(
            'Session Hash: ' . $cSh . ' ergab keine Bestellung aus tzahlungsession',
            JTLLOG_LEVEL_ERROR,
            false,
            'Notify'
        );

        die();
    }
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog(
            'Session Hash: ' . $cSh . ' ergab tzahlungsession ' . print_r($paymentSession, true),
            JTLLOG_LEVEL_DEBUG,
            false,
            'Notify'
        );
    }
    if (session_id() !== $paymentSession->cSID) {
        session_destroy();
        session_id($paymentSession->cSID);
        $session = Session::getInstance(true, true);
    } else {
        $session = Session::getInstance(false, false);
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
    // EOS Workaround für Server to Server Kommunikation
    pruefeEOSServerCom($cSh);

    Jtllog::writeLog('Session Hash: ' . $cSh . ' ergab cModulId aus Session: ' .
        (isset($_SESSION['Zahlungsart']->cModulId)
            ? $_SESSION['Zahlungsart']->cModulId
            : '---'),
        JTLLOG_LEVEL_DEBUG,
        false,
        'Notify'
    );
    if (!isset($paymentSession->kBestellung) || !$paymentSession->kBestellung) {
        // Generate fake Order and ask PaymentMethod if order should be finalized
        $order = fakeBestellung();
        include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $paymentMethod = isset($_SESSION['Zahlungsart']->cModulId)
            ? PaymentMethod::create($_SESSION['Zahlungsart']->cModulId)
            : null;
        if ($paymentMethod !== null) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'Session Hash: ' . $cSh . ' ergab Methode: ' . print_r($paymentMethod, true),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'Notify'
                );
            }

            $kPlugin = gibkPluginAuscModulId($_SESSION['Zahlungsart']->cModulId);
            if ($kPlugin > 0) {
                $oPlugin            = new Plugin($kPlugin);
                $GLOBALS['oPlugin'] = $oPlugin;
            }

            if ($paymentMethod->finalizeOrder($order, $sessionHash, $_REQUEST)) {
                Jtllog::writeLog(
                    'Session Hash: ' . $cSh . ' ergab finalizeOrder passed',
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'Notify'
                );

                $order = finalisiereBestellung();
                $session->cleanUp();

                if ($order->kBestellung > 0) {
                    Jtllog::writeLog('tzahlungsession aktualisiert.', JTLLOG_LEVEL_DEBUG, false, 'Notify');
                    $_upd               = new stdClass();
                    $_upd->nBezahlt     = 1;
                    $_upd->dZeitBezahlt = 'now()';
                    $_upd->kBestellung  = (int)$order->kBestellung;
                    Shop::DB()->update('tzahlungsession', 'cZahlungsID', $sessionHash, $_upd);
                    $paymentMethod->handleNotification($order, '_' . $sessionHash, $_REQUEST);
                    if ($paymentMethod->redirectOnPaymentSuccess() === true) {
                        header('Location: ' . $paymentMethod->getReturnURL($order));
                        exit();
                    }
                }
            } else {
                Jtllog::writeLog('finalizeOrder failed -> zurueck zur Zahlungsauswahl.', JTLLOG_LEVEL_DEBUG, false, 'Notify');
                $linkHelper = LinkHelper::getInstance();
                // UOS Work Around
                if ($_SESSION['Zahlungsart']->cModulId === 'za_sofortueberweisung_jtl' ||
                    $paymentMethod->redirectOnCancel() ||
                    strpos($_SESSION['Zahlungsart']->cModulId, 'za_uos_') !== false ||
                    strpos($_SESSION['Zahlungsart']->cModulId, 'za_ut_') !== false
                ) {
                    // Go to 'Edit PaymentMethod' Page
                    $header = 'Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
                        '?editZahlungsart=1';
                    if (strlen($cEditZahlungHinweis) > 0) {
                        $header = 'Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
                            '?editZahlungsart=1&nHinweis=' . $cEditZahlungHinweis;
                    }
                    header($header);
                    exit();
                } else {
                    if (strlen($cEditZahlungHinweis) > 0) {
                        echo $linkHelper->getStaticRoute('bestellvorgang.php') .
                            '?editZahlungsart=1&nHinweis=' . $cEditZahlungHinweis;
                    } else {
                        echo $linkHelper->getStaticRoute('bestellvorgang.php') .
                            '?editZahlungsart=1';
                    }
                }
            }
        }
    } else {
        $order = new Bestellung($paymentSession->kBestellung);
        $order->fuelleBestellung(0);
        include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        Jtllog::writeLog(
            'Session Hash ' . $cSh . ' hat kBestellung. Modul ' .
                $order->Zahlungsart->cModulId . ' wird aufgerufen',
            JTLLOG_LEVEL_DEBUG,
            false,
            'Notify'
        );

        $paymentMethod = PaymentMethod::create($order->Zahlungsart->cModulId);
        $paymentMethod->handleNotification($order, '_' . $sessionHash, $_REQUEST);
        if ($paymentMethod->redirectOnPaymentSuccess() === true) {
            header('Location: ' . $paymentMethod->getReturnURL($order));
            exit();
        }
    }

    die();
}

/*** Payment Hash ***/

$session = Session::getInstance();
if (strlen($cPh) > 0) {
    if (Jtllog::doLog(JTLLOG_LEVEL_NOTICE)) {
        Jtllog::writeLog(print_r($_REQUEST, true), JTLLOG_LEVEL_NOTICE, false, 'Notify');
    }
    if (NO_MODE === 1) {
        writeLog(NO_PFAD, 'Payment Hash ' . $cPh, 1);
    }
    // Payment Hash
    $paymentId   = Shop::DB()->executeQueryPrepared(
        "SELECT ZID.kBestellung, ZA.cModulId
            FROM tzahlungsid ZID
            LEFT JOIN tzahlungsart ZA
                ON ZA.kZahlungsart = ZID.kZahlungsart
            WHERE ZID.cId = :hash",
        ['hash' => StringHandler::htmlentities(StringHandler::filterXSS($cPh))],
        1
    );

    if ($paymentId === false) {
        if (NO_MODE === 1) {
            writeLog(NO_PFAD, 'Payment Hash ' . $cPh . ' ergab keine Bestellung aus tzahlungsid.', 1);
        }
        die(); // Payment Hash does not exist
    }
    if (NO_MODE === 1) {
        writeLog(NO_PFAD, 'Payment Hash ' . $cPh . ' ergab ' . print_r($paymentId, true), 1);
    }
    // Load Order
    $moduleId = $paymentId->cModulId;
    $order    = new Bestellung($paymentId->kBestellung);
    $order->fuelleBestellung(0);

    if (NO_MODE === 1) {
        writeLog(NO_PFAD, 'Payment Hash ' . $cPh . ' ergab ' . print_r($order, true), 1);
    }
}
if ($moduleId !== null) {
    // Let PaymentMethod handle Notification
    include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
    $paymentMethod = PaymentMethod::create($moduleId);
    if ($paymentMethod !== null) {
        if (NO_MODE === 1) {
            writeLog(NO_PFAD, 'Payment Hash ' . $cPh . ' ergab ' . print_r($paymentMethod, true), 1);
        }
        $paymentHash = Shop::DB()->escape(StringHandler::htmlentities(StringHandler::filterXSS($cPh)));
        $paymentMethod->handleNotification($order, $paymentHash, $_REQUEST);
        if ($paymentMethod->redirectOnPaymentSuccess() === true) {
            header('Location: ' . $paymentMethod->getReturnURL($order));
            exit();
        }
    }
}
