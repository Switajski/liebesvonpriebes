<?php

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI;

/**
 * Class PayPalHelper.
 */
class PayPalHelper
{
    /**
     * @var array
     */
    public $config;

    /**
     * @var Plugin
     */
    public $oPlugin;

    /**
     * @var string
     */
    public $currencyISO;

    /**
     * @var string
     */
    public $moduleID;

    /**
     * @param Plugin $oPlugin
     * @param array  $config
     * @param string $currencyISO
     * @param string $moduleID
     */
    public function __construct(Plugin $oPlugin, array $config, $currencyISO, $moduleID)
    {
        $this->oPlugin     = $oPlugin;
        $this->config      = $config;
        $this->currencyISO = $currencyISO;
        $this->moduleID    = $moduleID;
    }

    /**
     * @return array
     */
    public function test()
    {
        $error  = false;
        $result = array(
            'status' => 'success',
            'code'   => 0,
            'msg'    => '',
            'mode'   => $this->config['mode'],
        );
        $response      = new stdClass();
        $response->Ack = 'Failure';
        if (!isset($this->config['acct1.UserName']) || strlen($this->config['acct1.UserName']) < 1) {
            $error            = true;
            $result['status'] = 'failure';
            $result['code']   = 1;
            $result['msg'] .= 'User name not set. ';
        }
        if (!isset($this->config['acct1.Password']) || strlen($this->config['acct1.Password']) < 1) {
            $error            = true;
            $result['status'] = 'failure';
            $result['code']   = 1;
            $result['msg'] .= 'Password not set. ';
        }
        if (!isset($this->config['acct1.Signature']) || strlen($this->config['acct1.Signature']) < 1) {
            $error            = true;
            $result['status'] = 'failure';
            $result['code']   = 1;
            $result['msg'] .= 'Signature not set. ';
        }

        if ($error === false) {
            $getBalanceReq                          = new PayPalAPI\GetBalanceReq();
            $getBalanceRequest                      = new PayPalAPI\GetBalanceRequestType();
            $getBalanceRequest->ReturnAllCurrencies = '1';
            $getBalanceReq->GetBalanceRequest       = $getBalanceRequest;
            $service                                = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
            try {
                $response = $service->GetBalance($getBalanceReq);
            } catch (Exception $e) {
                $result['msg'] .= $e->getMessage();
                $result['code']   = 2;
                $result['status'] = 'failure';

                return $result;
            }
        }

        $result['status'] = strtolower($response->Ack);
        if (isset($response->Errors)) {
            foreach ($response->Errors as $_error) {
                $result['msg'] .= $_error->ShortMessage;
            }
            $result['code'] = 3;
        }

        return $result;
    }

    /**
     * @param string $token
     *
     * @return PayPal\EBLBaseComponents\GetExpressCheckoutDetailsResponseDetailsType|bool
     */
    public function GetExpressCheckoutDetails($token)
    {
        $getExpressCheckoutDetailsReq                                   = new PayPalAPI\GetExpressCheckoutDetailsReq();
        $getExpressCheckoutDetailsRequest                               = new PayPalAPI\GetExpressCheckoutDetailsRequestType($token);
        $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
        $service                                                        = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        try {
            $response = $service->GetExpressCheckoutDetails($getExpressCheckoutDetailsReq);
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return false;
        }
        if ($response->Ack === 'Success') {
            return $response->GetExpressCheckoutDetailsResponseDetails;
        } else {
            //			$logger->error("API Error Message : ". $response->Errors[0]->LongMessage);
        }

        return false;
    }

