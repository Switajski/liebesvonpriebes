<script>
    function pagiResort (pagiId, nSortBy, nSortDir)
    {
        $('#' + pagiId + '_nSortByDir').val(nSortBy * 2 + nSortDir);
        $('form#' + pagiId).submit();
        return false;
    }
</script>

{function sortControls}
    {if $oPagination->getSortBy() !== $nSortBy}
        <a href="#" onclick="return pagiResort('{$oPagination->getId()}', {$nSortBy}, 0);"><i class="fa fa-unsorted"></i></a>
    {elseif $oPagination->getSortDirSpecifier() === 'DESC'}
        <a href="#" onclick="return pagiResort('{$oPagination->getId()}', {$nSortBy}, 0);"><i class="fa fa-sort-desc"></i></a>
    {elseif $oPagination->getSortDirSpecifier() === 'ASC'}
        <a href="#" onclick="return pagiResort('{$oPagination->getId()}', {$nSortBy}, 1);"><i class="fa fa-sort-asc"></i></a>
    {/if}
{/function}