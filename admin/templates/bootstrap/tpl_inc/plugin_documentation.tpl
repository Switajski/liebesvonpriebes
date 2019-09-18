{literal}
<style>
    /* set some documentation-styles, different from the shop */
    div.markdown {
        padding: 0px 10px;
    }
    div.markdown ul li {
        list-style: disc outside none;
    }
    div.markdown ol li {
        list-style: decimal outside none;
    }
    div.markdown p {
        text-align: justify;
    }
    div.markdown blockquote {
        font-size: inherit;
    }
    div.markdown pre {
        overflow-wrap: break-word;
        white-space: pre-line;
        word-break: unset;
    }
    div.markdown table {
        border: 1px solid #ddd;
    }
    div.markdown thead tr th,
    div.markdown tbody tr th,
    div.markdown tfoot tr th,
    div.markdown thead tr td,
    div.markdown tbody tr td,
    div.markdown tfoot tr td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    div.markdown thead tr th,
    div.markdown thead tr td {
        border-bottom-width: 2px;
    }
</style>
{/literal}
<div class="panel panel-default">
    <div style="padding:5px 40px 30px;">
        {if $fMarkDown}
        <div class="markdown">
            {$szReadmeContent}
        </div>
        {else}
        <br>
        <pre>{$szReadmeContent}</pre>
        {/if}
    </div>
</div>
