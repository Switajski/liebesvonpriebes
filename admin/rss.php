<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';

$oAccount->permission('EXPORT_RSSFEED_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';

if (isset($_GET['f']) && (int)$_GET['f'] === 1 && validateToken()) {
    if (generiereRSSXML()) {
        $cHinweis = 'RSS Feed wurde erstellt!';
    } else {
        $cFehler = 'RSS Feed konnte nicht erstellt werden!';
    }
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_RSS, $_POST);
}
$oConfig_arr = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_RSS, '*', 'nSort');
$count = count($oConfig_arr);
for ($i = 0; $i < $count; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            $oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::DB()->select('teinstellungen', 'kEinstellungenSektion', CONF_RSS, 'cName', $oConfig_arr[$i]->cWertName);
    $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert) ? $oSetValue->cWert : null;
}

if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
    $rssNotice = "'" . PFAD_ROOT . FILE_RSS_FEED . "' kann nicht geschrieben werden.
        Bitte achten Sie darauf, dass diese Datei ausreichende Schreibrechte besitzt. Ansonsten kann keine RSS XML Datei erstellt werden.";
} else {
    $rssNotice = '<a href="rss.php?f=1">RSS-Feed XML Datei erstellen</a>';
}
$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('rsshinweis', $rssNotice)
       ->assign('fehler', $cFehler)
       ->display('rss.tpl');
