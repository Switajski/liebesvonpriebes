<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

// Mode
define('PP_MODE', 0); // 1 = Test / 0 = Live
// Debug
define('D_MODE', 1); // 1 = An / 0 = Aus
define('D_PFAD', PFAD_LOGFILES . 'ut.log');
// Sandbox
define('URL_TEST', 'http://transfer.uos-test.com/interfaces/payment.php');
// Live
define('URL_LIVE', 'https://www.united-online-transfer.com/interfaces/payment.php');

/**
 * Class UT
 */
class UT extends PaymentMethod
{
    /**
     * @var
     */
    public $cModulId;

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'UT';
        $this->caption = 'UT';

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProjectID()
    {
        global $Einstellungen;

        return $Einstellungen['zahlungsarten']['zahlungsart_' . $this->paymentConf . '_projectid'];
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        global $Einstellungen;

        return $Einstellungen['zahlungsarten']['zahlungsart_' . $this->paymentConf . '_secretkey'];
    }

    /**
     * @return string
     */
    public function getShopTitle()
    {
        global $Einstellungen;

        return $Einstellungen['global']['global_meta_title'];
    }

    /**
     * @param $cStrasse
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
     * @param $cStrasse
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
     * @param int $kSprache
     * @return mixed|string
     */
    public function getISOLang($kSprache)
    {
        $kSprache = (int)$kSprache;
        if ($kSprache > 0) {
            $oSprache = Shop::DB()->query(
                "SELECT kSprache, cISO
                    FROM tsprache
                    WHERE kSprache = " . $kSprache, 1
            );

            if ($oSprache->kSprache > 0) {
                return StringHandler::convertISO2ISO639($oSprache->cISO);
            }
        }

        return 'de';
    }

    /**
     *
     */
    public function switchModule()
    {
        switch ($this->cModulId) {
            case 'za_ut_stand_jtl':
                $this->payment     = 'ut_stand';
                $this->paymentConf = 'ut_stand';
                $this->paymentId   = 0;
                $this->transmode   = 0;
                break;

            case 'za_ut_dd_jtl':
                $this->payment     = 'ut_debit';
                $this->paymentConf = 'ut_dd';
                $this->paymentId   = 5;
                $this->transmode   = 0;
                break;

            case 'za_ut_cc_jtl':
                $this->payment     = 'ut_ccard';
                $this->paymentConf = 'ut_cc';
                $this->paymentId   = 6;
                $this->transmode   = 0;
                break;

            case 'za_ut_prepaid_jtl':
                $this->payment     = 'ut_prepaid';
                $this->paymentConf = 'ut_prepaid';
                $this->paymentId   = 18;
                $this->transmode   = 0;
                break;

            case 'za_ut_gi_jtl':
                $this->payment     = 'ut_giro';
                $this->paymentConf = 'ut_gi';
                $this->paymentId   = 28;
                $this->transmode   = 0;
                break;

            case 'za_ut_ebank_jtl':
                $this->payment     = 'ut_ebank';
                $this->paymentConf = 'ut_ebank';
                $this->paymentId   = 43;
                $this->transmode   = 0;
                break;
        }
    }

