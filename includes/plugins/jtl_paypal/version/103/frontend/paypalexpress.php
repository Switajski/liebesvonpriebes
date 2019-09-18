<?php
//if (!isset($_GET['callback'])) {
if (!isset($_GET['jtl_paypal_redirect'])) {
    if (isset($_GET['return'])) {
        header('Location: ' . URL_SHOP . '/warenkorb.php?return=1&jtl_paypal_redirect=1');
    } else {
        header('Location: ' . URL_SHOP . '/warenkorb.php?jtl_paypal_redirect=1');
    }
    exit;
}
//}

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalExpress.class.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_INCLUDES . 'registrieren_inc.php';

$paypalexpress = new PayPalExpress();

/*
if (isset($_GET['callback']) && $_GET['callback'] === '1') {
    $r = print_r($_POST, true);
    ZahlungsLog::add($paypalexpress->moduleID, "Callback Request:\n\n<pre>{$r}</pre>", '', LOGLEVEL_NOTICE);
    
    if (isset($_GET['sid'])) {
        session_write_close();
        session_id($_GET['sid']);
        session_start();
    }
    
    switch ($_POST['METHOD']) {
        case 'CallbackRequest':
        {
            $street = utf8_decode($_POST['SHIPTOSTREET']);
            $city = utf8_decode($_POST['SHIPTOCITY']);
            $state = utf8_decode($_POST['SHIPTOSTATE']);
            $lieferland = utf8_decode($_POST['SHIPTOCOUNTRY']);
            $plz = utf8_decode($_POST['SHIPTOZIP']);
            $street2 = utf8_decode($_POST['SHIPTOSTREET2']);
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
            
            $r = print_r($_POST, true);
            ZahlungsLog::add($paypalexpress->moduleID, "Callback Data:\n\n<pre>{$r}</pre>", '', LOGLEVEL_NOTICE);
            
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
            }

            $oVersandart_arr = VersandartHelper::getPossibleShippingMethods(
                StringHandler::filterXSS($lieferland),
                StringHandler::filterXSS($plz),
                VersandartHelper::getShippingClasses($_SESSION['Warenkorb']),
                $kKundengruppe
            );

            $r = print_r($oVersandart_arr, true);
            ZahlungsLog::add($paypalexpress->moduleID, "Callback Response:\n\n<pre>{$r}</pre>", '', LOGLEVEL_NOTICE);
            
            $response = [
                'METHOD' => 'CallbackResponse',
                'OFFERINSURANCEOPTION' => 'false',
                'CURRENCYCODE' => $_POST['CURRENCYCODE']
            ];
            
            if (count($oVersandart_arr) == 0) {
                $response['NO_SHIPPING_OPTION_DETAILS'] = 1;
            }
            else {
                $index = 0;
                $language = Shop::getLanguage(true);
                foreach ($oVersandart_arr as $oVersandart) {
                    $response['L_SHIPPINGOPTIONISDEFAULT' . $index] = $index == 0;
                    $response['L_SHIPPINGOPTIONAMOUNT' . $index] = number_format($oVersandart->fEndpreis, 2, '.', '');
                    $response['L_SHIPPINGOPTIONLABEL' . $index] = 'LABEL_' . $oVersandart->kVersandart;//$oVersandart->angezeigterName[$language];
                    $response['L_SHIPPINGOPTIONNAME' . $index] = $oVersandart->kVersandart;
                    $response['L_TAXAMT' . $index] = '0.00';
                    $index++;
                }
            }
            
            $plain = http_build_query($response);
            
            ZahlungsLog::add($paypalexpress->moduleID, "Callback Response-Plain:\n\n<pre>{$plain}</pre>", '', LOGLEVEL_NOTICE);
            
            die($plain);

            break;
        }
        default:
            break;
    }
    exit;
} 
else */

