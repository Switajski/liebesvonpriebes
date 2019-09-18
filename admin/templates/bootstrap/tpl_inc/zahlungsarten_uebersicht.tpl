{include file='tpl_inc/seite_header.tpl' cTitel=#paymentmethods# cBeschreibung=#installedPaymentmethods# cDokuURL=#paymentmethodsURL#}
<div id="content" class="container-fluid">
    <form method="post" action="zahlungsarten.php" class="top" style="margin-bottom: 15px;">
        {$jtl_token}
        <input type="hidden" name="checkNutzbar" value="1" />
        <button name="checkSubmit" type="submit" value="{#paymentmethodsCheckAll#}" class="btn btn-info button"><i class="fa fa-refresh"></i> {#paymentmethodsCheckAll#}</button>
    </form>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Installierte Zahlungsarten</h3>
        </div>
        <div class="panel-body table-responsive">
            <table class="list table">
                <thead>
                    <tr>
                        <th>{#name#}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach name=zahlungsarten from=$zahlungsarten item=zahlungsart}
                        <tr class="text-vcenter">
                            <td class="text-left">
                                <h4>{$zahlungsart->cName}
                                    <small>{$zahlungsart->cAnbieter}</small>
                                </h4>
                            </td>
                            <td class="tright">
                                {if $zahlungsart->nActive == 1}
                                    <span class="label label-success" title="Aktiv"><i class="fa fa-check"></i></span>
                                {else}
                                    <span class="label label-danger" title="Inaktiv"><i class="fa fa-times"></i></span>
                                {/if}
                            </td>
                            <td class="tright">
                                <div class="btn-group" role="group">
                                    <a href="zahlungsarten.php?a=log&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                       class="btn btn-sm down
                                              {if $zahlungsart->nLogCount > 0}
                                                    {if $zahlungsart->nErrorLogCount}btn-danger{else}btn-default{/if}
                                              {else}
                                                    btn-default disabled
                                              {/if}"
                                       title="{#viewLog#}">
                                        <i class="fa
                                                  {if $zahlungsart->nLogCount > 0}
                                                        {if $zahlungsart->nErrorLogCount}fa-warning{else}fa-bars{/if}
                                                  {else}
                                                        fa-check
                                                  {/if}"></i>
                                    </a>
                                    <a {if $zahlungsart->nEingangAnzahl > 0}href="zahlungsarten.php?a=payments&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"{/if}
                                       class="btn btn-default {if $zahlungsart->nEingangAnzahl === 0}disabled{/if}"
                                       title="Zahlungseing&auml;nge">
                                        <i class="fa fa-money"></i>
                                    </a>
                                    <a href="zahlungsarten.php?kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                       class="btn btn-default btn-sm" title="{#edit#}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>