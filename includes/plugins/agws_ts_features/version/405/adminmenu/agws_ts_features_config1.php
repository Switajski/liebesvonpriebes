<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_config1.php
 * Project: agws_ts_features
 */


global $oPlugin;

include_once($oPlugin->cAdminmenuPfad . 'inc/agws_ts_features_predefine.php');
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Boxen.php");

$smarty = Shop::Smarty();

unset($_SESSION['ts_features_error_add']);

$smarty->assign('ts_message', '');
$smarty->assign('ts_message_class', '');

/** Initialisierung - Standardwerte für Trusted Shop CLASSIC - Einstellungen aus Core neutralisieren */
$ts_init_template_default = new stdClass();
$ts_init_template_default->cWert = "N";

Shop::DB()->updateRow("ttemplateeinstellungen", "cName", "show_trustbadge", $ts_init_template_default);

$sql = "SELECT cTSID FROM ttrustedshopszertifikat WHERE eType = 'CLASSIC'";
$ts_init_TSID_classic_arr = Shop::DB()->executeQuery($sql, 2);

if (count($ts_init_TSID_classic_arr) > 0) {
    foreach ($ts_init_TSID_classic_arr as $ts_init_TSID_classic) {
        Shop::DB()->deleteRow("ttrustedshopskundenbewertung", "cTSID", $ts_init_TSID_classic->cTSID, $echo = 0);
        Shop::DB()->deleteRow("ttrustedshopsstatistik", "cTSID", $ts_init_TSID_classic->cTSID, $echo = 0);
        Shop::DB()->deleteRow("ttrustedshopszertifikat", "cTSID", $ts_init_TSID_classic->cTSID, $echo = 0);
    }
}
/**/

