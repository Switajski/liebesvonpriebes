{if $ReviewStickerCode|@count > 0}
    {if $bIstShop4 === true}
        <style>
            #sidebox_ts_review .ts-rating-light.skyscraper_vertical {ldelim}width: auto;{rdelim}
            #sidebox_ts_review .ts-rating-light.skyscraper_vertical .ts-reviews .ts-reviews-list li {ldelim}width:100%;{rdelim} }
        </style>

        <section class="panel panel-default box box-custom" id="sidebox_ts_review">
            <div class="panel-heading">
                <h5 class="panel-title">{$ts_features_review_boxtitel}</h5>
            </div>
            <div class="panel-body text-center">
                {$ReviewStickerCode}
            </div>
        </section>
    {else}
        <div class="sidebox" id="sidebox_ts_review">
            <h3 class="boxtitle">{$ts_features_review_boxtitel}</h3>
            <div class="sidebox_content tcenter">
                {$ReviewStickerCode}
            </div>
        </div>
    {/if}
{/if}