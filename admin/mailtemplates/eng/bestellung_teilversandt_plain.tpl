{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

The tracking status for order no. {$Bestellung->cBestellNr} has changed.

{foreach name=pos from=$Bestellung->oLieferschein_arr item=oLieferschein}
    {if !$oLieferschein->getEmailVerschickt()}
        {foreach from=$oLieferschein->oPosition_arr item=Position}
            {$Position->nAusgeliefert} x {if $Position->nPosTyp==1}{$Position->cName} {if $Position->cArtNr}({$Position->cArtNr}){/if}
            {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
                {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
            {/foreach}
            {if $Position->cSeriennummer|strlen > 0}
                Serialnumber: {$Position->cSeriennummer}
            {/if}
            {if $Position->dMHD|strlen > 0}
                Best before: {$Position->dMHD}
            {/if}
            {if $Position->cChargeNr|strlen > 0}
                Charge: {$Position->cChargeNr}
            {/if}
        {else}
            {$Position->cName}
        {/if}
        {/foreach}

        {foreach from=$oLieferschein->oVersand_arr item=oVersand}
            {if $oVersand->getIdentCode()|strlen > 0}
                Tracking-Url: {$oVersand->getLogistikVarUrl()}
            {/if}
        {/foreach}
    {/if}
{/foreach}

You will be notified about the subsequent status of your order separately.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}