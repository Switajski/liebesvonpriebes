{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

wir haben Ihre Kundengruppe geändert. Sie müßten ab sofort andere Preise als den Standardpreis angezeigt bekommen.

Momentan haben wir es noch nicht geschafft, alle Preise anzupassen.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}