{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

{assign var=cUrlAppend value=$cParam_arr|http_build_query}

{if isset($cAnchor)}
    {assign var=cUrlAppend value=$cUrlAppend|cat:'#'|cat:$cAnchor}
{/if}

{assign var=bItemsAvailable value=$oPagination->getItemCount() > 0}
{assign var=bMultiplePages value=$oPagination->getPageCount() > 1}
{assign var=bSortByOptions value=$oPagination->getSortByOptions()|@count > 0}

{function pageButtons}
    <label>
        {if $bMultiplePages}
            Eintr&auml;ge {$oPagination->getFirstPageItem() + 1}
            - {$oPagination->getFirstPageItem() + $oPagination->getPageItemCount()}
            von {$oPagination->getItemCount()}
        {else}
            Eintr&auml;ge gesamt:
        {/if}
    </label>
    {if $bMultiplePages}
        <ul class="pagination">
            <li>
                <a {if $oPagination->getPrevPage() != $oPagination->getPage()}href="?{$oPagination->getId()}_nPage={$oPagination->getPrevPage()}&{$cUrlAppend}"{/if}>&laquo;</a>
            </li>
            {if $oPagination->getLeftRangePage() > 0}
                <li>
                    <a href="?{$oPagination->getId()}_nPage=0&{$cUrlAppend}">1</a>
                </li>
            {/if}
            {if $oPagination->getLeftRangePage() > 1}
                <li>
                    <a>&hellip;</a>
                </li>
            {/if}
            {for $i=$oPagination->getLeftRangePage() to $oPagination->getRightRangePage()}
                <li{if $oPagination->getPage() == $i} class="active"{/if}>
                    <a href="?{$oPagination->getId()}_nPage={$i}&{$cUrlAppend}">{$i+1}</a>
                </li>
            {/for}
            {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 2}
                <li>
                    <a>&hellip;</a>
                </li>
            {/if}
            {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 1}
                <li>
                    <a href="?{$oPagination->getId()}_nPage={$oPagination->getPageCount() - 1}&{$cUrlAppend}">{$oPagination->getPageCount()}</a>
                </li>
            {/if}
            <li>
                <a {if $oPagination->getNextPage() != $oPagination->getPage()}href="?{$oPagination->getId()}_nPage={$oPagination->getNextPage()}&{$cUrlAppend}"{/if}>&raquo;</a>
            </li>
        </ul>
    {else}
        <ul class="pagination">
            <li>
                <a>{$oPagination->getItemCount()}</a>
            </li>
        </ul>
    {/if}
{/function}

{function itemsPerPageOptions}
    <label for="{$oPagination->getId()}_nItemsPerPage">Eintr&auml;ge/Seite</label>
    <select class="form-control" name="{$oPagination->getId()}_nItemsPerPage" id="{$oPagination->getId()}_nItemsPerPage"
            onchange="this.form.submit()">
        {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
            <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected="selected"{/if}>
                {if $nItemsPerPageOption === -1}
                    alle
                {else}
                    {$nItemsPerPageOption}
                {/if}
            </option>
        {/foreach}
    </select>
{/function}

{function sortByDirOptions}
    <label for="{$oPagination->getId()}_nSortByDir">Sortierung</label>
    <select class="form-control" name="{$oPagination->getId()}_nSortByDir" id="{$oPagination->getId()}_nSortByDir"
            onchange="this.form.submit()">
        {foreach $oPagination->getSortByOptions() as $i => $cSortByOption}
            <option value="{$i * 2}"
                    {if $i * 2 == $oPagination->getSortByDir()} selected="selected"{/if}>
                {$cSortByOption[1]} aufsteigend
            </option>
            <option value="{$i * 2 + 1}"
                    {if $i * 2 + 1 == $oPagination->getSortByDir()} selected="selected"{/if}>
                {$cSortByOption[1]} absteigend
            </option>
        {/foreach}
    </select>
{/function}

{if $bItemsAvailable}
    <div class="toolbar well well-sm">
        <div class="container-fluid toolbar-container">
            <div class="row toolbar-row">
                <div class="col-md-{if $bSortByOptions}8{else}10{/if} toolbar-col">
                    {pageButtons}
                </div>
                <div class="col-md-{if $bSortByOptions}4{else}2{/if} toolbar-col">
                    <form action="{if isset($cAnchor)}#{$cAnchor}{/if}" method="get" name="{$oPagination->getId()}" id="{$oPagination->getId()}">
                        {foreach $cParam_arr as $cParamName => $cParamValue}
                            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
                        {/foreach}
                        <div class="row toolbar-row">
                            <div class="col-md-{if $bSortByOptions}4{else}12{/if} toolbar-col">
                                {itemsPerPageOptions}
                            </div>
                            {if $bSortByOptions}
                                <div class="col-md-8 toolbar-col">
                                    {sortByDirOptions}
                                </div>
                            {/if}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/if}