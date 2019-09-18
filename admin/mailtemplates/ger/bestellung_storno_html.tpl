{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben storniert.
<strong>Bestellnummer:</strong> {$Bestellung->cBestellNr}<br>
<br>
Mit freundlichem Gruﬂ,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}