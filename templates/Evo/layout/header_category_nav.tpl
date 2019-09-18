{* desktop navigation (> screen-min-sm) *}
{strip}
<div id="evo-main-nav-wrapper" class="nav-wrapper{if $Einstellungen.template.theme.static_header === 'Y'} do-affix{/if}">
    <nav id="evo-main-nav" class="navbar navbar-default">
        <div class="container{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}-fluid{/if}">
            {if $Einstellungen.template.theme.static_header === 'Y'}
                {if isset($ShopLogoURL)}
                {block name="category-nav-logo"}
                <div class="navbar-header visible-affix pull-left hidden-xs hidden-sm">
                    <a class="navbar-brand" href="{$ShopURL}" title="{$Einstellungen.global.global_shopname}">
                        {image src=$ShopLogoURL alt=$Einstellungen.global.global_shopname class="img-responsive-height"}
                    </a>
                </div>
                {/block}
                {/if}
            {/if}
            {block name="category-nav-megamenu"}
            <div class="megamenu">
                <ul class="nav navbar-nav force-float">
                    {include file='snippets/categories_mega.tpl'}
                    {if $Einstellungen.template.theme.static_header === 'Y'}
                        <li class="cart-menu visible-affix dropdown bs-hover-enabled pull-right{if $nSeitenTyp == 3} current{/if}" data-toggle="basket-items">
                            {include file='basket/cart_dropdown_label.tpl'}
                        </li>
                    {/if}
                </ul>
            </div>
            {/block}
        </div>
    </nav>
</div>
{/strip}