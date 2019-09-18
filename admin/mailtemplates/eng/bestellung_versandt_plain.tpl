{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Your order dated {$Bestellung->dErstelldatum_de} with order no. {$Bestellung->cBestellNr} has been shipped to you today.

{foreach name=pos from=$Bestellung->oLieferschein_arr item=oLieferschein}
    {if $oLieferschein->oVersand_arr|count > 1}
        You may track the shipping status by clicking on the links below:
    {else}
        You may track the shipping status by clicking on the link below:
    {/if}

    {foreach from=$oLieferschein->oVersand_arr item=oVersand}
        {if $oVersand->getIdentCode()|strlen > 0}
            Tracking URL: {$oVersand->getLogistikVarUrl()}
            {if $oVersand->getHinweis()|strlen > 0}
                Tracking notice: {$oVersand->getHinweis()}
            {/if}
        {/if}
    {/foreach}
{/foreach}

We hope the merchandise meets with your full satisfaction and thank you for your purchase.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}