<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ServerPaymentMethod.class.php';

// Mode
define('EOS_PP_MODE', 0); // 1 = Test / 0 = Live

// Debug
define('EOS_D_MODE', 1); // 1 = An / 0 = Aus
define('EOS_D_PFAD', PFAD_LOGFILES . 'eos.log');

// EOS ErgebnisURLs
define('EOS_BACKURL_CODE', 1);
define('EOS_SUCCESSURL_CODE', 2);
define('EOS_FAILURL_CODE', 3);
define('EOS_ERRORURL_CODE', 4);

/**
 * Class EOS
 */
class EOS extends ServerPaymentMethod
{
    /**
     * @var string
     */
    public $payment;

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'EOS';
        $this->caption = 'EOS';
        $this->path    = '/PaymentGateway_ELV.acgi';
        // Test
        $this->hostname = 'ssl://www.eos-payment.de';
        $this->host     = 'www.eos-payment.de';

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHaendlerID()
    {
        global $Einstellungen;

        if (!isset($Einstellungen['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlerid'])) {
            $cSetting_arr = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
            $cSetting     = isset($cSetting_arr['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlerid'])
                ? $cSetting_arr['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlerid']
                : null;
        } else {
            $cSetting = isset($Einstellungen['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlerid'])
                ? $Einstellungen['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlerid']
                : null;
        }

        return $cSetting;
    }

    /**
     * @return string
     */
    public function getHaendlerCode()
    {
        global $Einstellungen;

        if (!isset($Einstellungen['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlercode'])) {
            $cSetting_arr = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
            $cSetting     = $cSetting_arr['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlercode'];
        } else {
            $cSetting = $Einstellungen['zahlungsarten']['zahlungsart_' . $this->payment . '_haendlercode'];
        }

        return $cSetting;
    }

    /**
     * @param string $cStrasse
     * @return string
     */
    public function getStreet($cStrasse)
    {
        if (strlen($cStrasse) > 0) {
            $cNr_arr = explode(' ', $cStrasse);
            unset($cNr_arr[count($cNr_arr) - 1]);

            return implode(' ', $cNr_arr);
        }

        return '';
    }

    /**
     * @param string $cStrasse
     * @return string
     */
    public function getStreetNumber($cStrasse)
    {
        if (strlen($cStrasse) > 0) {
            $cNr = explode(' ', $cStrasse);

            return $cNr[count($cNr) - 1];
        }

        return '';
    }

