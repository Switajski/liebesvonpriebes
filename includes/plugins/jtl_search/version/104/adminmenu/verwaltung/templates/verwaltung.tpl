<div>
    {if $cBaseCssURL|count_characters > 0}
    <link media="screen" href="{$cBaseCssURL}" type="text/css" rel="stylesheet" />
    {/if}
    
    {foreach from=$cStatusModulAssoc_arr item=cStatusModulAssoc}
    <div class="jtlsearch_status_box">
        <div class="jtlsearch_inner">
            {if $cStatusModulAssoc.cCssURL|count_characters > 0}
            <link media="screen" href="{$cStatusModulAssoc.cCssURL}" type="text/css" rel="stylesheet" />
            {/if}
            <fieldset>
                <legend>{$cStatusModulAssoc.cName}</legend>
                <div class="jtlsearch_wrapper">
                    {$cStatusModulAssoc.cContent}
                </div>
            </fieldset>
        </div>
    </div>
    {/foreach}
    <script type="text/javascript">
        
        $(function() {ldelim}
            $('.jtlsearch_wrapper').each(function() {ldelim}
                var heightActionColumn = $(this).children('.jtlsearch_actioncolumn').height();
                var heightInfoColumn = $(this).children('.jtlsearch_infocolumn').height();
                
                if(heightActionColumn > heightInfoColumn) {ldelim}
                    $(this).children('.jtlsearch_infocolumn').height(heightActionColumn);
                {rdelim} else {ldelim}
                    $(this).children('.jtlsearch_actioncolumn').height(heightInfoColumn);
                {rdelim}
            {rdelim});
        {rdelim});
        
    </script>
</div>