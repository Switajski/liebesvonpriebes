<div class="last_order_wrapper">
	<h3>Details</h3>
	<div class="last_order">
		<ul>
			<li>Bestellnummer:</li>
			<li><strong>{$oBestellung->cBestellNr}</strong></li>
		</ul>
		<ul>
			<li>Bestelldatum:</li>
			<li><strong>{$oBestellung->dErstelldatum_de}</strong></li>
		</ul>
	</div>
	{if $oBestellung->Positionen|@count > 0}
		<div class="positions">
			<br />
			<h3>Positionen</h3>
			<div class="last_order">
				{if isset($oBestellung->Positionen) && $oBestellung->Positionen|@count > 0}
					{foreach from=$oBestellung->Positionen item=Position}
						{include file=$cDetailPosition Position=$Position Bestellung=$oBestellung}
					{/foreach}
				{/if}
			</div>
		</div>
	{/if}
</div>