//Shop::dbg($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ts_id']) && Shop::DB()->realEscape($_POST['ts_id']) != "" && isset($_POST['ts_id_is_add']) && (int)$_POST['ts_id_is_add'] == 1) {
        $ts_id_add = new stdClass();
        $ts_id_add->ts_id = Shop::DB()->realEscape($_POST['ts_id']);
        $ts_id_add->ts_sprache = Shop::DB()->realEscape($_POST['ts_sprache']);

        $queryResult = 0;
        if ($ts_id_add->ts_sprache != 0) {
            /** Pre-Define Badge-Code **/
            $ts_BadgeCode = file_get_contents($oPlugin->cAdminmenuPfad . "template/tpl_inc/inc_ts_BadgeCode_pre.tpl", true);
            $ts_BadgeCode = str_replace("##ts_id##", $ts_id_add->ts_id, $ts_BadgeCode);
            $ts_id_add->ts_BadgeCode = $ts_BadgeCode;

            /** Pre-Define ProduktBewertungRegister-Code **/
            $ts_ProduktBewertungRegisterCode = file_get_contents($oPlugin->cAdminmenuPfad . "template/tpl_inc/inc_ts_ProduktBewertungRegisterCode_pre.tpl", true);
            $ts_ProduktBewertungRegisterCode = str_replace("##ts_id##", $ts_id_add->ts_id, $ts_ProduktBewertungRegisterCode);
            $ts_id_add->ts_ProduktBewertungRegisterCode = $ts_ProduktBewertungRegisterCode;

            /** Pre-Define ProduktBewertungSterne-Code **/
            $ts_ProduktBewertungSterneCode = file_get_contents($oPlugin->cAdminmenuPfad . "template/tpl_inc/inc_ts_ProduktBewertungSterneCode_pre.tpl", true);
            $ts_ProduktBewertungSterneCode = str_replace("##ts_id##", $ts_id_add->ts_id, $ts_ProduktBewertungSterneCode);
            $ts_id_add->ts_ProduktBewertungSterneCode = $ts_ProduktBewertungSterneCode;

            /** Pre-Define ReviewSticker-Code **/
            $ts_ReviewStickerCode = file_get_contents($oPlugin->cAdminmenuPfad . "template/tpl_inc/inc_ts_ReviewStickerCode_pre.tpl", true);
            $ts_ReviewStickerCode = str_replace("##ts_id##", $ts_id_add->ts_id, $ts_ReviewStickerCode);
            $ts_id_add->ts_ReviewStickerCode = $ts_ReviewStickerCode;

            /** Pre-Define RichSnippets-Code **/
            $ts_RichSnippetsCode = file_get_contents($oPlugin->cAdminmenuPfad . "template/tpl_inc/inc_ts_RichSnippetsCode_pre.tpl", true);
            $ts_RichSnippetsCode = str_replace("##ts_id##", $ts_id_add->ts_id, $ts_RichSnippetsCode);
            $ts_id_add->ts_RichSnippetsCode = $ts_RichSnippetsCode;

            $queryResult = Shop::DB()->insertRow("xplugin_agws_ts_features_config", $ts_id_add);
        }

        if ($queryResult == 1) {
            $_SESSION['ts_features_error_add'] = "0";
            $smarty->assign('ts_message', 'Die ID: ' . StringHandler::filterXSS($_POST['ts_id']) . ' wurde angelegt!');
            $smarty->assign('ts_message_class', 'box_success alert alert-success');
        } else {
            $_SESSION['ts_features_error_add'] = "1";
            $js_redirect = '<script type="text/javascript">';
            $js_redirect .= 'window.location = "' . Shop::getURL() . '/admin/plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Konfiguration&ts_add_error=1' . '"';
            $js_redirect .= '</script>';
            echo $js_redirect;
        }
    }

    if (isset($_POST['ts_id']) && Shop::DB()->realEscape($_POST['ts_id']) != "" && isset($_POST['ts_id_is_delete']) && (int)$_POST['ts_id_is_delete'] == 1) {
        $queryResult = Shop::DB()->deleteRow("xplugin_agws_ts_features_config", "ts_id", Shop::DB()->realEscape($_POST['ts_id']));

        if ($queryResult == 1) {
            $smarty->assign('ts_message', 'Die ID: ' . Shop::DB()->realEscape($_POST['ts_id']) . ' wurde gelöscht!');
            $smarty->assign('ts_message_class', 'box_success alert alert-success');
        } else {
            $smarty->assign('ts_message', 'Es trat ein Fehler auf - ID konnte nicht gelöscht werden!');
            $smarty->assign('ts_message_class', 'box_error alert alert-danger');
        }
    }

    if (isset($_POST['ts_id']) && Shop::DB()->realEscape($_POST['ts_id']) != "" && isset($_POST['ts_id_options_cancel']) && (int)$_POST['ts_id_options_cancel'] == 1) {
        $smarty->assign('ts_message', 'Die Bearbeitung wurde abgebrochen - Änderungen wurden nicht gespeichert!');
        $smarty->assign('ts_message_class', 'box_info alert alert-warning');
    }

    if (isset($_POST['ts_id']) && Shop::DB()->realEscape($_POST['ts_id']) != "" && isset($_POST['ts_id_options_save']) && (int)$_POST['ts_id_options_save'] == 1) {
        $ts_id_edit = new stdClass();
        $ts_id_edit->ts_id = Shop::DB()->realEscape($_POST['ts_id']);
        $ts_id_edit->ts_sprache = (int)$_POST['ts_sprache'];
        $ts_id_edit->ts_BadgeCode = $_POST['ts_BadgeCode'];
        $ts_id_edit->ts_RatingWidgetAktiv = (int)$_POST['ts_RatingWidgetAktiv'];
        $ts_id_edit->ts_RatingWidgetPosition = (int)$_POST['ts_RatingWidgetPosition'];
        $ts_id_edit->ts_ReviewStickerAktiv = (int)$_POST['ts_ReviewStickerAktiv'];
        $ts_id_edit->ts_ReviewStickerPosition = (int)$_POST['ts_ReviewStickerPosition'];
        $ts_id_edit->ts_RichSnippetsKategorieseite = (int)$_POST['ts_RichSnippetsKategorieseite'];
        $ts_id_edit->ts_RichSnippetsArtikelseite = (int)$_POST['ts_RichSnippetsArtikelseite'];
        $ts_id_edit->ts_RichSnippetsStartseite = (int)$_POST['ts_RichSnippetsStartseite'];
        $ts_id_edit->ts_ReviewStickerCode = $_POST['ts_ReviewStickerCode'];
        //$ts_id_edit->bTS_ProductStickerShow = (int)$_POST['ts_ProductStickerShow'];
        //$ts_id_edit->iTS_ProductStickerArt = (int)$_POST['ts_ProductStickerArt'];
        $ts_id_edit->ts_modus = Shop::DB()->realEscape($_POST['ts_modus']);
        $ts_id_edit->ts_BadgeVariante = Shop::DB()->realEscape($_POST['ts_BadgeVariante']);
        $ts_id_edit->ts_BadgeYOffset = Shop::DB()->realEscape($_POST['ts_BadgeYOffset']);
        $ts_id_edit->ts_ProduktBewertungAktiv = Shop::DB()->realEscape($_POST['ts_ProduktBewertungAktiv']);
        $ts_id_edit->ts_ProduktBewertungRegisterAktiv = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterAktiv']);
        $ts_id_edit->ts_ProduktBewertungRegisterNameTab = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterNameTab']);
        $ts_id_edit->ts_ProduktBewertungRegisterRahmenfarbe = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterRahmenfarbe']);
        $ts_id_edit->ts_ProduktBewertungRegisterSternfarbe = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterSternfarbe']);
        $ts_id_edit->ts_ProduktBewertungRegisterSterngroesse = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterSterngroesse']);
        //$ts_id_edit->ts_ProduktBewertungRegisterLeer = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterLeer']);
        $ts_id_edit->ts_ProduktBewertungRegisterCode = $_POST['ts_ProduktBewertungRegisterCode'];
        $ts_id_edit->ts_ProduktBewertungRegisterSelector = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterSelector']);
        //$ts_id_edit->ts_ProduktBewertungRegisterMethode = Shop::DB()->realEscape($_POST['ts_ProduktBewertungRegisterMethode']);
        $ts_id_edit->ts_ProduktBewertungSterneAktiv = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneAktiv']);
        $ts_id_edit->ts_ProduktBewertungSterneSternfarbe = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneSternfarbe']);
        $ts_id_edit->ts_ProduktBewertungSterneSterngroesse = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneSterngroesse']);
        $ts_id_edit->ts_ProduktBewertungSterneSchriftgroesse = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneSchriftgroesse']);
        $ts_id_edit->ts_ProduktBewertungSterneLeer = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneLeer']);
        $ts_id_edit->ts_ProduktBewertungSterneCode = $_POST['ts_ProduktBewertungSterneCode'];
        $ts_id_edit->ts_ProduktBewertungSterneSelector = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneSelector']);
        //$ts_id_edit->ts_ProduktBewertungSterneMethode = Shop::DB()->realEscape($_POST['ts_ProduktBewertungSterneMethode']);
        $ts_id_edit->ts_ReviewStickerModus = Shop::DB()->realEscape($_POST['ts_ReviewStickerModus']);
        $ts_id_edit->ts_ReviewStickerSchriftart = Shop::DB()->realEscape($_POST['ts_ReviewStickerSchriftart']);
        $ts_id_edit->ts_ReviewStickerBewertungsanzahl = Shop::DB()->realEscape($_POST['ts_ReviewStickerBewertungsanzahl']);
        $ts_id_edit->ts_ReviewStickerMindestnote = Shop::DB()->realEscape($_POST['ts_ReviewStickerMindestnote']);
        $ts_id_edit->ts_ReviewStickerHintergrundfarbe = Shop::DB()->realEscape($_POST['ts_ReviewStickerHintergrundfarbe']);
        $ts_id_edit->ts_RichSnippetsAktiv = Shop::DB()->realEscape($_POST['ts_RichSnippetsAktiv']);
        $ts_id_edit->ts_RichSnippetsCode = $_POST['ts_RichSnippetsCode'];

        $oTSFeature_Box = new Boxen();

        /** Boxensteuerung Review-Sticker **/
        $ts_check_box = Shop::DB()->selectSingleRow('tboxen', 'cTitel', 'Trusted Shops - Reviews');
        $ts_check_box_vorlage = Shop::DB()->selectSingleRow('tboxvorlage', 'cName', 'Trusted Shops - Reviews');

        if (count($ts_check_box) == 1)
            $oTSFeature_Box->loescheBox($ts_check_box->kBox);

        if ((int)$ts_id_edit->ts_ReviewStickerPosition == 1 && (int)$ts_id_edit->ts_ReviewStickerAktiv == 1)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage, 0, 'left');

        if ((int)$ts_id_edit->ts_ReviewStickerPosition == 2 && (int)$ts_id_edit->ts_ReviewStickerAktiv == 1)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage, 0, 'right');
        /**/

        /** Boxensteuerung Rating-Widget (deprecated)**/
        $ts_check_box = Shop::DB()->selectSingleRow('tboxen', 'cTitel', 'Trusted Shops - Rating');
        $ts_check_box_vorlage = Shop::DB()->selectSingleRow('tboxvorlage', 'cName', 'Trusted Shops - Rating');

        if (count($ts_check_box) == 1)
            $oTSFeature_Box->loescheBox($ts_check_box->kBox);

        if ((int)$ts_id_edit->ts_RatingWidgetPosition == 1 && (int)$ts_id_edit->ts_RatingWidgetAktiv == 1)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage, 0, 'left');

        if ((int)$ts_id_edit->ts_RatingWidgetPosition == 2 && (int)$ts_id_edit->ts_RatingWidgetAktiv == 1)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage, 0, 'right');

        $queryResult = Shop::DB()->updateRow("xplugin_agws_ts_features_config", "ts_id", $ts_id_edit->ts_id, $ts_id_edit);

        if ($queryResult >= 0) {
            $smarty->assign('ts_message', 'Die Konfiguration wurden gespeichert!');
            $smarty->assign('ts_message_class', 'box_success alert alert-success');
        } else {
            $smarty->assign('ts_message', 'Es trat ein Fehler auf - Konfiguration konnte nicht gespeichert werden!');
            $smarty->assign('ts_message_class', 'box_error alert alert-danger');
        }
        /**/
    }
}

