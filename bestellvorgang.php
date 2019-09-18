<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'registrieren_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'trustedshops_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';

/** @global JTLSmarty $smarty */

$AktuelleSeite = 'BESTELLVORGANG';
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_KUNDENFELD,
    CONF_TRUSTEDSHOPS,
    CONF_ARTIKELDETAILS
]);
Shop::setPageType(PAGE_BESTELLVORGANG);
$step     = 'accountwahl';
$cHinweis = '';
// Kill Ajaxcheckout falls vorhanden
unset($_SESSION['ajaxcheckout']);
// Loginbenutzer?
if (isset($_POST['login']) && (int)$_POST['login'] === 1) {
    fuehreLoginAus($_POST['email'], $_POST['passwort']);
}
if (verifyGPCDataInteger('basket2Pers') === 1) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';

    setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
    header('Location: bestellvorgang.php?wk=1');
    exit();
}
// Ist Bestellung moeglich?
if ($_SESSION['Warenkorb']->istBestellungMoeglich() !== 10) {
    pruefeBestellungMoeglich();
}
// Pflicht-Uploads vorhanden?
if (class_exists('Upload') && !Upload::pruefeWarenkorbUploads($_SESSION['Warenkorb'])) {
    Upload::redirectWarenkorb(UPLOAD_ERROR_NEED_UPLOAD);
}
// Download-Artikel vorhanden?
if (class_exists('Download') && Download::hasDownloads($_SESSION['Warenkorb'])) {
    // Nur registrierte Benutzer
    $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] = 'N';
}
// oneClick? Darf nur einmal ausgeführt werden und nur dann, wenn man vom Warenkorb kommt.
if ($Einstellungen['kaufabwicklung']['bestellvorgang_kaufabwicklungsmethode'] === 'NO' &&
    verifyGPCDataInteger('wk') === 1
) {
    $kKunde = 0;
    if (isset($_SESSION['Kunde']->kKunde)) {
        $kKunde = $_SESSION['Kunde']->kKunde;
    }
    $oWarenkorbPers = new WarenkorbPers($kKunde);
    if (!(isset($_POST['login']) && (int)$_POST['login'] === 1 &&
        $Einstellungen['global']['warenkorbpers_nutzen'] === 'Y' &&
        $Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P' &&
        count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0)
    ) {
        pruefeAjaxEinKlick();
    }
}
if (verifyGPCDataInteger('wk') === 1) {
    resetNeuKundenKupon();
}
if (isset($_FILES['vcard']) &&
    $Einstellungen['kunden']['kundenregistrierung_vcardupload'] === 'Y' &&
    validateToken()
) {
    gibKundeFromVCard($_FILES['vcard']['tmp_name']);
    @unlink($_FILES['vcard']['tmp_name']);
}
if (isset($_POST['unreg_form']) && (int)$_POST['unreg_form'] === 1 &&
    $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y'
) {
    pruefeUnregistriertBestellen($_POST);
}
if (isset($_GET['editLieferadresse'])) {
    // Shipping address and customer address are now on same site
    $_GET['editRechnungsadresse'] = $_GET['editLieferadresse'];
}
if (isset($_POST['unreg_form']) && (int)$_POST['unreg_form'] === 0) {
    $_POST['checkout'] = 1;
    $_POST['form']     = 1;
    include 'registrieren.php';
}
if (isset($_POST['versandartwahl']) && (int)$_POST['versandartwahl'] === 1 || isset($_GET['kVersandart'])) {
    unset($_SESSION['Zahlungsart']);
    $kVersandart = null;

    if (isset($_GET['kVersandart'])) {
        $kVersandart = (int)$_GET['kVersandart'];
    } elseif (isset($_POST['Versandart'])) {
        $kVersandart = (int)$_POST['Versandart'];
    }

    pruefeVersandartWahl($kVersandart);
}
if (isset($_GET['unreg']) && (int)$_GET['unreg'] === 1 &&
    $Einstellungen['kaufabwicklung']['bestellvorgang_unregistriert'] === 'Y'
) {
    $step = 'edit_customer_address';
}
//autom. step ermitteln
if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']) {
    $step = 'Lieferadresse';

    if (!isset($_SESSION['Lieferadresse'])) {
        pruefeLieferdaten(['kLieferadresse' => 0]);
    }

    if (!isset($_SESSION['Versandart']) || !is_object($_SESSION['Versandart'])) {
        $land          = isset($_SESSION['Lieferadresse']->cLand) ? $_SESSION['Lieferadresse']->cLand : $_SESSION['Kunde']->cLand;
        $plz           = isset($_SESSION['Lieferadresse']->cPLZ) ? $_SESSION['Lieferadresse']->cPLZ : $_SESSION['Kunde']->cPLZ;
        $kKundengruppe = isset($_SESSION['Kunde']->kKundengruppe) ? $_SESSION['Kunde']->kKundengruppe : $_SESSION['Kundengruppe']->kKundengruppe;

        $oVersandart_arr  = VersandartHelper::getPossibleShippingMethods(
            $land,
            $plz,
            VersandartHelper::getShippingClasses($_SESSION['Warenkorb']),
            $kKundengruppe
        );
        $activeVersandart = gibAktiveVersandart($oVersandart_arr);

        pruefeVersandartWahl(
            $activeVersandart,
            ['kVerpackung' => array_keys(gibAktiveVerpackung(gibMoeglicheVerpackungen($kKundengruppe)))]
        );
    }
}
//Download-Artikel vorhanden?
if ($step !== 'accountwahl' &&
    class_exists('Download') &&
    Download::hasDownloads($_SESSION['Warenkorb']) &&
    (!isset($_SESSION['Kunde']->cPasswort) || strlen($_SESSION['Kunde']->cPasswort) === 0)
) {
    // Falls unregistrierter Kunde bereits im Checkout war und einen Downloadartikel hinzugefuegt hat
    $step      = 'accountwahl';
    $cHinweis  = Shop::Lang()->get('digitalProductsRegisterInfo', 'checkout');
    $cPost_arr = StringHandler::filterXSS($_POST);

    Shop::Smarty()->assign('cKundenattribut_arr', getKundenattribute($cPost_arr))
                  ->assign('kLieferadresse', $cPost_arr['kLieferadresse'])
                  ->assign('cPost_var', $cPost_arr);

    if ((int)$cPost_arr['shipping_address'] === 1) {
        Shop::Smarty()->assign('Lieferadresse', mappeLieferadresseKontaktdaten($cPost_arr['register']['shipping_address']));
    }

    unset($_SESSION['Kunde']);
}
//autom. step ermitteln
pruefeVersandkostenStep();
//autom. step ermitteln
pruefeZahlungStep();
//autom. step ermitteln
pruefeBestaetigungStep();
//sondersteps Rechnungsadresse aendern
pruefeRechnungsadresseStep($_GET);
//sondersteps Lieferadresse aendern
pruefeLieferadresseStep($_GET);
//sondersteps Versandart aendern
pruefeVersandartStep($_GET);
//sondersteps Zahlungsart aendern
pruefeZahlungsartStep($_GET);
pruefeZahlungsartwahlStep($_POST);

