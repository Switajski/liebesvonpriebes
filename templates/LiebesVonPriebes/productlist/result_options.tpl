{assign var='show_filters' value=false}
{if $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0 || $Suchergebnisse->GesamtanzahlArtikel >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab || $NaviFilter->nAnzahlFilter > 0}
    {assign var='show_filters' value=true}
{/if}
