{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='einstellungen'}
{config_load file="$lang.conf" section="billpay"}

{include file='tpl_inc/seite_header.tpl' cTitel=#billpay# cBeschreibung=#billpayDesc# cDokuURL=#billpayURL#}
<div id="content">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#overview">{#billpayOverview#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'log'} active{/if}">
            <a data-toggle="tab" role="tab" href="#log">{#billpayLog#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'config'} active{/if}">
            <a data-toggle="tab" role="tab" href="#config">{#billpayConfig#}</a>
        </li>
    </ul>
    <div class="container-fluid2">
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if isset($cTab) && $cTab === 'uebersicht'} active in{/if}">
                {if isset($cFehlerBillpay) && $cFehlerBillpay|strlen > 0}
                    <div class="alert alert-danger">{$cFehlerBillpay}</div>
                {else}
                    <div id="settings">
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            {foreach $oItem_arr as $i => $oItem}
                                <div class="panel panel-primary">
                                    <div class="panel-heading" role="tab" id="heading{$i}">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse{$i}" aria-expanded="false" aria-controls="collapse{$i}">
                                                {$oItem->cLand} - {$oItem->cWaehrung}
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapse{$i}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{$i}">
                                        <div class="panel-body">
                                            {include file='tpl_inc/billpay_config.tpl' oItem=$oItem}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            </div>
            <div id="log" class="tab-pane fade{if isset($cTab) && $cTab === 'log'} active in{/if}">
                {if !isset($oLog_arr) || $oLog_arr|@count === 0}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {else}
                    {include file='tpl_inc/pagination.tpl' oPagination=$oPagiLog cAnchor='log'}
                    <div class="container-fluid2 table-responsive">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="text-left">Meldung</th>
                                <th class="text-center">Typ</th>
                                <th class="text-center">Datum</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$oLog_arr item="oLog"}
                                <tr class="text-vcenter">
                                    <td>{$oLog->cLog}</td>
                                    <td class="text-center">
                                        <h4 class="label-wrap">
                                        {if $oLog->nLevel == 1}
                                            <span class="label label-danger logError">{#logError#}</span>
                                        {elseif $oLog->nLevel == 2}
                                            <span class="label label-info logNotice">{#logNotice#}</span>
                                        {else}
                                            <span class="label label-default logDebug">{#logDebug#}</span>
                                        {/if}
                                        </h4>
                                    </td>
                                    <td class="text-center">{$oLog->dDatum|date_format:"%d.%m.%Y - %H:%M:%S"}</td>
                                    <td class="text-center" style="width:24px">
                                        {if $oLog->cLogData|strlen > 0}
                                            <a href="#" onclick="$('#data{$oLog->kZahlunglog}').toggle();return false;" class="btn btn-default btn-sm"><i class="fa fa-bars"></i></a>
                                        {/if}
                                    </td>
                                </tr>
                                {if $oLog->cLogData|strlen > 0}
                                    {assign var="oKunde" value=$oLog->cLogData|unserialize}
                                    <tr class="hidden" id="data{$oLog->kZahlunglog}">
                                        <td colspan="4">
                                            {if $oKunde->kKunde > 0}
                                                <p><strong>Kdn:</strong> {$oKunde->kKunde}</p>
                                            {/if}
                                            <p><strong>Name:</strong> {$oKunde->cVorname} {$oKunde->cNachname}</p>
                                            <p><strong>Stra&szlig;e:</strong> {$oKunde->cStrasse} {$oKunde->cHausnummer}</p>
                                            <p><strong>Ort:</strong> {$oKunde->cPLZ} {$oKunde->cOrt}</p>
                                            <p><strong>E-Mail:</strong> {$oKunde->cMail}</p>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>
            <div id="config" class="tab-pane fade{if isset($cTab) && $cTab === 'config'} active in{/if}">
                {if isset($saved) && $saved}
                    <div class="alert alert-success"><i class="fa fa-info-circle"></i> {#settingsSaved#}</div>
                {/if}
                {include file='tpl_inc/einstellungen_bearbeiten.tpl' action='billpay.php?tab=config'}
            </div>
        </div>
    </div>
</div>
<script>$(function() { $('#collapse0').collapse('show'); });</script>
{include file='tpl_inc/footer.tpl'}