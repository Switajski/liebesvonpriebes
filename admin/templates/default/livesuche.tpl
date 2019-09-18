{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shoptemplate.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="livesuche"}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{include file="tpl_inc/seite_header.tpl" cTitel=#livesearch# cBeschreibung=#livesucheDesc# cDokuURL=#livesucheURL#}
<div id="content">

{if isset($hinweis) && $hinweis|count_characters > 0}			
	 <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}			
	 <p class="box_error">{$fehler}</p>
{/if}
	 
<div class="container">
<form name="sprache" method="post" action="livesuche.php">
<p class="txtCenter">
<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
 <input type="hidden" name="sprachwechsel" value="1">
  <select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
  {foreach name=sprachen from=$Sprachen item=sprache}
	 <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
  {/foreach}
  </select>
	  </p>
 </form>
 <br />
	 
	 <div class="tabber">
				
		  <div class="tabbertab{if isset($cTab) && $cTab == 'suchanfrage'} tabbertabdefault{/if}">
				
				<h2>{#searchrequest#}</h2>                                        
		  {if $Suchanfragen && $Suchanfragen|@count > 0}
				<form name="suche" method="POST" action="livesuche.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="Suche" value="1" />
				<input type="hidden" name="tab" value="suchanfrage" />
				{if isset($cSuche) && $cSuche|count_characters > 0}
					 <input name="cSuche" type="hidden" value="{$cSuche}" />
				{/if}
						  
		  <div class="block tcenter container">
					 <strong>{#livesucheSearchItem#}:</strong> <input name="cSuche" type="text" value="{if isset($cSuche) && $cSuche|count_characters > 0}{$cSuche}{/if}" />
					 <input name="submitSuche" type="submit" value="{#livesucheSearchBTN#}" class="button blue" />
		  </div>
		  </form> 
				
				<form name="login" method="post" action="livesuche.php">
				<input type="hidden" name="livesuche" value="1">
				<input type="hidden" name="s1" value="{$oBlaetterNaviSuchanfragen->nAktuelleSeite}">
				<input type="hidden" name="cSuche" value="{$cSuche}">
				<input type="hidden" name="nSort" value="{$nSort}">
				<input type="hidden" name="tab" value="suchanfrage" />                    
				
				{if $oBlaetterNaviSuchanfragen->nAktiv == 1}
				<div class="container">
						  <p>
						  {$oBlaetterNaviSuchanfragen->nVon} - {$oBlaetterNaviSuchanfragen->nBis} {#from#} {$oBlaetterNaviSuchanfragen->nAnzahl}
						  {if $oBlaetterNaviSuchanfragen->nAktuelleSeite == 1}
								&laquo; {#back#}
						  {else}
								<a href="livesuche.php?s1={$oBlaetterNaviSuchanfragen->nVoherige}&tab=suchanfrage{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&laquo; {#back#}</a>
						  {/if}
						  
						  {if $oBlaetterNaviSuchanfragen->nAnfang != 0}<a href="livesuche.php?s1={$oBlaetterNaviSuchanfragen->nAnfang}&tab=suchanfrage{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviSuchanfragen->nAnfang}</a> ... {/if}
						  {foreach name=blaetternavi from=$oBlaetterNaviSuchanfragen->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviSuchanfragen->nAktuelleSeite == $Blatt}[{$Blatt}]
								{else}
									 <a href="livesuche.php?s1={$Blatt}&tab=suchanfrage{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$Blatt}</a>
								{/if}
						  {/foreach}
						  
						  {if $oBlaetterNaviSuchanfragen->nEnde != 0} ... <a href="livesuche.php?s1={$oBlaetterNaviSuchanfragen->nEnde}&tab=suchanfrage{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviSuchanfragen->nEnde}</a>{/if}
						  
						  {if $oBlaetterNaviSuchanfragen->nAktuelleSeite == $oBlaetterNaviSuchanfragen->nSeiten}
								{#next#} &raquo;
						  {else}
								<a href="livesuche.php?s1={$oBlaetterNaviSuchanfragen->nNaechste}&tab=suchanfrage{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{#next#} &raquo;</a>
						  {/if}
						  
						  </p>
				</div>
				{/if}
				
				{if $cSuche}
					 {assign var=cSuchStr value="Suche=1&cSuche=`$cSuche`&"}
				{else}
					 {assign var=cSuchStr value=""}
				{/if}
				
					 <table>
						  <tr>
								<th class="th-1"></th>
								<th class="tleft">(<a href="livesuche.php?{$cSuchStr}nSort=1{if $nSort == 1}1{/if}{$session_name}={$session_id}&tab=suchanfrage{if $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}">{if $nSort == 1}Z...A{else}A...Z{/if}</a>) {#search#}</th>
								<th class="tleft">(<a href="livesuche.php?{$cSuchStr}nSort=2{if $nSort == 2 || $nSort == -1}2{/if}{$session_name}={$session_id}&tab=suchanfrage{if $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}">{if $nSort == 2 || $nSort == -1}1...9{else}9...1{/if}</a>) {#searchcount#}</th>
								<th class="th-4">(<a href="livesuche.php?{$cSuchStr}nSort=3{if $nSort == 3 || $nSort == -1}3{/if}{$session_name}={$session_id}&tab=suchanfrage{if $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}">{if $nSort == 3 || $nSort == -1}0...1{else}1...0{/if}</a>) {#active#}</th>
								<th class="th-5">{#mapping#}</th>
						  </tr>
				
					 {foreach name=suchanfragen from=$Suchanfragen item=suchanfrage}
						  <input name="kSuchanfrageAll[]" type="hidden" value="{$suchanfrage->kSuchanfrage}">
						  <tr class="tab_bg{$smarty.foreach.suchanfragen.iteration%2}">
								<td class="TD1"><input type="checkbox" name="kSuchanfrage[]" value="{$suchanfrage->kSuchanfrage}"></td>
								<td class="TD2">{$suchanfrage->cSuche}</td>
								<td class="TD3"><input class="fieldOther" name="nAnzahlGesuche_{$suchanfrage->kSuchanfrage}" type="text" value="{$suchanfrage->nAnzahlGesuche}" style="width:50px;"></td>
								<td class="tcenter"><input type="checkbox" name="nAktiv[]" id="nAktiv_{$suchanfrage->kSuchanfrage}" value="{$suchanfrage->kSuchanfrage}" {if $suchanfrage->nAktiv==1}checked{/if}></td>
								<td class="tcenter"><input class="fieldOther" type="text" name="mapping_{$suchanfrage->kSuchanfrage}"></td>
						  </tr>
					 {/foreach}
				 <tr>
					 <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessagesExcept(this.form, 'nAktiv_');" /></td>
					 <td colspan="5" class="TD7">{#livesucheSelectAll#}</td>
				 </tr>
					 </table>                    

				<p class="submit">
					 <input name="suchanfragenUpdate" type="submit" value="{#update#}" class="button reset" /><input name="delete" type="submit" value="{#delete#}" class="button remove" />
					 <input name="nMapping" type="radio" value="1" /> {#livesucheMappingOn#} <input name="cMapping" type="text"  value="" /> <input name="submitMapping" type="submit" value="{#livesucheMappingOnBTN#}" class="button blue" />
				</p>
				</form>
				
		  {else}
					 <div class="box_info">{#noDataAvailable#}</div>
		  {/if}
		  
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'erfolglos'} tabbertabdefault{/if}">
				
				<br />
				<h2>{#searchmiss#}</h2>
		  {if $Suchanfragenerfolglos && $Suchanfragenerfolglos|@count > 0}
				<form name="login" method="post" action="livesuche.php">
				<input type="hidden" name="livesuche" value="2">
				<input type="hidden" name="s2" value="{$oBlaetterNaviSuchanfrageerfolglos->nAktuelleSeite}">
				<input type="hidden" name="tab" value="erfolglos">
				<input type="hidden" name="nErfolglosEditieren" value="{$nErfolglosEditieren}">				
				
				{if $oBlaetterNaviSuchanfrageerfolglos->nAktiv == 1}
				<div class="content">
						  <p>
						  {$oBlaetterNaviSuchanfrageerfolglos->nVon} - {$oBlaetterNaviSuchanfrageerfolglos->nBis} {#from#} {$oBlaetterNaviSuchanfrageerfolglos->nAnzahl}
						  {if $oBlaetterNaviSuchanfrageerfolglos->nAktuelleSeite == 1}
								&laquo; {#back#}
						  {else}
								<a href="livesuche.php?s2={$oBlaetterNaviSuchanfrageerfolglos->nVoherige}&tab=erfolglos">&laquo; {#back#}</a>
						  {/if}
						  
						  {if $oBlaetterNaviSuchanfrageerfolglos->nAnfang != 0}<a href="livesuche.php?s2={$oBlaetterNaviSuchanfrageerfolglos->nAnfang}&tab=erfolglos">{$oBlaetterNaviSuchanfrageerfolglos->nAnfang}</a> ... {/if}
						  {foreach name=blaetternavi from=$oBlaetterNaviSuchanfrageerfolglos->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviSuchanfrageerfolglos->nAktuelleSeite == $Blatt}[{$Blatt}]
								{else}
									 <a href="livesuche.php?s2={$Blatt}&tab=erfolglos">{$Blatt}</a>
								{/if}
						  {/foreach}
						  
						  {if $oBlaetterNaviSuchanfrageerfolglos->nEnde != 0} ... <a href="livesuche.php?s2={$oBlaetterNaviSuchanfrageerfolglos->nEnde}&tab=erfolglos">{$oBlaetterNaviSuchanfrageerfolglos->nEnde}</a>{/if}
						  
						  {if $oBlaetterNaviSuchanfrageerfolglos->nAktuelleSeite == $oBlaetterNaviSuchanfrageerfolglos->nSeiten}
								{#next#} &raquo;
						  {else}
								<a href="livesuche.php?s2={$oBlaetterNaviSuchanfrageerfolglos->nNaechste}&tab=erfolglos">{#next#} &raquo;</a>
						  {/if}
						  
						  </p>
				</div>
				{/if}
				
				<div id="payment">
					 <div id="tabellenLivesuche">                    
					 <table>
						  <tr>
                                <th class="th-1" style="width: 40px;">&nbsp;</th>
								<th class="th-1" align="left">{#search#}</th>
								<th class="th-2" align="left">{#searchcount#}</th>
								<th class="th-3" align="left">{#lastsearch#}</th>
								<th class="th-4" align="left">{#mapping#}</th>
						  </tr>
					 {foreach name=suchanfragenerfolglos from=$Suchanfragenerfolglos item=Suchanfrageerfolglos}
						  <tr class="tab_bg{$smarty.foreach.suchanfragenerfolglos.iteration%2}">
                                <td class="TD1"><input name="kSuchanfrageErfolglos[]" type="checkbox" value="{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" /></td>
								<td class="TD1">{if $nErfolglosEditieren == 1}<input name="cSuche_{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" type="text" value="{$Suchanfrageerfolglos->cSuche}" />{else}{$Suchanfrageerfolglos->cSuche}{/if}</td>
								<td class="TD2">{$Suchanfrageerfolglos->nAnzahlGesuche}</td>
								<td class="TD3">{$Suchanfrageerfolglos->dZuletztGesucht}</td>
								<td class="TD4">{if $nErfolglosEditieren != 1}<input class="fieldOther" name="mapping_{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" type="text">{/if}</td>
						  </tr>
					 {/foreach}
                          <tr>
                                <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessagesExcept(this.form, 'nAktiv_');" /></td>
                                <td colspan="4" class="TD7">{#livesucheSelectAll#}</td>
                          </tr>
					 </table>                    
					 </div>
				</div>                    
				<p style="text-align:center;"><input name="erfolglosUpdate" type="submit" value="{#update#}" /> <input name="erfolglosEdit" type="submit" value="{#livesucheEdit#}" /> <input name="erfolglosDelete" type="submit" value="{#delete#}" /></p>
				</form>                                              
		  {else}
					 <div class="box_info">{#noDataAvailable#}</div>
		  {/if}
										  
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'mapping'} tabbertabdefault{/if}">
				
				<br />
				<h2>{#mapping#}</h2>
				
		  {if $Suchanfragenmapping && $Suchanfragenmapping|@count > 0}
				<form name="login" method="post" action="livesuche.php">
				<input type="hidden" name="livesuche" value="4">
				<input type="hidden" name="tab" value="mapping">
				<input type="hidden" name="s3" value="{$oBlaetterNaviSuchanfragenMapping->nAktuelleSeite}">
				
				{if $oBlaetterNaviSuchanfragenMapping->nAktiv == 1}
				<div class="content">
						  <p>
						  {$oBlaetterNaviSuchanfragenMapping->nVon} - {$oBlaetterNaviSuchanfragenMapping->nBis} {#from#} {$oBlaetterNaviSuchanfragenMapping->nAnzahl}
						  {if $oBlaetterNaviSuchanfragenMapping->nAktuelleSeite == 1}
								&laquo; {#back#}
						  {else}
								<a href="livesuche.php?s3={$oBlaetterNaviSuchanfragenMapping->nVoherige}&tab=mapping">&laquo; {#back#}</a>
						  {/if}
						  
						  {if $oBlaetterNaviSuchanfragenMapping->nAnfang != 0}<a href="livesuche.php?s3={$oBlaetterNaviSuchanfragenMapping->nAnfang}&tab=mapping">{$oBlaetterNaviSuchanfragenMapping->nAnfang}</a> ... {/if}
						  {foreach name=blaetternavi from=$oBlaetterNaviSuchanfragenMapping->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviSuchanfragenMapping->nAktuelleSeite == $Blatt}[{$Blatt}]
								{else}
									 <a href="livesuche.php?s3={$Blatt}&tab=mapping">{$Blatt}</a>
								{/if}
						  {/foreach}
						  
						  {if $oBlaetterNaviSuchanfragenMapping->nEnde != 0} ... <a href="livesuche.php?s3={$oBlaetterNaviSuchanfragenMapping->nEnde}&tab=mapping">{$oBlaetterNaviSuchanfragenMapping->nEnde}</a>{/if}
						  
						  {if $oBlaetterNaviSuchanfragenMapping->nAktuelleSeite == $oBlaetterNaviSuchanfragenMapping->nSeiten}
								{#next#} &raquo;
						  {else}
								<a href="livesuche.php?s3={$oBlaetterNaviSuchanfragenMapping->nNaechste}&tab=mapping">{#next#} &raquo;</a>
						  {/if}
						  
						  </p>
				</div>
				{/if}
				
				<div id="payment">
					 <div id="tabellenLivesuche">                    
					 <table>
						  <tr>
								<th class="th-1"></th>
								<th class="th-2">{#search#}</th>
								<th class="th-3">{#searchnew#}</th>
								<th class="th-4">{#searchcount#}</th>
						  </tr>
					 {foreach name=suchanfragenmapping from=$Suchanfragenmapping item=Suchanfragenmapping}
						  <tr class="tab_bg{$smarty.foreach.suchanfragenmapping.iteration%2}">                                
								<td class="TD1"><input name="kSuchanfrageMapping[]" type="checkbox" value="{$Suchanfragenmapping->kSuchanfrageMapping}"></td>
								<td class="TD2">{$Suchanfragenmapping->cSuche}</td>
								<td class="TD3">{$Suchanfragenmapping->cSucheNeu}</td>
								<td class="TD4">{$Suchanfragenmapping->nAnzahlGesuche}</td>
						  </tr>
					 {/foreach}
					 </table>                    
					 </div>
				</div>                    
				<p style="text-align:center;"><input name="delete" type="submit" value="{#mappingDelete#}" /></p>
				</form>
		  {else}
				<div class="box_info">{#noDataAvailable#}</div>
		  {/if}
		  
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'blacklist'} tabbertabdefault{/if}">
				
				<br />
				<h2>{#blacklist#}</h2>
				<form name="login" method="post" action="livesuche.php">
				<input type="hidden" name="livesuche" value="3">
				<input type="hidden" name="tab" value="blacklist">
				<div id="payment">
					 <div id="tabellenLivesuche">
					 <table>
						  <tr>
								<th class="th-1">{#blacklist#}</th>
						  </tr>
						  <tr class="tab-1_bg">
								<td class="TD2"><textarea name="suchanfrageblacklist" style="width:550px;height:400px;">{foreach name=suchanfragenblacklist from=$Suchanfragenblacklist item=Suchanfrageblacklist}{$Suchanfrageblacklist->cSuche};{/foreach}</textarea></td>
						  </tr>
					 </table>
					 </div>    
				</div>    
				<p style="text-align:center;"><input type="submit" value="{#update#}" class="button orange" /></p>
				</form>
				
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
				
				<br />
				<h2>{#livesucheSettings#}</h2>
				<form name="einstellen" method="post" action="livesuche.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}">
				<input type="hidden" name="einstellungen" value="1">
				<input type="hidden" name="tab" value="einstellungen">
				<div class="settings">
					 {foreach name=conf from=$oConfig_arr item=oConfig}
						  {if $oConfig->cConf == "Y"}
								<p><label for="{$oConfig->cWertName}">({$oConfig->kEinstellungenConf}) {$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
						  {/if}
						  {if $oConfig->cInputTyp=="selectbox"}
								<select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
								{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
									 <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
								{/foreach}
								</select>
						  {elseif $oConfig->cInputTyp=="listbox"}
								<select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="combo" style="width: 250px; height: 150px;"> 
								{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
									 <option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
								{/foreach}
								</select>
						  {else}
								<input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
						  {/if}
						  {else}
								{if $oConfig->cName}<h3 style="text-align:center;">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</h3>{/if}
						  {/if}
					 {/foreach}
				</div>
				
				<p class="submit"><input type="submit" value="{#livesucheSave#}" class="button orange" /></p>
				</form>    
		  </div>
	 
	 </div>
            
</div>
        
{include file='tpl_inc/footer.tpl'}