{include file='tpl_inc/seite_header.tpl' cTitel=#couponStatistic# cDokuURL=#couponstatisticsURL#}
<div id="content">
    <div class="form-group">
        <form method="post" action="kuponstatistik.php" class="form-inline">
            {$jtl_token}
            <div class="form-group">
                <input type="hidden" name="formFilter" value="1" class="form-control"/>
                <label for="SelectFromDay">{#fromUntilDate#}:</label>
                <input type="text" size="21" name="daterange" class="form-control"/>
                <script type="text/javascript">
                    {literal}
                    $(function() {
                        $('input[name="daterange"]').daterangepicker(
                            {
                                locale: {
                                    format: 'YYYY-MM-DD'
                                },
                                {/literal}
                                startDate: '{$startDate}',
                                endDate: '{$endDate}',
                                minDate: '{$startDateShop}',
                                maxDate: '{$smarty.now|date_format:"%Y%m%d"}'
                                {literal}
                            }
                        );
                    });
                    {/literal}
                </script>
            </div>
            <div class="form-group">
                <select id="kKupon" name="kKupon" class="combo form-control">
                    <option value="-1">Alle</option>
                    {foreach from=$coupons_arr item=coupon_arr}
                        <option value="{$coupon_arr.kKupon}"{if isset($coupon_arr.aktiv) && $coupon_arr.aktiv} selected{/if}>{$coupon_arr.cName}</option>
                    {/foreach}
                </select>
            </div>
            <button name="btnSubmit" type="submit" value="Filtern" class="btn btn-primary">{#filtering#}</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table">
            <tr>
                <td>{#countUsedCoupons#}:</td>
                <td><strong>{$overview_arr.nCountUsedCouponsOrder} ({$overview_arr.nPercentCountUsedCoupons}%)</strong></td>
            </tr>
            <tr>
                <td>{#countOrders#}:</td>
                <td><strong>{$overview_arr.nCountOrder}</strong></td>
            </tr>
            <tr>
                <td>{#countCustomers#}:</td>
                <td><strong>{$overview_arr.nCountCustomers}</strong></td>
            </tr>
            <tr>
                <td>{#couponAmountAll#}:</td>
                <td><strong>{$overview_arr.nCouponAmountAll}</strong></td>
            </tr>
            <tr>
                <td>{#shoppingCartAmountAll#}:</td>
                <td><strong>{$overview_arr.nShoppingCartAmountAll}</strong></td>
            </tr>
        </table>
    </div>
    {if $usedCouponsOrder|@count > 0}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{#couponName#}</th>
                        <th>{#customerName#}</th>
                        <th>{#orderNumber#}</th>
                        <th>{#couponValue#}</th>
                        <th>{#orderValue#}</th>
                        <th>{#date#}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$usedCouponsOrder item=usedCouponOrder}
                        <tr>
                            <td>
                                {if $usedCouponOrder.kKupon}
                                    <a href="kupons.php?&kKupon={$usedCouponOrder.kKupon}&token={$smarty.session.jtl_token}">{$usedCouponOrder.cName}</a>
                                {else}
                                    {$usedCouponOrder.cName}
                                {/if}
                            </td>
                            <td>{$usedCouponOrder.cUserName}</td>
                            <td>{$usedCouponOrder.cBestellNr}</td>
                            <td>{$usedCouponOrder.nCouponValue}</td>
                            <td>{$usedCouponOrder.nShoppingCartAmount}</td>
                            <td>{$usedCouponOrder.dErstellt|date_format:"%d.%m.%Y %H:%M:%S"}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#order_{$usedCouponOrder.cBestellNr}"><i class="fa fa-info"></i></button>
                                <div class="modal fade bs-example-modal-lg" id="order_{$usedCouponOrder.cBestellNr}" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <strong>{#order#}: </strong><span class="value">{$usedCouponOrder.cBestellNr} ({$usedCouponOrder.cUserName})</span>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th>{#orderPosition#}</th>
                                                        <th>{#amount#}</th>
                                                        <th>{#unitPrice#}</th>
                                                        <th>{#totalPrice#}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    {foreach from=$usedCouponOrder.cOrderPos_arr item=cOrderPos_arr}
                                                        <tr>
                                                            <td>{$cOrderPos_arr.cName}</td>
                                                            <td>{$cOrderPos_arr.nAnzahl}</td>
                                                            <td>{$cOrderPos_arr.nPreis}</td>
                                                            <td>{$cOrderPos_arr.nGesamtPreis}</td>
                                                        </tr>
                                                    {/foreach}
                                                    </tbody>
                                                    <tfoot>
                                                    <tr>
                                                        <td>{#totalAmount#}:</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>{$usedCouponOrder.nShoppingCartAmount}</td>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>