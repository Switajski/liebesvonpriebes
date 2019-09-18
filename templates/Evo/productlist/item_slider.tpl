{* template to display products in slider *}

<div class="product-cell text-center{if isset($class)} {$class}{/if} thumbnail">
    <a class="image-wrapper" href="{$Artikel->cURL}">
        {if isset($Artikel->Bilder[0]->cAltAttribut)}
            {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:"html"}
        {else}
            {assign var="alt" value=$Artikel->cName}
        {/if}

        {*include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadKlein alt=$alt*}
        <img src="{$Artikel->Bilder[0]->cPfadKlein}" alt="{$alt}" />
        {if isset($Artikel->oSuchspecialBild) && !isset($hideOverlays)}
            <img class="overlay-img hidden-xs" src="{$Artikel->oSuchspecialBild->cPfadKlein}"
                 alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}">
        {/if}
    </a>
    <div class="caption">
        <h4 class="title word-break">
            {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                <span class="article-bundle-info">
                    <span class="bundle-amount">{$Artikel->fAnzahl_stueckliste}</span> <span class="bundle-times">x</span>
                </span>
            {/if}
            <a href="{$Artikel->cURL}">{$Artikel->cKurzbezeichnung}</a>
        </h4>
        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}<small>{include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}</small>{/if}
        {if isset($Artikel->Preise->strPreisGrafik_Suche)}
            {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$Artikel->Preise->strPreisGrafik_Suche tplscope=$tplscope}
        {else}
            {include file="productdetails/price.tpl" Artikel=$Artikel price_image=null tplscope=$tplscope}
        {/if}
    </div>
</div>{* /product-cell *}
