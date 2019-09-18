<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';

/**
 * @param object $Bestellung
 * @return boolean True, wenn eine URL zum Zahlungsanbieter generiert wurde
 */
function eosNeueZahlung($Bestellung)
{
    global $Einstellungen;
    // BestellungsHash aus DB holen
    $BestellungsID   = Shop::DB()->query("SELECT cID FROM tbestellid WHERE kBestellung = " . (int)$Bestellung->kBestellung, 1);
    $BestellungsHash = $BestellungsID->cID;
    // Attribute setzen
    $BestellNr     = $Bestellung->cBestellNr;
    $Referenz      = $BestellNr;
    $Zahlungsarten = $Einstellungen['zahlungsarten'];
    $HaendlerID    = $Zahlungsarten['zahlungsart_eos_haendlerid'];
    $HaendlerCode  = $Zahlungsarten['zahlungsart_eos_haendlercode'];
    $BruttoBetrag  = number_format($Bestellung->fGesamtsumme, 2, ',', '');
    $Waehrung      = $Bestellung->Waehrung->cISO;
    $MWSTSatz      = number_format($Bestellung->Positionen[0]->fMwSt, 2, ',', '');
    $Kunde         = $_SESSION['Kunde'];
    $Karteninhaber = $Kunde->cVorname . ' ' . $Kunde->cNachname;
    $ShopTitle     = $Einstellungen['global']['global_meta_title'];
    $Text          = sprintf(Shop::Lang()->get('eosText', 'paymentMethods'), $BestellNr, $ShopTitle);
    // URLs
    $ZahlungsHash = eosZahlungsHash($Bestellung);
    $BaseURL      = Shop::getURL() . '/bestellabschluss.php?i=' . $BestellungsHash . '&za=eos&zh=' . $ZahlungsHash . '&state=';
    $BackURL      = Shop::getURL() . '/jtl.php?bestellung=' . $Bestellung->kBestellung; // Zeigt Status und Link zur erneuten Bezahlung
    $SuccessURL   = $BaseURL . 'success';
    $FailURL      = $BaseURL . 'fail';
    $ErrorURL     = $BaseURL . 'error';
    $EndURL       = $BackURL;

    // Argumente enkodieren
    $Arguments = compact(
        'Referenz', 'HaendlerID', 'HaendlerCode',
        'BruttoBetrag', 'Waehrung', 'MWSTSatz', 'Karteninhaber', 'Text',
        'BackURL', 'SuccessURL', 'FailURL', 'ErrorURL', 'EndURL', 'EndURL'
    );
    $Arguments = array_map('rawurlencode', $Arguments);

    // Fill EOS URL Template
    $EOSPath = '/PaymentGateway_CC.acgi?referenz=%s'
        . '&haendlerid=%s&haendlercode=%s&bruttobetrag=%s&waehrung=%s'
        . '&mwstsatz=%s&karteninhaber=%s&text=%s&BackURL=%s&SuccessURL=%s'
        . '&FailURL=%s&ErrorURL=%s&EndURL=%s';
    $Path = vsprintf($EOSPath, $Arguments);

    // Server Anfrage (URL holen)
    $Antwort = eosServerAnfrage($Path);

    // HTTP Error
    if ($Antwort === false) {
        Shop::Smarty()->assign('eosStatus', 'Error')
            ->assign('eosError', Shop::Lang()->get('errorText', 'paymentMethods'));
        // Mail an ShopBetreiber
        $Error = Shop::Lang()->get('eosHttpError', 'paymentMethods');
        $Body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $ShopTitle, $BestellNr, 'EOS', $Error);
        eosSendeFehlerMail($Body);

        return false;
    }

    // Antwort parsen
    parse_str($Antwort);

    // Error: Syntax, HaendlerID, HaendlerCode, ..
    if ($status !== 'OK') {
        Shop::Smarty()->assign('eosStatus', 'Error')
            ->assign('eosError', Shop::Lang()->get('errorText', 'paymentMethods'));
        $Error = sprintf(Shop::Lang()->get('eosError', 'paymentMethods'), $Antwort);
        $Body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $ShopTitle, $BestellNr, 'EOS', $Error);
        eosSendeFehlerMail($Body);

        return false;
    }

    Shop::Smarty()->assign('eosStatus', 'Success')
        ->assign('eosURL', $URL);

    return true;
}

/**
 *
 * @param string $Body
 * @return void
 */
function eosSendeFehlerMail($Body)
{
    global $Einstellungen;

    // Mail Einstellungen nachladen
    $Einstellungen = array_merge($Einstellungen, Shop::getSettings([CONF_EMAILS]));

    // Inhalt
    $Mail            = new stdClass();
    $Mail->toEmail   = $Einstellungen['emails']['email_master_absender'];
    $Mail->toName    = $Einstellungen['emails']['email_master_absender_name'];
    $Mail->fromEmail = $Mail->toEmail; // Empfaenger = Sender
    $Mail->fromName  = $Mail->toName;
    $Mail->subject   = sprintf(Shop::Lang()->get('errorMailSubject', 'paymentMethods'), $Einstellungen['global']['global_meta_title']);
    $Mail->bodyText  = $Body;

    // Methode
    $Mail->methode       = $Einstellungen['eMails']['eMail_methode'];
    $Mail->sendMail_pfad = $Einstellungen['eMails']['eMail_sendMail_pfad'];
    $Mail->smtp_hostname = $Einstellungen['eMails']['eMail_smtp_hostname'];
    $Mail->smtp_port     = $Einstellungen['eMails']['eMail_smtp_port'];
    $Mail->smtp_auth     = $Einstellungen['eMails']['eMail_smtp_auth'];
    $Mail->smtp_user     = $Einstellungen['eMails']['eMail_smtp_user'];
    $Mail->smtp_pass     = $Einstellungen['eMails']['eMail_smtp_pass'];
    $Mail->SMTPSecure    = $Einstellungen['emails']['email_smtp_verschluesselung'];

    // Abschicken
    include_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    verschickeMail($Mail);
}

