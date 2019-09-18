<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

global $oPlugin;

$eWertHerkunft_arr = array(
    'Artikel-Eigenschaft' => 'ArtikelEigenschaft',
    'Funktionsattribut'   => 'FunktionsAttribut',
    'Attribut'            => 'Attribut',
    'Merkmal'             => 'Merkmal',
    'statischer Wert'     => 'WertName',
    'Vater-Attribut'      => 'VaterAttribut');
$stepPlugin        = 'attribute';
$cHinweis          = '';
$cFehler           = '';

if (isset($_POST['stepPlugin']) && ($_POST['stepPlugin'] === 'neuesAttr' || $_POST['stepPlugin'] === 'alteAttr')) {
    if (isset($_POST['btn_delete'])) {
        $kAttribut = key($_POST['btn_delete']);
        $bSuccess  = Shop::DB()->query("
            DELETE FROM xplugin_" . $oPlugin->cPluginID . "_attribut
                WHERE bStandard != 1
                    AND kAttribut = " . $kAttribut, 3
        );
        if ($bSuccess) {
            $cHinweis = 'Einstellung mit der ID: ' . $kAttribut . ' wurde erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Einstellung mit der ID: ' . $kAttribut . ' konnte nicht gel&ouml;scht werden.';
        }
        unset($_POST);
    } elseif (isset($_POST['btn_standard'])) {
        $kAttribut = key($_POST['btn_standard']);
        $bSuccess  = Shop::DB()->query(
            "UPDATE
                xplugin_" . $oPlugin->cPluginID . "_attribut
            SET
                cGoogleName = cStandardGoogleName,
                cWertName = cStandardWertName,
                eWertHerkunft = eStandardWertHerkunft,
                kVaterAttribut = kStandardVaterAttribut
            WHERE
                bStandard = 1 AND
                kAttribut = " . $kAttribut, 3
        );
        if ($bSuccess) {
            $cHinweis = 'Einstellung mit der ID: ' . $kAttribut . ' wurde erfolgreich auf Standard zur&uuml;ck gesetzt.';
        } else {
            $cFehler = 'Einstellung mit der ID: ' . $kAttribut . ' konnte nicht auf Standard zur&uuml;ck gesetzt werden.';
        }
        unset($_POST);
    } elseif (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] === 'neuesAttr') {
        $oNewAttribut                 = new stdClass();
        $oNewAttribut->cGoogleName    = $_POST['cGoogleName'];
        $oNewAttribut->cWertName      = $_POST['cWertName'];
        $oNewAttribut->eWertHerkunft  = $_POST['eWertHerkunft'];
        $oNewAttribut->kVaterAttribut = isset($_POST['kVaterAttribut']) ? intval($_POST['kVaterAttribut']) : 0;
        $oNewAttribut->bAktiv         = isset($_POST['bAktiv']) ? 1 : 0;

        $oPlausiReturn = plausiAttribut($oNewAttribut, $eWertHerkunft_arr, $oPlugin);
        if ($oPlausiReturn->bFehler == true) {
            $cFehler = $oPlausiReturn->cFehler;
        } else {
            $iRes = Shop::DB()->insert('xplugin_' . $oPlugin->cPluginID . '_attribut', $oNewAttribut);
            if ($iRes > 0) {
                $cHinweis = 'Daten wurden erfolgreich hinzugef&uuml;gt.';
                unset($_POST);
            } else {
                $cFehler = 'Es ist ein Fehler beim Einf&uuml;gen in die Datenbank aufgetreten.';
            }
        }
    } elseif (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] === 'alteAttr') {
        foreach ($_POST['eWertHerkunft'] as $key => $value) {
            $oOldAttribut            = new stdClass();
            $oOldAttribut->kAttribut = intval($key);
            if (isset($_POST['cGoogleName'][$key]) && strlen(trim($_POST['cGoogleName'][$key])) > 0) {
                $oOldAttribut->cGoogleName = trim($_POST['cGoogleName'][$key]);
            } else {
                $oOldAttribut->bStandard = true;
            }
            $oOldAttribut->cWertName      = trim($_POST['cWertName'][$key]);
            $oOldAttribut->eWertHerkunft  = $_POST['eWertHerkunft'][$key];
            $oOldAttribut->kVaterAttribut = isset($_POST['kVaterAttribut'][$key]) ? intval($_POST['kVaterAttribut'][$key]) : 0;
            $oOldAttribut->bAktiv         = isset($_POST['bAktiv'][$key]) ? 1 : 0;
            $oPlausiReturn                = plausiAttribut($oOldAttribut, $eWertHerkunft_arr, $oPlugin);
            if ($oPlausiReturn->bFehler == true) {
                $cFehler .= $oPlausiReturn->cFehler;
            } else {
                $cSqlStandard     = "";
                $cSqlNoneStandard = "";
                if ($oOldAttribut->bStandard) {
                    $cSqlStandard = "AND bStandard = 1";
                } else {
                    $cSqlNoneStandard = "cGoogleName = '{$oOldAttribut->cGoogleName}', kVaterAttribut = {$oOldAttribut->kVaterAttribut},";
                }
                $iRes = Shop::DB()->query(
                    "UPDATE
                        xplugin_" . $oPlugin->cPluginID . "_attribut
                    SET
                        {$cSqlNoneStandard}
                        cWertName = '{$oOldAttribut->cWertName}',
                        eWertHerkunft = '{$oOldAttribut->eWertHerkunft}',
                        bAktiv = {$oOldAttribut->bAktiv}
                    WHERE
                        kAttribut = {$oOldAttribut->kAttribut} {$cSqlStandard}", 3
                );
                if ($iRes === false) {
                    $cFehler = "Es ist ein Fehler beim &Auml;ndern des Attribut {$oOldAttribut->kAttribut} aufgetreten.";
                }
                if ($iRes > 0) {
                    $cHinweis .= "Attribut mit der ID {$oOldAttribut->kAttribut} erfolgreich ge&auml;ndert.<br />";
                }
            }
        }
        unset($_POST);
    }
}