if ($step === 'accountwahl') {
    gibStepAccountwahl();
    gibStepUnregistriertBestellen();
}
if ($step === 'edit_customer_address' || $step === 'Lieferadresse') {
    validateCouponInCheckout();
    gibStepUnregistriertBestellen();
    gibStepLieferadresse();
}
if ($step === 'Versand' || $step === 'Zahlung') {
    gibStepVersand();
    gibStepZahlung();
    Warenkorb::refreshChecksum($_SESSION['Warenkorb']);
}
if ($step === 'ZahlungZusatzschritt') {
    gibStepZahlungZusatzschritt($_POST);
    Warenkorb::refreshChecksum($_SESSION['Warenkorb']);
}
if ($step === 'Bestaetigung') {
    plausiGuthaben($_POST);
    $smarty->assign('cKuponfehler_arr', plausiKupon($_POST));
    //evtl genutztes guthaben anpassen
    pruefeGuthabenNutzen();
    gibStepBestaetigung($_GET);
    $_SESSION['Warenkorb']->cEstimatedDelivery = $_SESSION['Warenkorb']->getEstimatedDeliveryTime();
    Warenkorb::refreshChecksum($_SESSION['Warenkorb']);
}
//SafetyPay Work Around
if (isset($_SESSION['Zahlungsart']->cModulId) &&
    $_SESSION['Zahlungsart']->cModulId === 'za_safetypay' &&
    $step === 'Bestaetigung'
) {
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/safetypay.php';
    $smarty->assign('safetypay_form', gib_safetypay_form(
        $_SESSION['Kunde'],
        $_SESSION['Warenkorb'],
        $Einstellungen['zahlungsarten']
    ));
}
//Billpay
if (isset($_SESSION['Zahlungsart']) &&
    $_SESSION['Zahlungsart']->cModulId === 'za_billpay_jtl' &&
    $step === 'Bestaetigung'
) {
    /** @var Billpay $paymentMethod */
    $paymentMethod = PaymentMethod::create('za_billpay_jtl');
    $paymentMethod->handleConfirmation();
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;

WarenkorbHelper::addVariationPictures($_SESSION['Warenkorb']);

//specific assigns
$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('AGB', gibAGBWRB(Shop::getLanguage(), $_SESSION['Kundengruppe']->kKundengruppe))
       ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
       ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
       ->assign('Einstellungen', $Einstellungen)
       ->assign('hinweis', $cHinweis)
       ->assign('step', $step)
       ->assign('editRechnungsadresse', verifyGPCDataInteger('editRechnungsadresse'))
       ->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
       ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren())
       ->assign('Steuerpositionen', $_SESSION['Warenkorb']->gibSteuerpositionen())
       ->assign('bestellschritt', gibBestellschritt($step))
       ->assign('requestURL', (isset($requestURL) ? $requestURL : null))
       ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
       ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_BESTELLVORGANG_PAGE);

$smarty->display('checkout/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
