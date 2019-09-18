{if $cFehler}
<div style="width:100%; background-color:#FF0000; color:#ffffff; font-weight:bold; text-align:center;">{$cFehler}</div>
<br />
{/if}
{if $cHinweis}
<div style="width:100%; background-color:#00FF00; color:#ffffff; font-weight:bold; text-align:center;">{$cHinweis}</div>
<br />
{/if}
<form method="post" enctype="multipart/form-data" name="settings">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Einstellungen" />
    <input type="hidden" name="stepPlugin" value="{$stepPlugin}" />
    <table>
        <tr>
            <td>Ankerpunkt für Suchvorschläge</td>
            <td>
                <select name="jtlsearch_suggest_align">
                    <option value="left" {if $oSettings_arr->jtlsearch_suggest_align == 'left'}selected{/if}>Links</option>
                    <option value="center" {if $oSettings_arr->jtlsearch_suggest_align == 'center'}selected{/if}>Mitte</option>
                    <option value="right" {if $oSettings_arr->jtlsearch_suggest_align == 'right'}selected{/if}>Rechts</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Zu exportierende Sprachen</td>
            <td>
                <select class="combo" size="5" multiple="" name="jtlsearch_export_languages[]">
                    {foreach from=$oLanguage_arr item=oExportLanguage}
                    <option value="{$oExportLanguage->cISO}" {if $oExportLanguage->cISO|in_array:$oSettings_arr->jtlsearch_export_languages}selected{/if} >{$oExportLanguage->cNameDeutsch}{if $oExportLanguage->cShopStandard == 'Y'} (Shop Standard){/if}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
    <input type="submit" name="btn_save" class="button orange" value="Speichern" />
</form>