$attribute_arr        = Shop::DB()->query("SELECT * FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE kVaterAttribut = 0 ORDER BY kAttribut", 2);
$resKindAttribute_arr = Shop::DB()->query("SELECT * FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE kVaterAttribut != 0 ORDER BY kAttribut", 2);
$kindAttribute_arr    = array();
if (isset($resKindAttribute_arr) && is_array($resKindAttribute_arr)) {
    foreach ($resKindAttribute_arr as $kindAttribut_arr) {
        if (isset($kindAttribute_arr[$kindAttribut_arr->kVaterAttribut]) && is_array($kindAttribute_arr[$kindAttribut_arr->kVaterAttribut])) {
            $kindAttribute_arr[$kindAttribut_arr->kVaterAttribut][] = $kindAttribut_arr;
        } else {
            $kindAttribute_arr[$kindAttribut_arr->kVaterAttribut] = array($kindAttribut_arr);
        }
    }
}
Shop::Smarty()->assign('attribute_arr', $attribute_arr)
    ->assign('kindAttribute_arr', $kindAttribute_arr)
    ->assign('eWertHerkunft_arr', $eWertHerkunft_arr)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('URL_SHOP', Shop::getURL())
    ->assign('PFAD_ROOT', PFAD_ROOT)
    ->assign('URL_ADMINMENU', Shop::getURL() . '/' . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_ADMINMENU)
    ->assign('stepPlugin', $stepPlugin)
    ->display($oPlugin->cAdminmenuPfad . 'templates/attribute.tpl');

/**
 * @param object $oAttribut
 * @param array  $eWertHerkunft_arr
 * @param Plugin $oPlugin
 * @return stdClass
 */
function plausiAttribut($oAttribut, $eWertHerkunft_arr, $oPlugin)
{
    $cErweiterungAttribut = (isset($oAttribut->kAttribut) && $oAttribut->kAttribut > 0) ?
        "des Attribut mit der ID {$oAttribut->kAttribut} " :
        '';
    $oReturn              = new stdClass();
    $oReturn->cFehler     = "Es ist ein Fehler beim Validieren der Eingaben {$cErweiterungAttribut}aufgetreten:<br />";
    $oReturn->bFehler     = false;

    if (!isset($oAttribut->bStandard) || $oAttribut->bStandard == 0) {
        if (!isset($oAttribut->cGoogleName) || strlen(trim($oAttribut->cGoogleName)) == 0) {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= '- Es muss ein g&uuml;ltiger Wert f&uuml;r "Google Name" eingegeben werden.<br />';
        }
    }

    if (isset($oAttribut->eWertHerkunft) && $oAttribut->eWertHerkunft != "VaterAttribut") {
        if (!isset($oAttribut->cWertName) || strlen(trim($oAttribut->cWertName)) == 0) {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= '- Es muss ein g&uuml;ltiger Wert f&uuml;r "Wert Name" eingegeben werden.<br />';
        }
    }
    if (!isset($oAttribut->eWertHerkunft) || !in_array($oAttribut->eWertHerkunft, $eWertHerkunft_arr)) {
        $oReturn->bFehler = true;
        $oReturn->cFehler .= '- Es muss ein g&uuml;ltiger Wert f&uuml;r "Werttyp" ausgew&auml;hlt werden.<br />';
    }
    if (!isset($oAttribut->kVaterAttribut) || !is_int($oAttribut->kVaterAttribut)) {
        $oReturn->bFehler = true;
        $oReturn->cFehler .= '- Es muss eine g&uuml;ltige Ganzzahl f&uuml;r "V-ID" eingegeben werden.<br />';
    } elseif ($oAttribut->kVaterAttribut > 0) {
        $oRes = Shop::DB()->query("SELECT eWertHerkunft FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE kAttribut = " . intval($oAttribut->kVaterAttribut), 1);
        if ($oRes->eWertHerkunft !== 'VaterAttribut') {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= '- Als "V-ID" k&ouml;nnen Sie nur die ID eines Atributes angeben wenn dessen Werttyp "VaterAttribut" ist.<br />';
        }
        if ($oAttribut->eWertHerkunft === 'VaterAttribut') {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= '- Wenn eine "V-ID" gesetzt ist darf der Werttyp nicht "VaterAttribut" sein.<br />';
        }
    }

    return $oReturn;
}
