{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kontaktformular_betreff.tpl, smarty template inc file

	admin page for JTL-Shop 3

	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#contactformSubject# cBeschreibung=#contanctformSubjectDesc#}
<div id="content">
	 <div class="container">
	 <form name="einstellen" method="post" action="kontaktformular.php">
	 <input type="hidden" name="{$session_name}" value="{$session_id}" />
	 <input type="hidden" name="kKontaktBetreff" value="{$Betreff->kKontaktBetreff}" />
	 <input type="hidden" name="betreff" value="1" />
	 <div class="settings">
	 <p><label for="cName">{#subject#}</label>
	 <input type="text" name="cName" id="cName"  value="{$Betreff->cName}" tabindex="1" /></p>
	 {foreach name=sprachen from=$sprachen item=sprache}
	 {assign var="cISO" value=$sprache->cISO}
	 <p><label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
	 <input type="text" name="cName_{$cISO}" id="cName_{$cISO}"  value="{$Betreffname[$cISO]}" tabindex="2" /></p>
	 {/foreach}  
	 <p><label for="cMail">{#mail#}</label>
	 <input type="text" name="cMail" id="cMail"  value="{$Betreff->cMail}" tabindex="3" /></p> 
	 <p><label for="cMail">{#restrictedToCustomerGroups#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#multipleChoice#}" title="{#multipleChoice#}" style="vertical-align:middle; cursor:help;" /></label>
	 <select name="cKundengruppen[]" multiple="multiple" id="cKundengruppen">
	 <option value="0" {if $gesetzteKundengruppen[0]}selected{/if}>{#allCustomerGroups#}</option>
	 {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
	 {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
	 <option value="{$kundengruppe->kKundengruppe}" {if $gesetzteKundengruppen[$kKundengruppe]}selected{/if}>{$kundengruppe->cName}</option>
	 {/foreach}
	 </select></p>
	 <p><label for="nSort">{#sortNo#}</label>
	 <input style="width:40px" type="text" name="nSort" id="nSort"  value="{$Betreff->nSort}" tabindex="4" /></p>  
	 </div>
	 <p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
	 </form>
	 </div>
</div>	