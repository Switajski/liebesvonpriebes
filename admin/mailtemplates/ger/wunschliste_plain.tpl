{includeMailTemplate template=header type=plain}

Hallo,
schau dir doch mal meinen Wunschzettel an {$Firma->cName}.

{foreach name=wunschlistepos from=$Wunschliste->CWunschlistePos_arr item=CWunschlistePos}
*{$CWunschlistePos->cArtikelName}*
{$ShopURL}/{$CWunschlistePos->Artikel->cURL}
{foreach name=eigenschaft from=$CWunschlistePos->CWunschlistePosEigenschaft_arr item=CWunschlistePosEigenschaft}
{if $CWunschlistePosEigenschaft->cFreifeldWert}
{$CWunschlistePosEigenschaft->cEigenschaftName}: {$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}{/if}
{else}
{$CWunschlistePosEigenschaft->cEigenschaftName}: {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}{/if}
{/if}
{/foreach}
{/foreach}


Alle Artikel anschauen >
{$ShopURL}/index.php?wlid={$CWunschliste->cURLID}

Danke.
{$Kunde->cVorname} {$Kunde->cNachname}

{includeMailTemplate template=footer type=plain}