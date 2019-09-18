{if $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
    {assign var=tabanzeige value=true}
{else}
    {assign var=tabanzeige value=false}
{/if}
{if $bIstShop4 === true}
    <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-votes-ts">
        <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-votes-ts">
            <h3 class="panel-title">{$agws_ts_features_tabtitel}</h3>
        </div>
        <div class="tab-content-wrapper">
            <div class="panel-body">
                <div id="ts_article_reviews_wrapper"></div>
            </div>
        </div>
    </div>
{else}
    <style>
        {literal}
        #ts_article_reviews_wrapper ul.ts-reviews-list {margin-left:0px;max-height:500px;}
        #ts_article_reviews_wrapper ul.ts-reviews-list li {float:left !important;}
        {/literal}
    </style>

    <div class="panel {if $tabanzeige == false}notab{/if}" id="tab-votes-ts">
        <h2 class="title">{$agws_ts_features_tabtitel}</h2>
        <div class="custom_content">
            <div id="ts_article_reviews_wrapper"></div>
        </div>
    </div>
{/if}
<script type="text/javascript">

    ts_review_tsid_global = "{$agws_ts_features_TSID}";
    ts_review_tabintrotext_global = "{$agws_ts_features_tabintrotext}";

    function ts_article_review_init(ts_review_sku)
    {ldelim}
        _tsProductReviewsConfig = {ldelim}
        tsid: ts_review_tsid_global,
        sku:  ts_review_sku,
        variant: 'productreviews',
        borderColor: '#fa9600',
        introtext: ts_review_tabintrotext_global
        {rdelim}
        var _ts_wrapper = document.getElementById('ts_article_reviews_wrapper');
        var _ts = document.createElement('SCRIPT');

        _ts.type = 'text/javascript';
        _ts.async = true;
        _ts.charset = 'utf-8';
        _ts.src = '//widgets.trustedshops.com/reviews/tsSticker/tsProductSticker.js';
        _ts_wrapper.insertBefore(_ts, _ts_wrapper.firstChild);
        _tsProductReviewsConfig.script = _ts;
    {rdelim};

    $( document ).ready(function()
    {ldelim}
        {if $agws_ts_features_showtab=="on"}
            ts_article_review_init({$agws_ts_features_reviews_sku});
        {else}
            $('div#tab-votes-ts').remove();
        {/if}
    {rdelim});
</script>