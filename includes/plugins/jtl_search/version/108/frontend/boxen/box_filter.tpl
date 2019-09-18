{if isset($oExtendedJTLSearchResponse)}
    <link type="text/css" href="{$oPlugin->cFrontendPfadURL}boxen/css/base.css" rel="stylesheet" />
    {*
    <strong>Solr time:</strong> {$oExtendedJTLSearchResponse->nProcessingTimeSolr} sec.<br /><br />
    <strong>Response time:</strong> {$oExtendedJTLSearchResponse->nProcessingTime} sec.<br /><br />
    <strong>Overall time:</strong> {$nOverallTime} sec.<br /><br />
    *}

    {foreach name=filtergroups from=$oExtendedJTLSearchResponse->oSearch->oFilterGroup_arr item=oFilterGroup}
        <section class="sidebox panel panel-default box box-jtl-search">
            <div class="panel-heading">
                <h5 class="panel-title">{$oFilterGroup->cMapping}</h5>
            </div>
            {if $oFilterGroup->nType == 2} {* Slider *}
                <div class="box-body panel-body sidebox_content">
                    {assign var="oFilterItem" value=$oFilterGroup->oFilterItem_arr[0]}
                    <div class="layout-slider" style="width: 100%; height: 50px; padding-top: 10px;">
                        <input id="slider_{$oFilterGroup->cName}" type="slider" name="price" value="{$oFilterItem->cStateFrom};{$oFilterItem->cStateTo}" />
                    </div>
                    <script type="text/javascript" charset="utf-8">
                        jQuery("#slider_{$oFilterGroup->cName}").slider({ldelim}
                            from: {$oFilterItem->fFrom},
                            to: {$oFilterItem->fTo},
                            limits: false,
                            step: {$oFilterItem->fStep},
                            scale: [
                                {foreach name=scale from=$oFilterItem->nScale_arr key=key item=nScale}
                                {if $key % 2 == 0}
                                {$nScale}{if !$smarty.foreach.scale.last}, {/if}
                                {else}
                                '|'{if !$smarty.foreach.scale.last}, {/if}
                                {/if}
                                {/foreach}
                            ],
                            dimension: '&nbsp;{$oFilterItem->cUnit}',
                            min: {$oFilterItem->fSolrFrom},
                            max: {$oFilterItem->fSolrTo},
                            skin: "round_plastic",
                            smooth: true,
                            callback: function (value) {ldelim}
                                var xScale_arr = value.split(';');
                                window.location.href = '{$oFilterItem->cURL}&fq{$nStatedFilterCount}={$oFilterGroup->cName}:{$oFilterItem->fFrom}_{$oFilterItem->fTo}-' + xScale_arr[0] + '_' + xScale_arr[1];
                            {rdelim}
                        {rdelim});
                    </script>
                </div>
            {elseif $oFilterGroup->nType == 3} {* Colorbox *}
                <div class="box-body panel-body sidebox_content">
                    {foreach name=filteritems from=$oFilterGroup->oFilterItem_arr item=oFilterItem}
                        <div class="color{if $oFilterItem->bSet} active{/if}">
                            <a rel="nofollow" href="{$oFilterItem->cURL}" class="color {$oFilterItem->cValue}" title="{$oFilterItem->cValue} ({$oFilterItem->nCount})">
                                <span>{$oFilterItem->cValue}</span>
                            </a>
                        </div>
                    {/foreach}
                </div>
            {elseif $oFilterGroup->nType == 4} {* Tinybox *}
                <div class="box-body panel-body sidebox_content">
                    {foreach name=filteritems from=$oFilterGroup->oFilterItem_arr item=oFilterItem}
                        <div class="tiny">
                            <a rel="nofollow" href="{$oFilterItem->cURL}" class="tiny{if $oFilterItem->bSet} active{/if}" title="{$oFilterItem->cValue} ({$oFilterItem->nCount})">
                                <span>{$oFilterItem->cValue}</span>
                            </a>
                        </div>
                    {/foreach}
                </div>
            {else} {* Checkbox *}
                <div class="box-body sidebox_content">
                    <ul class="nav nav-list filter_state">
                        <li class="filter-label">{$oFilterGroup->cMapping}</li>
                        {foreach name=filteritems from=$oFilterGroup->oFilterItem_arr item=oFilterItem}
                            <li>
                                <a rel="nofollow" href="{$oFilterItem->cURL}"{if $oFilterItem->bSet} class="active"{/if} title="{$oFilterItem->cValue}">
                                    <i class="fa fa-{if $oFilterItem->bSet}check-{/if}square-o text-muted"></i> {$oFilterItem->cValue}
                                    <span class="badge count">({$oFilterItem->nCount})</span>
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        </section>
    {/foreach}
{/if}