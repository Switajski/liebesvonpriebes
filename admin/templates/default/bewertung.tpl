{*
-------------------------------------------------------------------------------
	File: bewertung.tpl, smarty template inc file

	Vote system admin template page for JTL-Shop 3
	Admin

	Author: Daniel B�hmer daniel.boehmer@jtl-software.de
	http://www.jtl-software.de
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="bewertungen"}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>
<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{if $step == "bewertung_uebersicht"}
	{include file='tpl_inc/bewertung_uebersicht.tpl'}
{elseif $step == "bewertung_editieren"}
	{include file='tpl_inc/bewertung_editieren.tpl'}
{/if}	       
        

{include file='tpl_inc/footer.tpl'}
