<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_config1.php
 * Project: agws_ts_features
 */


global $oPlugin;

include_once($oPlugin->cAdminmenuPfad.'inc/agws_ts_features_predefine.php');
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Boxen.php");
require_once $oPlugin->cAdminmenuPfad . 'inc/class.agws_plugin_ts_feature.helper.php';

$helper = agwsPluginHelperTS::getInstance($oPlugin);

if ($helper->isShop4()) {
    $smarty = Shop::Smarty();
} else {
    global $smarty;
}

unset($_SESSION['ts_features_error_add']);

$smarty->assign('ts_message','');
$smarty->assign('ts_message_class','');

/** Initialisierung - Standardwerte für Trusted Shop CLASSIC - Einstellungen aus Core neutralisieren */
$ts_init_template_default = new stdClass();
$ts_init_template_default->cWert = "N";

($helper->isShop4()) ?
    Shop::DB()->updateRow("ttemplateeinstellungen", "cName", "show_trustbadge", $ts_init_template_default):
    $GLOBALS["DB"]->updateRow("ttemplateeinstellungen", "cName", "show_trustbadge", $ts_init_template_default);

$sql = "SELECT cTSID FROM ttrustedshopszertifikat WHERE eType = 'CLASSIC'";
($helper->isShop4())?
    $ts_init_TSID_classic_arr = Shop::DB()->executeQuery($sql,2):
    $ts_init_TSID_classic_arr = $GLOBALS["DB"]->executeQuery($sql,2);

