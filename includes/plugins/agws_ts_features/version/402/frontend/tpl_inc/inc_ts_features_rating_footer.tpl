{if $bIstShop4===true}
    <style>
        {literal}
            #footer_ts_rating .panel a::before {content: none;}
        {/literal}
    </style>
    <div id="footer_ts_rating" class="col-xs-12 col-md-3">
    <section id="box122" class="panel panel-default box box-linkgroup">
        <div class="panel-heading">
            <h5 class="panel-title">{$ts_features_rating_boxtitel}</h5>
        </div>
        <div class="box-body text-center" style="padding-top: 10px;">
            <a href="{$ts_ratingwidget_url}" title="{$ts_ratingwidget_alt_title}" target="_blank">
                <img src="{$ts_ratingwidget_img}" alt="{$ts_ratingwidget_alt_title}" />
            </a>
        </div>
    </section>
{else}
    <div id="footer_ts_rating" style="padding-top:20px">
        <h2>{$ts_features_rating_boxtitel}</h2>
        <a href="{$ts_ratingwidget_url}" title="{$ts_ratingwidget_alt_title}" target="_blank">
            <img src="{$ts_ratingwidget_img}" alt="{$ts_ratingwidget_alt_title}" />
        </a>
    </div>
{/if}