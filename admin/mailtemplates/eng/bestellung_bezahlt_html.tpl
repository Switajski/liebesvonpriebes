{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
We have received your payment of {$Bestellung->WarensummeLocalized[0]} for your order of {$Bestellung->dErstelldatum_en}.<br>
<br>
Your order is as follows:<br>
<br>
{foreach name=pos from=$Bestellung->Positionen item=Position}
    <table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
        <tr>
            <td class="column" {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen=="Y"}width="50%"{else}width="70%"{/if} align="left" valign="top">
                {if $Position->nPosTyp==1}
                    <strong>{$Position->cName}</strong>
                    {if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen=="Y" && $Position->cLieferstatus}
                        <br><small>Shipping time: {$Position->cLieferstatus}</small>
                    {/if}<br>
                    {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
                        <br><strong>{$WKPosEigenschaft->cEigenschaftName}</strong>: {$WKPosEigenschaft->cEigenschaftWertName}
                    {/foreach}
                {else}
                    <strong>{$Position->cName}</strong>
                {/if}
            </td>
            <td class="column" width="10%" align="left" valign="top">
                <strong class="mobile-only">Quantity:</strong> {$Position->nAnzahl}
            </td>
            {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen=="Y"}
                <td class="column" width="20%" align="right" valign="top">
                    <span class="standard">{$Position->cEinzelpreisLocalized[$NettoPreise]}</span>
                </td>
            {/if}
            <td class="column" width="20%" align="right" valign="top">
                <span class="standard">{$Position->cGesamtpreisLocalized[$NettoPreise]}</span>
            </td>
        </tr>
    </table>
{/foreach}
<table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
    {foreach name=steuerpositionen from=$Bestellung->Steuerpositionen item=Steuerposition}
        <tr>
            <td align="right" valign="top">
                {$Steuerposition->cName}:
            </td>
            <td width="90" align="right" valign="top">
                {$Steuerposition->cPreisLocalized}
            </td>
        </tr>
    {/foreach}
    {if isset($GuthabenNutzen) && $GuthabenNutzen == 1}
        <tr>
            <td align="right" valign="top">
                Voucher:
            </td>
            <td width="90" align="right" valign="top">
                <strong>-{$GutscheinLocalized}</strong>
            </td>
        </tr>
    {/if}
    <tr>
        <td align="right" valign="top">
            <strong>Total:</strong>
        </td>
        <td width="90" align="right" valign="top">
            <strong>{$Bestellung->WarensummeLocalized[0]}</strong>
        </td>
    </tr>
</table><br>
<br>
You will be notified about the dispatch of your goods separately.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}