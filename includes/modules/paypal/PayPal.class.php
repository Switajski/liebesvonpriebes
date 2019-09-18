<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

// Debug
ifndef('PP_D_MODE', 0); // 1 = An / 0 = Aus
ifndef('PP_D_PFAD', PFAD_LOGFILES . 'paypal.log');
// Sandbox
ifndef('URL_TEST', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
ifndef('URLVALID_TEST', 'ssl://www.sandbox.paypal.com');
ifndef('URLHOST_TEST', 'www.sandbox.paypal.com');
// Live
ifndef('URL_LIVE', 'https://www.paypal.com/cgi-bin/webscr');
ifndef('URLVALID_LIVE', 'ssl://www.paypal.com');
ifndef('URLHOST_LIVE', 'www.paypal.com');

/**
 * Class PayPal
 */
class PayPal extends PaymentMethod
{
    /**
     * @var array
     */
    public $oPosition_arr = [];

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        global $Einstellungen;

        parent::init($nAgainCheckout);

        $this->name    = 'PayPal';
        $this->caption = 'PayPal';

        // Mode
        $nMode = (isset($Einstellungen['zahlungsarten']['zahlungsart_paypal_modus']) && 
            $Einstellungen['zahlungsarten']['zahlungsart_paypal_modus'] === 'T') 
            ? 1 
            : 0;
        if (!defined('PP_MODE')) {
            define('PP_MODE', $nMode);
        } // 1 = Test / 0 = Live

        return $this;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        global $Einstellungen;
        $hash            = $this->generateHash($order);
        $cISOSprache     = '';
        $cISOSprache_arr = [
            'FR',
            'ES',
            'IT',
            'DE',
            'CN',
            'AU',
            'EN'
        ];
        if (strlen($_SESSION['cISOSprache']) > 0) {
            $cISOSprache = StringHandler::convertISO2ISO639($_SESSION['cISOSprache']);
        } else {
            $oSprache = Shop::DB()->query("SELECT kSprache, cISO FROM tsprache WHERE cShopStandard = 'Y'", 1);
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $cISOSprache = StringHandler::convertISO2ISO639($oSprache->cISO);
            }
        }
        if (!in_array(strtoupper($cISOSprache), $cISOSprache_arr, true)) {
            $cISOSprache = 'DE';
        }
        if (isset($order->kLieferadress, $order->Lieferadresse) && (int)$order->kLieferadresse > 0) {
            $oLieferadresse = $order->Lieferadresse;
        } else {
            $oLieferadresse = $order->oRechnungsadresse;
        }
        $cCountryISO = $oLieferadresse->cLand;
        if (strlen($cCountryISO) > 2) {
            $cCountryISO = landISO($cCountryISO);
        }

        $cancelUrl = Shop::getURL();
        if ($order->kBestellung > 0) {
            $cancelUrl .= '/jtl.php?bestellung=' . $order->kBestellung;
        }

        $fields = [
            'cmd'              => '_xclick',
            'business'         => $Einstellungen['zahlungsarten']['zahlungsart_paypal_empfaengermail'],
            // Sprache, W?hrung
            'currency_code'    => $order->Waehrung->cISO,
            'country'          => $cCountryISO,
            // Lieferadresse
            'address1'         => $oLieferadresse->cStrasse . ' ' . $oLieferadresse->cHausnummer,
            'address2'         => $oLieferadresse->cAdressZusatz,
            'city'             => $oLieferadresse->cOrt,
            'first_name'       => $oLieferadresse->cVorname,
            'last_name'        => $oLieferadresse->cNachname,
            'zip'              => $oLieferadresse->cPLZ,
            'address_override' => '1',
            'item_name'        => Shop::Lang()->get('order', 'global') . ' ' . $order->cBestellNr,
            'amount'           => round($order->fWarensummeKundenwaehrung, 2),
            'shipping'         => round($order->fVersandKundenwaehrung, 2),
            'invoice'          => $order->cBestellNr,
            'email'            => $order->oKunde->cMail,
            'upload'           => '1',
            'cancel_return'    => $cancelUrl,
            'return'           => Shop::getURL() . '/bestellabschluss.php?i=' . $hash,
            'custom'           => $hash,
            'lc'               => strtoupper($cISOSprache),
            'notify_url'       => $this->getNotificationURL($hash),
            'bn'               => 'JTLSoftwareGmbH_Cart'
        ];
        if (strlen($oLieferadresse->cBundesland) > 0) {
            $cISO            = Staat::getRegionByName($oLieferadresse->cBundesland);
            $fields['state'] = is_object($cISO) ?
                $cISO->cCode
                : $oLieferadresse->cBundesland;
        }
        if ($fields['amount'] <= 0 && $fields['shipping'] > 0) {
            $fields['amount'] += $fields['shipping'];
            $fields['shipping'] = 0;
        }
        if (PP_D_MODE === 1) {
            writeLog(PP_D_PFAD, var_export($fields, true), 1);
        }

        Shop::Smarty()->assign('fields', $fields)
            ->assign('url', PP_MODE === 1 ? URL_TEST : URL_LIVE);
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        if ($this->verifyNotification($order, $paymentHash, $args)) {
            $zahlungsid = Shop::DB()->select('tzahlungsid', 'cId', $args['custom']);

            if (PP_D_MODE === 1) {
                writeLog(PP_D_PFAD, ' zahlungsid= ' . var_export($zahlungsid, true), 1);
            }

            // Zahlungseingang darf nur einmal gesetzt werden.
            // Falls jedoch mehrere Notifications ankommen, darf nur einmal
            // der Zahlungseingang gesetzt werden
            if (isset($zahlungsid->kBestellung) && (int)$zahlungsid->kBestellung > 0) {
                $oZahlungseingang = Shop::DB()->query(
                    "SELECT kZahlungseingang
                        FROM tzahlungseingang
                        WHERE kBestellung = {$zahlungsid->kBestellung}", 1
                );

                if (is_object($oZahlungseingang) && 
                    isset($oZahlungseingang->kZahlungseingang) && 
                    (int)$oZahlungseingang->kZahlungseingang > 0
                ) {
                    die('0');
                }
            }

            $b       = Shop::DB()->query("
                SELECT kKunde 
                    FROM tbestellung 
                    WHERE kBestellung = " . (int)$zahlungsid->kBestellung, 1
            );
            $kunde   = new Kunde($b->kKunde);
            $Sprache = Shop::DB()->query("
                SELECT cISO 
                    FROM tsprache 
                    WHERE kSprache = " . (int)$kunde->kSprache, 1
            );
            if (!$Sprache) {
                $Sprache = Shop::DB()->query("SELECT cISO FROM tsprache WHERE cShopStandard = 'Y'", 1);
            }

            $bestellung = new Bestellung($zahlungsid->kBestellung);
            $bestellung->fuelleBestellung(0);
            if ($bestellung->Waehrung->cISO !== $_POST['mc_currency']) {
                if (PP_D_MODE === 1) {
                    writeLog(PP_D_PFAD, 'Falsche Waehrung: ' . $bestellung->Waehrung->cISO . ' != ' . 
                        StringHandler::filterXSS($_POST['mc_currency']), 1);
                }
                die('0');
            }

            // zahlung setzen
            $_upd                = new stdClass();
            $_upd->cStatus       = BESTELLUNG_STATUS_BEZAHLT;
            $_upd->dBezahltDatum = 'now()';
            Shop::DB()->update('tbestellung', 'kBestellung', (int)$bestellung->kBestellung, $_upd);

            $bestellung = new Bestellung($zahlungsid->kBestellung);
            $bestellung->fuelleBestellung(0);

            // process payment
            $paymentDateTmp                     = strtotime($_POST['payment_date']);
            $zahlungseingang                    = new stdClass();
            $zahlungseingang->kBestellung       = $bestellung->kBestellung;
            $zahlungseingang->cZahlungsanbieter = 'PayPal';
            $zahlungseingang->fBetrag           = $_POST['mc_gross'];
            $zahlungseingang->fZahlungsgebuehr  = $_POST['payment_fee'];
            $zahlungseingang->cISO              = $_POST['mc_currency'];
            $zahlungseingang->cEmpfaenger       = $_POST['receiver_email'];
            $zahlungseingang->cZahler           = $_POST['payer_email'];
            $zahlungseingang->cAbgeholt         = 'N';
            $zahlungseingang->cHinweis          = $_POST['txn_id'];
            $zahlungseingang->dZeit             = strftime('%Y-%m-%d %H:%M:%S', $paymentDateTmp);

            Shop::DB()->insert('tzahlungseingang', $zahlungseingang);

            $this->sendMail($bestellung->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);

            echo $this->getReturnURL($order);
        }
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     * @return bool
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        $header = '';
        $req    = 'cmd=_notify-validate';

        foreach ($args as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        // post back to PayPal system to validate
        $header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Host: " . (PP_MODE === 1 ? URLHOST_TEST : URLHOST_LIVE) . "\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n";
        $header .= "Connection: Close\r\n\r\n";
        $fp       = @fsockopen(PP_MODE === 1 ? URLVALID_TEST : URLVALID_LIVE, 443, $errno, $errstr, 30);
        $verified = false;

        if (!$fp) {
            // HTTP ERROR
            if (PP_D_MODE === 1) {
                writeLog(PP_D_PFAD, "Paypal verifyNotification HTTP ERROR!\n" . $errstr . "(" . $errno . ")", 1);
            }
        } else {
            fwrite($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                if (strpos($res, 'VERIFIED') !== false) {
                    // echo the response
                    if (PP_D_MODE === 1) {
                        writeLog(PP_D_PFAD, 'VERIFIED - true', 1);
                    }

                    // check the payment_status is Completed
                    if ($args['payment_status'] !== 'Completed') {
                        if (PP_D_MODE === 1) {
                            writeLog(PP_D_PFAD, $args['payment_status'] . 
                                ' erwartet "Completed", ist "' . $args['payment_status'] . '"', 1);
                        }

                        return false;
                    }
                    // check that txn_id has not been previously processed
                    $txn_id_obj = Shop::DB()->select('tzahlungsid', 'txn_id', $args['txn_id']);
                    if (isset($txn_id_obj->kBestellung) && $txn_id_obj->kBestellung > 0) {
                        if (PP_D_MODE === 1) {
                            writeLog(PP_D_PFAD, "ZahlungsID " . $args['txn_id'] . " bereits gehabt.", 1);
                        }

                        return false;
                    }
                    // check that receiver_email is your Primary PayPal email
                    $Einstellungen = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
                    if (strtolower($Einstellungen['zahlungsarten']['zahlungsart_paypal_empfaengermail']) !==
                            strtolower($args['receiver_email']) &&
                        strtolower($Einstellungen['zahlungsarten']['zahlungsart_paypal_empfaengermail']) !==
                            strtolower($args['business'])
                    ) {
                        if (PP_D_MODE === 1) {
                            writeLog(PP_D_PFAD, "Falscher Emailempfaenger: " . $args['receiver_email'] . " != " . 
                                $Einstellungen['zahlungsarten']['zahlungsart_paypal_empfaengermail'], 1);
                        }

                        return false;
                    }
                    // check that payment_amount/payment_currency are correct

                    if ($_POST['custom']{0} === '_') {
                        checkeExterneZahlung($args['custom']);
                    } else {
                        $zahlungsid = Shop::DB()->select('tzahlungsid', 'cId', $args['custom']);
                        if (!$zahlungsid->kBestellung) {
                            if (PP_D_MODE === 1) {
                                writeLog(PP_D_PFAD, 'ZahlungsID ist unbekannt: ' . $args['custom'], 1);
                            }

                            return false;
                        }
                    }
                    $verified = true;
                }
            }
        }

        if ($verified) {
            return true;
        } elseif (PP_D_MODE === 1) {
            writeLog(PP_D_PFAD, 'PayPal verifyNotification fehlgeschlagen!', 1);
        }

        return false;
    }

    /**
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
     * @param $resultURL
     * @return mixed
     */
    public function parse($resultURL)
    {
        $r_arr       = explode('&', $resultURL);
        $returnvalue = [];
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
        if (!isset($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_paypal_empfaengermail']) ||
            strlen($GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_paypal_empfaengermail']) === 0 ||
            $GLOBALS['Einstellungen']['zahlungsarten']['zahlungsart_paypal_empfaengermail'] === ''
        ) {
            ZahlungsLog::add(
                $this->moduleID,
                'Pflichtparameter "Empfaengeremail" ist nicht gesetzt!',
                null,
                LOGLEVEL_ERROR
            );

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
