{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: zahlungsarten_einstellungen.tpl, smarty template inc file

	page for JTL-Shop 3
	Admin

	Author: JTL-Software-GmbH
	http://www.jtl-software.de

	Copyright (c) 2007 JTL-Software


-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cBeschreibung=#configurePaymentmethod# cTitel=$zahlungsart->cName}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}
		  <p class="box_error">{$fehler}</p>
	 {/if}

	 <div class="settings">
		  <form name="einstellen" method="post" action="zahlungsarten.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="einstellungen_bearbeiten" value="1" />
		  <input type="hidden" name="kZahlungsart" value="{if isset($zahlungsart->kZahlungsart)}{$zahlungsart->kZahlungsart}{/if}" />

		  <div class="category">{#settings#}: Allgemein</div>

		  {foreach name=sprachen from=$sprachen item=sprache}
		  {assign var="cISO" value=$sprache->cISO}
		  <p><label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
		  <input type="text" name="cName_{$cISO}" id="cName_{$cISO}"  value="{if isset($Zahlungsartname[$cISO])}{$Zahlungsartname[$cISO]}{/if}" tabindex="1" /></p>
		  {/foreach}
		  <p><label for="cBild">{#pictureURL#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#pictureDesc#}" title="{#pictureDesc#}" style="vertical-align:middle; cursor:help;" />
		  </label>
		  <input name="cBild" id="cBild"  value="{if isset($zahlungsart->cBild)}{$zahlungsart->cBild}{/if}" tabindex="1" /></p>
		  {foreach name=sprachen from=$sprachen item=sprache}
		  {assign var="cISO" value=$sprache->cISO}
		  <p>
			  <label for="cGebuehrname_{$cISO}">{#feeName#} ({$sprache->cNameDeutsch})</label>
			  <input type="text" name="cGebuehrname_{$cISO}" id="cGebuehrname_{$cISO}"  value="{if isset($Gebuehrname[$cISO])}{$Gebuehrname[$cISO]}{/if}" tabindex="2" />
		  </p>
		  {/foreach}

		  <p><label for="kKundengruppe">{#restrictedToCustomerGroups#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#multipleChoice#}" title="{#multipleChoice#}" style="vertical-align:middle; cursor:help;" /></label>
			  <select name="kKundengruppe[]" multiple size="6" id="kKundengruppe" class="combo">
			  <option value="0" {if isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0]}selected{/if}>{#allCustomerGroups#}</option>
			  {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
			  {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
			  <option value="{$kundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen[$kKundengruppe]) && $gesetzteKundengruppen[$kKundengruppe]}selected{/if}>{$kundengruppe->cName}</option>
			  {/foreach}
			  </select>
		  </p>
		  <p>
			  <label for="nSort">{#sortNo#}</label>
			  <input type="text" name="nSort" id="nSort"  value="{if isset($zahlungsart->nSort)}{$zahlungsart->nSort}{/if}" tabindex="3" />
		  </p>

		  {foreach name=sprachen from=$sprachen item=sprache}
		    {assign var="cISO" value=$sprache->cISO}
			  <p>
				  <label for="kKundengruppe">{#noticeText#} ({$sprache->cNameDeutsch})</label>
				  <textarea name="cHinweisText_{$cISO}" style="width: 300px; height: 100px;">{if isset($cHinweisTexte_arr[$cISO])}{$cHinweisTexte_arr[$cISO]}{/if}</textarea>
			  </p>
		  {/foreach}

		  <p>
			  <label for="nMailSenden">{#paymentAckMail#}</label>
			  <select name="nMailSenden" class="combo" style="width: 60px;">
			  <option value="1"{if $zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_EINGANG} selected="selected"{/if}>Ja</option>
			  <option value="0"{if !($zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_EINGANG)} selected="selected"{/if}>Nein</option>
			  </select>
		  </p>

		  <p>
			  <label for="nMailSendenStorno">{#paymentCancelMail#}</label>
			  <select name="nMailSendenStorno" class="combo" style="width: 60px;">
			  <option value="1"{if $zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_STORNO} selected="selected"{/if}>Ja</option>
			  <option value="0"{if !($zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_STORNO)} selected="selected"{/if}>Nein</option>
			  </select>
		  </p>

		  {if $zahlungsart->cModulId != "za_nachnahme_jtl" && $zahlungsart->cModulId != "za_ueberweisung_jtl" && $zahlungsart->cModulId != "za_rechnung_jtl" && $zahlungsart->cModulId != "za_barzahlung_jtl"
		  && $zahlungsart->cModulId != "za_lastschrift_jtl" && $zahlungsart->cModulId != "za_kreditkarte_jtl" && $zahlungsart->cModulId != "za_iloxx_jtl" && $zahlungsart->cModulId != "za_paypal_jtl"
		  && $zahlungsart->cModulId != "za_safetypay" && $zahlungsart->cModulId != "za_billpay_jtl" && $zahlungsart->cModulId != "za_eos_cc_jtl" && $zahlungsart->cModulId != "za_eos_dd_jtl"
        && $zahlungsart->cModulId != "za_eos_direct_jtl" && $zahlungsart->cModulId != "za_eos_ewallet_jtl" && $zahlungsart->cModulId != "za_clickandbuy_jtl"}
		  <p>
			  <label for="nWaehrendBestellung">{#duringOrder#}</label>
			  <select name="nWaehrendBestellung" class="combo" style="width: 60px;">
			  <option value="1"{if isset($zahlungsart->nWaehrendBestellung) && $zahlungsart->nWaehrendBestellung == 1} selected{/if}>Ja</option>
			  <option value="0"{if isset($zahlungsart->nWaehrendBestellung) && $zahlungsart->nWaehrendBestellung == 0} selected{/if}>Nein</option>
			  </select>
		  </p>
		  {else}
				{if $zahlungsart->cModulId == "za_billpay_jtl" || $zahlungsart->cModulId == "za_eos_cc_jtl" || $zahlungsart->cModulId == "za_eos_dd_jtl"
                    || $zahlungsart->cModulId == "za_eos_direct_jtl" || $zahlungsart->cModulId == "za_eos_ewallet_jtl"}
					 <input type="hidden" name="nWaehrendBestellung" value="1" />
				{/if}
		  {/if}
		  </div>
		  <div class="settings">
				{foreach name=conf from=$Conf item=cnf}
				{if $cnf->cConf=="Y"}
				<p>
					<label for="fax">{$cnf->cName} <img src="{$currentTemplateDir}gfx/help.png" alt="{$cnf->cBeschreibung}" title="{$cnf->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
					{if $cnf->cInputTyp=="selectbox"}
					<select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="combo">
					{foreach name=selectfor from=$cnf->ConfWerte item=wert}
					<option value="{$wert->cWert}" {if isset($cnf->gesetzterWert) && $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
				{/foreach}
				</select>
				{else}
				<input type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}"  value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="4"{*{if ($cnf->cWertName|strpos:"_min" && $cnf->cWertName|strpos:"_min_" === false) || $cnf->cWertName|strpos:"_max"} onKeyUp="javascript:setzePreisAjax(false, 'EinstellungAjax_{$cnf->kEinstellungenConf}', this);"{/if}*} /><span id="EinstellungAjax_{$cnf->kEinstellungenConf}"></span>
				{/if}
				</p>
				{else}
				<div class="category">{#settings#}: {$cnf->cName}</div>
				{/if}
				{/foreach}
		  </div>

		  <p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>

		  </form>
	 </div>
</div>

{*
<script type="text/javascript">
{foreach name=conf from=$Conf item=cnf}
{if $cnf->cConf=="Y"}
{if ($cnf->cWertName|strpos:"_min" && $cnf->cWertName|strpos:"_min_" === false) || $cnf->cWertName|strpos:"_max"}
xajax_getCurrencyConversionAjax(0, document.getElementById('{$cnf->cWertName}').value, 'EinstellungAjax_{$cnf->kEinstellungenConf}');
{/if}
{/if}
{/foreach}
</script>
*}