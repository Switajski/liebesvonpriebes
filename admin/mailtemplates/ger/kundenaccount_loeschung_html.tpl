{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
wie von Ihnen gewünscht haben wir heute Ihr Kundenkonto mit der
Emailadresse {$Kunde->cMail} gelöscht.<br>
<br>
Sollten Sie mit unserem Service nicht zufrieden gewesen sein, so
teilen Sie uns dies bitte mit, damit wir unseren Service verbessern
können.<br>
<br>
Falls Sie zu einem späteren Zeitpunkt wieder bei uns einkaufen
möchten, melden Sie sich einfach erneut an und eröffnen Sie ein neues
Kundenkonto bei uns.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}