{function sprache_buttons}
    <div class="btn-group">
        {if $oWert_arr|@count > 0}
            <button type="submit" class="btn btn-primary" name="action" value="saveall">
                <i class="fa fa-save"></i>
                {#save#}
            </button>
        {/if}
        <a class="btn btn-default" href="sprache.php?token={$smarty.session.jtl_token}&action=newvar">
            <i class="fa fa-share"></i>
            {#btnAddVar#}
        </a>
        {if $oWert_arr|@count > 0}
            {include file='tpl_inc/csv_export_btn.tpl' exporterId="langvars"}
        {/if}
        {include file='tpl_inc/csv_import_btn.tpl' importerId="langvars" bCustomStrategy=true}
    </div>
{/function}
{include file='tpl_inc/seite_header.tpl' cTitel=#lang# cBeschreibung=#langDesc# cDokuURL=#langURL#}
{assign var="cSearchString" value=$oFilter->getField(1)->getValue()}
{assign var="bAllSections" value=((int)$oFilter->getField(0)->getValue() === 0)}
<script>
    function toggleTextarea(kSektion, cWertName)
    {
        $('#cWert_' + kSektion + '_' + cWertName).show();
        $('#cWert_caption_' + kSektion + '_' + cWertName).hide();
        $('#bChanged_' + kSektion + '_' + cWertName).val('1');
    }
    function resetVarText(kSektion, cWertName, cStandard)
    {
        $('#cWert_' + kSektion + '_' + cWertName).val($('#cStandard_' + kSektion + '_' + cWertName).text());
        toggleTextarea(kSektion, cWertName);
    }
</script>
<div id="content" class="container-fluid">
    <div class="block">
        <form method="post" action="sprache.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1">
            <div class="input-group p25">
                <div class="input-group-addon">
                    <label for="kSprache">Sprache:</label>
                </div>
                <select id="kSprache" name="kSprache" class="form-control" onchange="this.form.submit();">
                    {foreach $oSprache_arr as $oSprache}
                        <option value="{$oSprache->kSprache}"
                                {if (int)$smarty.session.kSprache === (int)$oSprache->kSprache}selected{/if}
                                {if !$oSprache->bImported}class="alert-success"{/if}>
                            {$oSprache->cNameDeutsch}
                            {if $oSprache->cShopStandard === 'Y'}(Standard){/if}
                        </option>
                    {/foreach}
                </select>
            </div>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab {if $tab === 'variables'}active{/if}">
            <a data-toggle="tab" href="#variables">{#langVars#}</a>
        </li>
        <li class="tab {if $tab === 'notfound'}active{/if}">
            <a data-toggle="tab" href="#notfound">{#notFoundVars#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="variables" class="tab-pane fade {if $tab === 'variables'}active in{/if}">
            <div class="panel panel-default">
                {if $bSpracheAktiv}
                    {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
                    {include file='tpl_inc/pagination.tpl' oPagination=$oPagination}
                {/if}
                <form action="sprache.php" method="post">
                    {$jtl_token}
                    {if $oWert_arr|@count > 0}
                        <div class="table-responsive">
                            <table class="list table">
                                <thead>
                                    <tr>
                                        {if $bAllSections}<th>{#section#}</th>{/if}
                                        <th>{#variableName#}</th>
                                        <th>{#variableContent#}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $oWert_arr as $oWert}
                                        <tr>
                                            {if $bAllSections}<td>{$oWert->cSektionName}</td>{/if}
                                            <td onclick="toggleTextarea({$oWert->kSprachsektion}, '{$oWert->cName}');"
                                                style="cursor:pointer;">
                                                <label for="cWert_{$oWert->kSprachsektion}_{$oWert->cName}">
                                                    {if $cSearchString !== ''}
                                                        {$oWert->cName|regex_replace:"/($cSearchString)/i":"<mark>\$1</mark>"}
                                                    {else}
                                                        {$oWert->cName}
                                                    {/if}
                                                </label>
                                            </td>
                                            <td onclick="toggleTextarea({$oWert->kSprachsektion}, '{$oWert->cName}');"
                                                style="cursor:pointer;">
                                                <span id="cWert_caption_{$oWert->kSprachsektion}_{$oWert->cName}">
                                                    {if $cSearchString !== ''}
                                                        {$oWert->cWert|escape|regex_replace:"/($cSearchString)/i":"<mark>\$1</mark>"}
                                                    {else}
                                                        {$oWert->cWert|escape}
                                                    {/if}
                                                </span>
                                                <textarea id="cWert_{$oWert->kSprachsektion}_{$oWert->cName}" class="form-control"
                                                          name="cWert_arr[{$oWert->kSprachsektion}][{$oWert->cName}]"
                                                          style="display:none;">{$oWert->cWert|escape}</textarea>
                                                <input type="hidden" id="bChanged_{$oWert->kSprachsektion}_{$oWert->cName}"
                                                       name="bChanged_arr[{$oWert->kSprachsektion}][{$oWert->cName}]"
                                                       value="0">
                                                <span style="display:none;"
                                                      id="cStandard_{$oWert->kSprachsektion}_{$oWert->cName}">{$oWert->cStandard|escape}</span>
                                            </td>
                                            <td style="width:6em;">
                                                <div class="btn-group right">
                                                    <button type="button" class="btn btn-default"
                                                            onclick="resetVarText({$oWert->kSprachsektion},
                                                                                  '{$oWert->cName}');">
                                                        <i class="fa fa-refresh"></i>
                                                    </button>
                                                    {if $oWert->bSystem === '0'}
                                                        <a href="sprache.php?token={$smarty.session.jtl_token}&action=delvar&kSprachsektion={$oWert->kSprachsektion}&cName={$oWert->cName}"
                                                           class="btn btn-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    {/if}
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {elseif $bSpracheAktiv}
                        <div class="alert alert-info" role="alert">{#noFilterResults#}</div>
                    {else}
                        <div class="alert alert-info" role="alert">{#notImportedYet#}</div>
                    {/if}
                    <div class="panel-footer">
                        {sprache_buttons}
                    </div>
                </form>
            </div>
        </div>
        <div id="notfound" class="tab-pane fade {if $tab === 'notfound'}active in{/if}">
            <div class="panel panel-default">
                {if $oNotFound_arr|@count > 0}
                    <table class="list table">
                        <thead>
                            <tr>
                                <th>{#section#}</th>
                                <th>{#variableName#}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $oNotFound_arr as $oWert}
                                <tr>
                                    <td>{$oWert->cSektion}</td>
                                    <td>{$oWert->cName}</td>
                                    <td>
                                        <div class="btn-group right">
                                            <a href="sprache.php?token={$smarty.session.jtl_token}&action=newvar&kSprachsektion={$oWert->kSprachsektion}&cName={$oWert->cName}&tab=notfound"
                                               class="btn btn-default" title="erstellen">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
                <div class="panel-footer">
                    <div class="btn-group">
                        <a href="sprache.php?token={$smarty.session.jtl_token}&action=clearlog&tab=notfound" class="btn btn-danger">
                            <i class="fa fa-refresh"></i>
                            {#btnResetLog#}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>