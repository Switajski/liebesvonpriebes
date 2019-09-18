{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

wir freuen uns Ihnen mitteilen zu dürfen, dass in unserem Onlineshop folgenden Kupon ({$Kupon->AngezeigterName}) verwenden dürfen:

{if $Kupon->cKuponTyp=="standard"}Kuponwert: {$Kupon->cLocalizedWert} {if $Kupon->cWertTyp=="prozent"}Rabatt auf den gesamten Einkauf{/if}{/if}{if $Kupon->cKuponTyp=="versandkupon"}Mit diesem Kupon können Sie versandkostenfrei bei uns einkaufen!
Er gilt für folgende Lieferländer: {$Kupon->cLieferlaender|upper}{/if}

Kuponcode: {$Kupon->cCode}

Gültig vom {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} bis {$Kupon->cGueltigBisLong}{/if}

{if $Kupon->fMindestbestellwert>0}Mindestbestellwert: {$Kupon->cLocalizedMBW}

{else}Es gibt keinen Mindestbestellwert!

{/if}{if $Kupon->nVerwendungenProKunde>1}Sie dürfen diesen Kupon bei insgesamt {$Kupon->nVerwendungenProKunde} Einkäufen bei uns nutzen.

{elseif $Kupon->nVerwendungenProKunde==0}Sie dürfen diesen Kupon bei beliebig vielen Einkäufen bei uns nutzen.

{/if}{if $Kupon->nVerwendungen>0}Bitte beachten Sie, dass dieser Kupon auf eine maximale Verwendungsanzahl hat.

{/if}{if count($Kupon->Kategorien)>0}Der Kupon gilt für folgende Kategorien:


{foreach name=art from=$Kupon->Kategorien item=Kategorie}
{$Kategorie->cName} >
{$Kategorie->cURL}
{/foreach}{/if}

{if count($Kupon->Artikel)>0}Der Kupon gilt für folgende Artikel:


{foreach name=art from=$Kupon->Artikel item=Artikel}
{$Artikel->cName} >
{$Artikel->cURL}
{/foreach}{/if}


Sie lösen den Kupon ein, indem Sie beim Bestellvorgang den Kuponcode in das vorgesehene Feld eintragen.

Viel Spaß bei Ihrem nächsten Einkauf in unserem Shop.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}