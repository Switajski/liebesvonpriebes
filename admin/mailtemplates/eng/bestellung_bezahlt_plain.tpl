{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

We have received your payment of {$Bestellung->WarensummeLocalized[0]} for your order of {$Bestellung->dErstelldatum_en}.

Your order is as follows:

{foreach name=pos from=$Bestellung->Positionen item=Position}
    {if $Position->nPosTyp==1}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}
        {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
        {/foreach}
    {else}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}
    {/if}
{/foreach}

{foreach name=steuerpositionen from=$Bestellung->Steuerpositionen item=Steuerposition}
    {$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}

{if isset($GuthabenNutzen) && $GuthabenNutzen == 1}
    Voucher: -{$GutscheinLocalized}
{/if}

Total: {$Bestellung->WarensummeLocalized[0]}

You will be notified about the dispatch of your goods separately.

Yours sincerely,

{$Firma->cName}

{includeMailTemplate template=footer type=plain}