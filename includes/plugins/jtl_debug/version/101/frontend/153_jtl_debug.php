<?php
/**
 * HOOK_MAILTOOLS_SENDEMAIL_ENDE
 *
 * @package     jtl_debug
 * @createdAt   10.10.2016
 * @author      Felix Moche <felix.moche@jtl-software.com>
 *
 * @var JTLSmarty $mailSmarty
 * @global array  $args_arr
 * @global Plugin $oPlugin
 */

if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_mail_smarty_vars_text'] === 'Y' ||
    $oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_mail_smarty_vars_html'] === 'Y'
) {
    $mailSmarty = $args_arr['mailsmarty'];
    if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_mail_smarty_vars_html'] === 'Y') {
        $html = phpQuery::newDocumentHTML($args_arr['mail']->bodyHtml, JTL_CHARSET);
        pq('body')->append('<h3>Variablen:</h3><pre>' . print_r($mailSmarty->getTemplateVars(), true) . '</pre>');
        $args_arr['mail']->bodyHtml = utf8_decode($html->htmlOuter());
    }
    if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_mail_smarty_vars_text'] === 'Y') {
        $args_arr['mail']->bodyText = $args_arr['mail']->bodyText . "\nVariablen: \n" . print_r($mailSmarty->getTemplateVars(), true);
    }
}
