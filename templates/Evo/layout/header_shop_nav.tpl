{strip}
<ul class="header-shop-nav nav navbar-nav force-float horizontal pull-right">
    {block name="navbar-productsearch"}
        <li id="search">
            <form action="navi.php" method="GET">
                <div class="input-group">
                    <input name="qs" type="text" class="form-control ac_input" placeholder="{lang key='search'}" autocomplete="off" aria-label="{lang key='search'}"/>
                    <span class="input-group-addon">
                        <button type="submit" name="search" id="search-submit-button" aria-label="{lang key='search'}">
                            <span class="fa fa-search"></span>
                        </button>
                    </span>
                </div>
            </form>
        </li>
    {/block}{* /navbar-productsearch *}

    {block name="navbar-top-user"}
    {*  ACCOUNT *}
    <li class="dropdown hidden-xs">
        {if empty($smarty.session.Kunde->kKunde)}
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="{lang key='login'}">
                <i class="fa fa-user"></i> <span class="hidden-xs hidden-sm"> {lang key='login'} </span> <i class="caret"></i>
            </a>
            <ul id="login-dropdown" class="dropdown-menu dropdown-menu-right">
                <li>
                    <form action="{get_static_route id='jtl.php' secure=true}" method="post" class="form evo-validate">
                        {$jtl_token}
                        <fieldset id="quick-login">
                            <div class="form-group">
                                <input type="email" name="email" id="email_quick" class="form-control" placeholder="{lang key='emailadress'}" required/>
                            </div>
                            <div class="form-group">
                                <input type="password" name="passwort" id="password_quick" class="form-control" placeholder="{lang key='password'}" required/>
                            </div>
                            {if isset($showLoginCaptcha) && $showLoginCaptcha}
                                <div class="form-group text-center float-label-control">
                                    <div class="g-recaptcha" data-sitekey="{$Einstellungen.global.global_google_recaptcha_public}" data-callback="captcha_filled"></div>
                                </div>
                            {/if}
                            <div class="form-group">
                                <input type="hidden" name="login" value="1"/>
                                {if !empty($oRedirect->cURL)}
                                    {foreach name=parameter from=$oRedirect->oParameter_arr item=oParameter}
                                        <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                                    {/foreach}
                                    <input type="hidden" name="r" value="{$oRedirect->nRedirect} "/>
                                    <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                                {/if}
                                <button type="submit" id="submit-btn" class="btn btn-primary btn-block">{lang key="login" section="global"}</button>
                            </div>
                        </fieldset>
                    </form>
                </li>
                <li>
                    <a href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}">{lang key='forgotPassword'}</a>
                </li>
                <li>
                    <a href="{get_static_route id='registrieren.php'}" title="{lang key='registerNow'}">{lang key='newHere'} {lang key='registerNow'}</a>
                </li>
            </ul>
        {else}
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="{lang key='hello'}">
                <span class="fa fa-user"></span>
                <span class="hidden-xs hidden-sm hidden-md"> {lang key='hello'} {if $smarty.session.Kunde->cAnrede === 'w'}{$Anrede_w}{elseif $smarty.session.Kunde->cAnrede === 'm'}{$Anrede_m}{/if} {$smarty.session.Kunde->cNachname}</span>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <a href="{get_static_route id='jtl.php' secure=true}" title="{lang key='myAccount'}">{lang key='myAccount'}</a>
                </li>
                <li>
                    <a href="{get_static_route id='jtl.php' secure=true}?logout=1" title="{lang key='logOut'}">{lang key='logOut'}</a>
                </li>
            </ul>
        {/if}
    </li>
    {*  ACCOUNT END *}

    {*  COMPARE LIST *}
    {include file='layout/header_shop_nav_compare.tpl'}
    {*  COMPARE LIST *}

    {*  WISH LIST *}
    {if isset($smarty.session.Wunschliste->kWunschliste) && $smarty.session.Wunschliste->CWunschlistePos_arr|count > 0}
    <li class="hidden-xs wish-list-menu">
        <a href="{get_static_route id='wunschliste.php'}" title="{lang key="goToWishlist" sektion="global"}" class="link_to_wishlist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"><span class="fa fa-heart"></span><sup class="badge"><em>{$smarty.session.Wunschliste->CWunschlistePos_arr|count}</em></sup></a>
    </li>
    {/if}
    {*  WISH LIST *}

    {*  CART *}
    <li class="hidden-xs cart-menu dropdown{if $WarenkorbArtikelanzahl >= 1} items{/if}{if $nSeitenTyp == 3} current{/if}" data-toggle="basket-items">
        {include file='basket/cart_dropdown_label.tpl'}
    </li>
    {*  CART END *}
    {/block}{* /navbar-top-user *}
</ul>{* /shop-nav *}
{/strip}