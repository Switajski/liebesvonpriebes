<form{if !empty($name)} name="{$name}"{/if} method="{if !empty($method)}{$method}{else}post{/if}"{if !empty($action)} action="{$action}"{/if}>
    {$jtl_token}
    <input type="hidden" name="einstellungen" value="1" />
    {if !empty($a)}
        <input type="hidden" name="a" value="{$a}" />
    {/if}
    {if !empty($tab)}
        <input type="hidden" name="tab" value="{$tab}" />
    {/if}
    <div class="panel panel-default settings">
        {if !empty($title)}
            <div class="panel-heading">
                <h3 class="panel-title">{$title}</h3>
            </div>
        {/if}
        <div class="panel-body">
            {foreach name=conf from=$config item=configItem}
                {if $configItem->cConf === 'Y'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="{$configItem->cWertName}">{$configItem->cName}{if $configItem->cWertName|strpos:"_bestandskundenguthaben" || $configItem->cWertName|strpos:"_neukundenguthaben"}<span id="EinstellungAjax_{$configItem->cWertName}"></span>{/if}</label>
                        </span>
                        <span class="input-group-wrap">
                            {if $configItem->cInputTyp === 'selectbox'}
                                <select name="{$configItem->cWertName}" id="{$configItem->cWertName}" class="form-control combo">
                                    {foreach name=selectfor from=$configItem->ConfWerte item=wert}
                                        <option value="{$wert->cWert}" {if $configItem->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'listbox'}
                                <select name="{$configItem->cWertName}[]" id="{$configItem->cWertName}" multiple="multiple" class="form-control combo">
                                {foreach name=selectfor from=$configItem->ConfWerte item=wert}
                                    <option value="{$wert->kKundengruppe}" {foreach name=werte from=$configItem->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'number'}
                                <input class="form-control" type="number" step="any" name="{$configItem->cWertName}" id="{$configItem->cWertName}" value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" tabindex="1"{if $configItem->cWertName|strpos:"_bestandskundenguthaben" || $configItem->cWertName|strpos:"_neukundenguthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->cWertName}', this);"{/if} />
                            {elseif $configItem->cInputTyp === 'selectkdngrp'}
                                <select name="{$configItem->cWertName}[]" id="{$configItem->cWertName}" class="form-control combo">
                                {foreach name=selectfor from=$configItem->ConfWerte item=wert}
                                    <option value="{$wert->kKundengruppe}" {foreach name=werte from=$configItem->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'pass'}
                                <input class="form-control" type="password" name="{$configItem->cWertName}" id="{$configItem->cWertName}"  value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" />
                            {else}
                                <input class="form-control" type="text" name="{$configItem->cWertName}" id="{$configItem->cWertName}"  value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" tabindex="1"{if $configItem->cWertName|strpos:"_bestandskundenguthaben" || $configItem->cWertName|strpos:"_neukundenguthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->cWertName}', this);"{/if} />
                            {/if}
                        </span>
                        {if $configItem->cBeschreibung}
                            <span class="input-group-addon">{getHelpDesc cDesc=$configItem->cBeschreibung cID=$configItem->kEinstellungenConf}</span>
                        {/if}
                    </div>
                {/if}
            {/foreach}
        </div>
        <div class="panel-footer">
            <button name="speichern" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {if !empty($buttonCaption)}{$buttonCaption}{else}{#save#}{/if}</button>
        </div>
    </div>
</form>