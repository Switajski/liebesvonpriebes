<div class="pagination_container row">
    <div class="pagination">
        <ul>
            {if 1 != $currentPage}
                <li><a href="marktplatz.php?{if isset($params)}{$params}{/if}page={$currentPage-1}">&laquo; Zur&uuml;ck</a>
                </li>
            {else}
                <li><a class="disabled" onclick="return false;" href="#">&laquo; Zur&uuml;ck</a></li>
            {/if}
            {section name="i" start=1 loop=$data->nSeitenzahlen+1 step=1}
                {if $currentPage == $smarty.section.i.index}
                    <li>
                        <a class="active" href="marktplatz.php?{if isset($params)}{$params}{/if}page={$smarty.section.i.index}">{$smarty.section.i.index}</a>
                    </li>
                {else}
                    <li>
                        <a href="marktplatz.php?{if isset($params)}{$params}{/if}page={$smarty.section.i.index}">{$smarty.section.i.index}</a>
                    </li>
                {/if}
            {/section}
            {if $data->nSeitenzahlen != $currentPage}
                <li>
                    <a href="marktplatz.php?{if isset($params)}{$params}{/if}page={$currentPage+1}">Weiter &raquo;</a>
                </li>
            {else}
                <li>
                    <a class="disabled" onclick="return false;" href="#">Weiter &raquo;</a>
                </li>
            {/if}
        </ul>
    </div>
</div>