if (isset($_GET['ts_add_error']) && (int)$_GET['ts_add_error'] == 1) {
    $smarty->assign('ts_message', 'Es trat ein Fehler auf - ID ist bereits installiert oder es wurde keine Shop-Sprache ausgewählt!');
    $smarty->assign('ts_message_class', 'box_error alert alert-danger');
}

$sql = "SELECT tsprache.cNameDeutsch,xplugin_agws_ts_features_config.*
          FROM xplugin_agws_ts_features_config
          LEFT JOIN tsprache ON tsprache.kSprache = xplugin_agws_ts_features_config.ts_sprache";
$ts_id_all_arr = Shop::DB()->executeQuery($sql, 2);

$sql = "SELECT * FROM tsprache WHERE kSprache NOT IN (SELECT ts_sprache FROM xplugin_agws_ts_features_config)";
$ts_id_sprache_free_arr = Shop::DB()->executeQuery($sql, 2);

$smarty->assign('ts_css_class', "ts_shop4");

$smarty->assign('ts_id_shopsprachen_free', $ts_id_sprache_free_arr);
$smarty->assign('ts_id_all_arr', $ts_id_all_arr);
$smarty->assign('ts_id_add_form_action', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Erweiterte%20Konfiguration');
$smarty->assign('ts_id_edit_form_action', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Erweiterte%20Konfiguration');
$smarty->assign('ts_id_delete_form_action', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Konfiguration');
$smarty->assign('ts_id_cancel_form_action', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Konfiguration');
$smarty->assign('ts_id_save_form_action', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Erweiterte%20Konfiguration');
$smarty->assign('ts_id_save2_form_action', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&cPluginTab=Konfiguration');
$smarty->assign("URL_ADMINMENU", Shop::getURL() . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);

$smarty->display($oPlugin->cAdminmenuPfad . "template/agws_ts_features_config1.tpl");