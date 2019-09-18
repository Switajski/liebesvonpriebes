<?php

global $smarty, $oPlugin;

require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");

$eWertHerkunft_arr = array(
    'Artikel Eigenschaft' => 'ArtikelEigenschaft',
    'Funktions Attribut' => 'FunktionsAttribut',
    'Attribut' => 'Attribut',
    'Merkmal' => 'Merkmal',
    'statischer Wert' => 'WertName',
    'Vater Attribut' => 'VaterAttribut');
$stepPlugin = "attribute";
$cHinweis = "";
$cFehler = "";

if (isset($_POST['stepPlugin']) && ($_POST['stepPlugin'] == "neuesAttr" || $_POST['stepPlugin'] == "alteAttr")) {
    if (isset($_POST['btn_delete'])) {
        $kAttribut = key($_POST['btn_delete']);
        $bSuccess = $GLOBALS["DB"]->executeQuery("DELETE FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE bStandard != 1 AND kAttribut = " . $kAttribut, 'x');
        if ($bSuccess) {
            $cHinweis = 'Einstellung mit der ID: ' . $kAttribut . ' wurde erfolgreich gelöscht.';
        } else {
            $cFehler = 'Einstellung mit der ID: ' . $kAttribut . ' konnte nicht gelöscht werden.';
        }
        unset($_POST);
    } elseif (isset($_POST['btn_standard'])) {
        $kAttribut = key($_POST['btn_standard']);
        $bSuccess = $GLOBALS["DB"]->executeQuery("
            UPDATE
                xplugin_" . $oPlugin->cPluginID . "_attribut
            SET
                cGoogleName = cStandardGoogleName,
                cWertName = cStandardWertName,
                eWertHerkunft = eStandardWertHerkunft,
                kVaterAttribut = kStandardVaterAttribut
            WHERE
                bStandard = 1 AND
                kAttribut = " . $kAttribut, 'x');
        if ($bSuccess) {
            $cHinweis = 'Einstellung mit der ID: ' . $kAttribut . ' wurde erfolgreich auf Standard zurück gesetzt.';
        } else {
            $cFehler = 'Einstellung mit der ID: ' . $kAttribut . ' konnte nicht auf Standard zurück gesetzt werden.';
        }
        unset($_POST);
    } elseif (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == "neuesAttr") {
        $oNewAttribut = new stdClass();
        $oNewAttribut->cGoogleName = $_POST['cGoogleName'];
        $oNewAttribut->cWertName = $_POST['cWertName'];
        $oNewAttribut->eWertHerkunft = $_POST['eWertHerkunft'];
        $oNewAttribut->kVaterAttribut = isset($_POST['kVaterAttribut']) ? intval($_POST['kVaterAttribut']) : 0;
        $oNewAttribut->bAktiv = isset($_POST['bAktiv']) ? 1 : 0;

        $oPlausiReturn = plausiAttribut($oNewAttribut, $eWertHerkunft_arr, $oPlugin);
        if ($oPlausiReturn->bFehler == true) {
            $cFehler = $oPlausiReturn->cFehler;
        } else {
            $iRes = $GLOBALS["DB"]->insertRow("xplugin_" . $oPlugin->cPluginID . "_attribut", $oNewAttribut);
            if ($iRes > 0) {
                $cHinweis = "Daten wurden erfolgreich hinzugefügt";
                unset($_POST);
            } else {
                $cFehler = "Es ist ein Fehler beim Einfügen in die Datenbank aufgetreten.";
            }
        }
    } elseif (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == "alteAttr") {
        foreach ($_POST['eWertHerkunft'] as $key => $value) {
            $oOldAttribut = new stdClass();
            $oOldAttribut->kAttribut = intval($key);
            if (isset($_POST['cGoogleName'][$key]) && strlen(trim($_POST['cGoogleName'][$key])) > 0) {
                $oOldAttribut->cGoogleName = trim($_POST['cGoogleName'][$key]);
            } else {
                $oOldAttribut->bStandard = true;
            }
            $oOldAttribut->cWertName = trim($_POST['cWertName'][$key]);
            $oOldAttribut->eWertHerkunft = $_POST['eWertHerkunft'][$key];
            $oOldAttribut->kVaterAttribut = isset($_POST['kVaterAttribut'][$key]) ? intval($_POST['kVaterAttribut'][$key]) : 0;
            $oOldAttribut->bAktiv = isset($_POST['bAktiv'][$key]) ? 1 : 0;
            $oPlausiReturn = plausiAttribut($oOldAttribut, $eWertHerkunft_arr, $oPlugin);
            if ($oPlausiReturn->bFehler == true) {
                $cFehler .= $oPlausiReturn->cFehler;
            } else {
                $cSqlStandard = "";
                $cSqlNoneStandard = "";
                if ($oOldAttribut->bStandard) {
                    $cSqlStandard = "AND bStandard = 1";
                } else {
                    $cSqlNoneStandard = "cGoogleName = '{$oOldAttribut->cGoogleName}',
                    kVaterAttribut = {$oOldAttribut->kVaterAttribut},";
                }
                $iRes = $GLOBALS["DB"]->executeQuery("
                    UPDATE
                        xplugin_" . $oPlugin->cPluginID . "_attribut
                    SET
                        {$cSqlNoneStandard}
                        cWertName = '{$oOldAttribut->cWertName}',
                        eWertHerkunft = '{$oOldAttribut->eWertHerkunft}',
                        bAktiv = {$oOldAttribut->bAktiv}
                    WHERE
                        kAttribut = {$oOldAttribut->kAttribut} {$cSqlStandard}", 3);
                if ($iRes === false) {
                    $cFehler = "Es ist ein Fehler beim Ändern des Attribut {$oOldAttribut->kAttribut} aufgetreten.";
                }
                if ($iRes > 0) {
                    $cHinweis .= "Attribut mit der ID {$oOldAttribut->kAttribut} erfolgreich ge&auml;ndert.<br />";
                }
            }
        }
        unset($_POST);
    }
}

$attribute_arr = $GLOBALS["DB"]->executeQuery("SELECT * FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE kVaterAttribut = 0 ORDER BY kAttribut", 2);
$resKindAttribute_arr = $GLOBALS["DB"]->executeQuery("SELECT * FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE kVaterAttribut != 0 ORDER BY kAttribut", 2);
$kindAttribute_arr = array();
if (isset($resKindAttribute_arr) && is_array($resKindAttribute_arr)) {
    foreach ($resKindAttribute_arr as $kindAttribut_arr) {
        if (isset($kindAttribute_arr[$kindAttribut_arr->kVaterAttribut]) && is_array($kindAttribute_arr[$kindAttribut_arr->kVaterAttribut])) {
            array_push($kindAttribute_arr[$kindAttribut_arr->kVaterAttribut], $kindAttribut_arr);
        } else {
            $kindAttribute_arr[$kindAttribut_arr->kVaterAttribut] = array($kindAttribut_arr);
        }
    }
}
$smarty->assign('attribute_arr', $attribute_arr);
$smarty->assign('kindAttribute_arr', $kindAttribute_arr);
$smarty->assign('eWertHerkunft_arr', $eWertHerkunft_arr);

$smarty->assign("cHinweis", $cHinweis);
$smarty->assign("cFehler", $cFehler);
$smarty->assign("URL_SHOP", URL_SHOP);
$smarty->assign("PFAD_ROOT", PFAD_ROOT);
$smarty->assign("URL_ADMINMENU", URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);
$smarty->assign("stepPlugin", $stepPlugin);
print($smarty->fetch($oPlugin->cAdminmenuPfad . "templates/attribute.tpl"));

function plausiAttribut($oAttribut, $eWertHerkunft_arr, $oPlugin)
{
    //var_dump($oAttribut);
    if (isset($oAttribut->kAttribut) && $oAttribut->kAttribut > 0) {
        $cErweiterungAttribut = "des Attribut mit der ID {$oAttribut->kAttribut} ";
    } else {
        $cErweiterungAttribut = "";
    }

    $oReturn = new stdClass();
    $oReturn->cFehler = "Es ist ein Fehler beim Validieren der Eingaben {$cErweiterungAttribut}aufgetreten:<br />";
    $oReturn->bFehler = false;

    if (!isset($oAttribut->bStandard) || $oAttribut->bStandard == 0) {
        if (!isset($oAttribut->cGoogleName) || strlen(trim($oAttribut->cGoogleName)) == 0) {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= "-Es muss ein g&uuml;ltiger Wert für \"Google Name\" eingegeben werden.<br />";
        }
    }

    if (isset($oAttribut->eWertHerkunft) && $oAttribut->eWertHerkunft != "VaterAttribut") {
        if (!isset($oAttribut->cWertName) || strlen(trim($oAttribut->cWertName)) == 0) {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= "-Es muss ein g&uuml;ltiger Wert für \"Wert Name\" eingegeben werden.<br />";
        }
    }
    if (!isset($oAttribut->eWertHerkunft) || !in_array($oAttribut->eWertHerkunft, $eWertHerkunft_arr)) {
        $oReturn->bFehler = true;
        $oReturn->cFehler .= "-Es muss ein g&uuml;ltiger Wert für \"Werttyp\" ausgewählt werden.<br />";
    }
    if (!isset($oAttribut->kVaterAttribut) || !is_int($oAttribut->kVaterAttribut)) {
        $oReturn->bFehler = true;
        $oReturn->cFehler .= "-Es muss eine g&uuml;ltige Ganzzahl für \"V-ID\" eingegeben werden.<br />";
    } elseif ($oAttribut->kVaterAttribut > 0) {
        $oRes = $GLOBALS["DB"]->executeQuery("SELECT eWertHerkunft FROM xplugin_" . $oPlugin->cPluginID . "_attribut WHERE kAttribut = " . intval($oAttribut->kVaterAttribut), 1);
        if ($oRes->eWertHerkunft != "VaterAttribut") {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= "-Als \"V-ID\" können Sie nur die ID eines Atributes angeben wenn dessen Werttyp \"VaterAttribut\" ist.<br />";
        }
        if ($oAttribut->eWertHerkunft == "VaterAttribut") {
            $oReturn->bFehler = true;
            $oReturn->cFehler .= "-Wenn eine \"V-ID\" gesetzt ist darf der Werttyp nicht \"VaterAttribut\" sein.<br />";
        }
    }
    return $oReturn;
}
