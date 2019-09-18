{include file='tpl_inc/seite_header.tpl' cBeschreibung=#configurePaymentmethod# cTitel=$zahlungsart->cName}
<div id="content" class="container-fluid">
    <form name="einstellen" method="post" action="zahlungsarten.php" class="settings">
        {$jtl_token}
        <input type="hidden" name="einstellungen_bearbeiten" value="1" />
        <input type="hidden" name="kZahlungsart" value="{if isset($zahlungsart->kZahlungsart)}{$zahlungsart->kZahlungsart}{/if}" />

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#settings#}: Allgemein</h3>
            </div>
            <div class="panel-body">
                <ul class="jtl-list-group">
                    {foreach name=sprachen from=$sprachen item=sprache}
                        {assign var="cISO" value=$sprache->cISO}
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
                            </span>
                            <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($Zahlungsartname[$cISO])}{$Zahlungsartname[$cISO]}{/if}" tabindex="1" />
                        </li>
                    {/foreach}
                    <li class="input-group">
                        <span class="input-group-addon">
                            <label for="cBild">{#pictureURL#}</label>
                        </span>
                        <input class="form-control" type="text" name="cBild" id="cBild" value="{if isset($zahlungsart->cBild)}{$zahlungsart->cBild}{/if}" tabindex="1" />
                        <span class="input-group-addon">{getHelpDesc cDesc=#pictureDesc#}</span>
                    </li>
                    {foreach name=sprachen from=$sprachen item=sprache}
                        {assign var="cISO" value=$sprache->cISO}
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cGebuehrname_{$cISO}">{#feeName#} ({$sprache->cNameDeutsch})</label>
                            </span>
                            <input class="form-control" type="text" name="cGebuehrname_{$cISO}" id="cGebuehrname_{$cISO}" value="{if isset($Gebuehrname[$cISO])}{$Gebuehrname[$cISO]}{/if}" tabindex="2" />
                        </li>
                    {/foreach}
                    <li class="input-group">
                        <span class="input-group-addon">
                            <label for="kKundengruppe">{#restrictedToCustomerGroups#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select name="kKundengruppe[]" multiple size="6" id="kKundengruppe" class="form-control combo">
                                <option value="0" {if isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0]}selected{/if}>{#allCustomerGroups#}</option>
                                {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
                                    {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
                                    <option value="{$kundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen[$kKundengruppe]) && $gesetzteKundengruppen[$kKundengruppe]}selected{/if}>{$kundengruppe->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
                    </li>
                    <li class="input-group">
                        <span class="input-group-addon">
                            <label for="nSort">{#sortNo#}</label>
                        </span>
                        <input class="form-control" type="text" name="nSort" id="nSort" value="{if isset($zahlungsart->nSort)}{$zahlungsart->nSort}{/if}" tabindex="3" />
                    </li>

                    {foreach name=sprachen from=$sprachen item=sprache}
                        {assign var="cISO" value=$sprache->cISO}
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cHinweisTextShop_{$cISO}">{#noticeTextShop#} ({$sprache->cNameDeutsch})</label>
                            </span>
                            <textarea class="form-control" id="cHinweisTextShop_{$cISO}" name="cHinweisTextShop_{$cISO}">{if isset($cHinweisTexteShop_arr[$cISO])}{$cHinweisTexteShop_arr[$cISO]}{/if}</textarea>
                        </li>
                    {/foreach}

                    {foreach name=sprachen from=$sprachen item=sprache}
                        {assign var="cISO" value=$sprache->cISO}
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cHinweisText_{$cISO}">{#noticeTextEmail#} ({$sprache->cNameDeutsch})</label>
                            </span>
                            <textarea class="form-control" id="cHinweisText_{$cISO}" name="cHinweisText_{$cISO}">{if isset($cHinweisTexte_arr[$cISO])}{$cHinweisTexte_arr[$cISO]}{/if}</textarea>
                        </li>
                    {/foreach}

                    <li class="input-group">
                        <span class="input-group-addon">
                            <label for="nMailSenden">{#paymentAckMail#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nMailSenden" name="nMailSenden" class="form-control combo">
                                <option value="1"{if $zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_EINGANG} selected="selected"{/if}>
                                    Ja
                                </option>
                                <option value="0"{if !($zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_EINGANG)} selected="selected"{/if}>
                                    Nein
                                </option>
                            </select>
                        </span>
                    </li>

                    <li class="input-group">
                        <span class="input-group-addon">
                            <label for="nMailSendenStorno">{#paymentCancelMail#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nMailSendenStorno" name="nMailSendenStorno" class="form-control combo">
                                <option value="1"{if $zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_STORNO} selected="selected"{/if}>
                                    Ja
                                </option>
                                <option value="0"{if !($zahlungsart->nMailSenden & $ZAHLUNGSART_MAIL_STORNO)} selected="selected"{/if}>
                                    Nein
                                </option>
                            </select>
                        </span>
                    </li>

                    {$filters = [
                        'za_nachnahme_jtl',
                        'za_ueberweisung_jtl',
                        'za_rechnung_jtl',
                        'za_barzahlung_jtl',
                        'za_lastschrift_jtl',
                        'za_kreditkarte_jtl',
                        'za_iloxx_jtl',
                        'za_paypal_jtl',
                        'za_safetypay' ,
                        'za_eos_cc_jtl',
                        'za_eos_dd_jtl',
                        'za_eos_direct_jtl',
                        'za_eos_ewallet_jtl',
                        'za_clickandbuy_jtl',
                        'za_billpay_invoice_jtl',
                        'za_billpay_direct_debit_jtl',
                        'za_billpay_rate_payment_jtl',
                        'za_billpay_paylater_jtl'
                    ]}

                    {if !$zahlungsart->cModulId|in_array:$filters}
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="nWaehrendBestellung">{#duringOrder#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nWaehrendBestellung" name="nWaehrendBestellung" class="combo form-control">
                                    <option value="1"{if isset($zahlungsart->nWaehrendBestellung) && $zahlungsart->nWaehrendBestellung == 1} selected{/if}>
                                        Ja
                                    </option>
                                    <option value="0"{if isset($zahlungsart->nWaehrendBestellung) && $zahlungsart->nWaehrendBestellung == 0} selected{/if}>
                                        Nein
                                    </option>
                                </select>
                            </span>
                        </li>
                    {/if}
                </ul>
            </div>
        </div>
        <div class="panel panel-default">
            {assign var=hasBody value=false}
            {foreach name=conf from=$Conf item=cnf}
            {if $cnf->cConf === 'Y'}
            {if $hasBody === false}<div class="panel-body">{assign var=hasBody value=true}{/if}
            <div class="input-group">
                    <span class="input-group-addon">
                        <label for="{$cnf->cWertName}">{$cnf->cName}</label>
                    </span>
                {if $cnf->cInputTyp === 'selectbox'}
                    <span class="input-group-wrap">
                        <select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="form-control combo">
                            {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                <option value="{$wert->cWert}" {if isset($cnf->gesetzterWert) && $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                            {/foreach}
                        </select>
                    </span>
                {elseif $cnf->cInputTyp === 'password'}
                    <input class="form-control" autocomplete="off" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" />
                {else}
                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" />
                    {if isset($cnf->kEinstellungenConf)}
                        <span id="EinstellungAjax_{$cnf->kEinstellungenConf}"></span>
                    {/if}
                {/if}
                    <span class="input-group-addon">{getHelpDesc cDesc=$cnf->cBeschreibung}</span>
                </div>
                {else}
                <div class="panel-heading">
                    <h3 class="panel-title">{#settings#}: {$cnf->cName}</h3>
                </div>
                <div class="panel-body">
                    {assign var=hasBody value=true}
                    {/if}
                    {/foreach}
                </div>
            </div>
        <p class="submit">
            <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
        </p>
    </form>
</div>