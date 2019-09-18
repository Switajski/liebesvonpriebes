<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
use PayPal\PayPalAPI;

/**
 * Class PayPalHelper.
 */
class PayPalHelper
{
    /**
     * @param bool $apiCall
     *
     * @return array
     */
    public static function test(array $config, $apiCall = true)
    {
        $error  = false;
        $result = [
            'status' => 'success',
            'code'   => 0,
            'code'   => 0,
            'msg'    => '',
            'mode'   => $config['mode'],
        ];
        $response      = new stdClass();
        $response->Ack = 'Failure';
        if (!isset($config['acct1.UserName']) || strlen($config['acct1.UserName']) < 1) {
            $error            = true;
            $result['status'] = 'failure';
            $result['code']   = 1;
            $result['msg'] .= 'User name not set. ';
        }
        if (!isset($config['acct1.Password']) || strlen($config['acct1.Password']) < 1) {
            $error            = true;
            $result['status'] = 'failure';
            $result['code']   = 1;
            $result['msg'] .= 'Password not set. ';
        }
        if (!isset($config['acct1.Signature']) || strlen($config['acct1.Signature']) < 1) {
            $error            = true;
            $result['status'] = 'failure';
            $result['code']   = 1;
            $result['msg'] .= 'Signature not set. ';
        }

        if ($apiCall === false) {
            return $result;
        }

        if ($error === false) {
            $getBalanceReq                          = new PayPalAPI\GetBalanceReq();
            $getBalanceRequest                      = new PayPalAPI\GetBalanceRequestType();
            $getBalanceRequest->ReturnAllCurrencies = '1';
            $getBalanceReq->GetBalanceRequest       = $getBalanceRequest;
            $service                                = new \PayPal\Service\PayPalAPIInterfaceServiceService($config);
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

    public static function getLanguageISO($locale = null)
    {
        if (is_integer($locale)) {
            if (($lang = Sprache::getIsoFromLangID($locale)) !== null) {
                $locale = $lang->cISO;
            }
        }

        $locale = $locale ?: Shop::getLanguage(true);

        return self::_toValidISO($locale);
    }

    public static function getCountryISO($mixed)
    {
        if (strlen($mixed) > 2) {
            if (($iso = landISO($mixed)) !== 'noISO') {
                $mixed = $iso;
            }
        }

        return self::_toValidISO($mixed);
    }

    public static function isStateRequired($iso)
    {
        $countries = ['CA', 'IT', 'NL', 'US'];

        return in_array($iso, $countries);
    }

    public static function getStateISO($mixed)
    {
        $region = Staat::getRegionByName($mixed);

        return is_object($region)
            ? $region->cCode
            : $mixed;
    }

    protected static function _toValidISO($iso)
    {
        if (strlen($iso) === 3) {
            $iso = StringHandler::convertISO2ISO639($iso);
        }

        $iso = strtoupper($iso);

        if (strlen($iso) !== 2) {
            $iso = 'DE';
        }

        return $iso;
    }

    public static function extractName($name)
    {
        $parts = explode(' ', $name, 2);
        if (count($parts) == 1) {
            array_unshift($parts, '');
        }

        return (object) [
            'first' => trim($parts[0]),
            'last'  => trim($parts[1]),
        ];
    }

    // https://gist.github.com/devotis/c574beaf73adcfd74997
    public static function extractStreet($street)
    {
        $re     = "/^(\\d*[\\wäöüß\\d '\\-\\.]+)[,\\s]+(\\d+)\\s*([\\wäöüß\\d\\-\\/]*)$/i";
        $number = '';
        if (preg_match($re, $street, $matches)) {
            $offset = strlen($matches[1]);
            $number = substr($street, $offset);
            $street = substr($street, 0, $offset);
        }

        return (object) [
            'name'   => trim($street),
            'number' => trim($number),
        ];
    }

    public static function getOrderId($invoice)
    {
        $invoice = StringHandler::filterXSS($invoice);
        $result  = Shop::DB()->query("SELECT kBestellung FROM tbestellung WHERE cBestellNr = '{$invoice}'", 1);
        if (isset($result->kBestellung) && intval($result->kBestellung) > 0) {
            return $result->kBestellung;
        }

        return false;
    }

    public static function setFlashMessage($message)
    {
        if (!isset($_SESSION['jtl_paypal_jtl']) || !is_array($_SESSION['jtl_paypal_jtl'])) {
            $_SESSION['jtl_paypal_jtl'] = [];
        }
        $_SESSION['jtl_paypal_jtl']['flash'] = $message;
    }

    public static function getFlashMessage()
    {
        return isset($_SESSION['jtl_paypal_jtl']) &&
            is_array($_SESSION['jtl_paypal_jtl']) &&
            isset($_SESSION['jtl_paypal_jtl']['flash']) ?
            $_SESSION['jtl_paypal_jtl']['flash'] : null;
    }

    public static function clearFlashMessage()
    {
        unset($_SESSION['jtl_paypal_jtl']['flash']);
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

    public static function addSurcharge($paymentId = 0)
    {
        $paymentId = $paymentId <= 0 &&
            isset($_SESSION['Zahlungsart']) &&
            isset($_SESSION['Zahlungsart']->kZahlungsart)
            ? (int) $_SESSION['Zahlungsart']->kZahlungsart : $paymentId;

        $shippingId = isset($_SESSION['Versandart']) &&
            isset($_SESSION['Versandart']->kVersandart)
            ? $_SESSION['Versandart']->kVersandart : 0;

        if ($shippingId <= 0 || $paymentId <= 0) {
            return;
        }

        $paymentMethod = new Zahlungsart();
        $paymentMethod->load($paymentId);

        if ((int)$paymentMethod->kZahlungsart <= 0) {
            return;
        }

        $surcharge = Shop::DB()->selectSingleRow(
            'tversandartzahlungsart',
            'kVersandart', $shippingId,
            'kZahlungsart', $paymentId);

        if ($surcharge !== null && is_object($surcharge)) {
            if (isset($surcharge->cAufpreisTyp) && $surcharge->cAufpreisTyp === 'prozent') {
                $amount = ($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(['1'], 1) * $surcharge->fAufpreis) / 100.0;
            } else {
                $amount = (isset($surcharge->fAufpreis)) ? $surcharge->fAufpreis : 0;
            }

            $name = $paymentMethod->cName;

            if (isset($_SESSION['Zahlungsart'])) {
                $name                                  = $_SESSION['Zahlungsart']->angezeigterName;
                $_SESSION['Zahlungsart']->fAufpreis    = $surcharge->fAufpreis;
                $_SESSION['Zahlungsart']->cAufpreisTyp = $surcharge->cAufpreisTyp;
            }

            if ($amount != 0) {
                $_SESSION['Warenkorb']->erstelleSpezialPos(
                    $name,
                    1,
                    $amount,
                    $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse(),
                    C_WARENKORBPOS_TYP_ZAHLUNGSART,
                    true
                );
            }
        }
    }

    public static function getProducts()
    {
        $oArtikel_arr = [];
        foreach ($_SESSION['Warenkorb']->PositionenArr as $Positionen) {
            if ($Positionen->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                $oArtikel_arr[] = $Positionen->Artikel;
            }
        }

        return $oArtikel_arr;
    }

    public static function getBasket($helper = null)
    {
        if ($helper === null) {
            $helper = new WarenkorbHelper();
        }

        $basket = $helper->getTotal();

        $rounding = function ($prop) {
            return [
                WarenkorbHelper::NET   => round($prop[WarenkorbHelper::NET], 2),
                WarenkorbHelper::GROSS => round($prop[WarenkorbHelper::GROSS], 2),
            ];
        };

        $article = [
            WarenkorbHelper::NET   => 0,
            WarenkorbHelper::GROSS => 0,
        ];

        foreach ($basket->items as $i => &$p) {
            $p->name   = utf8_encode($p->name);
            $p->amount = $rounding($p->amount);

            $article[WarenkorbHelper::NET] += $p->amount[WarenkorbHelper::NET] * $p->quantity;
            $article[WarenkorbHelper::GROSS] += $p->amount[WarenkorbHelper::GROSS] * $p->quantity;
        }

        $basket->article   = $rounding($article);
        $basket->shipping  = $rounding($basket->shipping);
        $basket->discount  = $rounding($basket->discount);
        $basket->surcharge = $rounding($basket->surcharge);
        $basket->total     = $rounding($basket->total);

        $calculated = [
            WarenkorbHelper::NET   => 0,
            WarenkorbHelper::GROSS => 0,
        ];

        $calculated[WarenkorbHelper::NET]   = $basket->article[WarenkorbHelper::NET] + $basket->shipping[WarenkorbHelper::NET] - $basket->discount[WarenkorbHelper::NET] + $basket->surcharge[WarenkorbHelper::NET];
        $calculated[WarenkorbHelper::GROSS] = $basket->article[WarenkorbHelper::GROSS] + $basket->shipping[WarenkorbHelper::GROSS] - $basket->discount[WarenkorbHelper::GROSS] + $basket->surcharge[WarenkorbHelper::GROSS];

        $calculated = $rounding($calculated);

        $difference = [
            WarenkorbHelper::NET   => $basket->total[WarenkorbHelper::NET] - $calculated[WarenkorbHelper::NET],
            WarenkorbHelper::GROSS => $basket->total[WarenkorbHelper::GROSS] - $calculated[WarenkorbHelper::GROSS],
        ];

        $difference = $rounding($difference);

        $addDifference = function ($difference, $type) use (&$basket) {
            if ($difference[$type] < 0.0) {
                if ($basket->shipping[$type] >= $difference[$type] * -1) {
                    $basket->shipping[$type] += $difference[$type];
                } else {
                    $basket->discount[$type] += $difference[$type] * -1;
                }
            } else {
                $basket->surcharge[$type] += $difference[$type];
            }
        };

        $addDifference($difference, WarenkorbHelper::NET);
        $addDifference($difference, WarenkorbHelper::GROSS);

        return $basket;
    }

    public static function sendPaymentDeniedMail($customer, $order)
    {
        if (!function_exists('sendeMail')) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
        }

        $mail              = new stdClass();
        $mail->tkunde      = $customer;
        $mail->tbestellung = $order;

        $plugin   = Plugin::getPluginById('jtl_paypal');
        $moduleId = 'kPlugin_' . $plugin->kPlugin . '_pppd';

        sendeMail($moduleId, $mail);
    }
}
