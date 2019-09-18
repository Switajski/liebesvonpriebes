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
     * https://developer.paypal.com/docs/classic/api/locale_codes/
     */
    public static $_locales = [
        'AL' => 'en_US',  // ALBANIA
        'DZ' => 'ar_EG',  // ALGERIA
        'AD' => 'en_US',  // ANDORRA
        'AO' => 'en_US',  // ANGOLA
        'AI' => 'en_US',  // ANGUILLA
        'AG' => 'en_US',  // ANTIGUA & BARBUDA
        'AR' => 'es_XC',  // ARGENTINA
        'AM' => 'en_US',  // ARMENIA
        'AW' => 'en_US',  // ARUBA
        'AU' => 'en_AU',  // AUSTRALIA
        'AT' => 'de_DE',  // AUSTRIA
        'AZ' => 'en_US',  // AZERBAIJAN
        'BS' => 'en_US',  // BAHAMAS
        'BH' => 'ar_EG',  // BAHRAIN
        'BB' => 'en_US',  // BARBADOS
        'BY' => 'en_US',  // BELARUS
        'BE' => 'en_US',  // BELGIUM
        'BZ' => 'es_XC',  // BELIZE
        'BJ' => 'fr_XC',  // BENIN
        'BM' => 'en_US',  // BERMUDA
        'BT' => 'en_US',  // BHUTAN
        'BO' => 'es_XC',  // BOLIVIA
        'BA' => 'en_US',  // BOSNIA & HERZEGOVINA
        'BW' => 'en_US',  // BOTSWANA
        'BR' => 'pt_BR',  // BRAZIL
        'VG' => 'en_US',  // BRITISH VIRGIN ISLANDS
        'BN' => 'en_US',  // BRUNEI
        'BG' => 'en_US',  // BULGARIA
        'BF' => 'fr_XC',  // BURKINA FASO
        'BI' => 'fr_XC',  // BURUNDI
        'KH' => 'en_US',  // CAMBODIA
        'CM' => 'fr_XC',  // CAMEROON
        'CA' => 'en_US',  // CANADA
        'CV' => 'en_US',  // CAPE VERDE
        'KY' => 'en_US',  // CAYMAN ISLANDS
        'TD' => 'fr_XC',  // CHAD
        'CL' => 'es_XC',  // CHILE
        'CN' => 'zh_CN',  // CHINA
        'C2' => 'zh_XC',  // CHINA WORLDWIDE
        'CO' => 'es_XC',  // COLOMBIA
        'KM' => 'fr_XC',  // COMOROS
        'CG' => 'en_US',  // CONGO - BRAZZAVILLE
        'CD' => 'fr_XC',  // CONGO - KINSHASA
        'CK' => 'en_US',  // COOK ISLANDS
        'CR' => 'es_XC',  // COSTA RICA
        'CI' => 'fr_XC',  // CÔTE D’IVOIRE
        'HR' => 'en_US',  // CROATIA
        'CY' => 'en_US',  // CYPRUS
        'CZ' => 'en_US',  // CZECH REPUBLIC
        'DK' => 'da_DK',  // DENMARK
        'DJ' => 'fr_XC',  // DJIBOUTI
        'DM' => 'en_US',  // DOMINICA
        'DO' => 'es_XC',  // DOMINICAN REPUBLIC
        'EC' => 'es_XC',  // ECUADOR
        'EG' => 'ar_EG',  // EGYPT
        'SV' => 'es_XC',  // EL SALVADOR
        'ER' => 'en_US',  // ERITREA
        'EE' => 'en_US',  // ESTONIA
        'ET' => 'en_US',  // ETHIOPIA
        'FK' => 'en_US',  // FALKLAND ISLANDS
        'FO' => 'da_DK',  // FAROE ISLANDS
        'FJ' => 'en_US',  // FIJI
        'FI' => 'en_US',  // FINLAND
        'FR' => 'fr_FR',  // FRANCE
        'GF' => 'en_US',  // FRENCH GUIANA
        'PF' => 'en_US',  // FRENCH POLYNESIA
        'GA' => 'fr_XC',  // GABON
        'GM' => 'en_US',  // GAMBIA
        'GE' => 'en_US',  // GEORGIA
        'DE' => 'de_DE',  // GERMANY
        'GI' => 'en_US',  // GIBRALTAR
        'GR' => 'en_US',  // GREECE
        'GL' => 'da_DK',  // GREENLAND
        'GD' => 'en_US',  // GRENADA
        'GP' => 'en_US',  // GUADELOUPE
        'GT' => 'es_XC',  // GUATEMALA
        'GN' => 'fr_XC',  // GUINEA
        'GW' => 'en_US',  // GUINEA-BISSAU
        'GY' => 'en_US',  // GUYANA
        'HN' => 'es_XC',  // HONDURAS
        'HK' => 'en_GB',  // HONG KONG SAR CHINA
        'HU' => 'en_US',  // HUNGARY
        'IS' => 'en_US',  // ICELAND
        'IN' => 'en_GB',  // INDIA
        'ID' => 'id_ID',  // INDONESIA
        'IE' => 'en_US',  // IRELAND
        'IL' => 'he_IL',  // ISRAEL
        'IT' => 'it_IT',  // ITALY
        'JM' => 'es_XC',  // JAMAICA
        'JP' => 'ja_JP',  // JAPAN
        'JO' => 'ar_EG',  // JORDAN
        'KZ' => 'en_US',  // KAZAKHSTAN
        'KE' => 'en_US',  // KENYA
        'KI' => 'en_US',  // KIRIBATI
        'KW' => 'ar_EG',  // KUWAIT
        'KG' => 'en_US',  // KYRGYZSTAN
        'LA' => 'en_US',  // LAOS
        'LV' => 'en_US',  // LATVIA
        'LS' => 'en_US',  // LESOTHO
        'LI' => 'en_US',  // LIECHTENSTEIN
        'LT' => 'en_US',  // LITHUANIA
        'LU' => 'en_US',  // LUXEMBOURG
        'MK' => 'en_US',  // MACEDONIA
        'MG' => 'en_US',  // MADAGASCAR
        'MW' => 'en_US',  // MALAWI
        'MY' => 'en_US',  // MALAYSIA
        'MV' => 'en_US',  // MALDIVES
        'ML' => 'fr_XC',  // MALI
        'MT' => 'en_US',  // MALTA
        'MH' => 'en_US',  // MARSHALL ISLANDS
        'MQ' => 'en_US',  // MARTINIQUE
        'MR' => 'en_US',  // MAURITANIA
        'MU' => 'en_US',  // MAURITIUS
        'YT' => 'en_US',  // MAYOTTE
        'MX' => 'es_XC',  // MEXICO
        'FM' => 'en_US',  // MICRONESIA
        'MD' => 'en_US',  // MOLDOVA
        'MC' => 'fr_XC',  // MONACO
        'MN' => 'en_US',  // MONGOLIA
        'ME' => 'en_US',  // MONTENEGRO
        'MS' => 'en_US',  // MONTSERRAT
        'MA' => 'ar_EG',  // MOROCCO
        'MZ' => 'en_US',  // MOZAMBIQUE
        'NA' => 'en_US',  // NAMIBIA
        'NR' => 'en_US',  // NAURU
        'NP' => 'en_US',  // NEPAL
        'NL' => 'nl_NL',  // NETHERLANDS
        'NC' => 'en_US',  // NEW CALEDONIA
        'NZ' => 'en_US',  // NEW ZEALAND
        'NI' => 'es_XC',  // NICARAGUA
        'NE' => 'fr_XC',  // NIGER
        'NG' => 'en_US',  // NIGERIA
        'NU' => 'en_US',  // NIUE
        'NF' => 'en_US',  // NORFOLK ISLAND
        'NO' => 'no_NO',  // NORWAY
        'OM' => 'ar_EG',  // OMAN
        'PW' => 'en_US',  // PALAU
        'PA' => 'es_XC',  // PANAMA
        'PG' => 'en_US',  // PAPUA NEW GUINEA
        'PY' => 'es_XC',  // PARAGUAY
        'PE' => 'es_XC',  // PERU
        'PH' => 'en_US',  // PHILIPPINES
        'PN' => 'en_US',  // PITCAIRN ISLANDS
        'PL' => 'pl_PL',  // POLAND
        'PT' => 'pt_PT',  // PORTUGAL
        'QA' => 'en_US',  // QATAR
        'RE' => 'en_US',  // RÉUNION
        'RO' => 'en_US',  // ROMANIA
        'RU' => 'ru_RU',  // RUSSIA
        'RW' => 'fr_XC',  // RWANDA
        'WS' => 'en_US',  // SAMOA
        'SM' => 'en_US',  // SAN MARINO
        'ST' => 'en_US',  // SÃO TOMÉ & PRÍNCIPE
        'SA' => 'ar_EG',  // SAUDI ARABIA
        'SN' => 'fr_XC',  // SENEGAL
        'RS' => 'en_US',  // SERBIA
        'SC' => 'fr_XC',  // SEYCHELLES
        'SL' => 'en_US',  // SIERRA LEONE
        'SG' => 'en_GB',  // SINGAPORE
        'SK' => 'en_US',  // SLOVAKIA
        'SI' => 'en_US',  // SLOVENIA
        'SB' => 'en_US',  // SOLOMON ISLANDS
        'SO' => 'en_US',  // SOMALIA
        'ZA' => 'en_US',  // SOUTH AFRICA
        'KR' => 'ko_KR',  // SOUTH KOREA
        'ES' => 'es_ES',  // SPAIN
        'LK' => 'en_US',  // SRI LANKA
        'SH' => 'en_US',  // ST. HELENA
        'KN' => 'en_US',  // ST. KITTS & NEVIS
        'LC' => 'en_US',  // ST. LUCIA
        'PM' => 'en_US',  // ST. PIERRE & MIQUELON
        'VC' => 'en_US',  // ST. VINCENT & GRENADINES
        'SR' => 'en_US',  // SURINAME
        'SJ' => 'en_US',  // SVALBARD & JAN MAYEN
        'SZ' => 'en_US',  // SWAZILAND
        'SE' => 'sv_SE',  // SWEDEN
        'CH' => 'de_DE',  // SWITZERLAND
        'TW' => 'zh_TW',  // TAIWAN
        'TJ' => 'en_US',  // TAJIKISTAN
        'TZ' => 'en_US',  // TANZANIA
        'TH' => 'th_TH',  // THAILAND
        'TG' => 'fr_XC',  // TOGO
        'TO' => 'en_US',  // TONGA
        'TT' => 'en_US',  // TRINIDAD & TOBAGO
        'TN' => 'ar_EG',  // TUNISIA
        'TM' => 'en_US',  // TURKMENISTAN
        'TC' => 'en_US',  // TURKS & CAICOS ISLANDS
        'TV' => 'en_US',  // TUVALU
        'UG' => 'en_US',  // UGANDA
        'UA' => 'en_US',  // UKRAINE
        'AE' => 'en_US',  // UNITED ARAB EMIRATES
        'GB' => 'en_GB',  // UNITED KINGDOM
        'US' => 'en_US',  // UNITED STATES
        'UY' => 'es_XC',  // URUGUAY
        'VU' => 'en_US',  // VANUATU
        'VA' => 'en_US',  // VATICAN CITY
        'VE' => 'es_XC',  // VENEZUELA
        'VN' => 'en_US',  // VIETNAM
        'WF' => 'en_US',  // WALLIS & FUTUNA
        'YE' => 'ar_EG',  // YEMEN
        'ZM' => 'en_US',  // ZAMBIA
        'ZW' => 'en_US',  // ZIMBABWE
        'EN' => 'en_US',  // EN DEFAULT
    ];

    public static function getDefaultLocale($iso)
    {
        $iso = strtoupper($iso);

        if (!isset(static::$_locales[$iso])) {
            $iso = 'DE';
        }

        return static::$_locales[$iso];
    }

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

    public static function getLanguageISO()
    {
        $languageIso = Shop::getLanguage(true);

        return self::_toValidISO($languageIso);
    }

    public static function getCustomerGroupId()
    {
        return (int)(isset($_SESSION['Kunde']) && isset($_SESSION['Kunde']->kKundengruppe)
            ? $_SESSION['Kunde']->kKundengruppe
            : Kundengruppe::getDefaultGroupID());
    }

    public static function getCountryISO()
    {
        $countryIso = isset($_SESSION['Kunde']) && $_SESSION['Kunde']->cLand
            ? $_SESSION['Kunde']->cLand
            : '';

        if (strlen($countryIso) > 2) {
            if (($iso = landISO($countryIso)) !== 'noISO') {
                $countryIso = $iso;
            }
        }

        return self::_toValidISO($countryIso);
    }

    public static function getState($address)
    {
        if (!isset($address->cLand) || !isset($address->cBundesland)) {
            return null;
        }

        if (in_array($address->cLand, ['AR', 'BR', 'IN', 'US', 'CA', 'IT', 'JP', 'MX', 'TH'])) {
            $state = Staat::getRegionByName($address->cBundesland);
            if ($state !== null) {
                return $state->cCode;
            }
        }

        return $address->cBundesland;
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
            'name'   => trim($street, "-:, "),
            'number' => trim($number, "-:, ")
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

    public static function dropPaymentPositions()
    {
        $_SESSION['Warenkorb']
            ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
            ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
            ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
            ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    }

    public static function addTrustedShops($feature)
    {
        $_SESSION['TrustedShops']->cKaeuferschutzProdukt = $feature;

        $net = $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr[$feature];
        $countryIso = isset($_SESSION['Lieferadresse']->cLand) ? $_SESSION['Lieferadresse']->cLand : '';
        $taxClass = $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($countryIso);

        $amount = ($net * ((100 + (float)$_SESSION['Steuersatz'][$taxClass]) / 100));

        $name = [
            'ger' => Shop::Lang()->get('trustedshopsName', 'global'),
            'eng' => Shop::Lang()->get('trustedshopsName', 'global')
        ];

        $_SESSION['Warenkorb']->erstelleSpezialPos(
            $name, 1, $amount, $taxClass, C_WARENKORBPOS_TYP_TRUSTEDSHOPS, true
        );
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

        if (!function_exists('plausiNeukundenKupon')) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
        }

        plausiNeukundenKupon();
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

    public static function getWebhooks(PaymentMethod $paymentMethod)
    {
        if (!$paymentMethod->isConfigured(false)) {
            return null;
        }

        try {
            return \PayPal\Api\Webhook::getAllWithParams(array(), $paymentMethod->getContext());
        } catch (Exception $ex) { }

        return null;
    }

    public static function getWebhookUrl(PaymentMethod $paymentMethod)
    {
        $url = $paymentMethod->getCallbackUrl(['a' => 'webhook'], true);
        return str_replace('http://', 'https://', $url);
    }

    public static function deleteWebhook(PaymentMethod $paymentMethod)
    {
        if (!$paymentMethod->isConfigured(false)) {
            return null;
        }

        try {
            if ($hook = static::getWebhook($paymentMethod)) {
                return $hook->delete($paymentMethod->getContext());
            }
        }
        catch (Exception $e) {}

        return false;
    }

    public static function setWebhook(PaymentMethod $paymentMethod)
    {
        if (!$paymentMethod->isConfigured(false)) {
            return null;
        }

        try {
            $webhook = new \PayPal\Api\Webhook();
            $webhook->setUrl(static::getWebhookUrl($paymentMethod))
                ->setEventTypes(array(new \PayPal\Api\WebhookEventType('{ "name": "*" }')));

            return $webhook->create($paymentMethod->getContext());
        }
        catch (Exception $e) {}

        return null;
    }

    public static function getWebhook(PaymentMethod $paymentMethod)
    {
        try {
            $url = static::getWebhookUrl($paymentMethod);
            if ($list = PayPalHelper::getWebhooks($paymentMethod)) {
                foreach ($list->getWebhooks() as $hook) {
                    if ($hook->getUrl() == $url) {
                        return $hook;
                    }
                }
            }
        }
        catch (Exception $e) {}

        return null;
    }

    public static function toObject($d)
    {
        return is_array($d) ? (object) array_map([__CLASS__, __METHOD__], $d) : $d;
    }

    public static function toArray($d) {
        $d = is_object($d) ? get_object_vars($d) : $d;
        return is_array($d) ? array_map([__CLASS__, __METHOD__], $d) : $d;
    }

    /**
     * shorten a long payment-name to 'nLimit', by inserting a placholder 'szPlaceHolder' and
     * show the 'nLastShownChars' characters
     *
     * @param $szPaymentDesc  string to shorten
     * @return string  string of length 'nLimit'
     */
    function shortenPaymentName($szPaymentDesc) {
        $nLimit          = 25; // hard limit of the payment-wall (should not be altered)
        $nLastShownChars = 0;  // (maybe 3 looks good)
        $szPlaceHolder   = '...';
        $szShortedDesc   = $szPaymentDesc;
        if ($nLimit < strlen($szPaymentDesc)) {
            $szShortedDesc   =
                  substr($szPaymentDesc, 0, ($nLimit - (strlen($szPlaceHolder) + $nLastShownChars)))
                . $szPlaceHolder
                . ($nLastShownChars ? substr($szPaymentDesc, - $nLastShownChars) : '')
            ;
        }

        return $szShortedDesc;
    }

}
