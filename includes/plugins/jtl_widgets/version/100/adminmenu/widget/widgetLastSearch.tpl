<div class="widget-custom-data">
	{if $oLastSearch_arr|@count > 0}
		<table class="table" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="width: 60%;"><strong>Suche</strong></td>
				<td align="center"><strong>Treffer</strong></td>
				<td align="center"><strong>Gesuche</strong></td>
			</tr>
			{foreach name=lastsearch from=$oLastSearch_arr item=oLastSearch}
				<tr>
					<td style="width: 60%;">{$oLastSearch->cSuche}</td>
					<td align="center">{$oLastSearch->nAnzahlTreffer}</td>
					<td align="center">{$oLastSearch->nAnzahlGesuche}</td>
				</tr>
			{/foreach}
		</table>
	{else}
		<ul class="infolist">
			<li>Keine Suchanfragen vorhanden!</li>
		</ul>
	{/if}
</div>