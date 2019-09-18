<nav id="shop-nav-xs" class="navbar navbar-default visible-xs">
{strip}
    <div class="container-fluid">
        {block name="megamenu-xs-nav"}
        <div class="navbar-collapse">
            <ul class="nav navbar-nav navbar-left force-float">
                <li>
                    <a href="#" class="offcanvas-toggle" data-toggle="offcanvas" data-target="#navbar-offcanvas">
                        <i class="fa fa-bars"></i> {lang key="allCategories" section="global"}
                    </a>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right force-float action-nav">
                {if isset($smarty.session.Kunde) && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0}
                    <li>
                        <a href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}">
                            <span class="fa fa-sign-out"></span>
                        </a>
                    </li>
                {/if}
                <li>
                    <a href="{get_static_route id='jtl.php'}" title="{lang key='myAccount'}">
                        <span class="fa fa-user"></span>
                    </a>
                </li>
                <li>
                    <a href="{get_static_route id='warenkorb.php'}" title="{lang key='basket'}">
                        <span class="fa fa-shopping-cart"></span>
                        {if $WarenkorbArtikelPositionenanzahl >= 1}
                            <sup class="badge">
                                <em>{$WarenkorbArtikelPositionenanzahl}</em>
                            </sup>
                        {/if}
                        {*
                        <span class="shopping-cart-label">{$WarensummeLocalized[$NettoPreise]}</span>
                        *}
                    </a>
                </li>
            </ul>{* /row *}
        </div>
        {/block}{* /block megamenu-xs-nav *}
    </div>{* /container-fluid *}
{/strip}
</nav>{* /shop-nav-xs *}

{* offcanvas navigation *}
<nav class="navbar navbar-default navbar-offcanvas" id="navbar-offcanvas">
{strip}
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-nav nav navbar-right text-right">
                <a class="btn btn-offcanvas btn-default btn-close navbar-btn"><span class="fa fa-times"></span></a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="sidebar-offcanvas">
            {$firstelement = false}
            {if isset($Einstellungen.global.global_sichtbarkeit) && ($Einstellungen.global.global_sichtbarkeit != 3 || isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde != 0)}
                {$firstelement = true}
                <div class="navbar-categories">
                    <ul class="nav navbar-nav">
                        {include file='snippets/categories_recursive.tpl' i=0 categoryId=0 limit=2 caret='right'}
                    </ul>
                </div>
                {block name="megamenu-manufacturers"}
                    {if isset($Einstellungen.template.megamenu.show_manufacturers) && $Einstellungen.template.megamenu.show_manufacturers !== 'N'}
                        {get_manufacturers assign='manufacturers'}
                        {if !empty($manufacturers)}
                            <hr>
                            <div class="navbar-manufacturers">
                                <ul class="nav navbar-nav navbar-right">
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="{lang key='manufacturers'}">
                                            {lang key='manufacturers'} <span class="fa fa-caret-down pull-right"></span>
                                        </a>
                                        <ul class="dropdown-menu keepopen">
                                            {foreach name='hersteller' from=$manufacturers item='hst'}
                                                <li role="presentation">
                                                    <a role="menuitem" tabindex="-1" href="{$hst->cSeo}" title="{$hst->cName|escape:"html"}">{$hst->cName|escape:"html"}</a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        {/if}
                    {/if}
                {/block}{* megamenu-manufacturers *}
            {/if}
            {block name="megamenu-pages"}
                {if isset($Einstellungen.template.megamenu.show_pages) && $Einstellungen.template.megamenu.show_pages !== 'N'}
                    {if $firstelement}
                        <hr>
                    {/if}
                    {$firstelement = true}
                    <ul class="nav navbar-nav">
                        {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu' caret="fa fa-caret-down pull-right"}
                    </ul>
                {/if}
            {/block}{* megamenu-pages *}
            {block name="navbar-top-cms"}
                {if !empty($linkgroups->Kopf)}
                    {if $firstelement}
                        <hr>
                    {/if}
                    <ul class="nav navbar-nav">
                        {foreach name=kopflinks from=$linkgroups->Kopf->Links item=Link}
                            {if $Link->cLocalizedName|has_trans}
                                <li class="{if isset($Link->aktiv) && $Link->aktiv == 1}active{/if}">
                                    <a href="{$Link->URL}" title="{$Link->cLocalizedName|trans}">{$Link->cLocalizedName|trans}</a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}
            {/block}{* /navbar-top *}
        </div>
    </div>
{/strip}
</nav>