    /**
     * @return bool
     */
    public function isCURL()
    {
        return function_exists('curl_init');
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        if (isset($_GET['fail']) && (int)$_GET['fail'] === 1) {
            /*** Session Hash ***/
            $cPh = verifyGPDataString('ph');
            $cSh = verifyGPDataString('sh');

            switch ((int)$_GET['error']) {
                case 1:
                    $cFehler = Shop::Lang()->get('uos1', 'errorMessages');
                    break;
                case 2:
                    $cFehler = sprintf(Shop::Lang()->get('uos2', 'errorMessages'), $_GET['fields']);
                    break;
                case 3:
                    $cFehler = sprintf(Shop::Lang()->get('uos3', 'errorMessages'), $_GET['fields']);
                    break;
                case 4:
                    $cFehler = Shop::Lang()->get('uos4', 'errorMessages');
                    break;
                case 5:
                    $cFehler = Shop::Lang()->get('uos5', 'errorMessages');
                    break;
                case 6:
                    $cFehler = Shop::Lang()->get('uos6', 'errorMessages');
                    break;
            }

            Shop::Smarty()->assign('cFehler', $cFehler);

            if (D_MODE === 1) {
                writeLog(D_PFAD, ': preparePaymentProcess _GET fail: ' . $cFehler, 1);
            }
        }

        $this->switchModule();

        $customer = $_SESSION['Kunde'];

        if (strlen($cPh) > 0 || strlen($cSh) > 0) {
            if (strlen($cPh) > 0) {
                if (D_MODE === 1) {
                    writeLog(D_PFAD, ': preparePaymentProcess cPh: ' . $cPh, 1);
                }
                // Payment Hash
                $paymentHash = StringHandler::htmlentities(StringHandler::filterXSS($cPh));
                $sql         = "SELECT ZID.kBestellung, ZA.cModulId 
                                    FROM tzahlungsid ZID 
                                    LEFT JOIN tzahlungsart ZA 
                                        ON ZA.kZahlungsart = ZID.kZahlungsart 
                                    WHERE ZID.cId = '$paymentHash' ";
                $paymentId   = Shop::DB()->query($sql, 1);

                if ($paymentId->kBestellung > 0) {
                    $order = new Bestellung($paymentId->kBestellung);
                    $order->fuelleBestellung(0);
                    $paymentHash = $cPh;
                }
            } elseif (strlen($cSh) > 0) {
                if (D_MODE === 1) {
                    writeLog(D_PFAD, ': preparePaymentProcess cSh fail: ' . $cSh, 1);
                }

                // Load from Session Hash / Session Hash starts with "_"
                $sessionHash    = substr(StringHandler::htmlentities(StringHandler::filterXSS($cSh)), 1);
                $paymentSession = Shop::DB()->select('tzahlungsession', 'cZahlungsID', $sessionHash);
                if (strlen($paymentSession->cSID) > 0) {
                    /** @var array('Warenkorb') $_SESSION['Warenkorb'] */
                    // Load Session
                    $_COOKIE['JTLSHOP'] = $paymentSession->cSID;
                    new Session();
                    $order = new Bestellung($paymentSession->kBestellung);
                    $order->fuelleBestellung(0);
                    $order->fGesamtsummeKundenwaehrung = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);
                    $paymentHash                       = $cSh;
                }
            }
        } else {
            $paymentHash = $this->generateHash($order);
        }
        // Daten
        $cUTDaten_arr = [];
        $kBestellung  = $order->kBestellung;
        if (!$order->kBestellung) {
            $kBestellung = str_replace(['.', ' '], '', microtime());
        }
        // Projekt-Daten
        $cUTDaten_arr['uos_p']        = $this->getProjectID();
        $cUTDaten_arr['uos_eu']       = $order->fGesamtsummeKundenwaehrung * 100;
        $cUTDaten_arr['uos_transfer'] = 1;

        if ($this->payment === 'ut_stand') {
            $cUTDaten_arr['uos_mode_ccard'] = 0;
            $cUTDaten_arr['uos_mode_debit'] = 0;

            $checkSum = $cUTDaten_arr['uos_p'];
            $checkSum .= $cUTDaten_arr['uos_eu'];
            $checkSum .= $cUTDaten_arr['uos_transfer'];
            $checkSum .= $cUTDaten_arr['uos_mode_ccard'];
            $checkSum .= $cUTDaten_arr['uos_mode_debit'];
        } else {
            $cUTDaten_arr['uos_payment'] = $this->paymentId;

            $checkSum = $cUTDaten_arr['uos_p'];
            $checkSum .= $cUTDaten_arr['uos_eu'];
            $checkSum .= $cUTDaten_arr['uos_transfer'];
            $checkSum .= $cUTDaten_arr['uos_payment'];
            $checkSum .= $cUTDaten_arr['uos_mode_debit'];
        }

