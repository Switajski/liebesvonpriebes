{include file='tpl_inc/seite_header.tpl' cTitel=#coupons# cBeschreibung=#couponsDesc# cDokuURL=#couponsURL#}
{include file='tpl_inc/sortcontrols.tpl'}

{function kupons_uebersicht_tab}
    <div id="{$cKuponTyp}" class="tab-pane fade{if $tab === $cKuponTyp} active in{/if}">
        <div class="panel panel-default">
            {if $nKuponCount > 0}
                {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter cParam_arr=['tab'=>$cKuponTyp]}
            {/if}
            {if $oKupon_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cParam_arr=['tab'=>$cKuponTyp]}
            {/if}
            <form method="post" action="kupons.php">
                {$jtl_token}
                <input type="hidden" name="cKuponTyp" id="cKuponTyp" value="{$cKuponTyp}">
                {if $oKupon_arr|@count > 0}
                    <div class="table-responsive">
                        <table class="list table">
                            <thead>
                                <tr>
                                    <th title="Aktiv"></th>
                                    <th></th>
                                    <th>{#name#} {call sortControls oPagination=$oPagination nSortBy=0}</th>
                                    {if $cKuponTyp === 'standard' || $cKuponTyp === 'neukundenkupon'}<th>{#value#}</th>{/if}
                                    {if $cKuponTyp === 'standard' || $cKuponTyp === 'versandkupon'}
                                        <th>{#code#} {call sortControls oPagination=$oPagination nSortBy=1}</th>
                                    {/if}
                                    <th>{#mbw#}</th>
                                    <th>{#curmaxusage#} {call sortControls oPagination=$oPagination nSortBy=2}</th>
                                    <th>{#restrictions#}</th>
                                    <th>{#validityPeriod#}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $oKupon_arr as $oKupon}
                                    <tr{if $oKupon->cAktiv === 'N'} class="text-danger"{/if}>
                                        <td>{if $oKupon->cAktiv === 'N'}<i class="fa fa-times"></i>{/if}</td>
                                        <td><input type="checkbox" name="kKupon_arr[]" id="kupon-{$oKupon->kKupon}" value="{$oKupon->kKupon}"></td>
                                        <td>
                                            <label for="kupon-{$oKupon->kKupon}">
                                                {$oKupon->cName}
                                            </label>
                                        </td>
                                        {if $cKuponTyp === 'standard' || $cKuponTyp === 'neukundenkupon'}
                                            <td>
                                                {if $oKupon->cWertTyp === 'festpreis'}
                                                    <span data-toggle="tooltip" data-placement="right" data-html="true"
                                                          title='{getCurrencyConversionSmarty fPreisBrutto=$oKupon->fWert}'>
                                                        {$oKupon->cLocalizedValue}
                                                    </span>
                                                {else}
                                                    {$oKupon->fWert} %
                                                {/if}
                                            </td>
                                        {/if}
                                        {if $cKuponTyp === 'standard' || $cKuponTyp === 'versandkupon'}<td>{$oKupon->cCode}</td>{/if}
                                        <td>
                                            <span data-toggle="tooltip" data-placement="right" data-html="true"
                                                  title='{getCurrencyConversionSmarty fPreisBrutto=$oKupon->fMindestbestellwert}'>
                                                {$oKupon->cLocalizedMbw}
                                            </span>
                                        </td>
                                        <td>
                                            {$oKupon->nVerwendungenBisher}
                                            {if $oKupon->nVerwendungen > 0}
                                                von {$oKupon->nVerwendungen}</td>
                                            {/if}
                                        <td>
                                            {if !empty({$oKupon->cKundengruppe})}
                                                nur {$oKupon->cKundengruppe}<br>
                                            {/if}
                                            {if !empty({$oKupon->cArtikelInfo})}
                                                {$oKupon->cArtikelInfo} {#products#}<br>
                                            {/if}
                                            {if !empty({$oKupon->cHerstellerInfo})}
                                                {$oKupon->cHerstellerInfo} {#manufacturers#}<br>
                                            {/if}
                                            {if !empty({$oKupon->cKategorieInfo})}
                                                {$oKupon->cKategorieInfo} {#categories#}<br>
                                            {/if}
                                            {if !empty({$oKupon->cKundenInfo})}
                                                {$oKupon->cKundenInfo} {#customers#}<br>
                                            {/if}
                                        </td>
                                        <td>
                                            {#from#}: {$oKupon->cGueltigAbShort}<br>
                                            {#to#}: {$oKupon->cGueltigBisShort}
                                        </td>
                                        <td>
                                            <a href="kupons.php?kKupon={$oKupon->kKupon}&token={$smarty.session.jtl_token}"
                                               class="btn btn-default" title="{#modify#}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td><input type="checkbox" name="ALLMSGS" id="ALLMSGS_{$cKuponTyp}" onclick="AllMessages(this.form);"></td>
                                    <td colspan="9"><label for="ALLMSGS_{$cKuponTyp}">Alle ausw&auml;hlen</label></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                {elseif $nKuponCount > 0}
                    <div class="alert alert-info" role="alert">{#noFilterResults#}</div>
                {else}
                    <div class="alert alert-info" role="alert">
                        {#emptySetMessage1#} {$cKuponTypName}s {#emptySetMessage2#}
                    </div>
                {/if}
                <div class="panel-footer">
                    <div class="btn-group">
                        <a href="kupons.php?kKupon=0&cKuponTyp={$cKuponTyp}&token={$smarty.session.jtl_token}"
                           class="btn btn-primary" title="{#modify#}">
                            <i class="fa fa-share"></i> {$cKuponTypName} {#create#}
                        </a>
                        {if $oKupon_arr|@count > 0}
                            <button type="submit" class="btn btn-danger" name="action" value="loeschen"><i class="fa fa-trash"></i> {#delete#}</button>
                            {include file='tpl_inc/csv_export_btn.tpl' exporterId=$cKuponTyp}
                        {/if}
                        {include file='tpl_inc/csv_import_btn.tpl' importerId="kupon"}
                    </div>
                </div>
            </form>
        </div>
    </div>
{/function}

<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if $tab === 'standard'} active{/if}">
            <a data-toggle="tab" role="tab" href="#standard" aria-expanded="false">{#standardCoupon#}s</a>
        </li>
        <li class="tab{if $tab === 'versandkupon'} active{/if}">
            <a data-toggle="tab" role="tab" href="#versandkupon" aria-expanded="false">{#shippingCoupon#}s</a>
        </li>
        <li class="tab{if $tab === 'neukundenkupon'} active{/if}">
            <a data-toggle="tab" role="tab" href="#neukundenkupon" aria-expanded="false">{#newCustomerCoupon#}s</a>
        </li>
    </ul>
    <div class="tab-content">
        {kupons_uebersicht_tab
            cKuponTyp='standard'
            cKuponTypName=#standardCoupon#
            oKupon_arr=$oKuponStandard_arr
            nKuponCount=$nKuponStandardCount
            oPagination=$oPaginationStandard
            oFilter=$oFilterStandard
        }
        {kupons_uebersicht_tab
            cKuponTyp='versandkupon'
            cKuponTypName=#shippingCoupon#
            oKupon_arr=$oKuponVersandkupon_arr
            nKuponCount=$nKuponVersandCount
            oPagination=$oPaginationVersandkupon
            oFilter=$oFilterVersand
        }
        {kupons_uebersicht_tab
            cKuponTyp='neukundenkupon'
            cKuponTypName=#newCustomerCoupon#
            oKupon_arr=$oKuponNeukundenkupon_arr
            nKuponCount=$nKuponNeukundenCount
            oPagination=$oPaginationNeukundenkupon
            oFilter=$oFilterNeukunden
        }
    </div>
</div>