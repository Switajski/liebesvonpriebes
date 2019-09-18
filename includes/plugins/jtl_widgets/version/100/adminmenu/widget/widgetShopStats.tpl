<div class="widget-custom-data">
	<table class="table">
		<tr>
			<td>&nbsp;</td>
			<td><strong>Heute</strong></td>
			<td><strong>Gestern</strong></td>
		</tr>
		<!-- Umsatz -->
		<tr>
			<td>Umsatz</td>
			<td>{$oStatToday->fUmsatz}</td>
			<td>{$oStatYesterday->fUmsatz}</td>
		</tr>
		<!-- Besucher -->
		<tr>
			<td>Besucher</td>
			<td>{$oStatToday->nBesucher}</td>
			<td>{$oStatYesterday->nBesucher}</td>
		</tr>
		<!-- Neuregistrierungen -->
		<tr>
			<td>Neuregistrierungen</td>
			<td>{$oStatToday->nNeuKunden}</td>
			<td>{$oStatYesterday->nNeuKunden}</td>
		</tr>
		<!-- Anzahl Bestellungen -->
		<tr>
			<td>Anzahl Bestellungen</td>
			<td>{$oStatToday->nAnzahlBestellung}</td>
			<td>{$oStatYesterday->nAnzahlBestellung}</td>
		</tr>
		<!-- Besucher pro Bestellung -->
		<tr>
			<td>Besucher pro Bestellung</td>
			<td>{$oStatToday->nBesucherProBestellung}</td>
			<td>{$oStatYesterday->nBesucherProBestellung}</td>
		</tr>
	</table>
</div>