<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global $smarty
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$linkHelper = LinkHelper::getInstance();
if (isset($_SESSION['Kunde']->kKunde) &&
    $_SESSION['Kunde']->kKunde > 0 &&
    verifyGPCDataInteger('editRechnungsadresse') === 0
) {
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true), true, 301);
}

require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'registrieren_inc.php';

$AktuelleSeite = 'REGISTRIEREN';
$Einstellungen = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_KUNDEN,
    CONF_KUNDENFELD,
    CONF_KUNDENWERBENKUNDEN,
    CONF_NEWSLETTER
]);
Shop::setPageType(PAGE_REGISTRIERUNG);
$kLink                = $linkHelper->getSpecialPageLinkKey(LINKTYP_REGISTRIEREN);
$step                 = 'formular';
$hinweis              = '';
$titel                = Shop::Lang()->get('newAccount', 'login');
$editRechnungsadresse = isset($_GET['editRechnungsadresse'])
    ? (int)$_GET['editRechnungsadresse']
    : 0;
if (isset($_POST['editRechnungsadresse'])) {
    $editRechnungsadresse = (int)$_POST['editRechnungsadresse'];
}
// Kunde speichern
if (isset($_POST['form']) && (int)$_POST['form'] === 1) {
    kundeSpeichern($_POST);
}
// Kunde ändern
if (isset($_GET['editRechnungsadresse']) && (int)$_GET['editRechnungsadresse'] === 1) {
    gibKunde();
}
if ($step === 'formular') {
    gibFormularDaten(verifyGPCDataInteger('checkout'));
}
if (isset($_FILES['vcard']) &&
    $Einstellungen['kunden']['kundenregistrierung_vcardupload'] === 'Y' &&
    validateToken()
) {
    gibKundeFromVCard($_FILES['vcard']['tmp_name']);
    @unlink($_FILES['vcard']['tmp_name']);
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
//specific assigns
$smarty->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('editRechnungsadresse', $editRechnungsadresse)
       ->assign('Ueberschrift', $titel)
       ->assign('Einstellungen', $Einstellungen)
       ->assign('hinweis', $hinweis)
       ->assign('step', $step)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
       ->assign('code_registrieren', generiereCaptchaCode($Einstellungen['kunden']['registrieren_captcha']));

$cCanonicalURL = $linkHelper->getStaticRoute('registrieren.php', true);
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_REGISTRIEREN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
//Zum prüfen wie lange ein User/Bot gebraucht hat um das Registrieren-Formular auszufüllen
if (isset($Einstellungen['kunden']['kundenregistrierung_pruefen_zeit']) &&
    $Einstellungen['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
) {
    $_SESSION['dRegZeit'] = time();
}

executeHook(HOOK_REGISTRIEREN_PAGE);

$smarty->display('register/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
