{config_load file="$lang.conf" section='plugin'}
<div class="settings-content">
    <form method="post" action="plugin.php?kPlugin={$oPlugin->kPlugin}" class="navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$oPluginAdminMenu->kPluginAdminMenu}" />
        <input type="hidden" name="Setting" value="1" />
        {assign var=open value=0}
        {foreach name="plugineinstellungenconf" from=$oPlugin->oPluginEinstellungConf_arr item=oPluginEinstellungConf}
            {if $oPluginAdminMenu->kPluginAdminMenu == $oPluginEinstellungConf->kPluginAdminMenu}
                {foreach name="plugineinstellungen" from=$oPlugin->oPluginEinstellung_arr item=oPluginEinstellung}
                    {if $oPluginEinstellung->cName == $oPluginEinstellungConf->cWertName}
                        {assign var=cEinstellungWert value=$oPluginEinstellung->cWert}
                    {/if}
                {/foreach}
                {if $oPluginEinstellungConf->cConf === 'N'}
                    {if $open > 0}
                        </div><!-- .panel-body -->
                        </div><!-- .panel -->
                    {/if}
                    <div class="panel panel-default panel-idx-{$smarty.foreach.plugineinstellungenconf.index}{if $smarty.foreach.plugineinstellungenconf.index === 0} first{/if}">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$oPluginEinstellungConf->cName}
                            {if $oPluginEinstellungConf->cBeschreibung|strlen > 0}
                                <span class="panel-title-addon">{getHelpDesc cDesc=$oPluginEinstellungConf->cBeschreibung}</span>
                            {/if}
                        </h3>
                    </div>
                    <div class="panel-body">
                    {assign var=open value=1}
                {else}
                    {if $open === 0 && $smarty.foreach.plugineinstellungenconf.index === 0}
                        <div class="panel panel-default first">
                        <div class="panel-heading"><h3 class="panel-title">{#settings#}</h3></div>
                        <div class="panel-body">
                        {assign var=open value=1}
                    {/if}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="{$oPluginEinstellungConf->cWertName}">{$oPluginEinstellungConf->cName}</label>
                        </span>
                        <span class="input-group-wrap">
                        {if $oPluginEinstellungConf->cInputTyp === 'selectbox'}
                            <select id="{$oPluginEinstellungConf->cWertName}" name="{$oPluginEinstellungConf->cWertName}{if $oPluginEinstellungConf->cConf === 'M'}[]{/if}" class="form-control combo"{if $oPluginEinstellungConf->cConf === 'M'} multiple{/if}>
                                {foreach name="plugineinstellungenconfwerte" from=$oPluginEinstellungConf->oPluginEinstellungenConfWerte_arr item=oPluginEinstellungenConfWerte}
                                    {if $oPluginEinstellungConf->cConf === 'M' && $cEinstellungWert|is_array}
                                        {assign var=selected value=($oPluginEinstellungenConfWerte->cWert|in_array:$cEinstellungWert)}
                                    {else}
                                        {assign var=selected value=($cEinstellungWert == $oPluginEinstellungenConfWerte->cWert)}
                                    {/if}
                                    <option value="{$oPluginEinstellungenConfWerte->cWert}"{if $selected} selected{/if}>{$oPluginEinstellungenConfWerte->cName}</option>
                                {/foreach}
                            </select>
                        {elseif $oPluginEinstellungConf->cInputTyp === 'password'}
                            <input autocomplete="off" class="form-control" id="{$oPluginEinstellungConf->cWertName}" name="{$oPluginEinstellungConf->cWertName}" type="password" value="{$cEinstellungWert}" />
                        {elseif $oPluginEinstellungConf->cInputTyp === 'textarea'}
                            <textarea class="form-control" id="{$oPluginEinstellungConf->cWertName}" name="{$oPluginEinstellungConf->cWertName}">{$cEinstellungWert}</textarea>
                        {elseif $oPluginEinstellungConf->cInputTyp === 'checkbox'}
                            <div class="input-group-checkbox-wrap">
                            <input class="form-control" id="{$oPluginEinstellungConf->cWertName}" type="checkbox" name="{$oPluginEinstellungConf->cWertName}"{if $cEinstellungWert === 'on'} checked="checked"{/if}>
                        </div>
                        {elseif $oPluginEinstellungConf->cInputTyp === 'radio'}
                            <div class="input-group-checkbox-wrap">
                            {foreach name="plugineinstellungenconfwerte" from=$oPluginEinstellungConf->oPluginEinstellungenConfWerte_arr item=oPluginEinstellungenConfWerte key=i}
                                <input id="opt-{$oPluginEinstellungenConfWerte->kPluginEinstellungenConf}-{$i}" type="radio" name="{$oPluginEinstellungConf->cWertName}[]" value="{$oPluginEinstellungenConfWerte->cWert}"{if $cEinstellungWert == $oPluginEinstellungenConfWerte->cWert} checked="checked"{/if} />
                                <label for="opt-{$oPluginEinstellungenConfWerte->kPluginEinstellungenConf}-{$i}">{$oPluginEinstellungenConfWerte->cName}</label> <br />
                            {/foreach}
                        </div>
                        {else}
                            <input class="form-control" id="{$oPluginEinstellungConf->cWertName}" name="{$oPluginEinstellungConf->cWertName}" type="text" value="{$cEinstellungWert|escape:'html'}" />
                        {/if}
                        </span>
                        {if $oPluginEinstellungConf->cBeschreibung|strlen > 0}
                            <span class="input-group-addon">{getHelpDesc cDesc=$oPluginEinstellungConf->cBeschreibung}</span>
                        {/if}
                    </div>
                {/if}
            {/if}
        {/foreach}
    {if $open > 0}
        </div><!-- .panel-body -->
        </div><!-- .panel -->
    {/if}
        <button name="speichern" type="submit" value="{#pluginSettingSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#pluginSettingSave#}</button>
    </form>
</div><!-- .settings-content -->