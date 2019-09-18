{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
We would love it if you could write a rating and share your experience with your recently products.<br>
<br>
Please click on the product to rate it:<br>
<br>
{foreach name=pos from=$Bestellung->Positionen item=Position}
    <table cellpadding="00" cellspacing="0" border="0" width="100%">
        <tr>
            <td valign="top" style="padding-bottom:5px;">
                {if $Position->nPosTyp==1}
                    <a href="{$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1"><strong>{$Position->cName}</strong> ({$Position->cArtNr})</a>
                    {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
                        <br><strong>{$WKPosEigenschaft->cEigenschaftName}</strong>: {$WKPosEigenschaft->cEigenschaftWertName}
                    {/foreach}
                {/if}
            </td>
        </tr>
    </table>
{/foreach}<br>
<br>
Thank you for sharing!<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}