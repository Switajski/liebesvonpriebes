<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

$oAccount->permission('EXPORT_FORMATS_VIEW', true, true);
/** @global JTLSmarty $smarty */
$fehler              = '';
$hinweis             = '';
$step                = 'uebersicht';
$oSmartyError        = new stdClass();
$oSmartyError->nCode = 0;
$link                = null;
if (isset($_GET['neuerExport']) && (int)$_GET['neuerExport'] === 1 && validateToken()) {
    $step = 'neuer Export';
}
// hacky
if (isset($_GET['kExportformat']) && (int)$_GET['kExportformat'] > 0 && !isset($_GET['action']) && validateToken()) {
    $step                   = 'neuer Export';
    $_POST['kExportformat'] = (int)$_GET['kExportformat'];

    if (isset($_GET['err'])) {
        $smarty->assign('oSmartyError', $oSmartyError);
        $fehler = "<b>Smarty-Syntax Fehler.</b><br />";
        if (is_array($_SESSION['last_error'])) {
            $fehler .= $_SESSION['last_error']['message'];
            unset($_SESSION['last_error']);
        }
    }
}
if (isset($_POST['neu_export']) && (int)$_POST['neu_export'] === 1 && validateToken()) {
    $ef          = new Exportformat();
    $checkResult = $ef->check($_POST);
    if ($checkResult === true) {
        $kExportformat = $ef->getExportformat();
        if ($kExportformat > 0) {
            //update
            $kExportformat = (int)$_POST['kExportformat'];
            $revision = new Revision();
            $revision->addRevision('export', $kExportformat);
            $ef->update();
            $hinweis .= 'Das Exportformat <strong>' . $ef->getName() . '</strong> wurde erfolgreich ge&auml;ndert.';
        } else {
            //insert
            $kExportformat = $ef->save();
            $hinweis .= 'Das Exportformat <strong>' . $ef->getName() . '</strong> wurde erfolgreich erstellt.';
        }

        Shop::DB()->delete('texportformateinstellungen', 'kExportformat', $kExportformat);
        $Conf        = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_EXPORTFORMATE, '*', 'nSort');
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                = new stdClass();
            $aktWert->cWert         = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName         = $Conf[$i]->cWertName;
            $aktWert->kExportformat = $kExportformat;
            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)$aktWert->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->insert('texportformateinstellungen', $aktWert);
        }
        $step  = 'uebersicht';
        $error = $ef->checkSyntax();
        if ($error !== false) {
            $step   = 'neuer Export';
            $fehler = $error;
        }
    } else {
        $_POST['cContent']   = str_replace('<tab>', "\t", $_POST['cContent']);
        $_POST['cKopfzeile'] = str_replace('<tab>', "\t", $_POST['cKopfzeile']);
        $_POST['cFusszeile'] = str_replace('<tab>', "\t", $_POST['cFusszeile']);
        $smarty->assign('cPlausiValue_arr', $checkResult)
               ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
        $step   = 'neuer Export';
        $fehler = 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben.';
    }
}
$cAction       = null;
$kExportformat = null;
if (isset($_POST['action']) && strlen($_POST['action']) > 0 && (int)$_POST['kExportformat'] > 0) {
    $cAction       = $_POST['action'];
    $kExportformat = (int)$_POST['kExportformat'];
} elseif (isset($_GET['action']) && strlen($_GET['action']) > 0 && (int)$_GET['kExportformat'] > 0) {
    $cAction       = $_GET['action'];
    $kExportformat = (int)$_GET['kExportformat'];
}
if ($cAction !== null && $kExportformat !== null && validateToken()) {
    switch ($cAction) {
        case 'export':
            $bAsync               = isset($_GET['ajax']);
            $queue                = new stdClass();
            $queue->kExportformat = $kExportformat;
            $queue->nLimit_n      = 0;
            $queue->nLimit_m      = $bAsync ? EXPORTFORMAT_ASYNC_LIMIT_M : EXPORTFORMAT_LIMIT_M;
            $queue->dErstellt     = 'now()';
            $queue->dZuBearbeiten = 'now()';

            $kExportqueue = Shop::DB()->insert('texportqueue', $queue);

            $cURL = 'do_export.php?&back=admin&token=' . $_SESSION['jtl_token'] . '&e=' . $kExportqueue;
            if ($bAsync) {
                $cURL .= '&ajax';
            }
            header('Location: ' . $cURL);
            exit;
        case 'download':
            $exportformat = Shop::DB()->select('texportformat', 'kExportformat', $kExportformat);
            if ($exportformat->cDateiname && file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename=' . $exportformat->cDateiname);
                echo file_get_contents(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
                //header('Location: ' . Shop::getURL() . '/' . PFAD_EXPORT . $exportformat->cDateiname);
                exit;
            }
            break;
        case 'edit':
            $step                   = 'neuer Export';
            $_POST['kExportformat'] = $kExportformat;
            break;
        case 'delete':
            $bDeleted = Shop::DB()->query(
                "DELETE tcron, texportformat, tjobqueue, texportqueue
                   FROM texportformat
                   LEFT JOIN tcron 
                      ON tcron.kKey = texportformat.kExportformat
                      AND tcron.cKey = 'kExportformat'
                      AND tcron.cTabelle = 'texportformat'
                   LEFT JOIN tjobqueue 
                      ON tjobqueue.kKey = texportformat.kExportformat
                      AND tjobqueue.cKey = 'kExportformat'
                      AND tjobqueue.cTabelle = 'texportformat'
                      AND tjobqueue.cJobArt = 'exportformat'
                   LEFT JOIN texportqueue 
                      ON texportqueue.kExportformat = texportformat.kExportformat
                   WHERE texportformat.kExportformat = " . $kExportformat, 3
            );

            if ($bDeleted > 0) {
                $hinweis = 'Exportformat erfolgreich gel&ouml;scht.';
            } else {
                $fehler = 'Exportformat konnte nicht gel&ouml;scht werden.';
            }
            break;
        case 'exported':
            $exportformat = Shop::DB()->select('texportformat', 'kExportformat', $kExportformat);
            if ($exportformat->cDateiname && (file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname) ||
                    file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname . '.zip') ||
                    (isset($exportformat->nSplitgroesse) && (int)$exportformat->nSplitgroesse > 0))
            ) {
                if (empty($_GET['hasError'])) {
                    $hinweis = 'Das Exportformat <b>' . $exportformat->cName . '</b> wurde erfolgreich erstellt.';
                } else {
                    $fehler = 'Das Exportformat <b>' . $exportformat->cName . '</b> konnte nicht erstellt werden.' .
                        ' Fehlende Schreibrechte?';
                }
            } else {
                $fehler = 'Das Exportformat <b>' . $exportformat->cName . '</b> konnte nicht erstellt werden.';
            }
            break;
        default:
            break;
    }
}

