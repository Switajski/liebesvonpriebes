{if $cFehler}
	<div style="width:100%; background-color:#FF0000; color:#ffffff; font-weight:bold; text-align:center;">{$cFehler}</div>
	<br />
{/if}
{if $cHinweis}
	<div style="width:100%; background-color:#00FF00; color:#ffffff; font-weight:bold; text-align:center;">{$cHinweis}</div>
	<br />
{/if}
<form method="post" enctype="multipart/form-data" name="settings">
	{$jtl_token}
	<input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
	<input type="hidden" name="cPluginTab" value="Einstellungen" />
	<input type="hidden" name="stepPlugin" value="{$stepPlugin}" />
	<div class="panel panel-default">
		<div class="panel-body">
			<div class="item input-group">
				<span class="input-group-addon">
					<label for="jtlsearch_suggest_align">Ankerpunkt für Suchvorschl&auml;ge</label>
				</span>
				<span class="input-group-wrap">
					<select id="jtlsearch_suggest_align" class="form-control" name="jtlsearch_suggest_align">
						<option value="left"{if isset($oSettings_arr->jtlsearch_suggest_align) && $oSettings_arr->jtlsearch_suggest_align === 'left'} selected="selected"{/if}>Links</option>
						<option value="center"{if isset($oSettings_arr->jtlsearch_suggest_align) && $oSettings_arr->jtlsearch_suggest_align === 'center'} selected="selected"{/if}>Mitte</option>
						<option value="right"{if isset($oSettings_arr->jtlsearch_suggest_align) && $oSettings_arr->jtlsearch_suggest_align === 'right'} selected="selected"{/if}>Rechts</option>
					</select>
				</span>
			</div>
			<div class="item input-group">
				<span class="input-group-addon">
					<label for="jtlsearch_export_languages">zu exportierende Sprachen</label>
				</span>
				<span class="input-group-wrap">
					<select id="jtlsearch_export_languages" class="form-control combo" size="5" multiple="" name="jtlsearch_export_languages[]">
						{foreach from=$oLanguage_arr item=oExportLanguage}
							<option value="{$oExportLanguage->cISO}" {if $oExportLanguage->cISO|in_array:$oSettings_arr->jtlsearch_export_languages}selected{/if} >{$oExportLanguage->cNameDeutsch}{if $oExportLanguage->cShopStandard == 'Y'} (Shop Standard){/if}</option>
						{/foreach}
					</select>
				</span>
			</div>
		</div>
	</div>
	<button type="submit" name="btn_save" class="btn btn-primary button orange" value="Speichern">Speichern</button>
</form>