/**
 * @param string $Path
 * @return bool/string False, falls Socket Error, Antwort sonst.
 */
function eosServerAnfrage($Path)
{
    // Config
    $Hostname = 'ssl://www.eos-payment.de';
    $Host     = 'www.eos-payment.de';
    $Port     = 443;
    $Timeout  = 30;

    $Socket = fsockopen($Hostname, $Port, $ErrNo, $ErrStr, $Timeout);

    // Socket Error
    if (!$Socket) {
        //echo $ErrStr;
        return false;
    }

    // Senden
    $Header = "GET $Path HTTP/1.1\r\n"
        . "Host: $Host\r\n"
        . "Connection: close\r\n\r\n";
    fwrite($Socket, $Header);

    // Empfangen (ohne Header)
    $Antwort = '';
    $Inhalt  = false;
    while (feof($Socket) === false) {
        $Zeile = fgetss($Socket, 1024); // 1024 = komplette URL bekommen
        $Zeile = trim($Zeile);
        if ($Inhalt) {
            $Antwort .= $Zeile;
        }
        $Inhalt = ($Zeile === '' && $Inhalt === false);
    }
    fclose($Socket);

    return trim($Antwort);
}

/**
 * @param object Bestellung
 * @return string Hash
 */
function eosZahlungsHash($Bestellung)
{
    // In DB einfuegen
    $Zahlung              = new stdClass();
    $Zahlung->kBestellung = (int)$Bestellung->kBestellung;
    $Zahlung->cId         = gibUID(40, (string)$Bestellung->kBestellung);

    return Shop::DB()->insert('tzahlungsid', $Zahlung);
}

/**
 * @param object $Bestellung
 * @return boolean True, wenn die Zahlung erfolgreich verbucht werden konnte
 */
function eosZahlungsNachricht($Bestellung)
{
    $ZahlungsHash = Shop::DB()->realEscape($_GET['zh']);
    // uebermittelten Zahlungshash ueberpruefen
    $Zahlung = Shop::DB()->select('tzahlungsid', 'cId', $ZahlungsHash, 'kBestellung', $Bestellung->kBestellung);
    if ($Zahlung->kBestellung != $Bestellung->kBestellung) {
        // Richtiger BestellungsHash und falscher Zahlungshash!?!
        // Da versucht doch eine(r) was. Zur Strafe keine neue BestellID!
        return false;
    }
    if ($_GET['state'] !== 'success') {
        // Falls die Bezahlung nicht erfolgreich war, wird eine neue BestellID
        // generiert, damit der Kunde spaeter bezahlen kann.
        $BestellID              = new stdClass();
        $BestellID->cId         = gibUID(40, $Bestellung->kBestellung . md5(time()));
        $BestellID->kBestellung = $Bestellung->kBestellung;
        $BestellID->dDatum      = 'now()';
        Shop::DB()->insert('tbestellid', $BestellID);

        return false;
    }
    // Bezahlung war erfolgreich
    eosZahlungVerbuchen($Zahlung, $Bestellung);
    // ZahlungsID loeschen
    Shop::DB()->delete('tzahlungsid', ['cId', 'kBestellung'], [$ZahlungsHash, (int)$Bestellung->kBestellung]);

    return true;
}

/**
 * @param object            $zahlung
 * @param object|Bestellung $bestellung
 */
function eosZahlungVerbuchen($zahlung, $bestellung)
{
    //Zahlung setzen
    $_upd                = new stdClass();
    $_upd->cStatus       = BESTELLUNG_STATUS_BEZAHLT;
    $_upd->dBezahltDatum = 'now()';
    Shop::DB()->update('tbestellung', 'kBestellung', (int)$bestellung->kBestellung, $_upd);
    // Reload
    $bestellung = new Bestellung($zahlung->kBestellung);
    $bestellung->fuelleBestellung(0);
    // Zahlungseingang
    $ZE                    = new stdClass();
    $ZE->kBestellung       = (int)$bestellung->kBestellung;
    $ZE->cZahlungsanbieter = 'EOS';
    $ZE->fBetrag           = $bestellung->fGesamtsumme;
    $ZE->cAbgeholt         = 'N';
    $ZE->dZeit             = 'now()';
    Shop::DB()->insert('tzahlungseingang', $ZE);
    $Kunde = new Kunde($bestellung->kKunde);
    // Benachrichtigung an den Kunden
    $Obj              = new stdClass();
    $Obj->tkunde      = $Kunde;
    $Obj->tbestellung = $bestellung;
    sendeMail(MAILTEMPLATE_BESTELLUNG_BEZAHLT, $Obj);
}