if (isset($_GET['return']) && $_GET['return'] === '1') {
    $session  = Session::getInstance();
    $conf     = Shop::getConfig(array(CONF_KUNDEN));
    $response = $paypalexpress->GetExpressCheckoutDetails($_SESSION['reshash']['Token']);

    $_SESSION['reshash']['Token']   = $response->Token;
    $_SESSION['reshash']['Payer']   = $response->PayerInfo->Payer;
    $_SESSION['reshash']['PayerID'] = $response->PayerInfo->PayerID;

    $name   = PayPalHelper::extractName(StringHandler::convertISO($response->PayerInfo->Address->Name));
    $street = PayPalHelper::extractStreet(StringHandler::convertISO($response->PayerInfo->Address->Street1));

    $_POST['anrede']     = 'm';
    $_POST['vorname']    = $name->first;
    $_POST['nachname']   = $name->last;
    $_POST['strasse']    = $street->name;
    $_POST['hausnummer'] = $street->number;

    $_POST['adresszusatz'] = StringHandler::convertISO($response->PayerInfo->Address->Street2);
    $_POST['bundesland']   = StringHandler::convertISO($response->PayerInfo->Address->StateOrProvince);
    $_POST['plz']          = StringHandler::convertISO($response->PayerInfo->Address->PostalCode);
    $_POST['ort']          = StringHandler::convertISO($response->PayerInfo->Address->CityName);
    $_POST['land']         = StringHandler::convertISO($response->PayerInfo->Address->Country);
    $_POST['tel']          = StringHandler::convertISO($response->PayerInfo->Address->Phone);

    $_POST['email'] = StringHandler::convertISO($response->PayerInfo->Payer);

    if ($conf['kunden']['kundenregistrierung_abfragen_firma'] === 'Y') {
        $_POST['firma'] = 'NOCOMPANY';
    }

    if ($conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N') {
        $_POST['ustid'] = 'NOVATID';
    }

    if ($conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'Y') {
        $_POST['geburtstag'] = '01.01.1970';
    }

    if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_create_account'] === 'Y') {
        $sql = "SELECT *,date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag FROM tkunde WHERE cMail = '" . $_POST['email'] . "' AND cPLZ = '" . $_POST['plz'] . "'";
        $obj = Shop::DB()->query($sql, 1);

        if (is_object($obj) && $obj->kKunde > 0) {
            $customer = new Kunde($obj->kKunde);

            if (isset($customer->cSperre) && $customer->cSperre === 'Y') { //customer is blocked
                header('Location: warenkorb.php?fillOut=ppexpress_blocked');
                exit;
            }

            if (isset($customer->cAktiv) && $customer->cAktiv === 'N') { //customer is not active
                header('Location: warenkorb.php?fillOut=ppexpress_inactive');
                exit;
            }

            $session->setCustomer($customer);
        } else {
            $plainPassword = gibUID(max(intval($conf['kunden']['kundenregistrierung_passwortlaenge']), 8));
            $_POST['pass'] = $_POST['pass2'] = $plainPassword;

            //avoid redirect to customer account page
            $_POST['ajaxcheckout_return'] = 1;

            kundeSpeichern($_POST);

            $oObj                            = new stdClass();
            $oObj->tkunde                    = $_SESSION['Kunde'];
            $oObj->tkunde->cPasswortKlartext = $plainPassword;

            sendeMail('kPlugin_' . $oPlugin->kPlugin . '_ppexpresskkpw', $oObj);
        }
    } else {
        $customer               = getKundendaten($_POST, 0, 0);
        $customer->nRegistriert = 0;

        $session->setCustomer($customer);

        $_SESSION['Kunde']->kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        $_SESSION['Kunde']->kSprache      = $_SESSION['kSprache'];
        $_SESSION['Kunde']->cAbgeholt     = 'N';
        $_SESSION['Kunde']->cAktiv        = 'Y';
        $_SESSION['Kunde']->cSperre       = 'N';
        $_SESSION['Kunde']->dErstellt     = date_format(date_create(), 'Y-m-d');
        $_SESSION['Kunde']->nRegistriert  = 0;

        if (!isset($_SESSION['Kunde']->cAnrede) || $_SESSION['Kunde']->cAnrede === null) {
            $_SESSION['Kunde']->cAnrede = '';
        }

        $_SESSION['Kunde']->insertInDB();
    }

    setzeLieferadresseAusRechnungsadresse();

    $step = 'Zahlung';

    $_SESSION['Zahlungsart']                 = $paypalexpress->zahlungsartsession();
    $_SESSION['paypalexpress']->sZahlungsart = $_SESSION['Zahlungsart'];
    $_POST['Zahlungsart']                    = $_SESSION['Zahlungsart']->kZahlungsart;
    $_POST['zahlungsartwahl']                = '1';

    pruefeZahlungsartwahlStep($_POST);

    //workaround since the session is deleted in pruefeZahlungsartwahlStep()

    $_SESSION['Zahlungsart']  = $_SESSION['paypalexpress']->sZahlungsart;
    $_POST['Zahlungsart']     = $_SESSION['Zahlungsart']->kZahlungsart;
    $_POST['zahlungsartwahl'] = '1';

    PayPalHelper::addSurcharge();

    header('Location: bestellvorgang.php?refresh=1');
    exit;
} else {
    $products = PayPalHelper::getProducts();

    $min = $oPlugin->oPluginEinstellungAssoc_arr['kPlugin_' . $oPlugin->kPlugin . '_paypalexpress_min'];
    $max = $oPlugin->oPluginEinstellungAssoc_arr['kPlugin_' . $oPlugin->kPlugin . '_paypalexpress_max'];
    $sum = $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true);

    if ($max > 0 && $max < $sum) {
        header('Location: warenkorb.php?fillOut=ppexpress_max&max=' . $max);
        exit;
    }

    if ($min > 0 && $min > $sum) {
        header('Location: warenkorb.php?fillOut=ppexpress_min&min=' . $min);
        exit;
    }

    if ($paypalexpress->zahlungErlaubt($products) === false) {
        header('Location: warenkorb.php?fillOut=ppexpress_notallowed');
        exit;
    }

    $paypalexpress->zahlungsprozess();
}
