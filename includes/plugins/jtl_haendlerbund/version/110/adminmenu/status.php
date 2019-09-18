<?php
global $oPlugin, $smarty;
$cHB_URL = 'https://www.hb-intern.de/www/hbm/api/live_rechtstexte.htm';

if ($oPlugin->oPluginEinstellungAssoc_arr['hb_nutzen'] === 'Y' && strlen($oPlugin->oPluginEinstellungAssoc_arr['hb_token']) > 0) {
    if (isset($_GET['hb_activate'])) {
        if ($_GET['hb_activate'] === 'all') {
            Shop::DB()->query("UPDATE xplugin_" . $oPlugin->cPluginID . "_tupdate SET nAktiv=1, nVersuch=0", 3);
        } else {
            $kUpdate = (int)$_GET['hb_activate'];
            if ($kUpdate > 0) {
                Shop::DB()->query("UPDATE xplugin_" . $oPlugin->cPluginID . "_tupdate SET nAktiv=1, nVersuch=0 WHERE kUpdate={$kUpdate}", 3);
            }
        }
    }
    $cRechtstext_arr = [
        '12766C46A8A' => 'AGB',
        '12766C53647' => 'WRB',
        '1293C20B491' => 'Impressum',
        '12766C5E204' => 'Datenschutz',
        '134CBB4D101' => 'Batteriegesetz-Hinweise'
    ];
    $oTexte_arr      = Shop::DB()->query("SELECT * FROM xplugin_" . $oPlugin->cPluginID . "_tupdate", 2);
    foreach ($oTexte_arr as $oText) {
        $cParams             = http_build_query(array(
            'APIkey'      => '1IqJF0ap6GdDNF7HKzhFyciibdml8t4v',
            'AccessToken' => $oPlugin->oPluginEinstellungAssoc_arr['hb_token'],
            'mode'        => ($oText->cFormat === 'html') ? 'classes' : 'plain',
            'did'         => $oText->cType
        ), '', '&');
        $oText->cURL         = $cHB_URL . '?' . $cParams;
        $oText->cActivateUrl = "plugin.php?kPlugin={$oPlugin->kPlugin}&hb_activate={$oText->kUpdate}&cPluginTab=Status";
    }
    $smarty->assign('oTexte_arr', $oTexte_arr)
           ->assign('cRechtstext_arr', $cRechtstext_arr)
           ->assign('cUrlActivateAll', "plugin.php?kPlugin={$oPlugin->kPlugin}&hb_activate=all&cPluginTab=Status")
           ->display($oPlugin->cAdminmenuPfad . 'templates/status.tpl');
} else {
    echo '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' .
        'Bitte aktivieren Sie dieses Plugin im Reiter "Einstellungen" und tragen dort auch Ihren Token ein.</div>';
}
