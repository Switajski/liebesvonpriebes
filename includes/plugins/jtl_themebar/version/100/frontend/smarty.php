<?php
/** @global Plugin $oPlugin */

require_once __DIR__ . '/includes/themebar_inc.php';
Shop::Smarty()->assign('oTheme_arr', getThemes())
    ->assign('oPlugin', $oPlugin)
    ->assign('getDefaultTheme', getDefaultTemplate());