{if $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
    {assign var=tabanzeige value=true}
{else}
    {assign var=tabanzeige value=false}
{/if}
<div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-votes-ts">
    <div class="panel-heading" data-toggle="collapse" data-parent="#article-tabs"
         data-target="#tab-votes-ts">
        <h3 class="panel-title">{$agws_ts_features_tabtitel}</h3>
    </div>
    <div class="tab-content-wrapper">
        <div class="panel-body">
            <div id="ts_article_reviews_wrapper">
                <div id="ts_product_sticker"></div>
            </div>
        </div>
    </div>
</div>