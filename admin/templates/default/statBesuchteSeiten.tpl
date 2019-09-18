{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: statBesuchteSeiten.tpl, smarty template inc file
	
	sales statistics page for JTL-Shop 3 
	Admin
	
	Author: niclas@jtl-software.de, JTL-Software
	Edited by: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="statistics"}
<div id="page">
	<div id="content">
		<div id="welcome" class="post">
			<h2 class="title"><span>{#vititsSitesTitle#}</span></h2>

			<div class="content">
				<p>{#vititsSitesDesc#}</p>
			</div>
		</div>
		<div class="container">
			<form name="produkte_anzeigen" method="post" action="">
				<div id="payment">
					<fieldset>
						<legend>{#filters#}</legend>
						<p><label for="anzahl">{#siteOptions#}</label>
							<select name="anzahl" id="anzahl" class="combo" style="width:150px">
								<option value="10" {if $anzahl=="10"}selected="selected"{/if}>10</option>
								<option value="25" {if $anzahl=="25"}selected="selected"{/if}>25</option>
								<option value="50" {if $anzahl=="50"}selected="selected"{/if}>50</option>
								<option value="100" {if $anzahl=="100"}selected="selected"{/if}>100</option>
							</select> {#cells#}</p>
						<p><label for="order_by">{#sortby#}</label>
							<select name="order_by" id="order_by" class="combo" style="width:150px">
								<option value="AR.kArtikel" {if $order_by=="AR.kArtikel"}selected="selected"{/if}>{#sortby1#}</option>
								<option value="SA.cSuche" {if $order_by=="SA.cSuche"}selected="selected"{/if}>{#sortby2#}</option>
								<option value="TA.cName" {if $order_by=="TA.cName"}selected="selected"{/if}>{#sortby3#}</option>
								<option value="HE.cName" {if $order_by=="HE.cName"}selected="selected"{/if}>{#sortby4#}</option>
								<option value="KAT.kKategorie" {if $order_by=="KAT.kKategorie"}selected="selected"{/if}>{#sortby5#}</option>
								<option value="LI.kLink" {if $order_by=="LI.kLink"}selected="selected"{/if}>{#sortby6#}</option>
								<option value="NE.kNews" {if $order_by=="NE.kNews"}selected="selected"{/if}>{#sortby8#}</option>
								<option value="NEM.kNewsMonatsUebersicht" {if $order_by=="NEM.kNewsMonatsUebersicht"}selected="selected"{/if}>{#sortby9#}</option>
								<option value="NEK.kNewsKategorie" {if $order_by=="NEK.kNewsKategorie"}selected="selected"{/if}>{#sortby10#}</option>
							</select></p>
					</fieldset>
				</div>
				<p class="submit">
					<input onclick="document.getElementById('not_new').value='1'; return false;" type="submit" name="show" value="{#submit#}" />
					<input type="submit" name="export" value="{#export#}" /></p>
			</form>
		</div>

		<div id="example-1" class="post">
			<div id="statisticTable">
				<img src="includes/diagramm.php"><br /><br /><br />
				<hr>
				<br />
				{if $filter=="artikel"}
					<table>
						<thead>
						<tr>
							<th class="th-1">{#sortby7#}</th>
							{if $order_by == "AR.kArtikel"}
								<th class="th-2">{#sortby1#}</th>{/if}
							{if $order_by == "SA.cSuche"}
								<th class="th-2">{#sortby2#}</th>{/if}
							{if $order_by == "TA.cName"}
								<th class="th-2">{#sortby3#}</th>{/if}
							{if $order_by == "HE.cName"}
								<th class="th-2">{#sortby4#}</th>{/if}
							{if $order_by == "KAT.kKategorie"}
								<th class="th-2">{#sortby5#}</th>{/if}
							{if $order_by == "LI.kLink"}
								<th class="th-2">{#sortby6#}</th>{/if}
							{if $order_by == "NE.kNews"}
								<th class="th-2">{#sortby8#}</th>{/if}
							{if $order_by == "NEM.kNewsMonatsUebersicht"}
								<th class="th-2">{#sortby9#}</th>{/if}
							{if $order_by == "NEK.kNewsKategorie"}
								<th class="th-2">{#sortby10#}</th>{/if}
						</tr>
						</thead>
						<tbody>
						{foreach name=query from=$arQuery item=row}
							<tr class="tab_bg{$smarty.foreach.query.iteration%2}">
								<td class="TD1">{$row->N_BESUCHTE_SEITEN}</td>
								{if $order_by == "AR.kArtikel"}
									<td class="TD2"><a href="../{$row->AR_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "SA.cSuche"}
									<td class="TD2"><a href="../{$row->SA_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "TA.cName"}
									<td class="TD2"><a href="../{$row->SA_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "HE.cName"}
									<td class="TD2"><a href="../{$row->SA_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "KAT.kKategorie"}
									<td class="TD2"><a href="../{$row->SA_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "LI.kLink"}
									<td class="TD2"><a href="../{$row->KAT_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}

								{if $order_by == "NE.kNews"}
									<td class="TD2"><a href="../{$row->NE_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "NEM.kNewsMonatsUebersicht"}
									<td class="TD2"><a href="../{$row->NEM_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
								{if $order_by == "NEK.kNewsKategorie"}
									<td class="TD2"><a href="../{$row->NEK_SEO}" rel="external">{$row->cWert}</a>
									</td>{/if}
							</tr>
						{/foreach}
						</tbody>
					</table>
				{/if}

			</div>
		</div>

{include file='tpl_inc/footer.tpl'}