if (count($ts_init_TSID_classic_arr)>0){
    foreach($ts_init_TSID_classic_arr as $ts_init_TSID_classic) {
        if ($helper->isShop4()) {
            Shop::DB()->deleteRow("ttrustedshopskundenbewertung", "cTSID", $ts_init_TSID_classic->cTSID, $echo = 0);
            Shop::DB()->deleteRow("ttrustedshopsstatistik", "cTSID", $ts_init_TSID_classic->cTSID, $echo = 0);
            Shop::DB()->deleteRow("ttrustedshopszertifikat", "cTSID", $ts_init_TSID_classic->cTSID, $echo = 0);
        } else {
            $GLOBALS["DB"]->deleteRow ("ttrustedshopskundenbewertung", "cTSID", $ts_init_TSID_classic->cTSID, $echo=0);
            $GLOBALS["DB"]->deleteRow ("ttrustedshopsstatistik", "cTSID", $ts_init_TSID_classic->cTSID, $echo=0);
            $GLOBALS["DB"]->deleteRow ("ttrustedshopszertifikat", "cTSID", $ts_init_TSID_classic->cTSID, $echo=0);
        }
    }
}
/**/

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if (isset($_POST['ts_id']) && $helper->real__Escape($_POST['ts_id']) != "" && isset($_POST['ts_id_is_add']) && (int) $_POST['ts_id_is_add']==1)
	{
		$ts_id_add = new stdClass();
		$ts_id_add->cTS_ID = $helper->real__Escape($_POST['ts_id']);
        $ts_id_add->iTS_Sprache = $helper->real__Escape($_POST['ts_sprache']);
        $ts_id_add->cTS_BadgeCode = "";

        $queryResult = 0;
        if ($ts_id_add->iTS_Sprache != 0)
        {
            $smarty->assign('ts_id_review_pre',$helper->filter__XSS($_POST['ts_id']));
            $ts_review_pre = $smarty->fetch($oPlugin->cAdminmenuPfad . "template/tpl_inc/inc_ts_features_review_pre.tpl");
            $ts_id_add->cTS_ReviewStickerCode = $ts_review_pre;

            ($helper->isShop4()) ?
                $queryResult = Shop::DB()->insertRow("xplugin_agws_ts_features_config", $ts_id_add):
                $queryResult = $GLOBALS["DB"]->insertRow("xplugin_agws_ts_features_config", $ts_id_add);
        }

        if($queryResult==1)
        {
            $_SESSION['ts_features_error_add']="0";
            $smarty->assign('ts_message','Die ID: '.$helper->filter__XSS($_POST['ts_id']).' wurde angelegt!');
            $smarty->assign('ts_message_class','box_success alert alert-success');
        } else {
            $_SESSION['ts_features_error_add']="1";
            $js_redirect = '<script type="text/javascript">';
            $js_redirect .= 'window.location = "' . $helper->gibShop__URL() .'/admin/plugin.php?kPlugin='.$oPlugin->kPlugin.'&cPluginTab=Konfiguration&ts_add_error=1' . '"';
            $js_redirect .= '</script>';
            echo $js_redirect;
        }
	}

    if (isset($_POST['ts_id']) && $helper->real__Escape($_POST['ts_id']) != "" && isset($_POST['ts_id_is_delete']) && (int) $_POST['ts_id_is_delete']==1)
    {
        ($helper->isShop4()) ?
            $queryResult = Shop::DB()->deleteRow("xplugin_agws_ts_features_config", "cTS_ID",$helper->real__Escape($_POST['ts_id'])):
            $queryResult = $GLOBALS["DB"]->deleteRow("xplugin_agws_ts_features_config", "cTS_ID",$helper->real__Escape($_POST['ts_id']));

        if($queryResult==1)
        {
            $smarty->assign('ts_message','Die ID: '.$helper->real__Escape($_POST['ts_id']).' wurde gelöscht!');
            $smarty->assign('ts_message_class','box_success alert alert-success');
        } else {
            $smarty->assign('ts_message','Es trat ein Fehler auf - ID konnte nicht gelöscht werden!');
            $smarty->assign('ts_message_class','box_error alert alert-danger');
        }
    }

    if (isset($_POST['ts_id']) && $helper->real__Escape($_POST['ts_id']) != "" && isset($_POST['ts_id_options_cancel']) && (int) $_POST['ts_id_options_cancel']==1)
    {
        $smarty->assign('ts_message','Die Bearbeitung wurde abgebrochen - Änderungen wurden nicht gespeichert!');
        $smarty->assign('ts_message_class','box_info alert alert-info');
    }

    if (isset($_POST['ts_id']) && $helper->real__Escape($_POST['ts_id']) != "" && isset($_POST['ts_id_options_save']) && (int) $_POST['ts_id_options_save']==1)
    {
        $ts_id_edit = new stdClass();
        $ts_id_edit->cTS_ID = $helper->real__Escape($_POST['ts_id']);
        $ts_id_edit->iTS_Sprache = (int) $_POST['ts_sprache'];
        $ts_id_edit->cTS_BadgeCode = $_POST['ts_BadgeCode'];
        $ts_id_edit->bTS_RatingWidgetShow = (int) $_POST['ts_RatingWidgetShow'];
        $ts_id_edit->iTS_RatingWidgetPosition = (int) $_POST['ts_RatingWidgetPosition'];
        $ts_id_edit->bTS_ReviewStickerShow = (int) $_POST['ts_ReviewStickerShow'];
        $ts_id_edit->iTS_ReviewStickerPosition = (int) $_POST['ts_ReviewStickerPosition'];
        $ts_id_edit->bTS_RichSnippetsCategory = (int) $_POST['ts_RichSnippetsCategory'];
        $ts_id_edit->bTS_RichSnippetsProduct = (int) $_POST['ts_RichSnippetsProduct'];
        $ts_id_edit->bTS_RichSnippetsMain = (int) $_POST['ts_RichSnippetsMain'];
        $ts_id_edit->cTS_ReviewStickerCode = $_POST['ts_ReviewStickerCode'];
        $ts_id_edit->bTS_ProductStickerShow = (int) $_POST['ts_ProductStickerShow'];
        $ts_id_edit->iTS_ProductStickerArt = (int) $_POST['ts_ProductStickerArt'];

        $oTSFeature_Box = new Boxen();

        /** Boxensteuerung Review-Sticker **/
        ($helper->isShop4())?
            $ts_check_box = Shop::DB()->selectSingleRow('tboxen', 'cTitel', 'Trusted Shops - Reviews'):
            $ts_check_box = $GLOBALS["DB"]->selectSingleRow('tboxen', 'cTitel', 'Trusted Shops - Reviews');

        ($helper->isShop4() === true)?
            $ts_check_box_vorlage = Shop::DB()->selectSingleRow('tboxvorlage', 'cName', 'Trusted Shops - Reviews'):
            $ts_check_box_vorlage = $GLOBALS["DB"]->selectSingleRow('tboxvorlage', 'cName', 'Trusted Shops - Reviews');

        if(count($ts_check_box) == 1)
                $oTSFeature_Box->loescheBox($ts_check_box->kBox);

        if((int)$ts_id_edit->iTS_ReviewStickerPosition==1)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage,0,'left');

        if((int)$ts_id_edit->iTS_ReviewStickerPosition==2)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage,0,'right');
        /**/

        /** Boxensteuerung Rating-Widget **/
        ($helper->isShop4()) ?
            $ts_check_box = Shop::DB()->selectSingleRow('tboxen', 'cTitel', 'Trusted Shops - Rating'):
            $ts_check_box = $GLOBALS["DB"]->selectSingleRow('tboxen', 'cTitel', 'Trusted Shops - Rating');

        ($helper->isShop4()) ?
            $ts_check_box_vorlage = Shop::DB()->selectSingleRow('tboxvorlage', 'cName', 'Trusted Shops - Rating'):
            $ts_check_box_vorlage = $GLOBALS["DB"]->selectSingleRow('tboxvorlage', 'cName', 'Trusted Shops - Rating');

        if(count($ts_check_box) == 1)
            $oTSFeature_Box->loescheBox($ts_check_box->kBox);

        if((int)$ts_id_edit->iTS_RatingWidgetPosition==1)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage,0,'left');

        if((int)$ts_id_edit->iTS_RatingWidgetPosition==2)
            $oTSFeature_Box->setzeBox($ts_check_box_vorlage->kBoxvorlage,0,'right');

        ($helper->isShop4()) ?
            $queryResult = Shop::DB()->updateRow("xplugin_agws_ts_features_config", "cTS_ID", $ts_id_edit->cTS_ID, $ts_id_edit):
            $queryResult = $GLOBALS["DB"]->updateRow("xplugin_agws_ts_features_config", "cTS_ID", $ts_id_edit->cTS_ID, $ts_id_edit);

        if($queryResult >= 0)
        {
            $smarty->assign('ts_message','Die Konfiguration wurden gespeichert!');
            $smarty->assign('ts_message_class','box_success alert alert-success');
        } else {
            $smarty->assign('ts_message','Es trat ein Fehler auf - Konfiguration konnte nicht gespeichert werden!');
            $smarty->assign('ts_message_class','box_error alert alert-danger');
        }
        /**/
    }
}

