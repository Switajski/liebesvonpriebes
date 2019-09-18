<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global JTLSmarty $smarty
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
/** @global JTLSmarty $smarty */
$Emailvorlage          = null;
$hinweis               = '';
$cHinweis              = '';
$cFehler               = '';
$nFehler               = 0;
$continue              = true;
$oEmailvorlage         = null;
$Emailvorlagesprache   = [];
$cFehlerAnhang_arr     = [];
$step                  = 'uebersicht';
$Einstellungen         = Shop::getSettings([CONF_EMAILS]);
$oSmartyError          = new stdClass();
$oSmartyError->nCode   = 0;
$cTable                = 'temailvorlage';
$cTableSprache         = 'temailvorlagesprache';
$cTableSpracheOriginal = 'temailvorlagespracheoriginal';
$cTableSetting         = 'temailvorlageeinstellungen';
$cTablePluginSetting   = 'tpluginemailvorlageeinstellungen';
if (verifyGPCDataInteger('kPlugin') > 0) {
    $cTable                = 'tpluginemailvorlage';
    $cTableSprache         = 'tpluginemailvorlagesprache';
    $cTableSpracheOriginal = 'tpluginemailvorlagespracheoriginal';
    $cTableSetting         = 'tpluginemailvorlageeinstellungen';
}
// Errorhandler
if (isset($_GET['err'])) {
    setzeFehler($_GET['kEmailvorlage'], true);
    $cFehler = '<b>Die Emailvorlage ist fehlerhaft.</b>';
    if (is_array($_SESSION['last_error'])) {
        $cFehler .= '<br />' . $_SESSION['last_error']['message'];
        unset($_SESSION['last_error']);
    }
}
// Emailvorlage zuruecksetzen
if (isset($_POST['resetConfirm']) && (int)$_POST['resetConfirm'] > 0) {
    $oEmailvorlage = Shop::DB()->select($cTable, 'kEmailvorlage', (int)$_POST['resetConfirm']);

    if (isset($oEmailvorlage->kEmailvorlage) && $oEmailvorlage->kEmailvorlage > 0) {
        $step = 'zuruecksetzen';

        $smarty->assign('oEmailvorlage', $oEmailvorlage);
    }
}

