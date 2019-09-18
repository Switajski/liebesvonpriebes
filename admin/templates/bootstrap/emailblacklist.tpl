{config_load file="$lang.conf" section="emailblacklist"}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#emailblacklist# cBeschreibung=#emailblacklistDesc# cDokuURL=#emailblacklistURL#}
<div id="content" class="container-fluid">
    <form method="post" action="emailblacklist.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <input type="hidden" name="emailblacklist" value="1" />
        <div id="settings">
            {assign var=open value=false}
            {foreach name=conf from=$oConfig_arr item=oConfig}
                {if $oConfig->cConf === 'Y'}
                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                        </span>
                        <span class="input-group-wrap">
                            {if $oConfig->cInputTyp === 'selectbox'}
                                <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="form-control combo">
                                    {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                        <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $oConfig->cInputTyp === 'number'}
                                <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                            {else}
                                <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{$oConfig->gesetzterWert}" tabindex="1" />
                            {/if}
                        </span>
                        {if $oConfig->cBeschreibung}
                            <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung}</span>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="panel panel-default">
                    {if $oConfig->cName}
                        <div class="panel-heading"><h3 class="panel-title">{$oConfig->cName}</h3></div>
                    {/if}
                        <div class="panel-body">
                    {assign var=open value=true}
                {/if}
            {/foreach}
            {if $open}
                    </div>
                </div>
            {/if}
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#emailblacklistEmail#} {#emailblacklistSeperator#}</h3>
            </div>
            <div class="panel-body">
                <textarea class="form-control" name="cEmail" cols="50" rows="10">{if isset($oEmailBlacklist_arr)}{foreach name=emailblacklist from=$oEmailBlacklist_arr item=oEmailBlacklist}{$oEmailBlacklist->cEmail}{if !$smarty.foreach.emailblacklist.last};{/if}{/foreach}{/if}</textarea>
            </div>
        </div>
        <div class="save_wrapper">
            <button name="speichern" type="submit" value="{#emailblacklistSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#emailblacklistSave#}</button>
        </div>
    </form>
    {if isset($oEmailBlacklistBlock_arr) && $oEmailBlacklistBlock_arr|@count > 0}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#emailblacklistBlockedEmails#}</h3>
            </div>
            <div class="panel-body">
                {foreach name=blocked from=$oEmailBlacklistBlock_arr item=entry}
                    {$entry->cEmail} ({$entry->dLetzterBlock})<br />
                {/foreach}
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}