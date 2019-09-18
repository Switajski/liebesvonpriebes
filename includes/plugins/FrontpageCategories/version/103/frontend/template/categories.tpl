{if $type === 'list'}
    <div class="row row-eq-height content-cats-small clearfix">
        {foreach from=$startCategories key=i item=oStartKategorie}
            {if $categoriesPerRow === 2 || $categoriesPerRow === 3 || $categoriesPerRow === 4 || $categoriesPerRow === 6}
                {assign var='col' value='col-lg-'|cat:(12/$categoriesPerRow)}
            {else}
                {assign var='col' value='col-lg-3'}
            {/if}

            <div class="col-xs-6 col-md-4">
            <a href="{$oStartKategorie->cURL}">
                <div class="mosaic-container">
                    <img src={$oStartKategorie->cBildURL} alt="{$oStartKategorie->cName}" class="mosaic-image" />
                    <div class="mosaic-overlay">
                        <div class="mosaic-text">{$oStartKategorie->cName}</div>
                    </div>
                </div>
                </a>
            </div>
        {/foreach}
    </div>
{elseif $type === 'slider'}
    <section class="panel panel-default panel-slider clearfix">
        <div class="panel-heading">
            {if $itemcount > $categoriesPerRow}
                <span class="controls">
                    <a class="left" href="#home-categories" data-slide="prev" role="button"><span class="fa fa-chevron-left" aria-hidden="true"></span></a>
                    &nbsp;&nbsp;
                    <a class="right" href="#home-categories" data-slide="next" role="button"><span class="fa fa-chevron-right" aria-hidden="true"></span></a>
                </span>
            {/if}
            <h5 class="panel-title">{$title}</h5>
        </div>
        <div class="panel-body">
            <div id="home-categories" class="evo-slider" data-item-count="{$categoriesPerRow}">
                {foreach from=$startCategories key=i item=oStartKategorie}
                    <div class="product-wrapper{if isset($style)} {$style}{/if}">
                        <div class="product-cell text-center thumbnail">
                            <a class="image-wrapper" href="{$oStartKategorie->cURL}">
                                <img src="{$oStartKategorie->cBildURL}" class="image" alt="{$oStartKategorie->cName}" />
                            </a>
                            <div class="caption">
                                <a class="title" href="{$oStartKategorie->cURL}">{$oStartKategorie->cName}</a>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </section>
{/if}