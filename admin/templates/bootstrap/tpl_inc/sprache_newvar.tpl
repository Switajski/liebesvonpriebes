{include file='tpl_inc/seite_header.tpl' cTitel=#lang# cBeschreibung=#langDesc# cDokuURL=#langURL#}
<div id="content" class="container-fluid">
    <div class="panel panel-default settings">
        <div class="panel-heading">
            <h3 class="panel-title">{#newLangVar#}</h3>
        </div>
        <form action="sprache.php" method="post">
            {$jtl_token}
            <input type="hidden" name="tab" value="{$tab}">
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="kSprachsektion">{#langSection#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select class="form-control" name="kSprachsektion" id="kSprachsektion">
                            {foreach $oSektion_arr as $oSektion}
                                <option value="{$oSektion->kSprachsektion}"
                                        {if $oVariable->kSprachsektion === (int)$oSektion->kSprachsektion}selected{/if}>
                                    {$oSektion->cName}
                                </option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName">{#variableName#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="cName" id="cName" value="{$oVariable->cName}">
                    </span>
                </div>
                {foreach $oSprache_arr as $oSprache}
                    {if isset($oVariable->cWertAlt_arr[$oSprache->cISO])}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="bOverwrite_{$oSprache->cISO}_yes">
                                    <input type="radio" id="bOverwrite_{$oSprache->cISO}_yes"
                                           name="bOverwrite_arr[{$oSprache->cISO}]" value="1">
                                    {$oSprache->cNameDeutsch} ({#new#})
                                </label>
                            </span>
                            <span class="input-group-wrap">
                                <input type="text" class="form-control" name="cWert_arr[{$oSprache->cISO}]"
                                       id="cWert_{$oSprache->cISO}" value="{$oVariable->cWert_arr[$oSprache->cISO]}">
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="bOverwrite_{$oSprache->cISO}_no">
                                    <input type="radio" id="bOverwrite_{$oSprache->cISO}_no"
                                           name="bOverwrite_arr[{$oSprache->cISO}]" value="0" checked>
                                    {$oSprache->cNameDeutsch} ({#current#})
                                </label>
                            </span>
                                <span class="input-group-wrap">
                                <input type="text" class="form-control" name="cWertAlt_arr[{$oSprache->cISO}]" disabled
                                       id="cWertAlt_{$oSprache->cISO}"
                                       value="{$oVariable->cWertAlt_arr[$oSprache->cISO]}">
                            </span>
                        </div>
                    {else}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cWert_{$oSprache->cISO}">
                                    {$oSprache->cNameDeutsch}
                                </label>
                            </span>
                            <span class="input-group-wrap">
                                <input type="text" class="form-control" name="cWert_arr[{$oSprache->cISO}]"
                                       id="cWert_{$oSprache->cISO}" value="{$oVariable->cWert_arr[$oSprache->cISO]}">
                            </span>
                        </div>
                    {/if}
                {/foreach}
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" name="action" value="savevar">
                        <i class="fa fa-save"></i>
                        {#save#}
                    </button>
                    <a href="sprache.php?tab={$tab}" class="btn btn-danger">{#goBack#}</a>
                </div>
            </div>
        </form>
    </div>
</div>