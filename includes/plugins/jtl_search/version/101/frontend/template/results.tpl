{if $cSearch_arr|@count > 0}
<div id="result_set">
    <div class="result_row_wrapper clearall">
        <div class="result_row first">
            <p>Suche nach</p>
            <a href="#" rel="{$cSearch|escape:'html'}">{$cSearch}</a>

            {if $oSearchResponse->oSuggest->cSuggest|@count_characters > 0 && $oSearchResponse->oSuggest->nForwarding == 1}
                <p>Meinten Sie</p>
                <a href="#" rel="{$oSearchResponse->oSuggest->cSuggest|escape:'html'}">{$oSearchResponse->oSuggest->cSuggest}</a>
            {/if}

            {include file="`$cTemplatePath`result_item.tpl" cType=$cSearch_arr.landingpage cName="Seiten"}
            {include file="`$cTemplatePath`result_item.tpl" cType=$cSearch_arr.query cName="Suchbegriffe"}
            {include file="`$cTemplatePath`result_item.tpl" cType=$cSearch_arr.category cName="Kategorievorschläge"}
            {include file="`$cTemplatePath`result_item.tpl" cType=$cSearch_arr.manufacturer cName="Herstellervorschläge"}
        </div>
        <div class="result_row">
            {include file="`$cTemplatePath`result_item.tpl" cType=$cSearch_arr.product cName="Produktvorschläge"}
        </div>
    </div>
    <div class="result_copy"></div>
</div>
{/if}