{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: preisanzeige.tpl, smarty template inc file

	admin page for JTL-Shop 3

	http://www.jtl-software.de

	Copyright (c) 2008 JTL-Software


-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#coupons# cDokuURL=#couponsURL#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}

	 {if $kupons_aktiv|@count > 0}
	 <div class="category">{#activeCoupons#}</div>
	       <div class=" block clearall">
                <div class="left">
                     {if $oBlaetterNaviAktiv->nAktiv == 1}
                          <div class="pages tright">
                                <span class="pageinfo"><strong>{$oBlaetterNaviAktiv->nVon}</strong> - {$oBlaetterNaviAktiv->nBis} {#from#} {$oBlaetterNaviAktiv->nAnzahl}</span>
                                <a class="back" href="kupons.php?s1={$oBlaetterNaviAktiv->nVoherige}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&laquo;</a>
                                {if $oBlaetterNaviAktiv->nAnfang != 0}<a href="kupons.php?s1={$oBlaetterNaviAktiv->nAnfang}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviAktiv->nAnfang}</a> ... {/if}
                                     {foreach name=blaetternavi from=$oBlaetterNaviAktiv->nBlaetterAnzahl_arr item=Blatt}
                                          <a class="page {if $oBlaetterNaviAktiv->nAktuelleSeite == $Blatt}active{/if}" href="kupons.php?s1={$Blatt}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$Blatt}</a>
                                     {/foreach}
                                
                                {if $oBlaetterNaviAktiv->nEnde != 0}
                                     ... <a class="page" href="kupons.php?s1={$oBlaetterNaviAktiv->nEnde}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviAktiv->nEnde}</a>
                                {/if}
                                <a class="next" href="kupons.php?s1={$oBlaetterNaviAktiv->nNaechste}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&raquo;</a>
                          </div>
                     {/if}
                </div>
          </div>
	 <form method="POST" action="kupons.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="del_aktive_kupons" value="1" />
		  <table class="list">
		  <thead>
				<tr>
				<th class="check"></th>
				<th class="tleft">{#name#}</th>
				<th class="tleft">{#value#}</th>
				<th class="tleft">{#code#}</th>
				<th class="th-4">{#mbw#}</th>
				<th class="th-5">{#customerGroup#}</th>
				<th class="th-6">{#restrictions#}</th>
				<th class="th-7">{#validity#}</th>
				<th></th>
				</tr>
		  </thead>
		  <tbody>
				{foreach name=aktivekupons from=$kupons_aktiv item=kupon_aktiv}
				<tr>
				<td class="check"><input name="kKupon[]" type="checkbox" value="{$kupon_aktiv->kKupon}" /></td>
				<td class="TD1">{$kupon_aktiv->cName}</td>
				<td class="TD2">{if $kupon_aktiv->cWertTyp == "prozent"}{$kupon_aktiv->fWert} %{else}{getCurrencyConversionSmarty fPreisBrutto=$kupon_aktiv->fWert}{/if}</td>
				<td class="TD3">{$kupon_aktiv->cCode}</td>
				<td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$kupon_aktiv->fMindestbestellwert}</td>
				<td class="tcenter">{$kupon_aktiv->Kundengruppe}</td>
				<td class="tcenter">{$kupon_aktiv->Artikel}</td>
				<td class="tcenter">{$kupon_aktiv->Gueltigkeit}</td>
				<td><a href="kupons.php?{$SID}&kKupon={$kupon_aktiv->kKupon}" class="button edit">bearbeiten</a></td>
				</tr>
				{/foreach}
				<tfoot>
				<tr>
				<td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
				<td colspan="8" align="left">{#globalSelectAll#}</td>
				</tr>
				</tfoot>
		  </tbody>
		  </table>
		  <p class="submit"><input name="kuponLoeschBTN" type="submit" value="{#delete#}" class="button orange"></p>
	 </form>
	 {/if}

	 {if $kupons_inaktiv|@count > 0}
	 <div class="category">{#inactiveCoupons#}</div>
	       <div class=" block clearall">
                <div class="left">
                     {if $oBlaetterNaviInaktiv->nAktiv == 1}
                          <div class="pages tright">
                                <span class="pageinfo"><strong>{$oBlaetterNaviInaktiv->nVon}</strong> - {$oBlaetterNaviInaktiv->nBis} {#from#} {$oBlaetterNaviInaktiv->nAnzahl}</span>
                                <a class="back" href="kupons.php?s2={$oBlaetterNaviInaktiv->nVoherige}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&laquo;</a>
                                {if $oBlaetterNaviInaktiv->nAnfang != 0}<a href="kupons.php?s2={$oBlaetterNaviInaktiv->nAnfang}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviInaktiv->nAnfang}</a> ... {/if}
                                     {foreach name=blaetternavi from=$oBlaetterNaviInaktiv->nBlaetterAnzahl_arr item=Blatt}
                                          <a class="page {if $oBlaetterNaviInaktiv->nAktuelleSeite == $Blatt}active{/if}" href="kupons.php?s2={$Blatt}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$Blatt}</a>
                                     {/foreach}
                                
                                {if $oBlaetterNaviInaktiv->nEnde != 0}
                                     ... <a class="page" href="kupons.php?s2={$oBlaetterNaviInaktiv->nEnde}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviInaktiv->nEnde}</a>
                                {/if}
                                <a class="next" href="kupons.php?s2={$oBlaetterNaviInaktiv->nNaechste}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&raquo;</a>
                          </div>
                     {/if}
                </div>
          </div>
	 <form method="POST" action="kupons.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="del_inaktive_kupons" value="1" />
		  <table class="list">
		  <thead>
		  <tr>
		  <th class="check"></th>
		  <th class="tleft">{#name#}</th>
		  <th class="tleft">{#value#}</th>
		  <th class="tleft">{#code#}</th>
		  <th class="th-4">{#mbw#}</th>
		  <th class="th-5">{#customerGroup#}</th>
		  <th class="th-6">{#restrictions#}</th>
		  <th class="th-7">{#validity#}</th>
		  <th></th>
		  </tr>
		  </thead>
		  <tbody>
		  {foreach name=inaktivekupons from=$kupons_inaktiv item=kupon_inaktiv}
		  <tr>
		  <td class="check"><input name="kKupon[]" type="checkbox" value="{$kupon_inaktiv->kKupon}" /></td>
		  <td class="TD1">{$kupon_inaktiv->cName}</td>
		  <td class="TD2">{if $kupon_inaktiv->cWertTyp == "prozent"}{$kupon_inaktiv->fWert} %{else}{getCurrencyConversionSmarty fPreisBrutto=$kupon_inaktiv->fWert}{/if}</td>
		  <td class="TD3">{$kupon_inaktiv->cCode}</td>
		  <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$kupon_inaktiv->fMindestbestellwert}</td>
		  <td class="tcenter">{$kupon_inaktiv->Kundengruppe}</td>
		  <td class="tcenter">{$kupon_inaktiv->Artikel}</td>
		  <td class="tcenter">{$kupon_inaktiv->Gueltigkeit}</td>
		  <td><a href="kupons.php?{$SID}&kKupon={$kupon_inaktiv->kKupon}" class="button edit">bearbeiten</a></td>
		  </tr>
		  {/foreach}
		  <tr>
		  <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
		  <td colspan="7" align="left">{#globalSelectAll#}</td>
		  </tr>
		  </tbody>
		  </table>
		  <p class="submit"><input name="kuponLoeschBTN" type="submit" value="{#delete#}"></p>
	 </form>
	 {/if}


	 <div class="category">{#newCoupon#}</div>
	 <form name="kupon_erstellen" method="post" action="kupons.php">
	 <input type="hidden" name="{$session_name}" value="{$session_id}" />
	 <input type="hidden" name="neu" value="1" />

	 <table class="list">
	 <tbody>
		  <tr>
				<td>
					 <input class="checkfield" type="radio" id="cKuponTyp" name="cKuponTyp" value="standard" checked="checked" />
					 <label for="cKuponTyp">{#standardCoupon#}</label>
				</td>
		  </tr>
		  <tr>
				<td>
					 <input class="checkfield" type="radio" id="cKuponTyp1" name="cKuponTyp" value="versandkupon" />
					 <label for="cKuponTyp1">{#shippingCoupon#}</label>
				</td>
		  </tr>
		  <tr>
				<td>
					 <input class="checkfield" type="radio" id="cKuponTyp2" name="cKuponTyp" value="neukundenkupon" />
					 <label for="cKuponTyp2">{#newCustomerCoupon#}</label>
				</td>
		  </tr>
	 </tbody>
	 </table>

	 <p class="submit">
	 <input type="submit" value="{#newCoupon#}" class="button orange" />
	 </p>
	 </form>