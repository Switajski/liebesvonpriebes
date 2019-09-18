{includeMailTemplate template=header type=plain}
=== {$oMailObjekt->cIntervall} ===

--- Period: {$oMailObjekt->dVon} - {$oMailObjekt->dBis} ---

{if is_array($oMailObjekt->oAnzahlArtikelProKundengruppe)}Products per customer group:

{foreach $oMailObjekt->oAnzahlArtikelProKundengruppe as $oArtikelProKundengruppe}
    {$oArtikelProKundengruppe->cName}: {$oArtikelProKundengruppe->nAnzahl}

{/foreach}{/if}
{if $oMailObjekt->nAnzahlNeukunden != -1}
New customers: {$oMailObjekt->nAnzahlNeukunden}
{/if}

{if $oMailObjekt->nAnzahlNeukundenGekauft != -1}
New customers who purchased something: {$oMailObjekt->nAnzahlNeukundenGekauft}
{/if}

{if $oMailObjekt->nAnzahlBestellungen != -1}
Orders: {$oMailObjekt->nAnzahlBestellungen}
{/if}

{if $oMailObjekt->nAnzahlBestellungenNeukunden != -1}
Orders from new customers: {$oMailObjekt->nAnzahlBestellungenNeukunden}
{/if}

{if $oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen != -1}
Paid orders: {$oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen}
{/if}

{if $oMailObjekt->nAnzahlVersendeterBestellungen != -1}
Shipped orders: {$oMailObjekt->nAnzahlVersendeterBestellungen}
{/if}

{if $oMailObjekt->nAnzahlBesucher != -1}
Visitors: {$oMailObjekt->nAnzahlBesucher}
{/if}

{if $oMailObjekt->nAnzahlBesucherSuchmaschine != -1}
Visitors from search engines: {$oMailObjekt->nAnzahlBesucherSuchmaschine}
{/if}

{if $oMailObjekt->nAnzahlBewertungen != -1}
Ratings: {$oMailObjekt->nAnzahlBewertungen}
{/if}

{if $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet != -1}
Non-public ratings: {$oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet}
{/if}

{if $oMailObjekt->oAnzahlGezahltesGuthaben != -1}
Rating credit paid: {$oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl}

Rating credit total: {$oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben}
{/if}

{if $oMailObjekt->nAnzahlTags != -1}
Tags: {$oMailObjekt->nAnzahlTags}
{/if}

{if $oMailObjekt->nAnzahlTagsNichtFreigeschaltet != -1}
Tags not approved: {$oMailObjekt->nAnzahlTagsNichtFreigeschaltet}
{/if}

{if $oMailObjekt->nAnzahlGeworbenerKunden != -1}
Acquired customers: {$oMailObjekt->nAnzahlGeworbenerKunden}
{/if}

{if $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden != -1}
Acquired customers who purchased something: {$oMailObjekt->nAnzahlErfolgreichGeworbenerKunden}
{/if}

{if $oMailObjekt->nAnzahlVersendeterWunschlisten != -1}
Wish lists sent: {$oMailObjekt->nAnzahlVersendeterWunschlisten}
{/if}

{if $oMailObjekt->nAnzahlDurchgefuehrteUmfragen != -1}
Surveys conducted: {$oMailObjekt->nAnzahlDurchgefuehrteUmfragen}
{/if}

{if $oMailObjekt->nAnzahlNewskommentare != -1}
New article comments: {$oMailObjekt->nAnzahlNewskommentare}
{/if}

{if $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet != -1}
Article comments not published: {$oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet}
{/if}

{if $oMailObjekt->nAnzahlProduktanfrageArtikel != -1}
New product questions: {$oMailObjekt->nAnzahlProduktanfrageArtikel}
{/if}

{if $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit != -1}
New availability questions: {$oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit}
{/if}

{if $oMailObjekt->nAnzahlVergleiche != -1}
Product comparisons: {$oMailObjekt->nAnzahlVergleiche}
{/if}

{if $oMailObjekt->nAnzahlGenutzteKupons != -1}
Coupons used: {$oMailObjekt->nAnzahlGenutzteKupons}
{/if}
{includeMailTemplate template=footer type=plain}