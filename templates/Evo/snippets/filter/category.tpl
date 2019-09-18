<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach  name=kategorieauswahl from=$Suchergebnisse->Kategorieauswahl item=Kategorie}
        {if $Kategorie->nAnzahl >= 1}
            <li>
                <a rel="nofollow" href="{$Kategorie->cURL}">
                    <span class="badge pull-right">{if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Kategorie->nAnzahl}{/if}</span>
                    <span class="value">
                        <i class="fa {if isset($NaviFilter->KategorieFilter) && $NaviFilter->KategorieFilter->kKategorie == $Kategorie->kKategorie}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                        {$Kategorie->cName|escape:'html'}
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
</ul>
