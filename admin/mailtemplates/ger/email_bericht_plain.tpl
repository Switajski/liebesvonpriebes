{includeMailTemplate template=header type=plain}
=== {$oMailObjekt->cIntervall} ===

--- Zeitraum: {$oMailObjekt->dVon|date_format:"d.m.Y - H:i"} bis {$oMailObjekt->dBis|date_format:"d.m.Y - H:i"} ---

{if is_array($oMailObjekt->oAnzahlArtikelProKundengruppe)}Produkte pro Kundengruppe:

{foreach $oMailObjekt->oAnzahlArtikelProKundengruppe as $oArtikelProKundengruppe}
    {$oArtikelProKundengruppe->cName}: {$oArtikelProKundengruppe->nAnzahl}

{/foreach}{/if}
{if $oMailObjekt->nAnzahlNeukunden != -1}
Neukunde: {$oMailObjekt->nAnzahlNeukunden}
{/if}

{if $oMailObjekt->nAnzahlNeukundenGekauft != -1}
Neukunden, die gekauft haben: {$oMailObjekt->nAnzahlNeukundenGekauft}
{/if}

{if $oMailObjekt->nAnzahlBestellungen != -1}
Bestellungen: {$oMailObjekt->nAnzahlBestellungen}
{/if}

{if $oMailObjekt->nAnzahlBestellungenNeukunden != -1}
Bestellungen von Neukunden: {$oMailObjekt->nAnzahlBestellungenNeukunden}
{/if}

{if $oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen != -1}
Bestellungen die bezahlt wurden: {$oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen}
{/if}

{if $oMailObjekt->nAnzahlVersendeterBestellungen != -1}
Bestellungen die versendet wurden: {$oMailObjekt->nAnzahlVersendeterBestellungen}
{/if}

{if $oMailObjekt->nAnzahlBesucher != -1}
Besucher: {$oMailObjekt->nAnzahlBesucher}
{/if}

{if $oMailObjekt->nAnzahlBesucherSuchmaschine != -1}
Besucher von Suchmaschinen: {$oMailObjekt->nAnzahlBesucherSuchmaschine}
{/if}

{if $oMailObjekt->nAnzahlBewertungen != -1}
Bewertungen: {$oMailObjekt->nAnzahlBewertungen}
{/if}

{if $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet != -1}
Nicht freigeschaltete Bewertungen: {$oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet}
{/if}

{if isset($oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben) && isset($oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl)}
Bewertungsguthaben gezahlt: {$oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl}

Bewertungsguthaben Summe: {$oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben}
{/if}

{if $oMailObjekt->nAnzahlTags != -1}
Tags: {$oMailObjekt->nAnzahlTags}
{/if}

{if $oMailObjekt->nAnzahlTagsNichtFreigeschaltet != -1}
Tags nicht freigeschaltet: {$oMailObjekt->nAnzahlTagsNichtFreigeschaltet}
{/if}

{if $oMailObjekt->nAnzahlGeworbenerKunden != -1}
Geworbene Kunden: {$oMailObjekt->nAnzahlGeworbenerKunden}
{/if}

{if $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden != -1}
Geworbene Kunden, die kauften: {$oMailObjekt->nAnzahlErfolgreichGeworbenerKunden}
{/if}

{if $oMailObjekt->nAnzahlVersendeterWunschlisten != -1}
Versendete Wunschlisten: {$oMailObjekt->nAnzahlVersendeterWunschlisten}
{/if}

{if $oMailObjekt->nAnzahlDurchgefuehrteUmfragen != -1}
Durchgeführte Umfragen: {$oMailObjekt->nAnzahlDurchgefuehrteUmfragen}
{/if}

{if $oMailObjekt->nAnzahlNewskommentare != -1}
Neue Beitragskommentare: {$oMailObjekt->nAnzahlNewskommentare}
{/if}

{if $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet != -1}
Beitragskommentare nicht freigeschaltet: {$oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet}
{/if}

{if $oMailObjekt->nAnzahlProduktanfrageArtikel != -1}
Neue Produktanfragen: {$oMailObjekt->nAnzahlProduktanfrageArtikel}
{/if}

{if $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit != -1}
Neue Verfügbarkeitsanfragen: {$oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit}
{/if}

{if $oMailObjekt->nAnzahlVergleiche != -1}
Produktvergleiche: {$oMailObjekt->nAnzahlVergleiche}
{/if}

{if $oMailObjekt->nAnzahlGenutzteKupons != -1}
Genutzte Kupons: {$oMailObjekt->nAnzahlGenutzteKupons}
{/if}
{includeMailTemplate template=footer type=plain}