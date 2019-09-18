{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: Links_neue_linkgruppe.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}

{assign var=cTitel value=#newLinkGroup#}
{if $Linkgruppe->kLinkgruppe > 0}
	 {assign var=cTitel value=#editLinkGroup#}
{/if}
{include file="tpl_inc/seite_header.tpl" cTitel=$cTitel}

{if isset($hinweis) && $hinweis|count_characters > 0}
    <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}
    <p class="box_error">{$fehler}</p>
{/if}

<div id="content">
	 <div class="container">
		  <form name="linkgruppe_erstellen" method="post" action="links.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="neu_linkgruppe" value="1" />
				<input type="hidden" name="kLinkgruppe" value="{$Linkgruppe->kLinkgruppe}" />
				<div class="settings">
					 <p>
						  <label for="cName">{#linkGruop#}</label>
						  <input type="text" name="cName" id="cName"{if isset($xPlausiVar_arr.cName)} class="fieldfillout"{/if} value="{if $xPostVar_arr.cName}{$xPostVar_arr.cName}{elseif isset($Linkgruppe->cName)}{$Linkgruppe->cName}{/if}" />
                          {if isset($xPlausiVar_arr.cName)}<font class="fillout">{#FillOut#}</font>{/if}
					 </p>  
					 <p>
						  <label for="cTemplatename">{#linkGruopTemplatename#}</label>
						  <input type="text" name="cTemplatename" id="cTemplatename"{if isset($xPlausiVar_arr.cTemplatename)} class="fieldfillout"{/if} value="{if $xPostVar_arr.cTemplatename}{$xPostVar_arr.cTemplatename}{elseif isset($Linkgruppe->cTemplatename)}{$Linkgruppe->cTemplatename}{/if}" />
                          {if isset($xPlausiVar_arr.cTemplatename)}<font class="fillout">{#FillOut#}</font>{/if}
					 </p> 
					 {foreach name=sprachen from=$sprachen item=sprache}
						  {assign var="cISO" value=$sprache->cISO}
						  <p>
								<label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
								<input type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{$Linkgruppenname[$cISO]}" />
						  </p>
					 {/foreach}   
				</div>            
				<div class="save_wrapper">
					 <input type="submit" value="{$cTitel}" class="button orange" />
				</div>
		  </form>
	 </div>
</div>