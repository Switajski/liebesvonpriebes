<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

define('URL_WIRECARD', 'https://secure.wirecard-cee.com/qpay/init.php');

/**
 * Wirecard
 */
class Wirecard extends PaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'Wirecard';
        $this->caption = 'Wirecard';

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        global $Einstellungen;

        return isset($Einstellungen['zahlungsarten']['zahlungsart_wirecard_customer_id'])
            ? $Einstellungen['zahlungsarten']['zahlungsart_wirecard_customer_id']
            : null;
    }

    /**
     * @return string|null
     */
    public function getSecret()
    {
        global $Einstellungen;

        return isset($Einstellungen['zahlungsarten']['zahlungsart_wirecard_secret'])
            ? $Einstellungen['zahlungsarten']['zahlungsart_wirecard_secret']
            : null;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $amount      = number_format($order->fGesamtsummeKundenwaehrung, 2, '.', '');
        $paymentHash = $this->generateHash($order);

        $cFailureURL = $this->getReturnURL($order);
        if ($_SESSION['Zahlungsart']->nWaehrendBestellung == 1) {
            $cFailureURL = Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1&' . SID;
        }

        $cReturnUrl = $this->getReturnURL($order);
        if (strlen($cReturnUrl) === 0) {
            $cReturnUrl = Shop::getURL() . '/bestellabschluss.php?i=' . $paymentHash;
        }

        $fields = [
            'customerId'              => $this->getCustomerId(),
            'secret'                  => $this->getSecret(),
            'amount'                  => $amount,
            'currency'                => strtolower($order->Waehrung->cISO),
            'paymenttype'             => 'SELECT',
            'language'                => StringHandler::convertISO2ISO639($_SESSION['cISOSprache']),
            'orderDescription'        => sprintf(Shop::Lang()->get('wirecardText', 'paymentMethods'), $order->cBestellNr, $this->getShopTitle()),
            'confirmURL'              => $this->getNotificationURL($paymentHash) . '&jtls=1',
            'successURL'              => Shop::getURL() . '/jtl.php',
            'cancelURL'               => $cFailureURL,
            'failureURL'              => $cFailureURL,
            'serviceURL'              => Shop::getURL(),
            'requestFingerprintOrder' => ''
        ];
        $fields['requestFingerprintOrder'] = implode(',', array_keys($fields));
        $fields['requestFingerprint']      = md5(implode('', $fields));

        //secret darf nicht uebermittelt werden (nur zur Berechnung des Fingerprints wichtig)
        unset($fields['secret']);

        Shop::Smarty()->assign('fields', $fields)
            ->assign('url', URL_WIRECARD)
            ->assign('submitCaption', Shop::Lang()->get('payWithWirecard', 'global'));
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        if ($args['paymentState'] === 'SUCCESS') {
            if ($this->verifyNotification($order, $paymentHash, $args)) {
                $incomingPayment              = new stdClass();
                $incomingPayment->fBetrag     = $order->fGesamtsummeKundenwaehrung;
                $incomingPayment->cISO        = $order->Waehrung->cISO;
                $incomingPayment->cEmpfaenger = 'Wirecard Order No. ' . $args['orderNumber'];

                $this->addIncomingPayment($order, $incomingPayment);
                $this->setOrderStatusToPaid($order);
                $this->sendConfirmationMail($order);
                $this->updateNotificationID($order->kBestellung, $args['orderNumber']);
            }
        }
    }

    /**
     * @see includes/modules/PaymentMethod#finalizeOrder($order, $hash, $args)
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool|true
     */
    public function finalizeOrder($order, $hash, $args)
    {
        return $this->verifyNotification($order, $hash, $args);
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
        // Taken from Wirecard Description \\
        $responseFingerprintOrder   = $args['responseFingerprintOrder'];
        $responseFingerprint        = $args['responseFingerprint'];
        $str4responseFingerprint    = '';
        $mandatoryFingerPrintFields = 0;
        $secretUsed                 = 0;
        $wcOrder                    = explode(',', $responseFingerprintOrder);
        $wcCount                    = count($wcOrder);

        for ($i = 0; $i < $wcCount; $i++) {
            $key = $wcOrder[$i];
            // check if there are enough fields in den responsefingerprint
            if (strcmp($key, 'paymentState') === 0 && strlen($args[$wcOrder[$i]]) > 0) {
                $mandatoryFingerPrintFields++;
            }
            if (strcmp($key, 'orderNumber') === 0 && strlen($args[$wcOrder[$i]]) > 0) {
                $mandatoryFingerPrintFields++;
            }
            if (strcmp($key, 'paymentType') === 0 && strlen($args[$wcOrder[$i]]) > 0) {
                $mandatoryFingerPrintFields++;
            }
            if (strcmp($key, 'secret') === 0) {
                $str4responseFingerprint .= $this->getSecret();
                $secretUsed = 1;
            } else {
                $str4responseFingerprint .= $args[$wcOrder[$i]];
            }
        }

        // recalc the fingerprint
        $responseFingerprintCalc = md5($str4responseFingerprint);

        return (strcmp($responseFingerprintCalc, $responseFingerprint) === 0 &&
            $mandatoryFingerPrintFields == 3 &&
            $secretUsed == 1
        );
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (strlen($this->getCustomerId()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Kundennummer" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }

        if (strlen($this->getSecret()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Secret" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

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
