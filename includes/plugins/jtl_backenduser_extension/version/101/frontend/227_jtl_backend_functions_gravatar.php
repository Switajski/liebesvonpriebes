<?php
/**
 * HOOK_BACKEND_FUNCTIONS_GRAVATAR
 *
 * Dieses Plugin erweitert den Backendnutzer um sein Profilbild
 *
 * @package   jtl_backenduser_extension
 * @copyright JTL-Software-GmbH
 *
 * @global array $args_arr
 * @global Plugin $oPlugin
 */

$oAdminExt = $args_arr['AdminAccount'];
$url= Shop::DB()->select(
    'tadminloginattribut',
    'kAdminlogin',
    $oAdminExt->kAdminlogin,
    'cName',
    'useAvatarUpload',
    null,
    null,
    false,
    'cAttribValue'
);

$args_arr['url'] = !empty($url->cAttribValue) ? $url->cAttribValue : $args_arr['url'];
