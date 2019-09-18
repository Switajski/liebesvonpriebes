<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SEPARATOR_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.PlausiTrennzeichen.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Trennzeichen.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'trennzeichen_inc.php';
/** @global JTLSmarty $smarty */
setzeSprache();

$cHinweis = '';
$cFehler  = '';
$step     = 'trennzeichen_uebersicht';
// Speichern
if (verifyGPCDataInteger('save') === 1 && validateToken()) {
    // Plausi
    $oPlausiTrennzeichen = new PlausiTrennzeichen();
    $oPlausiTrennzeichen->setPostVar($_POST);
    $oPlausiTrennzeichen->doPlausi();

    $xPlausiVar_arr = $oPlausiTrennzeichen->getPlausiVar();
    if (count($xPlausiVar_arr) === 0) {
        if (speicherTrennzeichen($_POST)) {
            $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
            Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
        } else {
            $cFehler = 'Fehler: Ihr Einstellungen konnten nicht gespeichert werden!';
            $smarty->assign('xPostVar_arr', $oPlausiTrennzeichen->getPostVar());
        }
    } else {
        $cFehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        if (isset($xPlausiVar_arr['nDezimal_' . JTLSEPARATER_WEIGHT]) && $xPlausiVar_arr['nDezimal_' . JTLSEPARATER_WEIGHT] == 2) {
            $cFehler = 'Fehler: Die Anzahl der Dezimalstellen beim Gewicht d&uuml;rfen nicht gr&ouml;&szlig;er 4 sein!';
        }
        if (isset($xPlausiVar_arr['nDezimal_' . JTLSEPARATER_AMOUNT]) && $xPlausiVar_arr['nDezimal_' . JTLSEPARATER_AMOUNT] == 2) {
            $cFehler = 'Fehler: Die Anzahl der Dezimalstellen bei der Menge d&uuml;rfen nicht gr&ouml;&szlig;er 2 sein!';
        }
        $smarty->assign('xPlausiVar_arr', $oPlausiTrennzeichen->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiTrennzeichen->getPostVar());
    }
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('oTrennzeichenAssoc_arr', Trennzeichen::getAll($_SESSION['kSprache']))
       ->assign('JTLSEPARATER_WEIGHT', JTLSEPARATER_WEIGHT)
       ->assign('JTLSEPARATER_LENGTH', JTLSEPARATER_LENGTH)
       ->assign('JTLSEPARATER_AMOUNT', JTLSEPARATER_AMOUNT)
       ->display('trennzeichen.tpl');
