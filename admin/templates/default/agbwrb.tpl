{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="agbwrb"}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>

{if $step == "agbwrb_uebersicht"}
	{include file='tpl_inc/agbwrb_uebersicht.tpl'}
{elseif $step == "agbwrb_editieren"}
	{include file='tpl_inc/agbwrb_editieren.tpl'}
{/if}
			

{include file='tpl_inc/footer.tpl'}