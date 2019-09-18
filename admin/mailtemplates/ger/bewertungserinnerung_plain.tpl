{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

möchten Sie Ihre Erfahrungen mit Ihren kürzlich bei uns erworbenen Produkten mit anderen teilen, so würden wir uns sehr freuen, wenn Sie eine Bewertung abgeben.

Zur Abgabe einer Bewertung klicken Sie einfach auf eines Ihrer erworbenen Produkte:

{foreach name=pos from=$Bestellung->Positionen item=Position}
{if $Position->nPosTyp==1}
{$Position->cName} ({$Position->cArtNr})
{$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1

{foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}

{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
{/foreach}
{/if}
{/foreach}

Vielen Dank für Ihre Mühe.

{if !empty($oTrustedShopsBewertenButton->cURL)}
Waren Sie mit Ihrer Bestellung zufrieden? Dann würden wir uns über eine Empfehlung freuen ... es dauert auch nur eine Minute.
Bewerten Sie uns unter {$oTrustedShopsBewertenButton->cURL}
{/if}

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}