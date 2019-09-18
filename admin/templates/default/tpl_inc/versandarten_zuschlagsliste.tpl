{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: versandarten_zuschlagliste.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{assign var=isleListFor value=#isleListFor#}
{assign var=cVersandartName value=$Versandart->cName}
{assign var=cLandName value=$Land->cDeutsch}
{assign var=cLandISO value=$Land->cISO}

{include file="tpl_inc/seite_header.tpl" cTitel="`$isleListFor` `$cVersandartName`, `$cLandName` (`$cLandISO`)" cBeschreibung=#isleListsDesc#}
<div id="content">
 
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <div class="box_success">{$hinweis}</div>
	 {/if}
	 
	 {foreach name=zuschlaege from=$Zuschlaege item=zuschlag}
	 <div class="container">
		  <div class="category">{#isleList#}: {$zuschlag->cName}</div>
		  <table class="list">
				<tbody>
					 {foreach name=sprachen from=$sprachen item=sprache}
					 {assign var="cISO" value=$sprache->cISO}
					 <tr>
						  <td width="35%">{#showedName#} ({$sprache->cNameDeutsch})</td>
						  <td>{$zuschlag->angezeigterName[$cISO]}</td>
					 </tr>
					 {/foreach}
					 <tr>
						  <td width="35%">{#additionalFee#}</td>
						  <td>{getCurrencyConversionSmarty fPreisBrutto=$zuschlag->fZuschlag bSteuer=false}</td>
					 </tr>
					 <tr>
						  <td width="35%">{#plz#}</td>
						  <td>
								{foreach name=plz from=$zuschlag->zuschlagplz item=plz}
									 <p>
									 {if $plz->cPLZ}{$plz->cPLZ}{elseif $plz->cPLZAb}{$plz->cPLZAb} - {$plz->cPLZBis}{/if}
									 {if $plz->cPLZ || $plz->cPLZAb}
										  <a href="versandarten.php?{$SID}&delplz={$plz->kVersandzuschlagPlz}&kVersandart={$Versandart->kVersandart}&cISO={$Land->cISO}" class="button plain remove">{#delete#}</a>
									 {/if}
									 </p>
								{/foreach}
						  </td>
					 </tr>
					 <tr>
						  <td>&nbsp;</td>
						  <td>
								<form name="zuschlagplz_neu_{$zuschlag->kVersandzuschlag}" method="post" action="versandarten.php">
									 <input type="hidden" name="{$session_name}" value="{$session_id}" />										
									 <input type="hidden" name="neueZuschlagPLZ" value="1" />
									 <input type="hidden" name="kVersandart" value="{$Versandart->kVersandart}" />
									 <input type="hidden" name="cISO" value="{$Land->cISO}" />
									 <input type="hidden" name="kVersandzuschlag" value="{$zuschlag->kVersandzuschlag}" />
									 {#plz#} <input type="text" name="cPLZ" class="zipcode" /> {#orPlzRange#} <input type="text" name="cPLZAb" class="zipcode" /> - <input type="text" name="cPLZBis" class="zipcode" /> <input type="submit" value="{#add#}" class="button plain add" />
								</form>
						  </td>
					 </tr>
				</tbody>
				<tfoot class="light">
					 <td colspan="2">
						  <a href="versandarten.php?{$SID}&delzus={$zuschlag->kVersandzuschlag}&kVersandart={$Versandart->kVersandart}&cISO={$Land->cISO}" class="button remove">{#additionalFeeDelete#}</a>
						  <a href="versandarten.php?{$SID}&editzus={$zuschlag->kVersandzuschlag}&kVersandart={$Versandart->kVersandart}&cISO={$Land->cISO}" class="button edit">{#additionalFeeEdit#}</a>
					 </td>
				</tfoot>
		  </table>
	 </div>
	 {/foreach}

	 <div class="settings container">
		  <div class="category">{#createNewList#}</div>
				<form name="zuschlag_neu" method="post" action="versandarten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="neuerZuschlag" value="1" />
				{if isset($oVersandzuschlag->kVersandart) && $oVersandzuschlag->kVersandart > 0}
				<input type="hidden" name="kVersandart" value="{$oVersandzuschlag->kVersandart}" />	
				{else}
				<input type="hidden" name="kVersandart" value="{$Versandart->kVersandart}" />
				{/if}
				<input type="hidden" name="cISO" value="{$Land->cISO}" />
				{if isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0}
				<input type="hidden" name="kVersandzuschlag" value="{$oVersandzuschlag->kVersandzuschlag}" />	   
				{/if}				
				<p><label for="cName">{#isleList#}</label>
				<input type="text" id="cName" name="cName" value="{if isset($oVersandzuschlag->cName)}{$oVersandzuschlag->cName}{/if}"  tabindex="1" /></p>
				{foreach name=sprachen from=$sprachen item=sprache}
				{assign var="cISO" value=$sprache->cISO}
				<p><label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
				<input type="text" id="cName_{$cISO}" name="cName_{$cISO}" value="{if isset($oVersandzuschlag->oVersandzuschlagSprache_arr.$cISO->cName)}{$oVersandzuschlag->oVersandzuschlagSprache_arr.$cISO->cName}{/if}"  tabindex="2" /></p>
				{/foreach}
				<p><label for="fZuschlag">{#additionalFee#} ({#amount#})</label>
				<input type="text" id="fZuschlag" name="fZuschlag" value="{if isset($oVersandzuschlag->fZuschlag)}{$oVersandzuschlag->fZuschlag}{/if}" class="price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxzuschlag', this)" /> <span id="ajaxzuschlag"></span>*}
				</div>
				<p class="submit">
					 <input type="submit" value="{if isset($oVersandzuschlag->kVersandart) && $oVersandzuschlag->kVersandart > 0}{#createEditList#}{else}{#createNewList#}{/if}" class="button orange" /> 
				</p>
		  </form>
	 </div>
</div>

{if isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0}
<script type="text/javascript">
	xajax_getCurrencyConversionAjax(0, document.getElementById('fZuschlag').value, 'ajaxzuschlag');
</script>
{/if}