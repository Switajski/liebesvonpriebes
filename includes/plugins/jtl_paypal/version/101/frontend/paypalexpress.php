<?php

if (!isset($_GET['jtl_paypal_redirect'])) {
    if (isset($_GET['return'])) {
        header('Location: ' . URL_SHOP . '/warenkorb.php?return=1&jtl_paypal_redirect=1');
    } else {
        header('Location: ' . URL_SHOP . '/warenkorb.php?jtl_paypal_redirect=1');
    }
    exit;
}

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalExpress.class.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_INCLUDES . 'registrieren_inc.php';

$paypalexpress = new PayPalExpress();

if (isset($_GET['return']) && $_GET['return'] === '1') {
    $conf     = Shop::getConfig(array(CONF_KUNDEN));
    $response = $paypalexpress->GetExpressCheckoutDetails($_SESSION['reshash']['Token']);

    $_SESSION['reshash']['Token']   = $response->Token;
    $_SESSION['reshash']['Payer']   = $response->PayerInfo->Payer;
    $_SESSION['reshash']['PayerID'] = $response->PayerInfo->PayerID;

    $_POST['anrede']       = 'm';
    $_POST['vorname']      = '';
    $_POST['nachname']     = StringHandler::convertISO($response->PayerInfo->Address->Name);
    $_POST['strasse']      = StringHandler::convertISO($response->PayerInfo->Address->Street1);
    $_POST['hausnummer']   = ' ';
    $_POST['adresszusatz'] = StringHandler::convertISO($response->PayerInfo->Address->Street2);
    $_POST['plz']          = StringHandler::convertISO($response->PayerInfo->Address->PostalCode);
    $_POST['ort']          = StringHandler::convertISO($response->PayerInfo->Address->CityName);
    $_POST['land']         = StringHandler::convertISO($response->PayerInfo->Address->Country);
    $_POST['email']        = $response->PayerInfo->Payer;
    $_POST['tel']          = '';

    if (isset($response->PayerInfo->PayerName->Salutation) && $response->PayerInfo->PayerName->Salutation !== null) {
        $_POST['anrede'] = ''; //???
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_firma'] === 'Y') {
        $_POST['firma'] = '----';
    }
//	if (!isset($_SESSION['reshash']['SHIPTOPHONENUM']) || $_SESSION['reshash']['SHIPTOPHONENUM'] === '') {
//		$_POST['tel'] = $_SESSION['reshash']['PHONENUM'];
//	}
    if ($_POST['anrede'] === '') {
        $_POST['anrede'] = 'm';
    }
//	}
    if ($conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'Y') {
        $_POST['geburtstag'] = '01.01.1970';
    }
//	if ($conf['kunden']['kundenregistrierung_abfragen_firma'] !== 'N' && strlen($_SESSION['reshash']['BUSINESS']) > 0) {
//		$_POST['firma'] = $_SESSION['reshash']['BUSINESS'];
//	}
    if ($conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N') {
        $_POST['ustid'] = '';
    }

    if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_create_account'] === 'Y') {
        $sql = "SELECT *,date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag FROM tkunde WHERE cMail = '" . $_POST['email'] . "' AND cPLZ = '" . $_POST['plz'] . "'";
        $obj = (class_exists('Shop')) ? Shop::DB()->query($sql, 1) : $GLOBALS['DB']->executeQuery($sql, 1);

        if (is_object($obj) && $obj->kKunde > 0) {
            $customer = new Kunde($obj->kKunde);

            if (isset($obj->cSperre) && $obj->cSperre === 'Y') { //customer is blocked
                header('Location: warenkorb.php?fillOut=ppexpress_max&fehler=gesperrt&' . SID);
            }
            if (isset($obj->cAktiv) && $obj->cAktiv === 'N') { //customer is not active
                header('Location: warenkorb.php?fillOut=ppexpress_max&fehler=inaktiv&' . SID);
            }

            if (method_exists('Session', 'setCustomer')) {
                $session = Session::getInstance();
                $session->setCustomer($customer);
            } else {
                setzeKundeInSession($customer);
            }
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
        setzeInSession('Kunde', $customer);

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
    $_SESSION['Zahlungsart'] = $_SESSION['paypalexpress']->sZahlungsart;
//	$_SESSION['kommentar']    = $_SESSION['reshash']['PAYMENTREQUEST_0_NOTETEXT'];
    $_POST['Zahlungsart']     = $_SESSION['Zahlungsart']->kZahlungsart;
    $_POST['zahlungsartwahl'] = '1';

    PayPalHelper::addSurcharge();

    header('Location: bestellvorgang.php?refresh=1');
    exit;
} else {
    $oArtikel_arr = array();
    foreach ($_SESSION['Warenkorb']->PositionenArr as $Positionen) {
        if ($Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
            $oArtikel_arr[] = $Positionen->Artikel;
        }
    }

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

    if ($paypalexpress->zahlungErlaubt($oArtikel_arr) === false) {
        header('Location: warenkorb.php?fillOut=ppexpress_notallowed');
        exit;
    }

    $paypalexpress->zahlungsprozess();
}
