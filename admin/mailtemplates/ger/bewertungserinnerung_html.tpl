{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
möchten Sie Ihre Erfahrungen mit Ihren kürzlich bei uns erworbenen Produkten mit anderen teilen, so würden wir uns sehr freuen, wenn Sie eine Bewertung abgeben.<br>
<br>
Zur Abgabe einer Bewertung klicken Sie einfach auf eines Ihrer erworbenen Produkte:<br>
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
Vielen Dank für Ihre Mühe.<br>
<br>
{if !empty($oTrustedShopsBewertenButton->cURL)}
    Waren Sie mit Ihrer Bestellung zufrieden? Dann würden wir uns über eine Empfehlung freuen ... es dauert auch nur eine Minute.<br>
    <a href="{$oTrustedShopsBewertenButton->cURL}"><img src="{$oTrustedShopsBewertenButton->cPicURL}" alt="Bewerten Sie uns!"></a><br><br>
{/if}<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}