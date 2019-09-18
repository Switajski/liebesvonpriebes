<div class="widget-custom-data">
	{if $oTop10Search_arr|@count > 0}
		<ol class="infolist">
			{foreach name=top10search from=$oTop10Search_arr item=oTop10Search}
				<li{if $smarty.foreach.top10search.first} class="first"{elseif $smarty.foreach.top10search.last} class="last"{/if}>
					<span class="key">{$oTop10Search->cSuche}</span> <span class="badge value">{$oTop10Search->nAnzahlGesuche}</span>
				</li>
			{/foreach}
		</ol>
	{else}
		<div class="alert alert-info">
			<p>Keine Suchanfragen vorhanden.</p>
		</div>
	{/if}
</div>