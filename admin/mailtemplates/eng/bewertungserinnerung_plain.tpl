{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized}  {$Kunde->cNachname},

We would love it if you could write a rating and share your experience with your recently products.

Please click on the product to rate it:

{foreach name=pos from=$Bestellung->Positionen item=Position}
    {if $Position->nPosTyp==1}
        {$Position->cName} ({$Position->cArtNr})
        {$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1

        {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
        {/foreach}
    {/if}
{/foreach}

Thank you for sharing!

{if !empty($oTrustedShopsBewertenButton->cURL)}
Were you satisfied with your order? If so, we hope you'll take a minute to write a recommendation.
{$oTrustedShopsBewertenButton->cURL}
{/if}

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}