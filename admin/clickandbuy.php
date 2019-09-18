<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_CLICKANDBUY_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'clickandbuy_inc.php';
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'cab_uebersicht';

if (verifyGPCDataInteger('anmeldung') === 1) {
    $step = 'cab_anmeldung';
} elseif (verifyGPCDataInteger('register') === 1) { // Registrieren
    // Pruefen ob bereits eine MD5 Passwort gesetzt wurde
    $oEinstellungTMP = Shop::DB()->query("
        SELECT * 
            FROM teinstellungen 
            WHERE cName = 'zahlungsart_clickandbuy_md5_key'", 1
    );

    if (isset($oEinstellungTMP->cWert) && strlen($oEinstellungTMP->cWert) > 0) {
        $step    = 'cab_anmeldung';
        $cFehler = 'Fehler: Sie haben bereits aus einer voherigen Registrierung ein ' .
            'MD5-Verschl&uuml;sselungspasswort gesetzt. Bitte l&ouml;schen Sie Ihr MD5 Passwort unter ' .
            '"Kaufabwicklung->Zahlungsarten->ClickandBuy" oder brechen die Registrierung ab.';
    } else {
        // Dynkey neu generieren und als Einstellung in die DB speichern
        $cDynKey = gibUID(12, URL_SHOP);

        Shop::DB()->delete('teinstellungen', 'cName', 'zahlungsart_clickandbuy_md5_key');
        Shop::DB()->query("
            INSERT 
                INTO teinstellungen 
                VALUES('100', 'zahlungsart_clickandbuy_md5_key', '" . $cDynKey . "', 'za_clickandbuy_jtl')", 3
        );

        // Weiterleitung zur Registrierung bei ClickandBuy
        // Parameter
        $oParams = holeRegParameter($cDynKey);
        header('Location: ' . $oParams->cURL);
        exit();
    }
} elseif (verifyGPCDataInteger('succ') === 1) { // Erfolgreiche Anmeldung bei ClickandBuy
    $cHinweis = 'Sie haben sich erfolgreich bei ClickandBuy angemeldet.';
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('clickandbuy.tpl');
