<?php

/**
 * Class LPACurrencyHelper
 * Helper class for everything that has to do with multicurrency handling.
 */
require_once(__DIR__ . '/lpa_defines.php');

class LPACurrencyHelper {
    protected static $supportedCurrencies = array('AUD', 'GBP', 'DKK', 'EUR', 'HKD', 'JPY', 'NZD', 'NOK', 'ZAR', 'SEK', 'CHF', 'USD');

    public static function isSupportedCurrency($currencyCode) {
        if (empty($currencyCode)) {
            return false;
        }
        return in_array(strtoupper($currencyCode), self::$supportedCurrencies);
    }

    public static function isExcludedCurrency($currencyCode) {
        $excludedCurrencies = Shop::DB()->select(S360_LPA_TABLE_CONFIG, 'cName', S360_LPA_CONFKEY_EXCLUDED_CURRENCIES);
        if (!empty($excludedCurrencies)) {
            $excludedCurrenciesArray = explode(',', $excludedCurrencies->cWert);
            return in_array($currencyCode, $excludedCurrenciesArray);
        }
        return false;
    }

    public static function convertAmount($amount, $fromCurrencyISO, $toCurrencyISO) {
        /*
         * Currencies in the database have a conversion factor.
         * To get to the desired value, we need to divide the amount by the factor of the fromCurrency (the price is then normalized to the
         * standard shop currency).
         * Then we have to multiply the result with the toCurrency to get the value in the target currency. Note that this also works if from or to currency are the default currency!
         */
        $fromCurrency = new stdClass();
        if (!empty($fromCurrencyISO)) {
            $fromCurrency = Shop::DB()->select('twaehrung', 'cISO', $fromCurrencyISO);
        } else {
            $fromCurrency->fFaktor = 1;
        }
        $toCurrency = Shop::DB()->select('twaehrung', 'cISO', $toCurrencyISO);
        if (empty($fromCurrency) || empty($toCurrency)) {
            Jtllog::writeLog("LPA: Fehler bei der Währungskonvertierung. Ausgangswährung '{$fromCurrencyISO}' oder Zielwährung '{$toCurrencyISO}' nicht gefunden. Es wird nicht konvertiert.", JTLLOG_LEVEL_ERROR);
            return $amount;
        }
        $result = $amount;
        $result /= $fromCurrency->fFaktor;
        $result *= $toCurrency->fFaktor;
        return $result;
    }

    public static function getAvailableSupportedCurrencies($ignoreExcluded = false) {
        $result = array();
        $currencies = Shop::DB()->executeQueryPrepared('SELECT * FROM twaehrung', array(), 2);

        if (!empty($currencies)) {
            foreach ($currencies as $currency) {
                if (self::isSupportedCurrency($currency->cISO)) {
                    if($ignoreExcluded || !self::isExcludedCurrency($currency->cISO)) {
                        $result[] = $currency;
                    }
                }
            }
        }
        return $result;
    }

    public static function getCurrentCurrency() {
        if (isset($_SESSION['Waehrung'], $_SESSION['Waehrung']->kWaehrung) && $_SESSION['Waehrung']->kWaehrung) {
            $currentCurrency = $_SESSION['Waehrung'];
        } else {
            $currentCurrency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        }
        return $currentCurrency;
    }

    public static function getDefaultCurrency() {
        return Shop::DB()->select('twaehrung', 'cStandard', 'Y');
    }
}