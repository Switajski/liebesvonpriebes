<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
$oAccount->permission('EXTENSION_SELECTIONWIZARD_VIEW', true, true);
$cFehler  = '';
$cHinweis = '';
$step     = '';
$oNice    = Nice::getInstance();
$cTab     = 'uebersicht';
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    $step = 'uebersicht';
    setzeSprache();

    if (strlen(verifyGPDataString('tab')) > 0) {
        $cTab = verifyGPDataString('tab');
    }
    if (isset($_POST['a']) && validateToken()) {
        if ($_POST['a'] === 'newGrp') {
            $step = 'edit-group';
        } elseif ($_POST['a'] === 'newQuest') {
            $step = 'edit-question';
        } elseif ($_POST['a'] === 'addQuest') {
            $oAuswahlAssistentFrage                          = new AuswahlAssistentFrage();
            $oAuswahlAssistentFrage->cFrage                  = htmlspecialchars(
                $_POST['cFrage'],
                ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET
            );
            $oAuswahlAssistentFrage->kMerkmal                = (int)$_POST['kMerkmal'];
            $oAuswahlAssistentFrage->kAuswahlAssistentGruppe = (int)$_POST['kAuswahlAssistentGruppe'];
            $oAuswahlAssistentFrage->nSort                   = (int)$_POST['nSort'];
            $oAuswahlAssistentFrage->nAktiv                  = (int)$_POST['nAktiv'];

            $cPlausi_arr = [];
            if (isset($_POST['kAuswahlAssistentFrage']) && (int)$_POST['kAuswahlAssistentFrage'] > 0) {
                $oAuswahlAssistentFrage->kAuswahlAssistentFrage = (int)$_POST['kAuswahlAssistentFrage'];
                $cPlausi_arr                                    = $oAuswahlAssistentFrage->updateQuestion();
            } else {
                $cPlausi_arr = $oAuswahlAssistentFrage->saveQuestion();
            }

            if ((!is_array($cPlausi_arr) && $cPlausi_arr) || count($cPlausi_arr) === 0) {
                $cHinweis = 'Ihre Frage wurde erfolgreich gespeichert.';
                $cTab     = 'uebersicht';
            } elseif (is_array($cPlausi_arr) && count($cPlausi_arr) > 0) {
                $cFehler = 'Fehler: Bitte f&uuml;llen Sie alle Felder korrekt aus.';
                $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST))
                       ->assign('cPlausi_arr', $cPlausi_arr)
                       ->assign('kAuswahlAssistentFrage',
                           (isset($_POST['kAuswahlAssistentFrage']) ? (int)$_POST['kAuswahlAssistentFrage'] : 0));
            }
        }
    } elseif (isset($_GET['a'], $_GET['q']) && $_GET['a'] === 'delQuest' && (int)$_GET['q'] > 0 && validateToken()) {
        if (AuswahlAssistentFrage::deleteQuestion(['kAuswahlAssistentFrage_arr' => [$_GET['q']]])) {
            $cHinweis = 'Ihre ausgew&auml;hlte Frage wurden erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Fehler: Ihre ausgew&auml;hlte Frage konnten nicht gel&ouml;scht werden.';
        }
    } elseif (isset($_GET['a']) && $_GET['a'] === 'editQuest' && (int)$_GET['q'] > 0 && validateToken()) {
        $step = 'edit-question';
        $smarty->assign('oFrage', new AuswahlAssistentFrage((int)$_GET['q'], false));
    }

    if (isset($_POST['a']) && validateToken()) {
        if ($_POST['a'] === 'addGrp') {
            $oAuswahlAssistentGruppe                = new AuswahlAssistentGruppe();
            $oAuswahlAssistentGruppe->kSprache      = (int)$_SESSION['kSprache'];
            $oAuswahlAssistentGruppe->cName         = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET);
            $oAuswahlAssistentGruppe->cBeschreibung = $_POST['cBeschreibung'];
            $oAuswahlAssistentGruppe->nAktiv        = (int)$_POST['nAktiv'];

            $cPlausi_arr = [];
            if (isset($_POST['kAuswahlAssistentGruppe']) && (int)$_POST['kAuswahlAssistentGruppe'] > 0) {
                $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe = (int)$_POST['kAuswahlAssistentGruppe'];
                $cPlausi_arr                                      = $oAuswahlAssistentGruppe->updateGroup($_POST);
            } else {
                $cPlausi_arr = $oAuswahlAssistentGruppe->saveGroup($_POST);
            }
            if ((!is_array($cPlausi_arr) && $cPlausi_arr) || count($cPlausi_arr) === 0) {
                $step     = 'uebersicht';
                $cHinweis = 'Ihre Gruppe wurde erfolgreich gespeichert.';
                $cTab     = 'uebersicht';
            } elseif (is_array($cPlausi_arr) && count($cPlausi_arr) > 0) {
                $step    = 'edit-group';
                $cFehler = 'Fehler: Bitte f&uuml;llen Sie alle Felder korrekt aus.';
                $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST))
                       ->assign('cPlausi_arr', $cPlausi_arr)
                       ->assign('kAuswahlAssistentGruppe', (isset($_POST['kAuswahlAssistentGruppe'])
                           ? (int)$_POST['kAuswahlAssistentGruppe']
                           : 0));
            }
        } elseif ($_POST['a'] === 'delGrp') {
            if (AuswahlAssistentGruppe::deleteGroup($_POST)) {
                $cHinweis = 'Ihre ausgew&auml;hlten Gruppen wurden erfolgreich gel&ouml;scht.';
            } else {
                $cFehler = 'Fehler: Ihre ausgew&auml;hlten Gruppen konnten nicht gel&ouml;scht werden.';
            }
        } elseif ($_POST['a'] === 'saveSettings') {
            $step = 'uebersicht';
            $cHinweis .= saveAdminSectionSettings(CONF_AUSWAHLASSISTENT, $_POST);
        }
    } elseif (isset($_GET['a'], $_GET['g']) && $_GET['a'] === 'editGrp' && (int)$_GET['g'] > 0 && validateToken()) {
        $step = 'edit-group';
        $smarty->assign('oGruppe', new AuswahlAssistentGruppe($_GET['g'], false, false, true));
    }
    if ($step === 'uebersicht') {
        $smarty->assign('oAuswahlAssistentGruppe_arr',
            AuswahlAssistentGruppe::getGroups($_SESSION['kSprache'], false, false, true));
    } elseif ($step === 'edit-group') {
        $smarty->assign('oLink_arr', AuswahlAssistent::getLinks());
    } elseif ($step === 'edit-question') {
        $StdSprache = Shop::DB()->select('tsprache', 'cShopStandard', 'Y');
        $cSQLSelect = 'tmerkmal.*';
        $cSQLJoin   = '';
        if ((int)$StdSprache->kSprache !== (int)$_SESSION['kSprache']) {
            $cSQLSelect = "tmerkmalsprache.*";
            $cSQLJoin   = " JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = " . (int)$_SESSION['kSprache'];
        }
        $oMerkmal_arr = Shop::DB()->query(
            "SELECT " . $cSQLSelect . "
                FROM tmerkmal
                " . $cSQLJoin . "
                ORDER BY tmerkmal.nSort", 2
        );
        $smarty->assign('oMerkmal_arr', $oMerkmal_arr)
               ->assign('oAuswahlAssistentGruppe_arr',
                   AuswahlAssistentGruppe::getGroups($_SESSION['kSprache'], false, false, true));
    }
} else {
    $smarty->assign('noModule', true);
}
$smarty->assign('Sprachen', gibAlleSprachen())
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->assign('cTab', $cTab)
       ->assign('AUSWAHLASSISTENT_ORT_STARTSEITE', AUSWAHLASSISTENT_ORT_STARTSEITE)
       ->assign('AUSWAHLASSISTENT_ORT_KATEGORIE', AUSWAHLASSISTENT_ORT_KATEGORIE)
       ->assign('AUSWAHLASSISTENT_ORT_LINK', AUSWAHLASSISTENT_ORT_LINK)
       ->assign('oConfig_arr', getAdminSectionSettings(CONF_AUSWAHLASSISTENT));

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$smarty->display('auswahlassistent.tpl');
