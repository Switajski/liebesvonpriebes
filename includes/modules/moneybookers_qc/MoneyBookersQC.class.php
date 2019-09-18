<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ServerPaymentMethod.class.php';

// Constants
define('MBQC_REDIRECT_URL', 'https://www.skrill.com/app/payment.pl?sid=');
// Pending : Payment in process (e.g. bank transfer)
define('MBQC_PENDING', 0);
// Processed: Really paid now
define('MBQC_PROCESSED', 2);

/**
 * MoneyBookers Quick Checkout
 */
class MoneyBookersQC extends ServerPaymentMethod
{
    /**
     * i.e.
     * visa for za_mbqc_visa_jtl
     *
     * @var string
     */
    public $mbAbbr;

    /**
     *
     * @var string
     */
    public $recieverEmail;

    /**
     *
     * @var string
     */
    public $secretWord;

    /**
     *
     * @var string URL to Image Source
     */
    public $imageSource;

    /**
     * @param     $moduleID
     * @param int $nAgainCheckout
     */
    public function __construct($moduleID, $nAgainCheckout = 0)
    {
        parent::__construct($moduleID, $nAgainCheckout);

        // extract: za_mbqc_visa_jtl => visa
        $pattern      = '&za_mbqc_(.*)_jtl&is';
        $result       = preg_match($pattern, $moduleID, $subpattern);
        $this->mbAbbr = $subpattern[1];
    }

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name     = 'MoneyBookersQC';
        $this->hostname = 'ssl://www.skrill.com';
        $this->host     = 'www.skrill.com';
        $this->path     = '/app/payment.pl';

        // Fetch Caption/Name and Image from DB
        $sql               = "SELECT cName, cBild FROM tzahlungsart WHERE cModulId = '{$this->moduleID}'";
        $result            = Shop::DB()->query($sql, 1);
        $this->caption     = $result->cName;
        $this->imageSource = $result->cBild;

        // Fetch Reviever Email and Secret Word
        $sql                 = "SELECT cEmail, cSecretWord FROM tskrill";
        $result              = Shop::DB()->query($sql, 1);
        $this->recieverEmail = $result->cEmail;
        $this->secretWord    = $result->cSecretWord;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecieverEmail()
    {
        return $this->recieverEmail;
    }

