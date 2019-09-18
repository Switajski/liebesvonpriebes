{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
vielen Dank für Ihre Bewertung eines Artikels. Ihr Guthaben Bonus in Höhe von {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} steht Ihnen ab sofort zur Verfügung.<br>
Sie können Ihr Guthaben jederzeit bei einem Ihrer nächsten Einkäufe einlösen.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}