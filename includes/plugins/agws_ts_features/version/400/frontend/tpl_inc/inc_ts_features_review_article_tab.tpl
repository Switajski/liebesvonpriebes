{if $bIstShop4 === true}
    <div role="tabpanel" class="tab-pane" id="tab-votes-ts">
        <div class="panel-title" data-toggle="collapse" data-parent="#article-tabs" data-target="#tab-votes-ts">
            <h4>{$agws_ts_features_tabtitel}</h4>
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

    <div class="panel" id="tab-votes-ts">
        <h2 class="title">{$agws_ts_features_tabtitel}</h2>
        <div class="custom_content">
            <div id="ts_article_reviews_wrapper"></div>
        </div>
    </div>
{/if}
<script type="text/javascript">
    function ts_article_review_init (ts_review_sku,ts_review_tsid,ts_review_tabintrotext)
    {ldelim}
        _tsProductReviewsConfig = {ldelim}
            tsid: ts_review_tsid,
            sku:  ts_review_sku,
            variant: 'productreviews',
            borderColor: '#fa9600',
            introtext: ts_review_tabintrotext
        {rdelim}

	console.log(_tsProductReviewsConfig);
	
        var me = document.getElementById('ts_article_reviews_wrapper');
        var _ts = document.createElement('SCRIPT');

        _ts.type = 'text/javascript';
        _ts.async = true;
        _ts.charset = 'utf-8';
        _ts.src = '//widgets.trustedshops.com/reviews/tsSticker/tsProductSticker.js';
        me.insertBefore(_ts, me.firstChild);
        _tsProductReviewsConfig.script = _ts;
    {rdelim};

    $( document ).ready(function()
    {ldelim}
        {if $agws_ts_features_showtab=="on"}
            ts_article_review_init ({$agws_ts_features_reviews_sku},"{$agws_ts_features_TSID}","{$agws_ts_features_tabintrotext}");
        {else}
            $('div#tab-votes-ts').remove();
        {/if}
    {rdelim});
</script>