    /**
     * @return string
     */
    public function getSecretWord()
    {
        return $this->secretWord;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        // Giropay
        if ($this->mbAbbr === 'git') {
            $this->mbAbbr = 'gir';
        }

        $amount      = number_format($order->fGesamtsummeKundenwaehrung, 2, '.', '');
        $customer    = $_SESSION['Kunde'];
        $paymentHash = $this->generateHash($order, 32);
        $cReturnURL  = $this->getReturnURL($order);

        if (isset($_SESSION['Zahlungsart']->nWaehrendBestellung) && $_SESSION['Zahlungsart']->nWaehrendBestellung == 1) {
            $cReturnURL = $this->getNotificationURL($paymentHash);
        }

        $fields = [
            'payment_methods'       => strtoupper($this->mbAbbr),
            'language'              => StringHandler::convertISO2ISO639($_SESSION['cISOSprache']),
            'prepare_only'          => 1,
            'hide_login'            => 1,
            'merchant_fields'       => 'platform',
            'platform'              => 'JTL',
            'pay_to_email'          => $this->getRecieverEmail(),
            'amount'                => $amount,
            'currency'              => $order->Waehrung->cISO,
            'detail1_description'   => Shop::Lang()->get('order', 'global'),
            'detail1_text'          => $order->cBestellNr,
            'recipient_description' => $this->getShopTitle(),
            'transaction_id'        => $paymentHash,
            'return_url'            => $cReturnURL,
            'status_url'            => $this->getNotificationURL($paymentHash) . '&jtls=1',
            'address'               => $customer->cStrasse . ' ' . $customer->cHausnummer . ' ' . $customer->cAdressZusatz . ' ',
            'postal_code'           => $customer->cPLZ,
            'city'                  => $customer->cOrt,
            'firstname'             => $customer->cVorname,
            'lastname'              => $customer->cNachname,
            'pay_from_email'        => $customer->cMail
        ];

        if ($this->mbAbbr === 'acc' || $this->mbAbbr === 'vsa' || $this->mbAbbr === 'msc') {
            $fields['wpf_redirect'] = 1;
        }

        $request = $this->postRequest($fields);

        // HTTP Error
        if ($request['status'] === 'error') {
            Shop::Smarty()->assign('status', 'error')
                ->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = Shop::Lang()->get('moneybookersQcHttpError', 'paymentMethods');
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }
        // Error: No SessionID
        if (preg_match('/SESSION_ID=(\w*);/', $request['header'], $matches) == false) {
            Shop::Smarty()->assign('status', 'error')
                ->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = sprintf(Shop::Lang()->get('moneybookersQcError', 'paymentMethods'), 'No SessionID found');
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }

        $sessionID = $matches[1];
        $url       = MBQC_REDIRECT_URL . $sessionID;
        Shop::Smarty()->assign('status', 'success')
            ->assign('caption', $this->caption)
            ->assign('imageSource', $this->imageSource)
            ->assign('url', $url);
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        $status = $args['status'];

        if ($status == MBQC_PROCESSED) {
            $this->setOrderStatusToPaid($order);
            if ($this->verifyNotification($order, $paymentHash, $args)) {
                $incomingPayment              = new stdClass();
                $incomingPayment->fBetrag     = $order->fGesamtsummeKundenwaehrung;
                $incomingPayment->cISO        = $order->Waehrung->cISO;
                $incomingPayment->cEmpfaenger = $args['pay_to_email'];
                $incomingPayment->cZahler     = $args['pay_from_email'];

                $this->addIncomingPayment($order, $incomingPayment);
                $this->deletePaymentHash($paymentHash);
                $this->sendConfirmationMail($order);
                $this->updateNotificationID($order->kBestellung, $args['mb_transaction_id']);
            }
        }

        if (isset($_GET['jtls'])) {
            die(1);
        } else {
            header('Location: ' . $this->getReturnURL($order));
            exit();
        }
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool
     */
    public function finalizeOrder($order, $hash, $args)
    {
        $status = $args['status'];

        return ($status == MBQC_PENDING || $status == MBQC_PROCESSED);
    }

    /**
     * @param $order
     * @param $paymentHash
     * @param $args
     * @return bool
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        extract($args);
        // Checksum
        $secretWord = $this->getSecretWord();
        $str        = $merchant_id . $transaction_id . strtoupper(md5($secretWord)) . $mb_amount . $mb_currency . $status;
        if ($str === $secretWord) {
            // nothing sent?
            return false;
        } elseif (strtoupper(md5($str)) !== $md5sig) {
            return false;
        }
        // Reciever Email
        if ($pay_to_email !== $this->getRecieverEmail()) {
            return false;
        }
        // Transaction ID
        if ($transaction_id !== $paymentHash) {
            return false;
        }

        return true;
    }

    /**
     *
     */
    public function generatePaymentMethodsSQL()
    {
        $methods = [
            // Order:
            'wlt' => 'Moneybookers eWallet',
            'acc' => 'All Credit Cards',
            'did' => 'Lastschrift (ELV)',
            'git' => 'Giropay',
            'sft' => 'Sofort&uuml;berweisung',
            'msc' => 'Mastercard',
            'vsa' => 'VISA',
            'mae' => 'Maestro',
            'idl' => 'iDeal',
            'gcb' => 'Carte Bleue',
            'din' => 'Dinars',
            'amx' => 'Amex',
            // No Order:
            'csi' => 'CartaSi',
            'dnk' => 'Dankort',
            'ebt' => 'Nordea Solo Sweden',
            'ent' => 'eNTES',
            'lsr' => 'Laser',
            'npy' => 'EPS',
            'pli' => 'POLi',
            'psp' => 'Postepay',
            'pwy' => 'Przelewy',
            'slo' => 'Solo',
            'so2' => 'Nordea Solo Finland'
        ];

        reset($methods);
        $url  = PFAD_GFX . 'MoneyBookersQC/';
        $path = PFAD_ROOT . PFAD_GFX . 'MoneyBookersQC/';
        $sort = 200; // Start Value
        do {
            $abbr         = key($methods);
            $title        = current($methods);
            $pathFilename = $path . $abbr . '.gif';
            $urlFilename  = '';
            if (file_exists($pathFilename)) {
                $urlFilename = $url . $abbr . '.gif';
            }

            echo "
				INSERT INTO `teinstellungen` VALUES (100, 'zahlungsart_mbqc_" . $abbr . "_min_bestellungen', '0', 'za_mbqc_" . $abbr . "_jtl');
				INSERT INTO `teinstellungen` VALUES (100, 'zahlungsart_mbqc_" . $abbr . "_min', '0', 'za_mbqc_" . $abbr . "_jtl');
				INSERT INTO `teinstellungen` VALUES (100, 'zahlungsart_mbqc_" . $abbr . "_max', '0', 'za_mbqc_" . $abbr . "_jtl');				
				INSERT INTO `teinstellungenconf` (kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,cConf) 
				VALUES (100, '" . $title . "', '', NULL, NULL, 'za_mbqc_" . $abbr . "_jtl', 700, 'N');
				INSERT INTO `teinstellungenconf` (kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,cConf) 
				VALUES (100, 'Anzahl Bestellungen nötig', 'Nur Kunden, die min. soviele Bestellungen bereits durchgeführt haben, können diese Zahlungsart nutzen.', 'zahlungsart_mbqc_" . $abbr . "_min_bestellungen', 'zahl', 'za_mbqc_" . $abbr . "_jtl', 710, 'Y');
				INSERT INTO `teinstellungenconf` (kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,cConf) 
				VALUES (100, 'Mindestbestellwert', 'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.', 'zahlungsart_mbqc_" . $abbr . "_min', 'kommazahl', 'za_mbqc_" . $abbr . "_jtl', 720, 'Y');
				INSERT INTO `teinstellungenconf` (kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,cConf) 
				VALUES (100, 'Maximaler Bestellwert', 'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)', 'zahlungsart_mbqc_" . $abbr . "_max', 'kommazahl', 'za_mbqc_" . $abbr . "_jtl', 730, 'Y');
				INSERT INTO `tmodul` VALUES ('za_mbqc_" . $abbr . "_jtl', '" . $title . "', 'JTL-Software', 'Zahlungsmodul', '', '', '2009-06-19 19:04:00');
				INSERT INTO `tzahlungsart` (cName,cModulId,cKundengruppen,cZusatzschrittTemplate,cBild,nSort,nActive,cAnbieter) 
				VALUES ('" . $title . "', 'za_mbqc_" . $abbr . "_jtl', '', '', '" . $urlFilename . "', ' . $sort . ', 0, 'Moneybookers');
				SET @LastID = LAST_INSERT_ID();
				INSERT INTO `tzahlungsartsprache` VALUES (@LastID, 'ger', '" . $title . "', 'Gebühr');
				INSERT INTO `tzahlungsartsprache` VALUES (@LastID, 'eng', '" . $title . "', 'Fee');
			";
            $sort++;
        } while (next($methods));
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (strlen($this->getRecieverEmail()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "RecieverEmail" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }

        if (strlen($this->getSecretWord()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "SecretWord" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

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
