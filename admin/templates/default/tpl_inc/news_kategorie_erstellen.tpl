{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news_kategorie_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#newsCat#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
		<form name="news" method="post" action="news.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="news" value="1" />
		<input type="hidden" name="news_kategorie_speichern" value="1" />
			<input type="hidden" name="tab" value="kategorien" />
		{if isset($oNewsKategorie->kNewsKategorie) && $oNewsKategorie->kNewsKategorie > 0}
		<input type="hidden" name="newskategorie_edit_speichern" value="1" />
		<input type="hidden" name="kNewsKategorie" value="{$oNewsKategorie->kNewsKategorie}" />
		{/if}
		
		<table class="list" id="formtable">				
			<tr>
				<td>{#newsCatName#}:</td>
				<td><input name="cName" type="text" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewsKategorie->cName)}{$oNewsKategorie->cName}{/if}" />{if isset($cPlausiValue_arr.cName) && $cPlausiValue_arr.cName == 2} {#newsAlreadyExists#}{/if}</td>
			</tr>
			
			<tr>
				<td>{#newsSeo#}:</td>
				<td><input name="cSeo" type="text"  value="{if isset($cPostVar_arr.cSeo)}{$cPostVar_arr.cSeo}{elseif isset($oNewsKategorie->cSeo)}{$oNewsKategorie->cSeo}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsCatSort#}:</td>
				<td><input name="nSort" type="text"  value="{if isset($cPostVar_arr.nSort)}{$cPostVar_arr.nSort}{elseif isset($oNewsKategorie->nSort)}{$oNewsKategorie->nSort}{/if}" style="width: 60px;" /></td>
			</tr>
			
			<tr>
				<td>{#newsActive#}:</td>
				<td>
					<select name="nAktiv">
						<option value="1"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "1") || (isset($oNewsKategorie->nAktiv) && $oNewsKategorie->nAktiv == 1)} selected{/if}>Ja</option>
						<option value="0"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "0") || (isset($oNewsKategorie->nAktiv) && $oNewsKategorie->nAktiv == 0)} selected{/if}>Nein</option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td>{#newsMetaTitle#}:</td>
				<td><input name="cMetaTitle" type="text"  value="{if isset($cPostVar_arr.cMetaTitle)}{$cPostVar_arr.cMetaTitle}{elseif isset($oNewsKategorie->cMetaTitle)}{$oNewsKategorie->cMetaTitle}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsMetaDescription#}:</td>
				<td><input name="cMetaDescription" type="text"  value="{if isset($cPostVar_arr.cMetaDescription)}{$cPostVar_arr.cMetaDescription}{elseif isset($oNewsKategorie->cMetaDescription)}{$oNewsKategorie->cMetaDescription}{/if}" /></td>
			</tr>
			
			<tr>
				<td>{#newsCatDesc#}:</td>
				<td><textarea class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($cPostVar_arr.cBeschreibung)}{$cPostVar_arr.cBeschreibung}{elseif isset($oNewsKategorie->cBeschreibung)}{$oNewsKategorie->cBeschreibung}{/if}</textarea></td>
			</tr>
		</table>
		
		<div class="save_wrapper">
			<input name="speichern" type="button" value="{#newsSave#}" onclick="javascript:document.news.submit();" class="button orange" />
		</div>
			</form>
	</div>
</div>