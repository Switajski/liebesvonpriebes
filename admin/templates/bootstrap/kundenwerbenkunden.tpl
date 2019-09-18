{config_load file="$lang.conf" section="kundenwerbenkunden"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#kundenwerbenkunden# cBeschreibung=#kundenwerbenkundenDesc# cDokuURL=#kundenwerbenkundenURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'einladungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einladungen">{#kundenwerbenkundenNotReggt#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'registrierung'} active{/if}">
            <a data-toggle="tab" role="tab" href="#registrierung">{#kundenwerbenkundenReggt#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'praemie'} active{/if}">
            <a data-toggle="tab" role="tab" href="#praemie">{#kundenwerbenkundenBonis#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#kundenwerbenkundenSettings#}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="einladungen" class="tab-pane fade {if !isset($cTab) || $cTab === 'einladungen'} active in{/if}">
            {if $oKwKNichtReg_arr|@count > 0 && $oKwKNichtReg_arr}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiNichtReg cAnchor='einladungen'}
                <form name="umfrage" method="post" action="kundenwerbenkunden.php">
                    {$jtl_token}
                    <input type="hidden" name="KwK" value="1" />
                    <input type="hidden" name="nichtreggt_loeschen" value="1" />
                    <input type="hidden" name="tab" value="einladungen" />
                    <div id="payment">
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="check"></th>
                                    <th class="tleft">{#kundenwerbenkundenName#}</th>
                                    <th class="tleft">{#kundenwerbenkundenFromReg#}</th>
                                    <th class="tleft">{#kundenwerbenkundenCredit#}</th>
                                    <th class="th-5">{#kundenwerbenkundenDateInvite#}</th>
                                </tr>
                                {foreach name=nichtregkunden from=$oKwKNichtReg_arr item=oKwKNichtReg}
                                    <tr class="tab_bg{$smarty.foreach.nichtregkunden.iteration%2}">
                                        <td class="check">
                                            <input type="checkbox" name="kKundenWerbenKunden[]" value="{$oKwKNichtReg->kKundenWerbenKunden}">
                                        </td>
                                        <td class="tleft">
                                            <b>{$oKwKNichtReg->cVorname} {$oKwKNichtReg->cNachname}</b><br />{$oKwKNichtReg->cEmail}
                                        </td>
                                        <td class="tleft">
                                            <b>{$oKwKNichtReg->cBestandVorname} {$oKwKNichtReg->cBestandNachname}</b><br />{$oKwKNichtReg->cMail}
                                        </td>
                                        <td class="tleft">{getCurrencyConversionSmarty fPreisBrutto=$oKwKNichtReg->fGuthaben}</td>
                                        <td class="tcenter">{$oKwKNichtReg->dErstellt_de}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    </div>
                    <p class="submit">
                        <button name="loeschen" type="submit" value="{#kundenwerbenkundenDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#kundenwerbenkundenDelete#}</button>
                    </p>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="registrierung" class="tab-pane fade {if isset($cTab) && $cTab === 'registrierung'} active in{/if}">
            {if $oKwKReg_arr && $oKwKReg_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiReg cAnchor='registrierung'}
                <div id="payment">
                    <div id="tabellenLivesuche" class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="tleft">{#kundenwerbenkundenRegName#}</th>
                                <th class="tleft">{#kundenwerbenkundenFromReg#}</th>
                                <th class="tleft">{#kundenwerbenkundenCredit#}</th>
                                <th class="th-4">{#kundenwerbenkundenDateInvite#}</th>
                                <th class="th-5">{#kundenwerbenkundenDateErstellt#}</th>
                            </tr>
                            {foreach name=regkunden from=$oKwKReg_arr item=oKwKReg}
                                <tr class="tab_bg{$smarty.foreach.regkunden.iteration%2}">
                                    <td class="TD2"><b>{$oKwKReg->cVorname} {$oKwKReg->cNachname}</b><br />{$oKwKReg->cEmail}</td>
                                    <td class="TD2">
                                        <b>{$oKwKReg->cBestandVorname} {$oKwKReg->cBestandNachname}</b><br />{$oKwKReg->cMail}
                                    </td>
                                    <td class="TD3">{getCurrencyConversionSmarty fPreisBrutto=$oKwKReg->fGuthaben}</td>
                                    <td class="tcenter">{$oKwKReg->dErstellt_de}</td>
                                    <td class="tcenter">{$oKwKReg->dBestandErstellt_de}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="praemie" class="tab-pane fade {if isset($cTab) && $cTab === 'praemie'} active in{/if}">
            {if $oKwKBestandBonus_arr|@count > 0 && $oKwKBestandBonus_arr}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiPraemie cAnchor='praemie'}
                <div id="payment">
                    <div id="tabellenLivesuche" class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="tleft">{#kundenwerbenkundenFromReg#}</th>
                                <th class="tleft">{#kundenwerbenkundenCredit#}</th>
                                <th class="">{#kundenwerbenkundenExtraPoints#}</th>
                                <th class="th-4">{#kundenwerbenkundenDateBoni#}</th>
                            </tr>
                            {foreach name=letzte100bonis from=$oKwKBestandBonus_arr item=oKwKBestandBonus}
                                <tr class="tab_bg{$smarty.foreach.letzte100bonis.iteration%2}">
                                    <td class="TD2">
                                        <b>{$oKwKBestandBonus->cBestandVorname} {$oKwKBestandBonus->cBestandNachname}</b><br />{$oKwKBestandBonus->cMail}
                                    </td>
                                    <td class="TD2">{getCurrencyConversionSmarty fPreisBrutto=$oKwKBestandBonus->fGuthaben}</td>
                                    <td class="tcenter">{$oKwKBestandBonus->nBonuspunkte}</td>
                                    <td class="tcenter">{$oKwKBestandBonus->dErhalten_de}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='kundenwerbenkunden.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>

<script type="text/javascript">
    {foreach name=conf from=$oConfig_arr item=oConfig}
        {if $oConfig->cWertName|strpos:"_bestandskundenguthaben" || $oConfig->cWertName|strpos:"_neukundenguthaben"}
            ioCall('getCurrencyConversion', [0, $('#{$oConfig->cWertName}').val(), 'EinstellungAjax_{$oConfig->cWertName}']);
        {/if}
    {/foreach}
</script>
{include file='tpl_inc/footer.tpl'}