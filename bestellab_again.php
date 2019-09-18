<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
$AktuelleSeite = 'BESTELLVORGANG';
Shop::setPageType(PAGE_BESTELLABSCHLUSS);
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_ZAHLUNGSARTEN
]);
$kBestellung   = (int)$_REQUEST['kBestellung'];
$linkHelper    = LinkHelper::getInstance();
$bestellung    = new Bestellung($kBestellung);
$bestellung->fuelleBestellung();
//abfragen, ob diese Bestellung dem Kunden auch gehoert
//bei Gastbestellungen ist ggf das Kundenobjekt bereits entfernt bzw nRegistriert = 0
if ($bestellung->oKunde !== null && (int)$bestellung->oKunde->nRegistriert === 1) {
    if ((int)$bestellung->kKunde !== (int)$_SESSION['Kunde']->kKunde) {
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true), true, 303);
        exit;
    }
}

$bestellid         = Shop::DB()->select('tbestellid', 'kBestellung', $bestellung->kBestellung);
$successPaymentURL = Shop::getURL();
if ($bestellid->cId) {
    $orderCompleteURL  = $linkHelper->getStaticRoute('bestellabschluss.php', true);
    $successPaymentURL = $orderCompleteURL . '?i=' . $bestellid->cId;
}
if (!isset($obj)) {
    $obj = new stdClass();
}
$obj->tkunde      = $_SESSION['Kunde'];
$obj->tbestellung = $bestellung;
$smarty->assign('Bestellung', $bestellung);

