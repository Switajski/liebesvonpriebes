{includeMailTemplate template=header type=plain}

vielen Dank für Ihre Warenrücksendung bei {$Einstellungen.global.global_shopname}.

Ihre Warenrücksendung mit der RMA Nummer {$oRMA->cRMANumber} umfasst folgende Positionen:

{if isset($oRMA->oRMAArtikel_arr) && $oRMA->oRMAArtikel_arr|@count > 0}
{foreach name=artikel from=$oRMA->oRMAArtikel_arr item=oRMAArtikel}
Artikel: {$oRMAArtikel->cArtikelName}
Anzahl: {$oRMAArtikel->fAnzahl}
{/foreach}
{/if}

Sobald Ihre Rücksendung eintrifft, werden wir eine Erstattung über den
Warenwert veranlassen. Dieser Betrag wird auf das bei Ihrer Bestellung
genutzte Bankkonto zurückgebucht. Die Erstattung kann einige Tage
dauern. Wir bitten um Ihr Verständnis.

Sollten Sie noch weitere Fragen haben, zögern Sie nicht, uns zu schreiben.

{includeMailTemplate template=footer type=plain}