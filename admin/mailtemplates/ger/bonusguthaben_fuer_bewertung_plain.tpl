{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

vielen Dank für Ihre Bewertung eines Artikels. Ihr Guthaben Bonus in Höhe von {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} steht Ihnen ab sofort zur Verfügung.
Sie können Ihr Guthaben jederzeit bei einem Ihrer nächsten Einkäufe einlösen.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}