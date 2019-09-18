{if isset($ts_ratingwidget_url)}
    <section class="panel panel-default box box-custom" id="sidebox_ts_rating">
        <div class="panel-heading">
            <h5 class="panel-title">{$ts_features_rating_boxtitel}</h5>
        </div>
        <div class="panel-body text-center">
            <a href="{$ts_ratingwidget_url}" title="{$ts_ratingwidget_alt_title}" target="_blank">
                <img src="{$ts_ratingwidget_img}" alt="{$ts_ratingwidget_alt_title}"/>
            </a>
        </div>
    </section>
{/if}