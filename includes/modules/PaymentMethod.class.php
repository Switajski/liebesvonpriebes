<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PaymentMethod
 *
 * Represents a Method of Payment the customer can pay his order with.
 * Paypal, for example.
 */
class PaymentMethod
{
    /**
     * i.e. za_mbqc_visa_jtl
     *
     * @var string
     */
    public $moduleID;

    /**
     * i.e. mbqc_visa for za_mbqc_visa_jtl
     *
     * @var string
     */
    public $moduleAbbr;

    /**
     * Internal Name w/o whitespace, e.g. 'MoneybookersQC'.
     *
     * @var string
     */
    public $name;

    /**
     * E.g. 'Moneybookers Quick Connect'.
     *
     * @var string
     */
    public $caption;

    /**
     * @var bool
     */
    public $duringCheckout;

    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var bool
     */
    public $bPayAgain;

    /**
     * @var array
     */
    public $paymentConfig;

    /**
     * @param string $moduleID
     * @param int    $nAgainCheckout
     */
    public function __construct($moduleID, $nAgainCheckout = 0)
    {
        $this->moduleID = $moduleID;
        // extract: za_mbqc_visa_jtl => myqc_visa
        $pattern = '&za_(.*)_jtl&is';
        preg_match($pattern, $moduleID, $subpattern);
        $this->moduleAbbr = isset($subpattern[1]) ? $subpattern[1] : null;

        $this->loadSettings();
        $this->init($nAgainCheckout);
    }

    /**
     * Set Members Variables
     *
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        $this->name = '';
        // Fetch Caption/Name and Image from DB
        $result               = Shop::DB()->select('tzahlungsart', 'cModulId', $this->moduleID);
        $this->caption        = isset($result->cName)
            ? $result->cName
            : null;
        $this->duringCheckout = isset($result->nWaehrendBestellung)
            ? (int)$result->nWaehrendBestellung
            : 0;

        if ($nAgainCheckout == 1) {
            $this->duringCheckout = 0;
        }

        return $this;
    }

    /**
     * @param Bestellung $order
     * @return string
     */
    public function getOrderHash($order)
    {
        $orderId = isset($order->kBestellung)
            ? Shop::DB()->query("SELECT cId FROM tbestellid WHERE kBestellung = " . (int)$order->kBestellung, 1)
            : null;

        return isset($orderId->cId) ? $orderId->cId : null;
    }

    /**
     * Payment Provider redirects customer to this URL when Payment is complete
     *
     * @param Bestellung $order
     * @return string
     */
    public function getReturnURL($order)
    {
        if (!isset($_SESSION['Zahlungsart']->nWaehrendBestellung) || $_SESSION['Zahlungsart']->nWaehrendBestellung == 0) {
            global $Einstellungen;
            if ($Einstellungen['kaufabwicklung']['bestellabschluss_abschlussseite'] === 'A') {
                // Abschlussseite
                $oZahlungsID = Shop::DB()->query("
                    SELECT cId 
                        FROM tbestellid 
                        WHERE kBestellung = " . (int)$order->kBestellung, 1
                );
                if (is_object($oZahlungsID)) {
                    return Shop::getURL() . '/bestellabschluss.php?i=' . $oZahlungsID->cId;
                }
            }

            return $order->BestellstatusURL;
        }

        return Shop::getURL() . '/bestellvorgang.php';
    }

    /**
     * @param string $hash
     * @return string
     */
    public function getNotificationURL($hash)
    {
        $key = $this->duringCheckout ? 'sh' : 'ph';

        return Shop::getURL() . '/includes/modules/notify.php?' . $key . '=' . $hash;
    }

