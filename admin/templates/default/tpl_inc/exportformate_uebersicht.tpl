{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: exporte_uebersicht.tpl, smarty template inc file

	admin page for JTL-Shop 3

	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2008 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#exportformats# cBeschreibung=#exportformatsDesc# cDokuURL=#exportformatsURL#}
<div id="content">

	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}

	<script type="text/javascript" src="{$currentTemplateDir}js/jquery.progressbar.js"></script>
	<script type="text/javascript">
		var url = "{$URL_SHOP}/{$PFAD_ADMIN}/exportformate.php";
		var tpl = "{$URL_SHOP}/{$PFAD_ADMIN}/{$currentTemplateDir}/gfx/jquery";

		{literal}

		$(function () {
			$('#exportall').click(function () {
				$('.extract_async').trigger('click');
				return false;
			});
		});

		function init_export(id) {
			$.getJSON(url, {action: "export", kExportformat: id, ajax: "1"}, function (cb) {
				do_export(cb);
			});
			return false;
		}

		function do_export(cb) {
			if (typeof cb != 'object') {
				error_export();
			}
			else if (cb.bFinished) {
				finish_export(cb);
			}
			else {
				show_export_info(cb);
				$.getJSON(cb.cURL, {action: "export", e: cb.kExportqueue, back: "admin", ajax: "1"}, function (cb) {
					do_export(cb);
				});
			}
		}

		function error_export(cb) {
			alert('Es ist ein Fehler beim Erstellen der Exportdatei aufgetreten');
		}

		function show_export_info(cb) {
			var elem = '#progress' + cb.kExportformat;
			$(elem).find('p').hide();
			$(elem).find('div').fadeIn();
			$(elem).find('div').progressBar(cb.nCurrent, {
				max:          cb.nMax,
				textFormat:   'fraction',
				steps:        cb.bFirst ? 0 : 20,
				stepDuration: cb.bFirst ? 0 : 20,
				boxImage: tpl + '/progressbar.gif',
				barImage:     {
					0: tpl + '/progressbg_red.gif',
					30: tpl + '/progressbg_orange.gif',
					50: tpl + '/progressbg_yellow.gif',
					70: tpl + '/progressbg_green.gif'
				}
			});
		}

		function finish_export(cb) {
			var elem = '#progress' + cb.kExportformat;
			$(elem).find('div').fadeOut(250, function () {
				var text = $(elem).find('p').html();
				$(elem).find('p').html(text).fadeIn(1000);
			});
		}

		{/literal}
	</script>

	<div class="container">
		<form name="verarbeite_exportaktion" method="post" action="exportformate.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="exportaction" value="1" />

			<div id="kupon">
				<table>
					<thead>
					<tr>
						<th class="tleft">{#name#}</th>
						<th class="tleft" style="width:320px">{#filename#}</th>
						<th class="tcenter">{#languageCurrencyCustGrp#}</th>
						<th class="tcenter">{#lastModified#}</th>
						<th class="tcenter">{#actions#}</th>
					</tr>
					</thead>
					<tbody>
					{foreach name=exportformate from=$exportformate item=exportformat}
						{if $exportformat->nSpecial == 0}
							<tr>
								<td class="tleft"> {$exportformat->cName}</td>
								<td class="tleft" id="progress{$exportformat->kExportformat}">
									<p>{$exportformat->cDateiname}</p>

									<div></div>
								</td>
								<td class="tcenter">{$exportformat->Sprache->cNameDeutsch}
									/ {$exportformat->Waehrung->cName} / {$exportformat->Kundengruppe->cName}</td>
								<td class="tcenter">{$exportformat->dZuletztErstellt}</td>
								<td class="tcenter">
									<a href="exportformate.php?action=export&kExportformat={$exportformat->kExportformat}" class="button extract notext" title="{#createExportFile#}"></a>
									{if !$exportformat->bPluginContentExtern}
										<a href="#" onclick="return init_export('{$exportformat->kExportformat}');" class="button extract_async notext" title="{#createExportFileAsync#}"></a>
									{/if}
									<a href="exportformate.php?action=download&kExportformat={$exportformat->kExportformat}" class="button download notext" title="{#download#}"></a>
									<a href="exportformate.php?action=edit&kExportformat={$exportformat->kExportformat}" class="button edit notext" title="{#edit#}"></a>
									<a href="exportformate.php?action=delete&kExportformat={$exportformat->kExportformat}" class="button remove notext" title="{#delete#}" onclick="return confirm('Exportformat löschen?');"></a>
								</td>
							</tr>
						{/if}
					{/foreach}
					</tbody>
				</table>
			</div>
			<p class="submit">
				<a class="button orange" href="exportformate.php?neuerExport=1&{$SID}">{#newExportformat#}</a>
				<a class="button orange" href="#" id="exportall">Alle exportieren</a>
			</p>
		</form>
	</div>
</div>