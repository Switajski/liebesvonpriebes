{if !empty($cFehler)}
    <div class="alert alert-danger">{$cFehler}</div>
{/if}

{include file="productwizard/index.tpl"}

{if isset($StartseiteBoxen) && $StartseiteBoxen|@count > 0}
    <hr>
    {assign var='moreLink' value=null}
    {assign var='moreTitle' value=null}
    {foreach name=startboxen from=$StartseiteBoxen item=Box}
        {if isset($Box->Artikel->elemente) && count($Box->Artikel->elemente)>0 && isset($Box->cURL)}
            {if $Box->name === 'TopAngebot'}
                {lang key="topOffer" section="global" assign='title'}
                {lang key='showAllTopOffers' section='global' assign='moreTitle'}
            {elseif $Box->name === 'Sonderangebote'}
                {lang key="specialOffer" section="global" assign='title'}
                {lang key='showAllSpecialOffers' section='global' assign='moreTitle'}
            {elseif $Box->name === 'NeuImSortiment'}
                {lang key="newProducts" section="global" assign='title'}
                {lang key='showAllNewProducts' section='global' assign='moreTitle'}
            {elseif $Box->name === 'Bestseller'}
                {lang key="bestsellers" section="global" assign='title'}
                {lang key='showAllBestsellers' section='global' assign='moreTitle'}
            {/if}
            {assign var='moreLink' value=$Box->cURL}
            {include file='snippets/product_slider.tpl' productlist=$Box->Artikel->elemente title=$title hideOverlays=true moreLink=$moreLink moreTitle=$moreTitle}
        {/if}
    {/foreach}
{/if}

{block name="index-additional"}
{if isset($oNews_arr) && $oNews_arr|@count > 0}
    <hr>
    <h2>{lang key="news" section="news"}</h2>
    {foreach name=news from=$oNews_arr item=oNewsUebersicht}
        {include file="blog/preview.tpl"}
    {/foreach}
{/if}
{/block}