        $checkSum .= $this->getSecretKey();

        $cUTDaten_arr['uos_chk']      = md5($checkSum);
        $cUTDaten_arr['uos_url_ok']   = $this->getNotificationURL($paymentHash);
        $cUTDaten_arr['uos_language'] = $this->getISOLang($customer->kSprache);
        $cUTDaten_arr['uos_nonce']    = $paymentHash;
        $cUTDaten_arr['uos_param']    = $kBestellung;

        if ($this->duringCheckout) {
            $cUTDaten_arr['uos_url_fail']   = Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1';
            $cUTDaten_arr['uos_url_cancel'] = Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1';
        } else {
            $cUTDaten_arr['uos_url_fail']   = Shop::getURL() . '/bestellab_again.php?kBestellung=' .
                $order->kBestellung . '&fail=1';
            $cUTDaten_arr['uos_url_cancel'] = Shop::getURL() . '/bestellab_again.php?kBestellung=' .
                $order->kBestellung . '&fail=1';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $cIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $cIP = $_SERVER['REMOTE_ADDR'];
        }

        // Kunden-Daten
        if ($this->payment !== 'ut_stand') {
            $cUTDaten_arr['cus_direct'] = 1;
            $cUTDaten_arr['is_direct']  = 1;
        }
        $cUTDaten_arr['cus_gender']    = $customer->cAnrede === 'm' ? 'm' : 'f';
        $cUTDaten_arr['cus_title']     = $customer->cTitel;
        $cUTDaten_arr['cus_firstname'] = $customer->cVorname;
        $cUTDaten_arr['cus_lastname']  = $customer->cNachname;
        $cUTDaten_arr['cus_company']   = $customer->cFirma;
        $cUTDaten_arr['cus_street']    = $customer->cStrasse;
        $cUTDaten_arr['cus_nr']        = $customer->cHausnummer;
        $cUTDaten_arr['cus_extra']     = $customer->cAdressZusatz;
        $cUTDaten_arr['cus_zipcode']   = $customer->cPLZ;
        $cUTDaten_arr['cus_city']      = $customer->cOrt;
        $cUTDaten_arr['cus_country']   = $customer->cLand;
        $cUTDaten_arr['cus_prephone']  = preg_replace('/\D+/', '', substr($customer->cTel, 0, 4));
        $cUTDaten_arr['cus_phone']     = preg_replace('/\D+/', '', substr($customer->cTel, 4));
        $cUTDaten_arr['cus_email']     = $customer->cMail;
        $cUTDaten_arr['cus_ip']        = $cIP;

        if (D_MODE === 1) {
            writeLog(D_PFAD, ': preparePaymentProcess cUTDaten_arr: ' . print_r($cUTDaten_arr, 1), 1);
        }

        $cRes = $this->parseResult($this->doUOSRequest($cUTDaten_arr));

        if (D_MODE === 1) {
            writeLog(D_PFAD, ': preparePaymentProcess parseResult: ' . print_r($cRes, 1), 1);
        }

