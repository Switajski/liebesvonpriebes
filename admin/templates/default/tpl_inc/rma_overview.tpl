{if isset($oRMA_arr) && $oRMA_arr|@count > 0}
<script type="text/javascript">
{literal}
function setRMAStatus(kRMA, kRMAStatus)
{
    myCallback = xajax.callback.create();
	myCallback.onComplete = function(obj) {
        data = obj.context.response;
		console.log(data);
    }
    xajax.call('setRMAStatusAjax', { parameters: [kRMA, kRMAStatus], callback: myCallback, context: this } );
	return false;
}
{/literal}
</script>

<div id="rma_overview">
	<table class="list">
		<thead>
			<tr>
				<th class="tleft">{#rmaNumber#}</th>
				<th class="tleft">{#rmaStatus#}</th>
				<th class="tcenter">{#rmaCustomer#}</th>
				<th class="tcenter">{#rmaProductCount#}</th>
				<th class="tcenter">{#rmaBuild#}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		{foreach name=rmas from=$oRMA_arr item=oRMA}
			<tr>
				<td><strong>{$oRMA->getRMANumber()}</strong></td>
				<td style="width: 150px;">
					<form id="rma_form_{$oRMA->getRMA()}" method="POST" action="plugin.php?kPlugin={$oPlugin->kPlugin}">
						<div id="rma_status_{$oRMA->getRMA()}">{$oRMA->oRMAStatus->getStatus()}</div>
						<div id="rma_select_{$oRMA->getRMA()}" style="display: none;">
							<select name="nStatus" style="padding: 0px; font-size: 0.8em;">
							{foreach name=rmastatus from=$oRMAStatus_arr item=oRMAStatus}
								<option value="{$oRMAStatus->getRMAStatus()}">{$oRMAStatus->getStatus()}</option>
							{/foreach}
							</select>						
						</div>            		
					</form>
				</td>
				<td class="tcenter">{$oRMA->oKunde->cVorname} {$oRMA->oKunde->cNachname}</td>
				<td class="tcenter">{$oRMA->oRMAArtikel_arr|@count}</td>
				<td class="tcenter">{$oRMA->getErstellt()}</td>
				<td class="tcenter"><button id="more_info_{$oRMA->getRMA()}" class="button">weitere Informationen</button></td>
			</tr>
			<tr id="rma_info_{$oRMA->getRMA()}" style="display: none;">
				<td colspan="6">
					<br />
					<strong>Artikel:</strong><br />
					<ul style="padding: 0 0 0 60px;">
				{foreach name=products from=$oRMA->oRMAArtikel_arr item=oRMAArtikel}
						<li style="list-style-type: disc;">
							<a href="{$oRMAArtikel->cArtikelURL}" target="_blank">{$oRMAArtikel->cArtikelName}</a>
							<br /><small>Grund: {$oRMAArtikel->getGrund()}</small>
						{if $oRMAArtikel->getKommentar()|count_characters > 0}
							<br /><small>Kommentar: {$oRMAArtikel->getKommentar()}</small>
						{/if}
						</li>
				{/foreach}
					</ul>
					<br />
					<strong>Kunde:</strong><br />
				{if isset($oRMA->oKunde->cFirma) && $oRMA->oKunde->cFirma|count_characters > 0}
					{$oRMA->oKunde->cFirma}<br />
				{/if}
					{$oRMA->oKunde->cVorname} {$oRMA->oKunde->cNachname}<br />
					{$oRMA->oKunde->cStrasse} {$oRMA->oKunde->cHausnummer}<br />
					{$oRMA->oKunde->cPLZ} {$oRMA->oKunde->cOrt}<br />
					{$oRMA->oKunde->angezeigtesLand}<br />
					{$oRMA->oKunde->cMail}<br />
				</td>
			</tr>
			<script>
				$("#more_info_{$oRMA->getRMA()}").click(function() {ldelim}
					if($("#rma_info_{$oRMA->getRMA()}").is(":hidden"))
						$("#rma_info_{$oRMA->getRMA()}").slideDown("slow");
					else
						$("#rma_info_{$oRMA->getRMA()}").slideUp("slow");
				{rdelim});

				$("#rma_status_{$oRMA->getRMA()}").click(function() {ldelim}
					$("#rma_select_{$oRMA->getRMA()}").css("display", "block");
					$("#rma_status_{$oRMA->getRMA()}").css("display", "none");
				{rdelim});

				$("#rma_status_{$oRMA->getRMA()}").mouseover(function() {ldelim}
					$(this).css("cursor", "pointer");
				{rdelim});
				
				$("#rma_select_{$oRMA->getRMA()}").change(function() {ldelim}
					$("#rma_status_{$oRMA->getRMA()}").html($("#rma_select_{$oRMA->getRMA()} option:selected").text());
					$("#rma_select_{$oRMA->getRMA()}").css("display", "none");
					$("#rma_status_{$oRMA->getRMA()}").css("display", "block");
					setRMAStatus({$oRMA->getRMA()}, $("#rma_select_{$oRMA->getRMA()} option:selected").val());										
				{rdelim});
			</script>
		{/foreach}
		</tbody>
	</table>
</div>
{else}
	<br/>{#noDataAvailable#}<br/><br/>
{/if}