    /**
     * @param int    $kBestellung
     * @param string $cNotifyID
     * @return $this
     */
    public function updateNotificationID($kBestellung, $cNotifyID)
    {
        $kBestellung = (int)$kBestellung;
        if ($kBestellung > 0) {
            $_upd            = new stdClass();
            $_upd->cNotifyID = Shop::DB()->escape($cNotifyID);
            $_upd->dNotify   = 'now()';
            Shop::DB()->update('tzahlungsession', 'kBestellung', $kBestellung, $_upd);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getShopTitle()
    {
        global $Einstellungen;

        return $Einstellungen['global']['global_shopname'];
    }

    /**
     * Prepares everything so that the Customer can start the Payment Process.
     * Tells Template Engine.
     *
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        // overwrite!
    }

    /**
     * Sends Error Mail to Master
     *
     * @param string $body
     * @return $this
     */
    public function sendErrorMail($body)
    {
        global $Einstellungen;
        // Load Mail Settings
        if (!isset($Einstellungen['emails'])) {
            $Einstellungen = Shop::getSettings([CONF_EMAILS]);
        }
        $mail = new stdClass();
        // Content
        $mail->toEmail   = $Einstellungen['emails']['email_master_absender'];
        $mail->toName    = $Einstellungen['emails']['email_master_absender_name'];
        $mail->fromEmail = $mail->toEmail;
        $mail->fromName  = $mail->toName;
        $mail->subject   = sprintf(
            Shop::Lang()->get('errorMailSubject', 'paymentMethods'),
            $Einstellungen['global']['global_meta_title']
        );
        $mail->bodyText  = $body;
        // Method
        $mail->methode       = $Einstellungen['eMails']['eMail_methode'];
        $mail->sendMail_pfad = $Einstellungen['eMails']['eMail_sendMail_pfad'];
        $mail->smtp_hostname = $Einstellungen['eMails']['eMail_smtp_hostname'];
        $mail->smtp_port     = $Einstellungen['eMails']['eMail_smtp_port'];
        $mail->smtp_auth     = $Einstellungen['eMails']['eMail_smtp_auth'];
        $mail->smtp_user     = $Einstellungen['eMails']['eMail_smtp_user'];
        $mail->smtp_pass     = $Einstellungen['eMails']['eMail_smtp_pass'];
        $mail->SMTPSecure    = $Einstellungen['emails']['email_smtp_verschluesselung'];
        // Send
        include_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
        verschickeMail($mail);

        return $this;
    }

    /**
     * Generates Hash (Payment oder Session Hash) and saves it to DB
     *
     * @param Bestellung $order
     * @param int        $length
     * @return string
     */
    public function generateHash($order, $length = 40)
    {
        $hash = null;
        if ($this->duringCheckout == 1) {
            if (!isset($_SESSION['IP'])) {
                $_SESSION['IP'] = new stdClass();
            }
            $_SESSION['IP']->cIP = gibIP(true);
        }

        if ($order->kBestellung !== null) {
            $oBestellID                = Shop::DB()->select('tbestellid', 'kBestellung', (int)$order->kBestellung);
            $hash                      = $oBestellID->cId;
            $oZahlungsID               = new stdClass();
            $oZahlungsID->kBestellung  = $order->kBestellung;
            $oZahlungsID->kZahlungsart = $order->kZahlungsart;
            $oZahlungsID->cId          = $hash;
            $oZahlungsID->txn_id       = '';
            $oZahlungsID->dDatum       = 'now()';
            Shop::DB()->insert('tzahlungsid', $oZahlungsID);
        } else {
            Shop::DB()->delete('tzahlungsession', ['cSID', 'kBestellung'], [session_id(), 0]);
            $oZahlungSession               = new stdClass();
            $oZahlungSession->cSID         = session_id();
            $oZahlungSession->cNotifyID    = '';
            $oZahlungSession->dZeitBezahlt = '0000-00-00 00:00:00';
            $oZahlungSession->cZahlungsID  = gibUID($length, md5($oZahlungSession->cSID . mt_rand()) . time());
            $oZahlungSession->dZeit        = 'now()';
            Shop::DB()->insert('tzahlungsession', $oZahlungSession);
            $hash = '_' . $oZahlungSession->cZahlungsID;
        }

        return $hash;
    }

    /**
     * @param string $paymentHash
     * @return $this
     */
    public function deletePaymentHash($paymentHash)
    {
        Shop::DB()->delete('tzahlungsid', 'cId', $paymentHash);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @param Object     $payment (Key, Zahlungsanbieter, Abgeholt, Zeit is set here)
     * @return $this
     */
    public function addIncomingPayment($order, $payment)
    {
        $model = (object)array_merge([
            'kBestellung'       => (int)$order->kBestellung,
            'cZahlungsanbieter' => empty($order->cZahlungsartName) ? $this->name : $order->cZahlungsartName,
            'fBetrag'           => 0,
            'fZahlungsgebuehr'  => 0,
            'cISO'              => $_SESSION['Waehrung']->cISO,
            'cEmpfaenger'       => '',
            'cZahler'           => '',
            'dZeit'             => 'now()',
            'cHinweis'          => '',
            'cAbgeholt'         => 'N'
        ], (array)$payment);
        Shop::DB()->insert('tzahlungseingang', $model);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @return $this
     */
    public function setOrderStatusToPaid($order)
    {
        $_upd                = new stdClass();
        $_upd->cStatus       = BESTELLUNG_STATUS_BEZAHLT;
        $_upd->dBezahltDatum = 'now()';
        Shop::DB()->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $_upd);

        return $this;
    }

    /**
     * Sends a Mail to the Customer if Payment was recieved
     *
     * @param Bestellung $order
     * @return $this
     */
    public function sendConfirmationMail($order)
    {
        $this->sendMail($order->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     */
    public function handleNotification($order, $hash, $args)
    {
        // overwrite!
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     *
     * @return true, if $order should be finalized
     */
    public function finalizeOrder($order, $hash, $args)
    {
        // overwrite!
        return false;
    }

    /**
     * @return bool
     */
    public function redirectOnCancel()
    {
        // overwrite!
        return false;
    }

    /**
     * @return bool
     */
    public function redirectOnPaymentSuccess()
    {
        // overwrite!
        return false;
    }

    /**
     * @param string $msg
     * @param int    $level
     * @return $this
     */
    public function doLog($msg, $level = LOGLEVEL_NOTICE)
    {
        ZahlungsLog::add($this->moduleID, $msg, null, $level);

        return $this;
    }

    /**
     * @param int $kKunde
     * @return int
     */
    public function getCustomerOrderCount($kKunde)
    {
        if ((int)$kKunde > 0) {
            $oBestellung = Shop::DB()->query(
                "SELECT count(*) AS nAnzahl
                    FROM tbestellung
                    WHERE (cStatus = '2' || cStatus = '3' || cStatus = '4')
                        AND kKunde = " . (int)$kKunde, 1
            );

            if (isset($oBestellung->nAnzahl) && count($oBestellung->nAnzahl) > 0) {
                return (int)$oBestellung->nAnzahl;
            }
        }

        return 0;
    }

    /**
     * @return $this
     */
    public function loadSettings()
    {
        global $Einstellungen;

        if (!is_array($Einstellungen)) {
            $Einstellungen = [];
        }
        if (!array_key_exists('zahlungsarten', $Einstellungen) || $Einstellungen['zahlungsarten'] === null) {
            $Einstellungen = array_merge($Einstellungen, Shop::getSettings([CONF_ZAHLUNGSARTEN]));
        }
        $this->paymentConfig = $Einstellungen['zahlungsarten'];

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        $Einstellungen = Shop::getSettings([CONF_ZAHLUNGSARTEN, CONF_PLUGINZAHLUNGSARTEN]);

        return isset($Einstellungen['zahlungsarten']['zahlungsart_' . $this->moduleAbbr . '_' . $key])
            ? $Einstellungen['zahlungsarten']['zahlungsart_' . $this->moduleAbbr . '_' . $key]
            : (isset($Einstellungen['pluginzahlungsarten'][$this->moduleID . '_' . $key])
                ? $Einstellungen['pluginzahlungsarten'][$this->moduleID . '_' . $key]
                : null
            );
    }

    /**
     *
     * @param object    $customer
     * @param Warenkorb $cart
     * @return bool - true, if $customer with $cart may use Payment Method
     */
    public function isValid($customer, $cart)
    {
        if ($this->getSetting('min_bestellungen') > 0) {
            if (isset($customer->kKunde) && $customer->kKunde > 0) {
                $res = Shop::DB()->query("
                  SELECT count(*) AS cnt 
                      FROM tbestellung 
                      WHERE kKunde = " . (int) $customer->kKunde . " 
                          AND (
                                cStatus = '" . BESTELLUNG_STATUS_BEZAHLT . "' 
                                OR cStatus = '" . BESTELLUNG_STATUS_VERSANDT .
                            "')", 1
                );
                $count = (int)$res->cnt;
                if ($count < $this->getSetting('min_bestellungen')) {
                    ZahlungsLog::add($this->moduleID,
                        'Bestellanzahl ' . $count . ' ist kleiner als der Mindestanzahl von ' .
                            $this->getSetting('min_bestellungen'),
                        null,
                        LOGLEVEL_NOTICE
                    );

                    return false;
                }
            } else {
                ZahlungsLog::add($this->moduleID, "Es ist kein kKunde vorhanden", null, LOGLEVEL_NOTICE);

                return false;
            }
        }

        if ($this->getSetting('min') > 0 && $cart->gibGesamtsummeWaren(1) <= $this->getSetting('min')) {
            ZahlungsLog::add($this->moduleID,
                'Bestellwert ' . $cart->gibGesamtsummeWaren(1) .
                    ' ist kleiner als der Mindestbestellwert von ' . $this->getSetting('min'),
                null,
                LOGLEVEL_NOTICE
            );

            return false;
        }

        if ($this->getSetting('max') > 0 && $cart->gibGesamtsummeWaren(1) >= $this->getSetting('max')) {
            ZahlungsLog::add($this->moduleID,
                'Bestellwert ' . $cart->gibGesamtsummeWaren(1) .
                    ' ist groesser als der maximale Bestellwert von ' . $this->getSetting('max'),
                null,
                LOGLEVEL_NOTICE
            );

            return false;
        }

        if (!$this->isValidIntern($customer, $cart)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        // Overwrite
        return true;
    }

    /**
     * determines, if the payment method can be selected in the checkout process
     *
     * @return bool
     */
    public function isSelectable()
    {
        // Overwrite
        return true;
    }

    /**
     * @param array $aPost_arr
     * @return bool
     */
    public function handleAdditional($aPost_arr)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        return true;
    }

    /**
     *
     * @param string $cKey
     * @param string $cValue
     * @return $this
     */
    public function addCache($cKey, $cValue)
    {
        $_SESSION[$this->moduleID][$cKey] = $cValue;

        return $this;
    }

    /**
     * @param string|null $cKey
     * @return $this
     */
    public function unsetCache($cKey = null)
    {
        if ($cKey === null) {
            unset($_SESSION[$this->moduleID]);
        } else {
            unset($_SESSION[$this->moduleID][$cKey]);
        }

        return $this;
    }

    /**
     * @param null|string $cKey
     * @return null
     */
    public function getCache($cKey = null)
    {
        if ($cKey === null) {
            return isset($_SESSION[$this->moduleID])
                ? $_SESSION[$this->moduleID]
                : null;
        }

        return isset($_SESSION[$this->moduleID][$cKey])
            ? $_SESSION[$this->moduleID][$cKey]
            : null;
    }

    /**
     * @param int $kBestellung
     * @param int $kSprache
     * @return object
     */
    public function createInvoice($kBestellung, $kSprache)
    {
        $oInvoice        = new stdClass();
        $oInvoice->nType = 0;
        $oInvoice->cInfo = '';

        return $oInvoice;
    }

    /**
     * @param int $kBestellung
     * @return $this
     */
    public function reactivateOrder($kBestellung)
    {
        $kBestellung = (int)$kBestellung;
        $this->sendMail($kBestellung, MAILTEMPLATE_BESTELLUNG_RESTORNO);
        $_upd                = new stdClass();
        $_upd->cStatus       = BESTELLUNG_STATUS_IN_BEARBEITUNG;
        $_upd->dBezahltDatum = 'now()';
        Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, $_upd);

        return $this;
    }

    /**
     * @param int  $kBestellung
     * @param bool $bDelete
     * @return $this
     */
    public function cancelOrder($kBestellung, $bDelete = false)
    {
        if (!$bDelete) {
            $kBestellung = (int)$kBestellung;
            $this->sendMail($kBestellung, MAILTEMPLATE_BESTELLUNG_STORNO);
            $_upd                = new stdClass();
            $_upd->cStatus       = BESTELLUNG_STATUS_STORNO;
            $_upd->dBezahltDatum = 'now()';
            Shop::DB()->update('tbestellung', 'kBestellung', $kBestellung, $_upd);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        // overwrite
        return false;
    }

    /**
     * @param int  $kBestellung
     * @param int  $nType
     * @param null $oAdditional
     * @return $this
     */
    public function sendMail($kBestellung, $nType, $oAdditional = null)
    {
        $oOrder = new Bestellung($kBestellung);
        $oOrder->fuelleBestellung(0);
        $oCustomer = new Kunde($oOrder->kKunde);
        $oMail     = new stdClass();

        switch ($nType) {
            case MAILTEMPLATE_BESTELLBESTAETIGUNG:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_AKTUALISIERT:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_VERSANDT:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_TEILVERSANDT:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_BEZAHLT:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (($oOrder->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG) && strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_STORNO:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (($oOrder->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_RESTORNO:
                $oMail->tkunde      = $oCustomer;
                $oMail->tbestellung = $oOrder;
                if (($oOrder->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_RESTORNO) && strlen($oCustomer->cMail) > 0) {
                    sendeMail($nType, $oMail, $oAdditional);
                }
                break;

            default:
                break;
        }

        return $this;
    }

    /**
     * @param string $moduleId
     * @return PaymentMethod
     */
    public static function create($moduleId)
    {
        global $oPlugin;
        $oTmpPlugin    = $oPlugin;
        $paymentMethod = null;
        // Zahlungsart als Plugin
        $kPlugin = gibkPluginAuscModulId($moduleId);
        if ($kPlugin > 0) {
            $oPlugin            = new Plugin($kPlugin);
            $GLOBALS['oPlugin'] = $oPlugin;

            if ($oPlugin->kPlugin > 0) {
                require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
                    PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
                    PFAD_PLUGIN_PAYMENTMETHOD . $oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleId]->cClassPfad;
                $className               = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleId]->cClassName;
                $paymentMethod           = new $className($moduleId);
                $paymentMethod->cModulId = $moduleId;
            }
        } elseif ($moduleId === 'za_paypal_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paypal/PayPal.class.php';
            $paymentMethod = new PayPal($moduleId);
        } elseif ($moduleId === 'za_ipayment_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ipayment/iPayment.class.php';
            $paymentMethod = new iPayment($moduleId);
        } elseif ($moduleId === 'za_worldpay_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'worldpay/WorldPay.class.php';
            $paymentMethod = new WorldPay($moduleId);
        } elseif ($moduleId === 'za_sofortueberweisung_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'sofortueberweisung/SofortUeberweisung.class.php';
            $paymentMethod = new SofortUeberweisung($moduleId);
        } elseif ($moduleId === 'za_wirecard_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'wirecard/Wirecard.class.php';
            $paymentMethod = new Wirecard($moduleId);
        } elseif ($moduleId === 'za_postfinance_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'postfinance/PostFinance.class.php';
            $paymentMethod = new PostFinance($moduleId);
        } elseif ($moduleId === 'za_paymentpartner_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paymentpartner/PaymentPartner.class.php';
            $paymentMethod = new PaymentPartner($moduleId);
        } elseif (strpos($moduleId, 'za_mbqc_') === 0) {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'moneybookers_qc/MoneyBookersQC.class.php';
            $paymentMethod = new MoneyBookersQC($moduleId);
        } elseif ($moduleId === 'za_ut_stand_jtl') {
            // United Transfer
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
            $paymentMethod           = new UT($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_ut_dd_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
            $paymentMethod           = new UT($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_ut_cc_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
            $paymentMethod           = new UT($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_ut_prepaid_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
            $paymentMethod           = new UT($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_ut_gi_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
            $paymentMethod           = new UT($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_ut_ebank_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
            $paymentMethod           = new UT($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_billpay_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/Billpay.class.php';
            $paymentMethod           = new Billpay($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_billpay_invoice_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/BillpayInvoice.class.php';
            $paymentMethod           = new BillpayInvoice($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_billpay_direct_debit_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/BillpayDirectDebit.class.php';
            $paymentMethod           = new BillpayDirectDebit($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_billpay_rate_payment_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/BillpayRatePayment.class.php';
            $paymentMethod           = new BillpayRatePayment($moduleId);
            $paymentMethod->cModulId = $moduleId;
        } elseif ($moduleId === 'za_billpay_paylater_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'billpay/BillpayPaylater.class.php';
            $paymentMethod           = new BillpayPaylater($moduleId);
            $paymentMethod->cModulId = $moduleId;
        }
        $oPlugin = $oTmpPlugin;

        return $paymentMethod;
    }
}
