{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
<script type="text/javascript">
var i = 10;
var j = 2;

function addInputRow()
{ldelim}
	var row = document.getElementById('formtable').insertRow(i);
	row.id = i;
	row.valign = 'top';
	
	var cell_1 = row.insertCell(0);
	var cell_2 = row.insertCell(1);
	
	var input1 = document.createElement('input');
	input1.type = 'file';
	input1.name = 'Bilder[]';
	input1.className = 'field';
	input1.id = 'Bilder_' + i;
	input1.maxlength = '2097152';
	input1.accept = 'image/*';
	
	var myText = document.createTextNode('Bild ' + j + ':');
	
	/*
	var input2 = document.createElement('input');
	input2.type = 'button';
	input2.name = 'delete';
	input2.value = '-';
	input2.className = 'button';
	input2.onlick = function() {ldelim} delInputRow(row.id); {rdelim};
	*/
	
	cell_1.appendChild(myText);
	cell_2.appendChild(input1);
	//cell_2.appendChild(input2);
	
	i += 1;
	j += 1;
{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#news# cBeschreibung=#newsDesc#}
<div id="content">
	
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	
	<form name="news" method="post" action="news.php" enctype="multipart/form-data">
	<input type="hidden" name="{$session_name}" value="{$session_id}" />
	<input type="hidden" name="news" value="1" />
	<input type="hidden" name="news_speichern" value="1" />
		<input type="hidden" name="tab" value="aktiv" />
	{if isset($oNews->kNews) && $oNews->kNews > 0}
	<input type="hidden" name="news_edit_speichern" value="1" />
	<input type="hidden" name="kNews" value="{if isset($oNews->kNews)}{$oNews->kNews}{/if}" />
	{/if}

   <div class="settings">
		<div class="category">{#news#}</div>
		<table id="formtable" class="list">
			<tr>
				<td>{#newsHeadline#}: *</td>
				<td><input type="text" name="betreff" value="{if isset($cPostVar_arr.betreff) && $cPostVar_arr.betreff}{$cPostVar_arr.betreff}{elseif isset($oNews->cBetreff)}{$oNews->cBetreff}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsSeo#}:</td>
				<td><input name="seo" type="text"  value="{if isset($oNews->cSeo)}{$oNews->cSeo}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsCustomerGrp#}: *</td>
				<td>
					<select name="kKundengruppe[]" multiple="multiple">
						<option value="-1"{if isset($oNews->kKundengruppe_arr)}{foreach name=kundengruppen from=$oNews->kKundengruppe_arr item=kKundengruppe}{if $kKundengruppe == "-1"} selected{/if}{/foreach}{/if}>Alle</option>
					{foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
						<option value="{$oKundengruppe->kKundengruppe}"
							{if isset($cPostVar_arr.kKundengruppe)}
								{foreach name=kkundengruppe from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
									{if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
								{/foreach}
							{elseif isset($oNews->kKundengruppe_arr)}
								{foreach name=kkundengruppen from=$oNews->kKundengruppe_arr item=kKundengruppe}
									{if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
								{/foreach}
							{/if}>{$oKundengruppe->cName}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			
			<tr>
				<td>{#newsCat#}: *</td>
				<td>
					<select name="kNewsKategorie[]" multiple="multiple">
					{foreach name=newskategorie from=$oNewsKategorie_arr item=oNewsKategorie}
						<option value="{$oNewsKategorie->kNewsKategorie}"
							{if isset($cPostVar_arr.kNewsKategorie)}
								{foreach name=kNewsKategorieNews from=$cPostVar_arr.kNewsKategorie item=kNewsKategorieNews}
									{if $oNewsKategorie->kNewsKategorie == $kNewsKategorieNews}selected{/if}
								{/foreach}
							{elseif isset($oNewsKategorieNews_arr)}
								{foreach name=kNewsKategorieNews from=$oNewsKategorieNews_arr item=oNewsKategorieNews}
									{if $oNewsKategorie->kNewsKategorie == $oNewsKategorieNews->kNewsKategorie}selected{/if}
								{/foreach}
							{/if}>{$oNewsKategorie->cName}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			
			<tr>
				<td>{#newsValidation#}: *</td>
				<td><input name="dGueltigVon" type="text"  value="{if isset($oNews->dGueltigVon_de) && $oNews->dGueltigVon_de|count_characters > 0}{$oNews->dGueltigVon_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}"  /></td>
			</tr>
			
			<tr>
				<td>{#newsActive#}: *</td>
				<td>
					<select name="nAktiv"  >
						<option value="1"{if isset($oNews->nAktiv) && $oNews->nAktiv == 1} selected{/if}>Ja</option>
						<option value="0"{if isset($oNews->nAktiv) && $oNews->nAktiv == 0} selected{/if}>Nein</option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td>{#newsMetaTitle#}:</td>
				<td><input name="cMetaTitle" type="text"  value="{if isset($oNews->cMetaTitle)}{$oNews->cMetaTitle}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsMetaDescription#}:</td>
				<td><input name="cMetaDescription" type="text"  value="{if isset($oNews->cMetaDescription)}{$oNews->cMetaDescription}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsMetaKeywords#}:</td>
				<td><input name="cMetaKeywords" type="text"  value="{if isset($oNews->cMetaKeywords)}{$oNews->cMetaKeywords}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsPictures#}:</td>
				<td valign="top">
					<input id="Bilder_0" name="Bilder[]" type="file"  maxlength="2097152" accept="image/*" />
				</td> 
			</tr>
			
			<tr>
				<td></td>
				<td><input name="hinzufuegen" type="button" value="{#newsPicAdd#}" onclick="javascript:addInputRow();" class="button add" /></td>
			</tr>
							
			<tr>
				<td>{#newsText#}: *</td>
				<td><textarea class="ckeditor" name="text" rows="15" cols="60">{if isset($cPostVar_arr.text) && $cPostVar_arr.text}{$cPostVar_arr.text}{elseif isset($oNews->cText)}{$oNews->cText}{/if}</textarea></td>
			</tr>
			
			<tr>
				<td>{#newsPreviewText#}:</td>
				<td><textarea class="ckeditor" name="cVorschauText" rows="15" cols="60">{if isset($oNews->cVorschauText)}{$oNews->cVorschauText}{/if}</textarea></td>
			</tr>
			
			<tr>
				<td>{#newsPics#}:</td>
				<td valign="top"></td>
			</tr>
			
			{if isset($oDatei_arr) && $oDatei_arr|@count > 0}
				{foreach name=bilder from=$oDatei_arr item=oDatei}
				<tr>
					<td>&nbsp;</td>
					<td valign="top">
						  <div class="box_plain"><a href="news.php?news=1&news_editieren=1&kNews={$oNews->kNews}&delpic={$oDatei->cName}"><img src="templates/default/gfx/layout/remove.png" alt="delete"></a> Link: $#{$oDatei->cName}#$</div>
						  <div>{$oDatei->cURL}</div>
					</td> 
				</tr>
				{/foreach}
			{/if}
		</table>
	</div>
		
	<p class="box_info container">{#newsMandatoryFields#}</p>
		
	<div class="save_wrapper">
		<input name="speichern" type="button" value="{#newsSave#}" onclick="javascript:document.news.submit();" class="button orange" />
	</div>
	</form>
</div>