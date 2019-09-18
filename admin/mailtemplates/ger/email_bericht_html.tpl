{includeMailTemplate template=header type=html}

{function quantityStatisticRow}
    {if $nAnzahlVar !== -1}
        <div style="display:table-row">
            <div style="display:table-cell;padding:0.5em;text-align:right;">
                {$cAnzahlTitle}
            </div>
            <div style="display:table-cell;padding:0.5em;">
                {$nAnzahlVar}
            </div>
        </div>
    {/if}
{/function}

<h1>{$oMailObjekt->cIntervall}</h1>
<h2>Zeitraum: {$oMailObjekt->dVon|date_format:"d.m.Y - H:i"} bis {$oMailObjekt->dBis|date_format:"d.m.Y - H:i"}</h2>

<div style="display:table">
    {if is_array($oMailObjekt->oAnzahlArtikelProKundengruppe)}
        {foreach $oMailObjekt->oAnzahlArtikelProKundengruppe as $oArtikelProKundengruppe}
            {quantityStatisticRow cAnzahlTitle='Produkte pro Kundengruppe: '|cat:$oArtikelProKundengruppe->cName nAnzahlVar=$oArtikelProKundengruppe->nAnzahl}
        {/foreach}
    {/if}

    {quantityStatisticRow cAnzahlTitle='Neukunden' nAnzahlVar=$oMailObjekt->nAnzahlNeukunden}
    {quantityStatisticRow cAnzahlTitle='Neukunden, die etwas kauften' nAnzahlVar=$oMailObjekt->nAnzahlNeukundenGekauft}
    {quantityStatisticRow cAnzahlTitle='Bestellungen' nAnzahlVar=$oMailObjekt->nAnzahlBestellungen}
    {quantityStatisticRow cAnzahlTitle='Bestellungen von Neukunden' nAnzahlVar=$oMailObjekt->nAnzahlBestellungenNeukunden}
    {quantityStatisticRow cAnzahlTitle='Bestellungen, die bezahlt wurden' nAnzahlVar=$oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen}
    {quantityStatisticRow cAnzahlTitle='Bestellungen, die versendet wurden' nAnzahlVar=$oMailObjekt->nAnzahlVersendeterBestellungen}
    {quantityStatisticRow cAnzahlTitle='Besucher' nAnzahlVar=$oMailObjekt->nAnzahlBesucher}
    {quantityStatisticRow cAnzahlTitle='Besucher von Suchmaschinen' nAnzahlVar=$oMailObjekt->nAnzahlBesucherSuchmaschine}
    {quantityStatisticRow cAnzahlTitle='Bewertungen' nAnzahlVar=$oMailObjekt->nAnzahlBewertungen}
    {quantityStatisticRow cAnzahlTitle='Bewertungen, nicht freigeschaltet' nAnzahlVar=$oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet}

    {if isset($oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben) && isset($oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl)}
        {quantityStatisticRow cAnzahlTitle='Bewertungsguthaben gezahlt' nAnzahlVar=$oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl}
        {quantityStatisticRow cAnzahlTitle='Bewertungsguthaben Summe' nAnzahlVar=$oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben}
    {/if}

    {quantityStatisticRow cAnzahlTitle='Tags' nAnzahlVar=$oMailObjekt->nAnzahlTags}
    {quantityStatisticRow cAnzahlTitle='Tags, nicht freigeschaltet' nAnzahlVar=$oMailObjekt->nAnzahlTagsNichtFreigeschaltet}
    {quantityStatisticRow cAnzahlTitle='Geworbene Kunden' nAnzahlVar=$oMailObjekt->nAnzahlGeworbenerKunden}
    {quantityStatisticRow cAnzahlTitle='Geworbene Kunden, die etwas kauften' nAnzahlVar=$oMailObjekt->nAnzahlErfolgreichGeworbenerKunden}
    {quantityStatisticRow cAnzahlTitle='Versendete Wunschlisten' nAnzahlVar=$oMailObjekt->nAnzahlVersendeterWunschlisten}
    {quantityStatisticRow cAnzahlTitle='Durchgeführte Umfragen' nAnzahlVar=$oMailObjekt->nAnzahlDurchgefuehrteUmfragen}
    {quantityStatisticRow cAnzahlTitle='Neue Beitragskommentare' nAnzahlVar=$oMailObjekt->nAnzahlNewskommentare}
    {quantityStatisticRow cAnzahlTitle='Beitragskommentare, nicht freigeschaltet' nAnzahlVar=$oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet}
    {quantityStatisticRow cAnzahlTitle='Neue Produktanfragen' nAnzahlVar=$oMailObjekt->nAnzahlProduktanfrageArtikel}
    {quantityStatisticRow cAnzahlTitle='Neue Verfügbarkeitsanfragen' nAnzahlVar=$oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit}
    {quantityStatisticRow cAnzahlTitle='Produktvergleiche' nAnzahlVar=$oMailObjekt->nAnzahlVergleiche}
    {quantityStatisticRow cAnzahlTitle='Genutzte Kupons' nAnzahlVar=$oMailObjekt->nAnzahlGenutzteKupons}
</div>

{includeMailTemplate template=footer type=html}