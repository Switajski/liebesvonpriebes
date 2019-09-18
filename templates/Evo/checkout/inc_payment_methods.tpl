{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{foreach name=paymentmethod from=$Zahlungsarten item=zahlungsart}
    <div id="{$zahlungsart->cModulId}" class="col-xs-12">
        <div class="radio">
            <label for="payment{$zahlungsart->kZahlungsart}" class="btn-block">
                <input name="Zahlungsart" value="{$zahlungsart->kZahlungsart}" class="radio-checkbox" type="radio" id="payment{$zahlungsart->kZahlungsart}"{if $AktiveZahlungsart === $zahlungsart->kZahlungsart || $Zahlungsarten|@count == 1} checked{/if}{if $smarty.foreach.paymentmethod.first} required{/if}>
                <span class="control-label label-default">
                        {if $zahlungsart->cBild}
                            <img src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="img-responsive-width img-sm">
                        {else}
                            <span class="content">
                                <span class="title">{$zahlungsart->angezeigterName|trans}</span>
                            </span>
                        {/if}
                    {if $zahlungsart->fAufpreis != 0}
                        <span class="badge pull-right">
                                {if $zahlungsart->cGebuehrname|has_trans}
                                    <span>{$zahlungsart->cGebuehrname|trans} </span>
                                {/if}
                            {$zahlungsart->cPreisLocalized}
                            </span>
                    {/if}
                    {if $zahlungsart->cHinweisText|has_trans}
                        <span class="btn-block">
                                <small>{$zahlungsart->cHinweisText|trans}</small>
                            </span>
                    {/if}
                    </span>
            </label>
        </div>
    </div>
{/foreach}