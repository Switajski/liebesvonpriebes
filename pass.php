<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
/** @global JTLSmarty $smarty */
Shop::setPageType(PAGE_PASSWORTVERGESSEN);
$AktuelleSeite                   = 'PASSWORT VERGESSEN';
Shop::$AktuelleSeite             = 'PASSWORT VERGESSEN';
$Einstellungen                   = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$GLOBALS['GlobaleEinstellungen'] = array_merge($GLOBALS['GlobaleEinstellungen'], $Einstellungen);
$linkHelper                      = LinkHelper::getInstance();
$kLink      = $linkHelper->getSpecialPageLinkKey(LINKTYP_PASSWORD_VERGESSEN);
//hole alle OberKategorien
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
$step                 = 'formular';
$hinweis              = '';
$cFehler              = '';
//loginbenutzer?
if (isset($_POST['passwort_vergessen'], $_POST['email']) && (int)$_POST['passwort_vergessen'] === 1) {
    $kunde = Shop::DB()->select(
        'tkunde',
        'cMail',
        $_POST['email'],
        'nRegistriert',
        1,
        null,
        null,
        false,
        'kKunde, cSperre'
    );
    if (isset($kunde->kKunde) && $kunde->kKunde > 0 && $kunde->cSperre !== 'Y') {
        $step   = 'passwort versenden';
        $oKunde = new Kunde($kunde->kKunde);
        $oKunde->prepareResetPassword();

        $smarty->assign('Kunde', $oKunde);
    } elseif (isset($kunde->kKunde) && $kunde->kKunde > 0 && $kunde->cSperre === 'Y') {
        $hinweis = Shop::Lang()->get('accountLocked');
    } else {
        $hinweis = Shop::Lang()->get('incorrectEmail');
    }
} elseif (isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpwh'])) {
    if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
        $resetItem = Shop::DB()->select('tpasswordreset', 'cKey', $_POST['fpwh']);
        if ($resetItem) {
            $dateExpires = new DateTime($resetItem->dExpires);
            if ($dateExpires >= new DateTime()) {
                $customer = new Kunde($resetItem->kKunde);
                if ($customer && $customer->cSperre !== 'Y') {
                    $customer->updatePassword($_POST['pw_new']);
                    Shop::DB()->delete('tpasswordreset', 'kKunde', $customer->kKunde);
                    header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '?updated_pw=true');
                    exit();
                } else {
                    $cFehler = Shop::Lang()->get('invalidCustomer', 'account data');
                }
            } else {
                $cFehler = Shop::Lang()->get('invalidHash', 'account data');
            }
        } else {
            $cFehler = Shop::Lang()->get('invalidHash', 'account data');
        }
    } else {
        $cFehler = Shop::Lang()->get('passwordsMustBeEqual', 'account data');
    }
    $step = 'confirm';
    Shop::Smarty()->assign('fpwh', StringHandler::filterXSS($_POST['fpwh']));
} elseif (isset($_GET['fpwh'])) {
    $resetItem = Shop::DB()->select('tpasswordreset', 'cKey', $_GET['fpwh']);
    if($resetItem) {
        $dateExpires = new DateTime($resetItem->dExpires);
        if($dateExpires >= new DateTime()) {
            Shop::Smarty()->assign('fpwh', StringHandler::filterXSS($_GET['fpwh']));
        } else {
            $cFehler = Shop::Lang()->get('invalidHash', 'account data');
        }
    } else {
        $cFehler = Shop::Lang()->get('invalidHash', 'account data');
    }
    $step = 'confirm';
}
$cCanonicalURL    = $linkHelper->getStaticRoute('pass.php');
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_PASSWORD_VERGESSEN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
//specific assigns
$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('cFehler', $cFehler)
       ->assign('Navigation', createNavigation($AktuelleSeite))
       ->assign('requestURL', isset($requestURL) ? $requestURL : null)
       ->assign('Einstellungen', $GLOBALS['GlobaleEinstellungen']);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
$smarty->display('account/password.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
