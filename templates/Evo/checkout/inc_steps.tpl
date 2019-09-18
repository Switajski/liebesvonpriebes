{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{assign var="step1_active" value=($bestellschritt[1] == 1 || $bestellschritt[2] == 1)}
{assign var="step2_active" value=($bestellschritt[3] == 1 || $bestellschritt[4] == 1)}
{assign var="step3_active" value=($bestellschritt[5] == 1)}
{if $bestellschritt[1] != 3}
    <ul class="nav nav-wizard row">
        <li class="{if $step1_active}active col-xs-8 col-sm-6{else}col-xs-2 col-sm-3{/if} col-md-4">
            {if $bestellschritt[1] < 3 || $bestellschritt[2] < 3}
                <a href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1" title="{lang section='account data' key='billingAndDeliveryAddress'}">
                    <i class="fa fa-user hidden-md hidden-lg{if $step1_active} hidden{/if}"></i>
                    <span class="{if !$step1_active}hidden-sm hidden-xs{/if}">{lang section='account data' key='billingAndDeliveryAddress'}</span>
                </a>
            {else}
                <span class="nav-badge">
                    <i class="fa fa-user hidden-md hidden-lg{if $step1_active} hidden{/if}"></i>
                    <span class="{if !$step1_active}hidden-sm hidden-xs{/if}">{lang section='account data' key='billingAndDeliveryAddress'}</span>
                </span>
            {/if}
        </li>
        <li class="{if $step2_active}active col-xs-8 col-sm-6{else}col-xs-2 col-sm-3{/if} col-md-4">
            {if $bestellschritt[3] < 3 || $bestellschritt[4] < 3}
                <a href="{get_static_route id='bestellvorgang.php'}?editVersandart=1" title="{lang section='account data' key='shippingAndPaymentOptions'}">
                    <i class="fa fa-truck hidden-md hidden-lg{if $step2_active} hidden{/if}"></i>
                    <span class="{if !$step2_active}hidden-sm hidden-xs{/if}">{lang section='account data' key='shippingAndPaymentOptions'}</span>
                </a>
            {else}
                <span class="nav-badge">
                    <i class="fa fa-truck hidden-md hidden-lg{if $step2_active} hidden{/if}"></i>
                    <span class="{if !$step2_active}hidden-sm hidden-xs{/if}">{lang section='account data' key='shippingAndPaymentOptions'}</span>
                </span>
            {/if}
        </li>
        <li class="{if $step3_active}active col-xs-8 col-sm-6{else}col-xs-2 col-sm-3{/if} col-md-4">
            <span class="nav-badge">
                <i class="fa fa-wpforms hidden-md hidden-lg{if $step3_active} hidden{/if}"></i>
                <span class="{if !$step3_active}hidden-sm hidden-xs{/if}">{lang section='checkout' key='summary'}</span>
            </span>
        </li>
    </ul>
{/if}