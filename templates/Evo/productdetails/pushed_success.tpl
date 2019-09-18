<div id="pushed-success" class="notification-alert bg-info panel-wrap{if isset($inline)} no-margin{/if}">
    {if isset($zuletztInWarenkorbGelegterArtikel)}
        {assign var=pushedArtikel value=$zuletztInWarenkorbGelegterArtikel}
    {else}
        {assign var=pushedArtikel value=$Artikel}
    {/if}
    <div class="panel panel-default ">
        <div class="panel-body">
            <div class="row clearfix">
                {assign var="showXSellingCart" value=false}
                {if isset($Xselling->Kauf) && count($Xselling->Kauf->Artikel) > 0}
                    {assign var="showXSellingCart" value=true}
                {/if}
                <div class="col-sm-5{if !$showXSellingCart} col-sm-offset-4{/if} text-center">
                    <h4 class="success-title">{$hinweis}</h4>
                    {block name="pushed-success-product-cell"}
                    <div class="product-cell text-center{if isset($class)} {$class}{/if}">
                        <div class="row">
                            <div class="col-xs-4 col-xs-offset-4">
                                {counter assign=imgcounter print=0}
                                <img src="{$pushedArtikel->Bilder[0]->cPfadNormal}" alt="{if isset($pushedArtikel->Bilder[0]->cAltAttribut)}{$pushedArtikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:"html"}{else}{$pushedArtikel->cName}{/if}" id="image{$pushedArtikel->kArtikel}_{$imgcounter}" class="image img-responsive" />
                            </div>
                            <div class="col-xs-12">
                                <div class="caption">
                                    <span class="title">{$pushedArtikel->cName}</span>
                                </div>
                            </div>{* /caption *}
                        </div>
                    </div>{* /product-cell *}
                    {/block}
                    <hr>
                    <p class="btn-group btn-group-justified btn-group-full" role="group">
                        <a href="{get_static_route id='warenkorb.php'}" class="btn btn-default btn-basket"><i class="fa fa-shopping-cart"></i> {lang key="gotoBasket"}</a>
                        <a href="{$pushedArtikel->cURL}" class="btn btn-primary btn-checkout" data-dismiss="{if isset($type)}{$type}{else}modal{/if}" aria-label="Close"><i class="fa fa-arrow-circle-right"></i> {lang key="continueShopping" section="checkout"}</a>
                    </p>
{*
                    <p class="continue-shopping">
                        <a href="{get_static_route id='bestellvorgang.php'}">{lang key="checkout" section="basketpreview"}</a>
                    </p>
*}
                </div>
                {block name="pushed-success-x-sell"}
                {if $showXSellingCart}
                    <div class="col-xs-7 recommendations hidden-xs">
                        <h4 class="text-center">{lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails'}</h4>
                        {include file='snippets/product_slider.tpl' id='slider-xsell' productlist=$Xselling->Kauf->Artikel title='' showPanel=false}
                    </div>
                {/if}
                {/block}
            </div>
        </div>
    </div>
</div>