if (isset($_POST['resetEmailvorlage']) && (int)$_POST['resetEmailvorlage'] === 1 && validateToken()) {
    if ((int)$_POST['kEmailvorlage'] > 0) {
        $oEmailvorlage = Shop::DB()->select($cTable, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
        if ($oEmailvorlage->kEmailvorlage > 0 && isset($_POST['resetConfirmJaSubmit'])) {
            // Resetten
            if (verifyGPCDataInteger('kPlugin') > 0) {
                Shop::DB()->delete('tpluginemailvorlagesprache', 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
            } else {
                Shop::DB()->query(
                    "DELETE temailvorlage, temailvorlagesprache
                        FROM temailvorlage
                        LEFT JOIN temailvorlagesprache
                            ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                        WHERE temailvorlage.kEmailvorlage = " . (int)$_POST['kEmailvorlage'], 4
                );
                Shop::DB()->query(
                    "INSERT INTO temailvorlage
                        SELECT *
                        FROM temailvorlageoriginal
                        WHERE temailvorlageoriginal.kEmailvorlage = " . (int)$_POST['kEmailvorlage'], 4
                );
            }
            Shop::DB()->query(
                "INSERT INTO " . $cTableSprache . "
                    SELECT *
                    FROM " . $cTableSpracheOriginal . "
                    WHERE " . $cTableSpracheOriginal . ".kEmailvorlage = " . (int)$_POST['kEmailvorlage'], 4
            );
            $languages = gibAlleSprachen();
            if (verifyGPCDataInteger('kPlugin') === 0) {
                $vorlage   = Shop::DB()->select('temailvorlageoriginal', 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
                if (isset($vorlage->cDateiname) && strlen($vorlage->cDateiname) > 0) {
                    foreach ($languages as $_lang) {
                        $path = PFAD_ROOT . PFAD_EMAILVORLAGEN . $_lang->cISO;
                        if (isset($_lang->cISO) && file_exists(PFAD_ROOT . PFAD_EMAILVORLAGEN . $_lang->cISO)) {
                            $fileHtml  = $path . '/' . $vorlage->cDateiname . '_html.tpl';
                            $filePlain = $path . '/' . $vorlage->cDateiname . '_plain.tpl';
                            if (file_exists($fileHtml) && file_exists($filePlain)) {
                                $upd               = new stdClass();
                                $html              = file_get_contents($fileHtml);
                                $text              = file_get_contents($filePlain);
                                $doDecodeHtml      = function_exists('mb_detect_encoding')
                                    ? (mb_detect_encoding($html, 'UTF-8', true) === 'UTF-8')
                                    : (StringHandler::is_utf8($html) === 1);
                                $doDecodeText      = function_exists('mb_detect_encoding')
                                    ? (mb_detect_encoding($text, 'UTF-8', true) === 'UTF-8')
                                    : (StringHandler::is_utf8($text) === 1);
                                $upd->cContentHtml = ($doDecodeHtml === true) ? utf8_decode($html) : $html;
                                $upd->cContentText = ($doDecodeText === true) ? utf8_decode($text) : $text;
                                Shop::DB()->update(
                                    $cTableSprache,
                                    ['kEmailVorlage', 'kSprache'],
                                    [(int)$_POST['kEmailvorlage'], (int)$_lang->kSprache],
                                    $upd
                                );
                            }
                        }
                    }
                }
            }
            $cHinweis .= 'Ihre markierte Emailvorlage wurde erfolgreich zur&uuml;ckgesetzt.<br />';
        }
    }
}
if (isset($_POST['preview']) && (int)$_POST['preview'] > 0) {
    $Sprachen                     = Shop::DB()->query("SELECT * FROM tsprache ORDER BY cShopStandard DESC, cNameDeutsch", 2);
    $Emailvorlage                 = Shop::DB()->select($cTable, 'kEmailvorlage', (int)$_POST['preview']);
    $bestellung                   = new stdClass();
    $bestellung->kWaehrung        = 1;
    $bestellung->kSprache         = 1;
    $bestellung->fGuthaben        = 5;
    $bestellung->fGesamtsumme     = 433;
    $bestellung->cBestellNr       = 'Prefix-3432-Suffix';
    $bestellung->cVersandInfo     = 'Optionale Information zum Versand';
    $bestellung->cTracking        = 'Track232837';
    $bestellung->cKommentar       = 'Kundenkommentar zur Bestellung';
    $bestellung->cVersandartName  = 'DHL bis 10kg';
    $bestellung->cZahlungsartName = 'Nachnahme';
    $bestellung->cStatus          = 1;
    $bestellung->dVersandDatum    = '2010-10-21';
    $bestellung->dErstellt        = '2010-10-12 09:28:38';
    $bestellung->dBezahltDatum    = '2010-10-20';

    $bestellung->cLogistiker            = 'DHL';
    $bestellung->cTrackingURL           = 'http://dhl.de/linkzudhl.php';
    $bestellung->dVersanddatum_de       = '21.10.2007';
    $bestellung->dBezahldatum_de        = '20.10.2007';
    $bestellung->dErstelldatum_de       = '12.10.2007';
    $bestellung->dVersanddatum_en       = '21st October 2010';
    $bestellung->dBezahldatum_en        = '20th October 2010';
    $bestellung->dErstelldatum_en       = '12th October 2010';
    $bestellung->cBestellwertLocalized  = '511,00 EUR';
    $bestellung->GuthabenNutzen         = 1;
    $bestellung->GutscheinLocalized     = '5,00 &euro;';
    $bestellung->fWarensumme            = 433.004004;
    $bestellung->fVersand               = 0;
    $bestellung->nZahlungsTyp           = 0;
    $bestellung->WarensummeLocalized[0] = '511,00 EUR';
    $bestellung->WarensummeLocalized[1] = '429,41 EUR';
    $bestellung->oEstimatedDelivery     = (object)[
        'localized'  => '',
        'longestMin' => 3,
        'longestMax' => 6,
    ];
    $bestellung->cEstimatedDelivery     = &$bestellung->oEstimatedDelivery->localized;

    $bestellung->Positionen                              = [];
    $bestellung->Positionen[0]                           = new stdClass();
    $bestellung->Positionen[0]->cName                    = 'LAN Festplatte IPDrive';
    $bestellung->Positionen[0]->cArtNr                   = 'AF8374';
    $bestellung->Positionen[0]->cEinheit                 = 'Stck.';
    $bestellung->Positionen[0]->cLieferstatus            = '3-4 Tage';
    $bestellung->Positionen[0]->fPreisEinzelNetto        = 111.2069;
    $bestellung->Positionen[0]->fPreis                   = 368.1069;
    $bestellung->Positionen[0]->fMwSt                    = 19;
    $bestellung->Positionen[0]->nAnzahl                  = 2;
    $bestellung->Positionen[0]->nPosTyp                  = 1;
    $bestellung->Positionen[0]->cHinweis                 = 'Hinweistext zum Artikel';
    $bestellung->Positionen[0]->cGesamtpreisLocalized[0] = '278,00 EUR';
    $bestellung->Positionen[0]->cGesamtpreisLocalized[1] = '239,66 EUR';
    $bestellung->Positionen[0]->cEinzelpreisLocalized[0] = '139,00 EUR';
    $bestellung->Positionen[0]->cEinzelpreisLocalized[1] = '119,83 EUR';

    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr                           = [];
    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr[0]                        = new stdClass();
    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cEigenschaftName      = 'Kapazit&auml;t';
    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cEigenschaftWertName  = '400GB';
    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr[0]->fAufpreis             = 128.45;
    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[0] = '149,00 EUR';
    $bestellung->Positionen[0]->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[1] = '128,45 EUR';

    $bestellung->Positionen[0]->nAusgeliefert       = 1;
    $bestellung->Positionen[0]->nAusgeliefertGesamt = 1;
    $bestellung->Positionen[0]->nOffenGesamt        = 1;
    $bestellung->Positionen[0]->dMHD                = '2025-01-01';
    $bestellung->Positionen[0]->dMHD_de             = '01.01.2025';
    $bestellung->Positionen[0]->cChargeNr           = 'A2100698.b12';
    $bestellung->Positionen[0]->cSeriennummer       = '465798132756';

    $bestellung->Positionen[1]                           = new stdClass();
    $bestellung->Positionen[1]->cName                    = 'Klappstuhl';
    $bestellung->Positionen[1]->cArtNr                   = 'KS332';
    $bestellung->Positionen[1]->cEinheit                 = 'Stck.';
    $bestellung->Positionen[1]->cLieferstatus            = '1 Woche';
    $bestellung->Positionen[1]->fPreisEinzelNetto        = 100;
    $bestellung->Positionen[1]->fPreis                   = 200;
    $bestellung->Positionen[1]->fMwSt                    = 19;
    $bestellung->Positionen[1]->nAnzahl                  = 1;
    $bestellung->Positionen[1]->nPosTyp                  = 2;
    $bestellung->Positionen[1]->cHinweis                 = 'Hinweistext zum Artikel';
    $bestellung->Positionen[1]->cGesamtpreisLocalized[0] = '238,00 EUR';
    $bestellung->Positionen[1]->cGesamtpreisLocalized[1] = '200,00 EUR';
    $bestellung->Positionen[1]->cEinzelpreisLocalized[0] = '238,00 EUR';
    $bestellung->Positionen[1]->cEinzelpreisLocalized[1] = '200,00 EUR';

    $bestellung->Positionen[1]->nAusgeliefert       = 1;
    $bestellung->Positionen[1]->nAusgeliefertGesamt = 1;
    $bestellung->Positionen[1]->nOffenGesamt        = 0;

    $bestellung->Steuerpositionen                     = [];
    $bestellung->Steuerpositionen[0]                  = new stdClass();
    $bestellung->Steuerpositionen[0]->cName           = 'inkl. 19% USt.';
    $bestellung->Steuerpositionen[0]->fUst            = 19;
    $bestellung->Steuerpositionen[0]->fBetrag         = 98.04;
    $bestellung->Steuerpositionen[0]->cPreisLocalized = '98,04 EUR';

    $bestellung->Waehrung                       = new stdClass();
    $bestellung->Waehrung->cISO                 = 'EUR';
    $bestellung->Waehrung->cName                = 'EUR';
    $bestellung->Waehrung->cNameHTML            = '&euro;';
    $bestellung->Waehrung->fFaktor              = 1;
    $bestellung->Waehrung->cStandard            = 'Y';
    $bestellung->Waehrung->cVorBetrag           = 'N';
    $bestellung->Waehrung->cTrennzeichenCent    = ',';
    $bestellung->Waehrung->cTrennzeichenTausend = '.';

    $bestellung->Zahlungsart           = new stdClass();
    $bestellung->Zahlungsart->cName    = 'Billpay';
    $bestellung->Zahlungsart->cModulId = 'za_billpay_jtl';

    $bestellung->Zahlungsinfo               = new stdClass();
    $bestellung->Zahlungsinfo->cBankName    = 'Bankname';
    $bestellung->Zahlungsinfo->cBLZ         = '3443234';
    $bestellung->Zahlungsinfo->cKontoNr     = 'Kto12345';
    $bestellung->Zahlungsinfo->cIBAN        = 'IB239293';
    $bestellung->Zahlungsinfo->cBIC         = 'BIC3478';
    $bestellung->Zahlungsinfo->cKartenNr    = 'KNR4834';
    $bestellung->Zahlungsinfo->cGueltigkeit = '20.10.2010';
    $bestellung->Zahlungsinfo->cCVV         = '1234';
    $bestellung->Zahlungsinfo->cKartenTyp   = 'VISA';
    $bestellung->Zahlungsinfo->cInhaber     = 'Max Mustermann';

    $bestellung->Lieferadresse                   = new stdClass();
    $bestellung->Lieferadresse->kLieferadresse   = 1;
    $bestellung->Lieferadresse->cAnrede          = 'm';
    $bestellung->Lieferadresse->cAnredeLocalized = 'Herr';
    $bestellung->Lieferadresse->cVorname         = 'John';
    $bestellung->Lieferadresse->cNachname        = 'Doe';
    $bestellung->Lieferadresse->cStrasse         = 'Musterlieferstr.';
    $bestellung->Lieferadresse->cHausnummer      = '77';
    $bestellung->Lieferadresse->cAdressZusatz    = '2. Etage';
    $bestellung->Lieferadresse->cPLZ             = '12345';
    $bestellung->Lieferadresse->cOrt             = 'Musterlieferstadt';
    $bestellung->Lieferadresse->cBundesland      = 'Lieferbundesland';
    $bestellung->Lieferadresse->cLand            = 'Lieferland';
    $bestellung->Lieferadresse->cTel             = '112345678';
    $bestellung->Lieferadresse->cMobil           = '123456789';
    $bestellung->Lieferadresse->cFax             = '12345678909';
    $bestellung->Lieferadresse->cMail            = 'john.doe@example.com';

    $bestellung->fWaehrungsFaktor = 1;

    //Lieferschein
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Lieferschein.php';
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Versand.php';
    $bestellung->oLieferschein_arr = [];

    $oLieferschein = new Lieferschein();
    $oLieferschein->setEmailVerschickt(false);
    $oLieferschein->oVersand_arr = [];
    $oVersand                    = new Versand();
    $oVersand->setLogistikURL('http://nolp.dhl.de/nextt-online-public/report_popup.jsp?lang=de&zip=#PLZ#&idc=#IdentCode#');
    $oVersand->setIdentCode('123456');
    $oLieferschein->oVersand_arr[]  = $oVersand;
    $oLieferschein->oPosition_arr   = [];
    $oLieferschein->oPosition_arr[] = $bestellung->Positionen[0];
    $oLieferschein->oPosition_arr[] = $bestellung->Positionen[1];

    $bestellung->oLieferschein_arr[] = $oLieferschein;

    $kunde                   = new stdClass();
    $kunde->fRabatt          = 0.00;
    $kunde->fGuthaben        = 0.00;
    $kunde->cAnrede          = 'm';
    $kunde->Anrede           = 'Herr';
    $kunde->cAnredeLocalized = 'Herr';
    $kunde->cTitel           = 'Dr.';
    $kunde->cVorname         = 'Max';
    $kunde->cNachname        = 'Mustermann';
    $kunde->cFirma           = 'Musterfirma';
    $kunde->cStrasse         = 'Musterstrasse';
    $kunde->cHausnummer      = '123';
    $kunde->cPLZ             = '12345';
    $kunde->cOrt             = 'Musterstadt';
    $kunde->cLand            = 'Musterland';
    $kunde->cTel             = '12345678';
    $kunde->cFax             = '98765432';
    $kunde->cMail            = $Einstellungen['emails']['email_master_absender'];
    $kunde->cUSTID           = 'ust234';
    $kunde->cBundesland      = 'NRW';
    $kunde->cAdressZusatz    = 'Linker Hof';
    $kunde->cMobil           = '01772322234';
    $kunde->dGeburtstag      = '1981-10-10';
    $kunde->cWWW             = 'http://max.de';
    $kunde->kKundengruppe    = 1;

    $Kundengruppe                = new stdClass();
    $Kundengruppe->kKundengruppe = 1;
    $Kundengruppe->cName         = 'Endkunden';
    $Kundengruppe->nNettoPreise  = 0;

    $gutschein                 = new stdClass();
    $gutschein->cLocalizedWert = '5,00 EUR';
    $gutschein->cGrund         = 'Geburtstag';

    $Kupon                        = new stdClass();
    $Kupon->cName                 = 'Kuponname';
    $Kupon->fWert                 = 5;
    $Kupon->cWertTyp              = 'festpreis';
    $Kupon->dGueltigAb            = '2007-11-07 17:05:00';
    $Kupon->dGueltigBis           = '2008-11-07 17:05:00';
    $Kupon->cCode                 = 'geheimcode';
    $Kupon->nVerwendungenProKunde = 2;
    $Kupon->AngezeigterName       = 'lokalisierter Name des Kupons';
    $Kupon->cKuponTyp             = 'standard';
    $Kupon->cLocalizedWert        = '5 EUR';
    $Kupon->cLocalizedMBW         = '100,00 EUR';
    $Kupon->fMindestbestellwert   = 100;
    $Kupon->Artikel               = [];
    $Kupon->Artikel[0]            = new stdClass();
    $Kupon->Artikel[1]            = new stdClass();
    $Kupon->Artikel[0]->cName     = 'Artikel eins';
    $Kupon->Artikel[0]->cURL      = 'http://meinshop.de/artikel=1';
    $Kupon->Artikel[1]->cName     = 'Artikel zwei';
    $Kupon->Artikel[1]->cURL      = 'http://meinshop.de/artikel=2';

    $Kupon->Kategorien           = [];
    $Kupon->Kategorien[0]        = new stdClass();
    $Kupon->Kategorien[1]        = new stdClass();
    $Kupon->Kategorien[0]->cName = 'Kategorie eins';
    $Kupon->Kategorien[0]->cURL  = 'http://meinshop.de/kat=1';
    $Kupon->Kategorien[1]->cName = 'Kategorie zwei';
    $Kupon->Kategorien[1]->cURL  = 'http://meinshop.de/kat=2';

    $Nachricht             = new stdClass();
    $Nachricht->cNachricht = 'Anfragetext...';
    $Nachricht->cAnrede    = 'm';
    $Nachricht->cVorname   = 'Max';
    $Nachricht->cNachname  = 'Mustermann';
    $Nachricht->cFirma     = 'Musterfirma';
    $Nachricht->cMail      = 'max@musterman.de';
    $Nachricht->cFax       = '34782034';
    $Nachricht->cTel       = '34782035';
    $Nachricht->cMobil     = '34782036';
    $Nachricht->cBetreff   = 'Allgemeine Anfrage';

    $Artikel                    = new stdClass();
    $Artikel->cName             = 'LAN Festplatte IPDrive';
    $Artikel->cArtNr            = 'AF8374';
    $Artikel->cEinheit          = 'Stck.';
    $Artikel->cLieferstatus     = '3-4 Tage';
    $Artikel->fPreisEinzelNetto = 111.2069;
    $Artikel->fPreis            = 368.1069;
    $Artikel->fMwSt             = 19;
    $Artikel->nAnzahl           = 1;
    $Artikel->cURL              = 'LAN-Festplatte-IPDrive';

    $CWunschliste               = new stdClass();
    $CWunschliste->kWunschlsite = 5;
    $CWunschliste->kKunde       = 1480;
    $CWunschliste->cName        = 'Wunschzettel';
    $CWunschliste->nStandard    = 1;
    $CWunschliste->nOeffentlich = 0;
    $CWunschliste->cURLID       = '5686f6vv6c86v65nv6m8';
    $CWunschliste->dErstellt    = '2009-07-12 13:55:10';

    $CWunschliste->CWunschlistePos_arr                     = [];
    $CWunschliste->CWunschlistePos_arr[0]                  = new stdClass();
    $CWunschliste->CWunschlistePos_arr[0]->kWunschlistePos = 3;
    $CWunschliste->CWunschlistePos_arr[0]->kWunschliste    = 5;
    $CWunschliste->CWunschlistePos_arr[0]->kArtikel        = 261;
    $CWunschliste->CWunschlistePos_arr[0]->cArtikelName    = 'Hansu Televsion';
    $CWunschliste->CWunschlistePos_arr[0]->fAnzahl         = 2;
    $CWunschliste->CWunschlistePos_arr[0]->cKommentar      = 'Television';
    $CWunschliste->CWunschlistePos_arr[0]->dHinzugefuegt   = '2009-07-12 13:55:11';

    $CWunschliste->CWunschlistePos_arr[0]->Artikel                        = new stdClass();
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->cName                 = 'LAN Festplatte IPDrive';
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->cEinheit              = 'Stck.';
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->fPreis                = 368.1069;
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->fMwSt                 = 19;
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->nAnzahl               = 1;
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->cURL                  = 'LAN-Festplatte-IPDrive';
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->Bilder                = [];
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->Bilder[0]             = new stdClass();
    $CWunschliste->CWunschlistePos_arr[0]->Artikel->Bilder[0]->cPfadKlein = BILD_KEIN_ARTIKELBILD_VORHANDEN;

    $CWunschliste->CWunschlistePos_arr[1]                  = new stdClass();
    $CWunschliste->CWunschlistePos_arr[1]->kWunschlistePos = 4;
    $CWunschliste->CWunschlistePos_arr[1]->kWunschliste    = 5;
    $CWunschliste->CWunschlistePos_arr[1]->kArtikel        = 262;
    $CWunschliste->CWunschlistePos_arr[1]->cArtikelName    = 'Hansu Phone';
    $CWunschliste->CWunschlistePos_arr[1]->fAnzahl         = 1;
    $CWunschliste->CWunschlistePos_arr[1]->cKommentar      = 'Phone';
    $CWunschliste->CWunschlistePos_arr[1]->dHinzugefuegt   = '2009-07-12 13:55:18';

    $CWunschliste->CWunschlistePos_arr[1]->Artikel                        = new stdClass();
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->cName                 = 'USB Connector';
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->cEinheit              = 'Stck.';
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->fPreis                = 89.90;
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->fMwSt                 = 19;
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->nAnzahl               = 1;
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->cURL                  = 'USB-Connector';
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->Bilder                = [];
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->Bilder[0]             = new stdClass();
    $CWunschliste->CWunschlistePos_arr[1]->Artikel->Bilder[0]->cPfadKlein = BILD_KEIN_ARTIKELBILD_VORHANDEN;

    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr                                = [];
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]                             = new stdClass();
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kWunschlistePosEigenschaft = 2;
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kWunschlistePos            = 4;
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kEigenschaft               = 2;
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->kEigenschaftWert           = 3;
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->cFreifeldWert              = '';
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->cEigenschaftName           = 'Farbe';
    $CWunschliste->CWunschlistePos_arr[1]->CWunschlistePosEigenschaft_arr[0]->cEigenschaftWertName       = 'rot';

    $NewsletterEmpfaenger                     = new stdClass();
    $NewsletterEmpfaenger->kSprache           = 1;
    $NewsletterEmpfaenger->kKunde             = null;
    $NewsletterEmpfaenger->nAktiv             = 0;
    $NewsletterEmpfaenger->cAnrede            = 'w';
    $NewsletterEmpfaenger->cVorname           = 'Erika';
    $NewsletterEmpfaenger->cNachname          = 'Mustermann';
    $NewsletterEmpfaenger->cEmail             = 'test@example.com';
    $NewsletterEmpfaenger->cOptCode           = '88abd18fe51be05d775a2151fbb74bf7';
    $NewsletterEmpfaenger->cLoeschCode        = 'a14a986321ff6a4998e81b84056933d3';
    $NewsletterEmpfaenger->dEingetragen       = 'now()';
    $NewsletterEmpfaenger->dLetzterNewsletter = '0000-00-00';
    $NewsletterEmpfaenger->cLoeschURL         = Shop::getURL() . '/newsletter.php?lang=ger&lc=a14a986321ff6a4998e81b84056933d3';
    $NewsletterEmpfaenger->cFreischaltURL     = Shop::getURL() . '/newsletter.php?lang=ger&fc=88abd18fe51be05d775a2151fbb74bf7';

    $Bestandskunde                = new stdClass();
    $Bestandskunde->kKunde        = 1379;
    $Bestandskunde->kKundengruppe = 1;
    $Bestandskunde->kSprache      = 1;
    $Bestandskunde->cKundenNr     = 1028;
    $Bestandskunde->cPasswort     = 'a725e241eceb20739d4617d6ae5a2cef';
    $Bestandskunde->cAnrede       = 'm';
    $Bestandskunde->Anrede        = 'Herr';
    $Bestandskunde->cTitel        = '';
    $Bestandskunde->cVorname      = 'Max';
    $Bestandskunde->cNachname     = 'Mustermann';
    $Bestandskunde->cFirma        = '';
    $Bestandskunde->cStrasse      = 'Beispielweg';
    $Bestandskunde->cHausnummer   = '5';
    $Bestandskunde->cAdressZusatz = '';
    $Bestandskunde->cPLZ          = 12345;
    $Bestandskunde->cOrt          = 'Musterhausen';
    $Bestandskunde->cBundesland   = '';
    $Bestandskunde->cLand         = 'DE';
    $Bestandskunde->cTel          = '';
    $Bestandskunde->cMobil        = '';
    $Bestandskunde->cFax          = '';
    $Bestandskunde->cMail         = 'test@example.com';
    $Bestandskunde->cUSTID        = '';
    $Bestandskunde->cWWW          = 'www.example.com';
    $Bestandskunde->fGuthaben     = 0.0;
    $Bestandskunde->cNewsletter   = '';
    $Bestandskunde->dGeburtstag   = '1980-12-03';
    $Bestandskunde->fRabatt       = 0.0;
    $Bestandskunde->cHerkunft     = '';
    $Bestandskunde->dErstellt     = '2016-07-06';
    $Bestandskunde->dVeraendert   = '2016-11-18 13:52:25';
    $Bestandskunde->cAktiv        = 'Y';
    $Bestandskunde->cAbgeholt     = 'Y';
    $Bestandskunde->nRegistriert  = 0;

    $BestandskundenBoni               = new stdClass();
    $BestandskundenBoni->kKunde       = 1379;
    $BestandskundenBoni->fGuthaben    = '2,00 &euro';
    $BestandskundenBoni->nBonuspunkte = 0;
    $BestandskundenBoni->dErhalten    = 'now()';

    $Neues_Passwort = 'geheim007';

    $Benachrichtigung            = new stdClass();
    $Benachrichtigung->cVorname  = $kunde->cVorname;
    $Benachrichtigung->cNachname = $kunde->cNachname;

    $sendStatus = true;

    foreach ($Sprachen as $Sprache) {
        $oAGBWRB = new stdClass();
        if ($kunde->kKundengruppe > 0 && $Sprache->kSprache > 0) {
            $oAGBWRB = Shop::DB()->select('ttext', ['kKundengruppe', 'kSprache'], [$kunde->kKundengruppe, $Sprache->kSprache]);
        }
        $Emailvorlagesprache[$Sprache->kSprache] = Shop::DB()->select($cTableSprache, ['kEmailvorlage', 'kSprache'], [(int)$Emailvorlage->kEmailvorlage, (int)$Sprache->kSprache]);
        if (!empty($Emailvorlagesprache[$Sprache->kSprache])) {
            $cModulId = $Emailvorlage->cModulId;
            if (verifyGPCDataInteger('kPlugin') > 0) {
                $cModulId = 'kPlugin_' . verifyGPCDataInteger('kPlugin') . '_' . $cModulId;
            }

            $bestellung->oEstimatedDelivery->localized = getDeliverytimeEstimationText($bestellung->oEstimatedDelivery->longestMin, $bestellung->oEstimatedDelivery->longestMax);
            $bestellung->cEstimatedDeliveryEx          = dateAddWeekday($bestellung->dErstellt, $bestellung->oEstimatedDelivery->longestMin)->format('d.m.Y')
                . ' - ' .
                dateAddWeekday($bestellung->dErstellt, $bestellung->oEstimatedDelivery->longestMax)->format('d.m.Y');

            $kunde->kSprache                       = $Sprache->kSprache;
            $NewsletterEmpfaenger->kSprache        = $Sprache->kSprache;
            $obj                                   = new stdClass();
            $obj->tkunde                           = $kunde;
            $obj->tkunde->cPasswortKlartext        = 'superGeheim';
            $obj->tkundengruppe                    = $Kundengruppe;
            $obj->tbestellung                      = $bestellung;
            $obj->neues_passwort                   = $Neues_Passwort;
            $obj->passwordResetLink                = Shop::getURL() . '/pass.php?fpwh=ca68b243f0c1e7e57162055f248218fd&mail=' . $kunde->cMail;
            $obj->tgutschein                       = $gutschein;
            $obj->AGB                              = $oAGBWRB;
            $obj->WRB                              = $oAGBWRB;
            $obj->tkupon                           = $Kupon;
            $obj->tnachricht                       = $Nachricht;
            $obj->tartikel                         = $Artikel;
            $obj->twunschliste                     = $CWunschliste;
            $obj->tvonkunde                        = $obj->tkunde;
            $obj->tverfuegbarkeitsbenachrichtigung = $Benachrichtigung;
            $obj->NewsletterEmpfaenger             = $NewsletterEmpfaenger;
            $res                                   = sendeMail($cModulId, $obj);
            if ($res === false) {
                $sendStatus = false;
            }
        } else {
            $cHinweis .= 'Es existiert keine Emailvorlage: ' . $Sprache->cNameDeutsch . '<br/>';
        }
    }
    if ($sendStatus === true) {
        $cHinweis .= 'E-Mail wurde erfolgreich versendet.';
    } else {
        $cFehler = 'E-Mail konnte nicht versendet werden.';
    }
}
if (isset($_POST['Aendern'], $_POST['kEmailvorlage']) && (int)$_POST['Aendern'] === 1 && (int)$_POST['kEmailvorlage'] > 0 && validateToken()) {
    $step                        = 'uebersicht';
    $kEmailvorlage               = (int)$_POST['kEmailvorlage'];
    $cUploadVerzeichnis          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $oEmailvorlageSpracheTMP_arr = Shop::DB()->selectAll(
        $cTableSprache,
        'kEmailvorlage',
        (int)$_POST['kEmailvorlage'],
        'cPDFS, cDateiname, kSprache'
    );
    $oEmailvorlageSprache_arr = [];
    if (is_array($oEmailvorlageSpracheTMP_arr) && count($oEmailvorlageSpracheTMP_arr) > 0) {
        foreach ($oEmailvorlageSpracheTMP_arr as $oEmailvorlageSpracheTMP) {
            $oEmailvorlageSprache_arr[$oEmailvorlageSpracheTMP->kSprache] = $oEmailvorlageSpracheTMP;
        }
    }
    $Sprachen = Shop::DB()->query("SELECT * FROM tsprache ORDER BY cShopStandard DESC, cNameDeutsch", 2);
    if (!isset($Emailvorlagesprache) || is_array($Emailvorlagesprache)) {
        $Emailvorlagesprache = new stdClass();
    }
    $Emailvorlagesprache->kEmailvorlage = (int)$_POST['kEmailvorlage'];
    $cAnhangError_arr                   = [];

    $revision = new Revision();
    $revision->addRevision('mail', (int)$_POST['kEmailvorlage'], true);

    foreach ($Sprachen as $Sprache) {
        // PDFs hochladen
        $cDateiname_arr    = [];
        $cPDFS_arr         = [];
        $cPDFSTMP_arr      = isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS)
            ? bauePDFArray($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS)
            : [];
        $cDateinameTMP_arr = isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname)
            ? baueDateinameArray($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname)
            : [];
        if (!isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS) ||
            strlen($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS) === 0 ||
            count($cPDFSTMP_arr) < 3
        ) {
            if (count($cPDFSTMP_arr) < 3) {
                foreach ($cPDFSTMP_arr as $i => $cPDFSTMP) {
                    $cPDFS_arr[] = $cPDFSTMP;

                    if (strlen($_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache]) > 0) {
                        $regs = [];
                        preg_match('/[A-Za-z0-9_-]+/', $_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache], $regs);
                        if (strlen($regs[0]) === strlen($_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache])) {
                            $cDateiname_arr[] = $_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache];
                            unset($_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache]);
                        } else {
                            $cFehler .= 'Fehler: Ihr Dateiname "' . $_POST['dateiname_' . ($i + 1) . '_' . $Sprache->kSprache] .
                                '" enth&auml;lt unzul&auml;ssige Zeichen (Erlaubt sind A-Z, a-z, 0-9, _ und -).<br />';
                            $nFehler = 1;
                            break;
                        }
                    } else {
                        $cDateiname_arr[] = $cDateinameTMP_arr[$i];
                    }
                }
            }

            for ($i = 1; $i <= 3; $i++) {
                if (isset($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name']) &&
                    strlen($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name']) > 0 &&
                    strlen($_POST['dateiname_' . $i . '_' . $Sprache->kSprache]) > 0
                ) {
                    if ($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['size'] <= 2097152) {
                        if (!strrpos($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name'], ';') &&
                            !strrpos($_POST['dateiname_' . $i . '_' . $Sprache->kSprache], ';')) {
                            $cPlugin = '';
                            if (verifyGPCDataInteger('kPlugin') > 0) {
                                $cPlugin = '_' . verifyGPCDataInteger('kPlugin');
                            }
                            $cUploadDatei = $cUploadVerzeichnis . $Emailvorlagesprache->kEmailvorlage .
                                '_' . $Sprache->kSprache . '_' . $i . $cPlugin . '.pdf';
                            if (!move_uploaded_file($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['tmp_name'], $cUploadDatei)) {
                                $cFehler .= 'Fehler: Die Dateien konnte nicht geschrieben werden. ' .
                                    'Pr&uuml;fen Sie bitte ob das PDF Verzeichnis Schreibrechte besitzt.<br />';
                                $nFehler = 1;
                                break;
                            }
                            $cDateiname_arr[] = $_POST['dateiname_' . $i . '_' . $Sprache->kSprache];
                            $cPDFS_arr[]      = $Emailvorlagesprache->kEmailvorlage . '_' .
                                $Sprache->kSprache . '_' . $i . $cPlugin . '.pdf';
                        } else {
                            $cFehler .= 'Fehler: Bitte geben Sie zu jeder Datei auch einen Dateinamen (Wunschnamen) ein.<br />';
                            $nFehler = 1;
                            break;
                        }
                    } else {
                        $cFehler .= 'Fehler: Die Datei muss ein PDF sein und darf maximal 2MB gro&szlig; sein.<br />';
                        $nFehler = 1;
                        break;
                    }
                } elseif (isset($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name']) &&
                    isset($_POST['dateiname_' . $i . '_' . $Sprache->kSprache]) &&
                    strlen($_FILES['pdf_' . $i . '_' . $Sprache->kSprache]['name']) > 0 &&
                    strlen($_POST['dateiname_' . $i . '_' . $Sprache->kSprache]) === 0
                ) {
                    $cFehlerAnhang_arr[$Sprache->kSprache][$i] = 1;
                    $cFehler .= 'Fehler: Sie haben zu einem PDF keinen Dateinamen angegeben.<br />';
                    $nFehler = 1;
                    break;
                }
            }
        } else {
            $cPDFS_arr = bauePDFArray($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS);

            foreach ($cPDFS_arr as $i => $cPDFS) {
                $j = $i + 1;
                if (strlen($_POST['dateiname_' . $j . '_' . $Sprache->kSprache]) > 0 && strlen($cPDFS_arr[$j - 1]) > 0) {
                    $regs = [];
                    preg_match("/[A-Za-z0-9_-]+/", $_POST['dateiname_' . $j . '_' . $Sprache->kSprache], $regs);
                    if (strlen($regs[0]) === strlen($_POST['dateiname_' . $j . '_' . $Sprache->kSprache])) {
                        $cDateiname_arr[] = $_POST['dateiname_' . $j . '_' . $Sprache->kSprache];
                    } else {
                        $cFehler .= 'Fehler: Ihr Dateiname "' . $_POST['dateiname_' . $j . '_' . $Sprache->kSprache] .
                            '" enth&auml;lt unzul&auml;ssige Zeichen (Erlaubt sind A-Z, a-z, 0-9, _ und -).<br />';
                        $nFehler = 1;
                        break;
                    }
                } else {
                    $cFehler .= 'Fehler: Sie haben zu einem PDF keinen Dateinamen angegeben.<br />';
                    $nFehler = 1;
                    break;
                }
            }
        }
        $Emailvorlagesprache->cDateiname   = '';
        $Emailvorlagesprache->kSprache     = $Sprache->kSprache;
        $Emailvorlagesprache->cBetreff     = isset($_POST['cBetreff_' . $Sprache->kSprache])
            ? $_POST['cBetreff_' . $Sprache->kSprache]
            : null;
        $Emailvorlagesprache->cContentHtml = isset($_POST['cContentHtml_' . $Sprache->kSprache])
            ? $_POST['cContentHtml_' . $Sprache->kSprache]
            : null;
        $Emailvorlagesprache->cContentText = isset($_POST['cContentText_' . $Sprache->kSprache])
            ? $_POST['cContentText_' . $Sprache->kSprache]
            : null;

        if (count($cPDFS_arr) > 0) {
            $Emailvorlagesprache->cPDFS = ';' . implode(';', $cPDFS_arr) . ';';
        } elseif (isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS) &&
            strlen($oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS) > 0) {
            $Emailvorlagesprache->cPDFS = $oEmailvorlageSprache_arr[$Sprache->kSprache]->cPDFS;
        } else {
            $Emailvorlagesprache->cPDFS = '';
        }
        if (count($cDateiname_arr) > 0) {
            $Emailvorlagesprache->cDateiname = ';' . implode(';', $cDateiname_arr) . ';';
        } elseif (isset($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname) &&
            strlen($oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname) > 0) {
            $Emailvorlagesprache->cDateiname = $oEmailvorlageSprache_arr[$Sprache->kSprache]->cDateiname;
        }
        if ($nFehler == 0) {
            Shop::DB()->delete(
                $cTableSprache,
                ['kSprache', 'kEmailvorlage'],
                [(int)$Sprache->kSprache,
                 (int)$_POST['kEmailvorlage']]
            );
            Shop::DB()->insert($cTableSprache, $Emailvorlagesprache);
            //Smarty Objekt bauen
            $mailSmarty = new JTLSmarty(true, false, false, 'mail');
            $mailSmarty->registerResource('db', new SmartyResourceNiceDB('mail'))
                       ->registerPlugin('function', 'includeMailTemplate', 'includeMailTemplate')
                       ->setCaching(0)
                       ->setDebugging(0)
                       ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR);
            try {
                $mailSmarty->fetch('db:html_' . $Emailvorlagesprache->kEmailvorlage .
                    '_' . $Sprache->kSprache . '_' . $cTableSprache);
                $mailSmarty->fetch('db:text_' . $Emailvorlagesprache->kEmailvorlage .
                    '_' . $Sprache->kSprache . '_' . $cTableSprache);
            } catch (Exception $e) {
                $oSmartyError->cText = $e->getMessage();
                $oSmartyError->nCode = 1;
            }
        }
    }
    $kEmailvorlage  = (int)$_POST['kEmailvorlage'];
    $_upd           = new stdClass();
    $_upd->cMailTyp = $_POST['cMailTyp'];
    $_upd->cAktiv   = $_POST['cEmailActive'];
    $_upd->nAKZ     = isset($_POST['nAKZ']) ? (int)$_POST['nAKZ'] : 0;
    $_upd->nAGB     = isset($_POST['nAGB']) ? (int)$_POST['nAGB'] : 0;
    $_upd->nWRB     = isset($_POST['nWRB']) ? (int)$_POST['nWRB'] : 0;
    $_upd->nWRBForm = isset($_POST['nWRBForm']) ? (int)$_POST['nWRBForm'] : 0;
    Shop::DB()->update($cTable, 'kEmailvorlage', $kEmailvorlage, $_upd);

    // Einstellungen
    Shop::DB()->delete($cTableSetting, 'kEmailvorlage', $kEmailvorlage);
    // Email Ausgangsadresse
    if (isset($_POST['cEmailOut']) && strlen($_POST['cEmailOut']) > 0) {
        saveEmailSetting($cTableSetting, $kEmailvorlage, "cEmailOut", $_POST['cEmailOut']);
    }
    // Email Absendername
    if (isset($_POST['cEmailSenderName']) && strlen($_POST['cEmailSenderName']) > 0) {
        saveEmailSetting($cTableSetting, $kEmailvorlage, "cEmailSenderName", $_POST['cEmailSenderName']);
    }
    // Email Kopie
    if (isset($_POST['cEmailCopyTo']) && strlen($_POST['cEmailCopyTo']) > 0) {
        saveEmailSetting($cTableSetting, $kEmailvorlage, "cEmailCopyTo", $_POST['cEmailCopyTo']);
    }

    if ($nFehler == 1) {
        $step = 'prebearbeiten';
    } elseif ($oSmartyError->nCode == 0) {
        setzeFehler((int)$_POST['kEmailvorlage'], false, true);
        $cHinweis = 'Emailvorlage erfolgreich ge&auml;ndert.';
        $step     = 'uebersicht';
        $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
    } else {
        $nFehler = 1;
        $step    = 'prebearbeiten';
        $cFehler = '<b>Die E-Mail Vorlage ist fehlerhaft</b><br />' . $oSmartyError->cText;
        setzeFehler($_POST['kEmailvorlage'], true);
    }
}
if (
    (
        (isset($_POST['kEmailvorlage']) && (int)$_POST['kEmailvorlage'] > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || (isset($_GET['a']) && $_GET['a'] === 'pdfloeschen')
    ) && validateToken()
) {
    $cUploadVerzeichnis  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $Emailvorlagesprache = [];

    if (empty($_POST['kEmailvorlage']) || (int)$_POST['kEmailvorlage'] === 0) {
        $_POST['kEmailvorlage'] = (isset($_GET['a'], $_GET['kEmailvorlage']) && $_GET['a'] === 'pdfloeschen')
            ? $_GET['kEmailvorlage']
            : $kEmailvorlage;
    }
    // PDF loeschen
    if (isset($_GET['kS'], $_GET['a'], $_GET['token']) &&
        $_GET['a'] === 'pdfloeschen' &&
        $_GET['token'] === $_SESSION['jtl_token']
    ) {
        $_POST['kEmailvorlage'] = $_GET['kEmailvorlage'];
        $_POST['kS']            = $_GET['kS'];
        $oEmailvorlageSprache   = Shop::DB()->select(
            $cTableSprache,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$_POST['kS'],
            null,
            null,
            false,
            'cPDFS, cDateiname'
        );
        $cPDFS_arr = bauePDFArray($oEmailvorlageSprache->cPDFS);

        if (is_array($cPDFS_arr) && count($cPDFS_arr) > 0) {
            foreach ($cPDFS_arr as $cPDFS) {
                if (file_exists($cUploadVerzeichnis . $cPDFS)) {
                    @unlink($cUploadVerzeichnis . $cPDFS);
                }
            }
        }
        $upd             = new stdClass();
        $upd->cPDFS      = '';
        $upd->cDateiname = '';
        Shop::DB()->update(
            $cTableSprache,
            ['kEmailvorlage', 'kSprache'],
            [(int)$_POST['kEmailvorlage'],
             (int)$_POST['kS']],
            $upd
        );
        $cHinweis .= 'Ihre Dateianh&auml;nge f&uuml;r Ihre gew&auml;hlte Sprache, wurden erfolgreich gel&ouml;scht.<br />';
    }

    $step       = 'bearbeiten';
    $cFromTable = isset($_REQUEST['kPlugin']) ? $cTablePluginSetting : $cTableSetting;

    $Sprachen                   = gibAlleSprachen();
    $Emailvorlage               = Shop::DB()->select($cTable, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    $oEmailEinstellung_arr      = Shop::DB()->selectAll($cFromTable, 'kEmailvorlage', (int)$Emailvorlage->kEmailvorlage);
    $oEmailEinstellungAssoc_arr = [];

    if (is_array($oEmailEinstellung_arr) && count($oEmailEinstellung_arr) > 0) {
        foreach ($oEmailEinstellung_arr as $oEmailEinstellung) {
            $oEmailEinstellungAssoc_arr[$oEmailEinstellung->cKey] = $oEmailEinstellung->cValue;
        }
    }

    foreach ($Sprachen as $Sprache) {
        $Emailvorlagesprache[$Sprache->kSprache] = Shop::DB()->select(
            $cTableSprache,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$Sprache->kSprache
        );
        // PDF Name und Dateiname vorbereiten
        $cPDFS_arr      = [];
        $cDateiname_arr = [];
        if (!empty($Emailvorlagesprache[$Sprache->kSprache]->cPDFS) > 0) {
            $cPDFSTMP_arr = bauePDFArray($Emailvorlagesprache[$Sprache->kSprache]->cPDFS);
            foreach ($cPDFSTMP_arr as $cPDFSTMP) {
                $cPDFS_arr[] = $cPDFSTMP;
            }
            $cDateinameTMP_arr = baueDateinameArray($Emailvorlagesprache[$Sprache->kSprache]->cDateiname);
            foreach ($cDateinameTMP_arr as $cDateinameTMP) {
                $cDateiname_arr[] = $cDateinameTMP;
            }
        }
        if (!isset($Emailvorlagesprache[$Sprache->kSprache]) ||
            $Emailvorlagesprache[$Sprache->kSprache] === false) {
            $Emailvorlagesprache[$Sprache->kSprache] = new stdClass();
        }
        $Emailvorlagesprache[$Sprache->kSprache]->cPDFS_arr      = $cPDFS_arr;
        $Emailvorlagesprache[$Sprache->kSprache]->cDateiname_arr = $cDateiname_arr;
    }
    $smarty->assign('Sprachen', $Sprachen)
           ->assign('oEmailEinstellungAssoc_arr', $oEmailEinstellungAssoc_arr)
           ->assign('cUploadVerzeichnis', $cUploadVerzeichnis);
}

if ($step === 'uebersicht') {
    $smarty->assign('emailvorlagen', Shop::DB()->selectAll('temailvorlage', [], [], '*', 'cModulId'))
           ->assign('oPluginEmailvorlage_arr', Shop::DB()->selectAll('tpluginemailvorlage', [], [], '*', 'cModulId'));
}

if ($step === 'bearbeiten') {
    $smarty->assign('Emailvorlage', $Emailvorlage)
           ->assign('Emailvorlagesprache', $Emailvorlagesprache);
}
$smarty->assign('kPlugin', verifyGPCDataInteger('kPlugin'))
       ->assign('cFehlerAnhang_arr', $cFehlerAnhang_arr)
       ->assign('step', $step)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('Einstellungen', $Einstellungen);

$smarty->display('emailvorlagen.tpl');

/**
 * @param string $cPDF
 * @return array
 */
function bauePDFArray($cPDF)
{
    $cPDFTMP_arr = explode(';', $cPDF);
    $cPDF_arr    = [];
    if (count($cPDFTMP_arr) > 0) {
        foreach ($cPDFTMP_arr as $cPDFTMP) {
            if (strlen($cPDFTMP) > 0) {
                $cPDF_arr[] = $cPDFTMP;
            }
        }
    }

    return $cPDF_arr;
}

/**
 * @param string $cDateiname
 * @return array
 */
function baueDateinameArray($cDateiname)
{
    $cDateinameTMP_arr = explode(';', $cDateiname);
    $cDateiname_arr    = [];
    if (count($cDateinameTMP_arr) > 0) {
        foreach ($cDateinameTMP_arr as $cDateinameTMP) {
            if (strlen($cDateinameTMP) > 0) {
                $cDateiname_arr[] = $cDateinameTMP;
            }
        }
    }

    return $cDateiname_arr;
}

/**
 * @param int  $kEmailvorlage
 * @param bool $bFehler
 * @param bool $bForce
 */
function setzeFehler($kEmailvorlage, $bFehler = true, $bForce = false)
{
    $cAktiv           = $bFehler ? 'N' : 'Y';
    $upd              = new stdClass();
    $upd->nFehlerhaft = (int)$bFehler;
    if (!$bForce) {
        $upd->cAktiv = $cAktiv;
    }
    Shop::DB()->update('temailvorlage', 'kEmailvorlage', (int)$kEmailvorlage, $upd);
}

/**
 * @param string $cTableSetting
 * @param int    $kEmailvorlage
 * @param string $cKey
 * @param string $cValue
 */
function saveEmailSetting($cTableSetting, $kEmailvorlage, $cKey, $cValue)
{
    if ((int)$kEmailvorlage > 0 && strlen($cTableSetting) > 0 && strlen($cKey) > 0 && strlen($cValue) > 0) {
        $oEmailvorlageEinstellung                = new stdClass();
        $oEmailvorlageEinstellung->kEmailvorlage = (int)$kEmailvorlage;
        $oEmailvorlageEinstellung->cKey          = $cKey;
        $oEmailvorlageEinstellung->cValue        = $cValue;

        Shop::DB()->insert($cTableSetting, $oEmailvorlageEinstellung);
    }
}
