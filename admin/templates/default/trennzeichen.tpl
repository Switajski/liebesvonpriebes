{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: trennzeichen.tpl, smarty template inc file
	
	page for JTL-Shop 3
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="trennzeichen"}

{include file="tpl_inc/seite_header.tpl" cTitel=#Trennzeichen# cBeschreibung=#trennzeichenDesc# cDokuURL=#trennzeichenURL#}
<div id="content">
	
	{if isset($cHinweis) && $cHinweis|count_characters > 0}
		<p class="box_success">{$cHinweis}</p>
	{/if}
	{if isset($cFehler) && $cFehler|count_characters > 0}
		<p class="box_error">{$cFehler}</p>
	{/if}
	
	<div class="container">
	
		<div class="block tcenter">
		{if isset($Sprachen) && $Sprachen|@count > 1}
			<form name="sprache" method="post" action="trennzeichen.php" class="inline_block">
				<label for="{#changeLanguage#}">{#changeLanguage#}</label>
				<input type="hidden" name="sprachwechsel" value="1" />
				<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
				{foreach name=sprachen from=$Sprachen item=sprache}
				<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
				{/foreach}
				</select>
			</form>
		{/if}
		</div>
	
		<form method="post" action="trennzeichen.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="save" value="1">
		<div id="settings">
         <div class="category">Trennzeichen</div>
            
         <table class="list">
            <thead>
               <tr>
                  <th class="tleft">Einheit</th>
                  <th class="tcenter">Anzahl Dezimalstellen</th>
                  <th class="tcenter">Dezimaltrennzeichen</th>
                  <th class="tcenter">Tausendertrennzeichen</th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td></td>
                  <td class="tcenter"><small>(z.b. 2)</small></td>
                  <td class="tcenter"><small>(z.b. .)</small></td>
                  <td class="tcenter"><small>(z.b. ,)</small></td>
               </tr>
               <tr>
               	  {assign var=nDezimal_weight value=nDezimal_$JTLSEPARATER_WEIGHT}
                  {assign var=cDezZeichen_weight value=cDezZeichen_$JTLSEPARATER_WEIGHT}
                  {assign var=cTausenderZeichen_weight value=cTausenderZeichen_$JTLSEPARATER_WEIGHT}
               {if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}
                  <input type="hidden" name="kTrennzeichen_{$JTLSEPARATER_WEIGHT}" value="{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getTrennzeichen()}" />
               {/if}
                  <td class="tleft">Gewicht</td>                  
                  <td class="widthheight tcenter"><input type="text" name="nDezimal_{$JTLSEPARATER_WEIGHT}"{if isset($xPlausiVar_arr[$nDezimal_weight])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$nDezimal_weight])}{$xPostVar_arr[$nDezimal_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getDezimalstellen()}{/if}{/if}" /></td>
                  <td class="widthheight tcenter"><input type="text" name="cDezZeichen_{$JTLSEPARATER_WEIGHT}"{if isset($xPlausiVar_arr[$cDezZeichen_weight])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$cDezZeichen_weight])}{$xPostVar_arr[$cDezZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getDezimalZeichen()}{/if}{/if}" /></td>
                  <td class="widthheight tcenter"><input type="text" name="cTausenderZeichen_{$JTLSEPARATER_WEIGHT}"{if isset($xPlausiVar_arr[$cTausenderZeichen_weight])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$cTausenderZeichen_weight])}{$xPostVar_arr[$cTausenderZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getTausenderZeichen()}{/if}{/if}" /></td>
               </tr>
               
               {*
               <tr>
               	  {assign var=nDezimal_length value=nDezimal_$JTLSEPARATER_LENGTH}
                  {assign var=cDezZeichen_length value=cDezZeichen_$JTLSEPARATER_LENGTH}
                  {assign var=cTausenderZeichen_length value=cTausenderZeichen_$JTLSEPARATER_LENGTH}
               {if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH])}
                  <input type="hidden" name="kTrennzeichen_{$JTLSEPARATER_LENGTH}" value="{$oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH]->getTrennzeichen()}" />
               {/if}
                  <td class="tleft">L&auml;nge</td>
                  <td class="widthheight tcenter"><input type="text" name="nDezimal_{$JTLSEPARATER_LENGTH}"{if isset($xPlausiVar_arr[$nDezimal_length])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$nDezimal_length])}{$xPostVar_arr[$nDezimal_length]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH]->getDezimalstellen()}{/if}{/if}" /></td>
                  <td class="widthheight tcenter"><input type="text" name="cDezZeichen_{$JTLSEPARATER_LENGTH}"{if isset($xPlausiVar_arr[$cTausenderZeichen_length])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$cDezZeichen_length])}{$xPostVar_arr[$cDezZeichen_length]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH]->getDezimalZeichen()}{/if}{/if}" /></td>
                  <td class="widthheight tcenter"><input type="text" name="cTausenderZeichen_{$JTLSEPARATER_LENGTH}"{if isset($xPlausiVar_arr[$cTausenderZeichen_length])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$cTausenderZeichen_length])}{$xPostVar_arr[$cTausenderZeichen_length]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_LENGTH]->getTausenderZeichen()}{/if}{/if}" /></td>
               </tr>
               *}
               
               <tr>
               	  {assign var=nDezimal_amount value=nDezimal_$JTLSEPARATER_AMOUNT}
                  {assign var=cDezZeichen_amount value=cDezZeichen_$JTLSEPARATER_AMOUNT}
                  {assign var=cTausenderZeichen_amount value=cTausenderZeichen_$JTLSEPARATER_AMOUNT}
               {if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}
                  <input type="hidden" name="kTrennzeichen_{$JTLSEPARATER_AMOUNT}" value="{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getTrennzeichen()}" />
               {/if}
                  <td class="tleft">Menge</td>
                  <td class="widthheight tcenter"><input type="text" name="nDezimal_{$JTLSEPARATER_AMOUNT}"{if isset($xPlausiVar_arr[$nDezimal_amount])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$nDezimal_amount])}{$xPostVar_arr[$nDezimal_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getDezimalstellen()}{/if}{/if}" /></td>
                  {*<td class="tcenter">2<input type="hidden" name="nDezimal_{$JTLSEPARATER_AMOUNT}" value="2" /></td>*}
                  <td class="widthheight tcenter"><input type="text" name="cDezZeichen_{$JTLSEPARATER_AMOUNT}"{if isset($xPlausiVar_arr[$cDezZeichen_amount])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$cDezZeichen_amount])}{$xPostVar_arr[$cDezZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getDezimalZeichen()}{/if}{/if}" /></td>
                  <td class="widthheight tcenter"><input type="text" name="cTausenderZeichen_{$JTLSEPARATER_AMOUNT}"{if isset($xPlausiVar_arr[$cTausenderZeichen_amount])} class="fieldfillout"{/if} value="{if isset($xPostVar_arr[$cTausenderZeichen_amount])}{$xPostVar_arr[$cTausenderZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getTausenderZeichen()}{/if}{/if}" /></td>
               </tr>
               
            </tbody>
         </table>
         <p class="submit"><input name="speichern" type="submit" value="{#trennzeichenSave#}" class="button orange" /></p>
		</form>
        </div>
	</div>
</div>

{include file='tpl_inc/footer.tpl'}