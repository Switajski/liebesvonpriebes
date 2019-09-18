<div id="content" class="container-fluid">
    {if !isset($noModule) || !$noModule}
        <form name="sprache" method="post" action="umfrage.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="block">
                <div class="input-group p25 left">
                    <span class="input-group-addon">
                        <label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
                    </span>
                    <span class="input-group-wrap last">
                        <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                            {foreach name=sprachen from=$Sprachen item=sprache}
                                <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </div>
        </form>
        <ul class="nav nav-tabs" role="tablist">
            <li class="tab{if !isset($cTab) || $cTab === 'umfrage'} active{/if}">
                <a data-toggle="tab" role="tab" href="#umfrage">{#umfrageOverview#}</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#einstellungen">{#umfrageSettings#}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="umfrage" class="tab-pane fade{if !isset($cTab) || $cTab === 'umfrage'} active in{/if}">
                <form name="erstellen" method="post" action="umfrage.php">
                    {$jtl_token}
                    <input type="hidden" name="umfrage" value="1" />
                    <input type="hidden" name="umfrage_erstellen" value="1" />
                    <input type="hidden" name="tab" value="umfrage" />
                    <p class="tcenter">
                        <button name="umfrageerstellen" type="submit" value="{#umfrageAdd#}" class="btn btn-primary"><i class="fa fa-share"></i> {#umfrageAdd#}</button>
                    </p>
                </form>
                {if $oUmfrage_arr|@count > 0 && $oUmfrage_arr}
                    {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cAnchor='umfrage'}
                    <form name="umfrage" method="post" action="umfrage.php">
                        {$jtl_token}
                        <input type="hidden" name="umfrage" value="1" />
                        <input type="hidden" name="umfrage_loeschen" value="1" />
                        <input type="hidden" name="tab" value="umfrage" />
                        <div id="payment">
                            <div id="tabellenLivesuche">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Aktive Umfragen</h3>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th class="th-1"></th>
                                                <th class="th-2">{#umfrageName#}</th>
                                                <th class="th-3">{#umfrageCustomerGrp#}</th>
                                                <th class="th-4">{#umfrageValidation#}</th>
                                                <th class="th-5">{#umfrageActive#}</th>
                                                <th class="th-6">{#umfrageQCount#}</th>
                                                <th class="th-7">{#umfrageDate#}</th>
                                                <th class="th-8">Aktionen</th>
                                            </tr>
                                            {foreach name=umfrage from=$oUmfrage_arr item=oUmfrage}
                                                <tr class="tab_bg{$smarty.foreach.umfrage.iteration%2}">
                                                    <td class="TD1"><input type="checkbox" name="kUmfrage[]" value="{$oUmfrage->kUmfrage}" /></td>
                                                    <td class="TD2">
                                                        <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&ud=1&kUmfrage={$oUmfrage->kUmfrage}&tab=umfrage">{$oUmfrage->cName}</a>
                                                    </td>
                                                    <td class="TD3">
                                                        {foreach name=kundengruppen from=$oUmfrage->cKundengruppe_arr item=cKundengruppe}
                                                            {$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
                                                        {/foreach}
                                                    </td>
                                                    <td class="TD4">{$oUmfrage->dGueltigVon_de}-{if $oUmfrage->dGueltigBis|truncate:10:"" === '0000-00-00'}{#umfrageInfinite#}{else}{$oUmfrage->dGueltigBis_de}{/if}</td>
                                                    <td class="TD5">{$oUmfrage->nAktiv}</td>
                                                    <td class="TD6">{$oUmfrage->nAnzahlFragen}</td>
                                                    <td class="TD7">{$oUmfrage->dErstellt_de}</td>
                                                    <td class="TD8">
                                                        <div class="btn-group">
                                                            <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&umfrage_editieren=1&kUmfrage={$oUmfrage->kUmfrage}&tab=umfrage" class="btn btn-default" title="{#modify#}">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&kUmfrage={$oUmfrage->kUmfrage}&umfrage_statistik=1" class="btn btn-default" title="{#umfrageStats#}"><i class="fa fa-bar-chart"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    </div>
                                    <div class="panel-footer">
                                        <button name="loeschen" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='umfrage.php' buttonCaption=#umfrageSave# title='Einstellungen' tab='einstellungen'}
            </div>
        </div>
    {else}
        <div class="alert alert-danger">{#noModuleAvailable#}</div>
    {/if}
</div>