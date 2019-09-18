<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG

require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalPlus.class.php';

if (isset($_GET['refresh'])) {
    header('location: bestellvorgang.php');
    exit;
}

$Zahlungsart_arr  = [];
$oZahlungsart_arr = $smarty->get_template_vars('Zahlungsarten');
if (count($oZahlungsart_arr) > 0) {
    foreach ($oZahlungsart_arr as $key => $oZahlungsart) {
        if (!isset($oZahlungsart->cModulId) || strpos($oZahlungsart->cModulId, 'paypalexpress') === false) {
            $Zahlungsart_arr[] = $oZahlungsart;
        }
    }
    Shop::Smarty()->assign('Zahlungsarten', $Zahlungsart_arr);
}

$api        = new PayPalPlus();
$items      = PayPalHelper::getProducts();
$shippingId = $_SESSION['Versandart']->kVersandart;

if ($api->isConfigured(false) && $api->isUseable($items, $shippingId)) {
    $payment = $api->createPayment();

    if ($payment !== null) {
        $approvalUrl = $payment->getApprovalLink();

        $settings = $api->getSettings();
        $embedded = (int) $settings['jtl_paypal_psp_type'] === 0;

        $styles  = null;
        $shopUrl = Shop()->getURL(true);
        $link    = PayPalHelper::getLinkByName($oPlugin, 'PayPalPLUS');

        $thirdPartyPaymentMethods = [];

        if ($embedded) {
            $availablePayments = Shop::DB()->query('SELECT * FROM xplugin_jtl_paypal_additional_payment ORDER BY sort ASC', 2);
            $defaultPayments   = Shop::Smarty()->get_template_vars('Zahlungsarten');

            $styles                   = null;
            $sortedPayments           = [];

            foreach ($availablePayments as $p) {
                foreach ($defaultPayments as $d) {
                    if (intval($p->paymentId) == intval($d->kZahlungsart)) {
                        $sortedPayments[] = $d;
                        break;
                    }
                }
            }

            foreach ($sortedPayments as $i => $p) {
                if ($i >= 5) {
                    break;
                }

                $thirdParty = [
                    'methodName'  => utf8_encode($p->angezeigterName[Shop::$cISO]),
                    'redirectUrl' => sprintf('%s/index.php?s=%d&a=payment_method&id=%d', $shopUrl, $link->kLink, $p->kZahlungsart),
                ];

                if (!empty($p->cBild)) {
                    if (strpos($p->cBild, 'http') !== 0) {
                        $p->cBild = $shopUrl . '/' . ltrim($p->cBild, '/');
                    }
                    $thirdParty['imageUrl'] = str_replace('http://', 'https://', $p->cBild);
                }

                if (!empty($p->cHinweisText[Shop::$cISO])) {
                    $thirdParty['description'] = utf8_encode($p->cHinweisText[Shop::$cISO]);
                }

                $thirdPartyPaymentMethods[] = $thirdParty;
            }
        }

        if ($settings['jtl_paypal_style_enabled'] === 'Y') {
            $styles = (object) [
                'psp' => (object) [
                    'font-family' => $settings['jtl_paypal_style_font_family'],
                    'color'       => $settings['jtl_paypal_style_link_color'],
                    'font-size'   => $settings['jtl_paypal_style_font_size'],
                    'font-style'  => $settings['jtl_paypal_style_font_style'],
                ],

                'link' => (object) [
                    'color'           => $settings['jtl_paypal_style_link_color'],
                    'text-decoration' => $settings['jtl_paypal_style_link_text_decoration'],
                ],

                'visited' => (object) [
                    'color'           => $settings['jtl_paypal_style_visited_color'],
                    'text-decoration' => $settings['jtl_paypal_style_visited_text_decoration'],
                ],

                'active' => (object) [
                    'color'           => $settings['jtl_paypal_style_active_color'],
                    'text-decoration' => $settings['jtl_paypal_style_active_text_decoration'],
                ],

                'hover' => (object) [
                    'color'           => $settings['jtl_paypal_style_hover_color'],
                    'text-decoration' => $settings['jtl_paypal_style_hover_text_decoration'],
                ],
            ];
        }

        $language = StringHandler::convertISO2ISO639(Shop::$cISO);
        $language = sprintf('%s_%s', strtolower($language), strtoupper($language));
        $country  = $_SESSION['cLieferlandISO'];

        Shop::Smarty()->assign('language', $language)
            ->assign('country', $country)
            ->assign('embedded', $embedded)
            ->assign('payPalPlus', true)
            ->assign('mode', $api->getModus())
            ->assign('approvalUrl', $approvalUrl)
            ->assign('paymentId', $payment->getId())
            ->assign('linkId', $link->kLink)
            ->assign('styles', $styles)
            ->assign('thirdPartyPaymentMethods', $thirdPartyPaymentMethods);
    }
}
