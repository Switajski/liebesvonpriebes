{include file="tpl_inc/seite_header.tpl" cTitel=Cache cBeschreibung=''}
{literal}
<style type="text/css">
	span.inactive{
		color: red;
	}
	span.active{
		color: green;
	}
	ol.affacted-tables {
		list-style-type: decimal-leading-zero;
	}
	ol.affacted-tables li{
		list-style-position: inside;
		padding-left: 5px;
	}
	pre{
		background: lightgray;
		font-size: 11px;
	}
	.submit-wrapper{
		overflow: hidden;
		margin-bottom: 10px;
	}
	.submit-form{
		float: left;
		margin-right: 10px;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('#massaction-main-switch').toggle(
			function () {
				$('.massaction-checkbox').attr('checked','checked');
			},
			function () {
				$('.massaction-checkbox').removeAttr('checked');
			}
		);
		$('#btn_toggle_cache').click(function() {
			$("#row_toggle_cache").slideToggle('slow', 'linear');
		});
	});
</script>
{/literal}
<div id="content">
	{if isset($notice) && $notice|count_characters > 0}
		<p class="box_success">{$notice}</p>
	{/if}
	{if isset($error) && $error|count_characters > 0}
		<p class="box_error">{$error}</p>
	{/if}
	<div class="tabber">
		<div class="tabbertab{if $tab === 'massaction'} tabbertabdefault{/if}">
			<h2>Cacheverwaltung</h2>
			<div class="submit-wrapper">
				<form method="post" action="cache.php" class="submit-form">
					<input name="a" type="hidden" value="clearPageCache" />
					<input name="submit" type="submit" value="Seiten-Cache leeren" class="button remove" />
				</form>
				<form method="post" action="cache.php" class="submit-form">
					<input name="a" type="hidden" value="clearAll" />
					<input name="submit" type="submit" value="Objekt-Cache leeren" class="button delete" />
				</form>
			</div>
			<div class="clear"></div>
			<form method="post" action="cache.php">
				<table id="cache-type-status" class="list">
					<thead>
					<tr>
						<th class="tleft"><input type="checkbox" class="massaction-checkbox" id="massaction-main-switch" value=""></th>
						<th class="tleft">Art</th>
						<th class="tleft">Beschreibung</th>
						<th class="tleft">Status</th>
					</tr>
					</thead>
					<tbody>
					<tr class="odd">
						<td><input type="checkbox" class="massaction-checkbox" value="article" name="cache-types[]"></td>
						<td>Artikel</td>
						<td>Enth&auml;lt Produktdaten</td>
						<td>{if 'article'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="even">
						<td><input type="checkbox" class="massaction-checkbox" value="category" name="cache-types[]"></td>
						<td>Kategorien</td>
						<td>Enth&auml;lt Kategoriedaten</td>
						<td>{if 'category'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="odd">
						<td><input type="checkbox" class="massaction-checkbox" value="option" name="cache-types[]"></td>
						<td>Optionen</td>
						<td>Enth&auml;lt globale Einstellungen</td>
						<td>{if 'option'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="odd">
						<td><input type="checkbox" class="massaction-checkbox" value="plugin" name="cache-types[]"></td>
						<td>Plugins</td>
						<td>Enth&auml;lt Plugindaten</td>
						<td>{if 'plugin'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="even">
						<td><input type="checkbox" class="massaction-checkbox" value="language" name="cache-types[]"></td>
						<td>Sprache</td>
						<td>Enth&auml;lt Sprachvariablen</td>
						<td>{if 'language'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="odd">
						<td><input type="checkbox" class="massaction-checkbox" value="core" name="cache-types[]"></td>
						<td>Core</td>
						<td>Enth&auml;lt JTL-eigene Daten</td>
						<td>{if 'core'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="even">
						<td><input type="checkbox" class="massaction-checkbox" value="object" name="cache-types[]"></td>
						<td>Objekte</td>
						<td>Enth&auml;lt generelle Objekte</td>
						<td>{if 'object'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="odd">
						<td><input type="checkbox" class="massaction-checkbox" value="news" name="cache-types[]"></td>
						<td>News</td>
						<td>Enth&auml;lt News-Daten</td>
						<td>{if 'news'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					<tr class="even">
						<td><input type="checkbox" class="massaction-checkbox" value="template" name="cache-types[]"></td>
						<td>Template</td>
						<td>Enth&auml;lt Template-Einstellungen</td>
						<td>{if 'template'|in_array:$disabled_caches}<span class="inactive">inaktiv</span>{else}<span class="active">aktiv</span>{/if}</td>
					</tr>
					</tbody>
				</table>
				<br />
				<div class="clear"></div>
				<select name="cache-action" id="cache-action">
					<option name="flush" value="flush">leeren</option>
					<option name="deaktivieren" value="deactivate">deaktivieren</option>
					<option name="aktivieren" value="activate">aktivieren</option>
				</select>
				<input name="a" type="hidden" value="cacheMassAction" />
				<input type="submit" value="speichern" class="button blue" />
			</form>
		</div>

		<div class="tabbertab">
			<h2>Cache-Statistik</h2>
			{if is_array($stats) && $stats|@count > 0}
				{if isset($stats.uptime_h) && $stats.uptime_h !== null}
					<p class="box_info">Uptime: {$stats.uptime_h}</p>
				{/if}
				{if isset($stats.mem) && $stats.mem !== null}
					<p class="box_info">Komplette Gr&ouml;&szlig;e: {$stats.mem} Bytes ({$stats.mem/1024/1024|string_format:"%.2f"} MB)</p>
				{/if}
				{if isset($stats.entries) && $stats.entries !== null}
					<p class="box_info">Anzahl Eintr&auml;ge: {$stats.entries}</p>
				{/if}
				{if isset($stats.misses) && $stats.misses !== null}
					<p class="box_info">Misses: {$stats.misses}
						{if isset($stats.mps) && $stats.mps !== null}
							<span class="inline"> ({$stats.mps|string_format:"%.2f"} Misses/s)</span>
						{/if}
					</p>
				{/if}
				{if isset($stats.hits) && $stats.hits !== null}
					<p class="box_info">Hits: {$stats.hits}
					{if isset($stats.hps) && $stats.hps !== null}
						<span class="inline"> ({$stats.hps|string_format:"%.2f"} Hits/s)</span>
					{/if}
					</p>
				{/if}
				{if isset($stats.inserts) && $stats.inserts !== null}
					<p class="box_info">Inserts: {$stats.inserts}</p>
				{/if}
				{if isset($stats.slow) && is_array($stats.slow)}
					<h4>Slowlog</h4>
					<br />
					{if $stats.slow|@count > 0}
					{foreach name=slowlog from=$stats.slow key=type item=slow}
						<p class="box_info">{$slow.date}: {$slow.cmd} ({$slow.exec_time}s)</p>
					{/foreach}
					{else}
						<p class="box_info">Keine Eintr&auml;ge</p>
					{/if}
				{/if}
			{else}
				<p class="box_info">Keine Statistiken vorhanden</p>
			{/if}
		</div>
		{if isset($all_methods) && $all_methods|@count > 0}
			<div class="tabbertab{if $tab === 'benchmark'} tabbertabdefault{/if}">
				<h2>Benchmark</h2>
				<form method="post" action="cache.php">
					<label for="runcount">Durchl&auml;ufe</label>
					<input type="text" name="runcount" id="runcount" value="1000" size="5" />
					<label for="repeat">Wiederholungen</label>
					<input type="text" name="repeat" id="repeat" value="1" size="2" />
					<br />
					<label for="testdata">Testdaten</label>
					<select name="testdata" id="testdata">
						<option value="array">Array</option>
						<option value="object">Objekt</option>
						<option value="string">String</option>
					</select>
					<label for="methods">Methoden</label>
					<select name="methods[]" id="methods" multiple style="margin-top: 5px;">
						{foreach from=$all_methods item=method}
							<option value="{$method}">{$method}</option>
		                {/foreach}
					</select>
					<input name="a" type="hidden" value="benchmark" />
					<input name="submit" type="submit" value="Benchmark starten" class="button orange" />
				</form>
				{if isset($bench_results)}
					{if is_array($bench_results)}
						<br />
						{foreach from=$bench_results key=resultsKey item=result}
							{if isset($result.method)}
								<div class="bench-result box_info">
									<p><span class="label">Methode: </span> <span class="text">{$result.method}</span></p>
									<p><span class="label">Status: </span> <span class="text">{$result.status}</span></p>
									<p><span class="label">Zeit get: </span>
										{if $result.status !== 'failed' && $result.status !== 'invalid'}
											<span class="text">{$result.timings.get}s</span> <span class="text">({$result.rps.get} Eintr&auml;ge/s)</span>
										{else}
											<span class="text">-</span>
										{/if}
									</p>
									<p><span class="label">Zeit set: </span>
										{if $result.status !== 'failed' && $result.status !== 'invalid'}
											<span class="text">{$result.timings.set}s</span> <span class="text">({$result.rps.set} Eintr&auml;ge/s)</span>
										{else}
											<span class="text">-</span>
										{/if}
									</p>
								</div>
							{/if}
						{/foreach}
					{else}
						<p class="box_info">Konnte Benchmark nicht ausf&uuml;hren.</p>
					{/if}
				{/if}
			</div>
		{/if}
		<div class="tabbertab{if $tab === 'settings'} tabbertabdefault{/if}">
			<h2>Einstellungen</h2>
			<form method="post" action="cache.php">
				<input type="hidden" name="a" value="settings">
				<input name="tab" type="hidden" value="settings">
				<div class="settings">
					{foreach name=conf from=$settings item=setting}
						{if $setting->cConf === "Y"}
							<p><label for="{$setting->cWertName}">{$setting->cName}
								{if $setting->cBeschreibung}
									<img src="{$currentTemplateDir}gfx/help.png" alt="{$setting->cBeschreibung}" title="{$setting->cBeschreibung}" style="vertical-align:middle; cursor:help;" />
								{/if}
								</label>
								{if $setting->cInputTyp=="selectbox"}
									<select name="{$setting->cWertName}" id="{$setting->cWertName}" class="combo">
										{foreach name=selectfor from=$setting->ConfWerte item=wert}
											<option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
										{/foreach}
									</select>
								{else}
								<input type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
								{/if}
							</p>
						{else}
							{if $setting->cName}<h3 style="text-align:center;">{$setting->cName}</h3>{/if}
						{/if}
					{/foreach}
					<a id="btn_toggle_cache" class="button down" style="margin: 10px 0;">Erweiterte Optionen anzeigen</a>
					<div id="row_toggle_cache" style="display: none;">
						{foreach name=conf from=$advanced_settings item=setting}
							{if $setting->cConf === "Y"}
								<p><label for="{$setting->cWertName}">{$setting->cName}
										{if $setting->cBeschreibung}
											<img src="{$currentTemplateDir}gfx/help.png" alt="{$setting->cBeschreibung}" title="{$setting->cBeschreibung}" style="vertical-align:middle; cursor:help;" />
										{/if}
									</label>
									{if $setting->cInputTyp=="selectbox"}
										<select name="{$setting->cWertName}" id="{$setting->cWertName}" class="combo">
											{foreach name=selectfor from=$setting->ConfWerte item=wert}
												<option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
											{/foreach}
										</select>
									{else}
										<input type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
									{/if}
								</p>
							{else}
								{if $setting->cName}<h3 style="text-align:center;">{$setting->cName}</h3>{/if}
							{/if}
						{/foreach}
						{if isset($expert_settings) && $expert_settings !== null}
							{foreach name=conf from=$expert_settings item=setting}
								{if $setting->cConf === "Y"}
									<p><label for="{$setting->cWertName}">{$setting->cName}
											{if $setting->cBeschreibung}
												<img src="{$currentTemplateDir}gfx/help.png" alt="{$setting->cBeschreibung}" title="{$setting->cBeschreibung}" style="vertical-align:middle; cursor:help;" />
											{/if}
										</label>
										{if $setting->cInputTyp=="selectbox"}
											<select name="{$setting->cWertName}" id="{$setting->cWertName}" class="combo">
												{foreach name=selectfor from=$setting->ConfWerte item=wert}
													<option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
												{/foreach}
											</select>
										{else}
											<input type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
										{/if}
									</p>
								{else}
									{if $setting->cName}<h3 style="text-align:center;">{$setting->cName}</h3>{/if}
								{/if}
							{/foreach}
						{/if}
					</div>
				</div>

				<p class="submit"><input name="speichern" type="submit" value="{#save#}" class="button orange" /></p>
			</form>
		</div>
		{if isset($sql_profiler_data)}
			<div class="tabbertab{if $tab === 'sqlprofiler'} tabbertabdefault{/if}">
				<h2>SQL-Debugging</h2>
				{if is_array($sql_profiler_data)}
					<p><span class="label">Queries: </span> <span class="text"> {$sql_profiler_data.query_count}</span></p>
					<p><span class="label">Single Selects: </span> <span class="text"> {$sql_profiler_data.single_selects}</span></p>
					<p><span class="label">Updates: </span> <span class="text"> {$sql_profiler_data.updates}</span></p>
					<p><span class="label">Inserts: </span> <span class="text"> {$sql_profiler_data.inserts}</span></p>
					<p><span class="label">Betroffene Tabellen:</span></p>
					<ol class="affacted-tables">
					{foreach name=ac from=$sql_profiler_data.affected_tables item=count key=table}
						<li class="a-table"><strong>{$table}</strong> ({$count}) </li>
					{/foreach}
					</ol>
					<p><span class="label">Top 20 Statements:</span></p>
					<ol class="affacted-tables statements">
						{foreach name=sm from=$sql_profiler_data.statements item=stmt key=sid}
							<li class="a-table"><strong>{$stmt.count}</strong>
							<span class="sql"><pre>{$stmt.sql}</pre></span>
							</li>
						{/foreach}
					</ol>
					<form method="post" id="reset-sql-stats" action="cache.php">
						<input type="hidden" name="a" value="reset-sql-stats">
						<input name="tab" type="hidden" value="sqldebug">
						<p class="submit"><input name="reset-sql-stats" type="submit" value="Statistik zur&uuml;cksetzen" class="button orange" /></p>
					</form>
				{else}
					<span class="info">Keine Daten vorhanden.</span>
				{/if}
			</div>
		{/if}
	</div>
</div>