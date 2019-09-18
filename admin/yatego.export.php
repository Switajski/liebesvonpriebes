<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

$oAccount->permission('EXPORT_YATEGO_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'yategoexport_uebersicht';

// Pruefe EUR als Waehrung
$oWaehrung       = Shop::DB()->query("SELECT kWaehrung FROM twaehrung WHERE cISO = 'EUR'", 1);
$bWaehrungsCheck = false;
if (isset($oWaehrung->kWaehrung) && $oWaehrung->kWaehrung > 0) {
    $bWaehrungsCheck = true;
}

if ($bWaehrungsCheck) {
    // Yatego Export
    if (verifyGPCDataInteger('yatego') === 1) {
        // Yatego Export Einstellungen
        if (isset($_POST['einstellungensubmit'])) {
            if (setzeEinstellung($_POST, $oWaehrung->kWaehrung)) {
                $cHinweis .= 'Ihre Einstellungen wurden erfolgreich gespeichert.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Einstellungen.<br />';
            }

            $smarty->assign('cTab', 'settings');
        } elseif (isset($_POST['expotieresubmit'])) { // Exportieren
            if (!exportiereYatego($_POST)) {
                $cFehler .= 'Fehler: Das Yatego Exportformat konnte nicht gefunden werden.<br />';
            }
        }
    } elseif (strlen(verifyGPDataString('rdy')) > 0) { // Export abgeschlossen
        $cHinweis = 'Der Yategoexport hat erfolgreich ' .
            base64_decode(verifyGPDataString('rdy')) . ' Artikel exportiert.';

        $smarty->assign('cTab', 'export');
    }

    if ($step === 'yategoexport_uebersicht') {
        $exportformat = Shop::DB()->select('texportformat', 'nSpecial', 1);

        $exportformat->cKopfzeile = str_replace("\t", "<tab>", $exportformat->cKopfzeile);
        $exportformat->cContent   = str_replace("\t", "<tab>", $exportformat->cContent);

        $Conf = Shop::DB()->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            CONF_EXPORTFORMATE,
            '*',
            'nSort'
        );
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
            if ($exportformat->kExportformat) {
                $setValue = Shop::DB()->select(
                    'texportformateinstellungen',
                    'kExportformat',
                    (int)$exportformat->kExportformat,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = isset($setValue->cWert)
                    ? $setValue->cWert
                    : null;
            }
        }

        $smarty->assign('Exportformat', $exportformat)
               ->assign('oConfig_arr', $Conf)
               ->assign('oSprachen', gibAlleSprachen())
               ->assign('kundengruppen', Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2))
               ->assign('waehrungen', Shop::DB()->query("SELECT * FROM twaehrung ORDER BY cStandard DESC", 2))
               ->assign('oKampagne_arr', holeAlleKampagnen(false, true));
    }
}

$smarty->assign('bWaehrungsCheck', $bWaehrungsCheck)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('bYategoSchreibbar', pruefeYategoExportPfad())
       ->assign('PFAD_EXPORT_YATEGO', PFAD_ROOT . PFAD_EXPORT_YATEGO)
       ->display('yatego.export.tpl');

/**
 * @param array $cPost_arr
 * @param int   $kWaehrung
 * @return bool
 */
function setzeEinstellung($cPost_arr, $kWaehrung)
{
    if ($cPost_arr['cName'] && (int)$cPost_arr['kSprache'] && (int)$kWaehrung && (int)$cPost_arr['kKundengruppe']) {
        if (!isset($exportformat)) {
            $exportformat = new stdClass();
        }
        $exportformat->cName           = $cPost_arr['cName'];
        $exportformat->cContent        = isset($cPost_arr['cContent'])
            ? str_replace("<tab>", "\t", $cPost_arr['cContent'])
            : null;
        $exportformat->cDateiname      = isset($cPost_arr['cDateiname'])
            ? $cPost_arr['cDateiname']
            : null;
        $exportformat->cKopfzeile      = isset($cPost_arr['cKopfzeile'])
            ? str_replace("<tab>", "\t", $cPost_arr['cKopfzeile'])
            : null;
        $exportformat->cFusszeile      = isset($cPost_arr['cFusszeile'])
            ? str_replace("<tab>", "\t", $cPost_arr['cFusszeile'])
            : null;
        $exportformat->kSprache        = (int)$cPost_arr['kSprache'];
        $exportformat->kWaehrung       = (int)$kWaehrung;
        $exportformat->kKampagne       = (int)$cPost_arr['kKampagne'];
        $exportformat->kKundengruppe   = (int)$cPost_arr['kKundengruppe'];
        $exportformat->cKodierung      = Shop::DB()->escape($cPost_arr['cKodierung']);
        $exportformat->nVarKombiOption = isset($cPost_arr['nVarKombiOption'])
            ? (int)$cPost_arr['nVarKombiOption']
            : 0;
        //update
        $kExportformat = (int)$cPost_arr['kExportformat'];
        Shop::DB()->update('texportformat', 'kExportformat', $kExportformat, $exportformat);
        Shop::DB()->delete('texportformateinstellungen', 'kExportformat', $kExportformat);
        $Conf      = Shop::DB()->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            CONF_EXPORTFORMATE,
            '*',
            'nSort'
        );
        $confCount = count($Conf);
        for ($i = 0; $i < $confCount; $i++) {
            unset($aktWert);
            $aktWert                = new stdClass();
            $aktWert->cWert         = $cPost_arr[$Conf[$i]->cWertName];
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

        return true;
    }

    return false;
}

/**
 * @param array $cPost_arr
 * @return bool
 */
function exportiereYatego($cPost_arr)
{
    if ((int)$cPost_arr['kExportformat']) {
        $queue                = new stdClass();
        $queue->kExportformat = (int)$cPost_arr['kExportformat'];
        $queue->nLimit_n      = 0;
        $queue->nLimit_m      = 2000;
        $queue->dErstellt     = 'now()';
        $queue->dZuBearbeiten = 'now()';
        Shop::DB()->insert('texportqueue', $queue);
        header('Location: yatego.do_export.php?back=admin&token=' . $_SESSION['jtl_token']);
        exit;
    }

    return false;
}
