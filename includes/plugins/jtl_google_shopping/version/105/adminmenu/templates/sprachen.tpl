<div class="alert alert-info">
    <p>Wenn Sie weitere Sprachen exportieren m&ouml;chten, m&uuml;ssen Sie dazu mehrere Export-Dateien erzeugen. Dies k&ouml;nnen Sie tun, indem Sie hier weitere Exportformate f&uuml;r die weiteren Daten-Feeds anlegen.</p>
</div>

{if !empty($cFehler)}
    <div class="alert alert-danger"><div class="box_error">{$cFehler}</div></div>
{/if}
{if !empty($cHinweis)}
    <div class="alert alert-info"><div class="box_success">{$cHinweis}</div></div>
{/if}
<table class="table">
    <tr>
        <th>Name</th>
        <th>Dateiname</th>
        <th>Sprache</th>
        <th>W&auml;hrung</th>
        <th>Kundengruppe</th>
        <th>Versandland (ISO)</th>
        <th>Aktionen</th>
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
                <td><a href="exportformate.php?action=edit&kExportformat={$oExportformat->kExportformat}" class="btn btn-default"><i class="fa fa-edit"></i> bearbeiten</a></td>
            </tr>
        {/foreach}
    {/if}
</table>
<br /><br />
<form method="post" enctype="multipart/form-data" name="export">
    <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
    <input type="hidden" name="cPluginTab" value="Weitere Datenfeeds" />
    <input type="hidden" name="stepPlugin" value="{$stepPlugin}" />

    <h3>Neue Attribute anlegen:</h3>
    <table class="table">
        <tr>
            <td><label for="cName">Feed-Name</label></td>
            <td><input class="form-control" type="text" id="cName" name="cName" value="{if isset($smarty.post.cName)}{$smarty.post.cName}{/if}" required /></td>
        </tr>
        <tr>
            <td><label for="cDateiname">Dateiname</label></td>
            <td><input class="form-control" type="text" id="cDateiname" name="cDateiname" value="{if isset($smarty.post.cDateiname)}{$smarty.post.cDateiname}{/if}" required /></td>
        </tr>
        <tr>
            <td><label for="kSprache">Sprache</label></td>
            <td>
                <select class="form-control" id="kSprache" name="kSprache" required>
                    {foreach from=$oSprache_arr item=oSprache}
                        <option value="{$oSprache->kSprache}">{$oSprache->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="kKundengruppe">Kundengruppe</label></td>
            <td>
                <select class="form-control" name="kKundengruppe" id="kKundengruppe" required>
                    {foreach from=$oKundengruppen_arr item=oKundengruppen}
                        <option value="{$oKundengruppen->kKundengruppe}">{$oKundengruppen->cName}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="kWaehrung">W&auml;hrung</label></td>
            <td>
                <select class="form-control" name="kWaehrung" id="kWaehrung" required>
                    {foreach from=$oWaehrung_arr item=oWaehrung}
                        <option value="{$oWaehrung->kWaehrung}">{$oWaehrung->cName}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="cLieferlandIso">Versandland</label></td>
            <td>
                <select class="form-control" name="cLieferlandIso" id="cLieferlandIso" required>
                    {foreach from=$cVersandlandIso_arr item=cVersandlandIso}
                        <option value="{$cVersandlandIso}">{$cVersandlandIso}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
    <button type="submit" name="btn_save_new" value="Neuen Datenfeed anlegen" class="orange btn btn-primary"><i class="fa fa-save"></i> Neuen Datenfeed anlegen</button>
</form>