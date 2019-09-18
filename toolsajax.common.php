<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
$xajax = new xajax('toolsajax.server.php');

$cAktuelleSeite = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    $cAktuelleSeite = basename($_SERVER['HTTP_REFERER']);
}
// Funtionen registrieren
$xajax->registerFunction('aenderKundenformularPLZ');
$xajax->registerFunction('suchVorschlag');
$xajax->registerFunction('tauscheVariationKombi');
$xajax->registerFunction('suggestions');
$xajax->registerFunction('setzeErweiterteDarstellung');
$xajax->registerFunction('fuegeEinInWarenkorbAjax');
$xajax->registerFunction('loescheWarenkorbPosAjax');
$xajax->registerFunction('gibVergleichsliste');
$xajax->registerFunction('gibPLZInfo');
$xajax->registerFunction('ermittleVersandkostenAjax');
$xajax->registerFunction('billpayRates');
$xajax->registerFunction('setSelectionWizardAnswerAjax');
$xajax->registerFunction('resetSelectionWizardAnswerAjax');
$xajax->registerFunction('checkVarkombiDependencies');
$xajax->registerFunction('gibFinanzierungInfo');
$xajax->registerFunction('gibRegionzuLand');
$xajax->registerFunction('generateToken');
$xajax->setCharEncoding(JTL_CHARSET);
$xajax->setFlag('decodeUTF8Input', true);