$oZahlungsInfo = new stdClass();
if (verifyGPCDataInteger('zusatzschritt') === 1) {
    $bZusatzangabenDa = false;
    switch ($bestellung->Zahlungsart->cModulId) {
        case 'za_kreditkarte_jtl':
            if ($_POST['kreditkartennr'] &&
                $_POST['gueltigkeit'] &&
                $_POST['cvv'] &&
                $_POST['kartentyp'] &&
                $_POST['inhaber']
            ) {
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenNr    =
                    StringHandler::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cGueltigkeit =
                    StringHandler::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cCVV         =
                    StringHandler::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKartenTyp   =
                    StringHandler::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber     =
                    StringHandler::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                $bZusatzangabenDa                                    = true;
            }
            break;
        case 'za_lastschrift_jtl':
            if (($_POST['bankname'] &&
                    $_POST['blz'] &&
                    $_POST['kontonr'] &&
                    $_POST['inhaber'])
                ||
                ($_POST['bankname'] &&
                    $_POST['iban'] &&
                    $_POST['bic'] &&
                    $_POST['inhaber'])
            ) {
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBankName =
                    StringHandler::htmlentities(stripslashes($_POST['bankname']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                    StringHandler::htmlentities(stripslashes($_POST['kontonr']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                    StringHandler::htmlentities(stripslashes($_POST['blz']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                    StringHandler::htmlentities(stripslashes($_POST['iban']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                    StringHandler::htmlentities(stripslashes($_POST['bic']), ENT_QUOTES);
                $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                    StringHandler::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                $bZusatzangabenDa                                 = true;
            }
            break;
    }

    if ($bZusatzangabenDa) {
        if (saveZahlungsInfo($bestellung->kKunde, $bestellung->kBestellung)) {
            Shop::DB()->update(
                'tbestellung',
                'kBestellung',
                (int)$bestellung->kBestellung,
                (object)['cAbgeholt' => 'N']
            );
            unset($_SESSION['Zahlungsart']);
            header('Location: ' . $successPaymentURL, true, 303);
            exit();
        }
    } else {
        $smarty->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
}
// Zahlungsart als Plugin
$kPlugin = gibkPluginAuscModulId($bestellung->Zahlungsart->cModulId);
if ($kPlugin > 0) {
    $oPlugin = new Plugin($kPlugin);

    if ($oPlugin->kPlugin > 0) {
        require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_PAYMENTMETHOD .
            $oPlugin->oPluginZahlungsKlasseAssoc_arr[$bestellung->Zahlungsart->cModulId]->cClassPfad;
        /** @var PaymentMethod $paymentMethod */
        $pluginName              = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$bestellung->Zahlungsart->cModulId]->cClassName;
        $paymentMethod           = new $pluginName($bestellung->Zahlungsart->cModulId);
        $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
        $paymentMethod->preparePaymentProcess($bestellung);
        $smarty->assign('oPlugin', $oPlugin);
    }
} elseif ($bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl') {
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $oKundenKontodaten = gibKundenKontodaten($_SESSION['Kunde']->kKunde);
    if ($oKundenKontodaten->kKunde > 0) {
        $smarty->assign('oKundenKontodaten', $oKundenKontodaten);
    }
} elseif ($bestellung->Zahlungsart->cModulId === 'za_paypal_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paypal/PayPal.class.php';
    $paymentMethod           = new PayPal($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_worldpay_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'worldpay/WorldPay.class.php';
    $paymentMethod           = new WorldPay($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_moneybookers_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'moneybookers/moneybookers.php';
    $smarty->assign(
        'moneybookersform',
        gib_moneybookers_form(
            $bestellung,
            strtolower($Einstellungen['zahlungsarten']['zahlungsart_moneybookers_empfaengermail']),
            $successPaymentURL
        )
    );
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ipayment_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ipayment/iPayment.class.php';
    $paymentMethod           = new iPayment($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_sofortueberweisung_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'sofortueberweisung/SofortUeberweisung.class.php';
    $paymentMethod           = new SofortUeberweisung($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ut_stand_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
    $paymentMethod           = new UT($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ut_dd_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
    $paymentMethod           = new UT($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ut_cc_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
    $paymentMethod           = new UT($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ut_prepaid_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
    $paymentMethod           = new UT($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ut_gi_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
    $paymentMethod           = new UT($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_ut_ebank_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ut/UT.class.php';
    $paymentMethod           = new UT($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_safetypay') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/confirmation.php';
    $smarty->assign('safetypay_form', show_confirmation($bestellung));
} elseif ($bestellung->Zahlungsart->cModulId === 'za_wirecard_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'wirecard/Wirecard.class.php';
    $paymentMethod           = new Wirecard($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_postfinance_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'postfinance/PostFinance.class.php';
    $paymentMethod           = new PostFinance($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_paymentpartner_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'paymentpartner/PaymentPartner.class.php';
    $paymentMethod           = new PaymentPartner($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif (strpos($bestellung->Zahlungsart->cModulId, 'za_mbqc_') === 0) {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'moneybookers_qc/MoneyBookersQC.class.php';
    $paymentMethod           = new MoneyBookersQC($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_eos_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
    $paymentMethod           = new EOS($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} // EOS Payment Solution
elseif ($bestellung->Zahlungsart->cModulId === 'za_eos_dd_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
    $paymentMethod           = new EOS($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_eos_cc_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
    $paymentMethod           = new EOS($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_eos_direct_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
    $paymentMethod           = new EOS($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
} elseif ($bestellung->Zahlungsart->cModulId === 'za_eos_ewallet_jtl') {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'eos/EOS.class.php';
    $paymentMethod           = new EOS($bestellung->Zahlungsart->cModulId);
    $paymentMethod->cModulId = $bestellung->Zahlungsart->cModulId;
    $paymentMethod->preparePaymentProcess($bestellung);
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;

$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('Firma', Shop::DB()->query("SELECT * FROM tfirma", 1))
       ->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
       ->assign('Einstellungen', $Einstellungen)
       ->assign('Bestellung', $bestellung);

unset(
    $_SESSION['Zahlungsart'],
    $_SESSION['Versandart'],
    $_SESSION['Lieferadresse'],
    $_SESSION['VersandKupon'],
    $_SESSION['NeukundenKupon'],
    $_SESSION['Kupon']
);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$smarty->display('checkout/order_completed.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
