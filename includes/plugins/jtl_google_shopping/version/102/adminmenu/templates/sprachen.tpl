<p>Wenn Sie weitere Sprachen Exportieren m&ouml;chten, m&uuml;ssen Sie dazu mehrere Export-Dateien erzeugen. Dies können Sie indem Sie hier weitere Exportformate für die weiteren Daten-Feeds anlegen.</p>

{if $cFehler}
    <div class="box_error">{$cFehler}</div>
    <br />
{/if}
{if $cHinweis}
    <div class="box_success">{$cHinweis}</div>
    <br />
{/if}
<table style="width: 1000px;">
    <tr>
        <td>Name</td>
        <td>Dateiname</td>
        <td>Sprache</td>
        <td>Währung</td>
        <td>Kundengruppe</td>
        <td>Versandland (ISO)</td>
        <td>Aktionen</td>
    </tr>
    {if $oExportformate}
        {foreach name=Exportformat from=$oExportformate item=oExportformat}
            <tr>
                <td>{$oExportformat->cName}</td>
                <td>{$oExportformat->cDateiname}</td>
                <td>{$oExportformat->cSprache}</td>
                <td>{$oExportformat->cWaehrung}</td>
                <td>{$oExportformat->cKundengruppe}</td>
                <td>{$oExportformat->cLieferlandIso}</td>
                <td><a href="exportformate.php?action=edit&kExportformat={$oExportformat->kExportformat}">bearbeiten</a></td>
            </tr>
        {/foreach}
    {/if}
</table>
<br /><br />
<form method="post" enctype="multipart/form-data" name="export">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Weitere Datenfeeds" />
    <input type="hidden" name="stepPlugin" value="{$stepPlugin}" />

    <b>Neue Attribute anlegen:</b><br />
    <table style="width: 1000px;">
        <tr>
            <td><label for="cName">Feed-Name</label></td>
            <td><input type="text" name="cName" value="{$smarty.post.cName}" /></td>
        </tr>
        <tr>
            <td><label for="cDateiname">Dateiname</label></td>
            <td><input type="text" name="cDateiname" value="{$smarty.post.cDateiname}" /></td>
        </tr>
        <tr>
            <td><label for="kSprache">Sprache</label></td>
            <td>
                <select name="kSprache">
                    {foreach from=$oSprache_arr item=oSprache}
                        <option value="{$oSprache->kSprache}">{$oSprache->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="kKundengruppe">Kundengruppe</label></td>
            <td>
                <select name="kKundengruppe">
                    {foreach from=$oKundengruppen_arr item=oKundengruppen}
                        <option value="{$oKundengruppen->kKundengruppe}">{$oKundengruppen->cName}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="kWaehrung">W&auml;hrung</label></td>
            <td>
                <select name="kWaehrung">
                    {foreach from=$oWaehrung_arr item=oWaehrung}
                        <option value="{$oWaehrung->kWaehrung}">{$oWaehrung->cName}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="cLieferlandIso">Versandland</label></td>
            <td>
                <select name="cLieferlandIso">
                    {foreach from=$cVersandlandIso_arr item=cVersandlandIso}
                        <option value="{$cVersandlandIso}">{$cVersandlandIso}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
    <input type="submit" name="btn_save_new" value="Neuen Datenfeed anlegen" class="orange" />
</form>