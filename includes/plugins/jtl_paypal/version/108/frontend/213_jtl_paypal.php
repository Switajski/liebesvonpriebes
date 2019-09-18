<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

$oPlugin = Plugin::getPluginById('jtl_paypal');

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalFinance.class.php';

$args_arr['io']->register('jtl_paypal_get_presentment', function($amount, $currency) use($oPlugin) {
    $amount = (float)$amount;
    $currency = $currency !== null
        ? $currency
        : $_SESSION['Waehrung']->cISO;

    $response = new IOResponse();
    $payPalFinance = new PayPalFinance();

    if ($amount > 0 && $payPalFinance->isVisible($amount)) {
        if ($presentment = $payPalFinance->getPresentment($amount, $currency)) {
            $financingOptions = $presentment->getFinancingOptions();
            $financingOptions = $financingOptions[0]->getQualifyingFinancingOptions();

            if (count($financingOptions) > 0) {
                usort($financingOptions, function ($a, $b) {
                    if ($a->getCreditFinancing()->getTerm() > $b->getCreditFinancing()->getTerm()) {
                        return 1;
                    } elseif ($a->getCreditFinancing()->getTerm() < $b->getCreditFinancing()->getTerm()) {
                        return -1;
                    }

                    return 0;
                });

                $company             = new Firma(true);
                $bestFinancingOption = end($financingOptions);
                $transactionAmount   = $presentment->getTransactionAmount();

                $paymentMethod = new Zahlungsart();
                $paymentMethod->load($payPalFinance->paymentId);

                $tplData = Shop::Smarty()
                    ->assign('plugin', $oPlugin)
                    ->assign('company', $company)
                    ->assign('financingOptions', $financingOptions)
                    ->assign('transactionAmount', $transactionAmount)
                    ->assign('bestFinancingOption', $bestFinancingOption)
                    ->assign('paymentMethod', $paymentMethod)
                    ->fetch($oPlugin->cFrontendPfad . 'template/presentment-article.tpl');

                $tplData = utf8_convert_recursive($tplData);
                $jsonData = json_encode($tplData);

                $response->script("this.response = {$jsonData};");
            }
        }
    }

    return $response;
});
