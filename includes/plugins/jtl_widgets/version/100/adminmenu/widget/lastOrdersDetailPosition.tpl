<ul>
	<li>
		{if $Position->nPosTyp==1}
			<a href="{$URL_SHOP}/index.php?a={$Position->kArtikel}" target="_blank">{$Position->cName}</a>
			{if $Position->cUnique|strlen > 0 && $Position->kKonfigitem == 0}
				<ul class="children_ex">
					{foreach from=$Bestellung->Positionen item=KonfigPos}
						{if $Position->cUnique == $KonfigPos->cUnique}
							<li>{if !($KonfigPos->cUnique|strlen > 0 && $KonfigPos->kKonfigitem == 0)}{$KonfigPos->nAnzahlEinzel}x {/if}{$KonfigPos->cName}
								<span class="price">{$KonfigPos->cEinzelpreisLocalized[1]}</span>
							</li>
						{/if}
					{/foreach}
				</ul>
			{/if}
			{foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
				<br>
				<span>
					{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
					{if $WKPosEigenschaft->fAufpreis}
						{$WKPosEigenschaft->cAufpreisLocalized[1]}
					{/if}
                </span>
			{/foreach}
		{else}
			{$Position->cName}
			{if $Position->cHinweis|strlen > 0}
				<p>
					<small>{$Position->cHinweis}</small>
				</p>
			{/if}
		{/if}
	</li>
	<li>
		{$Position->cEinzelpreisLocalized[1]}
	</li>
</ul>