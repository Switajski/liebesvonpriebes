<ul class="filter_state nav nav-list">
    {foreach name=suchfilter from=$NaviFilter->SuchFilter item=oSuchFilter}
        {assign var=kSuchanfrage value=$oSuchFilter->kSuchanfrage}
        <li>
            <a rel="nofollow" href="{$NaviFilter->URL->cAlleSuchFilter[$kSuchanfrage]}" class="active">
                <span class="badge pull-right">{$oSuchFilter->nAnzahl}</span>
                <span class="value">
                    <i class="fa fa-square-o text-muted"></i> {$oSuchFilter->cSuche}
                </span>
            </a>
        </li>
    {/foreach}
</ul>