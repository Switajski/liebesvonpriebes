{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben storniert.<br>
Bestellnummer: {$Bestellung->cBestellNr}

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}