        if ($cRes['status'] === 'OK') {
            header('Location: ' . $this->getNotificationURL($paymentHash), true, 303);
            exit();
        } elseif ($cRes['status'] === 'REDIRECT') {
            parse_str($cRes['params'], $data);
            $iframe_url = $data['redirect_url'] . '&lang=' . $this->getISOLang($customer->kSprache);
            Shop::Smarty()->assign('iFrame', '<iframe src="' . $iframe_url .
                '" width="100%" height="1250" name="uos_iframe" frameborder="0"></iframe>');
        } else {
            if ($this->duringCheckout) {
                header('Location: ' . Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1');
            } else {
                header('Location: ' . Shop::getURL() . '/bestellab_again.php?kBestellung=' .
                    $order->kBestellung . '&params=' . base64_encode($cRes['params']) . '&fail=1');
            }

            exit();
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
            $this->setOrderStatusToPaid($order);
            $incomingPayment          = new stdClass();
            $incomingPayment->fBetrag = $order->fGesamtsummeKundenwaehrung;
            $incomingPayment->cISO    = $order->Waehrung->cISO;

            $this->addIncomingPayment($order, $incomingPayment);
            $this->sendConfirmationMail($order);

            $this->updateNotificationID($order->kBestellung, $args['pid']);
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

        return ($status === 'OK');
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
        $this->switchModule();

        return $this->verifyNotification($order, $hash, $args);
    }

    /**
     * @param array $cUOSDaten_arr
     * @return mixed|string
     */
    public function doUOSRequest($cUOSDaten_arr = [])
    {
        // Wenn kein Curl vorhanden dann gleich abbrechen
        if (!$this->isCURL()) {
            return 'status=FAIL&code_1=300&msg_1=' . urlencode('Curl Fehler');
        }
        $cUOSDaten_arr = $this->convertUTF8($cUOSDaten_arr);
        // String erzeugen
        $cDataString = '';
        if (is_array($cUOSDaten_arr) && count($cUOSDaten_arr) > 0) {
            foreach ($cUOSDaten_arr as $k => $cUOSDaten) {
                if ($cUOSDaten !== '' || is_numeric($cUOSDaten)) {
                    $cDataString .= urlencode($k) . '=' . urlencode($cUOSDaten) . '&';
                }
            }

            $cDataString = substr($cDataString, 0, -1);
        }

        $oCURL = curl_init();
        curl_setopt($oCURL, CURLOPT_URL, PP_MODE === 1 ? URL_TEST : URL_LIVE);
        curl_setopt($oCURL, CURLOPT_HEADER, 0);
        curl_setopt($oCURL, CURLOPT_FAILONERROR, 1);
        curl_setopt($oCURL, CURLOPT_TIMEOUT, 25);
        curl_setopt($oCURL, CURLOPT_POST, 1);
        curl_setopt($oCURL, CURLOPT_POSTFIELDS, $cDataString);
        curl_setopt($oCURL, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCURL, CURLOPT_SSL_VERIFYPEER, DEFAULT_CURL_OPT_VERIFYPEER);
        curl_setopt($oCURL, CURLOPT_SSL_VERIFYHOST, DEFAULT_CURL_OPT_VERIFYHOST);
        curl_setopt($oCURL, CURLOPT_USERAGENT, 'UOS Payment Request');

        $cRes     = curl_exec($oCURL);
        $cCURLErr = curl_error($oCURL);

        curl_close($oCURL);

        if (!$cRes && $cCURLErr) {
            $cRes = 'status=FAIL&code_1=300&param_1=' . $cCURLErr;
        }

        return $cRes;
    }

    /**
     * @param $cRes
     * @return array
     */
    public function parseResult($cRes)
    {
        // Params
        $params = strstr($cRes, '&');
        // Status
        $status      = str_replace($params, '', $cRes);
        $statusArray = explode('=', $status);
        $status      = $statusArray[1];
        $params      = substr($params, 1);

        return ['status' => $status, 'params' => $params];
    }

    /**
     * @param $data
     * @return mixed
     */
    public function convertUTF8($data)
    {
        foreach ($data as $k => $v) {
            if ($this->isUTF8($v)) {
                $data[$k] = utf8_decode($v);
            }
        }

        return $data;
    }

    /**
     * @param $string
     * @return bool
     */
    public function isUTF8($string)
    {
        if (is_array($string)) {
            $enc = implode('', $string);

            return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
        } else {
            return (utf8_encode(utf8_decode($string)) === $string);
        }
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return true;
    }
}
