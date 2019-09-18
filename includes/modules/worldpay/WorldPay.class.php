<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

// Mode
define('WP_MODE', 0); // 1 = Test / 0 = Live
// Debug
define('WP_D_MODE', 1); // 1 = An / 0 = Aus
define('WP_D_PFAD', PFAD_LOGFILES . 'worldpay.log');
// Sandbox
define('WP_URL_TEST', 'https://select-test.wp3.rbsworldpay.com/wcc/purchase');
// Live
define('WP_URL_LIVE', 'https://select.wp3.rbsworldpay.com/wcc/purchase');

/**
 * WorldPay
 */
class WorldPay extends PaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'WorldPay';
        $this->caption = 'WorldPay';

        return $this;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        if ($order->fGesamtsummeKundenwaehrung > 0) {
            $worldpay_id    = $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_worldpay_id'];
            $worldpay_modus = $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_worldpay_modus'];

            $cURL    = WP_MODE === 1 ? WP_URL_TEST : WP_URL_LIVE;
            $cHidden = WP_MODE === 1 ? '<input type=hidden name="name" value="AUTHORISED">' : "";

            $cPost = '
                <form action="' . $cURL . '" name="BuyForm" method="POST">
                    <input type="hidden" name="instId"  value="' . $worldpay_id . '">
                    <input type="hidden" name="cartId" value="' . Shop::Lang()->get('order', 'global') . ' ' . $order->cBestellNr . '">
                    <input type="hidden" name="currency" value="' . $order->Waehrung->cISO . '">
                    <input type="hidden" name="amount"  value="' . round($order->fGesamtsummeKundenwaehrung, 2) . '">
                    <input type="hidden" name="desc" value="' . $order->cBestellNr . '">
                    <input type="hidden" name="testMode" value="' . $worldpay_modus . '">
                    ' . $cHidden . '
                    <input type="hidden" name="name" value="">
                    <input type="hidden" name="adress" value="">
                    <input type="hidden" name="postcode" value="">
                    <input type="hidden" name="country" value="">
                    <input type="hidden" name="tel" value="">
                    <input type="hidden" name="fax" value="">
                    <input type="hidden" name="email" value="">

                    <input type="submit" value="' . Shop::Lang()->get('payWithWorldpay', 'global') . '">
                </form>';

            Shop::Smarty()->assign('worldpayform', $cPost);
        }
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        $this->doLog(print_r($args, true));

        if ($this->verifyNotification($order, $paymentHash, $args)) {
            if (D_MODE === 1) {
                writeLog(WP_D_PFAD, "Verified!", 1);
            }

            if (in_array($args['STATUS'], [5, 9, 41, 51, 91])) {
                $this->setOrderStatusToPaid($order);

                if (($args['STATUS'] == 5) || ($args['STATUS'] == 9)) {
                    $incomingPayment          = new stdClass();
                    $incomingPayment->fBetrag = $args['amount'];
                    $incomingPayment->cISO    = $args['currency'];

                    $this->addIncomingPayment($order, $incomingPayment);
                    $this->sendConfirmationMail($order);
                    $this->updateNotificationID($order->kBestellung, $args['PAYID']);
                }
            }
        }

        $url    = $this->getReturnURL($order);
        $header = 'Location: ' . $url;
        header($header);
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     * @return bool
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        extract($args);

        $str = $orderID . $currency . $amount . $PM .
            $ACCEPTANCE . $STATUS . $CARDNO . $PAYID .
            $NCERROR . $BRAND . $this->getSHA1OutSignature();

        if (WP_D_MODE === 1) {
            writeLog(WP_D_PFAD, "SecurityString: " . $str, 1);
        }
        if (strtolower($SHASIGN) !== sha1($str)) {
            $this->doLog('SHASign falsch');
            if (WP_D_MODE === 1) {
                writeLog(WP_D_PFAD, "SHASign falsch: " . strtolower($SHASIGN) . " != " . sha1($str), 1);
            }

            return false;
        }
        if ($order->fGesamtsummeKundenwaehrung != $amount) {
            $this->doLog('Summe falsch');
            if (WP_D_MODE === 1) {
                writeLog(WP_D_PFAD, "Summe falsch: " . $order->fGesamtsummeKundenwaehrung . " != " . $amount, 1);
            }

            return false;
        }
        if ($order->Waehrung->cISO !== $currency) {
            $this->doLog('Waehrung falsch');
            if (WP_D_MODE === 1) {
                writeLog(WP_D_PFAD, "Waehrung falsch: " . $order->Waehrung->cISO . " != " . $currency, 1);
            }

            return false;
        }

        return true;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool
     */
    public function finalizeOrder($order, $hash, $args)
    {
        return $this->verifyNotification($order, $hash, $args);
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (!isset($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_worldpay_id']) ||
            strlen($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_worldpay_id']) === 0
        ) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "WorldPay-ID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (!isset($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_worldpay_modus']) ||
            strlen($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_worldpay_modus']) === 0
        ) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Modus" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return true;
    }
}
