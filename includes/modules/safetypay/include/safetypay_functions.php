<?php

/**
 * Get Currencies for 'ToCurrency'
 *
 * @param $proxySTP
 * @param $languageShop
 * @param $Currency
 * @param $txtAmount
 * @param $txtToCurrency
 * @param $calculationQuoteReferenceNo
 * @param $calculationQuoteToAmount
 * @return string
 */
function stp_GetCurrencies($proxySTP, $languageShop, $Currency, $txtAmount, &$txtToCurrency, &$calculationQuoteReferenceNo, &$calculationQuoteToAmount)
{
    $optionsCurrencies = '';
    $Result            = $proxySTP->GetCurrencies($languageShop, $Currency);
    if ($Result['ErrorManager']['ErrorNumber'] == '0') {
        if (isset($Result['ListOfCurrencies']['Currency'])) {
            foreach ($Result['ListOfCurrencies']['Currency'] as $key => $value) {
                if ($txtToCurrency == $value['Code']) {
                    $txtToCurrency = $value['Code'];
                    $optionsCurrencies .=  '<option value="' . $value['Code'] . '" selected>' . $value['Name'] . "</option>\n";
                } elseif ($value['Code'] === 'USD' && $key == 0) {
                    $txtToCurrency = $value['Code'];
                    $optionsCurrencies .=  '<option value="' . $value['Code'] . '" selected>' . $value['Name'] . "</option>\n";
                } else {
                    $optionsCurrencies .=  '<option value="' . $value['Code'] . '">' . $value['Name'] . "</option>\n";
                }
            }
            if ($txtToCurrency != '') {
                // Get Conversion Rate of Amount Order Total
                $Result = $proxySTP->CalculationQuote($Currency, $txtAmount, $txtToCurrency);
                if ($Result['ErrorManager']['ErrorNumber'] == '0') {
                    $calculationQuoteReferenceNo = $Result['FxCalculationQuote']['ReferenceNo'];
                    $calculationQuoteToAmount    = $Result['FxCalculationQuote']['ToCurrency']['Symbol'] . '&nbsp;' . $Result['FxCalculationQuote']['ToCurrency']['Code'] . '&nbsp;' . $Result['FxCalculationQuote']['ToAmount'] . '&nbsp;' . $Result['FxCalculationQuote']['ToCurrency']['Name'];
                }
            }
        } else {
            $optionsCurrencies = 'List Of Currencies not Found';
        }
    } else {
        $optionsCurrencies = '<option value="">Error: ' . $Result['ErrorManager']['ErrorNumber'] . ' - ' . $Result['ErrorManager']['Description'] . '</option>';
    }

    return $optionsCurrencies;
}

/**
 * Get Banks availables for 'BankID'
 *
 * @param $proxySTP
 * @param $txtToCurrency
 * @return string
 */
function stp_GetBanks($proxySTP, $txtToCurrency)
{
    $optionsBanks = '';
    $Result       = $proxySTP->GetBanks($txtToCurrency);

    if ($Result['ErrorManager']['ErrorNumber'] == '0') {
        if (isset($Result['ListOfBanks']['Bank'])) {
            if (isset($Result['ListOfBanks']['Bank']['Country'])) {
                $optionsBanks .=  '<option value="' . $Result['ListOfBanks']['Bank']['BankCode'] . '">' . $Result['ListOfBanks']['Bank']['BankName'] . "</option>\n";
            } else {
                foreach ($Result['ListOfBanks']['Bank'] as $key => $value) {
                    $optionsBanks .=  '<option value="' . $value['BankCode'] . '">' . $value['BankName'] . "</option>\n";
                }
            }
        } else {
            $optionsBanks = "<option value=''>Not available banks. Select other ToCurrency.</option>\n";
        }
    } else {
        $optionsBanks = '<option value="">Error: ' . $Result['ErrorManager']['ErrorNumber'] . ' - ' . $Result['ErrorManager']['Description'] . "</option>\n";
    }

    return $optionsBanks;            // return string data
}

/**
 * Create Transaction
 *
 * @param $proxySTP
 * @param $Currency
 * @param $txtAmount
 * @param $ToCurrency
 * @param $MerchantReferenceNo
 * @param $languageShop
 * @param $calculationQuoteReferenceNo
 * @param $TrackingCode
 * @param $BankID
 * @param $urlOK
 * @param $urlKO
 * @return string
 */
function stp_CreateTransaction($proxySTP, $Currency, $txtAmount, $ToCurrency, $MerchantReferenceNo, $languageShop, $calculationQuoteReferenceNo, $TrackingCode, $BankID, $urlOK, $urlKO)
{
    unset($TransactionData);
    $Result = $proxySTP->CreateTransaction($Currency, $txtAmount, $ToCurrency, $MerchantReferenceNo, $languageShop, $calculationQuoteReferenceNo, $TrackingCode, $BankID, $urlOK, $urlKO);
    if ($Result['ErrorManager']['ErrorNumber'] == '0') {
        $TransactionData = $Result['Transaction'];
    } else {
        $TransactionData .= 'Error: ' . $Result['ErrorManager']['ErrorNumber'] . ' - ' . $Result['ErrorManager']['Description'] . "\n";
    }

    return $TransactionData;        // return array or string data
}
