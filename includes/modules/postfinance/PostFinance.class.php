<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

// Production
define('POST_FINANCE_URL', 'https://e-payment.postfinance.ch/ncol/prod/orderstandard.asp');
// Test
define('POST_FINANCE_URL_TEST', 'https://e-payment.postfinance.ch/ncol/test/orderstandard.asp');

/**
 * PostFinance
 */
class PostFinance extends PaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'PostFinance';
        $this->caption = 'PostFinance';

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPSPID()
    {
        global $Einstellungen;

        return isset($Einstellungen['zahlungsarten']['zahlungsart_postfinance_pspid'])
            ? $Einstellungen['zahlungsarten']['zahlungsart_postfinance_pspid']
            : null;
    }

    /**
     * @return string|null
     */
    public function getSHA1InSignature()
    {
        global $Einstellungen;

        return isset($Einstellungen['zahlungsarten']['zahlungsart_postfinance_sha1in'])
            ? $Einstellungen['zahlungsarten']['zahlungsart_postfinance_sha1in']
            : null;
    }

    /**
     * @return string|null
     */
    public function getSHA1OutSignature()
    {
        global $Einstellungen;

        return isset($Einstellungen['zahlungsarten']['zahlungsart_postfinance_sha1out'])
            ? $Einstellungen['zahlungsarten']['zahlungsart_postfinance_sha1out']
            : null;
    }

    /**
     * @return string|null
     */
    public function getServer()
    {
        global $Einstellungen;

        return isset($Einstellungen['zahlungsarten']['zahlungsart_postfinance_server'])
            ? $Einstellungen['zahlungsarten']['zahlungsart_postfinance_server']
            : null;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $smarty      = Shop::Smarty();
        $customer    = $_SESSION['Kunde'];
        $paymentHash = $this->generateHash($order);
        $kBestellung = $order->kBestellung;
        if (!$order->kBestellung) {
            $kBestellung = str_replace(['.', ' '], '', microtime());
        }
        $stringToBeHashed = $order->cBestellNr .
            (round($order->fGesamtsummeKundenwaehrung, 2) * 100) .
            $order->Waehrung->cISO . $this->getPSPID() . $this->getSHA1InSignature();
        $shaSign          = sha1($stringToBeHashed);
        $url              = POST_FINANCE_URL;
        $mode             = $this->getSetting('server');
        if ($mode === 'test') {
            $url = POST_FINANCE_URL_TEST;
        }
        $smarty->assign('PSPID', $this->getPSPID());
        $smarty->assign('orderId', $order->cBestellNr);
        $smarty->assign('amount', round($order->fGesamtsummeKundenwaehrung, 2) * 100);
        $smarty->assign('currency', $order->Waehrung->cISO);
        $smarty->assign('language', $this->countryMapping($customer->cLand, $customer->kSprache));
        $smarty->assign('shopTitle', $this->getShopTitle() . ' ' . $order->cBestellNr);
        $smarty->assign('acceptURL', $this->getNotificationURL($paymentHash));
        $smarty->assign('CN', $customer->cVorname . ' ' . $customer->cNachname);
        $smarty->assign('EMAIL', $customer->cMail);
        $smarty->assign('shaSign', $shaSign);
        $smarty->assign('url', sprintf($url, $this->getServer()));
        $smarty->assign('submitCaption', Shop::Lang()->get('payWithPostfinance', 'global'));
    }

    /**
     * @param string $cLand
     * @param int    $kSprache
     * @return string
     */
    public function countryMapping($cLand, $kSprache)
    {
        if ($kSprache > 0 && strlen($cLand) > 0) {
            $oSprache = Shop::DB()->select('tsprache', 'kSprache', (int)$kSprache);

            return StringHandler::convertISO2ISO639($oSprache->cISO) . '_' . $cLand;
        }

        return '';
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        $this->doLog(print_r($args, true));
        if ($this->verifyNotification($order, $paymentHash, $args) && in_array($args['STATUS'], [5, 9, 41, 51, 91])) {
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

        if (strtolower($SHASIGN) !== sha1($str)) {
            $this->doLog('SHASign falsch ( IST: ' . sha1($str) . ' SOLL: ' . strtolower($SHASIGN) . ')');

            return false;
        }
        $amount1 = round($order->fGesamtsummeKundenwaehrung, 2) * 100;
        $amount2 = round($amount, 2) * 100;
        if ($amount1 != $amount2) {
            $this->doLog('Summe falsch (amount = ' . $amount2 .
                ', $order->fGesamtsummeKundenwaehrung=' . $amount1 . ')');

            return false;
        }

        if ($order->Waehrung->cISO !== $currency) {
            $this->doLog('Waehrung falsch');

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
        if (strlen($this->getPSPID()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "PSPID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($this->getSHA1InSignature()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "SHA1-In-Signatur" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($this->getSHA1OutSignature()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "SHA1-Out-Signatur" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($this->getServer()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Server" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

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
