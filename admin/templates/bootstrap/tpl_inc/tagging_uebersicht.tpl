{include file='tpl_inc/seite_header.tpl' cTitel=#tagging# cBeschreibung=#taggingDesc# cDokuURL=#taggingURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="tagging.php" class="inline_block">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon"><label for="kSprache">{#changeLanguage#}</label></span>
                <span class="input-group-wrap last">
                    <select id="kSprache" name="kSprache" class="selectBox form-control" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'freischalten' || $cTab === 'tags'} active{/if}">
            <a data-toggle="tab" role="tab" href="#freischalten">{#tags#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'mapping'} active{/if}">
            <a data-toggle="tab" role="tab" href="#mapping">{#mapping#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#taggingSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="freischalten" class="tab-pane fade{if !isset($cTab) || $cTab === 'freischalten' || $cTab === 'tags'} active in{/if}">
            {if $Tags && $Tags|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiTags cAnchor='freischalten'}
                <form name="login" method="post" action="tagging.php">
                    {$jtl_token}
                    <input type="hidden" name="tagging" value="1" />
                    <input type="hidden" name="tab" value="tags" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#tags#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="th-1"></th>
                                    <th class="tleft">{#tag#}</th>
                                    <th class="th-3">{#tagcount#}</th>
                                    <th class="th-4">{#active#}</th>
                                    <th class="th-5">{#mapping#}</th>
                                </tr>
                                {foreach name=tags from=$Tags item=tag}
                                    <tr class="tab_bg{$smarty.foreach.tags.iteration%2}">
                                        <td class="TD1">
                                            <input type="checkbox" name="kTag[]" value="{$tag->kTag}" />
                                            <input name="kTagAll[]" type="hidden" value="{$tag->kTag}" />
                                        </td>
                                        <td class="">
                                            <a href="tagging.php?tagdetail=1&kTag={$tag->kTag}&tab=tags&token={$smarty.session.jtl_token}">{$tag->cName}</a>
                                        </td>
                                        <td class="tcenter">{$tag->Anzahl}</td>
                                        <td class="tcenter">
                                            <input type="checkbox" name="nAktiv[]" value="{$tag->kTag}" {if $tag->nAktiv==1}checked{/if} />
                                        </td>
                                        <td class="tcenter">
                                            <input class="form-control fieldOther" type="text" name="mapping_{$tag->kTag}" />
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="update" type="submit" value="{#update#}" class="btn btn-default"><i class="fa fa-refresh"></i> {#update#}</button>
                                <button name="delete" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#delete#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="mapping" class="tab-pane fade{if isset($cTab) && $cTab === 'mapping'} active in{/if}">
            {if $Tagmapping && $Tagmapping|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiTagMappings cAnchor='mapping'}
                <form name="login" method="post" action="tagging.php">
                    {$jtl_token}
                    <input type="hidden" name="tagging" value="2" />
                    <input type="hidden" name="tab" value="mapping" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#mapping#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="th-1"></th>
                                    <th class="th-2">{#tag#}</th>
                                    <th class="th-3">{#tagnew#}</th>
                                </tr>
                                {foreach name=tagsmapping from=$Tagmapping item=tagmapping}
                                    <tr class="tab_bg{$smarty.foreach.tagsmapping.iteration%2}">
                                        <td class="TD1">
                                            <input name="kTagMapping[]" type="checkbox" value="{$tagmapping->kTagMapping}" />
                                        </td>
                                        <td class="tcenter">{$tagmapping->cName}</td>
                                        <td class="tcenter">{$tagmapping->cNameNeu}</td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="check">
                                        <input name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                    </td>
                                    <td colspan="2" class="TD7"><label for="ALLMSGS1">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <button name="delete" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#delete#}</button>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='tagging.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>