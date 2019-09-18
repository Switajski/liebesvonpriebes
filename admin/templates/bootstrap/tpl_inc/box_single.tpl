<li class="list-group-item {if $oBox->kContainer > 0}boxRowContainer{/if}">
    <div class="row">
        {if $oBox->bContainer}
            <div class="col-sm-8 col-xs-12{if $oBox->bAktiv == 0} inactive text-muted{/if}">
                <b>Container #{$oBox->kBox}</b>
            </div>
        {else}
            <div class="col-sm-3 col-xs-4{if $oBox->bAktiv == 0} inactive text-muted{/if}
                        {if $oBox->kContainer > 0}boxSubName{/if}">
                {$oBox->cTitel}
            </div>
            <div class="col-sm-2 col-xs-3{if $oBox->bAktiv == 0} inactive text-muted{/if}">
                {$oBox->eTyp|ucfirst}
            </div>
            <div class="col-sm-3 col-xs-4{if $oBox->bAktiv == 0} inactive text-muted{/if}">
                {$oBox->cName}
            </div>
        {/if}
        <div class="col-sm-2 col-xs-6 {if $oBox->kContainer > 0}boxSubName{/if}">
            <input type="hidden" name="box[]" value="{$oBox->kBox}">
            {if $nPage == 0}
                {if $oBox->bAktiv == 1}
                    <input type="hidden" name="aktiv[]" value="{$oBox->kBox}">
                {/if}
            {else}
                <input class="left" style="margin-right: 5px;" type="checkbox" name="aktiv[]"
                       {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}">
            {/if}
            <input class="form-control text-right" type="number" size="3" name="sort[]" value="{$oBox->nSort}"
                   autocomplete="off" id="{$oBox->nSort}">
        </div>
        <div class="col-sm-2 col-xs-6 btn-group">
            {if $nPage == 0}
                {if $oBox->bAktiv == 0}
                    <input type="hidden" name="box_show" value="0" />
                    <a href="boxen.php?action=activate&position={$position}&item={$oBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                       title="Auf jeder Seite aktivieren" class="btn btn-success">
                        <i class="fa fa-eye"></i>
                    </a>
                {else}
                    <input type="hidden" name="box_show" value="1" />
                    <a href="boxen.php?action=activate&position={$position}&item={$oBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                       title="Auf jeder Seite deaktivieren" class="btn btn-warning">
                        <i class="fa fa-eye-slash"></i>
                    </a>
                {/if}
            {/if}
            <a href="boxen.php?action=del&page={$nPage}&position={$position}&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
               onclick="return confirmDelete('{if $oBox->bContainer}Container #{$oBox->kBox}{else}{$oBox->cTitel}{/if}');"
               title="{#remove#}" class="btn btn-danger">
                <i class="fa fa-trash"></i>
            </a>
            <a href="boxen.php?action=edit_mode&page={$nPage}&position={$position}&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
               title="{#edit#}" class="btn btn-default
                    {if !isset($oBox->eTyp) || ($oBox->eTyp !== 'text' && $oBox->eTyp !== 'link' && $oBox->eTyp !== 'catbox')}disabled{/if}">
                <i class="fa fa-edit"></i>
            </a>
            {if $oBox->kContainer == 0}
                {if $nPage == 0}
                    <button type="button" data-toggle="tooltip" data-html="true" data-placement="left" data-container="body"
                            title="{#visibleOnPages#}: {$oBox->cVisibleOn|replace:"\n":"<br>"}" class="btn btn-info">
                        <i class="fa fa-info"></i>
                    </button>
                {elseif $nPage == 1 || $nPage == 2 || $nPage == 24 || $nPage == 31}
                    {if $nPage == 1}
                        {assign var="picker" value="articlePicker"}
                    {elseif $nPage == 2}
                        {assign var="picker" value="categoryPicker"}
                    {elseif $nPage == 24}
                        {assign var="picker" value="manufacturerPicker"}
                    {elseif $nPage == 31}
                        {assign var="picker" value="pagePicker"}
                    {/if}
                    <input type="hidden" id="box-filter-{$oBox->kBox}" name="box-filter-{$oBox->kBox}"
                           value="{foreach $oBox->cFilter as $filter}{$filter.id}{if !$filter@last},{/if}{/foreach}">
                    <button type="button" class="btn btn-default"
                            onclick="openFilterPicker({$picker}, {$oBox->kBox})">
                        <i class="fa fa-filter"></i>
                    </button>
                {/if}
            {/if}
        </div>
    </div>
    {if $oBox->kContainer == 0}
        <ul class="box-active-filters" id="box-active-filters-{$oBox->kBox}">
            {if !empty($oBox->cFilter)}
                {foreach name="filters" from=$oBox->cFilter item=filter}
                    {if $filter !== ''}
                        <li class="selected-item"><i class="fa fa-filter"></i> {$filter.name}</li>
                    {/if}
                {/foreach}
            {/if}
        </ul>
    {/if}
</li>