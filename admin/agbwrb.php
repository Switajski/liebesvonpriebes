<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'agbwrb_inc.php';
/** @global JTLSmarty $smarty */
$oAccount->permission('ORDER_AGB_WRB_VIEW', true, true);

$cHinweis = '';
$cFehler  = '';
$step     = 'agbwrb_uebersicht';

setzeSprache();

if (verifyGPCDataInteger('agbwrb') === 1 && validateToken()) {
    // Editieren
    if (verifyGPCDataInteger('agbwrb_edit') === 1) {
        if (verifyGPCDataInteger('kKundengruppe') > 0) {
            $step    = 'agbwrb_editieren';
            $oAGBWRB = Shop::DB()->select(
                'ttext',
                'kSprache', (int)$_SESSION['kSprache'],
                'kKundengruppe', verifyGPCDataInteger('kKundengruppe')
            );
            $smarty->assign('kKundengruppe', verifyGPCDataInteger('kKundengruppe'))
                   ->assign('oAGBWRB', $oAGBWRB);
        } else {
            $cFehler .= 'Fehler: Bitte geben Sie eine g&uuml;ltige Kundengruppe an.<br />';
        }
    } elseif (verifyGPCDataInteger('agbwrb_editieren_speichern') === 1) { // Speichern
        if (speicherAGBWRB(
            verifyGPCDataInteger('kKundengruppe'),
            $_SESSION['kSprache'],
            $_POST, verifyGPCDataInteger('kText'))
        ) {
            $cHinweis .= 'Ihre AGB bzw. WRB wurde erfolgreich gespeichert.<br />';
        } else {
            $cFehler .= 'Fehler: Ihre AGB/WRB konnte nicht gespeichert werden.<br />';
        }
    }
}

if ($step === 'agbwrb_uebersicht') {
    // Kundengruppen holen
    $oKundengruppe_arr = Shop::DB()->selectAll('tkundengruppe', [], [], 'kKundengruppe, cName', 'cStandard DESC');
    // AGB fuer jeweilige Sprache holen
    $oAGBWRB_arr    = [];
    $oAGBWRBTMP_arr = Shop::DB()->selectAll('ttext', 'kSprache', (int)$_SESSION['kSprache']);
    // Assoc Array mit kKundengruppe machen
    if (is_array($oAGBWRBTMP_arr) && count($oAGBWRBTMP_arr) > 0) {
        foreach ($oAGBWRBTMP_arr as $i => $oAGBWRBTMP) {
            $oAGBWRB_arr[$oAGBWRBTMP->kKundengruppe] = $oAGBWRBTMP;
        }
    }
    $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
           ->assign('oAGBWRB_arr', $oAGBWRB_arr);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('kSprache', $_SESSION['kSprache'])
       ->display('agbwrb.tpl');