    /**
     * @param Bestellung $order
     * @param string     $token
     * @param string     $payerID
     * @param string     $payer
     *
     * @return bool
     */
    public function doExpressCheckout($order, $token, $payerID, $payer)
    {
        $doExpressCheckoutPaymentReq                          = new PayPalAPI\DoExpressCheckoutPaymentReq();
        $doExpressCheckoutPaymentRequestDetails               = new \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType();
        $doExpressCheckoutPaymentRequestDetails->Token        = $token;//$_SESSION['reshash']['Token'];
        $doExpressCheckoutPaymentRequestDetails->PayerID      = $payerID;//$_SESSION['reshash']['PayerID'];
        $doExpressCheckoutPaymentRequestDetails->ButtonSource = 'JTL_Cart_ECM_CPI';

        $oArtikelString                   = new stdClass();
        $oArtikelString->kZaehler         = 0;
        $oArtikelString->nvpStr           = '';
        $oArtikelString->gesamtbetrag     = 0;
        $oArtikelString->fZahlungsgebuehr = 0;
        $paymentDetailsList               = array();

        foreach ($order->Positionen as $oPosition) {
            if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ZAHLUNGSART) {
                $oArtikelString->fZahlungsgebuehr = berechneBrutto($oPosition->fPreis, $oPosition->fMwSt);
            }
            if (isset($oPosition->kSteuerklasse)) {
                $ArticleAmount = $oPosition->fPreis * $_SESSION['Waehrung']->fFaktor * ((100 + gibUst($oPosition->kSteuerklasse)) / 100);
            } else {
                $ArticleAmount = $oPosition->fPreis * $_SESSION['Waehrung']->fFaktor * ((100 + $oPosition->fMwSt) / 100);
            }
            if ($ArticleAmount < 0) {
                $ArticleAmount = $ArticleAmount * pow(10, 2) - 0.5;
                $ArticleAmount = ceil($ArticleAmount) * pow(10, -2);
            } else {
                $ArticleAmount = round($ArticleAmount, 2);
            }
            $qty = 1;
            if (is_array($oPosition->cName)) {
                $name = $oPosition->cName[$this->cISOSprache];
            } else {
                $name = $oPosition->cName;
            }

            if ((int) $oPosition->nAnzahl != $oPosition->nAnzahl) {
                $ArticleAmount = round($ArticleAmount * $oPosition->nAnzahl, 2);
                if ($oPosition->Artikel->cEinheit) {
                    $name = $oPosition->nAnzahl . ' ' . $oPosition->Artikel->cEinheit . ' ' . $name;
                } else {
                    $name = $oPosition->nAnzahl . ' x ' . $name;
                }
            } else {
                $qty = (int) $oPosition->nAnzahl;
            }

            if ($ArticleAmount > 0) {
                //				$paymentDetails                = new PaymentDetailsType();
//				$orderTotal                    = new BasicAmountType($_SESSION['Waehrung']->cISO, number_format($ArticleAmount, 2, '.', ''));
//				$paymentDetails->OrderTotal    = $orderTotal;
//				$paymentDetails->PaymentAction = 'Sale';
//
//				$paymentDetailsList[] = $paymentDetails;

                $oArtikelString->gesamtbetrag += $ArticleAmount * $qty;
                ++$oArtikelString->kZaehler;
            }
        }
//		echo '<br>gesamt:';
//		var_dump($oArtikelString->gesamtbetrag);
        //die();
        $paymentDetails = new PaymentDetailsType();
        $orderTotal     = new BasicAmountType($_SESSION['Waehrung']->cISO,
            number_format($oArtikelString->gesamtbetrag, 2, '.', ''));
        $paymentDetails->OrderTotal    = $orderTotal;
        $paymentDetails->PaymentAction = 'Sale';

        $paymentDetailsList[] = $paymentDetails;

        $doExpressCheckoutPaymentRequestDetails->PaymentDetails       = $paymentDetailsList;
        $doExpressCheckoutPaymentRequest                              = new PayPalAPI\DoExpressCheckoutPaymentRequestType($doExpressCheckoutPaymentRequestDetails);
        $doExpressCheckoutPaymentReq->DoExpressCheckoutPaymentRequest = $doExpressCheckoutPaymentRequest;

        $service = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);

        try {
            $response = $service->DoExpressCheckoutPayment($doExpressCheckoutPaymentReq);
        } catch (Exception $e) {
        }
