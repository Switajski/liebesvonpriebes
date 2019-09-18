{if isset($ReviewStickerCode)}
    {literal}
        <style>
            #sidebox_ts_review .ts-rating-light.skyscraper_vertical {width: auto;}
            #sidebox_ts_review .ts-rating-light.skyscraper_vertical .ts-reviews .ts-reviews-list li {width: 100%;}
        </style>
    {/literal}
    <section class="panel panel-default box box-custom" id="sidebox_ts_review">
        <div class="panel-heading">
            <h5 class="panel-title">{$ts_features_review_boxtitel}</h5>
        </div>
        <div class="panel-body text-center">
            {$ReviewStickerCode}
        </div>
    </section>
{/if}
