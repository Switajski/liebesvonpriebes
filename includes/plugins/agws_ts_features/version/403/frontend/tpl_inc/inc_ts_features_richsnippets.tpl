<div id="ts_richsnippets"  class="{if $bIstShop4===true}container text-center{else}tcenter page_width {if $Einstellungen.template.general.page_align == 'L'}page_left{else}page_center{/if}{/if}">
    <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <span itemprop="ratingValue">{$ts_features_richsnippet_result} </span> /
        <span itemprop="bestRating">{$ts_features_richsnippet_max} </span> of
        <span itemprop="ratingCount">{$ts_features_richsnippet_count} </span>
        <a href="https://www.trustedshops.com/buyerrating/info_{$ts_features_richsnippet_tsid}.html" title="{$ts_features_richsnippet_shopName} custom reviews" target="_blank">{$ts_features_richsnippet_shopName} customer reviews | Trusted Shops</a>
    </div>
</div>