//		Shop::dbg($response, false, 'response from DoExpressCheckoutPayment:');

        if ($response->Ack === 'Success' || $response->Ack === 'SuccessWithWarning') { //@todo: SuccessWithWarning only for debugging - remove
            //set payment
            $sql = "UPDATE tbestellung SET
                                            dBezahltDatum=now(),
                                            cStatus='" . BESTELLUNG_STATUS_BEZAHLT . "' WHERE kBestellung=" . intval($order->kBestellung);
            if (class_exists('Shop')) {
                Shop::DB()->query($sql, 4);
            } else {
                $GLOBALS['DB']->executeQuery($sql, 4);
            }
            $bestellung = new Bestellung($order->kBestellung);
            $bestellung->fuelleBestellung(0);
            //process payment
            $paymentDateTmp                     = strtotime('now');
            $zahlungseingang                    = new stdClass();
            $zahlungseingang->kBestellung       = $bestellung->kBestellung;
            $zahlungseingang->cZahlungsanbieter = 'PayPal';
            $zahlungseingang->fBetrag           = $oArtikelString->gesamtbetrag;
            $zahlungseingang->fZahlungsgebuehr  = $oArtikelString->fZahlungsgebuehr;
            $zahlungseingang->cISO              = $this->currencyISO;
            $zahlungseingang->cEmpfaenger       = '';
            $zahlungseingang->cZahler           = $payer;
            $zahlungseingang->cAbgeholt         = 'N';
            $zahlungseingang->cHinweis          = $response->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
            $zahlungseingang->dZeit             = strftime('%Y-%m-%d %H:%M:%S', $paymentDateTmp);
            if (class_exists('Shop')) {
                Shop::DB()->insert('tzahlungseingang', $zahlungseingang);
            } else {
                $GLOBALS['DB']->insertRow('tzahlungseingang', $zahlungseingang);
            }

            return $response;
        } else {
            $debugString = "\n\nSESSION Reshash:\n";
            $debugString .= print_r($_SESSION['reshash'], true);
            $debugString .= "\n\nPayment Status:\n";
            $debugString .= print_r($response, true);
            $debugString .= "\n\nPOST Request:\n";
            ZahlungsLog::add($this->moduleID, "preparePaymentProcess error:\n" . $debugString, '', LOGLEVEL_ERROR);
        }
        unset($_SESSION['paypal']);

        return false;
    }

    public static function getLinkByName(&$plugin, $name)
    {
        foreach ($plugin->oPluginFrontendLink_arr as $link) {
            if (strcasecmp($link->cName, $name) === 0) {
                return $link;
            }
        }

        return;
    }

    public static function addSurcharge()
    {
        if (isset($_SESSION['Zahlungsart']->cAufpreisTyp) && $_SESSION['Zahlungsart']->cAufpreisTyp === 'prozent') {
            $Aufpreis = ($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array('1'), 1) * $_SESSION['Zahlungsart']->fAufpreis) / 100.0;
        } else {
            $Aufpreis = (isset($_SESSION['Zahlungsart']->fAufpreis)) ? $_SESSION['Zahlungsart']->fAufpreis : 0;
        }
        if ($Aufpreis != 0) {
            $_SESSION['Warenkorb']->erstelleSpezialPos(
                $_SESSION['Zahlungsart']->angezeigterName,
                1,
                $Aufpreis,
                $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse(),
                C_WARENKORBPOS_TYP_ZAHLUNGSART,
                true
            );
        }
    }

    /**
     * @param Bestellung $order
     * @param string     $token
     * @param string     $payerID
     * @param string     $payer
     *
     * @return bool
     */
    public static function getBasket()
    {
        $helper = new WarenkorbHelper();
        $basket = $helper->getTotal();

        $rounding = function ($prop) {
            return [
                WarenkorbHelper::NET   => (float) number_format($prop[WarenkorbHelper::NET], 2, '.', ''),
                WarenkorbHelper::GROSS => (float) number_format($prop[WarenkorbHelper::GROSS], 2, '.', ''),
            ];
        };

        $article = [
            WarenkorbHelper::NET   => 0,
            WarenkorbHelper::GROSS => 0,
        ];

        foreach ($basket->items as $i => &$p) {
            $p->amount = $rounding($p->amount);

            $p->amount[WarenkorbHelper::NET]   = $p->amount[WarenkorbHelper::NET] * $p->quantity;
            $p->amount[WarenkorbHelper::GROSS] = $p->amount[WarenkorbHelper::GROSS] * $p->quantity;

            // $p->quantity = 1;
            $p->amount = $rounding($p->amount);

            $article[WarenkorbHelper::NET] += $p->amount[WarenkorbHelper::NET];
            $article[WarenkorbHelper::GROSS] += $p->amount[WarenkorbHelper::GROSS];
        }

        $total = $basket->total;

        $basket->article   = $rounding($article);
        $basket->shipping  = $rounding($basket->shipping);
        $basket->discount  = $rounding($basket->discount);
        $basket->surcharge = $rounding($basket->surcharge);

        $basket->total[WarenkorbHelper::NET]   = $basket->article[WarenkorbHelper::NET] + $basket->shipping[WarenkorbHelper::NET] - $basket->discount[WarenkorbHelper::NET] + $basket->surcharge[WarenkorbHelper::NET];
        $basket->total[WarenkorbHelper::GROSS] = $basket->article[WarenkorbHelper::GROSS] + $basket->shipping[WarenkorbHelper::GROSS] - $basket->discount[WarenkorbHelper::GROSS] + $basket->surcharge[WarenkorbHelper::GROSS];
        $basket->total                         = $rounding($basket->total);

        $basket->diff = [
            WarenkorbHelper::NET   => $total[WarenkorbHelper::NET] - $basket->total[WarenkorbHelper::NET],
            WarenkorbHelper::GROSS => $total[WarenkorbHelper::GROSS] - $basket->total[WarenkorbHelper::GROSS],
        ];

        $basket->diff = $rounding($basket->diff);

        if ($basket->diff[WarenkorbHelper::NET] < 0) {
            $basket->diff[WarenkorbHelper::NET] *= -1;
        }

        if ($basket->diff[WarenkorbHelper::GROSS] < 0) {
            $basket->diff[WarenkorbHelper::GROSS] *= -1;
        }

        $basket->total[WarenkorbHelper::NET] += $basket->diff[WarenkorbHelper::NET];
        $basket->total[WarenkorbHelper::GROSS] += $basket->diff[WarenkorbHelper::GROSS];

        return $basket;
    }
}
