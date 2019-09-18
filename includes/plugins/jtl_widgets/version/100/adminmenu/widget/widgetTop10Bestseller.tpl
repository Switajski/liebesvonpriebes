<div class="widget-custom-data">
	{if $oTop10Bestseller_arr|@count > 0}
		<ul class="infolist">
			{foreach name=top10bestseller from=$oTop10Bestseller_arr item=oTop10Bestseller}
				<li{if $smarty.foreach.top10bestseller.first} class="first"{elseif $smarty.foreach.top10bestseller.last} class="last"{/if}>
					<p class="key">{$oTop10Bestseller->cName}
						<span class="value">{$oTop10Bestseller->fAnzahl|string_format:"%.0f"}</span></p>
				</li>
			{/foreach}
		</ul>
	{else}
		<div class="alert alert-info" role="alert">
			<p>Keine Bestseller vorhanden.</p>
		</div>
	{/if}
</div>