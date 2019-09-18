<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

// Debug
define('IP_D_MODE', 1); // 1 = An / 0 = Aus
define('IP_D_PFAD', PFAD_LOGFILES . 'ipayment.log');

/**
 * iPayment
 */
class iPayment extends PaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'iPayment';
        $this->caption = 'iPayment';

        return $this;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        if ($order->fGesamtsummeKundenwaehrung > 0) {
            $trx_currency     = $order->Waehrung->cISO;
            $trx_accountid    = strtolower($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_account_id']);
            $trx_userid       = $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxuser_id'];
            $trx_password     = $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxpassword'];
            $trx_securitykey  = $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxsecuritykey'];
            $trx_amount       = round($order->fGesamtsummeKundenwaehrung * 100, 0);
            $trx_securityhash = md5($trx_userid . $trx_amount . $trx_currency . $trx_password . $trx_securitykey);
            $paymentHash      = $this->generateHash($order);
            $cFailureURL      = Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1&' . SID;
            $cReturnURL       = $this->getReturnURL($order);
            if ($_SESSION['Zahlungsart']->nWaehrendBestellung == 1) {
                $cReturnURL = $this->getNotificationURL($paymentHash);
            }

            $cPost = '
            <form action="https://ipayment.de/merchant/' . $trx_accountid . '/processor/2.0/" method="post">
                <input type="hidden" name="trxuser_id" value="' . $trx_userid . '">
                <input type="hidden" name="trxpassword" value="' . $trx_password . '">

                <input type="hidden" name="trx_paymenttyp" value="cc">
                <input type="hidden" name="trx_typ" value="auth">
                <input type="hidden" name="trx_securityhash" value="' . $trx_securityhash . '">

                <input type="hidden" name="addr_name" value="' . $_SESSION['Kunde']->cVorname . ' ' . $_SESSION['Kunde']->cNachname . '">
                <input type="hidden" name="addr_street" value="' . $_SESSION['Kunde']->cStrasse . ' ' . $_SESSION['Kunde']->cHausnummer . '">
                <input type="hidden" name="addr_zip" value="' . $_SESSION['Kunde']->cPLZ . '">
                <input type="hidden" name="addr_city" value="' . $_SESSION['Kunde']->cOrt . '">
                <input type="hidden" name="addr_country" value="' . $_SESSION['Kunde']->cLand . '">
                <input type="hidden" name="addr_email" value="' . $_SESSION['Kunde']->cMail . '">
                <input type="hidden" name="trx_amount" value="' . $trx_amount . '">
                <input type="hidden" name="trx_currency" value="' . $trx_currency . '">
                <input type="hidden" name="invoice_text" value="' . Shop::Lang()->get('order', 'global') . ':">
                <input type="hidden" name="trx_user_comment" value="' . $order->cBestellNr . '">' .
                //@todo: $Firma undefined
                // <input type="hidden" name="item_name" value="' . $Firma->cName . '">
                '<input type="hidden" name="redirect_url" value="' . $cReturnURL . '">
                <input type="hidden" name="hidden_trigger_url" value="' . $this->getNotificationURL($paymentHash) . '&jtls=1">
                <input type="hidden" name="silent_error_url" value="' . $cFailureURL . '">                        
                <input type="hidden" name="redirect_action" value="REDIRECT">
                <input type="submit" value="' . Shop::Lang()->get('payWithIpayment', 'global') . '">
            </form>';

            Shop::Smarty()->assign('ipaymentform', $cPost);
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
            if (IP_D_MODE === 1) {
                writeLog(IP_D_PFAD, 'Verified!', 1);
            }
            $this->setOrderStatusToPaid($order);
            $incomingPayment          = new stdClass();
            $incomingPayment->fBetrag = (float)($args['trx_amount'] / 100);
            $incomingPayment->cISO    = $args['trx_currency'];

            $this->addIncomingPayment($order, $incomingPayment);
            $this->sendConfirmationMail($order);
            $this->updateNotificationID($order->kBestellung, $args['ret_trx_number']);
        }

        if (isset($_GET['jtls'])) {
            die(1);
        } else {
            header('Location: ' . $this->getReturnURL($order));
            exit();
        }
    }

    /**
     * @return bool
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        extract($args);
        if (!preg_match('/\.ipayment\.de$/', gethostbyaddr($_SERVER["REMOTE_ADDR"]))) {
            if (IP_D_MODE === 1) {
                writeLog(IP_D_PFAD, "Server Adresse Stimmt nicht: ipayment.de" . " != " . gethostbyaddr($_SERVER["REMOTE_ADDR"]), 1);
            }

            return false;
        }
        if ($ret_status === 'SUCCESS') {
            return true;
        }
        if (IP_D_MODE === 1) {
            writeLog(IP_D_PFAD, "Status Fail: " . $ret_status, 1);
        }

        return false;
    }

    /**
     * @see includes/modules/PaymentMethod#finalizeOrder($order, $hash, $args)
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
        $trxaccount_id = isset($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_account_id'])
            ? strtolower($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_account_id'])
            : null;
        $trxuser_id    = isset($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxuser_id'])
            ? $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxuser_id']
            : null;
        $trxpassword   = isset($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxpassword'])
            ? $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_ipayment_trxpassword']
            : null;
        if (strlen($trxaccount_id) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Account-ID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($trxuser_id) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "User-ID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($trxpassword) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Transaktions-Passwort" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

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
