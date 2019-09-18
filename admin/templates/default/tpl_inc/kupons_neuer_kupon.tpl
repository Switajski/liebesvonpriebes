{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kupons_neuer_kupon.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}

{if !isset($Kupon->kKupon) || !$Kupon->kKupon}
{assign var=cTitel value=#newCoupon#}
{else}
{assign var=cTitel value=#modifyCoupon#}
{/if}

{include file="tpl_inc/seite_header.tpl" cTitel=$cTitel cBeschreibung=#newCouponDesc#}
<div id="content">
		  
{if isset($hinweis) && $hinweis|count_characters > 0}			
	<p class="box_info">{$hinweis}</p>
{/if}

<div class="container">
	<form name="kupon_neu" method="post" action="kupons.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}" />
		<input type="hidden" name="neuerKupon" value="1" />
		<input type="hidden" name="cKuponTyp" value="{$Kupon->cKuponTyp}" />
		<input type="hidden" name="kKupon" value="{if isset($Kupon->kKupon)}{$Kupon->kKupon}{/if}" />
		<div class="settings">
			<p><label for="cName">{#name#}</label>
			<input type="text" name="cName" id="cName"  value="{if isset($Kupon->cName)}{$Kupon->cName}{/if}" tabindex="1" /></p>
			{foreach name=sprachen from=$sprachen item=sprache}
				{assign var="cISO" value=$sprache->cISO}
				<p><label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
				<input type="text" name="cName_{$cISO}" id="cName_{$cISO}"  value="{if isset($Kuponname[$cISO])}{$Kuponname[$cISO]}{/if}" tabindex="2" /></p>
			{/foreach}
			{if $Kupon->cKuponTyp=="standard" || $Kupon->cKuponTyp=="neukundenkupon"}
				<p><label for="fWert">{#value#} ({#gross#})</label>
				<input style="width:40px;" type="text" name="fWert" id="fWert"  value="{if isset($Kupon->fWert)}{$Kupon->fWert}{/if}" tabindex="3" onKeyUp="setzePreisAjax(false, 'WertAjax', this)" />
				<select style="width:60px;" name="cWertTyp" id="cWertTyp" class="combo">
					<option value="festpreis" {if isset($Kupon->cWertTyp) && $Kupon->cWertTyp=="festpreis"}selected{/if}>Betrag</option>
					<option value="prozent" {if isset($Kupon->cWertTyp) && $Kupon->cWertTyp=="prozent"}selected{/if}>%</option>
				</select> <span id="WertAjax"></span></p>

				<p><label for="nGanzenWKRabattieren">{#wholeWKDiscount#}</label>
					<select style="width: 60px" name="nGanzenWKRabattieren" id="nGanzenWKRabattieren" class="combo">
						<option value="1"{if isset($Kupon->nGanzenWKRabattieren) && $Kupon->nGanzenWKRabattieren == 1} selected{/if}>Ja</option>
						<option value="0"{if isset($Kupon->nGanzenWKRabattieren) && $Kupon->nGanzenWKRabattieren == 0} selected{/if}>Nein</option>
					</select>
				</p>

				<p><label for="kSteuerklasse">{#taxClass#}</label>
				<select name="kSteuerklasse" id="kSteuerklasse" class="combo">
					{foreach name=steuer from=$steuerklassen item=steuerklasse}
						<option value="{if isset($steuerklasse->kSteuerklasse)}{$steuerklasse->kSteuerklasse}{/if}" {if isset($Kupon->kSteuerklasse) && $Kupon->kSteuerklasse==$steuerklasse->kSteuerklasse}selected{/if}>{$steuerklasse->cName}</option>
					{/foreach}
				</select>
				{elseif $Kupon->cKuponTyp=="versandkupon"}
					<p>
						<label for="cZusatzgebuehren">{#additionalShippingCosts#}  <img src="{$currentTemplateDir}gfx/help.png" alt="{#additionalShippingCostsHint#}" title="{#additionalShippingCostsHint#}" style="vertical-align:middle; cursor:help;" /></label>
						<input type="checkbox" name="cZusatzgebuehren" id="cZusatzgebuehren" class="checkfield" value="Y" {if isset($Kupon->cZusatzgebuehren) && $Kupon->cZusatzgebuehren=="Y"}checked{/if} />
					</p>
				{/if}
				<p>
					<label for="fMindestbestellwert">{#minOrderValue#} ({#gross#})</label>
					<input style="width:40px;" type="text" name="fMindestbestellwert" id="fMindestbestellwert"  value="{if isset($Kupon->fMindestbestellwert)}{$Kupon->fMindestbestellwert}{/if}" tabindex="4" onKeyUp="setzePreisAjax(false, 'MindestWertAjax', this)" /> <span id="MindestWertAjax"></span>
				</p>
				{if isset($Kupon->cKuponTyp) && ($Kupon->cKuponTyp=="standard" || $Kupon->cKuponTyp=="versandkupon")}
				<p>
					<label for="cCode">{#code#}</label>
					<input type="text" name="cCode" id="cCode"  value="{if isset($Kupon->cCode)}{$Kupon->cCode}{/if}" tabindex="7" />
				</p>
				{/if}
				{if isset($Kupon->cKuponTyp) && $Kupon->cKuponTyp=="versandkupon"}
					<p>
						<label for="cLieferlaender">{#shippingCountries#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#shippingCountriesHint#}" title="{#shippingCountriesHint#}" style="vertical-align:middle; cursor:help;" /></label>
						<input type="text" name="cLieferlaender" id="cLieferlaender"  value="{if isset($Kupon->cLieferlaender)}{$Kupon->cLieferlaender}{/if}" tabindex="8" />
					</p>
				{/if}
				<p>
					<label for="nVerwendungen">{#uses#}</label>
					<input type="text" name="nVerwendungen" id="nVerwendungen"  value="{if isset($Kupon->nVerwendungen)}{$Kupon->nVerwendungen}{/if}" tabindex="9" />
				</p>
				{if $Kupon->cKuponTyp=="standard" || $Kupon->cKuponTyp=="versandkupon"}
					<p>
						<label for="cMetaTitle_{$cISO}">{#usesPerCustomer#}</label>
						<input type="text" name="nVerwendungenProKunde" id="nVerwendungenProKunde"  value="{if isset($Kupon->nVerwendungenProKunde)}{$Kupon->nVerwendungenProKunde}{/if}" tabindex="10" />
					</p>
				{/if}
				<p>
					<label for="assign_article_list">{#productRestrictions#}</label>
					<input type="text" name="cArtikel" id="assign_article_list"  value="{if isset($Kupon->cArtikel)}{$Kupon->cArtikel}{/if}" tabindex="10" />

					<a href="#" class="button edit" id="show_article_list">Artikel verwalten</a>
					<div id="ajax_list_picker" class="article">{include file="tpl_inc/popup_artikelsuche.tpl"}</div>
				</p>
				<p>
					<label for="kKundengruppe">{#restrictionToCustomerGroup#}</label>
					<select name="kKundengruppe" id="kKundengruppe" class="combo">
					<option value="-1" {if isset($Kupon->kKundengruppe) && isset($kundengruppe->kKundengruppe) && $Kupon->kKundengruppe==$kundengruppe->kKundengruppe}selected{/if}>Alle Kundengruppen</option>
						{foreach name=kundengruppen from=$kundengruppen item=kundengruppe}
							<option value="{$kundengruppe->kKundengruppe}" {if isset($Kupon->kKundengruppe) && $Kupon->kKundengruppe==$kundengruppe->kKundengruppe}selected{/if}>{$kundengruppe->cName}</option>
						{/foreach}
					</select>
				</p>
				<p>
					<label for="dGueltigAb">{#validity#}</label>

					{#from#}: <input style="width:120px" type="text" name="dGueltigAb" id="dGueltigAb"  value="{if isset($Kupon->GueltigAb)}{$Kupon->GueltigAb}{else}{$smarty.now|date_format:"%d.%m.%Y %H:%M"}{/if}" tabindex="11" />
					{#to#}: <input style="width:120px" type="text" name="dGueltigBis" id="dGueltigBis"  value="{if isset($Kupon->GueltigBis)}{$Kupon->GueltigBis}{/if}" tabindex="10" />
				</p>
				<p>
					<label for="cAktiv">{#active#}</label>
					<input type="checkbox" name="cAktiv" id="cAktiv" class="checkfield" value="Y" {if (isset($Kupon->cAktiv) && $Kupon->cAktiv=="Y") || !isset($Kupon->kKupon) || !$Kupon->kKupon}checked{/if} />
				</p>
				<p>
					<label for="kKategorien">{#restrictedToCategories#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#multipleChoice#}" title="{#multipleChoice#}" style="vertical-align:middle; cursor:help;" /></label>
					<select name="kKategorien[]" multiple size="10" id="kKategorien" class="combo">
						<option value="0" {if ((isset($Kupon->cKategorien) && $Kupon->cKategorien=="-1") || !isset($Kupon->kKupon) || !$Kupon->kKupon) && !$kategoriebaum_selected}selected{/if}>{#allCategories#}</option>
						{foreach name=kategorie from=$kategoriebaum item=kat}
							<option value="{$kat->kKategorie}" {if $kat->selected==1}selected{/if}>{$kat->cName}</option>
						{/foreach}
					</select>
				</p>
				{if isset($Kupon->cKuponTyp) && ($Kupon->cKuponTyp=="standard" || $Kupon->cKuponTyp=="versandkupon")}
					<p>
						<label for="kKunden">{#restrictedToCustomers#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#multipleChoice#}" title="{#multipleChoice#}" style="vertical-align:middle; cursor:help;" /></label>
						<select name="kKunden[]" multiple size="10" id="kKunden" class="combo">
							<option value="0" {if ((isset($Kupon->cKunden) && $Kupon->cKunden=="-1") || !isset($Kupon->kKupon) || !$Kupon->kKupon) && !$kunden_selected}selected{/if}>{#allCustomers#}</option>
							{foreach name=kunden from=$kunden item=kunde}
								<option value="{$kunde->kKunde}" {if $kunde->selected==1}selected{/if}>{$kunde->cNachname}, {$kunde->cVorname} {if isset($kunde->cFirma) && $kunde->cFirma|strlen > 0}({$kunde->cFirma}){/if}</option>
							{/foreach}
						</select>
					</p>
				<p>
					<label for="informieren">{#informCustomers#}</label>
					<input type="checkbox" name="informieren" id="informieren" class="checkfield" value="Y" />
				</p>
			{/if}
		</div>
		<div style="clear:both"></div>
		<p class="submit" style="margin-top:10px;"><input type="submit" value="{if !isset($Kupon->kKupon) || !$Kupon->kKupon}{#newCoupon#}{else}{#modifyCoupon#}{/if}" class="button orange" /></p>
	</form>
</div>
			
<script type="text/javascript">
xajax_getCurrencyConversionAjax(0, document.getElementById('fWert').value, 'WertAjax');
xajax_getCurrencyConversionAjax(0, document.getElementById('fMindestbestellwert').value, 'MindestWertAjax');
</script>