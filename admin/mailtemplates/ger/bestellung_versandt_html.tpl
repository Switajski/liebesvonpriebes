{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Ihre Bestellung vom {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} wurde heute an Sie versandt.<br>
<br>
{foreach name=pos from=$Bestellung->oLieferschein_arr item=oLieferschein}
    {if $oLieferschein->oVersand_arr|count > 1}
        Mit den nachfolgenden Links können Sie sich über den Status Ihrer Sendungen informieren:
    {else}
        Mit dem nachfolgenden Link können Sie sich über den Status Ihrer Sendung informieren:
    {/if}<br>
    <br>
    {foreach from=$oLieferschein->oVersand_arr item=oVersand}
        {if $oVersand->getIdentCode()|strlen > 0}
            <strong>Tracking-Url:</strong> <a href="{$oVersand->getLogistikVarUrl()}">{$oVersand->getIdentCode()}</a><br>
            {if $oVersand->getHinweis()|strlen > 0}
                <strong>Tracking-Hinweis:</strong> {$oVersand->getHinweis()}<br>
            {/if}
        {/if}
    {/foreach}
{/foreach}
<br>
Wir wünschen Ihnen viel Spaß mit der Ware und bedanken uns für Ihren Einkauf und Ihr Vertrauen.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}