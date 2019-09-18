<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_EMAILBLACKLIST]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'emailblacklist';

// Einstellungen
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST);
}
// Kundenfelder
if (isset($_POST['emailblacklist']) && (int)$_POST['emailblacklist'] === 1 && validateToken()) {
    // Speichern
    $cEmail_arr = explode(';', $_POST['cEmail']);

    if (is_array($cEmail_arr) && count($cEmail_arr) > 0) {
        Shop::DB()->query("TRUNCATE temailblacklist", 3);

        foreach ($cEmail_arr as $cEmail) {
            $cEmail = strip_tags(trim($cEmail));
            if (strlen($cEmail) > 0) {
                $oEmailBlacklist         = new stdClass();
                $oEmailBlacklist->cEmail = $cEmail;
                Shop::DB()->insert('temailblacklist', $oEmailBlacklist);
            }
        }
    }
}

$oConfig_arr = Shop::DB()->selectAll(
    'teinstellungenconf',
    'kEinstellungenSektion',
    CONF_EMAILBLACKLIST,
    '*',
    'nSort'
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }

    $oSetValue = Shop::DB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_EMAILBLACKLIST,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert)
        ? $oSetValue->cWert
        : null;
}

// Emails auslesen und in Smarty assignen
$oEmailBlacklist_arr = Shop::DB()->query("SELECT * FROM temailblacklist", 2);
// Geblockte Emails auslesen und assignen
$oEmailBlacklistBlock_arr = Shop::DB()->query("
    SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100", 2
);

$smarty->assign('Sprachen', gibAlleSprachen())
       ->assign('oEmailBlacklist_arr', is_array($oEmailBlacklist_arr) ? $oEmailBlacklist_arr : [])
       ->assign('oEmailBlacklistBlock_arr', is_array($oEmailBlacklistBlock_arr) ? $oEmailBlacklistBlock_arr : [])
       ->assign('oConfig_arr', $oConfig_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('emailblacklist.tpl');
