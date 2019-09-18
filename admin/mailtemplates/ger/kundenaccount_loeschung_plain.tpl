{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

wie von Ihnen gewünscht haben wir heute Ihr Kundenkonto mit der
Emailadresse {$Kunde->cMail} gelöscht.

Sollten Sie mit unserem Service nicht zufrieden gewesen sein, so
teilen Sie uns dies bitte mit, damit wir unseren Service verbessern
können.

Falls Sie zu einem späteren Zeitpunkt wieder bei uns einkaufen
möchten, melden Sie sich einfach erneut an und eröffnen Sie ein neues
Kundenkonto bei uns.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}