    /**
     *
     */
    public function switchModule()
    {
        switch ($this->cModulId) {
            case 'za_eos_dd_jtl':
                $this->path    = '/PaymentGateway_ELV.acgi';
                $this->payment = 'eos_dd';
                break;
            case 'za_eos_cc_jtl':
                $this->path    = '/PaymentGatewayMini_CC.acgi';
                $this->payment = 'eos_cc';
                break;
            case 'za_eos_direct_jtl':
                $this->path    = '/onlineueberweisung.acgi';
                $this->payment = 'eos_direct';
                break;
            case 'za_eos_ewallet_jtl':
                $this->path    = '/PayPal_SetExpressCheckout.acgi';
                $this->payment = 'eos_ewallet';
                break;
        }
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        global $Einstellungen, $DB, $smarty;

        $this->switchModule();

        if (EOS_D_MODE === 1) {
            $cDC = $this->duringCheckout ? '1' : '0';
            writeLog(EOS_D_PFAD, 'preparePaymentProcess duringCheckout: ' . $cDC, 1);
        }

        $amount    = number_format($order->fGesamtsummeKundenwaehrung, 2, ',', '');
        $customer  = $_SESSION['Kunde'];
        $hash      = $this->generateHash($order);
        // ISO des Lieferlandes
        $lieferland = $_SESSION['Lieferadresse']->cLand;
        if (!$lieferland) {
            $lieferland = $_SESSION['Kunde']->cLand;
        }
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'preparePaymentProcess switchModule: ' . $this->cModulId, 1);
        }
        $cReturnURL = $this->getReturnURL($order);
        if ($this->duringCheckout == 1) {
            $cReturnURL = $this->getNotificationURL($hash);
        }
        // Stylesheet
        $cCSSURL   = 'https://www.eos-payment.de/PaymentGateway_CC/css/stylesheetmini.css';
        $template  = Template::getInstance();
        $cTemplate = $template->getDir();
        if (file_exists(PFAD_ROOT . PFAD_TEMPLATES . $cTemplate . '/tpl_inc/modules/eos/eos.css')) {
            $cCSSURL = Shop::getURL() . '/' . PFAD_TEMPLATES . $cTemplate . '/tpl_inc/modules/eos/eos.css';
        }
        // Test
        $fields = [
            'referenz'     => $order->cBestellNr,
            'haendlerid'   => $this->getHaendlerID(),
            'haendlercode' => $this->getHaendlerCode(),
            'bruttobetrag' => $amount,
            'waehrung'     => $order->Waehrung->cISO,
            'mwstsatz'     => number_format($order->Positionen[0]->fMwSt, 2, ',', ''),
            'BackURL'      => $cReturnURL . '&eos=' . EOS_BACKURL_CODE,
            'SuccessURL'   => $cReturnURL . '&eos=' . EOS_SUCCESSURL_CODE,
            'FailURL'      => $cReturnURL . '&eos=' . EOS_FAILURL_CODE,
            'ErrorURL'     => $cReturnURL . '&eos=' . EOS_ERRORURL_CODE,
            '_stylesheet'  => $cCSSURL,
            'EndURL'       => $cReturnURL
        ];

        if ($this->payment === 'eos_ewallet') {
            $fields['Buchen'] = 1;
        }
        if ($this->payment === 'eos_direct') {
            $fields['Kontoinhaber'] = $customer->cVorname . ' ' . $customer->cNachname;
        }
        // Bei Onlineueberweisung darf nur EUR als Waehrung uebergeben werden
        if ($this->payment === 'eos_direct' && $fields['waehrung'] !== 'EUR') {
            $smarty->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = Shop::Lang()->get('heidelpayHttpError', 'paymentMethods');
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'preparePaymentProcess POST fields: ' . print_r($fields, 1), 1);
        }
        $request = $this->postRequest($fields, false, (bool) EOS_D_MODE, EOS_D_PFAD);
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'preparePaymentProcess request: ' . print_r($request, 1), 1);
        }
        // HTTP Error
        if ($request['status'] === 'error') {
            $smarty->assign('status', 'error');
            $smarty->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = Shop::Lang()->get('eosHttpError', 'paymentMethods');
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }
        // Parse Reponse
        $response = $this->parse($request['body']);

        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'preparePaymentProcess response: ' . print_r($response, 1), 1);
        }

        // Error: Not validated
        if (($response['status'] !== 'OK') || (strpos($response['URL'], 'https') === false)) {
            $smarty->assign('status', 'error');
            $smarty->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = sprintf(Shop::Lang()->get('eosError', 'paymentMethods'), $request['body']);
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }

        $smarty->assign('status', 'success');
        $iframe_url = $response['URL'];
        $smarty->assign('iFrame', '<iframe src="' . $iframe_url . '" width="100%" height="1250" name="uos_iframe" frameborder="0"></iframe>');
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        if ($this->verifyNotification($order, $paymentHash, $args)) {
            $incomingPayment          = new stdClass();
            $incomingPayment->fBetrag = $order->fGesamtsummeKundenwaehrung;
            $incomingPayment->cISO    = $order->Waehrung->cISO;

            $this->addIncomingPayment($order, $incomingPayment);
            $this->setOrderStatusToPaid($order);
            //$this->sendConfirmationMail($order);
            $this->updateNotificationID($order->kBestellung, $args['Kontaktid']);

            if (EOS_D_MODE === 1) {
                writeLog(EOS_D_PFAD, 'handleNotification Payment wurde gesetzt.', 1);
            }
        }

        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'handleNotification hash: ' . $paymentHash . ' args: ' . print_r($args, 1), 1);
        }

        // tzahlungbackground
        Shop::DB()->query(
            "DELETE FROM tzahlungbackground
                WHERE DATE_ADD(dErstellt,INTERVAL 1 DAY) < now()
                    AND cKey = 'eos'", 3
        );

        header('Location: ' . $this->getReturnURL($order));
        exit();
    }

    /**
     * @return bool
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'verifyNotification hash: ' . $paymentHash . ' args: ' . print_r($args, 1), 1);
        }

        extract($args);

        $cParemHash = '';
        if (isset($sh) && strlen($sh) > 0) {
            $cParemHash = $sh;
        } elseif (isset($ph) && strlen($ph) > 0) {
            $cParemHash = $ph;
        }

        if (strlen($cParemHash) === 0) {
            return false;
        }

        if ($paymentHash !== $cParemHash) {
            return false;
        }

        return true;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool|true
     */
    public function finalizeOrder($order, $hash, $args)
    {
        global $cEditZahlungHinweis;

        $hash = '_' . $hash;
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'finalizeOrder hash: ' . $hash . ' args: ' . print_r($args, 1), 1);
        }
        extract($args);
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'finalizeOrder argshash: ' . $sh, 1);
        }
        $nStatus = $this->getEOSServerCom($hash);
        if (EOS_D_MODE === 1) {
            writeLog(EOS_D_PFAD, 'finalizeOrder nStatus: ' . $nStatus, 1);
            writeLog(EOS_D_PFAD, 'finalizeOrder hash = sh: ' . $hash . ' = ' . $sh, 1);
        }

        if ($hash === $sh && $nStatus > 0) {
            if (EOS_D_MODE === 1) {
                writeLog(EOS_D_PFAD, 'finalizeOrder switch nStatus...', 1);
            }

            switch ((int)$nStatus) {
                case EOS_BACKURL_CODE:
                    $cEditZahlungHinweis = EOS_BACKURL_CODE;

                    return false;
                    break;
                case EOS_SUCCESSURL_CODE:
                    return true;
                    break;
                case EOS_FAILURL_CODE:
                    $cEditZahlungHinweis = EOS_FAILURL_CODE;

                    return false;
                    break;
                case EOS_ERRORURL_CODE:
                    $cEditZahlungHinweis = EOS_ERRORURL_CODE;

                    return false;
                    break;
            }
        }

        return false;
    }

    /**
     * EOS Server to Server
     *
     * @param string $cSh
     * @return int
     */
    public function getEOSServerCom($cSh)
    {
        if (strlen($cSh) > 0) {
            $oZahlungbackground = Shop::DB()->select('tzahlungbackground', 'cSID', StringHandler::filterXSS($cSh));

            return isset($oZahlungbackground->kKey) ? (int)$oZahlungbackground->kKey : 0;
        }

        return 0;
    }

    /**
     * Taken from HeidelPay
     *
     * @return array
     * @param string $resultURL
     */
    public function parse($resultURL)
    {
        $returnvalue = [];
        $r_arr       = explode('&', $resultURL);
        foreach ($r_arr as $buf) {
            $temp = urldecode($buf);
            $temp = explode('=', $temp, 2);

            $postatt = $temp[0];
            $postvar = $temp[1];

            $returnvalue[$postatt] = $postvar;
        }

        return $returnvalue;
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (strlen($this->getHaendlerID()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "HaendlerID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }

        if (strlen($this->getHaendlerCode()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "HaendlerCode" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

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
