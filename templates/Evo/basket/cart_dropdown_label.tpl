<a href="{get_static_route id='warenkorb.php'}" class="dropdown-toggle" data-toggle="dropdown" title="{lang key='basket'}">
    <span class="fa fa-shopping-cart"></span>
    {if $WarenkorbArtikelPositionenanzahl >= 1}
        <sup class="badge">
            <em>{$WarenkorbArtikelPositionenanzahl}</em>
        </sup>
    {/if}
    <span class="shopping-cart-label hidden-sm"> {$WarensummeLocalized[$NettoPreise]}</span> <span class="caret"></span>
</a>
<ul class="cart-dropdown dropdown-menu dropdown-menu-right">
    {include file='basket/cart_dropdown.tpl'}
</ul>