if (isset($_GET['ts_add_error']) && (int) $_GET['ts_add_error'] ==1)
{
    $smarty->assign('ts_message','Es trat ein Fehler auf - ID ist bereits installiert oder es wurde keine Shop-Sprache ausgewählt!');
    $smarty->assign('ts_message_class','box_error alert alert-danger');
}

$sql="SELECT tsprache.cNameDeutsch,xplugin_agws_ts_features_config.*
          FROM xplugin_agws_ts_features_config
          LEFT JOIN tsprache ON tsprache.kSprache = xplugin_agws_ts_features_config.iTS_Sprache";
($helper->isShop4()) ?
    $ts_id_all_arr = Shop::DB()->executeQuery($sql,2):
    $ts_id_all_arr = $GLOBALS["DB"]->executeQuery($sql,2);

$sql="SELECT * FROM tsprache WHERE kSprache NOT IN (SELECT iTS_Sprache FROM xplugin_agws_ts_features_config)";
($helper->isShop4() === true)?
    $ts_id_sprache_free_arr =  Shop::DB()->executeQuery($sql,2):
    $ts_id_sprache_free_arr = $GLOBALS["DB"]->executeQuery($sql,2);

($helper->isShop4() === true)?
    $smarty->assign('ts_css_class',"ts_shop4"):
    $smarty->assign('ts_css_class',"ts_shop3");

$smarty->assign('ts_id_shopsprachen_free',$ts_id_sprache_free_arr);
$smarty->assign('ts_id_all_arr',$ts_id_all_arr);
$smarty->assign('ts_id_add_form_action','plugin.php?kPlugin='.$oPlugin->kPlugin.'&cPluginTab=Erweiterte%20Konfiguration');
$smarty->assign('ts_id_edit_form_action','plugin.php?kPlugin='.$oPlugin->kPlugin.'&cPluginTab=Erweiterte%20Konfiguration');
$smarty->assign('ts_id_delete_form_action','plugin.php?kPlugin='.$oPlugin->kPlugin.'&cPluginTab=Konfiguration');
$smarty->assign('ts_id_cancel_form_action','plugin.php?kPlugin='.$oPlugin->kPlugin.'&cPluginTab=Konfiguration');
$smarty->assign('ts_id_save_form_action','plugin.php?kPlugin='.$oPlugin->kPlugin.'&cPluginTab=Konfiguration');
$smarty->assign("URL_ADMINMENU", $helper->gibShop__URL() . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);

$smarty->display($oPlugin->cAdminmenuPfad . "template/agws_ts_features_config1.tpl");