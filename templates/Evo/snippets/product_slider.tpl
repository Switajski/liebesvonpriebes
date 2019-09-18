{strip}
{if $productlist|@count > 0}
    {if !isset($tplscope)}
        {assign var='tplscope' value='slider'}
    {/if}
    <section class="panel{if $title|strlen > 0} panel-default{/if} panel-slider{if $tplscope === 'box'} box box-slider{/if}{if isset($class) && $class|strlen > 0} {$class}{/if}"{if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
        <div class="panel-heading">
            {if $title|strlen > 0}
                <h5 class="panel-title">
                    {$title}
                    {if !empty($moreLink)}
                        <a class="more pull-right" href="{$moreLink}" title="{$moreTitle}" data-toggle="tooltip" data-placement="auto right" aria-label="{$moreTitle}">
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    {/if}
                </h5>
            {/if}
        </div>
        <div{if $title|strlen > 0} class="panel-body"{/if}>
            <div class="{if $tplscope == 'box'}{block name="product-box-slider-class"}evo-box-slider{/block}{else}{block name="product-slider-class"}evo-slider{/block}{/if}">
                {foreach name="sliderproducts" from=$productlist item='product'}
                    <div class="product-wrapper{if isset($style)} {$style}{/if}">
                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                    </div>
                {/foreach}
            </div>
        </div>
    </section>{* /panel *}
{/if}
{/strip}