if ($step === 'uebersicht') {
    $exportformate = Shop::DB()->query("SELECT * FROM texportformat ORDER BY cName", 2);
    $eCount        = count($exportformate);
    for ($i = 0; $i < $eCount; $i++) {
        $exportformate[$i]->Sprache              = Shop::DB()->select('tsprache', 'kSprache', (int)$exportformate[$i]->kSprache);
        $exportformate[$i]->Waehrung             = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$exportformate[$i]->kWaehrung);
        $exportformate[$i]->Kundengruppe         = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$exportformate[$i]->kKundengruppe);
        $exportformate[$i]->bPluginContentExtern = false;
        if ($exportformate[$i]->kPlugin > 0 && strpos($exportformate[$i]->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $exportformate[$i]->bPluginContentExtern = true;
        }
    }
    $smarty->assign('exportformate', $exportformate);
}

if ($step === 'neuer Export') {
    $smarty->assign('sprachen', gibAlleSprachen())
           ->assign('kundengruppen', Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2))
           ->assign('waehrungen', Shop::DB()->query("SELECT * FROM twaehrung ORDER BY cStandard DESC", 2))
           ->assign('oKampagne_arr', holeAlleKampagnen(false, true));

    $exportformat = null;
    if (isset($_POST['kExportformat']) && (int)$_POST['kExportformat'] > 0) {
        $exportformat             = Shop::DB()->select('texportformat', 'kExportformat', (int)$_POST['kExportformat']);
        $exportformat->cKopfzeile = str_replace("\t", "<tab>", $exportformat->cKopfzeile);
        $exportformat->cContent   = str_replace("\t", "<tab>", $exportformat->cContent);
        $exportformat->cFusszeile = str_replace("\t", "<tab>", $exportformat->cFusszeile);
        if ($exportformat->kPlugin > 0 && strpos($exportformat->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $exportformat->bPluginContentFile = true;
        }
        $smarty->assign('Exportformat', $exportformat);
    }

    $Conf      = Shop::DB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_EXPORTFORMATE, '*', 'nSort');
    $confCount = count($Conf);
    for ($i = 0; $i < $confCount; $i++) {
        if ($Conf[$i]->cInputTyp === 'selectbox') {
            $Conf[$i]->ConfWerte = Shop::DB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$Conf[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        if (isset($exportformat->kExportformat)) {
            $setValue = Shop::DB()->select(
                'texportformateinstellungen',
                ['kExportformat', 'cName'],
                [(int)$exportformat->kExportformat, $Conf[$i]->cWertName]
            );
            $Conf[$i]->gesetzterWert = isset($setValue->cWert)
                ? $setValue->cWert
                : null;
        }
    }
    $smarty->assign('Conf', $Conf);
}

$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('exportformate.tpl');