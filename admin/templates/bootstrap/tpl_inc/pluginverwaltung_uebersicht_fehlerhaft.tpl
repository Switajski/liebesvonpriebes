<div id="fehlerhaft" class="tab-pane fade {if isset($cTab) && $cTab === 'fehlerhaft'} active in{/if}">
    {if isset($PluginFehlerhaft_arr) && $PluginFehlerhaft_arr|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListNotInstalledAndError#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">{#pluginName#}</th>
                            <th class="tleft">{#pluginErrorCode#}</th>
                            <th>{#pluginVersion#}</th>
                            <th>{#pluginFolder#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$PluginFehlerhaft_arr item=PluginFehlerhaft}
                            <tr>
                                <td>
                                    <strong>{if !empty($PluginFehlerhaft->cName)}{$PluginFehlerhaft->cName}{/if}</strong>
                                    <p>{if !empty($PluginFehlerhaft->cDescription)}{$PluginFehlerhaft->cDescription}{/if}</p>
                                </td>
                                <td>
                                    <p>
                                        <span class="badge error">{if !empty($PluginFehlerhaft->cFehlercode)}{$PluginFehlerhaft->cFehlercode}{/if}</span>
                                        {if !empty($PluginFehlerhaft->cFehlerBeschreibung)}{$PluginFehlerhaft->cFehlerBeschreibung}{/if}
                                    </p>
                                    {if isset($PluginFehlerhaft->shop4compatible) && $PluginFehlerhaft->shop4compatible === false}
                                        <div class="alert alert-info"><strong>Achtung:</strong> Plugin ist nicht vollst&auml;ndig Shop4-kompatibel! Es k&ouml;nnen daher Probleme beim Betrieb entstehen.</div>
                                    {/if}
                                </td>
                                <td class="tcenter">{if !empty($PluginFehlerhaft->cVersion)}{$PluginFehlerhaft->cVersion}{/if}</td>
                                <td class="tcenter">{if !empty($PluginFehlerhaft->cVerzeichnis)}{$PluginFehlerhaft->cVerzeichnis}{/if}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>