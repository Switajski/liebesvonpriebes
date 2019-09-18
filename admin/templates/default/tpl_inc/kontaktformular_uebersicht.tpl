{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kontaktformular_uebersicht.tpl, smarty template inc file

	admin page for JTL-Shop 3

	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#configureContactform# cBeschreibung=#contanctformDesc# cDokuURL=#cURL#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
     {if isset($error) && $error|count_characters > 0}            
          <p class="box_error">{$error}</p>
     {/if}
  
	 <div class="tabber">
		  <div class="tabbertab tabbertabdefault">
		  <form name="einstellen" method="post" action="kontaktformular.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="einstellungen" value="1" />
		  <div class="settings">
		  {foreach name=conf from=$Conf item=cnf}
		  {if $cnf->cConf=="Y"}
		  <p><label for="{$cnf->cWertName}">{$cnf->cName} {if $cnf->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$cnf->cBeschreibung}" title="{$cnf->cBeschreibung}" style="vertical-align:middle; cursor:help;" />{/if}</label>
		  {if $cnf->cInputTyp=="selectbox"}
		  <select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="combo"> 
		  {foreach name=selectfor from=$cnf->ConfWerte item=wert}
		  <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
		  {/foreach}
		  </select> 
		  {else}
		  <input type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}"  value="{$cnf->gesetzterWert}" tabindex="1" /></p>
		  {/if}
		  {else}
		  {if $cnf->cName}<h3 style="text-align:center;">{$cnf->cName}</h3>{/if}
		  {/if}
		  {/foreach}
		  </div>
		  <p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
		  </form>
		  </div>	
		  
		  <div class="tabbertab">
			  <h2>{#subjects#}</h2>
			  <p class="box_info">{#contanctformSubjectDesc#}</p>

			  <table class="list">
			  <thead>
			  <tr>
			  <th class="tleft">{#subject#}</th>
			  <th class="tleft">{#mail#}</th>
			  <th>{#custgrp#}</th>
			  <th>{#delete#}</th>
			  <th>{#modify#}</th>
			  </tr>
			  </thead>
			  <tbody>
			  {foreach name=betreffs from=$Betreffs item=Betreff}
			  <tr>
			  <td class="TD1"><a href="kontaktformular.php?{$SID}&kKontaktBetreff={$Betreff->kKontaktBetreff}">{$Betreff->cName}</a></td>
			  <td class="TD2">{$Betreff->cMail}</td>
			  <td class="tcenter">{$Betreff->Kundengruppen}</td>
			  <td class="tcenter"><a href="kontaktformular.php?{$SID}&del={$Betreff->kKontaktBetreff}" class="button remove">{#delete#}</a></td>
			  <td class="tcenter"><a href="kontaktformular.php?{$SID}&kKontaktBetreff={$Betreff->kKontaktBetreff}" class="button edit">{#modify#}</a></td>
			  </tr>
			  {/foreach}
			  </tbody>
			  </table>
			  <p class="submit"><a class="button orange" href="kontaktformular.php?{$SID}&neu=1">{#newSubject#}</a></p>
		  </div>
		  
		  <div class="tabbertab">
			  <h2>Inhalte</h2>
			  <form name="einstellen" method="post" action="kontaktformular.php">
			  <input type="hidden" name="{$session_name}" value="{$session_id}" />
			  <input type="hidden" name="content" value="1" />
			  <div class="settings">
			  {foreach name=sprachen from=$sprachen item=sprache}
			  {assign var="cISOcat" value=$sprache->cISO|cat:"_titel"}
			  {assign var="cISO" value=$sprache->cISO}
			  <p><label for="cTitle_{$cISO}">{#title#} ({$sprache->cNameDeutsch})</label>
			  <input type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}"  value="{$Content[$cISOcat]}" tabindex="1" /></p>
			  {/foreach}
			  {foreach name=sprachen from=$sprachen item=sprache}
			  {assign var="cISOcat" value=$sprache->cISO|cat:"_oben"}
			  {assign var="cISO" value=$sprache->cISO}
			  <div class="category">{#topContent#} ({$sprache->cNameDeutsch})</div>
			  <textarea class="ckeditor" name="cContentTop_{$cISO}" id="cContentTop_{$cISO}">{$Content[$cISOcat]}</textarea></p>
			  {/foreach}
			  {foreach name=sprachen from=$sprachen item=sprache}
			  {assign var="cISOcat" value=$sprache->cISO|cat:"_unten"}
			  {assign var="cISO" value=$sprache->cISO}
			  <div class="category">{#bottomContent#} ({$sprache->cNameDeutsch})</div>
			  <textarea class="ckeditor" name="cContentBottom_{$cISO}" id="cContentBottom_{$cISO}">{$Content[$cISOcat]}</textarea></p>
			  {/foreach}
			  </div>
			  <p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
			  </form>
		  </div>
	  </div>
  </div>
</div>